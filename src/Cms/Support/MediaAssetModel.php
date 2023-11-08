<?php

namespace FewFar\Stacheless\Cms\Support;

use FewFar\Stacheless\Cms\Support\Concerns\BuildsModels;
use Illuminate\Support\Arr;
use Statamic\Contracts\Assets\Asset;
use Statamic\Imaging\ImageGenerator;

class MediaAssetModel
{
    use BuildsModels;

    public function __construct(public ?Asset $asset)
    {
    }

    protected function makeObjectPosition($asset)
    {
        $focus = Arr::get($asset->data(), 'focus');

        if (!$focus) {
            return null;
        }

        $parts = explode('-', $focus);

        return [ 'object-position' => $parts[0] . '% ' . $parts[1] . '%' ];
    }

    public function toPublicPath($path)
    {
        return preg_replace('/^containers/', '', $path);
    }

    public function defaultMediaModel()
    {
        if (!$this->asset) {
            return null;
        }

        $dimensions = $this->asset->dimensions();

        return [
            'src' => $this->asset->url(),
            'alt' => $this->asset->get('alt'),
            'width' => Arr::get($dimensions, 0),
            'height' => Arr::get($dimensions, 1),
        ];
    }

    public function model()
    {
        if (!$this->asset) {
            return null;
        }

        if ($this->asset->isVideo()) {
            return [ 'video' => $this->defaultMediaModel() ];
        }

        else if ($this->asset->isSvg()) {
            return [ 'image' => $this->defaultMediaModel() ];
        }

        else if (!$this->asset->isImage()) {
            return null;
        }

        $dimensions = $this->asset->dimensions();

        $max_width = min(2500, $dimensions[0]);
        $breakpoint_count = intval(ceil($max_width / 250));

        /** @var \League\Glide\Server */
        $glide = app(\League\Glide\Server::class);
        $glide->setCachePathPrefix(ImageGenerator::assetCachePathPrefix($this->asset).'/'.$this->asset->folder());

        $paths = collect(range(1, $breakpoint_count))
            ->keyBy(fn ($n) => $n)
            ->map(fn ($n) => $glide->getCachePath($this->asset->basename(), [
                'w' => min($max_width, $n * 250),
                'fm' => 'webp',
                'fit' => '',
                // 'q' => 100,
            ]))

            ->map(fn ($path) => $this->toPublicPath($path));

        $images = $paths
            ->map(fn ($path, $n) => [
                'width' => min($max_width, $n * 250),
                'src' => $path,
                'srcset' => collect([1, 2])
                    ->map(fn ($x) => ($paths->get(($x * $n) ?? $paths->last()) . ' ' . $x . 'x'))
                    ->implode(', ')
            ])
            ->values();

        return [
            'image' => [
                'sources' => [
                    'step' => 250,
                    'images' => $images,
                ],
                'src' => $this->asset->url(),
                'alt' => $this->asset->get('alt'),
                'width' => $dimensions[0],
                'height' => $dimensions[1],
                'style' => $this->makeObjectPosition($this->asset),
            ],
        ];
    }
}
