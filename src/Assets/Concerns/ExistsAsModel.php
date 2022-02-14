<?php

namespace FewFar\Stacheless\Assets\Concerns;

use FewFar\Stacheless\Config;
use Illuminate\Support\Arr;
use Statamic\Facades\YAML;

trait ExistsAsModel
{
    public function exists()
    {
        if (! $this->path()) {
            return false;
        }

        if (! $this->container()) {
            return false;
        }

        if (! $this->model) {
            $this->hydrateModel();
        }

        return boolval($this->model);
    }

    protected $model;

    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    public function hydrateModel()
    {
        return $this->model = app(Config::class)->get('types.assets.model')::query()
            ->where('container', $this->containerHandle())
            ->where('path', $this->path())
            ->first();
    }

    public function meta($key = null)
    {
        if (func_num_args() === 1) {
            return $this->metaValue($key);
        }

        if ($this->meta) {
            return array_merge($this->meta, ['data' => $this->data->all()]);
        }

        return $this->meta = YAML::parse($this->model->yaml);
    }

    private function metaValue($key)
    {
        return Arr::get($this->meta(), $key);
    }

    public function setMeta($meta)
    {
        $this->meta = $meta;

        $this->data = collect($meta['data']);

        return $this;
    }

    public function metaPath()
    {
        throw new \Exception('not supported');
    }

    public function writeMeta($meta)
    {
        throw new \Exception('not supported');
    }
}
