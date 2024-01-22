<?php

namespace FewFar\Stacheless\Cms\Http;

use FewFar\Stacheless\Cms\Redirects\Redirect;
use FewFar\Stacheless\RequestUsage\RecordsUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Http\Controllers\FrontendController as StatamicController;

class FrontendController extends StatamicController
{
    /**
     * Handles all URLs.
     *
     * @return string
     */
    public function index(Request $request)
    {
        try {
            $entry = $this->findEntry($request);
            app(RecordsUsage::class)->load($entry->id());
            return $entry;
        } catch (NotFoundHttpException $ex) {
            $response = $this->handleNotFound($request);

            if ($response === null) {
                throw $ex;
            }

            return $response;
        }
    }

    protected function handleNotFound(Request $request)
    {
        return $this->findRedirect($request);
    }

    protected function findRedirect(Request $request)
    {
        $path = ('/' . trim($request->getPathInfo(), '/'));
        $redirect = Redirect::query()
            ->where('enabled', true)
            ->where(function ($query) use ($path) {
                $query->orWhere(function ($query) use ($path) {
                    $query->where('source_type', 'equals');
                    $query->where('source', [ $path ]);
                });

                $query->orWhere(function ($query) use ($path) {
                    $query->where('source_type', 'like');
                    $query->whereRaw('? ILIKE source', [ $path ]);
                });

                $query->orWhere(function ($query) use ($path) {
                    $query->where('source_type', 'regex');
                    $query->whereRaw('? ~* source', [ $path ]);
                });
            })
            ->first();

        DB::table('cms_redirect_logs')->insert([
            'id' => Str::uuid(),
            'url' => $request->url(),
            'path' => $path,
            'created_at' => now(),
            'redirect' => ($redirect)->id ?? null,
        ]);

        return (!$redirect) ? null : redirect($redirect->target);
    }

    protected function findEntry(Request $request)
    {
        $site = Site::current();
        $url = $site->relativePath($request->getPathInfo());

        if (Str::endsWith($url, '/') && Str::length($url) > 1) {
            $url = rtrim($url, '/');
        }

        $entry = Entry::findByUri($url, $site->handle());

        if (!$entry || $this->isHiddenToUser($entry)) {
            throw new NotFoundHttpException;
        }

        return $entry;
    }

    protected function isHiddenToUser(EntryContract $entry)
    {
        if (!$entry->private()) {
            return false;
        }

        if (Auth::user(config('statamic.users.guards.cp'))) {
            return false;
        }

        return true;
    }
}
