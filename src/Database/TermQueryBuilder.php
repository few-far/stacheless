<?php

namespace FewFar\Stacheless\Database;

use FewFar\Stacheless\Config;
use Illuminate\Database\PostgresConnection;
use Illuminate\Support\Arr;
use Statamic\Contracts\Taxonomies\TermRepository;
use Statamic\Facades\Collection;
use Statamic\Facades\Taxonomy;
use Statamic\Query\EloquentQueryBuilder;
use Statamic\Sites\Site;
use Statamic\Taxonomies\TermCollection;

class TermQueryBuilder extends EloquentQueryBuilder
{
    protected $real_columns = [
        'slug', 'taxonomy', 'json', 'yaml', 'created_at', 'updated_at',
    ];

    protected function transform($items, $columns = [])
    {
        $repo = app(TermRepository::class);

        return TermCollection::make($items)
            ->map(fn ($model) => $repo->toType($model));
    }

    protected function column($column)
    {
        if (! in_array($column, $this->real_columns)) {
            return 'json->'.$column;
        }

        return $column;
    }

    // protected function handleWhereSite($site, $value)
    // {
    //     $this->builder->whereExists(function ($query) use ($site) {
    //         $handle = $site instanceof Site ? $site->handle() : $site;
    //         $query->from('statamic_taxonomies')
    //             ->whereRaw("statamic_taxonomies.json->'sites' @> to_jsonb(?::text)", $handle)
    //             ->whereRaw('statamic_terms.taxonomy = statamic_taxonomies.handle');
    //     });

    //     return $this;
    // }

    // protected function handleWhereCollection($operator, $value)
    // {
    //     $stacheless_taxonomies = app(Config::class)->get('types.collections.enabled');
    //     $using_postgres = $this->builder->getConnection() instanceof PostgresConnection;

    //     if ($stacheless_taxonomies && $using_postgres) {
    //         $this->builder->whereExists(function ($query) use ($operator, $value) {
    //             $query->from('statamic_collections')
    //                 ->where('statamic_collections.handle', $operator, $value)
    //                 ->whereRaw("statamic_collections.json->'taxonomies' @> to_jsonb(statamic_terms.taxonomy)");
    //         });

    //         return $this;
    //     }

    //     return $this;
    // }

    public function whereIn($column, $values, $boolean = 'and')
    {
        if ($column === 'site') {
            $handles = Taxonomy::all()
                ->filter(function ($taxonomy) use ($values) {
                    return $taxonomy->sites()
                        ->map(fn ($handle) => compact('handle'))
                        ->whereIn('handle', $values)
                        ->isNotEmpty();
                })
                ->map->handle();

            $this->builder->whereIn('taxonomy', $handles, $boolean);

            return $this;
        }

        if ($column === 'collection') {
            $handles = Collection::all()
                ->filter(function ($collection) use ($values) {
                    return collect([ 'handle' => $collection->handle() ])
                        ->where('handle', $values)
                        ->isNotEmpty();
                })
                ->flatMap->taxonomies()
                ->map->handle();

            $this->builder->whereIn('taxonomy', $handles, $boolean);

            return $this;
        }

        return parent::whereIn($column, $values, $boolean);
    }

    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        if ($column === 'site') {
            $handles = Taxonomy::all()
                ->filter(function ($taxonomy) use ($operator, $value) {
                    return $taxonomy->sites()
                        ->map(fn ($handle) => compact('handle'))
                        ->where('handle', $operator, $value)
                        ->isNotEmpty();
                })
                ->map->handle();

            $this->builder->whereIn('taxonomy', $handles, $boolean);

            return $this;
        }

        if ($column === 'collection') {
            throw new \Exception('here');

            $handles = Collection::all()
                ->filter(function ($collection) use ($operator, $value) {
                    return collect([ 'handle' => $collection->handle() ])
                        ->where('handle', $operator, $value)
                        ->isNotEmpty();
                })
                ->flatMap->taxonomies()
                ->map->handle();

            $this->builder->whereIn('taxonomy', $handles, $boolean);

            return $this;
        }

        return parent::where($column, $operator, $value);
    }

    public function orWhere($column, $operator = null, $value = null)
    {
        return $this->where($column, $operator, $value, 'or');
    }

    public function orWhereIn($column, $values)
    {
        return $this->whereIn($column, $values, 'or');
    }

    public function first()
    {
        if (!($first = $this->builder->select($this->selectableColumns($this->columns))->first())) {
            return null;
        }

        return $this->transform([ $first ])->first();
    }

    protected function selectableColumns($columns = ['*'])
    {
        $wrapped = Arr::wrap($columns);
        return empty($wrapped) ? ['*'] : $columns;
    }

    public function __call($method, $args)
    {
        throw new \Exception('notsupported');
    }
}
