<?php

namespace FewFar\Stacheless\Database\Concerns;

use FewFar\Stacheless\Config;
use Illuminate\Support\Str;

trait PrefixedTable
{
    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table ?? app(Config::class)->get('table_prefix') . Str::snake(Str::pluralStudly(Str::replaceLast('Model', '', class_basename($this))));
    }
}
