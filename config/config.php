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
    | Blink Store Key
    |--------------------------------------------------------------------------
    |
    | Used for Spatie Blick per request cache.
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

        'global_sets' => [
            'enabled' => env('STACHELESS_GLOBAL_SETS', true),
            'repository' => \FewFar\Stacheless\Repositories\GlobalSetRepository::class,
            'model' => \FewFar\Stacheless\Database\GlobalSetModel::class,
            'variables_model' => \FewFar\Stacheless\Database\GlobalVariablesModel::class,
        ],
    ],

];
