<?php

namespace FewFar\Stacheless\RequestUsage;

use FewFar\Stacheless\Repositories\Events\TypeRequested;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\ServiceProvider;
use WeakMap;

class RequestUsageServiceProvider extends ServiceProvider
{
    /**
     * Independant request cache to allow support in Octane.
     *
     * @var WeakMap<\Illuminate\Http\Request, \FewFar\Stacheless\RequestUsage\RequestUsage>
     */
    protected WeakMap $lookup;

    /**
     * Register the Service Provider.
     */
    public function register()
    {
        $this->app->bind(RecordsUsage::class, function ($app) {
            return $this->lookup[$app['request']] ??= $app->make(RequestUsage::class);
        });

        $this->app['events']->listen(TypeRequested::class, [RecordsUsage::class, 'handleEvent']);
        $this->app['events']->listen(RequestHandled::class, [RecordsUsage::class, 'save']);
    }

    /**
     * Boot the Service Provider
     */
    public function boot()
    {
        $this->lookup = new WeakMap();
    }
}
