<?php

namespace FewFar\Stacheless\Cms\MonkeyPatch\Concerns;

use Statamic\Imaging\ImageGenerator;

trait HasDirectThumbnailUrl
{
    public function thumbnailUrl($preset = null)
    {
        if ($this->isSvg()) {
            return $this->svgThumbnailUrl();
        }

        if (!$this->isImage()) {
            return null;
        }

        $path = rescue(function () use ($preset) {
            return app(ImageGenerator::class)->generateByAsset(
                $this,
                ['p' => "cp_thumbnail_{$preset}_square"]
            );
        });

        return url(preg_replace('/^containers/', '', $path));
    }
}
