<?php

declare(strict_types=1);

namespace App\Providers;

use App\Actions\Fortify\ResetUserPassword;
use App\Domain\Identity\Models\LoginAttempt;
use App\Domain\Identity\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;

/**
 * Authentication wiring — FR-4.1 to FR-4.5, AC-F4.
 */
final class FortifyServiceProvider extends ServiceProvider
{
    /**
     * AC-F4 — "Six consecutive failed login attempts trigger a temporary
     * lockout." Not five, not ten. The acceptance test counts.
     */
    private const MAX_LOGIN_ATTEMPTS = 6;

    private const LOCKOUT_MINUTES = 15;

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Fortify does not bind these by default; without it the reset flow
        // falls back to Fortify's own rules and the 12-character minimum plus
        // compromised-password check in Schema §2.1 would silently not apply.
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        $this->registerViews();
        $this->registerRateLimiters();
        $this->registerAuthentication();
    }

    /**
     * Every screen is ours, so the Phase 2 design system covers the whole
     * authentication journey rather than stopping at the marketing site.
     */
    private function registerViews(): void
    {
        Fortify::loginView(fn () => view('auth.login'));                       // A-01
        Fortify::requestPasswordResetLinkView(fn () => view('auth.forgot-password')); // A-02
        Fortify::resetPasswordView(fn (Request $r) => view('auth.reset-password', ['request' => $r])); // A-03
        Fortify::verifyEmailView(fn () => view('auth.verify-email'));          // A-04
        Fortify::twoFactorChallengeView(fn () => view('auth.two-factor-challenge')); // A-06
        Fortify::confirmPasswordView(fn () => view('auth.confirm-password'));
    }

    private function registerRateLimiters(): void
    {
        /*
         * Throttled by email AND IP together (TRD §2.3).
         *
         * By email alone, one attacker locks a known admin out of their own
         * account at will — a denial of service dressed as a security
         * control. By IP alone, a shared office or mobile carrier NAT locks
         * out everybody behind it. The pair is what makes the limit target an
         * attempt rather than a person or a network.
         */
        RateLimiter::for('login', function (Request $request): Limit {
            $email = (string) $request->input('email');

            return Limit::perMinutes(self::LOCKOUT_MINUTES, self::MAX_LOGIN_ATTEMPTS)
                ->by(Str::transliterate(Str::lower($email)).'|'.$request->ip());
        });

        RateLimiter::for('two-factor', fn (Request $request): Limit => Limit::perMinute(5)
            ->by((string) $request->session()->get('login.id')));
    }

    private function registerAuthentication(): void
    {
        /*
         * Authentication is overridden rather than left to Fortify's default
         * so that three rules hold in one place:
         *
         *   1. every attempt is recorded durably (Schema §2.1) — the Redis
         *      limiter is fast but does not survive a cache flush, and a
         *      security investigation needs the table
         *   2. a deactivated account cannot log in, even with valid
         *      credentials
         *   3. the failure message never reveals whether the email exists
         *      (AC-F4 — enumeration must be impossible through login)
         */
        Fortify::authenticateUsing(function (Request $request): ?User {
            $email = (string) $request->input('email');
            $password = (string) $request->input('password');

            /** @var User|null $user */
            $user = User::query()->where('email', $email)->first();

            $succeeded = $user !== null
                && $user->canLogIn()
                && password_verify($password, $user->password);

            // Fortify runs this callback TWICE per request — once in
            // RedirectIfTwoFactorAuthenticatable to find the user, and again
            // in AttemptToAuthenticate. Without this guard a single successful
            // login writes two rows, and login_attempts stops being a count of
            // attempts.
            if (! $request->attributes->getBoolean('aldapcon.attempt_logged')) {
                LoginAttempt::create([
                    'email' => $email,
                    'ip_address' => $request->ip(),
                    'successful' => $succeeded,
                    'user_agent' => Str::limit((string) $request->userAgent(), 400, ''),
                    'attempted_at' => now(),
                ]);

                $request->attributes->set('aldapcon.attempt_logged', true);
            }

            if (! $succeeded) {
                // Return null rather than throwing.
                //
                // Throwing a ValidationException here skips Fortify's own
                // failure path — which is what increments the rate limiter.
                // The lockout in FR-4.5 then never fires, and the bug is
                // invisible because login still refuses the password.
                //
                // Fortify's message is generic for every failure — wrong
                // password, unknown email and deactivated account alike — so
                // returning null keeps AC-F4's enumeration guarantee intact
                // while letting the limiter do its job.
                return null;
            }

            $user->forceFill([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ])->saveQuietly();

            return $user;
        });
    }
}
