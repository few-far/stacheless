<?php

namespace FewFar\Stacheless\Cms;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Statamic\Facades\CP\Nav;
use Statamic\Statamic;

class CmsServiceProvider extends ServiceProvider
{
    protected $binds = [
        \Statamic\Http\Controllers\FrontendController::class => \FewFar\Stacheless\Cms\Http\FrontendController::class,

        \Statamic\Contracts\Entries\Entry::class => \FewFar\Stacheless\Cms\MonkeyPatch\Entry::class,
        \Statamic\Contracts\Taxonomies\Term::class => \FewFar\Stacheless\Cms\MonkeyPatch\Term::class,
        \Statamic\Contracts\Assets\Asset::class => \FewFar\Stacheless\Cms\MonkeyPatch\Asset::class,

        \Statamic\Fieldtypes\Assets\Assets::class => \FewFar\Stacheless\Cms\MonkeyPatch\Fieldtypes\Assets::class,
        \Statamic\Fieldtypes\Entries::class => \FewFar\Stacheless\Cms\MonkeyPatch\Fieldtypes\Entries::class,
    ];

    protected $listens = [
        \Statamic\Events\NavTreeSaved::class => [Navigation::class, 'handleNavTreeSaved'],
        \Statamic\Events\EntrySaved::class => [Navigation::class, 'handleEntrySaved'],
    ];

    protected $composers = [
        'errors.404' => \FewFar\Stacheless\Cms\NotFoundComposer::class,
        'errors::404' => \FewFar\Stacheless\Cms\NotFoundComposer::class,
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(Redirects\RedirectServiceProvider::class);
        $this->app->register(Forms\FormsServiceProvider::class);
        $this->app->register(Imaging\ImagingServiceProvider::class);

        foreach ($this->binds as $key => $value) {
            $this->app->bind($key, $value);
        }

        foreach ($this->listens as $key => $value) {
            Event::listen($key, $value);
        }

        foreach ($this->composers as $key => $value) {
            View::composer($key, $value);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/resources/blueprints/collections/reusable_content.yaml' => resource_path('blueprints/collections/reusable_content/reusable_content.yaml'),
            __DIR__.'/resources/blueprints/navigations/footer.yaml' => resource_path('blueprints/collections/navigations/footer.yaml'),
            __DIR__.'/resources/blueprints/navigations/header.yaml' => resource_path('blueprints/collections/navigations/header.yaml'),
            __DIR__.'/resources/blueprints/navigations/menu.yaml' => resource_path('blueprints/collections/navigations/menu.yaml'),
            __DIR__.'/resources/blueprints/globals/site_settings.yaml' => resource_path('blueprints/collections/globals/site_settings.yaml'),
        ], 'stacheless-resources');

        $this->boot_CpStatamicBooted();

        $this->boot_CpNavigation();
    }

    protected function boot_CpStatamicBooted()
    {
        Statamic::booted(function () {
            $this->app->bind(\Statamic\Contracts\Assets\Asset::class, \FewFar\Stacheless\Cms\MonkeyPatch\Asset::class);
            $this->app->bind(\Statamic\Contracts\Entries\Entry::class, \FewFar\Stacheless\Cms\MonkeyPatch\Entry::class);
            $this->app->bind(\Statamic\Contracts\Taxonomies\Term::class, \FewFar\Stacheless\Cms\MonkeyPatch\Term::class);
        });
    }

    protected function boot_CpNavigation()
    {
        Nav::extend(function ($nav) {
            $nav->remove('Top Level');

            $nav->findOrCreate('Content', 'Collections')
                ->route('collections.show', 'pages');

            $nav->findOrCreate('Content', 'Globals')
                ->route('globals.update', 'site_settings');
        });
    }
}
