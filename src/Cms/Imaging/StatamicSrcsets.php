<?php

namespace FewFar\Stacheless\Cms\Imaging;

use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Statamic\Contracts\Assets\Asset;
use Statamic\Facades\Glide;
use Statamic\Imaging\ImageGenerator;

/**
 * Creates crops using Statmic's glide interface.
 */
class StatamicSrcsets implements GeneratesCrops
{
    use CanGenerateCrops;

    public function __construct(protected ImageGenerator $generator)
    {
    }

    public function crops(?Asset $asset) : Enumerable
    {
        if (!$this->canGenerateCrops($asset)) {
            return collect();
        }

        // We want to generate all the crops insteps of 250 up to the width of
        // the image. For example, an image 520px wide, would generate:
        //  - 250
        //  - 500
        //  - 520

        $max = min(2500, $asset->width());

        return collect(range(1, intval(ceil($max / 250))))
            ->map(fn ($n) => min($max, $n * 250));
    }

    public function generateCrop(?Asset $asset, $width) : ?string
    {
        if (!$this->canGenerateCrops($asset)) {
            return null;
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

    /**
     * Generates the model needed for the frontend to use the crops.
     */
    public function model(Asset $asset) : Collection
    {
        $dimensions = $asset->dimensions();
        $max_width = min(2500, $dimensions[0]);
        $breakpoint_count = intval(ceil($max_width / 250));

        /** @var \League\Glide\Server */
        $glide = app(\League\Glide\Server::class);
        $glide->setCachePathPrefix(ImageGenerator::assetCachePathPrefix($asset).'/'.$asset->folder());

        $paths = collect(range(1, $breakpoint_count))
            ->keyBy(fn ($n) => $n)
            ->map(fn ($n) => $glide->getCachePath($asset->basename(), [
                'w' => min($max_width, $n * 250),
                'fm' => 'webp',
                'fit' => '',
            ]))

            ->map(fn ($path) => preg_replace('/^containers/', '', $path));

        $images = $paths
            ->map(fn ($path, $n) => [
                'width' => min($max_width, $n * 250),
                'src' => $path,
                'srcset' => collect([1, 2])
                    ->map(fn ($x) => ($paths->get(($x * $n) ?? $paths->last()) . ' ' . $x . 'x'))
                    ->implode(', ')
            ])
            ->values();

        return $images;
    }
}
