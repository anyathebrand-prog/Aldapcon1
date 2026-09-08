# ALDAPCON Platform — Technical Requirements Document (V1)

**Product:** ALDAPCON — Association of Data Protection Compliance Organizations of Nigeria
**Document:** 02-trd.md
**Companion to:** 01-prd.md (approved, unchanged)
**Version:** 1.1
**Status:** Approved, corrected per `08-spec-audit.md`
**Owner:** Architecture

> **v1.1 incorporates** audit corrections IG-2 (Scout removed) and the certificate-upload surface added by change set 01.

---

## 0. Constraints That Shaped Every Decision

Three constraints were confirmed before this document was written. They drive almost every choice below.

| Constraint | Consequence |
|---|---|
| **Solo builder and maintainer.** One person builds it, ships it, and is on call for it. | Optimise for the least code written and the least infrastructure to operate. Every service added is a service one person must patch, monitor and restore at 2am. Frameworks that generate the boring parts beat frameworks that make you write them. |
| **Member data must be hosted in Nigeria.** | Rules out every managed platform-as-a-service in common use — Vercel, Render, Railway, Fly.io, Supabase, PlanetScale, Heroku — and all three hyperscalers, none of which has a Nigerian region. Leaves self-managed Linux servers in Lagos data centres. Ops burden goes up; managed convenience goes away. |
| **Paystack is the payment gateway.** | Settled. Naira-native, hosted checkout, no PCI scope on our side, well-documented webhooks. |

**These three pull against each other.** Nigeria-only hosting means no managed platform; no managed platform means more operations; more operations lands entirely on one person. The architecture below resolves that tension by choosing a stack that puts as much of the application as possible into one well-trodden framework on one server, rather than distributing it across services that each need separate care.

Section 11 lists, without softening, the PRD requirements this stack cannot fully deliver.

---

## 1. Architecture Overview

### 1.1 Shape

A **single server-rendered monolith on one Linux VPS in a Lagos data centre**, with PostgreSQL and Redis on the same host, fronted by Nginx.

That is the entire production topology. No microservices, no separate API tier, no separate frontend deployment, no message broker beyond Redis, no Kubernetes, no object storage service.

This is the correct architecture for the approved scope. The PRD's stated ceiling is 5,000 members and 500 concurrent users. A single modest VPS handles that with a large margin. Splitting it would add failure modes, deployment steps and cost, and would buy capacity nobody needs and resilience that a solo maintainer cannot actually operate.

```
                        Internet
                            │
                    ┌───────┴───────┐
                    │  DNS (Cloudflare, DNS-only)
                    └───────┬───────┘
                            │ HTTPS
┌───────────────────────────┴────────────────────────────┐
│  VPS — Lagos data centre (Ubuntu LTS)                   │
│                                                          │
│  ┌────────────┐                                          │
│  │   Nginx    │  TLS termination, static assets,         │
│  │            │  rate limiting, security headers         │
│  └─────┬──────┘                                          │
│        │                                                 │
│  ┌─────┴──────────────────────────────────────────┐      │
│  │  Application (PHP-FPM / Laravel)                │      │
│  │  ├─ Public site (server-rendered)               │      │
│  │  ├─ Member portal (authenticated)               │      │
│  │  ├─ Admin back office (Filament)                │      │
│  │  └─ Webhook endpoints (Paystack)                │      │
│  └─────┬───────────────────┬──────────────────────┘      │
│        │                   │                             │
│  ┌─────┴──────┐     ┌──────┴──────┐    ┌───────────────┐ │
│  │ PostgreSQL │     │    Redis    │    │ Queue workers │ │
│  │            │     │ cache,      │    │ + scheduler   │ │
│  │            │     │ session,    │    │ (supervisor)  │ │
│  │            │     │ queue       │    │               │ │
│  └────────────┘     └─────────────┘    └───────────────┘ │
│                                                          │
│  ┌──────────────────────┐  ┌──────────────────────────┐  │
│  │ Media volume         │  │ Self-hosted analytics    │  │
│  │ (uploads, receipts)  │  │ (Umami)                  │  │
│  └──────────────────────┘  └──────────────────────────┘  │
└──────────────┬───────────────────────────────────────────┘
               │ outbound only
     ┌─────────┼──────────────┬─────────────────┐
     │         │              │                 │
  Paystack  Email provider  Backup target   Error monitoring
  (NG)      (see §5.2)      (NG, second     (self-hosted or
                             location)       SaaS — see §5.5)
```

### 1.2 Responsibility boundaries

The monolith is one deployable, but it has four internal domains with enforced boundaries. Keeping these clean is what makes the V2 modules in the PRD (certificates, training, chapters) additive rather than surgical.

