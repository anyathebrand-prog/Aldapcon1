<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\TwoFactorSetupController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\MembershipController;
use App\Http\Controllers\Public\NewsController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Public\SearchController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public site — FR-1.1, FR-1.5, FR-1.6
|--------------------------------------------------------------------------
|
| The App Flow screens Phase 5 owns. Membership (P-04) arrives in Phase 6,
| events (P-07, P-08) in Phase 12, contact (P-10) in Phase 15.
|
| Legal pages are served through the same editable Page model as About, so a
| new policy version can be published without a deploy (FR-12.4).
*/
Route::get('/', HomeController::class)->name('home');

Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/news/{slug}', [NewsController::class, 'show'])->name('news.show');

Route::get('/membership', [MembershipController::class, 'index'])->name('membership');
Route::get('/join', [MembershipController::class, 'join'])->name('join');

Route::get('/leadership', [PageController::class, 'leadership'])->name('leadership');
Route::get('/faq', [PageController::class, 'faq'])->name('faq');
Route::get('/search', SearchController::class)->name('search');

/*
|--------------------------------------------------------------------------
| Authentication — Phase 4
|--------------------------------------------------------------------------
*/

/*
 * A-07 two-factor enrolment (plan C-4). Behind `auth` but deliberately NOT
 * behind RequireTwoFactor: that middleware redirects here, so guarding this
 * route with it would loop.
 *
 * Declared before the /{slug} catch-all so an admin cannot create a page
 * whose slug shadows it.
 */
Route::middleware(['auth'])->group(function (): void {
    Route::get('/admin/two-factor-setup', TwoFactorSetupController::class)
        ->name('two-factor.setup');
});

/*
 * Member portal. Ownership, not permission (Schema §5.1). Built out in
 * Phase 10.
 */
Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/portal', fn () => view('portal.placeholder'))->name('portal');
});

/*
|--------------------------------------------------------------------------
| Development only
|--------------------------------------------------------------------------
*/
if (! App::environment('production')) {
    Route::get('/_gallery', fn () => view('gallery'))->name('gallery');
}

/*
|--------------------------------------------------------------------------
| Editable pages, by slug — LAST
|--------------------------------------------------------------------------
|
| Deliberately the final route in the file. It matches any single lowercase
| segment, so declaring it earlier would swallow /news, /portal and the
| Filament panel at /admin.
|
| The admin panel registers its own routes from AdminPanelProvider, which is a
| service provider and therefore loads before this file — but the constraint
| and ordering are kept explicit rather than relying on that.
*/
Route::get('/{slug}', [PageController::class, 'show'])
    ->where('slug', '[a-z0-9-]+')
    ->name('page');
