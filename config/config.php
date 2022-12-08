<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Blink Store Key
    |--------------------------------------------------------------------------
    |
    | Used for Spatie Blick per request cache.
    |
    */
    'blink_store' => 'stacheless',

    /*
    |--------------------------------------------------------------------------
    | Statamic Type Table Prefixes
    |--------------------------------------------------------------------------
    |
    | Prefix used for Eloquent models of Statamic Types.
    |
    */
    'table_prefix' => 'statamic_',

    /*
    |--------------------------------------------------------------------------
    | Statamic Type Overrides
    |--------------------------------------------------------------------------
    |
    | We provide repositories and Eloquent models to run statamic via the DB.
    | These can be subclassed and switched out here as well as turning them off
    | completely with the enabled flag. Disabled types will use Statamic's
    | Stache drivers.
    |
    */
    'types' => [
        'entries' => [
            'enabled' => env('STACHELESS_ENTRIES', true),
            'repository' => \FewFar\Stacheless\Repositories\EntryRepository::class,
            'model' => \FewFar\Stacheless\Database\EntryModel::class,
        ],

        'revisions' => [
            'enabled' => env('STACHELESS_REVISIONS', true),
            'repository' => \FewFar\Stacheless\Repositories\RevisionRepository::class,
            'model' => \FewFar\Stacheless\Database\RevisionModel::class,
        ],

        'collections' => [
            'enabled' => env('STACHELESS_COLLECTIONS', true),
            'repository' => \FewFar\Stacheless\Repositories\CollectionRepository::class,
            'model' => \FewFar\Stacheless\Database\CollectionModel::class,
        ],

        'collection_trees' => [
            'enabled' => env('STACHELESS_COLLECTION_TREES', true),
            'repository' => \FewFar\Stacheless\Repositories\CollectionTreeRepository::class,
            'model' => \FewFar\Stacheless\Database\CollectionTreeModel::class,
        ],

        'navigations' => [
            'enabled' => env('STACHELESS_NAVIGATIONS', true),
            'repository' => \FewFar\Stacheless\Repositories\NavigationRepository::class,
            'model' => \FewFar\Stacheless\Database\NavigationModel::class,
        ],

        'navigation_trees' => [
            'enabled' => env('STACHELESS_NAVIGATION_TREES', true),
            'repository' => \FewFar\Stacheless\Repositories\NavigationTreeRepository::class,
            'model' => \FewFar\Stacheless\Database\NavigationTreeModel::class,
        ],

        'global_sets' => [
            'enabled' => env('STACHELESS_GLOBAL_SETS', true),
            'repository' => \FewFar\Stacheless\Repositories\GlobalSetRepository::class,
            'model' => \FewFar\Stacheless\Database\GlobalSetModel::class,
            'variables_model' => \FewFar\Stacheless\Database\GlobalVariablesModel::class,
        ],

        'taxonomies' => [
            'enabled' => env('STACHELESS_TAXONOMIES', true),
            'repository' => \FewFar\Stacheless\Repositories\TaxonomyRepository::class,
            'model' => \FewFar\Stacheless\Database\TaxonomyModel::class,
        ],

        'terms' => [
            'enabled' => env('STACHELESS_TERMS', true),
            'repository' => \FewFar\Stacheless\Repositories\TermRepository::class,
            'model' => \FewFar\Stacheless\Database\TermModel::class,
        ],

        'asset_containers' => [
            'enabled' => env('STACHELESS_ASSET_CONTAINERS', true),
            'repository' => \FewFar\Stacheless\Repositories\AssetContainerRepository::class,
            'model' => \FewFar\Stacheless\Database\AssetContainerModel::class,
        ],

        'assets' => [
            'enabled' => env('STACHELESS_ASSETS', true),
            'repository' => \FewFar\Stacheless\Repositories\AssetRepository::class,
            'model' => \FewFar\Stacheless\Database\AssetModel::class,
            'query' => \FewFar\Stacheless\Database\AssetQueryBuilder::class,
            'asset' => \FewFar\Stacheless\Assets\Asset::class,
        ],
    ],


    /*
    |--------------------------------------------------------------------------
    | Request Usage
    |--------------------------------------------------------------------------
    |
    | Config to manage settings when using the Request Usage caching system.
    |
    */
    'request_usage' => [
        'cache_store' => env('STACHELESS_REQUEST_USAGE_CACHE_STORE', null),
        'cache_key_prefix' => env('STACHELESS_REQUEST_USAGE_CACHE_KEY_PREFIX', 'request_usage::'),
    ],

];
