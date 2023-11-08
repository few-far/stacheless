<?php

namespace FewFar\Stacheless\Cms;

use Exception;
use Illuminate\View\View;
use Statamic\Facades\GlobalSet;

class NotFoundComposer
{
    /**
     * Bind data to the view.
     *
     * @param  \Illuminate\View\View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $entry = GlobalSet::find('site_settings')
            ->inCurrentSite()
            ->augmentedValue('not_found_entry')
            ->value();

        if (!$entry) {
            throw new Exception('No 404 page set in site settings.');
        }

        $viewmodel = app(EntryResponse::class)
            ->viewModel($entry);

        $view->with($viewmodel);
    }
}
