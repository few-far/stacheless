<?php

namespace FewFar\Stacheless\Cms\Redirects;

use FewFar\Stacheless\Cms\Redirects\RedirectController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;
use Statamic\Statamic;

class RedirectServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Nav::extend(function ($nav) {
            $nav->tools('Redirects')
                ->route('redirects')
                ->icon('git')
                ->active('redirect')
                ->can('manage redirects');
        });

        Statamic::pushCpRoutes(function () {
            Route::get('redirects', [RedirectController::class, 'view'])->name('redirects');
            Route::get('redirects/create', [RedirectController::class, 'create'])->name('redirects.create');
            Route::post('redirects', [RedirectController::class, 'store'])->name('redirects.store');
            Route::get('redirects/{id}', [RedirectController::class, 'edit'])->name('redirects.edit');
            Route::post('redirects/{id}', [RedirectController::class, 'update'])->name('redirects.update');
            Route::delete('redirects/{id}', [RedirectController::class, 'destroy'])->name('redirects.delete');
        });

        $this->app->booted(function () {
            Permission::register('manage redirects')
                ->label('Manage Redirects');
        });
    }
}
