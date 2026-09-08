<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Database\Eloquent\Factories\Factory;
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
        // otherwise resolve <x-layouts::public> to views/components/layouts/,
        // so the namespace is registered rather than the directory moved —
        // the plan's structure is the approved one.
        Blade::anonymousComponentPath(resource_path('views/layouts'), 'layouts');

        // Laravel resolves a model's factory by stripping the "App\Models\"
        // prefix, so App\Domain\Identity\Models\User is guessed as
        // Database\Factories\Domain\Identity\Models\UserFactory — which does
        // not exist.
        //
        // The domain structure is required by TRD §1.2, so the resolver is
        // told about it once here rather than every model overriding
        // newFactory(). Factories keep a flat namespace: a model's basename
        // is unique across the four domains, and a second Media or Event
        // would be a naming problem worth having anyway.
        Factory::guessFactoryNamesUsing(
            static fn (string $model): string => 'Database\\Factories\\'.class_basename($model).'Factory'
        );
    }
}
