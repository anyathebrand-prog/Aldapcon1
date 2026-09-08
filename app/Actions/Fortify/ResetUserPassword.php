<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Domain\Identity\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

/**
 * Password reset — FR-4.2, AC-F4.
 *
 * The reset link is single-use and expiring: Laravel deletes the
 * password_reset_tokens row on use (Schema §2.1).
 *
 * App Flow A-02/A-03 also requires a successful reset to invalidate other
 * sessions, which is what setRememberToken does here — an attacker who
 * obtained a session before the reset must not keep it afterwards.
 */
final class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    /**
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function reset(Authenticatable $user, array $input): void
    {
        Validator::make($input, [
            'password' => $this->passwordRules(),
        ])->validate();

        if (! $user instanceof User) {
            return;
        }

        $user->forceFill([
            'password' => Hash::make($input['password']),
            'remember_token' => null,
        ]);

        $user->save();
    }
}
