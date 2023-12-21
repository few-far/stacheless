<?php

namespace FewFar\Stacheless\Cms\Imaging;

use Illuminate\Events\Dispatcher;
use Statamic\Contracts\Assets\AssetRepository;
use Statamic\Events\AssetReuploaded;
use Statamic\Events\AssetSaved;
use Statamic\Events\AssetUploaded;

class GenerateImageSrcsetsSubscriber
{
    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen(AssetReuploaded::class, [self::class, 'handle']);
        $events->listen(AssetUploaded::class, [self::class, 'handle']);
        $events->listen(AssetSaved::class, [self::class, 'handle']);
    }

    /**
     * Handle the events.
     */
    public function handle($event)
    {
        // We must load it from the DB incase this is the upload event.
        // The asset doesn't work as expected when it's been created from
        // a UploadedFile object. Weird stache quirk as usual.

        /** @var \Statamic\Assets\Asset */
        $asset = app(AssetRepository::class)->findNoCache($event->asset->id());

        GenerateImageSrcsetsJob::dispatchIf(app(GeneratesCrops::class)->canGenerateCrops($asset), $asset);
    }
}
