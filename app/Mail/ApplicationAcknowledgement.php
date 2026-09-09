<?php

declare(strict_types=1);

namespace App\Mail;

use App\Domain\Membership\Models\Applicant;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Sent when an application is submitted for review — change set 01, FR-3.13.
 *
 * The applicant has PAID and is now waiting on a human. App Flow J-10 exists
 * for the same reason this email does: to "stop a paying applicant feeling
 * abandoned between payment and approval."
 *
 * It states plainly that the money arrived, that the certificate arrived, and
 * that a decision is coming — because the alternative is somebody who has paid
 * an association and heard nothing.
 */
final class ApplicationAcknowledgement extends AldapconMailable
{
    public function __construct(public readonly Applicant $applicant) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'We have received your ALDAPCON application',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: null,
            view: 'mail.application-acknowledgement',
            with: [
                'applicant' => $this->applicant,
                'emailLogRelation' => ['applicant', $this->applicant->getKey()],
                'preheader' => 'Your payment and certificate have been received. A decision follows by email.',
            ],
        );
    }

    /** @return array{0: string, 1: int} */
    public function relatedTo(): array
    {
        return ['applicant', (int) $this->applicant->getKey()];
    }
}
