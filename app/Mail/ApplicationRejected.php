<?php

declare(strict_types=1);

namespace App\Mail;

use App\Domain\Membership\Models\Applicant;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Sent on rejection — change set 01, FR-3.13.4.
 *
 * The hardest email in the product to write, and the one most likely to be
 * written badly: somebody has paid an association and is being refused.
 *
 * FR-3.13.4 makes the reason mandatory, and the database enforces it, so this
 * email always has something specific to say. App Flow J-11 requires the same
 * things this template carries: a plain statement, the administrator's reason,
 * a route to contact a person, and the refund position.
 *
 * ── The refund position is the open question ─────────────────────────────
 *
 * B-6 / PRD Q12 is UNANSWERED. App Flow G-16 marks J-11 blocked on it, and the
 * plan is blunt: whatever the answer, the terms belong on J-02 BEFORE anybody
 * pays, not in this email afterwards. "An association of compliance
 * organisations collecting a fee and declining the service with no published
 * terms is precisely what it would pull a member up for."
 *
 * Until it is answered this email says what is true — that the association
 * will be in touch about the fee — rather than inventing a policy.
 */
final class ApplicationRejected extends AldapconMailable
{
    public function __construct(public readonly Applicant $applicant) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'About your ALDAPCON application',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: null,
            view: 'mail.application-rejected',
            with: [
                'applicant' => $this->applicant,
                'emailLogRelation' => ['applicant', $this->applicant->getKey()],
                'preheader' => 'We are unable to approve your application at this time.',
            ],
        );
    }

    /** @return array{0: string, 1: int} */
    public function relatedTo(): array
    {
        return ['applicant', (int) $this->applicant->getKey()];
    }
}
