<?php

namespace FewFar\Stacheless;

use FewFar\Stacheless\Cms\CmsServiceProvider;
use FewFar\Stacheless\Commands;
use FewFar\Stacheless\RequestUsage\RequestUsageServiceProvider;
use Illuminate\Support\Str;
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
     * Commands that Statamic Addon will register
     *
     * @var array<class-string<\Illuminate\Console\Command>>
     */
    protected $commands = [
        Commands\MakeCpPublicVendorSymlinkCommand::class,

        Commands\MakeMigrationsCommand::class,

        Commands\MigrateAssetContainersCommand::class,
        Commands\MigrateAssetsCommand::class,
        Commands\MigrateCollectionTreesCommand::class,
        Commands\MigrateCollectionsCommand::class,
        Commands\MigrateEntriesCommand::class,
        Commands\MigrateGlobalSetsCommand::class,
        Commands\MigrateNavigationTreesCommand::class,
        Commands\MigrateNavigationsCommand::class,
        Commands\MigrateRevisionsCommand::class,
        Commands\MigrateTaxonomiesCommand::class,
        Commands\MigrateTermsCommand::class,
        Commands\MigrateUsersCommand::class,
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        $this->app->booted(function () {
            $addon = !app()->environment('testing') ? $this->getAddon() : new class {
                public function name() { return 'stacheless-testing'; }
                public function version() { return 'testing'; }
            };

            $name = Str::lower($addon->name());
            $version = $addon->version();

            // Use "id" in query parameter to prevent statamic caching the url.
            Statamic::script($name, "cp.js?id=".md5($version));
        });
    }

    protected function bootConfig()
    {
        $origin = __DIR__.'/../config/config.php';

        $this->mergeConfigFrom($origin, $this->config->configKey());

        if ($this->app->runningInConsole()) {
            $this->publishes([
                $origin => config_path(str_replace('.', '/', $this->config->configKey())),
            ], "stacheless-config");
        }

        return $this;
    }

    public function bootAddon()
    {
        $this->registerTypes();

    }

    public function register()
    {
        $this->config = $this->app->get(Config::class);

        if (!$this->config->get('cms_disabled')) {
            $this->app->register(CmsServiceProvider::class);
        }

        $this->app->register(RequestUsageServiceProvider::class);
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
        $this->registerType('assets');
        $this->registerType('asset_containers');
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
        $this->app->when(\FewFar\Stacheless\Database\EntryQueryBuilder::class)
            ->needs(\Illuminate\Database\Eloquent\Builder::class)
            ->give(function () {
                return $this->config->get('types.entries.model')::query();
            });

        Statamic::repository(
            \Statamic\Contracts\Entries\EntryRepository::class,
            $this->config->get('types.entries.repository')
        );
    }

    protected function register_revisions()
    {
        $this->app->singleton(
            \Statamic\Contracts\Revisions\RevisionRepository::class,
            $this->config->get('types.revisions.repository')
        );
    }

    protected function register_collections()
    {
        Statamic::repository(
            \Statamic\Contracts\Entries\CollectionRepository::class,
            $this->config->get('types.collections.repository')
        );
    }

    protected function register_collection_trees()
    {
        Statamic::repository(
            \Statamic\Contracts\Structures\CollectionTreeRepository::class,
            $this->config->get('types.collection_trees.repository'),
        );
    }

    protected function register_navigations()
    {
        Statamic::repository(
            \Statamic\Contracts\Structures\NavigationRepository::class,
            $this->config->get('types.navigations.repository')
        );
    }

    protected function register_navigation_trees()
    {
        Statamic::repository(
            \Statamic\Contracts\Structures\NavTreeRepository::class,
            $this->config->get('types.navigation_trees.repository')
        );
    }

    protected function register_global_sets()
    {
        Statamic::repository(
            \Statamic\Contracts\Globals\GlobalRepository::class,
            $this->config->get('types.global_sets.repository')
        );

        Statamic::repository(
            \Statamic\Contracts\Globals\GlobalVariablesRepository::class,
            $this->config->get('types.global_sets.variables_repository')
        );
    }

    protected function register_taxonomies()
    {
        Statamic::repository(
            \Statamic\Contracts\Taxonomies\TaxonomyRepository::class,
            $this->config->get('types.taxonomies.repository')
        );
    }

    protected function register_terms()
    {
        Statamic::repository(
            \Statamic\Contracts\Taxonomies\TermRepository::class,
            $this->config->get('types.terms.repository')
        );
    }

    protected function register_asset_containers()
    {
        Statamic::repository(
            \Statamic\Contracts\Assets\AssetContainerRepository::class,
            $this->config->get('types.asset_containers.repository')
        );
    }

    protected function register_assets()
    {
        $this->app->when(\FewFar\Stacheless\Database\AssetQueryBuilder::class)
            ->needs(\Illuminate\Database\Eloquent\Builder::class)
            ->give(function () {
                return $this->config->get('types.assets.model')::query();
            });

        Statamic::repository(
            \Statamic\Contracts\Assets\AssetRepository::class,
            $this->config->get('types.assets.repository')
        );
    }
}
