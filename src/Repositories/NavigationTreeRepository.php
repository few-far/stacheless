<?php

namespace FewFar\Stacheless\Repositories;

use Statamic\Contracts\Structures\NavTreeRepository as RepositoryContract;
use Statamic\Contracts\Structures\Tree;
use Statamic\Facades\YAML;
use Statamic\Structures\NavTree;

class NavigationTreeRepository extends BaseLocaleRepository  implements RepositoryContract
{
    /**
     * Statamic type "slug" used for config and Blink cache?
     *
     * @var string
     */
    protected $typeKey = 'navigation_trees';

    /**
     * Determines which Statamic type this class manages.
     *
     * @var string
     */
    protected $typeClass = NavTree::class;

    public function find(string $handle, string $site): ?Tree
    {
        return $this->findWithCache(compact('handle', 'site'));
    }

    public function hydrateType($type, $model)
    {
        $type
            ->locale($model->site)
            ->tree(YAML::parse($model->yaml)['tree'] ?? [])
            ->syncOriginal();
    }
}
