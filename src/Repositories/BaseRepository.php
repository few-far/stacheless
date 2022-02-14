<?php

namespace FewFar\Stacheless\Repositories;

use FewFar\Stacheless\Config;

abstract class BaseRepository
{
    use Concerns\TypeRepository;

    /**
     * Creates in instance of the class.
     *
     * @var bool
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }
}
