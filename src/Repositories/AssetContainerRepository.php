<?php

namespace FewFar\Stacheless\Repositories;

use Statamic\Contracts\Assets\AssetContainerRepository as RepositoryContract;
use Statamic\Contracts\Assets\AssetContainer;
use Statamic\Facades\YAML;
use Illuminate\Support\Arr;

class AssetContainerRepository extends BaseRepository implements RepositoryContract
{
    /**
     * Statamic type "slug" used for config and Blink cache.
     *
     * @var string
     */
    protected $typeKey = 'asset_containers';

    /**
     * Determines which Statamic type this class manages.
     *
     * @var string
     */
    protected $typeClass = AssetContainer::class;

    /**
     * Finds the AssetContainer by handle.
     *
     * @param string  $id
     * @return null|\Statamic\Contracts\Assets\AssetContainer
     */
    public function find($id): ?AssetContainer
    {
        return parent::findInAll($id);
    }

    /**
     * Finds the AssetContainer by handle.
     *
     * @param string  $id
     * @return null|\Statamic\Contracts\Assets\AssetContainer
     */
    public function findByHandle($id): ?AssetContainer
    {
        return parent::findInAll($id);
    }

    /**
     * Hydrates the AssetContainer from the Stacheless model.
     *
     * @param \Statamic\Contracts\Assets\AssetContainer  $type
     * @param \FewFar\Stacheless\Database\AssetContainerModel  $model
     * @return null|\Statamic\Contracts\Assets\AssetContainer
     */
    public function hydrateType($type, $model)
    {
        $data = YAML::parse($model->yaml);

        $type
            ->disk(Arr::get($data, 'disk'))
            ->title(Arr::get($data, 'title'))
            ->allowDownloading(Arr::get($data, 'allow_downloading'))
            ->allowMoving(Arr::get($data, 'allow_moving'))
            ->allowRenaming(Arr::get($data, 'allow_renaming'))
            ->allowUploads(Arr::get($data, 'allow_uploads'))
            ->createFolders(Arr::get($data, 'create_folders'))
            ->searchIndex(Arr::get($data, 'search_index'))
            ->sortField(Arr::get($data, 'sort_by'))
            ->sortDirection(Arr::get($data, 'sort_dir'));
    }

    /**
     * Makes a new instance of a AssetContainer.
     *
     * @param null|string  $handle
     * @return \Statamic\Contracts\Assets\AssetContainer
     */
    public function make($handle = null): AssetContainer
    {
        return app($this->typeClass)->handle($handle);
    }
}