| Domain | Owns | Must not |
|---|---|---|
| **Content** | Pages, news posts, events, leadership profiles, FAQs, media | Touch payment or membership state |
| **Membership** | Applicants, members, categories, lifecycle status, membership numbers, renewals | Initiate payments directly; it reacts to payment events |
| **Payments** | Paystack transactions, webhook ingestion, verification, receipts, reconciliation records | Decide membership rules; it emits events |
| **Identity & access** | Accounts, sessions, 2FA, roles, permissions, audit log | Be bypassable by any other domain |

**The single most important boundary:** membership activation is driven by verified payment events, never by a controller responding to a browser redirect. Payments emits `PaymentVerified`; Membership listens. This is what makes FR-3.4 and AC-F3 provable rather than hopeful.

Enforcement for a solo developer is by directory structure and code review discipline, not by separate repositories. `app/Domain/{Content,Membership,Payments,Identity}` with a lint rule preventing cross-domain model imports outside declared interfaces is proportionate. Anything heavier is ceremony.

---

## 2. Technology Choices

### 2.1 Backend and frontend — Laravel, server-rendered

**Choice: PHP 8.3+ with Laravel 12. Blade templates, Livewire 3 for interactive components, Tailwind CSS, Alpine.js, Vite.**

There is no separate frontend application. Public pages, the member portal and the admin panel are all server-rendered from one codebase.

**Why:**

1. **The admin back office is roughly 40% of the PRD's functional surface** (FR-9 alone is eight sub-requirements, plus content management in FR-7 and FR-8). Filament — a Laravel admin panel builder — provides resource CRUD, searchable and filterable tables, CSV export, dashboard widgets, and role-gated navigation from model definitions. For a solo builder this is the difference between shipping in months and shipping in quarters.
2. **Server rendering satisfies the SEO requirement (NFR 6.7) by default**, with no hydration strategy, no meta-tag library, no rendering-mode decisions.
3. **Laravel ships the boring-but-mandatory parts**: authentication, password reset, email verification, TOTP two-factor, queues, a task scheduler, mail with templating, validation, rate limiting, CSRF protection, database migrations. Every one of those is a PRD requirement. None needs to be assembled.
4. **It runs correctly on a plain Linux VPS.** This matters enormously given the residency constraint. Laravel's deployment story assumes exactly the server we are forced onto. Modern JavaScript frameworks assume a platform we cannot use.
5. **Livewire covers the handful of genuinely interactive surfaces** — multi-step signup, admin tables, search-as-you-type — without a second language, a build-time API contract, or client-side state management.

**Rejected alternatives:**

| Alternative | Why rejected |
|---|---|
| **Next.js + Node** | Strong framework, but self-hosting it on an unmanaged Nigerian VPS discards most of what makes it pleasant, and it provides nothing resembling Filament. The admin back office would be hand-built, roughly doubling V1 effort for one person. Reconsider only if the builder's PHP comfort is low — see Q1. |
| **Django + Python** | Django Admin is comparable to Filament for internal tooling but is markedly worse as a customer-facing operations UI, and Django's frontend story would require adding HTMX or a JS framework anyway. Defensible second choice. |
| **WordPress + membership plugins** | Fastest to a demo, worst to live with. Payment, renewal and audit logic ends up spread across plugins nobody controls; the security surface is large; NDPA-grade audit logging and RBAC become plugin-dependent. Wrong for a compliance association. |
| **Rails** | Technically excellent and comparable in productivity. Rejected on ecosystem depth for this specific problem — Filament has no true Rails equivalent — and on Nigerian hiring depth if the project ever needs a second pair of hands. |
| **Headless CMS + separate SPA** | Two deployables, two hosting problems, a CORS surface, an auth-token surface, and worse SEO for zero benefit at this scale. |

### 2.2 Database — PostgreSQL 16

**Why:** Strong constraints and transactional integrity for money and membership state; native full-text search (removing the need for a search service, FR-1.6); `jsonb` for consent records and webhook payloads; excellent `pg_dump` backup and restore ergonomics; robust row-level locking for the membership-number allocation in AC-F3.

**Rejected:** MySQL (workable, weaker full-text and JSON handling); SQLite (single-file simplicity is appealing at this scale, but concurrent writes from web plus queue workers and the absence of good online backup tooling make it wrong for money); any managed cloud database (all outside Nigeria).

### 2.3 Authentication

**Choice: Laravel Fortify — first-party session-based authentication.**

