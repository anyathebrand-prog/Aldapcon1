# ALDAPCON Platform — Product Requirements Document (V1)

**Product:** ALDAPCON — Association of Data Protection Compliance Organizations of Nigeria
(*Aldapcon* in the original brief; **ALDAPCON** is the standard form across this specification)
**Document:** 01-prd.md
**Version:** 1.1 (first usable release)
**Status:** Approved, corrected per `08-spec-audit.md`
**Owner:** Product

> **v1.1 incorporates:** change set 01 (certificate verification, PRD Q3 resolved) and audit corrections BC-3, IG-9, IG-10, IG-11, IG-12, N-6, N-8.
> **Requirement numbering:** sub-requirements are written `FR-3.6` throughout, matching how every other document cites them (audit N-6).

---

## 1. Product Overview

### 1.1 What this is

A public website plus member platform for ALDAPCON, the association of licensed Data Protection Compliance Organizations and Data Protection Officers in Nigeria.

V1 does three jobs: it presents the association credibly to the public, it turns interested professionals into paid registered members without manual back-and-forth, and it gives the secretariat one place to see who is a member, who has paid, and what is coming up.

### 1.2 The problem

Today there is no single authoritative record of who belongs to the association. Membership is handled through informal channels — WhatsApp, email, spreadsheets, offline bank transfers — which creates four problems:

1. **No reliable member database.** Nobody can answer "how many active members do we have, and in which category?" with confidence.
2. **Manual, leaky onboarding.** Joining requires a human on the other end. People drop off, payments go unmatched, records go stale.
3. **No credible public presence.** A compliance association without a professional, up-to-date web presence undermines the credibility it exists to confer.
4. **News is scattered.** Data protection developments in Nigeria (NDPA, NDPC directives, enforcement actions) are not gathered anywhere the community can rely on.

### 1.3 What V1 does about it

V1 delivers a self-service join-and-pay flow backed by a real member database, a member portal where members can see and manage their own membership, an events module so the association can run and fill its events, a news/blog section so the association owns the conversation, and an admin back office to run all of it.

V1 deliberately does **not** attempt certification, elections, chapters, a jobs board, or a marketplace. Those depend on a functioning membership base that does not exist yet in digital form.

---

## 2. Users

### 2.1 Primary users

**Applicant (individual DPO)**
A data protection officer, privacy lawyer, or compliance professional who wants the credibility and network of association membership. Time-poor, mobile-first, expects to join and pay in one sitting.

**Applicant (licensed DPCO)**
An organisation licensed by the NDPC to provide compliance services. Registers as an organisational member, usually through a designated staff contact. Cares about the organisation being publicly listed as a member in good standing.

**Existing member**
Already joined. Needs to check their status, retrieve their membership number, register for events, download receipts, and renew before expiry.

**Association administrator / secretariat**
One to three people who run membership operations: reviewing members, reconciling payments, publishing news, creating events, pulling reports for the executive council.

### 2.2 Secondary users

- **Executive council members** — need visibility on membership numbers and revenue; consume reports rather than operate the system.
- **General public / regulators / clients** — read about the association, browse news, look up whether an organisation is a member, and make contact.
- **Content publisher** — a comms person who only publishes news and event listings, with no access to member data.

### 2.3 Core user outcome

> A qualified professional or organisation can discover ALDAPCON, pay, register, and receive a verifiable active membership in a single online session without contacting anyone — and the secretariat sees that membership appear in an accurate, exportable database in real time.

Everything in V1 either directly enables that outcome or is required to make the site credible enough that people trust it enough to pay.

---

## 3. V1 Scope

### 3.1 Must-have features (V1)

| # | Feature | Why it is in V1 |
|---|---|---|
| F1 | Public marketing site (Home, About, Leadership, Membership info, FAQ, Contact) | Nobody pays an association they cannot evaluate |
| F2 | Membership categories & fee display | Prerequisite for the paid signup flow |
| F3 | Pay-then-register signup flow with online payment gateway | The core conversion path |
| F4 | Member account & authentication | Members must be able to return |
| F5 | Member portal (profile, membership ID, status, expiry, category, payment history, receipts) | Delivers the thing the member paid for |
| F6 | Membership renewal | Membership is annual; without renewal, revenue is one-shot |
| F7 | News / blog | Stated purpose of the platform; drives repeat traffic and SEO |
| F8 | Events module — public listing, detail pages, member and non-member registration | Second-largest reason members engage |
| F9 | Admin dashboard & member management | Somebody has to run this |
| F10 | Admin content management (news, events, pages) | Content must not require a developer |
| F11 | Payment records, reconciliation view and receipt generation | Finance and audit necessity |
| F12 | Transactional email notifications | Signup, receipt, renewal reminders, event confirmation |
| F13 | Contact / enquiry form | Lowest-friction inbound channel |
| F14 | Site search (news, events, pages) | Content becomes useless once it accumulates |
| F15 | Legal & privacy pages + consent capture (Privacy Policy, Cookie Policy, Terms) | A data protection association cannot ship without these |
| F16 | **Certificate upload and administrator verification** | Membership in a compliance association must mean something. Unverified self-declared licence numbers would make the register worthless |

