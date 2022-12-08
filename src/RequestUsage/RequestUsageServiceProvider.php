<?php

namespace FewFar\Stacheless\RequestUsage;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class RequestUsageServiceProvider extends ServiceProvider
{
    /**
     * Register the Service Provider.
     *
     * @return void
    */
    public function register()
    {
        $this->app->singleton(RequestUsage::class);

        $this->app->extend(RequestUsage::class, function (RequestUsage $usage) {
            Event::listen(TypeRequested::class, [$usage, 'handleEvent']);

            $this->app->terminating([$usage, 'save']);
        });
    }
}
