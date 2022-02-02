<?php

namespace FewFar\Stacheless\Repositories;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection as IlluminateCollection;
use Statamic\Contracts\Taxonomies\Taxonomy as TypeContract;
use Statamic\Contracts\Taxonomies\TaxonomyRepository as RepositoryContract;
use Statamic\Facades\Site;
use Statamic\Facades\YAML;

class TaxonomyRepository extends BaseRepository implements RepositoryContract
{
    /**
     * Statamic type "slug" used for config and Blink cache?
     *
     * @var string
     */
    protected $typeKey = 'taxonomies';

    /**
     * Determines which Statamic type this class manages.
     *
     * @var string
     */
    protected $typeClass = TypeContract::class;

    protected function hydrateType($type, $model)
    {
        $data = YAML::parse($model->yaml);

        $sites = Arr::get($data, 'sites') ?? (Site::hasMultiple() ? [] : [Site::default()->handle()]);

        $type
            ->title(Arr::get($data, 'title'))
            ->cascade(Arr::get($data, 'inject', []))
            ->revisionsEnabled(Arr::get($data, 'revisions', false))
            ->searchIndex(Arr::get($data, 'search_index'))
            ->defaultPublishState((Arr::get($data, 'default_status', 'published') === 'published'))
            ->sites($sites);
    }

    public function all(): IlluminateCollection
    {
        return $this->getBlinkStore()->once($this->typeKey, function () {
            return $this->getModelClass()::all()->map(function ($model) {
                return $this->toType($model);
            });
        });
    }

    public function find($id): ?TypeContract
    {
        return $this->findByHandle($id);
    }

    public function findByHandle($handle): ?TypeContract
    {
        return $this->all()->first(function ($item) use ($handle) {
            return $item->handle() === $handle;
        });
    }

    public function findByUri(string $uri): ?TypeContract
    {
        throw new \Exception('Not implememented');
    }

    public function handles(): IlluminateCollection
    {
        return $this->all()->map->handle();
    }

    public function handleExists(string $handle): bool
    {
        return $this->handles()->contains($handle);
    }
}