### 3.2 Explicitly excluded from V1

These are good ideas that do not belong in the first release. They are recorded so they are not quietly re-added.

- Certificates and certificate verification (issuance, numbering, QR verification, public verify page)
- QR-code digital membership card and public member verification lookup
- Elections and online voting
- Committees and chapters (each with own leadership, events, content)
- Jobs board
- Marketplace and member service listings
- Mentorship programme
- Member community / forum / messaging
- Badges, achievements, gamified recognition
- Training / LMS, courses, CPD tracking, examinations
- Paid publications and research downloads
- Advertising inventory and sponsorship packages sold on-platform
- Native iOS and Android apps; PWA install experience
- AI assistant
- Advanced analytics and revenue forecasting dashboards
- Multi-language support
- Public API for third parties
- Migration of any existing member records held offline
- **Dark mode.** Every colour token in `04-ui-ux-brief.md` assumes a light surface, and introducing it mid-build would invalidate every measured contrast figure (UI brief F-11)
- **Downloadable digital membership card.** Distinct from the **Membership Record panel**, which is an on-screen element of the portal and welcome screen and *is* in V1. The excluded item is a saveable or printable card artefact

### 3.3 Likely later additions (V2 and beyond)

Rough order of value, subject to what V1 data tells us:

1. **V1.1** — Digital membership card + QR public verification; downloadable membership certificate; member-only resource library.
2. **V2** — Training and certification module (courses, exams, CPD), including paid non-member pricing.
3. **V2** — Corporate/multi-seat membership where one organisation manages several employee seats.
4. **V2.5** — Chapters and committees; jobs board.
5. **V3** — Elections and secure online voting; member directory and community features; marketplace.

---

## 4. User Stories

### 4.1 Prospective member

- As a prospective member, I want to see the membership categories, what each costs, and what each includes, so that I can pick the right one before spending money.
- As a prospective member, I want to pay online with a card or bank transfer through the site, so that I do not have to send a transfer and chase somebody for confirmation.
- As a prospective member, I want to complete my registration details immediately after payment succeeds, so that my membership is set up in one sitting.
- As a prospective member, I want an emailed receipt and membership confirmation, so that I have proof of payment for my records or my employer.
- As a prospective member whose payment fails, I want a clear message and a way to retry, so that I do not lose money or my place in the flow.
- As a prospective member who abandons registration after paying, I want to be able to return by email link and finish, so that my payment is not wasted.

### 4.2 Member

- As a member, I want to log in and see my membership number, category, status and expiry date, so that I know where I stand.
- As a member, I want to edit my profile and contact details, so that the association reaches me correctly.
- As a member, I want to view and download my past payments and receipts, so that I can claim expenses or file them.
- As a member, I want to be reminded before my membership expires and renew in a few clicks, so that I do not lapse accidentally.
- As a member, I want to register for an event and see my registrations, so that I can plan attendance.
- As a member, I want to read association news, so that I stay current on Nigerian data protection developments.

### 4.3 Administrator

- As an admin, I want a dashboard showing total, active, expiring and expired members plus recent payments, so that I can report to the executive council without building a spreadsheet.
- As an admin, I want to search and filter the member list by category, status and join date, so that I can find anybody quickly.
- As an admin, I want to open a member record and see their full history, so that I can answer queries.
- As an admin, I want to deactivate, reinstate or correct a membership, so that I can handle exceptions and errors.
- As an admin, I want to export members and payments to CSV, so that finance and the council can work with the data offline.
- As an admin, I want to publish and edit news posts and events without a developer, so that the site stays current.
- As an admin, I want to see who registered for an event and download the attendee list, so that I can run the event.
- As an admin, I want to see every payment with its status and gateway reference, so that I can reconcile against the bank account.

### 4.4 Public visitor

