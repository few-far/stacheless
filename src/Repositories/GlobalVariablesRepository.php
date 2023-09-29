<?php

namespace FewFar\Stacheless\Repositories;

use Illuminate\Support\Arr;
use Statamic\Contracts\Globals\GlobalVariablesRepository as RepositoryContract;
use Statamic\Contracts\Globals\Variables as TypeContract;
use Statamic\Facades\YAML;
use Statamic\Globals\Variables;
use Statamic\Globals\VariablesCollection;

class GlobalVariablesRepository extends BaseLocaleRepository implements RepositoryContract
{
    /**
     * Statamic type "slug" used for config and Blink cache.
     *
     * @var string
     */
    protected $typeKey = 'global_variables';

    /**
     * Determines which Statamic type this class manages.
     *
     * @var string
     */
    protected $typeClass = TypeContract::class;

    /**
     * Gets the Eloquent Model class name for the Repository’s Type.
     *
     * @return class-string<extends \Illuminate\Database\Eloquent\Model>
     */
    protected function getModelClass()
    {
        return $this->config->get("types.global_sets.variables_model");
    }

    /**
     * Creates an instance of Repository‘s type based on the given model.
     *
     * The type will not be hydrated and only some Repositories will
     * semi-hydrate the type with the handle.
     *
     * @return T
     */
    public function makeType($model)
    {
        return app($this->typeClass)
            ->globalSet($model->handle)
            ->locale($model->site);
    }

    public function hydrateType($type, $model)
    {
        $data = YAML::parse($model->yaml);

        $type->data($data);
    }

    public function find($id): ?TypeContract
    {
        $parts = explode('::', $id);

        return $this->findWithCache([
            'handle' => Arr::get($parts, 0),
            'site' => Arr::get($parts, 1),
        ]);
    }

    public function whereSet($handle): VariablesCollection
    {
        return $this->all()->filter(function ($variables) use ($handle) {
            return $variables->handle() === $handle;
        });
    }

    public function all(): VariablesCollection
    {
        return VariablesCollection::make(
            parent::all()->all()
        );
    }
}
