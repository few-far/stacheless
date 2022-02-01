<?php

namespace FewFar\Stacheless\Repositories;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection as IlluminateCollection;
use Statamic\Contracts\Entries\Collection as TypeContract;
use Statamic\Contracts\Entries\CollectionRepository as RepositoryContract;
use Statamic\Entries\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Facades\YAML;

class CollectionRepository extends BaseRepository implements RepositoryContract
{
    /**
     * Statamic type "slug" used for config and Blink cache?
     *
     * @var string
     */
    protected $typeKey = 'collections';

    /**
     * Determines which Statamic type this class manages.
     *
     * @var string
     */
    protected $typeClass = TypeContract::class;

    protected function hydrateType($type, $model)
    {
        $data = YAML::parse($model->yaml);

        $type
            ->title(Arr::get($data, 'title'))
            ->routes(Arr::get($data, 'route'))
            ->mount(Arr::get($data, 'mount'))
            ->dated(Arr::get($data, 'date', false))
            ->ampable(Arr::get($data, 'amp', false))
            ->sites(Arr::get($data, 'sites') ?? (Site::hasMultiple() ? [] : [Site::default()->handle()]))
            ->template(Arr::get($data, 'template'))
            ->layout(Arr::get($data, 'layout'))
            ->cascade(Arr::get($data, 'inject', []))
            ->searchIndex(Arr::get($data, 'search_index'))
            ->revisionsEnabled(Arr::get($data, 'revisions', false))
            ->defaultPublishState(Arr::get($data, 'published') === 'published' ? 'published' : 'draft')
            ->structureContents(Arr::get($data, 'structure'))
            ->sortField(Arr::get($data, 'sort_by'))
            ->sortDirection(Arr::get($data, 'sort_dir'))
            ->taxonomies(Arr::get($data, 'taxonomies'));

        if ($dateBehavior = array_get($data, 'date_behavior')) {
            $type
                ->futureDateBehavior($dateBehavior['future'] ?? null)
                ->pastDateBehavior($dateBehavior['past'] ?? null);
        }
    }

    public function make(string $handle = null): TypeContract
    {
        return parent::make($handle);
    }

    public function all(): IlluminateCollection
    {
        return $this->getBlinkStore()->once('collections', function () {
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

    public function findByMount($mount): ?TypeContract
    {
        if (! $mount->reference()) {
            return null;
        }

        return $this->all()->first(function ($collection) use ($mount) {
            return optional($collection->mount())->id() === $mount->id();
        });
    }

    public function handles(): IlluminateCollection
    {
        return $this->all()->map->handle();
    }

    public function handleExists(string $handle): bool
    {
        return $this->handles()->contains($handle);
    }

    public function updateEntryUris(TypeContract $collection, $ids = null)
    {
        Entry::whereCollection($collection->handle())
            ->each->save();
    }

    public function updateEntryOrder(TypeContract $collection, $ids = null)
    {
    }

    public function whereStructured(): IlluminateCollection
    {
        return $this->all()->filter->hasStructure()->values();
    }
}
