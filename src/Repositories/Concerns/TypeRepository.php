<?php

namespace FewFar\Stacheless\Repositories\Concerns;

use FewFar\Stacheless\Repositories\Events\TypeRequested;
use Statamic\Facades\Blink;
use Illuminate\Support\Collection;

/**
 * @template T
 */
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
     * @var class-string<T>
     */
    protected $typeClass = null;

    /**
     * Key for string instances of this Repository's Type in Blink Cache
     *
     * @param mixed  $key  Typically, key is a string, but can change for other types.
     * @return string
     */
    public function makeBlinkKey($key)
    {
        return "$this->typeKey::$key";
    }

    /**
     * Key for string instances of this Repository's Type in Blink Cache
     *
     * @param T  $type
     * @return string
     */
    public function makeBlinkKeyForType($type)
    {
        return $this->makeBlinkKey($type->id());
    }

    /**
     * Makes where query attributes for searching for this Type by key.
     *
     * @param string  $key
     * @return array
     */
    public function makeWhereArgsFromKey($key)
    {
        return [ 'handle' => $key ];
    }

    /**
     * Makes where query attributes for searching for this Type.
     *
     * @param T  $type
     * @return array
     */
    public function makeWhereArgs($type)
    {
        return [ 'handle' => $type->handle() ];
    }

    /**
     * Creates an instance of Repository‘s type based on the given model.
     *
     * The type will not be hydrated and only some Repositories will
     * semi-hydrate the type with the handle.
     *
     * @return T
     */
    public function makeType($model)
    {
        return app($this->typeClass)->handle($model->handle);
    }

    /**
     * Returns the Blink store for the this Repository.
     *
     * @return \Spatie\Blink\Blink
     */
    public function getBlinkStore()
    {
        return Blink::store($this->config->get('blink_store'));
    }

    /**
     * Gets the Eloquent Model class name for the Repository’s Type.
     *
     * @return class-string<extends \Illuminate\Database\Eloquent\Model>
     */
    protected function getModelClass()
    {
        return $this->config->get("types.$this->typeKey.model");
    }

    /**
     * Loads all types from the database for this Repository.
     *
     * Types are stored in Blink individually and the collection as a whole.
     *
     * @return \Illuminate\Support\Collection<T>
     */
    public function all(): Collection
    {
        $store = $this->getBlinkStore();
        return $store->once($this->typeKey, function () use ($store) {
            return $this->getModelClass()::all()
                ->map(fn ($model) => $this->toType($model))
                ->each(fn ($type) => $this->storeInCache($type, $store));
        });
    }

    /**
     * Convenience method to search the results of calling all() for a type
     * with the given handle.
     *
     * Not all Respositories will benefit from using this.
     *
     * @param string  $handle
     * @return null|T
     */
    public function findInAll($handle)
    {
        return $this->all()->first(function ($item) use ($handle) {
            return $item->handle() === $handle;
        });
    }

    /**
     * Finds the type by the given key using the Blink cache to see if it is
     * already loaded, if not will load from DB. Either way will fire a
     * TypeRequested event.
     *
     * @param  mixed  $key
     * @return null|T
     * @see \FewFar\Stacheless\Repositories\Events\TypeRequested
     */
    public function findWithCache($key)
    {
        if (!$key) {
            return null;
        }

        $store = $this->getBlinkStore();
        $type = $store->once($this->makeBlinkKey($key), function () use ($key) {
            return $this->findNoCache($key);
        });

        if ($type) {
            event(new TypeRequested($type));
        }

        return $type;
    }

    /**
     * Finds the type by the given key by doing a DB query.
     *
     * @param  mixed  $key
     * @return null|T
     */
    public function findNoCache($key)
    {
        if (! ($model = $this->findModel($key))) {
            return null;
        }

        return $this->toType($model);
    }

    /**
     * Loads the model from DB.
     *
     * @param  mixed  $key
     * @return null|\Illuminate\Database\Eloquent\Model
     */
    public function findModel($key)
    {
        return $this->getModelClass()::query()
            ->where($this->makeWhereArgsFromKey($key))
            ->first();
    }

    /**
     * Hydrates the Repository’s Type from the given Model.
     *
     * Each Repository must provide it’s own implementation.
     *
     * @param  T  $type
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function hydrateType($type, $model)
    {
        throw new \BadMethodCallException('Repository doesn’t provide it’s own implementation of this method.');
    }

    /**
     * Hydrates the Repository’s Model from the given Type.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  T  $type
     * @return \Illuminate\Database\Eloquent\Model  $model
     */
    public function hydrateModel($model, $type)
    {
        return $model->fill([
            'json' => $type->fileData(),
            'yaml' => $type->fileContents(),
        ]);
    }

    /**
     * Makes an instance of the Repository’s type from the given Model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return T
     */
    public function toType($model)
    {
        $type = $this->makeType($model);

        $this->hydrateType($type, $model);

        return $type;
    }

    /**
     * Convenience to store type on the Repository’s Blink cache.
     *
     * @param  T  $type
     * @param  \Spatie\Blink\Blink  $store
     * @return void
     */
    public function storeInCache($type, $store = null)
    {
        $key = $this->makeBlinkKeyForType($type);
        ($store ?? $this->getBlinkStore())->put($key, $type);
    }

    /**
     * Saves the type to the DB and stores it in the Blink cache.
     *
     * @param  T  $type
     * @return true
     */
    public function save($type)
    {
        $model = $this->getModelClass()::query()
            ->firstOrNew($this->makeWhereArgs($type));

        $this->hydrateModel($model, $type);

        $this->saveModel($model);

        $this->storeInCache($type);

        return true;
    }

    /**
     * Saves the Model to the DB.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function saveModel($model)
    {
        $model->save();
    }

    /**
     * Deletes the type in the DB and removes from Blink cache.
     *
     * @param  T  $type
     * @return true
     */
    public function delete($type)
    {
        $this->getModelClass()::query()
            ->where($this->makeWhereArgs($type))
            ->delete();

        $this->getBlinkStore()->forget($this->makeBlinkKeyForType($type));

        return true;
    }

    /**
     * Bindings to be mapped in the Laravel service container when Repository
     * is registered.
     *
     * @return array<string|class-string, class-string>
     */
    public static function bindings()
    {
        return [];
    }
}
