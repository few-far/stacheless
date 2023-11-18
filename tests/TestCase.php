<?php

namespace Tests;

use Statamic\Facades\GlobalSet;
use Statamic\Facades\Site;
use Statamic\Globals\Variables;

class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app->booted(function () {
            // $site_settings = GlobalSet::make('site_settings')->title('Site Settings')->save();
            // $site_settings->addLocalization(
            //     tap($site_settings->makeLocalization(Site::default()->handle()), function (Variables $variables) {
            //         // $variables->set('not_found_entry', $not_found->id());
            //     })
            // );
        });
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array<int, class-string<\Illuminate\Support\ServiceProvider>>
     */
    protected function getPackageProviders($app)
    {
        return [
            \Statamic\Providers\StatamicServiceProvider::class,
            \FewFar\Stacheless\ServiceProvider::class,
        ];
    }
}
