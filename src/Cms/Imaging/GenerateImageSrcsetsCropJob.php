<?php

namespace FewFar\Stacheless\Cms\Imaging;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Statamic\Contracts\Assets\Asset;
use Statamic\Contracts\Assets\AssetRepository;

class GenerateImageSrcsetsCropJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * Handle for asset in format container::asset/path.jpg
     */
    protected string $handle;

    /**
     * Width to crop the image to.
     */
    protected string $width;

    /**
     * Creates an instance of the job.
     */
    public function __construct(Asset $asset, $width)
    {
        $this->handle = $asset->id();
        $this->width = $width;

        $this->onConnection(config('domain.imaging.queue'));
    }

    /**
     * Handle the events.
     */
    public function handle(AssetRepository $repository, GenerateImageSrcsets $generator)
    {
        /** @var \Statamic\Assets\Asset */
        $asset = $repository->findNoCache($this->handle);
        $generator->generateCrop($asset, $this->width);
    }
}
