<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // The implementation plan's Phase 2 file list places layouts at
        // resources/views/layouts/{public,portal,form}.blade.php. Blade would
        // otherwise resolve <x-layouts.public> to views/components/layouts/,
        // so the namespace is registered rather than the directory moved —
        // the plan's structure is the approved one.
        Blade::anonymousComponentPath(resource_path('views/layouts'), 'layouts');
    }
}
