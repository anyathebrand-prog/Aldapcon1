<?php

declare(strict_types=1);

/*
 * Mail — FR-10.1, FR-10.2, FR-10.3, TRD §5.2.
 *
 * ── B-5 IS STILL OPEN ────────────────────────────────────────────────────
 *
 * TRD §11.1 sets out a conflict that has no clean answer:
 *
 *   "There is no email provider that is both Nigeria-hosted and proven at
 *    that deliverability bar. One of these two requirements must give."
 *
 * Every provider with reliable inbox placement — Postmark, SES, Resend,
 * Mailgun — is outside Nigeria, so using one is a cross-border transfer needing
 * a DPA and a documented basis under NDPA s.43. Self-hosting from a fresh
 * Nigerian VPS IP fails AC-F10 for months.
 *
 * TRD §5.2 recommends the overseas provider with the transfer documented, and
 * is explicit that it is the association's decision, not the builder's.
 *
 * This file is therefore written to be provider-agnostic: SMTP settings come
 * from the environment, so answering B-5 is a DNS and .env change rather than
 * a code change. Nothing here presumes the answer.
 *
 * Locally: Mailpit. Nothing leaves the machine (TRD §9).
 */

return [
    'default' => env('MAIL_MAILER', 'log'),

    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'scheme' => env('MAIL_SCHEME'),
            'url' => env('MAIL_URL'),
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => (int) env('MAIL_PORT', 1025),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            // Generous, because a slow provider handshake must not fail a
            // queued job that would otherwise have succeeded. The worker is
            // waiting, not a member.
            'timeout' => 30,
            'local_domain' => env('MAIL_EHLO_DOMAIN'),
        ],

        // Local and CI. Nothing leaves the process.
        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => ['smtp', 'log'],
            'retry_after' => 60,
        ],
    ],

    /*
     * FR-10.2 — "Every email must be sent from a verified association domain
     * with correct SPF/DKIM."
     *
     * One sender for the whole application. A per-mailable from address is
     * exactly how a domain's authentication quietly stops covering some of its
     * mail, and AldapconMailable forbids subclasses from overriding it.
     */
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'no-reply@aldapcon.org.ng'),
        'name' => env('MAIL_FROM_NAME', 'ALDAPCON'),
    ],

    /*
     * Replies go to a mailbox a person reads.
     *
     * Sending from no-reply@ and offering nowhere to reply is the pattern that
     * makes an association feel unreachable — and App Flow §5 requires every
     * message to leave the reader with a next step.
     */
    'reply_to' => [
        'address' => env('MAIL_REPLY_TO_ADDRESS', 'info@aldapcon.org.ng'),
        'name' => env('MAIL_REPLY_TO_NAME', 'ALDAPCON'),
    ],
];