- Email plus password, hashed with bcrypt (Laravel default; Argon2id acceptable).
- Email verification and password reset via signed, single-use, expiring links.
- TOTP two-factor, **mandatory for Super Admin and Admin roles** (FR-4.3), with recovery codes.
- Session cookies: `Secure`, `HttpOnly`, `SameSite=Lax`; sessions in Redis; idle timeout and logout invalidation.
- Login throttling by email plus IP, with temporary lockout (AC-F4).
- **RBAC via `spatie/laravel-permission`**, with three roles: Super Admin, Admin, Publisher (FR-9.7). Authorisation enforced by policies at the controller and Filament resource level — server-side on every request, never by hiding menu items.

**Rejected:** Auth0, Clerk, Firebase Auth, Supabase Auth. All are excellent and all store credentials and identity data outside Nigeria, which the residency constraint forbids. Session auth also avoids the JWT refresh and revocation complexity that a monolith does not need.

### 2.4 Supporting libraries

| Need | Library | Note |
|---|---|---|
| Admin panel | `filament/filament` v4 | The single largest effort saving in this stack |
| RBAC | `spatie/laravel-permission` | FR-9.7 |
| Audit log | `spatie/laravel-activitylog` | FR-9.8 — see §11.6 on "immutable" |
| Backups | `spatie/laravel-backup` | Scheduled encrypted dumps + media |
| PDF receipts | `barryvdh/laravel-dompdf` | AC-F5 receipt download |
| Image handling | `intervention/image` | Responsive derivatives at upload |
| Rich text | Filament's TipTap editor | News and event bodies; sanitised on save |
| Search | **PostgreSQL full-text search queried directly** via a small search service | No external search service, and no Scout. Scout's database driver performs LIKE matching against model attributes — it does not read a `tsvector` column, so pairing it with the generated `search_vector` columns in the schema would leave one of the two unused (audit IG-2) |
| Slugs | `spatie/laravel-sluggable` | Post and event URLs |
| Sitemap | `spatie/laravel-sitemap` | NFR 6.7 |
| CSV export | Filament export action + `league/csv` | UTF-8 BOM for Excel — AC-F9 |
| Test clock | Carbon `setTestNow` (framework built-in) | Required to prove AC-F6 lifecycle transitions |
| HTTP client | Laravel HTTP (Guzzle) | Paystack verification calls |

**Development tooling:** Git and GitHub; Composer and npm; Laravel Sail or Herd locally; Pest for tests; Laravel Pint for formatting; PHPStan/Larastan at level 5+; Laravel Telescope in local and staging only (never production — it records request payloads); Mailpit locally for mail; GitHub Actions for CI.

No library appears in this list that is not tied to a numbered PRD requirement.

---

## 3. Data Model (entities, not schema)

Enough to make the boundaries concrete. Field-level design belongs in implementation.

- **User** — authentication identity; has roles. Every member has one; not every user is a member (admins, publishers).
- **MembershipCategory** — name, applicant type (individual/organisation), eligibility, benefits, annual fee, active flag. Fee changes must never mutate historical payments (AC-F2), so payments store their own amount.
- **Applicant** — created *before* payment from name, email, phone, category. This is what makes FR-3.2 and the "paid, awaiting registration" state (FR-3.8) representable.
- **Membership** — member number, category, status, joined date, expiry date, links to User and Applicant. Status is derived-and-stored, recalculated by the scheduler (FR-6.2).
- **MemberProfile** — the full registration fields (organisation, job title, qualifications, NDPC licence number, location, referral source).
- **Payment** — purpose (registration / renewal / event), amount at time of payment, currency, status, Paystack reference, raw verified payload, related entity. Append-only in practice; corrections are new records.
- **PaymentWebhookEvent** — raw event, signature validity, processing state. Existence of this table is what makes webhook idempotency (AC-F3) testable.
- **Event / EventRegistration** — with member and non-member price fields and capacity.
- **Post**, **Page**, **LeadershipProfile**, **Faq**, **Announcement** — content.
- **ConsentRecord** — consent type, policy version, text snapshot, timestamp, IP (FR-12.2).
- **DataSubjectRequest** — export/erasure requests raised from the portal (FR-12.3).
- **Enquiry** — contact form submissions (FR-11.2).
- **ActivityLog** — actor, action, subject, before/after, timestamp (FR-9.8).

**Membership number allocation** must use a database sequence or a locked counter row inside the activation transaction. Application-level `max(number) + 1` fails the concurrency case in AC-F3 and will eventually issue duplicates.

---

## 4. Key Flows

### 4.1 Signup — pay, then register

