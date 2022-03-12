<?php

namespace FewFar\Stacheless\Database;

use Illuminate\Support\Arr;
use Statamic\Assets\AssetCollection;
use Statamic\Contracts\Assets\AssetContainer;
use Statamic\Contracts\Assets\AssetRepository;
use Statamic\Contracts\Assets\QueryBuilder;
use Statamic\Query\EloquentQueryBuilder;

class AssetQueryBuilder extends EloquentQueryBuilder implements QueryBuilder
{
    protected $real_columns = [
        'id', 'container', 'path', 'folder', 'json', 'yaml', 'created_at', 'updated_at',
    ];

    protected function transform($items, $columns = [])
    {
        $repo = app(AssetRepository::class);

        return AssetCollection::make($items)
            ->map(fn ($model) => $repo->toType($model));
    }

    protected function column($column)
    {
        if (! in_array($column, $this->real_columns)) {
            return 'json->'.$column;
        }

        return $column;
    }

    public function where($column, $operator = null, $value = null)
    {
        if ($column === 'container' && $operator instanceof AssetContainer) {
            $operator = $operator->handle();
        }

        return parent::where($column, $operator, $value);
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
