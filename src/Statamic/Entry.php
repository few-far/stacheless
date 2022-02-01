<?php

namespace FewFar\Stacheless\;

use Carbon\Carbon;
use Facades\Statamic\View\Cascade;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Statamic\Entries\Entry as StatamicEntry;
use Statamic\Facades\YAML;

class Entry extends StatamicEntry
{
    protected $model;

    public static function fromModel(EntryModel $model)
    {
        return (new static)
            ->locale($model->site)
            ->slug($model->slug)
            ->date($model->date)
            ->collection($model->collection)
            ->data(YAML::parse($model->yaml) ?: $model->json)
            ->blueprint($model->blueprint)
            ->published($model->published)
            ->model($model);
    }

    public function toModel()
    {
        $data = $this->fileData();

        return EntryModel::firstOrNew([ 'id' => $this->id() ])->fill([
            'origin_id' => $this->originId(),
            'site' => $this->locale(),
            'slug' => $this->slug(),
            'uri' => $this->uri(),
            'blueprint' => $this->blueprint()->handle(),
            'date' => $this->hasDate() ? $this->date() : null,
            'collection' => $this->collectionHandle(),
            'json' => $data,
            'yaml' => YAML::dump($data),
            'published' => $this->published(),
            'status' => $this->status(),
        ]);
    }

    public function model($model = null)
    {
        if (func_num_args() === 0) {
            return $this->model;
        }

        $this->model = $model;

        $this->id($model->id);

        return $this;
    }

    public function lastModified()
    {
        return $this->model->updated_at;
    }

    public function origin($origin = null)
    {
        if (func_num_args() > 0) {
            $this->origin = $origin;

            return $this;
        }

        if ($this->origin) {
            return $this->origin;
        }

        if (! $this->model?->origin) {
            return null;
        }

        return self::fromModel($this->model->origin);
    }

    public function originId()
    {
        return optional($this->origin)->id() ?? optional($this->model)->origin_id;
    }

    public function hasOrigin()
    {
        return $this->originId() !== null;
    }
}
