<?php

namespace FewFar\Stacheless\Repositories;

use Statamic\Contracts\Structures\CollectionTreeRepository as RepositoryContract;
use Statamic\Structures\CollectionTree;

class CollectionTreeRepository extends NavigationTreeRepository implements RepositoryContract
{
    /**
     * Statamic type "slug" used for config and Blink cache.
     *
     * @var string
     */
    protected $typeKey = 'collection_trees';

    /**
     * Determines which Statamic type this class manages.
     *
     * @var string
     */
    protected $typeClass = CollectionTree::class;
}
