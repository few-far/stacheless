<?php

namespace FewFar\Stacheless\Repositories;

use Illuminate\Support\Arr;
use Statamic\Contracts\Globals\GlobalRepository as RepositoryContract;
use Statamic\Contracts\Globals\GlobalSet as TypeContract;
use Statamic\Contracts\Globals\Variables;
use Statamic\Contracts\Structures\Tree;
use Statamic\Facades\Blink;
use Statamic\Facades\Site;
use Statamic\Globals\GlobalSet;
use Statamic\Facades\YAML;
use Statamic\Globals\GlobalCollection;

class GlobalSetRepository extends BaseRepository implements RepositoryContract
{
    /**
     * Statamic type "slug" used for config and Blink cache?
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

    public function findNoCache($key)
    {
        if (! ($model = $this->findModel($key))) {
            return null;
        }

        $type = $this->toType($model);

        $this->loadVariables($type);

        return dd($type);
    }

    protected function loadVariables($global)
    {
        $this->config->get("types.global_sets.variables_model")::query()
            ->where('handle', $key)
            ->get()
            ->each(function ($model) use ($global) {
                $variables = $global->makeLocalization($model->locale);

                $variables->data($data = YAML::parse($model->yaml));

                if ($origin = Arr::get($data, 'origin')) {
                    $variables->origin($origin);
                }

                $global->makeLocalization($variables);
            });
    }

    protected function hydrateType($type, $model)
    {
        $data = YAML::parse($model->yaml);

        $type->title($data['title'] ?? null);
    }

    public function all(): GlobalCollection
    {
        return GlobalCollection::make(
            $this->getModelClass()::all()->map(function ($model) {
                $global = $this->toType($model);
                $this->loadVariables($global);
                return $global;
            })
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

    // todo: make findModel load them all for the handle

    public function save($global)
    {
        parent::save($global);

        return true;

        // throw new \Exception('Not implemented');

        // Site::all()->each(function ($site) use ($set) {
        //     $handle = $site->handle();

        //     if ($set->existsIn($site)) {
        //         $this->saveType($set->in($handle));
        //     }
        // });

        // return true;
    }
}
