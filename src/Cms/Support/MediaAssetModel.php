<?php

namespace FewFar\Stacheless\Cms\Support;

use FewFar\Stacheless\Cms\Imaging\GeneratesCrops;
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
        $crops = app(GeneratesCrops::class)->model($this->asset);

        return [
            'image' => [
                'src' => $this->asset->url(),
                'alt' => $this->asset->get('alt'),
                'width' => $dimensions[0],
                'height' => $dimensions[1],
                'style' => $this->makeObjectPosition($this->asset),
            ] + (empty($crops) ? [] : [
                'sources' => [ 'images' => $crops ],
            ]),
        ];
    }
}
