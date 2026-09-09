<?php

declare(strict_types=1);

use App\Domain\Identity\Models\EmailLog;
use App\Domain\Identity\Models\User;
use App\Domain\Membership\Models\Applicant;
use App\Domain\Membership\Models\Membership;
use App\Mail\ApplicationAcknowledgement;
use App\Mail\ApplicationApproved;
use App\Mail\ApplicationRejected;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

/**
 * Email infrastructure — FR-10.1, FR-10.2, FR-10.3, AC-F10.
 *
 * What is NOT tested here, and cannot be: inbox placement. AC-F10 requires
 * emails to "land in the inbox rather than spam for Gmail, Yahoo and Outlook
 * test accounts", which needs a real provider (B-5), live DNS and real
 * accounts. The plan lists it as manual and non-negotiable, and Phase 7's
 * completion depends on it.
 *
 * These cover the half that is machine-checkable: that mail is queued, logged,
 * addressed from the right domain, and renders.
 */

// ─────────────────────────────────────────────── queueing

it('queues every email rather than sending it in the request', function (string $mailable): void {
    // ShouldQueue is on the base class, not left to each mailable: the one
    // that forgets it blocks a request on a third-party SMTP handshake, and
    // on Nigerian connectivity that turns a signup into a timeout at the
    // moment somebody has just paid.
    expect(is_subclass_of($mailable, ShouldQueue::class))->toBeTrue();
})->with([
    ApplicationAcknowledgement::class,
    ApplicationApproved::class,
    ApplicationRejected::class,
]);

it('retries a failed send with a widening backoff', function (): void {
    // A provider blip must not cost a member their welcome email.
    $mailable = new ApplicationAcknowledgement(Applicant::factory()->create());

    expect($mailable->tries)->toBe(3)
        ->and($mailable->backoff)->toBe([10, 60, 300]);
});

// ─────────────────────────────────────────────── the sender

it('sends every email from one verified association address', function (): void {
    // FR-10.2 — "Every email must be sent from a verified association domain
    // with correct SPF/DKIM." A per-mailable sender is how that quietly stops
    // being true for some of the mail.
    expect(config('mail.from.address'))->not->toBeEmpty()
        ->and(config('mail.from.name'))->toBe('ALDAPCON');
});

it('offers a reply address a person reads', function (): void {
    // Sending from no-reply@ with nowhere to reply is the pattern that makes
    // an association feel unreachable (App Flow §5).
    expect(config('mail.reply_to.address'))->not->toBeEmpty()
        ->and(config('mail.reply_to.address'))->not->toBe(config('mail.from.address'));
});

// ─────────────────────────────────────────────── the delivery log

it('records an email before the provider is called', function (): void {
    // The row is written on MessageSending, not MessageSent. If it were only
    // written on success, a failure would leave no trace — and "we have no
    // record of ever trying to email you" is a worse answer to a member than
    // "it failed at 14:02".
    $applicant = Applicant::factory()->create(['email' => 'ada@example.com']);

    // sendNow, not send: these mailables are queued, and a queued mailable
    // never reaches the transport in a test, so the delivery events that
    // write the log would never fire.
    Mail::to($applicant->email)->sendNow(new ApplicationAcknowledgement($applicant));

    $log = EmailLog::query()->first();

    expect($log)->not->toBeNull()
        ->and($log?->to_email)->toBe('ada@example.com')
        ->and($log?->mailable)->toBe('ApplicationAcknowledgement');
});

it('marks a delivered email as sent', function (): void {
    $applicant = Applicant::factory()->create();

    // sendNow, not send: these mailables are queued, and a queued mailable
    // never reaches the transport in a test, so the delivery events that
    // write the log would never fire.
    Mail::to($applicant->email)->sendNow(new ApplicationAcknowledgement($applicant));

    expect(EmailLog::query()->first()?->status)->toBe('sent');
});

it('never stores the email body', function (): void {
    // Schema §2.7 — metadata only. A welcome email carries a membership number
    // and a reset email carries a working token; storing rendered bodies would
    // be a second copy of every credential the system has ever sent, kept for
    // a year.
    $applicant = Applicant::factory()->create();

    // sendNow, not send: these mailables are queued, and a queued mailable
    // never reaches the transport in a test, so the delivery events that
    // write the log would never fire.
    Mail::to($applicant->email)->sendNow(new ApplicationAcknowledgement($applicant));

    $columns = array_keys(EmailLog::query()->first()?->getAttributes() ?? []);

    expect($columns)->not->toContain('body')
        ->and($columns)->not->toContain('html')
        ->and($columns)->not->toContain('content');
});

