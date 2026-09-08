# Audit Corrections — Change Log

**Document:** 09-audit-corrections-log.md
**Applies:** `08-spec-audit.md`
**Date:** 8 September 2026
**Files changed:** `01-prd.md`, `02-trd.md`, `03-app-flow.md`, `04-ui-ux-brief.md`, `05-backend-schema.md`, `06-implementation-plan.md`
**Files unchanged:** `07-change-set-01` and `08-spec-audit` — historical records, deliberately left as they were written

Every edit was applied by exact-anchor match with a uniqueness assertion. Any anchor found zero times or more than once aborted the batch before writing, so no edit landed in an unintended place.

---

## Version state

| File | Was | Now |
|---|---|---|
| `01-prd.md` | 1.0 | **1.1** |
| `02-trd.md` | 1.0 | **1.1** |
| `03-app-flow.md` | 1.0 | **1.1** |
| `04-ui-ux-brief.md` | 1.0 | **1.1** |
| `05-backend-schema.md` | 1.0 | **1.1** |
| `06-implementation-plan.md` | 1.1 | **1.2** |

All six are now one generation. **BC-1 resolved.**

---

## Blocking contradictions

### BC-1 — Version skew *(resolved)*
Change set 01 applied forward into the PRD, App Flow, UI brief and Schema. Verified: every screen the plan references now exists in the App Flow; `F16`, `FR-3.13`, `requires_verification` and `member_documents` now appear in every document that needs them.

### BC-2 — Constraint violation in the activation transaction *(fixed)*
**Schema §4.3.** Deleted `UPDATE payments SET membership_id = … WHERE applicant_id = :id`. That row already carried `applicant_id`; setting `membership_id` made `num_nonnulls` return 2 against a `CHECK … = 1`, which would have rolled back **every signup at the final commit, after payment**.

A registration payment now keeps `applicant_id` for life. The route to the membership is `payments → applicants.membership_id → memberships`. Only renewal payments carry `membership_id` directly. The constraint annotation says so explicitly, and the removed line is documented in place so nobody reintroduces it.

### BC-3 — Abandoned payments *(fixed, three files)*
- **PRD:** FR-3.9 rewritten — a payment record is created at initialisation. New **FR-3.14** adds the scheduled sweep to `abandoned`.
- **App Flow:** J-03 edge case corrected — a pending payment row does exist.
- **Schema:** row-creation rule documented on `payments`, with a caution that `payments.abandoned` and `applicants.abandoned` are different events.
- **Plan:** Phase 8 states the rule; Phase 14 gains the sweep job.

### BC-4 — Forward foreign key *(fixed)*
`events` and `event_registrations` move to **Phase 8**, alongside `payments`. The event interface stays in Phase 12. Noted in the schema at the column and in both phases.

### BC-5 — DSR exports pointed at `media` *(fixed)*
`export_media_id` replaced with `export_path` and `export_expires_at`. An export is transient and does not deserve a row — and it would otherwise have hit `media.alt_text NOT NULL`, forcing filler into an accessibility field on the one artefact made entirely of personal data.

### BC-6 — M-11 session management *(fixed)*
App Flow M-11 reduced to in-session password change. Session management explicitly deferred, with the reason recorded so it is not assumed available.

### BC-7 — D-04 "mark as resolved" *(fixed)*
Action removed. It wrote to no state, and PRD Q4 is unanswered. Recorded as returning when Q4 does.

---

## Important gaps

