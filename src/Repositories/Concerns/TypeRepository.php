<?php

namespace FewFar\Stacheless\Repositories\Concerns;

use FewFar\Stacheless\Config;
use Statamic\Contracts\Structures\Tree;
use Statamic\Facades\Blink;
use Statamic\Facades\YAML;
use Statamic\Structures\NavTree;

trait TypeRepository
{
    /**
     * Instance of the package config.
     *
     * @var \FewFar\Stacheless\Config
     */
    protected $config;

    /**
     * Statamic type "slug" used for config and Blink cache?
     *
     * @var string
     */
    protected $typeKey = null;

    /**
     * Determines which Statamic type this class manages.
     *
     * @var string
     */
    protected $typeClass = null;

    protected function makeBlinkKey($key)
    {
        return "$this->typeKey::$key";
    }

    protected function makeBlinkKeyForType($type)
    {
        return $this->makeBlinkKey($type->id());
    }

    protected function makeWhereArgsFromKey($key)
    {
        return [ 'handle' => $key ];
    }

    protected function makeWhereArgs($type)
    {
        return [ 'handle' => $type->handle() ];
    }

    public function make(string $handle = null)
    {
        return app($this->typeClass)->handle($handle);
    }

    protected function getBlinkStore()
    {
        return Blink::store($this->config->get('blink_store'));
    }

    protected function getModelClass()
    {
        return $this->config->get("types.$this->typeKey.model");
    }

    public function findWithCache($key)
    {
        return $this->getBlinkStore()->once($this->makeBlinkKey($key), function () use ($key) {
            return $this->findNoCache($key);
        });
    }

    protected function findModel($key)
    {
        return $this->getModelClass()::query()
            ->where($this->makeWhereArgsFromKey($key))
            ->first();
    }

    protected function hydrateType($type, $model)
    {
    }

    protected function hydrateModel($model, $type)
    {
        return $model->fill([
            'json' => $type->fileData(),
            'yaml' => $type->fileContents(),
        ]);
    }

    public function toType($model)
    {
        $type = $this->make($model->handle);

        $this->hydrateType($type, $model);

        return $type;
    }

    public function findNoCache($key)
    {
        if (! ($model = $this->findModel($key))) {
            return null;
        }

        return $this->toType($model);
    }

    public function save($type)
    {
        $model = $this->getModelClass()::query()
            ->firstOrNew($this->makeWhereArgs($type));

        $this->hydrateModel($model, $type);

        $this->saveModel($model);

        $this->getBlinkStore()->put($this->makeBlinkKeyForType($type), $type);

        return true;
    }

    protected function saveModel($model)
    {
        $model->save();
    }

    public function delete($type)
    {
        $this->getModelClass()::query()
            ->where($this->makeWhereArgs($type))
            ->delete();

        $this->getBlinkStore()->forget($this->makeBlinkKeyForType($type));

        return true;
    }

    public static function bindings()
    {
        return [];
    }
}
