<?php

namespace FewFar\Stacheless\Database;

class EntryModel extends Model
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'json' => 'json',
    ];
}
