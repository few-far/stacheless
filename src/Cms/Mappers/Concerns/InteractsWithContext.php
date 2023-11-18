<?php

namespace FewFar\Stacheless\Cms\Mappers\Concerns;

use FewFar\Stacheless\Cms\Mappers\Context;

trait InteractsWithContext
{
    /**
     * Context for the current request.
     */
    protected Context $context;

    /**
     * Set the context for the current request.
     */
    public function setContext(Context $context) : static
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Shorthand to get values from the current context.
     */
    public function __get($key)
    {
        return $this->context->{$key} ?? null;
    }
}
