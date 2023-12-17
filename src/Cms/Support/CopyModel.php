<?php

namespace FewFar\Stacheless\Cms\Support;

use FewFar\Stacheless\Cms\Support\Concerns\BuildsModels;
use Illuminate\Support\Arr;
use Statamic\Fields\Value;
use Statamic\Fields\Values;

class CopyModel
{
    use BuildsModels;

    public $usePlaceholder = true;
    public $useMarkdown = false;
    public $useClasses = true;
    public $classKey = null;
    public $classDefault = null;

    public static function for($block, $key)
    {
        return new static($block, $key);
    }

    public function __construct(public $block, public $key)
    {
    }

    public function usePlaceholder($enabled = true)
    {
        $this->usePlaceholder = $enabled;

        return $this;
    }

    public function useMarkdown($enabled = true)
    {
        $this->useMarkdown = $enabled;

        return $this;
    }

    public function useClasses($enabled = true, $key = null, $default = null)
    {
        $this->useClasses = $enabled;
        $this->classKey = $key;
        $this->classDefault = $default;

        return $this;
    }

    public function text()
    {
        return $this->model('text');
    }

    public function html()
    {
        return $this->model('html');
    }

    public function model($type)
    {
        $value = null;

        if ($this->block instanceof Values) {
            $value = $this->block->getProxiedInstance()->get($this->key);
        }

        else if (is_array($this->block)) {
            $value = Arr::get($this->block, $this->key);
        }

        else if (method_exists($this->block, 'get')) {
            $value = $this->block->get($this->key);
        }

        $fieldValue = null;

        if ($value instanceof Value) {
            if (!($fieldValue = ($value->value() ?: (!$this->usePlaceholder ? null : $value->fieldtype()->config('placeholder'))))) {
                return null;
            }
        } else {
            $fieldValue = $value;
        }

        if (!$value) {
            return null;
        }

        if ($type === 'html') {
            if ($this->useMarkdown) {
                $fieldValue = preg_replace('/_(.+?)_/', '<em>$1</em>', $fieldValue);
                $fieldValue = preg_replace('/\*(.+?)\*/', '<strong>$1</strong>', $fieldValue);
            }
        }

        $model = [ $type => $fieldValue ];

        if ($this->useClasses) {
            $key = $this->classKey ?? ($this->key . '_classes');
            $model['classes'] = collect($this->get($this->block, $key))
                ->pluck('value')
                ->implode(' ') ?: $this->classDefault;
        }

        return $model;
    }
}
