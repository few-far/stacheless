<?php

namespace FewFar\Stacheless;

use FewFar\Stacheless\Commands;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    /**
     * Instance of scoped package config.
     *
     * @var \FewFar\Stacheless\Config
     */
    protected $config;

    /**
     * Instance of Statamic Addon
     *
     * @var \Statamic\Extend\Addon
     */
    protected $addon;

    /**
     * Commands that Statamic Addon will register
     */
    protected $commands = [
        Commands\MakeMigrationsCommand::class,
        Commands\MigrateCollectionTreesCommand::class,
        Commands\MigrateCollectionsCommand::class,
        Commands\MigrateEntriesCommand::class,
        Commands\MigrateNavigationTreesCommand::class,
        Commands\MigrateNavigationsCommand::class,
        Commands\MigrateRevisionsCommand::class,
    ];


    /**
     * Register any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    protected function bootConfig()
    {
        $origin = __DIR__.'/../config/config.php';

        $this->mergeConfigFrom($origin, $this->config->configKey());

        $this->publishes([
            $origin => config_path(str_replace('.', '/', $this->config->configKey())),
        ], "stacheless-config");

        return $this;
    }

    public function bootAddon()
    {
        $this->registerTypes();

    }

    public function register()
    {
        $this->config = $this->app->get(Config::class);
    }

    protected function registerTypes()
    {
        $this->registerType('entries');
        $this->registerType('revisions');
        $this->registerType('collections');
        $this->registerType('collection_trees');
        $this->registerType('navigations');
        $this->registerType('navigation_trees');
        $this->registerType('global_sets');
        $this->registerType('taxonomies');
        $this->registerType('terms');

    }

    protected function registerType($type)
    {
        if (! $this->config->get("types.$type.enabled")) {
            return;
        }

        $this->{"register_$type"}();
    }

    protected function register_entries()
    {
        $this->app->bind(\FewFar\Stacheless\Database\EntryQueryBuilder::class, function () {
            return new \FewFar\Stacheless\Database\EntryQueryBuilder($this->config->get('types.entries.model')::query());
        });

        Statamic::repository(
            \Statamic\Contracts\Entries\EntryRepository::class,
            \FewFar\Stacheless\Repositories\EntryRepository::class
        );
    }

    protected function register_revisions()
    {
        $this->app->singleton(
            \Statamic\Contracts\Revisions\RevisionRepository::class,
            \FewFar\Stacheless\Repositories\RevisionRepository::class
        );
    }

    protected function register_collections()
    {
        Statamic::repository(
            \Statamic\Contracts\Entries\CollectionRepository::class,
            \FewFar\Stacheless\Repositories\CollectionRepository::class
        );
    }

    protected function register_collection_trees()
    {
        Statamic::repository(
            \Statamic\Contracts\Structures\CollectionTreeRepository::class,
            \FewFar\Stacheless\Repositories\CollectionTreeRepository::class
        );
    }

    protected function register_navigations()
    {
        Statamic::repository(
            \Statamic\Contracts\Structures\NavigationRepository::class,
            \FewFar\Stacheless\Repositories\NavigationRepository::class
        );
    }

    protected function register_navigation_trees()
    {
        Statamic::repository(
            \Statamic\Contracts\Structures\NavTreeRepository::class,
            \FewFar\Stacheless\Repositories\NavigationTreeRepository::class
        );
    }

    protected function register_global_sets()
    {
        Statamic::repository(
            \Statamic\Contracts\Globals\GlobalRepository::class,
            \FewFar\Stacheless\Repositories\GlobalSetRepository::class
        );
    }
}
