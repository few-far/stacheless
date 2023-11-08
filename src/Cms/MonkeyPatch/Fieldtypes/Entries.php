<?php

namespace FewFar\Stacheless\Cms\MonkeyPatch\Fieldtypes;

use Statamic\Contracts\Data\Localization;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Fieldtypes\Entries as Fieldtype;

class Entries extends Fieldtype
{
    public function augment($values)
    {
        $values = $this->collect($values)->map(function ($value) {
            return $this->augmentValue($value);
        })->filter()->values();

        return $this->config('max_items') === 1 ? $values->first() : $values;
    }

    public function shallowAugment($values)
    {
        $values = collect($values)->map(function ($value) {
            return $this->augmentValue($value);
        });

        $values = $values->filter()->map(function ($value) {
            return $this->shallowAugmentValue($value);
        });

        return $this->config('max_items') === 1 ? $values->first() : $values;
    }

    protected function augmentValue($value)
    {
        if (! is_object($value)) {
            $value = Entry::find($value);
        }

        if ($value != null && $parent = $this->field()->parent()) {
            $site = $parent instanceof Localization ? $parent->locale() : Site::current()->handle();
            $value = $value->in($site);
        }

        return ($value && $value->status() === 'published') ? $value : null;
    }

    protected function shallowAugmentValue($value)
    {
        return $value->toShallowAugmentedCollection();
    }

}
