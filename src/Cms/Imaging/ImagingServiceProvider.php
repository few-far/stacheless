<?php

namespace FewFar\Stacheless\Cms\Imaging;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;

class ImagingServiceProvider extends EventServiceProvider
{
    /**
     * Drivers to use for image crop generation.
     */
    protected $default = 'statamic';

    /**
     * Drivers to use for image crop generation.
     */
    protected $drivers = [
        'imagick' => ImagickSrcsets::class,
        'statamic' => StatamicSrcsets::class,
    ];

    /**
     * The subscribers to register.
     *
     * @var array
     */
    protected $subscribe = [
        GenerateImageSrcsetsSubscriber::class,
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        parent::register();

        $this->registerGeneratesCrops();
    }

    /**
     * Register instance of GeneratesCrops interface. vbb
     *
     * @return void
     */
    public function registerGeneratesCrops()
    {
        $driver = config('domain.imaging.driver') ?: $this->default;
        $cropper = $this->drivers[$driver] ?? throw new \Exception('Driver not supported by Stacheless imaging: ' . $driver);

        $this->app->bind(GeneratesCrops::class, $cropper);
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
