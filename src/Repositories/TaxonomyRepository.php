<?php

namespace FewFar\Stacheless\Repositories;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection as IlluminateCollection;
use Statamic\Contracts\Taxonomies\Taxonomy as TypeContract;
use Statamic\Contracts\Taxonomies\TaxonomyRepository as RepositoryContract;
use Statamic\Exceptions\EntryNotFoundException;
use Statamic\Facades\Site;
use Statamic\Facades\YAML;

class TaxonomyRepository extends BaseRepository implements RepositoryContract
{
    protected $additionalPreviewTargets = [];

    /**
     * Statamic type "slug" used for config and Blink cache.
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

    public function hydrateType($type, $model)
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

    public function make(string $handle = null): TypeContract
    {
        return app($this->typeClass)->handle($handle);
    }

    public function find($id): ?TypeContract
    {
        return $this->findByHandle($id);
    }

    public function findOrFail($id): TypeContract
    {
        return $this->find($id) ?? new EntryNotFoundException($id);
    }

    public function findByHandle($handle): ?TypeContract
    {
        return $this->findInAll($handle);
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

    public function addPreviewTargets($handle, $targets)
    {
        $targets = collect($this->additionalPreviewTargets[$handle] ?? [])
            ->merge($targets)
            ->unique(function ($target) {
                return $target['format'];
            })->all();

        $this->additionalPreviewTargets = array_merge($this->additionalPreviewTargets, [$handle => $targets]);
    }

    public function additionalPreviewTargets($handle)
    {
        return collect($this->additionalPreviewTargets[$handle] ?? []);
    }
}
