<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
 * Phase 1 placeholder.
 *
 * Phase 2 replaces this with the layout shell and the 404/500 handlers;
 * Phase 5 adds the real public pages (P-01 to P-14). Nothing here is a
 * product surface.
 */
Route::get('/', function () {
    return view('welcome');
})->name('home');
