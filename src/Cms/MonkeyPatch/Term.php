<?php

namespace FewFar\Stacheless\Cms\MonkeyPatch;

use ArrayAccess;
use BadMethodCallException;
use Statamic\Taxonomies\Term as StatamicTerm;

class Term extends StatamicTerm implements ArrayAccess
{
    /**
     * TODO: Remove this if possible.
     *
     * @see \Statamic\Query\OrderedQueryBuilder::performFallbackOrdering
     *
     * This method seemingly invokes the terms as arrays. I'm assuming this is
     * because in the Statche it is returned as an array and hydrated later?
     * Poor excuse. Quick fix seems to be allow this to happen.
     */
    public function offsetGet(mixed $offset): mixed
    {
        if (debug_backtrace()[1]['class'] !== 'Statamic\Query\OrderedQueryBuilder') {
            throw new BadMethodCallException('Only Statamic\Query\OrderQueryBuilder is intended to use this getter');
        }

        if ($offset !== 'id') {
            throw new BadMethodCallException('This getter is intended to only be used for \'id\'.');
        }

        return $this->id();
    }

    public function offsetExists(mixed $offset): bool
    {
        return false;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new BadMethodCallException('Term does not support this operation');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new BadMethodCallException('Term does not support this operation');
    }

}
