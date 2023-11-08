<?php

namespace FewFar\Stacheless\Cms\Http;

use FewFar\Stacheless\Cms\Redirects\Redirect;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Statamic\Facades\Entry;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Site;
use FewFar\Stacheless\RequestUsage\RequestUsage;
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
            app(RequestUsage::class)->load($entry->id());
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

        if (!$entry || $entry->private()) {
            throw new NotFoundHttpException();
        }

        return $entry;
    }
}
