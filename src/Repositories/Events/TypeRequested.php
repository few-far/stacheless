<?php

namespace FewFar\Stacheless\Repositories\Events;

class TypeRequested
{
    /**
     * Creates an instance of the Event.
     *
     * @param  mixed  $type
     * @var mixed  $type
     * @return void
     */
    public function __construct(public $type)
    {
    }
}
