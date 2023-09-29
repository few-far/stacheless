<?php

namespace FewFar\Stacheless\Repositories;

use Statamic\Contracts\Globals\GlobalRepository as RepositoryContract;
use Statamic\Contracts\Globals\GlobalSet as TypeContract;
use Statamic\Facades\YAML;
use Statamic\Globals\GlobalCollection;

class GlobalSetRepository extends BaseRepository implements RepositoryContract
{
    /**
     * Statamic type "slug" used for config and Blink cache.
     *
     * @var string
     */
    protected $typeKey = 'global_sets';

    /**
     * Determines which Statamic type this class manages.
     *
     * @var string
     */
    protected $typeClass = TypeContract::class;

    public function make($handle = null)
    {
        return app($this->typeClass)->handle($handle);
    }

    public function hydrateType($type, $model)
    {
        $data = YAML::parse($model->yaml);

        $type->title($data['title'] ?? null);
    }

    public function all(): GlobalCollection
    {
        return GlobalCollection::make(
            parent::all()->all()
        );
    }

    public function find($id): ?TypeContract
    {
        return $this->findByHandle($id);
    }

    public function findByHandle($handle): ?TypeContract
    {
        return $this->findWithCache($handle);
    }
}
