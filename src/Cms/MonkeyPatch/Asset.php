<?php

namespace FewFar\Stacheless\Cms\MonkeyPatch;

use FewFar\Stacheless\Assets\Asset as StachelessAsset;

class Asset extends StachelessAsset
{
    use Concerns\HasDirectThumbnailUrl;

    // public function meta($key = null)
    // {
    //     if (func_num_args() === 1) {
    //         return $this->metaValue($key);
    //     }

    //     if (! $this->meta) {
    //         if (!$this->model) {
    //             return $this->generateMeta();
    //         }

    //         $this->setMeta(YAML::parse($this->model->yaml));
    //     }

    //     return array_merge($this->meta, ['data' => $this->data->all()]);
    // }

    // public function warmPresets()
    // {
    //     return [];
    // }
}
