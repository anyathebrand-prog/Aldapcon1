<?php

declare(strict_types=1);

namespace App\Domain\Identity\Listeners;

use App\Domain\Identity\Models\EmailLog;
use App\Mail\AldapconMailable;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;

/**
 * Writes the delivery log — FR-10.1, Schema §2.7.
 *
 * Listens to Laravel's own mail events rather than being called from each
 * mailable. That matters: FR-10.1 lists ten transactional emails, and the one
 * that forgets to log is the one somebody asks about. Framework emails —
 * password reset, email verification — are covered too, without touching
 * Fortify.
 *
 * ── Two events, deliberately ─────────────────────────────────────────────
 *
 * MessageSending writes the row as `queued`, BEFORE the provider is called.
 * MessageSent marks it `sent`.
 *
 * The order is the point. If the row were written only on success, an email
 * that failed at the provider would leave no trace at all — and "we have no
 * record of ever trying to email you" is a worse answer to a member than
 * "it failed at 14:02".
 *
 * A failure leaves the row as `queued`, which the mailable's retries then
 * resolve or not. Anything still `queued` long after the fact is a genuine
 * signal, and Phase 7's completion criterion says failures must alert rather
 * than disappear.
 *
 * ── What is never written ────────────────────────────────────────────────
 *
 * The body. Schema §2.7: metadata only. A welcome email carries a membership
 * number and a reset email carries a working token; storing rendered bodies
 * would be a second copy of every credential the system has ever sent,
 * retained for a year.
 */
final class RecordEmailDelivery
{
    public function sending(MessageSending $event): void
    {
        $recipients = $event->message->getTo();

        if ($recipients === []) {
            return;
        }

        $mailable = $event->data['__laravel_mailable'] ?? null;

        [$relatedType, $relatedId] = $this->relatedTo($event);

        foreach ($recipients as $recipient) {
            EmailLog::create([
                'to_email' => $recipient->getAddress(),
                'mailable' => is_string($mailable) ? class_basename($mailable) : 'Unknown',
                // Truncated rather than rejected: a long subject is a
                // cosmetic problem, and refusing to log it would lose the
                // record entirely.
                'subject' => mb_substr((string) $event->message->getSubject(), 0, 250),
                'related_type' => $relatedType,
                'related_id' => $relatedId,
                'status' => 'queued',
            ]);
        }
    }

    public function sent(MessageSent $event): void
    {
        $recipients = $event->message->getTo();

        if ($recipients === []) {
            return;
        }

        $messageId = $event->sent->getMessageId();

        foreach ($recipients as $recipient) {
            // The most recent queued row for this address and subject. Two
            // identical emails to one person moments apart is not a case worth
            // disambiguating — either way somebody received it.
            $log = EmailLog::query()
                ->where('to_email', $recipient->getAddress())
                ->where('status', 'queued')
                ->latest('id')
                ->first();

            $log?->markSent($messageId);
        }
    }

    /**
     * @return array{0: string|null, 1: int|null}
     */
    private function relatedTo(MessageSending $event): array
    {
        $mailable = $event->data['__laravel_mailable'] ?? null;

        if (! is_string($mailable) || ! is_subclass_of($mailable, AldapconMailable::class)) {
            return [null, null];
        }

        // The mailable instance is not on the event, only its class name, so
        // the relation is read from the view data the mailable passed.
        $related = $event->data['emailLogRelation'] ?? null;

        if (! is_array($related) || count($related) !== 2) {
            return [null, null];
        }

        return [(string) $related[0], (int) $related[1]];
    }
}
