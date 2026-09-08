<?php

declare(strict_types=1);

namespace App\Domain\Identity\Models;

use Database\Factories\ConsentRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Evidence of lawful basis — Schema §2.8, FR-12.2, AC-F12.
 *
 * APPEND-ONLY. Withdrawal is a new row with granted = false; the current
 * state is the latest row per (email, purpose). Never an update — an updated
 * consent record destroys the evidence of what was agreed and when.
 *
 * Retained indefinitely (Schema §6.4) and NOT removed by erasure (§6.3):
 * these records are what prove the processing was lawful.
 */
final class ConsentRecord extends Model
{
    /** @use HasFactory<ConsentRecordFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    /** @var list<string> */
    protected $fillable = [
        'user_id', 'applicant_id', 'email', 'purpose', 'granted',
        'policy_version_id', 'consent_text_snapshot', 'ip_address', 'user_agent',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['granted' => 'boolean', 'created_at' => 'datetime'];
    }

    /**
     * The current state of one consent: the most recent row wins.
     */
    public static function currentlyGranted(string $email, string $purpose): bool
    {
        return (bool) (self::query()
            ->where('email', $email)
            ->where('purpose', $purpose)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->value('granted') ?? false);
    }
}
