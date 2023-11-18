<?php

namespace FewFar\Stacheless;

class Config
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
    protected function configKey()
    {
        return $this->configKey;
    }

    /**
     * Retreives config values using the config key property as a prefix to all keys given.
     *
     * @param string  $key  used after the configKey
     * @param mixed  $default  fallback used if not set
     * @return mixed
     */
    public function get($key = null, $default = null)
    {
        if (is_null($key)) {
            return Config::get($this->configKey());
        }

        return Config::get($this->configKey() . '.' . $key, $default);
    }

    /**
     * Sets config values using the config key property as a prefix to all keys given.
     *
     * @param string  $key  used after the configKey
     * @param mixed  $value  value to set for the key.
     * @return void
     */
    public function set($key, $value)
    {
        Config::set($this->configKey() . '.' . $key, $value);
    }
}
