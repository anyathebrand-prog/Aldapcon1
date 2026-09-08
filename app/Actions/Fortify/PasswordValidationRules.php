<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Validation\Rules\Password;

trait PasswordValidationRules
{
    /**
     * Password rules — Schema §2.1.
     *
     * "password minimum 12 characters, checked against a compromised-password
     * list."
     *
     * uncompromised() checks the k-anonymity range API at
     * api.pwnedpasswords.com: the first five characters of the SHA-1 hash are
     * sent, never the password. That is a third-party call, so it is a
     * processing relationship worth knowing about (TRD §5) — but no personal
     * data leaves, and a member whose password is in a public breach corpus is
     * a worse outcome for this association than a hash prefix in somebody's
     * request log.
     *
     * Twelve, not eight. A data protection association enforcing a weaker
     * password policy than it would advise a client to adopt is the kind of
     * detail an auditor enjoys finding.
     *
     * @return array<int, Rule|array<mixed>|string>
     */
    protected function passwordRules(): array
    {
        return ['required', 'string', Password::min(12)->uncompromised(), 'confirmed'];
    }
}
