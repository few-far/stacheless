<?php

namespace FewFar\Stacheless\Cms\Forms;

use FewFar\Stacheless\Cms\Support\Concerns\BuildsModels;
use FewFar\Stacheless\Cms\Forms\Mail\Submission;
use App\Domain\Newsletter\MailchimpService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\UrlWindow;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationRuleParser;
use Statamic\Facades\Entry;
use Statamic\Facades\GlobalSet;

class SubmissionController extends Controller
{
    use BuildsModels;

    /**
     * List all submissions for given form.
     *
     * @param Illuminate\Http\Request  $request
     * @return Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $paginator = DB::table('cms_forms_submissions')
            ->where('entry_id', $request->route('form_id'))
            ->paginate(50);

        $paginator->withQueryString();

        $items = $paginator->all();
        $window = UrlWindow::make($paginator);

        return response()
            ->json([
                'items' => collect($items)->map(function ($item) {
                    $model = (array)$item;
                    $model['payload'] = json_decode($item->payload, true);
                    $model['first_email'] = collect($model['payload']['field_rows'] ?? null)
                        ->where('name', 'email')
                        ->map->value
                        ->first();

                    return $model;
                }),
                'pagination' => (collect($window)->flatten()->filter()->count() < 2) ? null : [
                    'type' => 'pagination',
                    'window' => $window,
                    'current' => strval($paginator->currentPage()),
                ],
            ]);
    }

    /**
     * Stores form submission in the DB.
     *
     * @param Illuminate\Http\Request  $request
     * @return Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $form = Entry::query()
            ->where('collection', 'forms')
            ->where('id', $request->query('form'))
            ->first();

        abort_unless($form, 404);

        $fields = $form->augmentedValue('fields')->value();
        $rules = $this->makeValidationRules($fields);
        $valid = $request->validate($rules);

        $submission = $this->makeSubmission($form, $valid);

        rescue(fn () => $this->handleEmail($form, $submission));

        $this->handleStorage($form, $submission);

        // rescue(fn () => $this->handleMailchimp($form, $submission, $valid));

        return response()
            ->json(['status' => 'success']);
    }

    /**
     * Creates a record of the submission, serialising the form fields at this given moment in time.
     *
     * @param \Statamic\Contracts\Entries\Entry  $form
     * @param array|\Illuminate\Support\Collection  $values
     * @return array
     */
    protected function makeSubmission($form, $values)
    {
        $rows = $form->augmentedValue('fields')->value();

        return [
            'form_id' => $form->id(),
            'form_title' => $form->get('title'),
            'field_rows' => collect($rows)->map(function ($field) use ($values) {
                $field_values = $field->toArray();

                return [
                    'name' => $this->get($field_values, 'name'),
                    'label' => $this->get($field_values, 'label'),
                    'value' => $this->get($values, $this->get($field_values, 'name')),
                ];
            }),
            'meta_rows' => collect($this->get($values, '_meta'))->map(function ($value, $name) {
                return compact('value', 'name');
            }),
        ];
    }

    /**
     * Send email to admins.
     *
     * @param \Statamic\Contracts\Entries\Entry  $form
     * @param array|\Illuminate\Support\Collection  $values
     * @return void
     */
    protected function handleEmail($form, $submission)
    {
        /** @var \Statamic\Globals\Variables */
        $set = GlobalSet::find('site_settings');
        $to = $set->inCurrentSite()->get('form_submission_email');

        if (!$to) {
            report(new \Exception('No email set in Globals for form submissions.'));
            return;
        }

        // CMS allows comma separated list of emails.
        $emails = collect(preg_split('/,\s*/', $to))
            ->filter()
            ->values()
            ->all();

        Mail::to($emails)->send(app(Submission::class, compact('form', 'submission')));
    }

    /**
     * Stores copy in DB.
     *
     * @param \Statamic\Contracts\Entries\Entry|\Statamic\Entries\Entry  $form
     * @param array|\Illuminate\Support\Collection  $submission
     * @return void
     */
    protected function handleStorage($form, $submission)
    {
        // Prune older entries
        DB::table('cms_forms_submissions')
            ->where('created_at', '<', now()->subMonths(3))
            ->delete();

        DB::table('cms_forms_submissions')->insert([
            'id' => Str::uuid(),
            'created_at' => 'NOW()',
            'updated_at' => 'NOW()',
            'entry_id' => $form->id(),
            'payload' => json_encode($submission),
        ]);
    }

    /**
     * Create Laravel validation rules from the CMSable form.
     *
     * @param array<Statamic\Fields\Values>  $fields
     * @return array
     */
    protected function makeValidationRules($fields)
    {
        // Content editor is able to configure their old rules in the CMS,
        // compile the rules and the toggleable "required" cms field:
        $validator = $this->getValidationFactory()->make([], []);
        /** @var ValidationRuleParser */
        $parser = app(ValidationRuleParser::class, ['data' => []]);

        return collect($fields)
            ->keyBy->raw('name')
            ->map(function ($field) use ($parser, $validator) {
                // If the require toggle is on we can manually insert "required" as a rule.
                $rules = collect($field['required'] ? 'required' : null);
                $rules->push(...$parser->explode(['rules' => $field->raw('validation_rules') ?? []])->rules);
                $rules->filter(fn ($rule) => method_exists($validator, "validate" . $parser->parse($rule)[0] ?? '_NULL'));

                return $rules->values()->all();
            })
            ->put('_meta', 'array')
            ->put('_agreement', 'accepted')
            ->put('_newsletter', 'boolean')
            ->all();
    }

    /**
     * Adds user as subscriber in mailchimp if opted in
     *
     * @param \Statamic\Contracts\Entries\Entry|\Statamic\Entries\Entry  $form
     * @param array|\Illuminate\Support\Collection  $submission
     * @return void
     */
    protected function handleMailchimp($form, $submission, $valid)
    {
        if (!Arr::get($valid, '_newsletter')) {
            return;
        }

        /** @var MailchimpService */
        $service = app(MailchimpService::class);

        return $service->add($valid['email'], [
            'name' => $valid['name'],
        ]);
    }
}