| ID | Fix | File |
|---|---|---|
| IG-1 | `consent_records` and `policy_versions` added to Phase 3 data; `newsletter_subscribers` and `enquiries` added to Phase 15 | Plan |
| IG-2 | Laravel Scout removed. Postgres full-text queried directly — Scout's database driver never reads a `tsvector` column | TRD ×2, Plan |
| IG-3 | `payment_webhook_events.payment_id` FK added; ERD now matches the schema | Schema |
| IG-4 | `events.status` given its own `publication_status` enum. Events have no scheduled state and no requirement for one | Schema |
| IG-5 | Separate dompdf print variant of the record panel specified; built in Phase 8, not the Phase 2 component library. New flag **F-14** | UI, Plan |
| IG-6 | Bottom tabs named: Dashboard, Payments, Events, More | UI |
| IG-7 | "Add to calendar" flagged as **G-20** — specify it in FR-8 and Phase 12, or remove it | App Flow |
| IG-8 | Payment re-verification added to Phase 13 | Plan |
| IG-9 | FR-12.1 reworded to first-party cookie persistence, matching the schema's deliberate decision not to store visitor consent server-side | PRD |
| IG-10 | NFR 6.4 restated as best-effort with the reasoning, so Phase 17 no longer carries an unmeetable gate | PRD |
| IG-11 | Dark mode added to PRD §3.2 exclusions | PRD |
| IG-12 | FR-9.7 now names all four roles, including `member` | PRD |
| IG-13 | `slug_redirects` cleanup added to retention and to Phase 14 | Schema, Plan |

---

## Naming

| ID | Applied |
|---|---|
| N-1 | `ALDAPCON` standardised across all six. One `Aldapcon` remains, in the PRD header, explicitly marked as the original brief's form |
| N-3 | `requires_licence_number` → `requires_verification` everywhere, including the validation note that referenced it indirectly |
| N-4 | "Membership Record block" → **"Membership Record panel"** across UI brief and Plan. PRD §3.2 now distinguishes it from the excluded downloadable membership card |
| N-6 | **52 bare sub-requirement lines in the PRD prefixed with `FR-`.** Cross-references across the corpus are now mechanically verifiable, which they were not |
| N-7 | "paid, awaiting registration" → **"awaiting registration"** |
| N-8 | "Prospective member" → **"Applicant"** in PRD §2.1, matching the modelled entity |

### N-2 withdrawn — false positive

The audit flagged `licence` / `license` as inconsistent. On re-inspection the PRD's usage is **already correct British English**: `licence` as noun ("NDPC licence number"), `licensed` as adjective ("licensed DPCO"). No change made, and the audit finding is wrong rather than the document.

---

## Substantive changes worth re-reading

Three edits changed meaning rather than wording:

**PRD §8.2 metrics.** "Applications requiring manual admin intervention < 10%" was unachievable once every verifying application requires manual intervention by design. Replaced with three metrics, two of which are about **decision speed**: median under 2 working days, zero applications waiting over 5 days. Those matter more than they look — a verification queue nobody works turns the fastest path in the product into its slowest, and the applicant has already paid.

**Schema §4.3b.** The activation transaction is now split into T3a (registration, no membership, no number), T3b (approval, allocates the number) and T3c (rejection, touches no counter). Two consequences are stated explicitly: rejected applications consume no membership number, and the membership term runs from **approval**, not payment — an applicant who waits five days for a decision should not lose five days of membership. If the association would rather it run from payment, that is a one-line change.

**TRD §11.7.** New section on the certificate upload as the only file-upload surface in the member-facing product, and therefore the only path by which an outsider can place a file on the server.

---

## Still open

Nothing in the audit is outstanding. Four decisions still gate later phases, unchanged by this work:

| ID | Question | Gates |
|---|---|---|
| **B-4** | Paystack KYC | Phase 8, launch. Longest lead time in the project |
| **B-5** | Email provider | Phase 7. Needs DNS propagation and warm-up |
| **B-6 / PRD Q12** | Rejection refund policy | Phase 9b go-live. J-11 has no copy and D-23's reject action has no financial consequence without it — and the terms belong on the payment screen, before anyone pays |
| **B-3** | Real categories and fees | Phase 6 content, Phase 9 go-live |

Three lesser ones are flagged in place and do not block: **G-17** (verification SLA), **G-18** (certificate retention sign-off), **G-20** (add-to-calendar — specify or remove).

---

## Next

Phase 1 scaffold is delivered and awaits `./bin/setup.sh` on your machine. **Phase 2 — design system and layout shell** is next, and now includes the file-upload control and the fifth status treatment added by these corrections.
