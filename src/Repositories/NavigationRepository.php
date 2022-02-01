<?php

namespace FewFar\Stacheless\Repositories;

use Illuminate\Support\Collection as IlluminateCollection;
use Statamic\Contracts\Structures\Nav as NavigationContract;
use Statamic\Contracts\Structures\NavigationRepository as RepositoryContract;
use Statamic\Facades\Blink;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Facades\YAML;
use Statamic\Stache\Stache;

class NavigationRepository extends BaseRepository implements RepositoryContract
{
    /**
     * Statamic type "slug" used for config and Blink cache?
     *
     * @var string
     */
    protected $typeKey = 'navigations';

    /**
     * Determines which Statamic type this class manages.
     *
     * @var string
     */
    protected $typeClass = NavigationContract::class;

    protected function hydrateType($type, $model)
    {
        $data = YAML::parse($model->yaml);

        $type
            ->title($data['title'] ?? null)
            ->maxDepth($data['max_depth'] ?? null)
            ->collections($data['collections'] ?? null)
            ->expectsRoot($data['root'] ?? false);
    }

    public function all(): IlluminateCollection
    {
        return $this->getBlinkStore()->once('navigations', function () {
            return $this->getModelClass()::all()->map(function ($model) {
                return $this->toType($model);
            });
        });
    }

    public function find($id): ?NavigationContract
    {
        return $this->findByHandle($id);
    }

    public function findByHandle($handle): ?NavigationContract
    {
        return $this->all()->first(function ($item) use ($handle) {
            return $item->handle() === $handle;
        });
    }
}
