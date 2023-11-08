<?php

namespace FewFar\Stacheless\Cms\Redirects;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Statamic\CP\Column;
use Statamic\Facades\Blueprint;

class RedirectController extends Controller
{
    public function view()
    {
        return view('cp.redirects', [
            'redirect' => [
                'create_url' => cp_route('redirects.create'),
                'columns' => [
                    Column::make('source'),
                    Column::make('target'),
                    Column::make('code'),
                    Column::make('created_at')->label('Created'),
                    Column::make('source_type')->label('Type')->visible(false),
                    Column::make('updated_at')->label('Updated')->visible(false),
                ],
                'items' => Redirect::all()->map(function ($redirect) {
                    $model = $redirect->only('enabled', 'source_type', 'source', 'target', 'code');

                    $model['created_at'] = $redirect->created_at->format('Y-m-d h:i:s');
                    $model['updated_at'] = $redirect->updated_at->format('Y-m-d h:i:s');
                    $model['edit_url'] = cp_route('redirects.edit', $redirect);
                    $model['delete_url'] = cp_route('redirects.delete', $redirect);
                    return $model;
                }),
            ],
        ]);
    }

    protected function blueprint()
    {
        return Blueprint::makeFromFields([
            'source' => [
                'type' => 'text',
                'display' => 'Source',
                'validate' => 'required|max:2048',
                'instructions' => 'The url or pattern to use for the redirect.',
                'width' => 66,
            ],
            'source_type' => [
                'type' => 'button_group',
                'options' => [
                    'equals' => 'Equals',
                    'regex' => 'Regex',
                ],
                'instructions' => 'Allows for advanced redirects.',
                'default' => 'equals',
                'display' => 'Mode',
                'width' => 33,
            ],
            'target' => [
                'type' => 'text',
                'display' => 'Redirect to',
                'validate' => 'required|max:2048',
                'instructions' => 'Where to send the user once the url has been matched.',
                'width' => 66,
            ],
            'code' => [
                'type' => 'select',
                'instructions' => 'Which type of redirect to use.',
                'options' => [
                    '302' => 'Temporary (302)',
                    '301' => 'Permanent (301)',
                ],
                'placeholder' => 'Temporary (302)',
                'default' => '302',
                'display' => 'Code',
                'width' => 33,
            ],
            'enabled' => [
                'type' => 'toggle',
                'default' => false,
                'width' => 33,
            ],
        ]);
    }

    public function create()
    {
        $blueprint = $this->blueprint();
        $fields = $blueprint->fields();

        return view('cp.redirects-form', [
            'form' => [
                'title' => 'Create Redirect',
                'action' => cp_route('redirects.store'),
                'blueprint' => $blueprint->toPublishArray(),
                'values' => $fields->values()->merge([
                    'code' => '302',
                    'source_type' => 'equals',
                ]),
                'meta' => $fields->meta(),
            ],
        ]);
    }

    public function edit(Request $request)
    {
        $values = Redirect::findOrFail($request->route('id'))->toArray();
        $blueprint = $this->blueprint();
        $fields = $blueprint->fields()->addValues($values)->preProcess();

        return view('cp.redirects-form', [
            'form' => [
                'title' => 'Edit Redirect',
                'action' => cp_route('redirects.update', [$values['id']]),
                'blueprint' => $blueprint->toPublishArray(),
                'meta' => $fields->meta(),
                'values' => $fields->values(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $blueprint = $this->blueprint();
        $fields = $blueprint->fields()->addValues($request->all());

        $fields->validate();

        $values = $fields->process()->values();

        $redirect = Redirect::make($values->toArray());
        $redirect->id = Str::uuid();
        $redirect->save();

        return response()->json([
            'redirect' => cp_route('redirects.edit', $redirect),
        ]);
    }

    public function update(Request $request)
    {
        $redirect = Redirect::findOrFail($request->route('id'));
        $blueprint = $this->blueprint();
        $fields = $blueprint->fields()->addValues($request->all());

        $fields->validate();

        $values = $fields->process()->values();
        $redirect->update($values->toArray());

        return response()->noContent();
    }

    public function destroy(Request $request)
    {
        $redirect = Redirect::findOrFail($request->route('id'));

        $redirect->delete();

        return response()->noContent();
    }
}
