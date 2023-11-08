<?php

namespace FewFar\Stacheless\Cms\MonkeyPatch\Fieldtypes;

use Statamic\Fieldtypes\Assets\Assets as Fieldtype;

class Assets extends Fieldtype
{
    public function augment($value)
    {
        $assets = $this->getAssetsForAugmentation($value);

        return $this->config('max_files') === 1 ? $assets->first() : $assets;
    }

    public function shallowAugment($value)
    {
        $assets = $this->getAssetsForAugmentation($value)->map->toShallowAugmentedCollection();

        return $this->config('max_files') === 1 ? $assets->first() : $assets;
    }

    protected function getAssetsForAugmentation($value)
    {
        return collect($value)->map(function ($path) {
            return $this->container()->asset($path);
        })->filter()->values();
    }
}
