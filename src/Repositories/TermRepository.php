<?php

namespace FewFar\Stacheless\Repositories;

use FewFar\Stacheless\Database\TermQueryBuilder;
use Illuminate\Support\Arr;
use Statamic\Contracts\Taxonomies\TermRepository as RepositoryContract;
use Statamic\Contracts\Taxonomies\Term as TypeContract;
use Statamic\Contracts\Taxonomies\Term;
use Statamic\Exceptions\EntryNotFoundException;
use Statamic\Facades\Site;
use Statamic\Facades\YAML;

class TermRepository extends BaseRepository implements RepositoryContract
{
    /**
     * Statamic type "slug" used for config and Blink cache.
     *
     * @var string
     */
    protected $typeKey = 'terms';

    /**
     * Determines which Statamic type this class manages.
     *
     * @var string
     */
    protected $typeClass = TypeContract::class;

    public function makeBlinkKey($key)
    {
        return "$this->typeKey::{$key['taxonomy']}::{$key['slug']}";
    }

    public function makeBlinkKeyForType($type)
    {
        return $this->makeBlinkKey($this->makeWhereArgs($type));
    }

    public function makeWhereArgsFromKey($key)
    {
        return [
            'slug' => $key['slug'],
            'taxonomy' => $key['taxonomy'],
        ];
    }

    public function makeWhereArgs($type)
    {
        return [
            'slug' => $type->slug(),
            'taxonomy' => $type->taxonomyHandle(),
        ];
    }

    public function hydrateType($type, $model)
    {
        $data = YAML::parse($model->yaml);

        $type->slug($model->slug);
        $type->taxonomy($model->taxonomy);

        foreach (Arr::pull($data, 'localizations') ?? [] as $locale => $localeData) {
            $type->dataForLocale($locale, $localeData);
        }

        $type->dataForLocale($type->defaultLocale(), $data);
        $type->syncOriginal();
    }

    public function hydrateModel($model, $type)
    {
        return parent::hydrateModel($model, $type)->fill([
            'slug' => $type->slug(),
            'taxonomy' => $type->taxonomyHandle(),
        ]);
    }

    public function make(string $slug = null)
    {
        return app($this->typeClass)->slug($slug);
    }

    public function makeType($model)
    {
        return $this->make($model->slug);
    }

    public function whereTaxonomy(string $handle)
    {
        return ($store = $this->getBlinkStore())->once('terms::' . $handle, function () use ($handle, $store) {
            return $this->getModelClass()::where('taxonomy', $handle)
                ->get()
                ->map(fn ($model) => $this->toType($model))
                ->each(fn ($type) => $this->storeInCache($type, $store));
        });
    }

    public function whereInTaxonomy(array $handles)
    {
        $store = $this->getBlinkStore();

        $missing = collect($handles)
            ->reject(fn ($handle) => $store->has("terms::$handle"));

        if ($missing->isNotEmpty()) {
            $entriesByCollection = $this->query()
                ->whereIn('taxonomy', $handles)
                ->get()
                ->groupBy->taxonomyHandle();

            foreach ($entriesByCollection as $handle => $entries) {
                $store->put("terms::$handle", $entries);
            }
        }

        return collect($handles)
            ->flatMap(fn ($handle) => $store->get("terms::$handle"))
            ->filter()
            ->values();
    }

    public function find($id)
    {
        [$taxonomy, $slug] = explode('::', $id);
        return $this->findWithCache(compact('taxonomy', 'slug'));
    }

    public function findOrFail($id): TypeContract
    {
        return $this->find($id) ?? new EntryNotFoundException($id);
    }

    public function query()
    {
        return app(TermQueryBuilder::class, [
            'builder' => $this->config->get('types.terms.model')::query(),
        ]);
    }

    public function findByUri(string $uri)
    {
        throw new \Exception('Not implemented');
    }

    /** @deprecated */
    public function findBySlug(string $slug, string $collection)
    {
        throw new \Exception('Not implemented');
    }

    public function entriesCount(Term $term): int
    {
        throw new \Exception('Not implemented');
    }
}
