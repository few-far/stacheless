<?php

namespace FewFar\Stacheless\Repositories;

use FewFar\Stacheless\Database\TermQueryBuilder;
use Illuminate\Support\Arr;
use Statamic\Contracts\Taxonomies\TermRepository as RepositoryContract;
use Statamic\Contracts\Taxonomies\Term as TypeContract;
use Statamic\Contracts\Taxonomies\Term;
use Statamic\Facades\Site;
use Statamic\Facades\YAML;

class TermRepository extends BaseRepository implements RepositoryContract
{
    /**
     * Statamic type "slug" used for config and Blink cache?
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

    protected function makeBlinkKey($key)
    {
        return "$this->typeKey::{$key['taxonomy']}::{$key['slug']}";
    }

    protected function makeBlinkKeyForType($type)
    {
        return $this->makeBlinkKey($this->makeWhereArgs($type));
    }

    protected function makeWhereArgsFromKey($key)
    {
        return [
            'slug' => $key['slug'],
            'taxonomy' => $key['taxonomy'],
        ];
    }

    protected function makeWhereArgs($type)
    {
        return [
            'slug' => $type->slug(),
            'taxonomy' => $type->taxonomyHandle(),
        ];
    }

    protected function hydrateType($type, $model)
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

    protected function hydrateModel($model, $type)
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
        return $this->getBlinkStore()->once('terms::' . $handle, function () use ($handle) {
            $this->getModelClass()::where('taxonomy', $handle)
                ->get()
                ->map(fn ($model) => $this->toType($model));
        });
    }

    public function whereInTaxonomy(array $handles)
    {
        $store = $this->getBlinkStore();

        $missing = collect($handles)
            ->filter(fn ($handle) => $store->has("terms::$handle"));

        if ($missing->isNotEmpty()) {
            $entriesByCollection = $this->query()
                ->whereIn('taxonomy', $handles)
                ->get()
                ->groupBy
                ->taxonmyHandle();

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