- As a visitor, I want to understand who ALDAPCON is and who leads it, so that I can judge its credibility.
- As a visitor, I want to read news and browse upcoming events, so that I can decide whether this is worth joining.
- As a visitor, I want to register for a public event without being a member, so that I can attend before committing.
- As a visitor, I want to contact the association, so that I can ask a question.

---

## 5. Functional Requirements

### FR-1 — Public website

FR-1.1 The site must provide these pages: Home, About Us, Leadership/Executive Council, Membership, News (index + post), Events (index + detail), FAQ, Contact, Privacy Policy, Cookie Policy, Terms of Use.
FR-1.2 The Home page must surface: association positioning, a primary "Join the association" call to action, the three most recent news posts, and up to three upcoming events.
FR-1.3 The Leadership page must list council members with photo, name, position and short bio, editable by an admin.
FR-1.4 The Membership page must list each membership category with eligibility, annual fee, benefits, and a "Join" action.
FR-1.5 All pages must be reachable from a persistent primary navigation and a footer.
FR-1.6 Site-wide search must return matches across news posts, events and static pages.

### FR-2 — Membership categories

FR-2.1 The system must support multiple membership categories, each with a name, description, eligibility text, benefits list, annual fee, applicant type (individual or organisation), and a **`requires_verification`** flag. When that flag is true the category requires a licence number, a certificate upload, and administrator approval before membership is created.
FR-2.2 Categories and fees must be editable by an admin without a code change.
FR-2.3 A change to a category fee must not alter the amount recorded on any completed payment.

### FR-3 — Signup: pay, then register

FR-3.1 The flow is: select category → enter name, email and phone → pay online → complete full registration form → membership created, or submitted for verification where the category requires it.
FR-3.2 The system must collect the minimum identity fields (name, email, phone, category) before payment so a payment can always be traced to a person.
FR-3.3 Payment must be taken through a hosted online payment gateway supporting card and bank transfer, with at minimum card, bank transfer and USSD where the gateway offers them.
FR-3.4 Membership activation must be triggered only by a verified server-side confirmation from the gateway (webhook or server-to-server verification), never by a browser redirect alone.
FR-3.5 On successful payment, the system must create a pending registration and email the payer a receipt plus a secure link to complete registration.
FR-3.6 The full registration form must capture, at minimum: full name, email, phone, organisation, job title, professional qualifications, NDPC licence number, state/city, and how they heard about the association. **For categories where `requires_verification` is true, the applicant must also upload a copy of their NDPC licence certificate.** Accepted formats: PDF, JPG, PNG. Maximum 8 MB.
FR-3.7 On submission of the registration form, the system must create the member's account and profile. **For categories where `requires_verification` is false**, membership must be activated immediately, a unique membership number issued, and a welcome email sent. **For categories where `requires_verification` is true**, the applicant must enter a pending-verification state, receive an acknowledgement email, and **no membership must be created until an administrator approves**.
FR-3.8 A paid-but-unregistered applicant must be able to return via the emailed link at any time to complete registration. These must appear to admins as a distinct **"awaiting registration"** state.
FR-3.9 Failed or abandoned payments must not create a membership. **A payment record must be created when the transaction is initialised**, so that failed and abandoned attempts are visible to admins as transactions rather than disappearing (audit BC-3).
FR-3.10 The system must prevent duplicate active memberships on the same email address. **The check must run before payment is initiated**, never after.
FR-3.11 Membership numbers must be unique, sequential per category, and never reused. **They must be allocated at the point membership is created** — for verifying categories that is approval, not registration, so a rejected application consumes no number.
FR-3.12 The system must not create a membership for an applicant awaiting verification. A pending applicant holds an account and a profile but no membership record.

**FR-3.13 — Administrator verification**

FR-3.13.1 Applications awaiting verification must appear in a dedicated administrator queue showing applicant details, category, licence number, payment status, uploaded certificate and days elapsed.
FR-3.13.2 An administrator must be able to view or download the uploaded certificate through an authenticated, access-controlled route. Certificates must never be publicly reachable by URL.
FR-3.13.3 An administrator must be able to approve an application, which creates the membership, allocates the membership number, activates it, and sends the welcome email.
FR-3.13.4 An administrator must be able to reject an application with a mandatory reason, which notifies the applicant.
FR-3.13.5 An administrator must be able to request a corrected document without rejecting, returning the applicant to an upload state.
FR-3.13.6 Every approval, rejection and correction request must be written to the audit log with actor, reason and timestamp.

**FR-3.14 — Stale payments**

