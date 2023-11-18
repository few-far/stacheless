<?php

namespace FewFar\Stacheless\Cms\Mappers\Concerns;

use FewFar\Stacheless\Cms\Mappers\BlockMapper;

trait ModelsBlocks
{
    protected $blockMapper = BlockMapper::class;
    protected $blocksAttribute = 'blocks';
    protected $blocksPrepend = null;
    protected $blocksAppend = null;

    public function makeBlocks()
    {
        $blocks = $this->makeBlocksForAttribute($this->blocksAttribute);

        $prepend = $this->getPrependBlocks();
        $append = $this->getAppendBlocks();

        return collect([ $prepend, $blocks, $append ])
            ->flatten(1)
            ->filter()
            ->values();
    }

    public function blockMapper()
    {
        return app($this->blockMapper)
            ->setContext($this->context);
    }

    public function makeBlocksForAttribute($attribute)
    {
        return $this->blockMapper()->map($this->get($this->values, $attribute));
    }

    public function getPrependBlocks()
    {
        return $this->blocksPrepend;
    }

    public function getAppendBlocks()
    {
        return $this->blocksAppend;
    }

    public function setAppendBlocks($blocks)
    {
        $this->blocksAppend = $blocks;

        return $this;
    }

    public function setPrependBlocks($blocks)
    {
        $this->blocksPrepend = $blocks;

        return $this;
    }
}
