<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Identity\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * A-07 — two-factor enrolment. Derived screen, App Flow G-4, plan C-4.
 *
 * FR-4.3 makes 2FA mandatory for admins but specifies no enrolment step, so
 * this screen is inferred rather than stated. It was confirmed before being
 * built.
 *
 * App Flow A-07 requires four things on it: a QR code, a manual key for
 * anybody who cannot scan, a verification field, and one-time recovery codes
 * that must be acknowledged as saved before proceeding.
 *
 * The acknowledgement is not ceremony. Recovery codes are the only route back
 * for an admin who loses their phone, and an association with one to three
 * staff has nobody else who can restore that access.
 */
final class TwoFactorSetupController
{
    public function __invoke(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        return view('auth.two-factor-setup', [
            'enrolled' => $user->two_factor_confirmed_at !== null,
            // Present only once the user has begun enrolment; Fortify
            // populates the secret when /user/two-factor-authentication is
            // called.
            'hasSecret' => $user->two_factor_secret !== null,
        ]);
    }
}