FR-3.14 Payment records that remain pending beyond a configured interval must be swept to an `abandoned` state by a scheduled job, so the admin payments view reflects reality rather than an accumulating backlog of transactions that will never complete (audit BC-3).

> **Q3 resolved.** An administrator vets an uploaded NDPC certificate before membership is valid, scoped per category by `requires_verification`. Individual DPO categories continue to activate automatically.

### FR-4 — Accounts and authentication

FR-4.1 A member account must be created as part of registration, with email as the login identifier.
FR-4.2 The system must support password login, password reset by emailed link, and email verification.
FR-4.3 Admin accounts must support two-factor authentication.
FR-4.4 Sessions must expire after a period of inactivity and on logout.
FR-4.5 The system must rate-limit login attempts and lock an account temporarily after repeated failures.

### FR-5 — Member portal

FR-5.1 On login, a member must see: membership number, category, status (Active / Expiring soon / Expired / Suspended), join date, expiry date. **An applicant awaiting verification must instead see their pending status, the date they applied, and what happens next. A rejected applicant must see the administrator's reason and a route to contact the association.**
FR-5.2 A member must be able to view and edit their profile. Changes to name, category or membership status must not be self-editable; they require an admin.
FR-5.3 A member must see a chronological list of their payments with date, purpose, amount, status and a downloadable receipt.
FR-5.4 A member must see their event registrations, past and upcoming.
FR-5.5 A member must see member-facing announcements published by an admin.
FR-5.6 A member must be able to renew from the portal when within the renewal window or after expiry.

### FR-6 — Renewal and lifecycle

FR-6.1 Membership runs for 12 months from the activation date.
FR-6.2 Status must transition automatically: Active → Expiring soon (within 30 days of expiry) → Expired (after expiry date).
FR-6.3 Renewal reminder emails must be sent at 30, 7 and 1 days before expiry, and once after expiry.
FR-6.4 A renewal payment must extend the expiry date by 12 months from the previous expiry date if renewed before expiry, or from the payment date if renewed after expiry.
FR-6.5 Expired members must retain account access but lose member-only privileges (member event pricing, member-only announcements).
FR-6.6 An admin must be able to override expiry dates and status with the change recorded in an audit log.

### FR-7 — News / blog

FR-7.1 An admin must be able to create, edit, publish, unpublish and delete posts with title, slug, featured image, rich-text body, category/tags, author and publish date.
FR-7.2 Posts must support draft and scheduled publishing.
FR-7.3 The news index must be paginated and filterable by category/tag.
FR-7.4 Each post must have a shareable URL with correct social preview metadata.
FR-7.5 Visitors must be able to subscribe to a newsletter list from the news section; subscription requires explicit consent and confirmation.

### FR-8 — Events

FR-8.1 An admin must be able to create an event with title, description, date and time, end time, location or virtual link, cover image, speakers, capacity, and registration fee(s).
FR-8.2 Events must support separate member and non-member pricing, including free.
FR-8.3 The public events page must show upcoming and past events; past events must remain accessible.
FR-8.4 A logged-in member must be able to register at the member price; a visitor must be able to register at the non-member price by providing name, email and phone.
FR-8.5 Paid event registration must use the same payment gateway flow, with the same server-side confirmation requirement as FR-3.4.
FR-8.6 Registration must close when capacity is reached or the event date passes.
FR-8.7 Registrants must receive a confirmation email with event details.
FR-8.8 An admin must be able to view, search and export the attendee list per event.

### FR-9 — Admin back office

FR-9.1 The dashboard must show: total members, active members, expiring within 30 days, expired members, applicants awaiting registration, **applications awaiting verification with ageing**, revenue this month, revenue this year, recent payments, and upcoming events with registration counts.
FR-9.2 The member list must support search by name, email, membership number and organisation, and filters for category, status and join date range.
FR-9.3 An admin must be able to open a member record showing profile, membership history, payment history and event registrations.
FR-9.4 An admin must be able to deactivate, reinstate, correct and delete member records, subject to role permissions.
FR-9.5 An admin must be able to export members, payments and event attendees to CSV.
FR-9.6 The payments view must list every transaction with status, amount, purpose, gateway reference and payer, filterable by date and status.
FR-9.7 The system must support four roles: **Super Admin** (everything, including user management, audit log and settings), **Admin** (members, payments, content, verification decisions), **Publisher** (content and events only — no member, payment or certificate data), and **Member** (no administrative permission at all; portal access is granted by record ownership, not by permission).
FR-9.8 All admin actions that change member, payment or role data must be written to an immutable audit log with actor, action, target, timestamp.

