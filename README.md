# ALDAPCON Platform

Public website and member platform for **ALDAPCON** — the Association of Data Protection Compliance Organizations of Nigeria.

Build status: **Phase 1 (Foundation)**. No association-facing feature exists yet.

---

## The documents are the source of truth

Read these before changing anything. Where code and document disagree, the document wins until the document is changed.

| Document | Authority over |
|---|---|
| [01-prd.md](01-prd.md) | Product scope. What is in V1 and — just as binding — what is not (§3.2) |
| [02-trd.md](02-trd.md) | Technical decisions. Stack, hosting, security, testing strategy |
| [03-app-flow.md](03-app-flow.md) | Navigation, screens and every state each screen must ship |
| [04-ui-ux-brief.md](04-ui-ux-brief.md) | Visual system, tokens, components, accessibility |
| [05-backend-schema.md](05-backend-schema.md) | Tables, constraints, transactions, retention |
| [06-implementation-plan (1).md](06-implementation-plan%20(1).md) | Build order, phase by phase |
| [09-audit-corrections-log.md](09-audit-corrections-log.md) | What changed in the last correction pass, and why |

Two referenced documents are **not in this repository**: `07-change-set-01-certificate-verification.md` and `08-spec-audit.md`. Their substance survives in the five approved documents, but the originals are missing.

---

## Setup

Two supported paths. They produce the same stack — PHP 8.3, PostgreSQL 16, Redis, nginx (TRD §14) — and the repository works with either.

### A. Native Ubuntu (recommended)

The primary path. TRD §10: *"If Docker is unfamiliar territory, plain PHP-FPM on the host is entirely legitimate here and one less thing to debug during an incident."*

Ubuntu 24.04 LTS ships the whole stack from its own repositories — PHP 8.3, PostgreSQL 16, Redis 7, nginx — with no PPAs and no version pinning. It is also the production target (TRD §1.1), so what you install here is what Phase 17 provisions in Lagos.

On Windows, work inside WSL 2:

```powershell
wsl --install -d Ubuntu-24.04
```

Then, inside Ubuntu:

```bash
git clone <repository-url> ~/aldapcon
cd ~/aldapcon
./bin/setup-native.sh
```

Clone into the WSL filesystem (`~/`), not `/mnt/c/...`. Cross-filesystem I/O makes Composer and Vite several times slower.

The script installs the packages, starts PostgreSQL and Redis, creates the `aldapcon` and `aldapcon_test` databases, installs dependencies, generates `APP_KEY`, migrates and verifies. It is idempotent.

Run the three processes that mirror the production topology:

```bash
php artisan serve --host=0.0.0.0 --port=8000    # web
php artisan queue:work --tries=3 --backoff=5    # worker
php artisan schedule:work                       # scheduler
```

### B. Docker

```bash
git clone <repository-url> aldapcon
cd aldapcon
./bin/setup.sh
```

Needs Docker Desktop with Compose v2. On Windows its WSL 2 backend must be provisioned — if `docker ps` returns a 500, `wsl --list --verbose` will usually show no `docker-desktop` distribution, and Docker Desktop's first-run setup has not completed.

| | |
|---|---|
| Application | <http://localhost:8000> |
| Mail (Mailpit) | <http://localhost:8025> |

Target for a clean clone on a second machine: **under 15 minutes**, either path.

---

## Everyday commands

Native:

```bash
php artisan test
php artisan test --filter=Infrastructure
./vendor/bin/pint --test          # check    ./vendor/bin/pint to fix
./vendor/bin/phpstan analyse
php artisan aldapcon:verify-environment
```

Docker — the same commands, prefixed:

```bash
docker compose run --rm app php artisan test
docker compose logs -f app worker scheduler
docker compose down          # stop
docker compose down -v       # stop and destroy data
```

Pint, PHPStan and Pest must all pass before a phase is called complete.

---

## What Phase 1 delivers

A running Laravel 12 application with the full local toolchain and green CI. Nothing more — no schema beyond Laravel's own framework tables, no authentication, no public pages.

