<?php

declare(strict_types=1);

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

/*
 * Phase 2 placeholder routes.
 *
 * Phase 5 replaces the home route with the real public pages (P-01 to P-14).
 * Nothing here is a product surface.
 */
Route::get('/', function () {
    return view('welcome');
})->name('home');

/*
 * Component gallery — plan Phase 2 completion criterion.
 *
 * Registered only outside production. It is a development surface: it exposes
 * every component and every state, which is useful to a builder and noise to
 * anybody else. Gating it here rather than behind a config flag means there is
 * no setting somebody can get wrong on the live server.
 */
if (! App::environment('production')) {
    Route::get('/_gallery', function () {
        return view('gallery');
    })->name('gallery');
}
