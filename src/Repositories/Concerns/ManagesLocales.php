<?php

namespace FewFar\Stacheless\Repositories\Concerns;

trait ManagesLocales
{
    protected function makeBlinkKey($key)
    {
        return "$this->typeKey::{$key['site']}::{$key['handle']}";
    }

    protected function makeBlinkKeyForType($type)
    {
        return $this->makeBlinkKey($this->makeWhereArgs($type));
    }

    protected function makeWhereArgs($type)
    {
        return [
            'handle' => $type->handle(),
            'site' => $type->locale(),
        ];
    }
}
