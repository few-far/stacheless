<?php

namespace FewFar\Stacheless\Repositories\Events;

use Illuminate\Foundation\Events\Dispatchable;

class TypeRequested
{
    use Dispatchable;

    public function __construct(public $type)
    {
    }
}
