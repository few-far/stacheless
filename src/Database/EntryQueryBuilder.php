<?php

namespace FewFar\Stacheless\Database;

use Illuminate\Database\PostgresConnection;
use Illuminate\Support\Arr;
use Statamic\Contracts\Entries\EntryRepository;
use Statamic\Contracts\Entries\QueryBuilder;
use Statamic\Entries\EntryCollection;
use Statamic\Query\EloquentQueryBuilder;

class EntryQueryBuilder extends EloquentQueryBuilder implements QueryBuilder
{
    protected $real_columns = [
        'id', 'site', 'origin_id', 'published', 'slug', 'uri',
        'json', 'yaml', 'date', 'collection', 'created_at', 'updated_at',
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

    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        $switch_like_operator = (
            func_num_args() === 3
             && strtolower($operator) === 'like'
             && $this->builder->getConnection() instanceof PostgresConnection
        );

        if ($switch_like_operator) {
            return $this->where($column, 'ILIKE', $value, $boolean);
        }

        if ($column === 'status') {
            if (func_num_args() === 2) {
                return parent::where('published', $operator === 'published');
            }

            else {
                return parent::where('published', $operator, $value === 'published', $boolean);
            }
        }

        return parent::where(...func_get_args());
    }

    public function whereStatus(string $status)
    {
        if (! in_array($status, ['published', 'draft', 'scheduled', 'expired'])) {
            throw new \Exception("Invalid status [$status]");
        }

        if ($status === 'draft') {
            return $this->where('published', false);
        }

        $this->where('published', true);

        if ($status === 'scheduled') {
            $this->where('date', '>', now());
        } else {
            $this->where(function ($query) {
                $query->where('date', null);
                $query->orWhere('date', '<', now());
            });
        }

        if ($status === 'expired') {
            $this->where('date', 'invalid'); // intentionally trigger no results.
        }

        return $this;
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