The four checks the phase exists to prove, all in `tests/Feature/Infrastructure/`:

1. **PostgreSQL 16 is reachable** and is genuinely the driver in use — later phases depend on `CITEXT`, partial unique indexes, `JSONB`, generated `TSVECTOR` columns and `num_nonnulls`, none of which another engine provides.
2. **Redis is the cache, session and queue store.** The session assertion matters beyond this phase: Schema §2.1 records that there is no sessions table *because* sessions live in Redis, which is why App Flow M-11 was deferred.
3. **A dispatched job is consumed by a real worker.** Not `Queue::fake()` — the job goes onto Redis and `queue:work --once` drains it.
4. **`schedule:run` reaches a scheduled command.**

Checks 3 and 4 are the ones worth caring about. The plan names the risk directly: skip them here and you discover in Phase 7 that emails never send. From Phase 11 the entire membership lifecycle — expiry transitions and all four renewal reminders — runs on the scheduler, and a scheduler that has stopped produces no error at all. It produces a membership base where nobody ever expires.

---

## Layout

```
app/
├── Console/Commands/          heartbeat, environment verification
├── Domain/                    TRD §1.2 — four domains, enforced boundaries
│   ├── Content/               pages, posts, events, leadership, FAQs
│   ├── Membership/            applicants, members, categories, lifecycle
│   ├── Payments/              Paystack, webhooks, receipts, reconciliation
│   └── Identity/              accounts, sessions, 2FA, roles, audit log
│       └── {Models,Actions,Events,Listeners,Policies,Data}/
├── Filament/                  admin panel (Phase 5 onward)
├── Http/Controllers/{Public,Portal,Webhooks}/
├── Jobs/  Mail/  Support/
docker/{nginx,php}/            container configuration
tests/{Feature,Unit,Browser,Support}/
```

**The boundary that matters:** membership activation is driven by verified payment events, never by a controller responding to a browser redirect. Payments emits `PaymentVerified`; Membership listens. Cross-domain model imports are prohibited outside declared interfaces.

---

## Conventions

Binding on every phase (plan §2). Not negotiable without an explicit instruction.

1. Money is `BIGINT` **kobo**. Every column and variable carrying an amount is suffixed `_kobo`. **No floats, ever.**
2. Timestamps are `TIMESTAMPTZ` in UTC, rendered Africa/Lagos.
3. Every model has a factory. Every migration is reversible.
4. No `env()` calls outside `config/`.
5. No secrets in code, fixtures or tests. `.env.example` documents keys with empty values.
6. Authorisation is checked server-side on every route, including admin. Hiding a menu item is not authorisation.
7. Mass assignment is guarded on every model.
8. Every user-facing string follows UI brief §11 — sentence case, names the outcome, never "Submit".
9. Every list view ships its empty state in the same commit as the list.
10. Pint, PHPStan and Pest pass before a phase is complete.
11. One phase, one branch, one PR.
12. Never `--force` a migration on production without a preceding dump.

---

## Open decisions

Four answers still gate later phases. They are not blocked on code.

| ID | Question | Gates |
|---|---|---|
| **B-4** | Paystack KYC status | Phase 8 and launch. **Longest lead time in the project — start it now** |
| **B-5** | Email provider, and the residency-versus-deliverability conflict (TRD §11.1) | Phase 7. Needs DNS propagation and warm-up |
| **B-6** | Rejection refund policy | Phase 9b. The terms must appear on J-02 *before* anyone pays |
| **B-3** | Real membership categories and fees | Phase 6 content, Phase 9 go-live. Placeholders suffice for building |

---

## Security

- `.env` is never committed. Production `.env` is generated on the server, `chmod 600`.
- Paystack secret keys are server-side only, never in a template or client bundle.
- Webhook signatures verify HMAC SHA512 over the **raw request body** — reading the parsed body breaks the HMAC.
- Uploaded certificates live outside the web root and are streamed through an authenticated controller. Never a public URL.
- `storage/app/private/` is denied at nginx as well as being outside the document root.

Full detail in TRD §6.
