<?php

namespace FewFar\Stacheless\Cms\Imaging;

use Statamic\Contracts\Assets\Asset;
use Statamic\Facades\Glide;
use Statamic\Imaging\ImageGenerator;

class GenerateImageSrcsets
{
    /**
     * Create an instance of the subscriber.
     */
    public function __construct(protected ImageGenerator $generator)
    {
    }

    /**
     * Checks that Asset is a bitmap image.
     */
    public function canGenerateCrops(?Asset $asset)
    {
        if (!$asset) {
            return false;
        }

        if (! $asset->isImage()) {
            return false;
        }

        if ($asset->extensionIsOneOf(['svg', 'gif'])) {
            return false;
        }

        return true;
    }

    /**
     * Generates image crops for the frontend Image.vue class.
     */
    public function crops(?Asset $asset)
    {
        if (!$this->canGenerateCrops($asset)) {
            return [];
        }

        // We want to generate all the crops insteps of 250 up to the width of
        // the image. For example, an image 520px wide, would generate:
        //  - 250
        //  - 500
        //  - 520

        $max = min(2500, $asset->width());

        return collect(range(1, intval(ceil($max / 250))))
            ->map(fn ($n) => min($max, $n * 250))
            ->all();
    }

    /**
     * Generates image crops for the frontend Image.vue class.
     */
    public function generateCrops(?Asset $asset)
    {
        if (!$this->canGenerateCrops($asset)) {
            return;
        }

        return collect($this->crops($asset))
            ->map(fn ($width) => $this->generateCrop($asset, $width));
    }

    /**
     * Generates image crops for the frontend Image.vue class.
     */
    public function generateCrop(?Asset $asset, $width)
    {
        if (!$this->canGenerateCrops($asset)) {
            return;
        }

        $params = [
            'w' => intval($width),
            'fm' => 'webp',
            'fit' => '',
        ];

        /** @see \Statamic\Imaging\ImageGenerator::generateByAsset */
        $manipulationCacheKey = 'asset::'.$asset->id().'::'.md5(json_encode($params));

        Glide::cacheStore()->forget($manipulationCacheKey);

        logger('Image Cropping: ' . $width . ' for ' . $asset->url());
        $path = $this->generator->generateByAsset($asset, $params);
        logger('Image Cropping: generated ' . $path);

        return $path;
    }
}
