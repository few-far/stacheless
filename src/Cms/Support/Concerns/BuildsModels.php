<?php

namespace FewFar\Stacheless\Cms\Support\Concerns;

use FewFar\Stacheless\Cms\Support\CopyModel;
use FewFar\Stacheless\Cms\Support\MediaAssetModel;
use Illuminate\Support\Arr;
use Statamic\Assets\Asset;
use Statamic\Contracts\Data\Augmented;
use Statamic\Data\AbstractAugmented;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Fields\Value;
use Statamic\Fields\Values;
use Statamic\Imaging\ImageGenerator;

trait BuildsModels
{
    public function isAssoc(?array $arr)
    {
        if ([] === $arr) {
            return false;
        }

        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    public function getRaw($data, $key)
    {
        $value = match (true) {
            is_array($data) => Arr::get($data, $key),
            $data instanceof Values => $data->getProxiedInstance()->get($key),
            $data instanceof AbstractAugmented => $data->get($key),
            method_exists($data, 'get') => $data->get($key),
            default => throw new \Exception('Arg #0 ($data) is an unexpected type'),
        };

        if ($value instanceof Value) {
            return $value->raw();
        }

        return $value;
    }

    public function getFromData($data, $key)
    {
        if (is_array($data) || $data instanceof Values) {
            return Arr::get($data, $key);
        }

        else if ($data && ($data instanceof Augmented || method_exists($data, 'get'))) {
            return $data->get($key);
        }

        throw new \Exception('Arg #0 ($data) is an unexpected type');
    }

    public function get($data, $key)
    {
        $value = $this->getFromData($data, $key);

        if ($value instanceof Value) {
            return $value->value();
        }

        return $value;
    }

    protected function makeText($block, $key, $usePlaceholder = true)
    {
        $value = null;

        if ($block instanceof Values) {
            $value = $block->getProxiedInstance()->get($key);
        }

        else if (is_array($block)) {
            $value = Arr::get($block, $key);
        }

        else if (method_exists($block, 'get')) {
            $value = $block->get($key);
        }

        $text = null;

        if ($value instanceof Value) {
            if (!($text = ($value->value() ?: (!$usePlaceholder ? null : $value->fieldtype()->config('placeholder'))))) {
                return null;
            }
        } else {
            $text = $value;
        }

        if (!$text) {
            return null;
        }

        return compact('text');
    }

    protected function makeHtml($block, $key, $useMarkdown = false, $usePlaceholder = false, $classDefault = null)
    {
        return CopyModel::for($block, $key)
            ->useClasses(default: $classDefault)
            ->useMarkdown($useMarkdown)
            ->usePlaceholder($usePlaceholder)
            ->html();
    }

    protected function makeCta($block, $key, $type_key = null, $labelFallback = null)
    {
        // We can sometimes have a Augmented collection-like at this point.
        $type_value = $this->getFromData($block, $type_key);
        $type = $type_value instanceof Value ? $type_value->raw() : $type_value;

        return $this->mapCta($this->get($block, $key), $this->getRaw($block, $type_key), copyFallback: !$labelFallback ? null : ['text' => $labelFallback]);
    }

    protected function mapCta($cta, $type = null, $copyFallback = null)
    {
        if (!$cta || !$cta['url']) {
            return null;
        }

        $link = [ 'href' => $cta['url'] ];

        if ($cta['open_in_new_tab']) {
            $link['target'] = '_blank';
        }

        return [
            'copy' => ($text = $cta['label'])
                ? compact('text')
                : $copyFallback,
            'link' => $link,
            'type' => $type,
        ];
    }

    protected function mapAsset($asset)
    {
        if (!($asset instanceof \Statamic\Contracts\Assets\Asset)) {
            return null;
        }

        return (new MediaAssetModel($asset))->model();
    }

    protected function makeAsset($block, $key)
    {
        return $this->mapAsset($this->get($block, $key));
    }

    /**
     * Extracts the Asset from the Asset or Value as long as it is an image.
     *
     * @param mixed  $value
     * @return \Statamic\Assets\Asset
     */
    protected function toImageAsset($value)
    {
        $asset = $this->toAsset($value);

        if ($asset instanceof Asset) {
            if ($asset->isImage() || $asset->extensionIsOneOf(['svg'])) {
                return $asset;
            }
        }

        return null;
    }

    protected function toAssetUrl($value)
    {
        if (!($asset = $this->toAsset($value)) instanceof Asset) {
            return null;
        }

        return $asset->url();
    }
}