it('logs a failure as failed rather than losing it', function (): void {
    $log = EmailLog::create([
        'to_email' => 'ada@example.com',
        'mailable' => 'ApplicationApproved',
        'subject' => 'Your ALDAPCON membership is active',
        'status' => 'queued',
    ]);

    $log->markFailed('Connection to smtp.example.com timed out');

    expect($log->fresh()?->status)->toBe('failed')
        ->and($log->fresh()?->error)->toContain('timed out');
});

it('truncates a very long provider error', function (): void {
    // A provider stack trace can run to kilobytes; the useful part is at the
    // front, and the log is not a place to store megabytes of noise.
    $log = EmailLog::create([
        'to_email' => 'ada@example.com',
        'mailable' => 'ApplicationApproved',
        'subject' => 'Subject',
        'status' => 'queued',
    ]);

    $log->markFailed(str_repeat('x', 5000));

    expect(mb_strlen((string) $log->fresh()?->error))->toBe(2000);
});

// ─────────────────────────────────────────────── rendering

it('renders the acknowledgement without promising membership', function (): void {
    // UI brief §11 never-19 and App Flow J-10 — the Membership Record panel is
    // reserved for issued membership. This applicant has paid and is waiting;
    // showing them a membership number would tell them they are a member when
    // they are not.
    $applicant = Applicant::factory()->create(['full_name' => 'Ada Okonkwo']);

    $rendered = (new ApplicationAcknowledgement($applicant))->render();

    expect($rendered)->toContain('Ada Okonkwo')
        ->and($rendered)->toContain('Your payment has been received')
        ->and($rendered)->not->toContain('Membership number');
});

it('renders the approval with the membership number', function (): void {
    // App Flow J-07 — neither the welcome screen nor this email should be the
    // sole source of the number, because either can be missed.
    $user = User::factory()->create(['full_name' => 'Ada Okonkwo']);
    $membership = Membership::factory()->create([
        'user_id' => $user->id,
        'membership_number' => 'DPCO-2026-00034',
    ]);

    $rendered = (new ApplicationApproved($membership))->render();

    expect($rendered)->toContain('DPCO-2026-00034')
        ->and($rendered)->toContain('Membership number')
        ->and($rendered)->toContain('Valid until');
});

it('renders a rejection with the reason and a route to a person', function (): void {
    // App Flow J-11 — a plain statement, the administrator's reason, and a
    // contact route. FR-3.13.4 makes the reason mandatory and the database
    // enforces it, so there is always something specific to say.
    $user = User::factory()->create();
    $applicant = Applicant::factory()->create([
        'status' => 'rejected',
        'user_id' => $user->id,
        'reviewed_at' => now(),
        'review_reason' => 'The certificate was issued to a different organisation.',
    ]);

    $rendered = (new ApplicationRejected($applicant))->render();

    expect($rendered)->toContain('The certificate was issued to a different organisation.')
        ->and($rendered)->toContain('unable to approve')
        ->and($rendered)->toContain('apply again');
});

it('does not invent a refund policy while B-6 is open', function (): void {
    // App Flow G-16 — the refund position for a rejected application is
    // UNANSWERED, and the plan is blunt that whatever it turns out to be, the
    // terms belong on J-02 before anybody pays.
    //
    // So this email says what is true rather than stating a policy nobody has
    // agreed. If a refund promise ever appears here, it should be because the
    // association decided one, not because a template needed filling.
    $user = User::factory()->create();
    $applicant = Applicant::factory()->create([
        'status' => 'rejected',
        'user_id' => $user->id,
        'reviewed_at' => now(),
        'review_reason' => 'Licence number could not be verified.',
    ]);

    $rendered = (new ApplicationRejected($applicant))->render();

    expect($rendered)->toContain('in touch separately regarding the fee')
        ->and($rendered)->not->toContain('will be refunded')
        ->and($rendered)->not->toContain('non-refundable');
});

it('uses table layout and inline styles so it survives Outlook', function (): void {
    // AC-F10 requires correct rendering on mobile Gmail and Outlook. Outlook
    // on Windows renders through Word, which supports neither flexbox nor
    // grid, and Gmail strips <style> blocks in some clients.
    $applicant = Applicant::factory()->create();

    $rendered = (new ApplicationAcknowledgement($applicant))->render();

    expect($rendered)->toContain('role="presentation"')
        ->and($rendered)->toContain('style="margin:0')
        ->and($rendered)->not->toContain('display:flex')
        ->and($rendered)->not->toContain('display:grid');
});

it('carries no images', function (): void {
    // Most clients block them by default, so an email whose meaning depends on
    // one arrives meaningless — and a payment confirmation is the worst place
    // for that.
    $applicant = Applicant::factory()->create();

    expect((new ApplicationAcknowledgement($applicant))->render())->not->toContain('<img');
});
