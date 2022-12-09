<?php

namespace FewFar\Stacheless\Repositories;

use FewFar\Stacheless\Config;
use Statamic\Contracts\Assets\Asset;
use Statamic\Contracts\Assets\AssetRepository as RepositoryContract;
use Statamic\Contracts\Assets\QueryBuilder;
use Statamic\Facades\AssetContainer;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Statamic\Assets\AssetCollection;
use Statamic\Facades\YAML;

class AssetRepository extends BaseRepository implements RepositoryContract
{
    /**
     * Statamic type "slug" used for config and Blink cache
     *
     * @var string
     */
    protected $typeKey = 'assets';

    /**
     * Makes where query attributes for searching for this Type by key.
     *
     * @param string  $key
     * @return array
     */
    public function makeWhereArgsFromKey($key)
    {
        [$container, $path] = explode('::', $key, 2);

        return compact('container', 'path');
    }

    /**
     * Makes where query attributes for searching for this Type.
     *
     * @param T  $type
     * @return array
     */
    public function makeWhereArgs($type)
    {
        return [
            'container' => $type->containerHandle(),
            'path' => $type->getOriginal('path'),
        ];
    }

    /**
     * Hydrates the Repository’s Type from the given Model.
     *
     * @param  T  $type
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function hydrateType($type, $model)
    {
        $type
            ->setModel($model)
            ->path($model->path)
            ->container(AssetContainer::findByHandle($model->container))
            ->hydrate()
            ->syncOriginal();
    }

    /**
     * Hydrates the Repository’s Model from the given Type.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  T  $type
     * @return void
     */
    public function hydrateModel($model, $type)
    {
        $model->fill([
            'container' => $type->containerHandle(),
            'path' => $type->path(),
            'folder' => $type->folder(),
            'json' => $meta = $type->meta(),
            'yaml' => YAML::dump($meta),
        ]);
    }

    /**
     * Loads all types from the database for this Repository.
     *
     * Types are stored in Blink individually and the collection as a whole.
     *
     * @return \Statamic\Assets\AssetCollection<T>
     */
    public function all(): Collection
    {
        return AssetCollection::make(parent::all());
    }

    /**
     * Finds all assets in the given container.
     *
     * @param  string  $container
     * @return \Statamic\Assets\AssetCollection<T>
     */
    public function whereContainer(string $container)
    {
        return AssetContainer::find($container)->assets();
    }

    /**
     * Finds all assets in the given folder and container.
     *
     * @param  string  $folder
     * @param  string  $container
     * @return \Statamic\Assets\AssetCollection<T>
     */
    public function whereFolder(string $folder, string $container)
    {
        return AssetContainer::find($container)->assets($folder);
    }

    public function find(string $asset)
    {
        if (!Str::contains($asset, '::')) {
            throw new \Exception('not supported');
        }

        return $this->findWithCache($asset);
    }

    // public function findByUrl(string $url)
    // {
    //     // If a container can't be resolved, we'll assume there's no asset.
    //     if (! $container = $this->resolveContainerFromUrl($url)) {
    //         return null;
    //     }

    //     $siteUrl = rtrim(Site::current()->absoluteUrl(), '/');
    //     $containerUrl = $container->url();

    //     if (starts_with($containerUrl, '/')) {
    //         $containerUrl = $siteUrl.$containerUrl;
    //     }

    //     if (starts_with($containerUrl, $siteUrl)) {
    //         $url = $siteUrl.$url;
    //     }

    //     $path = str_after($url, $containerUrl);

    //     return $container->asset($path);
    // }

    // protected function resolveContainerFromUrl($url)
    // {
    //     return AssetContainer::all()
    //         ->sortByDesc(function ($container) {
    //             return strlen($container->url());
    //         })
    //         ->first(function ($container, $id) use ($url) {
    //             return starts_with($url, $container->url())
    //                 || starts_with(URL::makeAbsolute($url), $container->url());
    //         });
    // }

    public function findByUrl(string $url)
    {
        throw new \Exception('not supported');
    }

    public function whereUrl($url)
    {
        return $this->findByUrl($url); // TODO: Replace usages with findByUrl
    }

    public function findById(string $id)
    {
        return $this->findWithCache($id);
    }

    public function whereId($id)
    {
        return $this->findById($id); // TODO: Replace usages with findById
    }

    public function findByPath(string $path)
    {
        return $this->query()
            ->where('path', $path)
            ->first();
    }

    public function wherePath($path)
    {
        return $this->findByPath($path);
    }

    public function make()
    {
        return app(Asset::class);
    }

    public function makeType($model)
    {
        return $this->make();
    }

    public function query()
    {
        return app(QueryBuilder::class);
    }

    public function modelQuery()
    {
        return $this->config->get('types.assets.model')::query();
    }

    public function saveModel($model)
    {
        if (!$model->exists) {
            $model->id = (string) Str::uuid();
        }

        $model->save();
    }

    public static function bindings(): array
    {
        $config = app(Config::class);

        return [
            Asset::class => $config->get('types.assets.asset'),
            QueryBuilder::class => $config->get('types.assets.query'),
        ];
    }
}
