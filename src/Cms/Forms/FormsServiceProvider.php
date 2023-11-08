<?php

namespace FewFar\Stacheless\Cms\Forms;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Statamic\Statamic;

class FormsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Route::post(config('domain.forms.submission_url', '/!/submissions'), [SubmissionController::class, 'store']);

        Statamic::booted(function () {
            Fieldtypes\CmsFormSubmissions::register();
        });

        Statamic::pushCpRoutes(function () {
            Route::get('app/forms/{form_id}/submissions', [SubmissionController::class, 'index'])
                ->middleware('can:view forms entries')
                ->name('app.forms.submissions');
        });

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/resources/blueprints/form.yaml' => resource_path('blueprints/collections/forms/form.yaml'),
                __DIR__.'/resources/views/emails/form-submission.blade.php' => resource_path('views/emails/form-submission.blade.php'),
            ], 'stacheless-resources');
        }
    }
}
