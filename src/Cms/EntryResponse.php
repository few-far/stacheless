<?php

namespace FewFar\Stacheless\Cms;

use FewFar\Stacheless\Cms\Mappers\ViewModelMapper;
use Exception;
use Statamic\Facades\GlobalSet;

class EntryResponse
{
    /**
     * Lookup by collection.blueprint => mapper
     *
     * @var array<string, class-string<\FewFar\Stacheless\Cms\Mappers\ViewModelMapper>>
     */
    protected $mappers = null;

    /**
     * Created the view for the entry.
     *
     * @var \Statamic\Entries\Entry  $entry
     * @var array  $viewmodel
     * @return \Illuminate\Views\View
     */
    public function makeView($entry, $viewmodel)
    {
        return view($entry->collection()->template(), $viewmodel);
    }

    public function makeViewModel($mapper, $context)
    {
        if (! $mapper instanceof ViewModelMapper) {
            return null;
        }

        return $mapper
            ->setContext($context)
            ->viewModel();
    }

    public function viewModel($entry)
    {
        $mapper = $this->resolveMapper($entry);
        $context = $this->makeContext($entry);

        return $this->makeViewModel($mapper, $context);
    }

    public function view($entry)
    {
        $viewmodel = $this->viewModel($entry);

        return $this->makeView($entry, $viewmodel);
    }

    public function makeContext($entry)
    {
        return [
            'settings' => GlobalSet::findByHandle('site_settings')
                ->inCurrentSite()
                ->augmented(),
            'values' => $entry->augmented(),
            'entry' => $entry,
        ];
    }

    public function resolveMapper($entry)
    {
        if (! $entry) {
            return null;
        }

        if (! $this->mappers) {
            throw new Exception('$mappers lookup not set.');
        }

        $blueprint = $entry->blueprint();

        return app($this->mappers[$blueprint->namespace()][$blueprint->handle()] ?? $this->mappers['default']);
    }
}
