<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Http\Middleware\RequireTwoFactor;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * The admin panel — TRD §2.1, plan Phase 5.
 *
 * ── C-8, and the largest schedule risk in the project ────────────────────
 *
 * UI brief F-1: Filament ships its own design system, and applying this
 * project's brief to it deeply means fighting the framework and losing most of
 * the productivity that justified choosing it. The plan calls over-customising
 * Filament "the largest schedule risk in the project".
 *
 * So the rule, confirmed as C-8 before any admin UI work: theme with COLOUR,
 * TYPEFACE and RADIUS TOKENS ONLY, and accept Filament's component patterns
 * as they are. The member-facing product carries the full design system; the
 * admin carries the brand.
 *
 * Any request to restyle Filament components deeply is escalated, not
 * absorbed.
 * ─────────────────────────────────────────────────────────────────────────
 *
 * Access is gated three ways, matching the routes Phase 4 established:
 * authenticated, second factor confirmed, and holding a permission the role
 * actually grants. canAccessPanel() on the User model is the outermost gate.
 */
final class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            // No ->login(). Filament then registers no login route of its own
            // and the `auth` middleware redirects to Fortify's (Phase 4).
            // Two login screens for one application would mean two lockout
            // policies, two enumeration surfaces and two places to get 2FA
            // wrong.
            ->colors([
                // UI brief §2.1. Forest green does the structural work; flag
                // green is an identity accent only and never a UI colour.
                'primary' => Color::hex('#046A44'),   // forest-700
                'gray' => Color::hex('#4E5F56'),      // ink-muted
                'success' => Color::hex('#055537'),
                'warning' => Color::hex('#6E4600'),
                'danger' => Color::hex('#8A1F16'),
                'info' => Color::hex('#123A59'),
            ])
            ->font('Archivo')
            ->brandName('ALDAPCON')
            // §5.3 — admin navigation is grouped Overview / Members / Money /
            // Content / Governance. Governance is visible only to Super Admin,
            // and hidden rather than disabled: a greyed item advertises what a
            // Publisher cannot reach.
            ->navigationGroups([
                'Overview',
                'Members',
                'Money',
                'Content',
                'Governance',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                'auth',
                'verified',
                // FR-4.3 — no admin surface without a confirmed second factor.
                RequireTwoFactor::class,
            ]);
    }
}
