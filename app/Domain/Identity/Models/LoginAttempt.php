<?php

declare(strict_types=1);

namespace App\Domain\Identity\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A login attempt — Schema §2.1, FR-4.5, AC-F4.
 *
 * The durable record behind the lockout. The live throttle uses the Redis rate
 * limiter for speed; this is what survives a cache flush and what a security
 * investigation reads. Both must agree on the six-attempt threshold.
 */
final class LoginAttempt extends Model
{
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = ['email', 'ip_address', 'successful', 'user_agent', 'attempted_at'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['successful' => 'boolean', 'attempted_at' => 'datetime'];
    }
}
