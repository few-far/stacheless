<?php

namespace FewFar\Stacheless\Repositories;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection as IlluminateCollection;
use Statamic\Contracts\Entries\Collection as TypeContract;
use Statamic\Contracts\Entries\CollectionRepository as RepositoryContract;
use Statamic\Data\StoresScopedComputedFieldCallbacks;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Facades\YAML;

class CollectionRepository extends BaseRepository implements RepositoryContract
{
    use StoresScopedComputedFieldCallbacks;

    protected $additionalPreviewTargets = [];

    /**
     * Statamic type "slug" used for config and Blink cache.
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

    public function hydrateType($type, $model)
    {
        $data = YAML::parse($model->yaml);

        $type
            ->title(Arr::get($data, 'title'))
            ->routes(Arr::get($data, 'route'))
            ->mount(Arr::get($data, 'mount'))
            ->dated(Arr::get($data, 'date', false))
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
        return app($this->typeClass)->handle($handle);
    }

    public function all(): IlluminateCollection
    {
        return parent::all();
    }

    public function find($id): ?TypeContract
    {
        return parent::findInAll($id);
    }

    public function findByHandle($handle): ?TypeContract
    {
        return parent::findInAll($handle);
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