### FR-10 — Notifications

FR-10.1 Transactional emails required in V1: payment receipt, registration link, welcome/membership confirmation, password reset, email verification, event registration confirmation, renewal reminders (×4), contact form acknowledgement, admin notification of new member.
FR-10.2 Every email must be sent from a verified association domain with correct SPF/DKIM.
FR-10.3 Marketing emails (newsletter) must be separate from transactional emails and must include an unsubscribe link.

### FR-11 — Contact and enquiries

FR-11.1 The contact form must capture name, email, subject and message, with spam protection.
FR-11.2 Submissions must be emailed to a configured association address and stored in the admin back office.
FR-11.3 The contact page must show the association's address, email, phone and a map.

### FR-12 — Privacy and consent

FR-12.1 A cookie consent banner must allow the visitor to accept or reject non-essential cookies. **The choice must be persisted in a first-party cookie and be re-callable by the visitor at any time.** It must not be stored server-side: identifying an anonymous visitor in order to record their preference not to be tracked would defeat the purpose (audit IG-9).
FR-12.2 The registration flow must capture explicit, unbundled consent for processing and for marketing communications, and record consent text version, timestamp and IP.
FR-12.3 A member must be able to request an export of their data and request deletion from the portal; the request must reach an admin queue.
FR-12.4 Privacy Policy, Cookie Policy and Terms of Use must be published and versioned before launch.

---

## 6. Non-Functional Requirements

### 6.1 Security

- All traffic over HTTPS; HSTS enabled. No mixed content.
- Passwords stored using a modern adaptive hashing algorithm; never logged or emailed.
- Two-factor authentication mandatory for Super Admin and Admin roles.
- Role-based access control enforced server-side on every request, not only in the UI.
- No card data ever touches association infrastructure; payment is handled entirely by a PCI-DSS-compliant gateway.
- Protection against OWASP Top 10 categories, including injection, broken access control, XSS and CSRF.
- Payment webhooks must verify gateway signatures and be idempotent.
- Rate limiting on authentication, registration, payment initiation and contact endpoints.
- Automated daily encrypted backups with a tested restore procedure; retention of at least 30 days.

### 6.2 Privacy and regulatory

- The platform must be designed to comply with the Nigeria Data Protection Act 2023 and NDPC guidance. An association of compliance organisations will be held to a higher standard than most, and its own website is the first thing anyone will audit.
- Data minimisation: collect only fields with a stated purpose in this document.
- A documented data retention schedule, with deletion or anonymisation of records past retention.
- A record of processing activities, and a data processing agreement with every third-party processor (hosting, payment, email).
- Data subject rights (access, rectification, erasure, portability, objection) must be operationally supportable within statutory timelines.
- A defined incident response and breach notification process before launch.
- Privacy by design and by default in all V1 decisions.

### 6.3 Performance

- Largest Contentful Paint under 2.5 seconds on a mid-range Android device over a 4G connection — Nigerian mobile conditions, not office fibre.
- Total page weight for public pages under 1.5 MB; images served responsively and lazily.
- Server response for portal and admin pages under 500 ms at the 95th percentile under expected load.
- The system must handle 500 concurrent users and 5,000 registered members without degradation, with a clear path beyond that.

### 6.4 Availability and reliability

- **Best-effort availability, targeted at 99.5% monthly but not warranted.** A single VPS in a single Lagos facility with no automatic failover, maintained by one person, realistically achieves 99.0–99.5%, and one bad incident in a working week exceeds the monthly budget (TRD §11.3). The association must be told this plainly rather than given a figure the architecture cannot guarantee (audit IG-10).
- Payment confirmation must survive downtime: gateway webhooks must be retried and reconciled, so no successful payment is ever lost.
- Scheduled maintenance announced in advance and outside Nigerian business hours.

### 6.5 Usability and accessibility

- Mobile-first responsive design across 320 px to 1920 px viewports.
- WCAG 2.1 Level AA as the target: contrast, keyboard navigation, focus states, semantic headings, alt text, form labels and error messaging.
- Signup must be completable on a phone in under five minutes.
- All error states must tell the user what happened and what to do next, particularly around payment.

### 6.6 Maintainability and operations

- Content (pages, news, events, leadership, categories, fees, FAQ) must be editable by non-technical admins.
- Staging environment separate from production; no testing against live member data.
- Application and payment error logging with alerting to a named owner.
- Basic web analytics with consent, sufficient to measure the success criteria in Section 8.

### 6.7 SEO and discoverability

