<?php

namespace FewFar\Stacheless\Repositories\Concerns;

/**
 * Helper Trait for Repositories that have compound keys using their locale.
 *
 * @template T
 */
trait ManagesLocales
{
    /**
     * Key for string instances of this Repository's Type in Blink Cache
     *
     * @param array  $key
     * @return string
     */
    protected function makeBlinkKey($key)
    {
        return "$this->typeKey::{$key['site']}::{$key['handle']}";
    }

    /**
     * Key for string instances of this Repository's Type in Blink Cache
     *
     * @param T  $type
     * @return string
     */
    protected function makeBlinkKeyForType($type)
    {
        return $this->makeBlinkKey($this->makeWhereArgs($type));
    }

    /**
     * Makes where query attributes for searching for this Type.
     *
     * @param  T  $type
     * @return array
     */
    protected function makeWhereArgs($type)
    {
        return [
            'handle' => $type->handle(),
            'site' => $type->locale(),
        ];
    }

    /**
     * Hydrates the Repository’s Model from the given Type.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  T  $type
     * @return void
     */
    protected function hydrateModel($model, $type)
    {
        $model->fill([
            'site' => $type->locale(),
            'json' => $type->fileData(),
            'yaml' => $type->fileContents(),
        ]);
    }
}