```
1. Visitor picks category → submits name, email, phone
2. Applicant record created (status: initiated)
3. Server calls Paystack Initialize Transaction with server-side amount
   from the category — never an amount supplied by the browser
4. Visitor completes payment on Paystack hosted checkout
5. Paystack POSTs webhook → signature verified (HMAC SHA512 over raw
   body using the secret key) → event stored → queued for processing
6. Worker calls Paystack Verify Transaction as second confirmation
7. Payment recorded; PaymentVerified emitted
8. Applicant → status: paid. Receipt email + signed registration link sent
9. Applicant opens link (valid for a long, defined window), completes the
   full registration form
10. Inside one transaction: User created, MemberProfile saved, Membership
    activated, member number allocated, consent recorded
11. Welcome email queued; admin notified
```

Three non-negotiables in that flow: the amount is always derived server-side from the category; activation is triggered only by step 6, never by the browser redirect; and the whole of step 10 commits or rolls back together.

The browser redirect after payment shows a "confirming your payment" state that polls for the verified record. It never itself grants anything.

### 4.2 Membership lifecycle

A scheduled job runs daily to transition Active → Expiring soon (30 days out) → Expired, and to dispatch reminders at 30, 7 and 1 days before expiry and once after. Each reminder writes a sent-record so re-runs cannot double-send (AC-F6).

Renewal date arithmetic differs by timing: before expiry, extend from the old expiry; after expiry, extend from the payment date (FR-6.4). This is the requirement most likely to be implemented wrong and must be covered by tests at both branches.

### 4.3 Event registration

Same payment pipeline as signup, with a different purpose and a capacity check that must hold under concurrency — the capacity decrement and the registration insert belong in one locked transaction, or the last two seats will be sold three times.

---

## 5. External Services — Complete List

Every third party the system touches. Each represents an NDPA processing relationship requiring a data processing agreement, and each one that sits outside Nigeria is a cross-border transfer requiring a documented lawful basis.

| # | Service | Purpose | Personal data leaves Nigeria? | DPA required |
|---|---|---|---|---|
| 1 | **Paystack** | Payment initialisation, verification, webhooks, refunds | Nigerian company; underlying infrastructure not fully disclosed — treat as possible (see §11.2) | Yes |
| 2 | **Nigerian VPS provider** (Lagos data centre) | Compute, storage, network | No — this is the point | Yes, and it must be obtainable in writing |
| 3 | **Transactional email provider** | All emails in FR-10.1 | **Yes — unavoidable** (see §5.2, §11.1) | Yes |
| 4 | **Cloudflare** | Authoritative DNS | No, if DNS-only mode (no proxying) | Not if DNS-only |
| 5 | **Let's Encrypt** | TLS certificates | No | No |
| 6 | **Backup storage** — second Nigerian provider or second facility | Encrypted off-host backups | No, if kept in Nigeria | Yes |
| 7 | **Error monitoring** — self-hosted GlitchTip preferred | Exception capture | No if self-hosted; yes if Sentry SaaS | If SaaS |
| 8 | **Analytics** — self-hosted Umami | Consented web analytics | No | No |
| 9 | **Uptime monitoring** — UptimeRobot or similar | Availability alerting | No — hits public URLs only | No |
| 10 | **Spam protection** — Cloudflare Turnstile or hCaptcha | Contact form, registration | Yes — IP and challenge signals | Yes |
| 11 | **Map** — OpenStreetMap tiles via Leaflet | Contact page | Visitor IP to tile server | Preferred over Google Maps: no cookies, no consent complexity |
| 12 | **GitHub** | Source control, CI | No — no personal data in the repository, ever | No |
| 14 | **Private document storage** (same VPS, private disk) | NDPC certificates uploaded for verification | No | Covered by #2 |
| 13 | **Domain registrar** | `.org.ng` or `.org` | No | No |

**Deliberately not used:** object storage services, CDN for member-facing assets, SMS gateway (not a V1 requirement — add Termii or Sendchamp if SMS is later approved), external search, feature flag services, session replay tools (these are a privacy liability for this client in particular).

### 5.2 The email problem

Every transactional email provider with reliable inbox placement — Postmark, Amazon SES, Resend, Mailgun — is hosted outside Nigeria. Sending a member's name and email address to one is a cross-border transfer.

Three options, none clean:

| Option | Deliverability | Residency |
|---|---|---|
| **A. Postmark or SES** (recommended) | Excellent — meets AC-F10's inbox-placement bar | Cross-border; needs DPA plus documented transfer basis under NDPA s.43 |
| **B. Nigerian provider** (Sendchamp, Termii) | Unproven at this bar; must be tested against Gmail, Yahoo and Outlook before committing | Better residency story |
| **C. Self-hosted Postfix on the VPS** | Poor — a new Nigerian IP will land in spam; reputation takes months and constant attention | Fully compliant |

**Recommendation: Option A, with the transfer documented.** The NDPA does not require local hosting; it requires an adequate-protection basis for transfers. Option C fails AC-F10 outright, and a members' association whose welcome emails go to spam has a broken product. But this is your decision, not mine — see Q3.

