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

        return $type;
    }

    protected function loadVariables($global)
    {
        $this->config->get("types.global_sets.variables_model")::query()
            ->where('handle', $global->handle())
            ->get()
            ->each(function ($model) use ($global) {
                $variables = $global->makeLocalization($model->site);

                $variables->data($data = YAML::parse($model->yaml));

                if ($origin = Arr::get($data, 'origin')) {
                    $variables->origin($origin);
                }

                $global->addLocalization($variables);
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

    protected function saveVariables(Variables $variables)
    {
        $model = $this->config->get("types.global_sets.variables_model")::query()
            ->firstOrNew([
                'handle' => $variables->handle(),
                'site' => $variables->locale(),
            ]);

        $this->hydrateModel($model, $variables);

        $this->saveModel($model);
    }

    public function save($global)
    {
        parent::save($global);

        Site::all()
            ->map->handle()
            ->filter(fn ($site) => $global->existsIn($site))
            ->each(fn ($site) => $this->saveVariables($global->in($site)));

        return true;
    }
}
