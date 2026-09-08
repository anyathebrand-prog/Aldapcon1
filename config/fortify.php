<?php

declare(strict_types=1);

use Laravel\Fortify\Features;

/*
 * Laravel Fortify — TRD §2.3, FR-4.1 to FR-4.5.
 *
 * Fortify supplies the backend for authentication; every view is ours, so the
 * design system applies to the whole journey (A-01 to A-09).
 *
 * Enabled features are exactly the PRD's, and no more. Each one that is off is
 * off for a stated reason, because "it came with the package" is not a reason
 * to expose an authentication surface.
 */

return [
    'guard' => 'web',
    'passwords' => 'users',
    'username' => 'email',
    'email' => 'email',
    'home' => '/portal',

    /*
     * Routes are registered by Fortify; the views are ours (see
     * FortifyServiceProvider). Prefix is empty so URLs match the App Flow
     * screen inventory exactly — /login, /forgot-password, /reset-password.
     */
    'prefix' => '',
    'domain' => null,
    'middleware' => ['web'],

    /*
     * FR-4.5 and AC-F4 — "Six consecutive failed login attempts trigger a
     * temporary lockout."
     *
     * Fortify's limiter name maps to a RateLimiter defined in
     * FortifyServiceProvider, where the six is set. It is not five, and not
     * ten, because AC-F4 names six and the acceptance test counts.
     */
    'limiters' => [
        'login' => 'login',
        'two-factor' => 'two-factor',
    ],

    /*
     * Fortify's own view routes are disabled. Ours are registered explicitly
     * so each screen renders through the Phase 2 layouts rather than a
     * package default.
     */
    'views' => true,

    'features' => [
        // FR-4.2 — password reset by emailed link.
        Features::resetPasswords(),

        // FR-4.2 — email verification.
        Features::emailVerification(),

        /*
         * FR-4.3 — TOTP two-factor, MANDATORY for Super Admin and Admin.
         *
         * confirm: true means a code must be entered before the second factor
         * is considered enrolled. Without it a user can enable 2FA, never
         * scan the QR, and lock themselves out permanently.
         *
         * confirmPassword: true re-asks for the password before enrolling or
         * disabling, so a borrowed session cannot silently remove somebody's
         * second factor.
         */
        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]),

        /*
         * DELIBERATELY DISABLED
         *
         * registration()      — PRD FR-3.1. Accounts are created by the
         *                       signup flow AFTER payment is verified
         *                       (Phase 9a), never by a public register form.
         *                       An open /register would let anybody create an
         *                       account with no payment and no membership,
         *                       which is precisely the state the applicant
         *                       model exists to prevent.
         *
         * updateProfileInformation() — FR-5.2 locks name, email, category and
         *                       status to admin-only editing. The portal's own
         *                       profile screen (Phase 10) handles the fields a
         *                       member may change.
         *
         * updatePasswords()   — in-session password change is App Flow M-11,
         *                       built in Phase 10 with the rest of the portal.
         */
    ],
];