---

## 6. Security, Privacy and Secrets

### 6.1 Application security

- HTTPS everywhere; HSTS with a long max-age after a short trial period; TLS 1.2 minimum.
- Security headers: strict Content-Security-Policy (no inline scripts without nonces), `X-Content-Type-Options`, `Referrer-Policy`, `X-Frame-Options`, `Permissions-Policy`.
- CSRF tokens on all state-changing requests (Laravel default; must not be excluded for webhook routes by exempting too broadly — exempt the specific webhook path only).
- Output escaping by default in Blade; rich-text bodies sanitised through an HTML purifier on save, not on render.
- Eloquent parameter binding throughout; no raw SQL with interpolation.
- Mass-assignment guarded on every model — this is the realistic path to a member self-promoting to admin.
- Authorisation policies on every member and admin route, tested by direct URL access (AC-F4, AC-F5).
- Rate limiting on login, password reset, registration, payment initialisation, contact form and webhook endpoints.
- File uploads: extension and MIME allow-list, size caps, re-encoded on upload, stored outside the web root and served through the application, never executed.
- Dependency scanning via Dependabot; `composer audit` in CI.

### 6.2 Server hardening

SSH key-only authentication with password login disabled and root login disabled; UFW allowing only 22, 80, 443; fail2ban; unattended security upgrades; PostgreSQL and Redis bound to localhost only and never exposed publicly; separate non-root system users for the application and workers; automated Let's Encrypt renewal with an expiry alert as a backstop.

### 6.3 Privacy engineering

- **Data minimisation:** only the fields enumerated in FR-3.6 are collected. Any new field needs a stated purpose.
- **Consent:** unbundled checkboxes, unticked by default, storing text version, timestamp and IP (FR-12.2).
- **Cookies:** no non-essential cookies fire before consent. Self-hosted Umami is configured cookieless, which keeps analytics defensible.
- **Retention:** a documented schedule implemented as scheduled jobs — webhook payloads pruned after a defined window, enquiries after a defined period, lapsed member data reviewed on a stated cycle. A retention policy that exists only in a document is not a control.
- **Data subject rights:** portal-initiated export and erasure requests land in an admin queue (FR-12.3). Erasure must handle the conflict between the right to erasure and the statutory need to retain financial records — payments are anonymised, not deleted.
- **Records of processing:** the register of processing activities and the DPIA are association deliverables, not code. Flagged in Q6.
- **Registration with the NDPC:** an association processing the personal data of more than 200 data subjects within six months is likely to fall within "data controller of major importance" and to carry registration and reporting obligations. This is a governance action the association must take; the platform cannot do it for them.

### 6.4 Secrets

- `.env` never committed. `.env.example` documents keys with empty values.
- Production `.env` generated on the server, `chmod 600`, owned by the application user.
- Distinct Paystack test and live keys per environment; the secret key exists server-side only and is never exposed to a template or a client bundle.
- Webhook signature verification uses the secret key over the **raw request body** — reading the parsed body breaks the HMAC and is the classic mistake here.
- Secrets stored in a password manager (Bitwarden or 1Password) as the system of record. GitHub Actions holds only a deploy key and a host fingerprint — no application secrets.
- Documented rotation procedure for `APP_KEY`, database credentials, Paystack keys and email credentials, plus an immediate-rotation runbook for suspected compromise.
- No secrets in logs. Payment payloads are redacted before logging.

---

## 7. Performance and Accessibility

### 7.1 Performance

| Target (from PRD) | Approach |
|---|---|
| LCP < 2.5s on mid-range Android over 4G | Server-rendered HTML; Nginx caching of anonymous public pages; WebP/AVIF derivatives generated at upload with width-appropriate `srcset`; lazy loading below the fold; self-hosted variable fonts with `font-display: swap` and preload; Tailwind purged; minimal JavaScript — Alpine plus Livewire only where needed |
| Page weight < 1.5 MB | Image budget enforced at upload; no carousel libraries, no icon fonts, no jQuery |
| p95 server response < 500 ms | Redis cache for category, navigation and settings lookups; eager loading to kill N+1 queries; indexes on every filter column in FR-9.2; Laravel Octane available later if genuinely needed — not at launch |
| 500 concurrent / 5,000 members | Comfortable on 4 vCPU / 8 GB. Load test before launch to confirm rather than assume |

A Lagos origin serving Nigerian users is a real performance advantage — traffic stays on IXPN rather than routing through Europe. The residency constraint helps here. It hurts for diaspora and international members, discussed in §11.3.

### 7.2 Accessibility (WCAG 2.1 AA)

