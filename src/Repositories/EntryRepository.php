<?php

namespace FewFar\Stacheless\Repositories;

use Carbon\Carbon;
use FewFar\Stacheless\Database\EntryModel;
use Statamic\Contracts\Entries\EntryRepository as Contract;
use Illuminate\Support\Str;
use Illuminate\Support\Collection as IlluminateCollection;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Contracts\Entries\QueryBuilder;
use Statamic\Facades\Site;
use Statamic\Facades\YAML;

class EntryRepository extends BaseRepository implements Contract
{
    public static function bindings()
    {
        return [
            QueryBuilder::class => \FewFar\Stacheless\Database\EntryQueryBuilder::class,
        ];
    }

    /**
     * Statamic type "slug" used for config and Blink cache?
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

    protected function hydrateType($type, $model)
    {
        return $type
            ->id($model->id)
            ->locale($model->site)
            ->origin($model->origin_id)
            ->slug($model->slug)
            ->date(Carbon::parse($model->date))
            ->collection($model->collection)
            ->data(YAML::parse($model->yaml))
            ->blueprint($model->blueprint)
            ->published($model->published);
    }

    protected function hydrateModel($model, $type)
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

    protected function makeWhereArgs($type)
    {
        return [ 'id' => $type->id() ];
    }

    public function make(string $handle = null)
    {
        return app($this->typeClass);
    }

    public function all(): IlluminateCollection
    {
        return $this->query()->all();
    }

    public function whereCollection(string $handle)
    {
        return $this->getBlinkStore()->once("entries::$handle", function () use ($handle) {
            return $this->query()->where('entries', $handle)->get();
        });
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
                ->groupBy
                ->collectionHandle();

            foreach ($entriesByCollection as $handle => $entries) {
                $store->put("entries::$handle", $entries);
            }
        }

        return collect($handles)
            ->flatMap(fn ($handle) => $store->get("entries::$handle"))
            ->filter()
            ->values();
    }

    public function find($id)
    {
        if (! $id) {
            return null;
        }

        return $this->getBlinkStore()->once($this->makeBlinkKey($id), function () use ($id) {
            return $this->query()->where('id', $id)->first();
        });
    }

    public function findByUri(string $uri, string $site = null): ?EntryContract
    {
        $site = $site ?? Site::default()->handle();

        $entry = $this->query()
            ->where('uri', $uri)
            ->where('site', $site)
            ->first();

        if (! $entry) {
            return null;
        }

        return $entry;
    }

    /** @deprecated */
    public function findBySlug(string $slug, string $collection)
    {
        throw new \Exception('Not implemented');
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
}
