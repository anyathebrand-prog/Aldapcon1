<?php

declare(strict_types=1);

namespace App\Domain\Identity\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A record that an email was attempted — Schema §2.7, FR-10.1.
 *
 * Metadata only. The body is never stored: a welcome email carries a
 * membership number and a reset email carries a working token, so keeping
 * rendered bodies would be a second copy of every credential the system has
 * ever sent.
 *
 * @property string $status
 */
final class EmailLog extends Model
{
    protected $table = 'email_log';

    public const UPDATED_AT = null;

    /** @var list<string> */
    protected $fillable = [
        'to_email', 'mailable', 'subject', 'related_type', 'related_id',
        'status', 'provider_message_id', 'error', 'sent_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['sent_at' => 'datetime', 'created_at' => 'datetime'];
    }

    public function markSent(?string $providerMessageId = null): void
    {
        $this->update([
            'status' => 'sent',
            'provider_message_id' => $providerMessageId,
            'sent_at' => now(),
        ]);
    }

    /**
     * A failure is recorded, not swallowed.
     *
     * TRD §12 names the worst state in the product: "Payment succeeds,
     * activation fails silently — member paid and got nothing." An email that
     * vanishes into failed_jobs is the same shape of problem, one step
     * removed, and this row is what makes it visible.
     */
    public function markFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            // Truncated: a provider stack trace can run to kilobytes, and the
            // useful part is always at the front.
            'error' => mb_substr($error, 0, 2000),
        ]);
    }
}