- Server-rendered public pages, unique titles and meta descriptions, clean URLs, XML sitemap, robots.txt, structured data for articles and events, and correct social sharing previews.

---

## 7. Acceptance Criteria

### AC-F1 — Public website

- [ ] All pages listed in FR-1.1 exist, are linked from navigation, and render correctly on mobile and desktop.
- [ ] Home page shows the three latest published posts and up to three upcoming events, updating automatically as content changes.
- [ ] An admin can add, edit, reorder and remove a leadership profile, and the change appears publicly without a deploy.
- [ ] Search for a term present in a published post returns that post; search for a term in an unpublished draft returns nothing.
- [ ] Every page returns a 200 status; no broken internal links; a 404 page exists and offers navigation.

### AC-F2 — Membership categories

- [ ] An admin can create a category with name, eligibility, benefits, fee and applicant type, and it appears on the Membership page immediately.
- [ ] Editing a fee changes the price for new payments only; historical payment records retain their original amount.
- [ ] Deactivating a category removes it from the public join flow but preserves existing members in it.

### AC-F3 — Signup and payment

- [ ] Selecting a category and submitting name, email and phone leads to the gateway with the correct amount for that category.
- [ ] A successful payment results in a receipt email and a registration link email within 2 minutes.
- [ ] Membership is only activated after server-side verification of the payment; simulating a redirect back to the success URL without a real payment does not create a membership.
- [ ] Completing the registration form activates the membership, issues a unique membership number, and sends a welcome email.
- [ ] A user who pays and closes the browser can return via the emailed link days later and complete registration successfully.
- [ ] A failed payment shows a clear error, offers retry, and creates no membership.
- [ ] The same webhook delivered twice does not create two memberships or two payment records.
- [ ] Attempting to register a second active membership with an existing member's email is blocked with a clear message.
- [ ] Two members registering simultaneously receive different membership numbers.
- [ ] Admin can see the applicant in "paid, awaiting registration" state before they complete the form.

### AC-F4 — Accounts and authentication

- [ ] A member can log in with their registered email and password.
- [ ] Password reset email arrives within 2 minutes; the link expires after a defined period and works only once.
- [ ] Admin login requires a second factor; access is denied without it.
- [ ] A Publisher-role user attempting to open a member or payment record is denied, both in the UI and by direct URL.
- [ ] Six consecutive failed login attempts trigger a temporary lockout.

### AC-F5 — Member portal

- [ ] Dashboard displays correct membership number, category, status and expiry for the logged-in member and no data belonging to any other member.
- [ ] Changing the URL to another member's record returns an authorisation error, not their data.
- [ ] Editing phone, organisation and job title saves and persists; name, category and status are not editable by the member.
- [ ] Payment history lists every completed payment for that member, and each receipt downloads as a PDF showing association details, member details, amount, purpose, date and reference.
- [ ] Event registrations list is accurate for upcoming and past events.

### AC-F6 — Renewal and lifecycle

- [ ] A membership 25 days from expiry displays as "Expiring soon"; the day after expiry it displays as "Expired". Verified with a test clock.
- [ ] Reminder emails fire at 30, 7 and 1 days before expiry and once after, and each fires only once.
- [ ] Renewing 10 days before expiry sets the new expiry to the old expiry plus 12 months, not payment date plus 12 months.
- [ ] Renewing 40 days after expiry sets the new expiry to payment date plus 12 months and restores Active status.
- [ ] An expired member can log in but is offered non-member pricing on paid events.
- [ ] An admin overriding an expiry date produces an audit log entry naming the admin, the old value and the new value.

### AC-F7 — News

- [ ] An admin can create, save as draft, preview, publish, edit and unpublish a post; drafts are not publicly accessible by URL.
- [ ] A post scheduled for a future time is not visible before that time and appears without manual action after it.
- [ ] The news index paginates correctly beyond one page and filters by category.
- [ ] A post URL shared to WhatsApp, X and LinkedIn shows the correct title, description and image.
- [ ] Newsletter signup requires a confirmation step and records the consent timestamp.

### AC-F8 — Events

- [ ] An admin can create an event with all fields in FR-8.1 and it appears under upcoming events.
- [ ] A logged-in active member sees member pricing; a visitor and an expired member see non-member pricing.
- [ ] Paid event registration completes through the gateway and produces a confirmation email and an attendee record.
- [ ] Free event registration completes without a payment step.
- [ ] Registration is refused once capacity is reached, with a clear message.
- [ ] An event whose date has passed moves to past events and no longer accepts registrations.
- [ ] The attendee CSV export contains every registrant with name, email, phone, member status and payment status.

