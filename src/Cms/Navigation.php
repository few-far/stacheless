<?php

namespace FewFar\Stacheless\Cms;

use FewFar\Stacheless\Cms\Mappers\NavigationMapper;
use Illuminate\Support\Facades\Cache;
use Statamic\Events\EntrySaved;
use Statamic\Events\NavTreeSaved;

class Navigation
{
    /**
     * Mapper used when caching navigation.
     *
     * @var \App\Domain\Cms\Mappers\NavigationMapper
     */
    protected $mapper;

    /**
     * Key used in to cache viewmodel.
     */
    protected $cacheKey = 'viewmodels::navigation';

    /**
     * Creates an instance of the mapper.
     *
     * @var \App\Domain\Cms\Mappers\NavigationMapper
     */
    public function __construct(NavigationMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function model()
    {
        return Cache::rememberForever($this->cacheKey, function () {
            return $this->mapper->model();
        });
    }

    public function handleNavTreeSaved(NavTreeSaved $event)
    {
        Cache::forget($this->cacheKey);
        $this->model();
    }

    public function handleEntrySaved(EntrySaved $event)
    {
        Cache::forget($this->cacheKey);
        $this->model();
    }
}
