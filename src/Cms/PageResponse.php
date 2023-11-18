<?php

namespace FewFar\Stacheless\Cms;

use FewFar\Stacheless\Cms\Mappers\ViewModelMapper;
use Exception;
use FewFar\Stacheless\Cms\Mappers\Context;
use FewFar\Stacheless\Cms\Mappers\Mapper;
use FewFar\Stacheless\Cms\Mappers\PageMapper;
use Statamic\Facades\GlobalSet;

class PageResponse
{
    /**
     * Lookup by collection.blueprint => mapper
     *
     * @var class-string|null
     */
    protected $mapper = null;
    protected string $view = 'layouts.app';
    protected $entry = null;
    protected $values = null;

    public function getViewName() : string
    {
        return $this->view;
    }

    /**
     * Created the view for the entry.
     *
     * @var \Statamic\Entries\Entry  $entry
     * @var array  $viewmodel
     * @return \Illuminate\Views\View
     */
    public function makeView() : \Illuminate\Contracts\View\View
    {
        $context = $this->makeContext();
        $viewmodel = $this->resolveMapper()
            ->setContext($context)
            ->viewModel();

        return view($this->getViewName(), $viewmodel);
    }

    public function makeContext() : Context
    {
        return $this->makeDefaultContext();
    }

    public function makeDefaultContext()
    {
        return new Context([
            'settings' => GlobalSet::findByHandle('site_settings')
                ->inCurrentSite()
                ->augmented(),
            'entry' => $this->entry,
            'values' => $this->entry?->augmented() ?? $this->values ?? [],
        ]);
    }

    public function setMapper(string $mapper)
    {
        $this->mapper = $mapper;

        return $this;
    }

    public function resolveMapper() : PageMapper
    {
        if (! $this->mapper) {
            throw new Exception('$mapper not set.');
        }

        return app($this->mapper);
    }
}