### AC-F9 — Admin back office

- [ ] Dashboard counts reconcile exactly with the underlying member and payment records when checked manually against a seeded dataset.
- [ ] Searching a member by partial name, email, membership number or organisation returns the correct record.
- [ ] Filtering by category and status returns only matching records, and the count matches the dashboard.
- [ ] The member CSV export opens correctly in Excel and Google Sheets with all expected columns and correct character encoding for Nigerian names.
- [ ] The payments view shows every transaction including failed ones, each with a gateway reference that can be located in the gateway dashboard.
- [ ] Every deactivation, reinstatement, data correction and role change appears in the audit log with actor and timestamp.
- [ ] Role permissions hold under direct URL access, not only through hidden menu items.

### AC-F10 — Notifications

- [ ] Each email in FR-10.1 sends on its trigger, renders correctly on mobile Gmail and Outlook, and contains no broken links or placeholder text.
- [ ] Emails pass SPF and DKIM checks and land in the inbox rather than spam for Gmail, Yahoo and Outlook test accounts.
- [ ] Unsubscribing from the newsletter stops marketing email but does not stop transactional email.

### AC-F11 — Contact

- [ ] A submitted enquiry reaches the configured association inbox within 2 minutes and is stored in the admin back office.
- [ ] The sender receives an acknowledgement.
- [ ] Automated spam submissions are blocked in testing.

### AC-F12 — Privacy and consent

- [ ] The cookie banner appears on first visit, records the choice, and does not fire non-essential scripts when rejected.
- [ ] Consent checkboxes are unticked by default, separate for processing and marketing, and the stored record includes text version, timestamp and IP.
- [ ] A member-initiated data export request creates an admin task and is fulfillable.
- [ ] Privacy Policy, Cookie Policy and Terms are published and linked in the footer and in the signup flow.

### AC-F16 — Certificate verification

- [ ] A category with `requires_verification = false` still activates immediately on registration.
- [ ] A category with `requires_verification = true` creates no membership at registration.
- [ ] Registration without a certificate is refused for a verifying category, with the requirement explained rather than surfaced as a surprise error.
- [ ] Uploads reject disallowed types and oversize files, with the limits stated before the file picker.
- [ ] The applicant receives an acknowledgement email stating that review is in progress.
- [ ] The pending applicant can log in and sees a pending state, not an empty or broken portal.
- [ ] The application appears in the administrator queue within seconds.
- [ ] The certificate is not reachable by unauthenticated URL, by direct path, or by a Publisher.
- [ ] Approval creates the membership, allocates the next number **for that category**, activates it, and sends the welcome email.
- [ ] **A rejected application consumes no membership number**, verified by checking the counter before and after.
- [ ] Rejection requires a reason and notifies the applicant.
- [ ] Approve and reject are idempotent — a double-click produces one membership.
- [ ] Approval, rejection and correction requests each write an audit entry.

### AC-NFR — Cross-cutting

- [ ] Lighthouse mobile performance score of 80 or above on Home, News index and a news post.
- [ ] No critical or high findings in an automated security scan before launch.
- [ ] Automated accessibility scan on key pages returns no critical violations; keyboard-only navigation of the signup flow succeeds.
- [ ] A restore from backup into staging is performed successfully before launch.
- [ ] The full signup flow completes on a mid-range Android phone over 4G in under five minutes.

---

## 8. Success Criteria

### 8.1 Launch readiness

V1 is shippable when every acceptance criterion above passes, legal pages are published, at least three real end-to-end paid signups have completed in production, and the secretariat has been trained on the admin back office.

### 8.2 Outcome metrics (first 90 days post-launch)

Targets below are **proposed and need confirmation against the association's actual membership ambitions** — see Section 10, Q5.

| Metric | Proposed target |
|---|---|
| Registered members created through the platform | 100 |
| Signup completion rate (started → paid → registered) | ≥ 60% |
| Paid-but-unregistered applicants outstanding after 7 days | < 5% of payments |
| Payment success rate (attempted → completed) | ≥ 85% |
| **Non-verifying** applications requiring manual intervention | < 10% |
| Median time from submission to verification decision | < 2 working days |
| Applications waiting more than 5 days for a decision | 0 |
| Events published and filled | 2 events, ≥ 70% of capacity |
| News posts published | ≥ 12 (roughly weekly) |
| Admin time spent per new member | < 5 minutes |
| Support enquiries about membership status | Declining month over month |
| Critical production incidents | 0 |

