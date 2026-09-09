<?php

declare(strict_types=1);

namespace App\Mail;

use App\Domain\Membership\Models\Membership;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Sent on approval — change set 01, FR-3.13.3.
 *
 * Replaces the welcome email on the verifying path. This is the first moment
 * the applicant has a membership number, because the number is allocated at
 * APPROVAL, not registration (Schema §4.3b) — so a rejected application
 * consumes none.
 *
 * App Flow J-07 notes the welcome email "may arrive before or after" the
 * on-screen confirmation, and that neither should be the sole source of the
 * membership number. Hence the number is here in full.
 */
final class ApplicationApproved extends AldapconMailable
{
    public function __construct(public readonly Membership $membership) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your ALDAPCON membership is active',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: null,
            view: 'mail.application-approved',
            with: [
                'membership' => $this->membership,
                'emailLogRelation' => ['membership', $this->membership->getKey()],
                'preheader' => 'Your membership number and expiry date.',
            ],
        );
    }

    /** @return array{0: string, 1: int} */
    public function relatedTo(): array
    {
        return ['membership', (int) $this->membership->getKey()];
    }
}
