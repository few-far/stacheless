<?php

namespace FewFar\Stacheless\Cms\Imaging;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Statamic\Contracts\Assets\Asset;
use Statamic\Contracts\Assets\AssetRepository;

class GenerateImageSrcsetsJob implements ShouldQueue
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
     * Processing strategy for image generation.
     */
    protected ?string $strategy = null;

    public const STRATEGY_ONE_AT_A_TIME = 'single';
    public const STRATEGY_ALL_AT_ONCE = 'all';

    /**
     * Creates an instance of the job.
     */
    public function __construct(Asset $asset, string $strategy = null)
    {
        $this->handle = $asset->id();
        $this->strategy = $strategy ?: static::STRATEGY_ONE_AT_A_TIME;

        $this->onConnection(config('domain.imaging.queue'));
    }

    /**
     * Handle the events.
     */
    public function handle(AssetRepository $repository, GeneratesCrops $generator)
    {
        /** @var \Statamic\Assets\Asset */
        $asset = $repository->findNoCache($this->handle);

        if (!$generator->canGenerateCrops($asset)) {
            return;
        }

        if ($this->strategy === static::STRATEGY_ALL_AT_ONCE) {
            $generator->generateCrops($asset);
        } else {
            foreach ($generator->crops($asset) as $width) {
                GenerateImageSrcsetsCropJob::dispatch($asset, $width);
            }
        }

    }
}
