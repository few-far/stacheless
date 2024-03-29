<?php

namespace FewFar\Stacheless\Database;

use Illuminate\Support\Arr;
use Statamic\Contracts\Taxonomies\TermRepository;
use Statamic\Facades\Collection;
use Statamic\Facades\Taxonomy;
use Statamic\Query\EloquentQueryBuilder;
use Statamic\Taxonomies\TermCollection;

class TermQueryBuilder extends EloquentQueryBuilder
{
    protected $real_columns = [
        'slug', 'taxonomy', 'json', 'yaml', 'created_at', 'updated_at',
    ];

    public function builder()
    {
        return $this->builder;
    }

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

        if ($column === 'id') {
            if (empty($values)) {
                $this->builder->whereIn('id', $values, $boolean);

                return $this;
            }

            $termsByTaxonomy = collect($values)
                ->map(function ($value) {
                    $segments = explode('::', $value);

                    return [
                        'taxonomy' => Arr::get($segments, 0),
                        'term' => Arr::get($segments, 1),
                    ];
                })
                ->groupBy('taxonomy')
                ->map(fn ($values) => collect($values)->pluck('term')->all());

            $this->builder->where(function ($query) use ($termsByTaxonomy) {
                foreach ($termsByTaxonomy as $taxonomy => $terms) {
                    $query->orWhere(function ($query) use ($taxonomy, $terms) {
                        $query->where('taxonomy', $taxonomy);
                        $query->whereIn('slug', $terms);
                    });
                }
            });

            return $this;
        }

        return parent::whereIn($column, $values, $boolean);
    }

    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        if ($column === 'site') {
            if ($value !== null && $operator === null) {
                throw new \Exception('querying site isn\'t supported with an operator');
            }

            $handles = Taxonomy::all()
                ->filter(function ($taxonomy) use ($operator, $value) {
                    return $taxonomy->sites()
                        ->map(fn ($handle) => compact('handle'))
                        ->where('handle', is_string($operator) ? $operator : $operator->handle())
                        ->isNotEmpty();
                })
                ->map->handle();

            $this->builder->whereIn('taxonomy', $handles, $boolean);

            return $this;
        }

        if ($column === 'collection') {
            // We need to dynamically create the args so that the Laravel
            // Collection honors the nullable $value.
            $args = ['handle', $operator];

            if (func_num_args() > 2) {
                $args[] = $value;
            }

            $handles = Collection::all()
                ->filter(function ($collection) use ($args) {

                    return collect()
                        ->push([ 'handle' => $collection->handle() ])
                        ->where(...$args)
                        ->isNotEmpty();
                })
                ->flatMap->taxonomies()
                ->map->handle();

            $this->builder->whereIn('taxonomy', $handles, $boolean);

            return $this;
        }

        if ($column === 'id') {
            $segments = explode('::', func_num_args() > 2 ? $value : $operator);
            $taxonomy_handle = Arr::get($segments, 0);
            $term_handle = Arr::get($segments, 1);

            $this->builder->where('slug', $term_handle);
            $this->builder->where('taxonomy', $taxonomy_handle);

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

    public function count()
    {
        if (func_num_args() > 0) {
            throw new \Exception('notsupported');
        }

        return $this->builder->count();
    }

    public function __call($method, $args)
    {
        throw new \Exception('notsupported');
    }
}
