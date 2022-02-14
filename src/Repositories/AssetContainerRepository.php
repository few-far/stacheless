<?php

namespace FewFar\Stacheless\Repositories;

use Statamic\Contracts\Assets\AssetContainerRepository as RepositoryContract;
use Statamic\Assets\AssetContainer;
use Statamic\Facades\YAML;
use Illuminate\Support\Arr;

class AssetContainerRepository extends BaseRepository implements RepositoryContract
{
    /**
     * Statamic type "slug" used for config and Blink cache?
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

    public function find($id): ?AssetContainer
    {
        return parent::findInAll($id);
    }

    public function findByHandle($id): ?AssetContainer
    {
        return parent::findInAll($id);
    }

    protected function hydrateType($type, $model)
    {
        $data = Yaml::parse($model->yaml);

        $type
            ->disk(Arr::get($data, 'disk'))
            ->title(Arr::get($data, 'title'))
            ->allowDownloading(Arr::get($data, 'allow_downloading'))
            ->allowMoving(Arr::get($data, 'allow_moving'))
            ->allowRenaming(Arr::get($data, 'allow_renaming'))
            ->allowUploads(Arr::get($data, 'allow_uploads'))
            ->createFolders(Arr::get($data, 'create_folders'))
            ->searchIndex(Arr::get($data, 'search_index'));
    }

    public function make($handle = null): AssetContainer
    {
        return app($this->typeClass)->handle($handle);
    }
}
