<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\TwoFactorSetupController;
use App\Http\Middleware\RequireTwoFactor;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

/*
 * Phase 2 placeholder. Phase 5 replaces this with the real public pages.
 */
Route::get('/', fn () => view('welcome'))->name('home');

/*
 * A-07 — two-factor enrolment (plan C-4).
 *
 * Behind `auth` so it cannot be reached anonymously, but deliberately NOT
 * behind RequireTwoFactor: that middleware redirects HERE, so guarding this
 * route with it would loop.
 */
Route::middleware(['auth'])->group(function (): void {
    Route::get('/admin/two-factor-setup', TwoFactorSetupController::class)
        ->name('two-factor.setup');
});

/*
 * Admin surface — FR-9.7, AC-F9.
 *
 * Three gates, in order: authenticated, second factor confirmed, and holding
 * a permission that the role actually grants. Filament replaces the
 * placeholder in Phase 5; the middleware stack does not change.
 *
 * The `can:` gate is what makes AC-F9 provable. A Publisher holds no grant
 * touching member or payment data (Phase 3 RoleSeeder), so this refuses them
 * by direct URL rather than by a hidden menu item.
 */
Route::middleware(['auth', 'verified', RequireTwoFactor::class])->group(function (): void {
    Route::get('/admin', fn () => view('admin.placeholder'))
        ->middleware('can:members.view')
        ->name('admin.dashboard');

    Route::get('/admin/members', fn () => view('admin.placeholder'))
        ->middleware('can:members.view')
        ->name('admin.members');

    Route::get('/admin/payments', fn () => view('admin.placeholder'))
        ->middleware('can:payments.view')
        ->name('admin.payments');

    Route::get('/admin/verifications', fn () => view('admin.placeholder'))
        ->middleware('can:verifications.view')
        ->name('admin.verifications');

    Route::get('/admin/audit-log', fn () => view('admin.placeholder'))
        ->middleware('can:audit.view')
        ->name('admin.audit');

    Route::get('/admin/users', fn () => view('admin.placeholder'))
        ->middleware('can:users.manage')
        ->name('admin.users');

    Route::get('/admin/settings', fn () => view('admin.placeholder'))
        ->middleware('can:settings.manage')
        ->name('admin.settings');

    // Publisher's permitted surface. Events are content for this purpose
    // (plan C-6).
    Route::get('/admin/news', fn () => view('admin.placeholder'))
        ->middleware('can:content.manage')
        ->name('admin.news');

    Route::get('/admin/events', fn () => view('admin.placeholder'))
        ->middleware('can:events.manage')
        ->name('admin.events');
});

/*
 * Member portal. Ownership, not permission (Schema §5.1) — a member reaches
 * their own rows through the authenticated user, never through a parameter.
 * Built out in Phase 10.
 */
Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/portal', fn () => view('portal.placeholder'))->name('portal');
});

/*
 * Component gallery — registered only outside production.
 */
if (! App::environment('production')) {
    Route::get('/_gallery', fn () => view('gallery'))->name('gallery');
}
