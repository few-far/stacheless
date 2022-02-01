<?php

namespace FewFar\Stacheless\Database;

use Illuminate\Database\Eloquent\Model as Eloquent;

class RevisionModel extends Eloquent
{
    use Concerns\PrefixedTable;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var string[]|bool
     */
    protected $guarded = [];
}
