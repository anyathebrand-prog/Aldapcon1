<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\Identity\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mandatory two-factor for staff — FR-4.3, App Flow G-4 / A-07, plan C-4.
 *
 * FR-4.3 makes 2FA mandatory for Super Admin and Admin but specifies no
 * enrolment step. Without one the first admin login cannot complete: they have
 * no second factor, the challenge cannot be answered, and there is nowhere to
 * set one up. A-07 is the derived screen that closes that gap, and this
 * middleware is what makes it unavoidable.
 *
 * "Mandatory" has to mean redirected, not suggested. A staff member with no
 * confirmed second factor is sent to enrolment and cannot reach any other
 * admin route until they finish — including by typing the URL, which is the
 * only test that matters (plan §2 rule 6: hiding a menu item is not
 * authorisation).
 *
 * Publisher is deliberately NOT covered. FR-9.7 grants Publisher no member,
 * payment or certificate data, so a second factor would protect nothing they
 * can reach, and forcing it would be security theatre with a real cost in
 * onboarding friction.
 */
final class RequireTwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user === null || ! $user->requiresTwoFactor()) {
            return $next($request);
        }

        if ($user->two_factor_confirmed_at !== null) {
            return $next($request);
        }

        // Already on the enrolment screen, or acting on it — let it through,
        // or the redirect loops.
        if ($request->routeIs('two-factor.setup') || $request->is('admin/two-factor-setup*')) {
            return $next($request);
        }

        // Fortify's own 2FA endpoints must stay reachable, otherwise enrolment
        // cannot complete.
        if ($request->is('user/two-factor-*') || $request->is('user/confirm-password*')) {
            return $next($request);
        }

        return redirect()
            ->route('two-factor.setup')
            ->with('status', 'Set up two-factor authentication before continuing.');
    }
}
