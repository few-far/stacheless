<?php

namespace FewFar\Stacheless\Cms\Imaging;

use Illuminate\Support\Enumerable;
use Statamic\Contracts\Assets\Asset;

trait CanGenerateCrops
{
    public function canGenerateCrops(?Asset $asset) : bool
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

        if (collect($asset->dimensions())->some(fn ($px) => $px > 6000)) {
            return false;
        }

        return true;
    }

    public function generateCrops(?Asset $asset) : Enumerable
    {
        if (!$this->canGenerateCrops($asset)) {
            return collect();
        }

        return collect($this->crops($asset))
            ->map(fn ($width) => $this->generateCrop($asset, $width))
            ->filter()
            ->values();
    }
}
