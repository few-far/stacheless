<?php

namespace FewFar\Stacheless\Cms\Mappers;

use FewFar\Stacheless\Cms\Support\Concerns\BuildsModels;

class BlockMapper
{
    use BuildsModels;

    /**
     * Maps an array of BE blocks to FE blocks.
     *
     * @param \iterable
     * @return \Illuminate\Support\Collection
     */
    public function map($blocks)
    {
        return collect($blocks)
            ->flatMap(function ($block, $n) {
                if (!method_exists($this, $method = ('map_' . $block['type']))) {
                    return !app()->environment('local') ? [] : [[
                        'type' => 'debug',
                        'message' => 'Missing BlockMapper method: ' . $method,
                    ]];
                }

                $model = $this->{$method}($block, $n);

                // We've been given an array of blocks, let's just return them all.
                if (!$model || !$this->isAssoc($model)) {
                    return $model;
                }

                return [
                    collect(['type' => $block['type']])
                        ->merge($model)
                        ->all(),
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * Context attributes used the request.
     *
     * @var  mixed
     */
    protected $context;

    /**
     * Sets the context attributes used the request.
     *
     * @param  \Statamic\Entries\Entry  $entry
     * @return static
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }
}
