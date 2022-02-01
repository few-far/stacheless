<?php

namespace FewFar\Stacheless;

use EngageInteractive\LaravelConfigProvider\ConfigProvider;

class Config extends ConfigProvider
{
    /**
     * Key to use when retrieving config values.
     *
     * @var string
     */
    protected $configKey = 'vendor.fewfar.stacheless';

    /**
     * Gets key to use when retrieving config values.
     *
     * @var string
     */
    public function configKey()
    {
        return $this->configKey;
    }
}