Semantic HTML first — real headings, real buttons, real form labels tied to inputs. Visible focus indicators, never removed. Full keyboard operability including the entire signup flow. Contrast checked at design time in Figma, not discovered at audit. `alt` text required on media upload — enforced as a validation rule, not a hope. Errors announced programmatically and described in text, not by colour alone. Respect for `prefers-reduced-motion`.

Testing: axe-core in CI for automated violations, plus a manual keyboard-only and screen-reader pass on the signup flow, portal and news post. Automated tools catch perhaps 30–40% of WCAG issues; the manual pass is what makes the AA claim honest.

---

## 8. Testing Strategy

Proportionate to a solo builder: heavy on the paths where a bug costs money or credibility, light everywhere else. Chasing a coverage percentage is not the goal.

**Tier 1 — must be thoroughly tested (money, membership state, access control):**

- Webhook signature verification: valid, invalid, missing, and replayed signatures.
- Webhook idempotency: the same event delivered twice creates exactly one payment and one membership (AC-F3).
- Redirect-without-payment does not activate a membership (AC-F3) — the security test that matters most.
- Amount integrity: a tampered client-side amount cannot change what is charged.
- Membership number uniqueness under concurrent activation.
- Lifecycle transitions using a frozen test clock at each boundary: 31, 30, 8, 7, 2, 1, 0 and −1 days from expiry (AC-F6).
- Renewal arithmetic on both branches — before expiry and after expiry.
- Reminder jobs fire once and only once per stage.
- Authorisation: every role against every protected route by direct URL, including a Publisher attempting to reach member and payment data (AC-F4, AC-F9).
- Member A cannot read or modify member B's records under any parameter manipulation.
- Event capacity holds under concurrent registration.

**Tier 2 — feature tests:** registration form validation, profile editing restrictions, content publishing and scheduling, draft inaccessibility by URL, CSV export shape and encoding, receipt PDF contents.

**Tier 3 — browser tests (Playwright or Laravel Dusk), happy paths only:** full signup against Paystack test mode, login and portal navigation, event registration.

**Non-functional checks in CI:** Lighthouse CI budgets on Home, News index and a post; axe-core scan; PHPStan; Pint.

**Manual before launch:** keyboard-only and screen-reader pass; email rendering across Gmail mobile, Gmail web, Outlook and Yahoo, with SPF/DKIM/DMARC verification; a real ₦100 live transaction end to end before opening signups; **a full restore from backup into staging** — an untested backup is not a backup; a load test at target concurrency.

---

## 9. Environments

| | Local | Staging | Production |
|---|---|---|---|
| **Host** | Developer machine (Laravel Sail or Herd) | Same Lagos VPS, separate user, database and virtual host — or a second small VPS | Dedicated Lagos VPS |
| **Data** | Seeded fake data | Seeded fake data. **Never a copy of production member data** | Live |
| **Paystack** | Test keys | Test keys | Live keys |
| **Email** | Mailpit — nothing leaves the machine | Real provider, sandbox or restricted recipients | Live |
| **Access** | — | HTTP basic auth plus `noindex` | Public |
| **Debug** | On | On | **Off** — `APP_DEBUG=false`, verified as a launch gate |
| **Backups** | None | Weekly | Daily, encrypted, off-host |

Running staging on the production VPS as a separate site is acceptable for cost and is what most solo projects should do — provided the databases are genuinely separate and staging cannot reach production credentials. If budget allows a second small VPS, take it; it removes a whole class of accident.

The prohibition on copying production data to staging is not fussiness. Under NDPA, the moment real member data lands in a less-protected environment behind shared basic auth, it is a processing activity you cannot justify.

---

## 10. Deployment

**Approach: Git-based deploy over SSH, zero-downtime via release symlinks.**

```
push to main
   → GitHub Actions: Pint, PHPStan, Pest, npm build
   → on green: SSH to server
   → clone into releases/<timestamp>
   → composer install --no-dev --optimize-autoloader
   → build assets
   → link shared storage and .env
   → php artisan migrate --force
   → config/route/view cache
   → symlink current → new release
   → reload PHP-FPM, restart queue workers
   → keep last 5 releases for rollback
```

**Rollback:** repoint the symlink to the previous release and reload. Database migrations are the exception — always write migrations that can be rolled forward, and **take a database dump immediately before any migration that alters or drops data**.

**Deliberately not used:** Kubernetes, blue-green infrastructure, container orchestration, infrastructure-as-code tooling. One server, one script. Server provisioning should nonetheless be captured in a shell script or Ansible playbook checked into the repository — not for elegance, but because rebuilding the server from scratch after a data-centre failure is a scenario a solo maintainer will otherwise face with no notes.

