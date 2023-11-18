<?php

namespace FewFar\Stacheless\Cms\Mappers;

/**
 * Values required to render a page.
 *
 * E.g. current Entry, page meta, Global Settings.
 */
class Context
{
    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Stores values for the current request.
     */
    public array $attributes = [];

    /**
     * Shorthand to get values from the $attributes
     */
    public function __get($key)
    {
        return ($this->attributes)[$key] ?? null;
    }
}
