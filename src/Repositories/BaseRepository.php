<?php

namespace FewFar\Stacheless\Repositories;

use FewFar\Stacheless\Config;

abstract class BaseRepository
{
    use Concerns\TypeRepository;

    /**
     * Determines if Statamic type has a site/locale.
     *
     * @var bool
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }
}
