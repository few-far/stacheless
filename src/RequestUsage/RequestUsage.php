<?php

namespace FewFar\Stacheless\RequestUsage;

use FewFar\Stacheless\Config;
use FewFar\Stacheless\Repositories\Events\TypeRequested;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Statamic\Contracts\Assets\AssetRepository;
use Statamic\Contracts\Entries\EntryRepository;

class RequestUsage implements RecordsUsage
{
    /**
     * Cached usage map from previous requests.
     *
     * @var array<string, array<string>>
     */
    protected $cached_usage = [
        'assets' => [],
        'entries' => [],
    ];

    /**
     * Current usage map from current request.
     */
    protected $usage = [
        'assets' => [],
        'entries' => [],
    ];

    /**
     * Key for this current request.
     *
     * @var string
     */
    protected $key;

    /**
     * Creates an instance of the class.
     *
     * @return void
     */
    public function __construct(
        protected Config $config,
        protected EntryRepository $entries,
        protected AssetRepository $assets,
    )
    {
        $this->config = $config;
        $this->entries = $entries;
        $this->assets = $assets;
    }

    /**
     * Empties the cache. Useful when used with Laravel Octane.
     */
    public function flush()
    {
        $this->key = null;

        $this->cached_usage = [
            'assets' => [],
            'entries' => [],
        ];

        $this->usage = [
            'assets' => [],
            'entries' => [],
        ];
    }

    /**
     * Loads usage for given key. If key doesn't exist a blank usage will be created.
     *
     * @param  string  $view
     * @return void
     */
    public function load($key)
    {
        $this->key = (string)$key;

        $this->loadUsage();
        $this->loadTypes();
    }

    /**
     * Loads cached usage map for the current assign key.
     *
     * @return void
     */
    protected function loadUsage()
    {
        $store = Cache::store($this->config->get('request_usage.store'));
        $key = $this->config->get('request_usage.cache_key_prefix') . $this->key;

        $this->cached_usage = $store->get($key);
    }

    /**
     * Stores usage map in cache for the current assign key.
     *
     * @return void
     */
    public function storeUsage($usage)
    {
        $store = Cache::store($this->config->get('request_usage.store'));
        $key = $this->config->get('request_usage.cache_key_prefix') . $this->key;

        $store->put($key, $usage);
    }

    /**
     * Loads all Types from database into Repositories' Blink caches.
     *
     * @return void
     */
    protected function loadTypes()
    {
        $this->loadEntries();
        $this->loadAssets();
    }

    /**
     * Loads Entries from database into Repositories' Blink caches.
     *
     * @return void
     */
    protected function loadEntries()
    {
        if (! $entry_ids = Arr::get($this->cached_usage, 'entries')) {
            return;
        }

        $this->entries
            ->query()
            ->whereIn('id', $entry_ids)
            ->get()
            ->each(function ($entry) {
                $this->entries->storeInCache($entry);
            });
    }

    /**
     * Loads Assets from database into Repositories' Blink caches.
     *
     * @return void
     */
    protected function loadAssets()
    {
        if (! $asset_ids = Arr::get($this->cached_usage, 'assets')) {
            return;
        }

        $this->assets
            ->query()
            ->whereIn('id', $asset_ids)
            ->get()
            ->each(function ($asset) {
                $this->assets->storeInCache($asset);
            });
    }

    /**
     * Event handler for all Stacheless TypeRequested events.
     *
     * @param  \FewFar\Stacheless\Repositories\Events\TypeRequested  $event
     * @return void
     */
    public function handleEvent(TypeRequested $event)
    {
        if ($event->type instanceof \Statamic\Contracts\Assets\Asset) {
            // In some instances, during upload, there is an Asset but it is
            // not yet in the database so no model exist.
            $this->usage['assets'][$event->type->getModel()?->id ?? ''] = $event->type;
        }

        else if ($event->type instanceof \Statamic\Contracts\Entries\Entry) {
            $this->usage['entries'][$event->type->id()] = $event->type;;
        }
    }

    /**
     * Compares the current usage and stores to database if different from cached.
     */
    public function save()
    {
        if (!$this->key) {
            return;
        }

        $sorted_usage = [
            'assets' => collect($this->usage['assets'])->keys()->filter()->sort()->all(),
            'entries' => collect($this->usage['entries'])->keys()->filter()->sort()->all(),
        ];

        if ($sorted_usage !== $this->cached_usage) {
            $this->storeUsage($sorted_usage);
        }
    }

    /**
     * Saves the current usage and clears the usage cache.
     */
    public function saveAndFlush()
    {
        $this->save();
        $this->flush();
    }
}
