<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * The base every ALDAPCON email extends — FR-10.1, FR-10.2, AC-F10.
 *
 * ── Queued, always ───────────────────────────────────────────────────────
 *
 * ShouldQueue is on the base class rather than left to each mailable, because
 * the one that forgets it is the one that blocks a request on a third-party
 * SMTP handshake. On Nigerian connectivity that turns a two-second signup into
 * a timeout at the exact moment somebody has just paid.
 *
 * ── Retries ──────────────────────────────────────────────────────────────
 *
 * Three attempts with a widening backoff. A provider blip must not cost a
 * member their welcome email, and TRD §12 rates "payment succeeds, activation
 * fails silently" the worst possible failure — an email that disappears is a
 * quieter version of the same thing.
 *
 * After the final attempt the job lands in failed_jobs, which Schema §2.9
 * calls "operationally important": it must alert rather than sit quietly.
 * Alerting is Phase 17; the durable record is email_log, written by the
 * listener in Phase 7.
 *
 * ── What subclasses must do ──────────────────────────────────────────────
 *
 * Set a subject and point at a view under resources/views/mail/. They must NOT
 * override the from address: FR-10.2 requires every email to come from the
 * verified association domain with correct SPF and DKIM, and a per-mailable
 * sender is how that quietly stops being true.
 */
abstract class AldapconMailable extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Three attempts. Beyond that the failure is not transient and retrying
     * only delays somebody noticing.
     */
    public int $tries = 3;

    /**
     * Widening backoff in seconds. A provider rate limit or a brief outage
     * clears within a minute; hammering it does not help.
     *
     * @var list<int>
     */
    public array $backoff = [10, 60, 300];

    /**
     * Stop retrying after an hour regardless. An email about a payment that
     * happened yesterday is no longer reassurance; it is confusion.
     */
    public int $timeout = 60;

    /**
     * Categorises the message for the delivery log.
     *
     * Defaults to the class basename, which is what an administrator sees when
     * asked "did the welcome email go out?".
     */
    public function logName(): string
    {
        return class_basename(static::class);
    }

    /**
     * What this email is about, for the log's related_type / related_id.
     *
     * @return array{0: string, 1: int}|null
     */
    public function relatedTo(): ?array
    {
        return null;
    }
}
