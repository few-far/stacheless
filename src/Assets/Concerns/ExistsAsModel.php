<?php

namespace FewFar\Stacheless\Assets\Concerns;

use FewFar\Stacheless\Config;
use Illuminate\Support\Arr;
use Statamic\Facades\Asset;
use Statamic\Facades\YAML;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait ExistsAsModel
{
    protected $isBeingUploaded = false;

    public function exists()
    {
        if (! $this->path()) {
            return false;
        }

        if (! $this->container()) {
            return false;
        }

        if ($this->isBeingUploaded) {
            return true;
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

    public function getModel()
    {
        return $this->model;
    }

    public function hydrateModel()
    {
        if ($this->model) {
            return;
        }

        $asset = Asset::find($this->containerHandle() . '::' . $this->path());

        $this->model = ($asset->model ?? null);
    }

    public function hydrate()
    {
        $this->hydrateModel();

        if ($this->model) {
            $this->setMeta([
                'last_updated' => $this->model->updated_at->timestamp,
            ] + (YAML::parse($this->model->yaml) ?? []));
        }

        return $this;
    }

    public function get($key, $fallback = null)
    {
        return $this->traitGet($key, $fallback);
    }

    public function set($key, $value)
    {
        return $this->traitSet($key, $value);
    }

    public function remove($key)
    {
        return $this->traitRemove($key);
    }

    public function data($data = null)
    {
        return call_user_func_array([$this, 'traitData'], func_get_args());
    }

    public function meta($key = null)
    {
        if (func_num_args() === 1) {
            return $this->metaValue($key);
        }

        if (! $this->meta) {
            return null;
        }

        return array_merge($this->meta, ['data' => $this->data->all()]);
    }

    protected function metaValue($key)
    {
        return Arr::get($this->meta(), $key);
    }

    public function setMeta($meta)
    {
        $this->meta = $meta ?? [];

        $this->data = collect(Arr::get($meta, 'data'));

        return $this;
    }

    public function writeMeta($meta)
    {
        $this->setMeta($meta);

        parent::save();
    }

    /**
     * Upload a file.
     *
     * @param  \Symfony\Component\HttpFoundation\File\UploadedFile  $file
     * @return void
     */
    public function upload(UploadedFile $file)
    {
        // Statamic works by deferred writing of the meta file when the
        // meta data is first request. We've changed the semantics where
        // if the Asset is in the DB, then we consider it as existing.
        // To get around this on first save, we set this flag which will
        // allow meta() to generate the first time.
        $this->isBeingUploaded = true;

        return parent::upload($file);
    }

    public function save()
    {
        if ($this->isBeingUploaded) {
            $this->setMeta($this->generateMeta());
        }

        return parent::save();
    }
}
