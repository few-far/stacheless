<?php

namespace FewFar\Stacheless\Cms\MonkeyPatch;

use FewFar\Stacheless\Cms\EntryResponse;
use Carbon\Carbon;
use Statamic\Entries\Entry as StatamicEntry;

class Entry extends StatamicEntry
{
    public function private()
    {
        if (!$this->blueprint()->hasField('published_at')) {
            return parent::private();
        }

        $published = $this->published();

        if (!$published) {
            return true;
        }

        if (!($value = $this->get('published_at'))) {
            return false;
        }

        $date = Carbon::parse($value, 'UTC');
        $collection = $this->collection();

        if ($collection->futureDateBehavior() === 'private' && $date->isFuture()) {
            return true;
        }

        if ($collection->pastDateBehavior() === 'private' && $date->isPast()) {
            return true;
        }

        return false;
    }

    public function toResponse($request)
    {
        if ($request->ajax()) {
            return response()->json(
                app(EntryResponse::class)
                    ->viewModel($this)
            );
        }

        return response(
            app(EntryResponse::class)
                ->view($this)
                ->render()
        );
    }

    public function toLivePreviewResponse($request, $extras)
    {
        return response(
            app(EntryResponse::class)
                ->view($this)
                ->with([ 'live_preview' => $extras ])
                ->render()
        );
    }
}
