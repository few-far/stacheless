<?php

namespace FewFar\Stacheless\Cms\Imaging;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class ImagingServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Event::subscribe(GenerateImageSrcsetsSubscriber::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        GenerateImageSrcsetsAction::register();
    }
}
