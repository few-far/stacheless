<?php

namespace FewFar\Stacheless\Database;

use Illuminate\Support\Arr;
use Statamic\Contracts\Entries\EntryRepository;
use Statamic\Contracts\Entries\QueryBuilder;
use Statamic\Entries\EntryCollection;
use Statamic\Query\EloquentQueryBuilder;

class EntryQueryBuilder extends EloquentQueryBuilder implements QueryBuilder
{
    protected $real_columns = [
        'id', 'site', 'origin_id', 'published', 'status', 'slug', 'uri',
        'date', 'collection', 'created_at', 'updated_at',
    ];

    protected function transform($items, $columns = [])
    {
        $repo = app(EntryRepository::class);

        return EntryCollection::make($items)
            ->map(fn ($model) => $repo->toType($model));
    }

    protected function column($column)
    {
        if (! in_array($column, $this->real_columns)) {
            return 'json->'.$column;
        }

        return $column;
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
}