**Docker:** optional and reasonable for local/staging/production parity. If used, keep it to a plain Compose file — app, nginx, postgres, redis, worker, scheduler. If Docker is unfamiliar territory, plain PHP-FPM on the host is entirely legitimate here and one less thing to debug during an incident.

**Operational cadence:** daily encrypted backups with monthly restore drills; weekly `apt` security updates; monthly dependency updates; uptime monitoring with SMS or WhatsApp alerts; quarterly key rotation.

---

## 11. Requirements This Stack Cannot Fully Satisfy

Flagged plainly, as requested. Each needs an explicit decision — accept, mitigate, or change the requirement.

### 11.1 Nigeria-only residency vs. email deliverability

**Conflict:** NFR residency (your constraint) vs. AC-F10 ("emails land in the inbox rather than spam for Gmail, Yahoo and Outlook test accounts").

There is no email provider that is both Nigeria-hosted and proven at that deliverability bar. Self-hosting mail from a fresh Nigerian VPS IP will fail AC-F10 for months and will consume maintainer attention indefinitely. **One of these two requirements must give.** Recommendation in §5.2.

### 11.2 End-to-end residency is not actually achievable

Even with Nigerian hosting, personal data reaches Paystack (a Nigerian company whose underlying infrastructure is not fully disclosed and plausibly includes overseas cloud), the email provider, and the spam-protection service. **"All member data stays in Nigeria" will not be a truthful statement in the privacy policy.** For this client, of all clients, the privacy policy must describe transfers accurately.

Worth knowing: **the NDPA does not mandate local hosting.** Section 43 permits transfers where the recipient jurisdiction offers adequate protection or appropriate safeguards are in place — standard contractual clauses, binding corporate rules, codes of conduct, certification, or the data subject's informed consent. The GAID 2025 adds a Cross-Border Data Transfer Instrument mechanism. Nigeria-only hosting is a defensible *policy* choice and a strong signal for an association of compliance organisations, but it is a stricter standard than the law sets, and it is being paid for in reliability and operational burden. Keep it if the signalling value is worth that. Just choose it knowingly.

### 11.3 99.5% uptime on a single VPS

99.5% permits about 3.6 hours of downtime per month. A single server in a single Lagos facility, maintained by one person, with no automatic failover, realistically lands between 99.0% and 99.5% — and a single bad incident during a working week blows the month. Nigerian data centres also carry power and transit risks that the better facilities mitigate but do not eliminate.

Options: (a) restate the target as best-effort and be honest with the association; (b) add a standby server and a load balancer, which roughly doubles cost and adds a topology one person must maintain; (c) accept it. **Recommendation: (a) for V1.** True high availability is not a V1 problem for a membership site.

Related and equally real: **the bus factor is one.** If the maintainer is unreachable, nothing gets restored and no breach gets notified within statutory timelines. That is an organisational risk, not a technical one, and it does not have a technical fix.

### 11.4 Performance for international members

A Lagos-only origin with no CDN is fast in Nigeria and slow from London or Toronto. The PRD assumes a predominantly Nigerian membership (A4), so this is acceptable — but if international membership is later prioritised, either a CDN for static assets only (personal data never cached) or an overseas edge becomes necessary, and the second reopens §11.2.

### 11.5 Managed-service conveniences we lose

No managed database backups, no point-in-time recovery, no automatic patching, no one-click restore, no built-in DDoS absorption, no autoscaling. Each becomes a manual procedure that must be written down and rehearsed. This is the real, recurring cost of the residency constraint, and it is paid every month rather than once.

### 11.6 "Immutable" audit log

FR-9.8 specifies an immutable audit log. A database table is not immutable to anyone holding database or root access — which, in a solo setup, is the same person the log exists to record. It can be approximated: append-only application logic, revoked `UPDATE`/`DELETE` grants for the application role on that table, and log shipping to a separate host with different credentials. **That approximation is worth building; calling it immutable is not honest.** Genuine immutability needs an external append-only log service, which conflicts with §11.2.

### 11.7 The certificate upload surface

Change set 01 introduces the only file upload in the member-facing product. It is therefore the only path by which an outside party can place a file on the server, and the natural first target for anybody probing the application.

Mitigations are mandatory, not advisory: MIME and extension allow-list, size cap, re-encoding or sanitising on receipt, storage outside the web root with no execution permission, `sha256` recorded at upload, and authenticated streaming through a controller that checks a policy. None of this is exotic; all of it is easy to omit under time pressure.

### 11.8 WCAG 2.1 AA

Achievable with this stack, but no stack delivers it. It is a design-and-discipline outcome verified by manual testing. Flagged so it is not assumed to be handled by a library.

---

## 12. Risks

