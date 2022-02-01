<?php

namespace FewFar\Stacheless\Database\Concerns;

trait CompositeKeys
{
    /**
     * Set the keys for a save update query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery($query)
    {
        $query = parent::setKeysForSaveQuery($query);
        $query->where('site', '=', $this->site);

        return $query;
    }
}
