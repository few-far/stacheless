<?php

namespace FewFar\Stacheless\RequestUsage;

use FewFar\Stacheless\Config;
use FewFar\Stacheless\Repositories\Events\TypeRequested;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Statamic\Contracts\Assets\AssetRepository;
use Statamic\Contracts\Entries\EntryRepository;

interface RecordsUsage
{
    /**
     * Empties the cache. Useful when used with Laravel Octane.
     */
    public function flush();

    /**
     * Loads usage for given key. If key doesn't exist a blank usage will be created.
     *
     * @param  string  $view
     */
    public function load($key);

    /**
     * Stores usage map in cache for the current assign key.
     *
     * @return void
     */
    public function storeUsage($usage);

    /**
     * Event handler for all Stacheless TypeRequested events.
     *
     * @param  \FewFar\Stacheless\Repositories\Events\TypeRequested  $event
     * @return void
     */
    public function handleEvent(TypeRequested $event);

    /**
     * Compares the current usage and stores to database if different from cached.
     */
    public function save();

    /**
     * Saves the current usage and clears the usage cache.
     */
    public function saveAndFlush();
}