| Risk | Impact | Likelihood | Mitigation |
|---|---|---|---|
| Paystack KYC not completed before build finishes | Blocks launch entirely — nothing else matters if payment cannot go live | Medium-high | Start today, in parallel with design. This is the longest lead-time item in the project |
| Nigerian VPS provider has weak SLA, poor support or no processor DPA | Data-protection exposure plus outages you cannot escalate | Medium | Evaluate providers on written SLA, DPA availability, IXPN peering and support responsiveness before committing. Test support with a real ticket first |
| Webhook handling implemented incorrectly | Lost payments, duplicate memberships, angry members | Medium | Tier 1 tests are non-negotiable; reconciliation view in admin catches strays |
| Payment succeeds, activation fails silently | Member paid and got nothing — the worst possible failure | Medium | Queue retries with backoff, dead-letter alerting, admin "paid, awaiting registration" queue, daily reconciliation against Paystack |
| Solo maintainer unavailable | No recovery, no breach notification, no support | Certain at some point | Documented runbooks, a named backup contact with credential access, provider support contract |
| Scope creep back toward the brief's full feature list | V1 never ships | High — the brief is enormous and every excluded feature has an advocate | The PRD's exclusion list is the contract. Changes go through a written decision, not a WhatsApp message |
| Content not ready at launch | A live site with placeholder bios and no news reads as abandoned | Medium-high | Content deadline two weeks before launch; treat as a launch gate |
| Server compromise | Member data breach at a data protection association — reputationally fatal | Low, high impact | Hardening in §6.2, minimal exposed surface, patching cadence, and a breach process written *before* launch, not after |

---

## 13. Open Questions

**Q1 — PHP comfort (blocking the stack decision).** This document recommends Laravel because it is the highest-leverage choice for a solo builder on a self-managed server. If your working comfort is in JavaScript rather than PHP, say so now: the honest alternative is Next.js with a hand-built admin, which is roughly 40–60% more V1 effort but built in a language you move quickly in. A stack you are fast in usually beats a stack that is theoretically optimal. This is the one decision I would not make for you.

**Q2 — Which Nigerian hosting provider, and can they supply a written DPA?** Lagos-based options with real facilities exist, but they vary sharply in SLA, support and whether they will sign a processor agreement. Under GAID, a processor agreement is not optional. If the shortlisted provider will not sign one, the residency strategy has a hole in it.

**Q3 — Email provider decision.** §5.2 needs your call: deliverability (overseas, documented transfer) or residency purity (unproven deliverability). I recommend the former; the decision is yours because it is a compliance-posture question, not a technical one.

**Q4 — Budget for infrastructure?** The architecture assumes roughly one VPS at 4 vCPU / 8 GB plus backup storage. Whether staging gets its own server, and whether a standby node is affordable, both follow from this.

**Q5 — Is Cloudflare proxying acceptable, or DNS-only?** Proxying gives DDoS protection and caching but terminates TLS outside Nigeria. This document assumes DNS-only, which is the conservative reading of your constraint. Confirm.

**Q6 — Who owns the association's compliance artefacts?** The register of processing activities, DPIA, retention schedule, breach response plan and NDPC registration are association deliverables. The platform supports them; it cannot produce them. If nobody is named, they will not exist at launch — which for this particular client is the most damaging possible gap.

**Q7 — Named backup maintainer with credential access?** See §11.3. Not a technical question, but the one most likely to matter in a crisis.

**Q8 — Does staging get its own server?** Follows from Q4. Separate is safer; shared is acceptable if databases and credentials are genuinely isolated.

---

## 14. Summary of Recommended Stack

| Layer | Choice |
|---|---|
| Language / framework | PHP 8.3+, Laravel 12 |
| Frontend | Blade + Livewire 3, Tailwind CSS, Alpine.js, Vite — server-rendered |
| Admin | Filament v4 |
| Database | PostgreSQL 16 |
| Cache / queue / session | Redis |
| Auth | Laravel Fortify (session, TOTP 2FA) + spatie/laravel-permission |
| Search | PostgreSQL full-text, queried directly |
| Web server | Nginx + Let's Encrypt |
| Hosting | Single VPS, Lagos data centre, Ubuntu LTS |
| Payments | Paystack (hosted checkout, verified webhooks) |
| Email | Postmark or Amazon SES — pending Q3 |
| Analytics | Self-hosted Umami (cookieless) |
| Monitoring | Self-hosted GlitchTip + external uptime monitor |
| CI/CD | GitHub Actions → SSH deploy, symlink releases |
| Testing | Pest, Playwright/Dusk, Lighthouse CI, axe-core |

Nothing in this list exists to make the stack look sophisticated. Every entry maps to a numbered requirement in the approved PRD, and anything that did not has been left out.
