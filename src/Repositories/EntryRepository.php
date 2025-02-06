<?php

namespace FewFar\Stacheless\Repositories;

use Carbon\Carbon;
use FewFar\Stacheless\Database\EntryModel;
use FewFar\Stacheless\Repositories\Events\TypeRequested;
use Illuminate\Support\Arr;
use Statamic\Contracts\Entries\EntryRepository as Contract;
use Illuminate\Support\Str;
use Illuminate\Support\Collection as IlluminateCollection;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Contracts\Entries\QueryBuilder;
use Statamic\Exceptions\EntryNotFoundException;
use Statamic\Facades\Collection;
use Statamic\Facades\Site;
use Statamic\Facades\YAML;

class EntryRepository extends BaseRepository implements Contract
{
    protected $substitutionsById = [];
    protected $substitutionsByUri = [];

    public static function bindings()
    {
        return [
            QueryBuilder::class => \FewFar\Stacheless\Database\EntryQueryBuilder::class,
        ];
    }

    /**
     * Statamic type "slug" used for config and Blink cache.
     *
     * @var string
     */
    protected $typeKey = 'entries';

    /**
     * Determines which Statamic type this class manages.
     *
     * @var string
     */
    protected $typeClass = EntryContract::class;

    public function hydrateType($type, $model)
    {
        $entry = $type
            ->id($model->id)
            ->locale($model->site)
            ->origin($model->origin_id)
            ->slug($model->slug)
            ->collection($model->collection)
            ->data(YAML::parse($model->yaml))
            ->blueprint($model->blueprint)
            ->published($model->published);

        // Statamic 4 now throws error if Collection isn't dated.
        if ($entry->collection()?->dated() && $model->date) {
            $entry->date(Carbon::parse($model->date));
        }

        $entry->syncOriginal();

        return $entry;
    }

    public function hydrateModel($model, $type)
    {
        return parent::hydrateModel($model, $type)->fill([
            'origin_id' => optional($type->origin())->id(),
            'site' => $type->locale(),
            'slug' => $type->slug(),
            'uri' => $type->uri(),
            'blueprint' => $type->blueprint()->handle(),
            'date' => $type->hasDate() ? $type->date() : null,
            'collection' => $type->collectionHandle(),
            'published' => is_string($published = $type->published())
                ? $published === 'published'
                : $published === true,
        ]);
    }

    public function taxonomize($entry)
    {
    }

    public function makeWhereArgs($type)
    {
        return [ 'id' => $type->id() ];
    }

    public function makeWhereArgsFromKey($key)
    {
        return [ 'id' => $key ];
    }

    public function make(string $handle = null)
    {
        return app($this->typeClass);
    }

    public function makeType($model)
    {
        return $this->make();
    }

    public function all(): IlluminateCollection
    {
        return $this->query()->get();
    }

    public function whereCollection(string $handle)
    {
        return $this->whereInCollection([ $handle ]);
    }

    public function whereInCollection(array $handles)
    {
        $store = $this->getBlinkStore();

        $missing = collect($handles)
            ->filter(fn ($handle) => $store->has("entries::$handle"));

        if ($missing->isNotEmpty()) {
            $entriesByCollection = $this->query()
                ->whereIn('collection', $handles)
                ->get()
                ->groupBy->collectionHandle();

            foreach ($entriesByCollection as $handle => $entries) {
                $store->put("entries::$handle", $entries);
            }
        }

        // No, get all the entries in the order they've been asked
        // for in the $handles argument.
        return collect($handles)
            ->flatMap(fn ($handle) => $store->get("entries::$handle"))
            ->filter()
            ->values()
            ->each(fn ($entry) => $this->storeInCache($entry, $store));
    }

    public function find($id)
    {
        if (! $id) {
            return null;
        }

        if ($substitute = Arr::get($this->substitutionsById, $id)) {
            event(new TypeRequested($substitute));
            return $substitute;
        }

        return $this->findWithCache($id);
    }

    public function findOrFail($id): EntryContract
    {
        return $this->find($id) ?? new EntryNotFoundException($id);
    }

    public function findByUri(string $uri, string $site = null): ?EntryContract
    {
        $site = $site ?? Site::default()->handle();

        if ($substitute = Arr::get($this->substitutionsByUri, $site.'@'.$uri)) {
            return $substitute;
        }

        $entry = $this->query()
            ->where('uri', $uri)
            ->where('site', $site)
            ->first();

        if (! $entry) {
            return null;
        }

        $this->storeInCache($entry);

        return $entry;
    }

    /** @deprecated */
    public function findBySlug(string $slug, string $collection)
    {
        return $this->query()
            ->where('slug', $slug)
            ->where('collection', $collection)
            ->first();
    }

    public function query()
    {
        return app(QueryBuilder::class);
    }

    public function save($entry)
    {
        if (! $entry->id()) {
            $entry->id((string) Str::uuid());
        }

        return parent::save($entry);
    }

    public function createRules($collection, $site)
    {
        return [
            'title' => $collection->autoGeneratesTitles() ? '' : 'required',
            'slug' => 'alpha_dash',
        ];
    }

    public function updateRules($collection, $entry)
    {
        return [
            'title' => $collection->autoGeneratesTitles() ? '' : 'required',
            'slug' => 'alpha_dash',
        ];
    }

    public function substitute($item)
    {
        $this->substitutionsById[$item->id()] = $item;
        $this->substitutionsByUri[$item->locale().'@'.$item->uri()] = $item;
    }

    public function applySubstitutions($items)
    {
        return $items->map(function ($item) {
            return $this->substitutionsById[$item->id()] ?? $item;
        });
    }

    public function updateUri(\Statamic\Contracts\Entries\Entry $entry)
    {
        $entry->set('parent', $entry->parent()?->id());

        if (! $model = $this->findModel($entry->id())) {
            return;
        }

        $model->update([ 'uri' => $entry->uri() ]);
    }

    public function updateUris($collection, $ids = null)
    {
        Collection::updateEntryUris($collection, $ids);
    }

    public function updateOrders($collection, $ids = null)
    {
    }

    public function updateParents($collection, $ids = null)
    {
    }
}