### 8.3 The qualitative test

Three months after launch, the secretariat should be able to answer "how many active members do we have, by category, and how much have we collected this year?" in under a minute, from the dashboard, without opening a spreadsheet. If they still keep a parallel spreadsheet, V1 has failed regardless of the numbers.

---

## 9. Assumptions

These were not stated in the brief. Each is a decision that changes scope if wrong.

**A1.** V1 is responsive web only. No native iOS or Android app, and no PWA install experience.
**A2.** English only. No multi-language support.
**A3.** No existing member records will be migrated. Every member in the system joins through the platform. Existing members join or re-join through the same flow.
**A4.** Membership is paid, annual, and priced in Naira. Members are predominantly Nigeria-based; international payment is not a V1 requirement.
**A5.** Payment precedes registration. Membership activates automatically **only for categories where `requires_verification` is false**. Verifying categories require administrator approval of an uploaded NDPC certificate before any membership is created. This is a confirmed decision, not an assumption (PRD Q3, resolved).
**A6.** Individual and organisational members both register through a single flow that differs only in fields and fee. There is no multi-seat corporate account in V1 — a corporate member is one login.
**A7.** The association can supply all launch content (copy, leadership bios and photos, FAQ answers, initial news posts) before build completion.
**A8.** The association holds, or can obtain, a corporate bank account and can complete payment-gateway KYC before launch. This is frequently the longest lead-time item.
**A9.** The association owns or can register the domain and can grant DNS access for email authentication.
**A10.** One to three staff will administer the platform. Admin usage is low-volume and does not require bulk-operation tooling in V1.
**A11.** Refunds, where they occur, are handled manually through the gateway and recorded by an admin. There is no self-service refund flow.
**A12.** The association will designate a data protection officer for the platform itself and own the compliance documentation (RoPA, retention schedule, breach process). The platform enables compliance; it does not replace governance.
**A13.** Event capacity, pricing and logistics are managed by the association; the platform handles listing, registration and payment only, not ticketing hardware or check-in.

---

## 10. Open Questions

Answers needed before or during build. The first four are blocking.

**Q1 — Membership categories and fees (blocking).** What exactly are the categories, who is eligible for each, and what does each cost per year? The brief lists possible tiers generically. We need the association's actual approved schedule. Placeholder categories can be built, but the public Membership page cannot launch without real numbers.

**Q2 — Payment gateway and account status (blocking).** Which gateway, and has KYC started? This affects available payment methods, fee structure, settlement timing and webhook design.

**Q3 — RESOLVED.** Yes. An administrator vets an uploaded NDPC certificate before membership is valid, scoped per category. See FR-3.13.

**Q12 — What happens to the money when an application is rejected? (blocking)** An administrator can now refuse membership after payment has been taken, and no policy exists. Whatever the answer — full refund, partial, or none — **the terms must appear on the payment screen before anybody pays**, not in the rejection email afterwards. An association of compliance organisations collecting a fee and declining the service with no published terms is precisely what it would pull a member up for.

**Q4 — What happens to a payment where the applicant never registers?** Is it refunded, held indefinitely, or forfeited after a period? A stated policy is needed for the emails and for the admin queue.

**Q5 — What are the association's realistic membership targets?** The success metrics in Section 8.2 are proposed, not agreed. If the realistic pool is 40 licensed DPCOs, a target of 100 members is meaningless.

**Q6 — Who owns content operations after launch?** Weekly news requires a named person. Without one, the news module becomes a stale section that damages credibility rather than building it.

**Q7 — Does V1 need a public list of member organisations?** A searchable list of members in good standing is arguably the association's most valuable public asset and its strongest reason to join. It is currently excluded, but it is cheap relative to its value. Worth reconsidering. It also carries a consent implication: members would need to agree to public listing.

**Q8 — Membership year: rolling or fixed?** A5 assumes 12 months from activation. Many associations run a fixed calendar year with pro-rata joining. This materially changes renewal logic and should be confirmed early.

**Q9 — Is a membership certificate or ID card expected at V1?** Currently excluded. Members frequently expect something tangible immediately after paying, and its absence is a common source of post-launch complaints.

**Q10 — Hosting jurisdiction.** Does the association have a position on member data being hosted outside Nigeria? This is a technical decision, but the policy constraint should be settled now rather than after a stack is chosen.

**Q11 — Launch deadline.** Is there a fixed date — an AGM, conference or regulatory milestone — that V1 must hit? Scope is currently sized on quality, not on a date.
