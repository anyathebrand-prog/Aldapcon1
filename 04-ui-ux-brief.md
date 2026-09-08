# ALDAPCON Platform — UI/UX Design Brief (V1)

**Product:** ALDAPCON — Association of Data Protection Compliance Organizations of Nigeria
**Document:** 04-ui-ux-brief.md
**Companion to:** 01-prd.md, 02-trd.md, 03-app-flow.md (all approved)
**Version:** 1.1
**Status:** Approved, corrected per `08-spec-audit.md`
**Owner:** Product Design

> **v1.1 incorporates** change set 01 (fifth status, upload control, review panel) and audit corrections IG-5, IG-6, N-4.

> Every contrast ratio quoted in this document has been calculated, not estimated. Section 12 flags the design decisions that conflict with the journey, the technical stack or the accessibility target.

---

## 1. Experience Goal and Visual Direction

### 1.1 The job the design has to do

A data protection officer in Lagos lands on this site on a mid-range Android phone. Within about eight seconds they decide whether this association is a real institution or a hopeful WhatsApp group with a website. If they decide it is real, they will pay ₦X and hand over their professional details. If they decide it is not, they close the tab and nothing else in this project matters.

**The experience goal: earn enough institutional trust, fast enough, that a professional will pay before they have met anybody.**

That single goal outranks delight, personality and visual ambition. Every choice below is measured against it.

### 1.2 The concept — a register, not a startup

This product is fundamentally **a register**. It records who belongs, in what category, in good standing until when. That is the association's actual product, and the design should look like what it is.

Registers have their own visual vernacular: ruled lines, precise alignment, tabular figures that line up in columns, entries with numbers, dates that mean something legally. It is an aesthetic of **recorded fact** — closer to a certificate of incorporation or a professional roll than to a SaaS dashboard.

So the system is built on:

- **A strong left-hand alignment spine.** Content is left-aligned throughout. Almost nothing is centred. Registers are read down a column, not across a hero.
- **Rules instead of cards.** Structure comes from horizontal rules and background steps, not from chopping everything into identical rounded boxes. Where cards appear, the three card types look genuinely different from one another because they do different jobs.
- **Tabular figures everywhere numbers matter.** Membership numbers, amounts, dates and expiry countdowns use lining tabular numerals so they align and so a ₦ figure reads as a fact.
- **Deep forest green as institutional colour, not as decoration.** Green carries authority here. It does not appear as a wash, a gradient or a mood.

### 1.3 Where the boldness is spent

One element carries the design's personality: **the Membership Record panel**.

*(Renamed from "block" per audit N-4, so it is never confused with the downloadable digital membership card excluded by PRD §3.2. This is an on-screen panel, not a saveable artefact.)*

It is a bordered panel, square-cornered, ruled internally, setting the membership number in wide-set tabular figures with the category, status and valid-until date beneath. It looks deliberately like something issued rather than something rendered.

It appears in exactly three places: the welcome screen (J-07), the top of the portal dashboard (M-01), and the printed receipt. Nowhere else. It is the moment abstract payment becomes a tangible membership, and its scarcity is what gives it weight.

Everything around it is quiet, disciplined and unremarkable on purpose.

### 1.4 What this deliberately is not

Not a fintech dashboard. Not a conference microsite. Not a government portal from 2009. Not a template with the association's logo dropped in.

Explicitly rejected: gradient backgrounds and gradient text; glassmorphism and blur panels; identical rounded cards with a single soft grey shadow; decorative scroll-triggered reveals; all-caps tracked-out eyebrow labels above headings; a monospace face used for small labels as texture; arrows appended to button text; hero illustration of abstract shapes; stock photography of people shaking hands.

---

## 2. Colour

### 2.1 The core palette

Green is required and correct — it is the national colour and the association's natural register. But the Nigerian flag green **#008751** reaches only 4.58:1 against white. That clears AA for normal text by a margin too thin to build a whole interface on, and it fails AA for large-text-only surfaces where it would sit on a tint.

So the palette splits the job: **flag green is the identity note; deeper forest greens do the structural work.**

```css
/* ─── Brand & structure ─────────────────────────── */
--forest-900: #0A3B29;  /* deepest — headers, record panel, footer */
--forest-800: #055537;  /* headings on light, strong emphasis */
--forest-700: #046A44;  /* primary action fill, links */
--flag-green: #008751;  /* identity accent only — see rules */
--forest-100: #E3F1E9;  /* active tint, selected state */
--forest-050: #F1F7F4;  /* section wash */

/* ─── Neutrals ──────────────────────────────────── */
--ink:        #14201A;  /* body text — green-black, not grey-black */
--ink-muted:  #4E5F56;  /* secondary text, captions, metadata */
--paper:      #FAFBFA;  /* page background */
--surface:    #EFF3F0;  /* raised sections, table headers, admin chrome */
--white:      #FFFFFF;  /* form fields, record panel, content areas */
--rule:       #D3DDD7;  /* decorative dividers */
--border:     #73867C;  /* input and control boundaries — meets 3:1 */

/* ─── Seal (secondary identity) ─────────────────── */
--brass-700:  #7A5E18;  /* record panel accent, certification marks */
--brass-100:  #F5EEDA;

/* ─── Status ────────────────────────────────────── */
--status-active-fg:     #0A3B29;  --status-active-bg:     #E3F1E9;
--status-expiring-fg:   #6E4600;  --status-expiring-bg:   #FBF0DC;
--status-expired-fg:    #3F4F47;  --status-expired-bg:    #EAEEEB;
--status-suspended-fg:  #8A1F16;  --status-suspended-bg:  #FAE7E5;
--status-pending-fg:    #123A59;  --status-pending-bg:    #E4EEF6;

/* ─── Feedback ──────────────────────────────────── */
--success-fg: #055537;  --success-bg: #E3F1E9;  --success-border: #046A44;
--warning-fg: #6E4600;  --warning-bg: #FBF0DC;  --warning-border: #B07400;
--error-fg:   #8A1F16;  --error-bg:   #FAE7E5;  --error-border: #A32218;
--info-fg:    #14201A;  --info-bg:    #EFF3F0;  --info-border: #73867C;

/* ─── Focus ─────────────────────────────────────── */
--focus-light: #046A44;  /* on light backgrounds */
--focus-dark:  #C9F2DE;  /* on forest-900 / forest-700 surfaces */
```

### 2.2 Measured contrast

| Pair | Ratio | Verdict |
|---|---|---|
| `--ink` on `--paper` | **16.18:1** | AAA |
| `--ink` on `--surface` | **14.98:1** | AAA |
| `--ink-muted` on `--paper` | **6.54:1** | AAA normal text |
| `--forest-700` on white | **6.67:1** | AAA |
| `--forest-800` on white | **8.89:1** | AAA |
| White on `--forest-700` (primary button) | **6.67:1** | AAA |
| White on `--forest-900` (footer, header) | **12.57:1** | AAA |
| `--flag-green` on white | **4.58:1** | AA only — **not for body text** |
| `--border` on white | **3.87:1** | Passes 1.4.11 non-text 3:1 |
| `--border` on `--surface` | **3.45:1** | Passes 3:1 |
| `--rule` on `--paper` | 1.49:1 | Decorative only — never a control boundary |
| Active fg on active bg | **10.79:1** | AAA |
| Expiring fg on expiring bg | **7.33:1** | AAA |
| Expired fg on expired bg | **7.41:1** | AAA |
| Suspended fg on suspended bg | **7.70:1** | AAA |
| Pending fg on pending bg | **10.06:1** | AAA |
| `--focus-dark` on `--forest-900` | **10.32:1** | AAA |
| `--brass-700` on white | **6.10:1** | AAA |

### 2.3 Usage roles

| Role | Token | Notes |
|---|---|---|
| Page background | `--paper` | Never pure white at page level; white is reserved for content and fields so forms read as inputs |
| Body text | `--ink` | |
| Secondary text | `--ink-muted` | Metadata, captions, help text. Never for anything required to act on |
| Headings | `--forest-800` on light, `--white` on forest | |
| Primary action | `--forest-700` fill, white label | The only large filled green area on any screen |
| Links in prose | `--forest-700`, underlined | Underline is not optional — see §11 |
| Identity accent | `--flag-green` | Logo, the horizontal identity bar under the masthead, the record panel rule. Never as text, never as a button fill, never as a status |
| Certification accent | `--brass-700` | Record panel figures and the receipt seal mark only |
| Section separation | `--surface` background or `--rule` line | Not shadow |
| Control boundaries | `--border` | Inputs, checkboxes, secondary buttons, table cell edges |

### 2.4 Status colour, and one deliberate refusal

Membership status is the most important information in the product. Four states, four treatments:

| Status | Treatment | Why |
|---|---|---|
| **In review** | Blue tint, blue text, dotted-outline dot | Change set 01. See below on why it is not neutral |
| **Active** | Forest tint, forest text, filled dot | Neutral good standing. Not celebratory |
| **Expiring soon** | Amber tint, amber text, half-filled dot | Urgency without alarm |
| **Expired** | **Neutral slate — not red** | See below |
| **Suspended** | Red tint, red text, hollow dot with a bar | The only genuinely negative state |

**A fifth hue is introduced deliberately rather than reusing neutral.** The cheap option was to style "In review" in the same slate as Expired. Two neutral statuses meaning opposite things — one is "we are working on it", the other is "you have lapsed" — is a legibility failure that colour-blind users would feel worst, sitting next to the most sensitive moment in the product. Blue is worth the extra hue, and it appears **only** as a status: never a button, never a link, never decoration.

**Expired is not styled as an error, and this is a deliberate refusal.** The obvious move is red. But an expired member is the single highest-value renewal prospect in the product, and the journey (App Flow G-7) turns on making them feel lapsed rather than rejected. Red says "you failed"; slate says "this needs renewing". The renewal call to action next to it stays a full-strength primary button. Design should be pulling them back, not punishing them.

**Status is never carried by colour alone.** Every badge has three simultaneous signals: a text label, a shape (filled / half / hollow-with-bar dot), and colour. Deuteranopia makes the active-green and expiring-amber pair unreliable, so the shape difference is load-bearing, not decorative.

---

## 3. Typography

### 3.1 Families

Two families, chosen for what they are actually for rather than for fashion.

**Literata** — headings, article bodies, and any long-form reading.
A screen-first serif with real warmth and generous, sturdy shapes. It gives the association the gravity of a published body without the brittle high-contrast look of a display serif. News posts are the association's public voice and deserve a reading face, not a UI face.

**Archivo** — everything transactional: navigation, forms, tables, buttons, labels, status, admin.
A grotesque with genuine tabular figures and a variable width axis. Neutral where neutrality is right, and its **Expanded** width is what gives the Membership Record panel its distinctive treatment without introducing a third family.

The pairing has a rationale, not just a contrast: **reading matter in a reading serif, official business in a working sans.** That division is legible to the user even if they never name it.

```css
--font-serif: "Literata", Georgia, "Times New Roman", serif;
--font-sans:  "Archivo", "Segoe UI", system-ui, -apple-system, sans-serif;
--font-record: "Archivo", sans-serif;  /* font-variation-settings: "wdth" 115 */
```

**Loading budget** (see §12, F-10): variable subsets, Latin only, `font-display: swap`, preloaded WOFF2. Literata at two weights (400, 600), Archivo variable across 400–700 with the width axis. **Combined budget: 180 KB.** Exceeding it means dropping the Literata italic before dropping anything else.

### 3.2 Type scale

Base 16px. Mobile scale ratio 1.200; desktop 1.250. All line heights unitless.

| Token | Mobile | Desktop | Weight | Line height | Tracking | Family | Use |
|---|---|---|---|---|---|---|---|
| `display` | 32px | 48px | 600 | 1.12 | −0.02em | Serif | Home hero only |
| `h1` | 27px | 38px | 600 | 1.18 | −0.015em | Serif | Page titles, post titles |
| `h2` | 23px | 30px | 600 | 1.25 | −0.01em | Serif | Section headings |
| `h3` | 19px | 24px | 600 | 1.30 | −0.005em | Serif | Sub-sections |
| `h4` | 17px | 19px | 600 | 1.35 | 0 | Sans | Card and panel titles |
| `body-lg` | 18px | 19px | 400 | 1.65 | 0 | Serif | Article body, About |
| `body` | 16px | 16px | 400 | 1.55 | 0 | Sans | Interface prose, forms |
| `body-sm` | 14px | 14px | 400 | 1.50 | 0 | Sans | Help text, metadata |
| `caption` | 13px | 13px | 500 | 1.45 | 0.005em | Sans | Table headers, field labels |
| `micro` | 12px | 12px | 500 | 1.40 | 0.01em | Sans | Status badges, tags. **Floor — nothing smaller ships** |
| `record-number` | 30px | 38px | 600 | 1.0 | 0.06em | Sans Expanded | Membership number only |
| `figure` | inherit | inherit | inherit | inherit | 0 | Sans, `font-variant-numeric: tabular-nums` | Money, dates, counts |

Serif body gets 1.65 line height against the sans's 1.55 — serifs need the extra air, and article measure is longer.

### 3.3 Measure and alignment

- Article and long-form body: **max 68 characters** (≈ 680px at `body-lg`).
- Interface prose and form help text: **max 60 characters**.
- **Everything is left-aligned and ragged right.** No justification — justified text on a 360px screen produces rivers and destroys readability. No centred body copy anywhere.
- Centred text is permitted in exactly two places: the empty-state block and the payment-confirming screen, both of which are single short statements with no surrounding content to align to.

### 3.4 Typographic prohibitions

- No all-caps labels. Sentence case throughout, including buttons, table headers and badges.
- No single word in a headline coloured or italicised for emphasis.
- No eyebrow labels above headings.
- No metadata strings joined by middle dots. Use "12 March 2026 · Policy" → **no**; use "12 March 2026" and "Policy" as separate elements with spacing.
- No text below 12px, anywhere, including legal footers and admin tables.
- No letter-spacing on body text.

---

## 4. Spacing, Grid and Layout

### 4.1 Spacing scale

8px base with a 4px half-step for tight component interiors.

```
--space-1: 4px    --space-5: 24px    --space-9:  64px
--space-2: 8px    --space-6: 32px    --space-10: 80px
--space-3: 12px   --space-7: 40px    --space-11: 96px
--space-4: 16px   --space-8: 48px    --space-12: 128px
```

**Vertical rhythm rules.** Related items: `--space-3`. Field to field: `--space-5`. Sub-section gaps: `--space-7`. Section gaps: `--space-9` mobile, `--space-10` desktop. Page top padding: `--space-8` mobile, `--space-10` desktop.

### 4.2 Grid

| Breakpoint | Columns | Gutter | Margin |
|---|---|---|---|
| 320–599 | 4 | 16px | 16px |
| 600–899 | 8 | 20px | 24px |
| 900–1199 | 12 | 24px | 32px |
| 1200+ | 12 | 24px | centred, max container |

### 4.3 Containers

| Container | Max width | Use |
|---|---|---|
| `container-prose` | 680px | Article body, About, legal pages, FAQ |
| `container-form` | 560px | Signup, login, registration, renewal |
| `container-app` | 1120px | Home, membership, events, portal |
| `container-wide` | 1320px | Admin tables only |

Forms are narrow on purpose. A 560px column on desktop keeps the registration form (App Flow J-06, the longest form in the product) from sprawling into a two-column layout that people fill in the wrong order.

### 4.4 Radius and elevation

Radius is used to signal *kind of thing*, not applied uniformly:

```
--radius-none:  0     /* Membership Record panel, table cells, alert bars */
--radius-sm:    3px   /* inputs, selects, checkboxes, badges, tags */
--radius-md:    5px   /* buttons */
--radius-lg:    8px   /* media, images, avatars */
```

**The record panel is square-cornered and nothing else is.** That is the point — it reads as a document among interface elements.

Elevation is near-absent. Depth comes from borders and background steps.

```
--shadow-overlay: 0 8px 24px -6px rgba(10, 59, 41, 0.18);
--shadow-sticky:  0 -1px 0 0 var(--rule);
```

`--shadow-overlay` applies to exactly three things: dropdown menus, modal dialogs and toasts. **No card, panel, input or button carries a shadow.**

---

## 5. Components

### 5.1 Buttons

Four variants. Minimum target 44×44px; 48px on primary payment actions.

| Variant | Fill | Text | Border | Use |
|---|---|---|---|---|
| **Primary** | `--forest-700` | white | none | One per screen region. Join, Pay, Save changes, Renew |
| **Secondary** | transparent | `--forest-800` | 1px `--border` | Cancel, Back, secondary navigation |
| **Quiet** | transparent | `--forest-700` | none, underlined on hover | Tertiary actions inside tables and rows |
| **Destructive** | transparent | `--error-fg` | 1px `--error-border` | Deactivate, Delete. Filled red only inside a confirmation dialog |

Sizes: `sm` 36px (admin table rows), `md` 44px (default), `lg` 52px (payment actions and the mobile sticky bar).
Padding: `md` is 12px vertical, 20px horizontal. Label is `body` weight 600, sentence case.

**Button copy rule.** The label states the outcome and keeps the same verb through the whole flow. "Pay ₦25,000 and continue" → confirming screen → "Payment confirmed". Never "Submit", never "Click here", never a trailing arrow character.

### 5.2 Inputs

```
Height        48px (44px minimum, 48 default — Nigerian mobile-first)
Background    --white on --paper page
Border        1px --border  (3.87:1, meets 1.4.11)
Radius        --radius-sm
Padding       12px 14px
Label         caption, --ink, above the field, always visible
Help text     body-sm, --ink-muted, below the field
Error text    body-sm, --error-fg, below the field, with an icon
```

- **Labels are never placeholders.** Placeholder-as-label disappears on focus and fails cognitive accessibility outright.
- Placeholders, where used, show format only: `08012345678`.
- Required fields are marked; optional fields are marked "(optional)" where the form is mostly required — whichever set is smaller gets the marker.
- Focus: 2px `--focus-light` ring, 2px offset, plus the border darkens to `--forest-700`.
- Error: border `--error-border`, error text below, `aria-describedby` wired, `aria-invalid="true"`. **Never colour alone.**
- **Read-only fields** (name, email, category in M-02) use `--surface` fill with a lock icon and a one-line explanation of who can change it. They are **not** `disabled` — see §12, F-7.

**Consent checkbox** — a distinct component, not the standard checkbox:
24×24px box, 2px `--border`, square (`--radius-none`), with a 44px tappable label area. Unticked by default with no visual suggestion of a default. **It is never a toggle switch** (§12, F-6). Each consent purpose is a separate control with its own label; they are never bundled.

**File upload control** — new with change set 01, and the only file input in the member-facing product.
Bordered dropzone at `--radius-sm`, 1px `--border`, with a visible button inside for keyboard and screen-reader users (a drag-only zone is not operable). **Accepted formats and the size limit are stated above the control, not only in the error afterwards.** States: idle, dragging, selected (filename, size, remove action), uploading (determinate progress), complete, error. Under `prefers-reduced-motion` the progress bar becomes a static percentage with a polite `aria-live` update — an upload with no visible progress on Nigerian 4G reads as a frozen page, and the applicant has already paid.

**Naira amounts** render as `₦25,000` with tabular figures and no decimal places.

### 5.3 Navigation

**Public header (desktop).** `--forest-900` bar, 72px. Wordmark left. Links in Archivo 15px, white at 90% opacity, full white on hover with a 2px `--flag-green` underline. Right side: quiet Login link and a primary Join button. A 3px `--flag-green` rule sits under the header — the one place flag green appears at full strength.

**Public header (mobile).** 60px, wordmark and a menu disclosure. Panel slides from the right, full height, focus trapped, `Esc` closes, background scroll locked. Join is the last item as a full-width primary button.

**Portal navigation.** Horizontal tabs on desktop (Dashboard, Profile, Payments, Events, Announcements) under a compact member bar; on mobile, a bottom tab bar of exactly four items — **Dashboard, Payments, Events, More** — with Profile, Announcements and Security in the More sheet (audit IG-6). Payment and renewal actions live in thumb reach; profile editing is low-frequency and does not earn a slot.

**Admin navigation.** Left sidebar, `--forest-900`, grouped: Overview / Members / Money / Content / Governance. Governance is visible only to Super Admin — **hidden, not disabled**, since a greyed item advertises what a Publisher cannot reach.

**Breadcrumbs** appear in admin only, from the second level down.

### 5.4 Cards — three types, deliberately unlike each other

Identical cards are the failure mode this section exists to prevent.

**News card.** Image 16:9 at `--radius-lg`, then title (`h4`, serif), date, excerpt. No border, no shadow, no container box. Separated from siblings by whitespace and, on mobile, a `--rule` divider. It behaves like a piece of editorial, because it is.

**Event card.** No image in the list. A left-hand date block — day number in `record-number` style at 24px, month in `caption` — separated from the content by a 1px `--rule` vertical line. Title, location, price band, status. Bordered on the left edge in `--forest-700` at 3px when upcoming, `--rule` when past. Structurally different from a news card at a glance.

**Category card (J-01).** A selectable control, not content. 1px `--border`, `--radius-sm`, radio semantics, 20px padding. Selected: `--forest-100` fill, 2px `--forest-700` border, and a check mark. This is the only card the user can select, so it is the only card that looks selectable.

**Certificate review panel (D-23).** Document rendered in a bordered viewport at `--radius-none`, with the licence number set in `figure` tabular numerals directly beneath, so an administrator can compare document against field without scrolling. Three actions in a fixed footer: Approve (primary), Request correction (secondary), Reject (destructive).

**The Membership Record panel.** Not a card. `--white` on a 2px `--forest-900` border, `--radius-none`. Internal structure: a 3px `--flag-green` rule across the top; the membership number in `record-number` in `--brass-700`; below it a 1px `--rule`; then category, status badge and valid-until in a two-column definition list with tabular figures. It has no hover state and is not clickable. It is a record, not a control.

### 5.5 Data display

**Tables (admin, desktop).** Header row `--surface`, `caption` weight 600. Row height 52px. 1px `--rule` between rows, no vertical grid lines. Numeric columns right-aligned with tabular figures. Row hover `--forest-050`. Sortable headers carry a direction indicator and `aria-sort`.

**Tables on mobile become stacked records** — each row is a block with label/value pairs, never a horizontally scrolling table. Horizontal scroll for member data is a usability failure on a phone.

**Definition lists** (portal profile, event details) use a two-column layout at ≥600px and stack below, with labels in `caption`/`--ink-muted` and values in `body`/`--ink`.

### 5.6 Feedback

**Inline alert.** Full-width bar, 4px left border in the semantic colour, tinted background, `--radius-none`, icon plus text. Four variants: success, warning, error, info. Sits directly above the content it concerns, not floated at the top of the page.

**Toast.** Bottom-centre on mobile, bottom-left on desktop. Only for reversible, non-critical confirmations ("Profile saved"). **Never used for payment, membership or destructive outcomes** — those get a persistent on-screen state, because a toast the user missed is a support ticket.

**Modal dialog.** Only for destructive confirmation. States the consequence in the title, not "Are you sure?". Focus trapped, `Esc` closes, focus returns to the trigger. Primary action in the dialog is the destructive one, filled red; cancel is secondary and gets initial focus.

**Empty states.** Left-aligned, no illustration, no icon. A heading in `h4`, one sentence explaining what would be here, and a primary action if one exists. Where there is nothing to do, the section is hidden entirely rather than shown empty.

**Skeletons.** Used only in the admin panel where tables load asynchronously. Public pages are server-rendered and have no skeleton state.

**Signup progress.** A three-step indicator (Details → Payment → Registration) across J-02, J-04 and J-06. Steps are labelled, current step is announced with `aria-current`, and completed steps are marked. This is a genuine sequence, so numbering it is legitimate.

---

## 6. Interaction States

Every interactive element implements all of these. Missing states are the most common reason an interface feels unfinished.

| State | Specification |
|---|---|
| **Default** | As specified per component |
| **Hover** | Buttons darken one step (`--forest-700` → `--forest-800`); links gain underline; rows tint `--forest-050`. Transition 120ms. **Never the only signal for anything** |
| **Focus-visible** | 2px `--focus-light` ring, 2px offset (`--focus-dark` on forest surfaces). Applied via `:focus-visible`. **Never removed, never replaced by a colour change alone** |
| **Active/pressed** | 1px downward translate on buttons, darkest fill step. No scale transforms |
| **Disabled** | 40% opacity, `cursor: not-allowed`, `aria-disabled`. **A disabled primary action must be accompanied by text saying what would enable it** — a dead button with no explanation is a dead end |
| **Loading** | Button keeps its width, label replaced by a spinner plus "Working…" for screen readers via `aria-live="polite"`. Element stays focused |
| **Error** | Border, icon, and text. Focus moves to the first errored field on submit. Error summary at the top of long forms, linked to each field |
| **Success** | Persistent inline confirmation at the point of action. Financial and lifecycle successes are confirmed on screen *and* by email |
| **Selected** | `--forest-100` fill, 2px `--forest-700` border, check mark, `aria-selected` or `checked` |
| **Read-only** | `--surface` fill, lock icon, explanatory text. Remains focusable and readable |
| **Visited** | Links in article bodies only. Not in navigation |

---

## 7. Screen Composition

### P-01 Home

```
┌──────────────────────────────────────────────┐
│ [forest-900 header] ═══ flag-green rule ═════│
├──────────────────────────────────────────────┤
│                                              │
│  Association of Data Protection              │  ← display, serif
│  Compliance Organizations of Nigeria         │     left-aligned, 3 lines max
│                                              │
│  One sentence on what membership means.      │  ← body-lg, 60ch
│                                              │
│  [ Join the association ]  See categories    │  ← primary + quiet
│                                              │
├──────────────────────────────────────────────┤
│ ─── surface band ───────────────────────────  │
│  Latest                        View all news │
│  ┌ news ─┐ ┌ news ─┐ ┌ news ─┐               │
├──────────────────────────────────────────────┤
│  Upcoming                    View all events │
│  │07│ Event title …                          │
│  │MAR│                                       │
├──────────────────────────────────────────────┤
│ [forest-900 footer]                          │
└──────────────────────────────────────────────┘
```

**The hero is typographic.** No image, no illustration, no abstract shapes. The association's full name, set large in the serif, left-aligned against generous space, is the most credible thing this organisation can lead with — and it costs nothing in LCP. A hero image would be the default move and would be slower and less convincing.

Empty states: if no posts are published, the Latest band is removed entirely, not shown with placeholders (App Flow P-01).

Logged-in members see "My portal" replacing Login, and the Join button is replaced by a portal link.

### P-04 Membership

Page title, one paragraph of context, then categories as a stacked comparison list — **not a three-column pricing table**. Pricing tables imply consumer tiers and a "best value" nudge; a professional body has categories you qualify for, not plans you choose. Each entry: name, who it is for, annual fee in `figure` at `h3` size, eligibility, benefit list, and a per-category Join button that routes straight to J-02.

Expired members see a renewal banner above the categories instead of a join prompt.

### J-01 → J-02 → J-04 → J-06 → J-07 (the paid journey)

`container-form` at 560px, single column, progress indicator persistent at the top, and the association wordmark retained in the header — payment flows that strip branding lose trust exactly when they need it most.

**J-02** shows the selected category and the exact amount before any field, so nobody is surprised at Paystack. The consent checkboxes sit directly above the primary button. The button reads **"Pay ₦25,000 and continue"** — the amount is in the button because that is where people look last.

**J-04 (confirming)** is the most emotionally loaded screen in the product. Centred, minimal: a single non-decorative progress indicator, "Confirming your payment", the reference in tabular figures, and the reassurance that a receipt is being emailed. After the polling ceiling it changes to a calm state pointing at email — **it never says the payment failed while it is merely slow**. There is no cancel action, because there is nothing safe to cancel.

**J-06** splits into two steps with a save between them. The licence-number field appears only for DPCO applicant types, with its conditional requirement explained in help text rather than enforced by a surprise error.

**J-07** is where the Membership Record panel appears for the first time, full width, above a short list of what to do next.

### M-01 Portal dashboard

Record panel first, immediately below a compact greeting. Below it, in order: renewal prompt if expiring or expired; upcoming registered events; latest member announcements; quick links.

The renewal prompt is a warning-variant inline alert with a primary button, showing the exact expiry date and the new expiry date that renewing would produce.

Expired members see the announcements section replaced by a one-line explanation that member announcements resume on renewal — an empty list with no explanation reads as a bug (App Flow M-06).

### P-06 News post

`container-prose`, 680px, serif `body-lg` at 1.65. Title, date, author. No sidebar, no related-posts rail alongside the text, no floating share bar. Share links and up to three related posts sit after the article. Images full measure at `--radius-lg` with captions in `caption`/`--ink-muted`.

### P-08 Event detail

Two-column at ≥900px: details left, a registration panel right that becomes sticky on scroll. Below 900px the panel moves inline above the description and a sticky bottom bar carries the register action.

The panel shows the price **applicable to this viewer**, with one line of context: members see "Member price"; visitors see the price plus "Members pay ₦X — log in or join"; expired members see the non-member price plus "Renew to get member pricing" linking to M-07. That last line is the design's answer to App Flow G-7.

### A-01 Login

`container-form`, single column, generous top space. Email, password, remember me, primary Login, quiet Forgot password. Below a `--rule`: "Not a member yet? Join the association." Failures show a generic inline error that never reveals whether the email exists.

### D-01 Admin dashboard

`container-wide`. Metric row across the top — six figures in `figure` at `h2` size with `caption` labels, each one a link into its filtered list. Below: three work queues in priority order — **applicants awaiting registration first**, then recent payments, then enquiries. Upcoming events last.

Admin uses `--surface` chrome and denser spacing (`--space-3` rhythm, 36px controls). It is a work tool used daily by three people; it should be efficient, not spacious.

### Error and empty screens

404 and 500 use `container-prose`, a serif `h1`, one sentence, and real routes out — search plus the three most likely destinations. No large numerals as decoration, no illustration.

---

## 8. Mobile Behaviour

The primary device is a mid-range Android phone on 4G. This is not a desktop design that reflows.

- **Targets:** 44px minimum, 48px for payment and primary actions, 8px minimum spacing between adjacent targets.
- **Sticky action bar** on P-08, J-02, J-06, M-07 and E-01 — the primary action stays in thumb reach with `--shadow-sticky` and a `--paper` background. It never covers the final field; the form gets bottom padding equal to the bar height.
- **Bottom tab bar in the portal**, four items plus overflow. The public site keeps a top disclosure menu — a bottom bar on marketing pages competes with the browser chrome.
- **Input types and autocomplete are mandatory:** `type="email"` with `autocomplete="email"`, `type="tel"` with `inputmode="numeric"` for phone, `autocomplete="name"`, `autocomplete="new-password"`. Getting this wrong adds real seconds to every field on a phone.
- **Phone field** accepts `0801…`, `+234801…` and spaced input, normalising on save rather than rejecting on entry.
- **Tables never scroll horizontally.** They become stacked label/value records.
- **No hover-dependent information anywhere.** Every tooltip must have a tap equivalent.
- **Forms are single-column at every breakpoint.** No side-by-side fields, including first/last name.
- **The record panel** scales its number down to 30px and stacks its definition list; it never scrolls sideways.
- **Modals become full-screen sheets** below 600px with a visible close control in the top-left.
- **Images** ship as AVIF with WebP fallback, `srcset` at 400/800/1200, `loading="lazy"` below the fold, explicit `width`/`height` to hold layout.

---

## 9. Motion

Motion is functional here. This product handles other people's money and professional records; animated flourish reads as unseriousness.

**Durations:** 120ms micro (hover, focus), 180ms standard (disclosure, accordion), 240ms overlay (modal, sheet). Easing `cubic-bezier(0.2, 0, 0, 1)` entering, `cubic-bezier(0.4, 0, 1, 1)` exiting.

**Permitted:**
- State changes the user caused: menu opening, accordion expanding, modal entering, inline alert appearing.
- The signup progress indicator advancing between steps.
- The single spinner on the payment-confirming screen.

**Prohibited:**
- Scroll-triggered reveals of any kind. Every section is visible when it is reached.
- Staggered entrance animations on cards or lists.
- Parallax, marquees, animated counters, auto-playing carousels.
- Hover animations on cards.
- Page-load orchestration on the home page.

**Reduced motion.** Under `prefers-reduced-motion: reduce`, all transforms and translations are removed; opacity transitions are capped at 100ms; the confirming-screen spinner is replaced by a static indicator plus a polite `aria-live` text update ("Still confirming — this can take up to a minute"). **The reduced-motion path must convey identical information**; a user who cannot see the spinner must still know the system is working. See §12, F-5.

---

## 10. Accessibility

Target: **WCAG 2.1 Level AA**, as required by PRD NFR 6.5.

**Colour and contrast.** All ratios in §2.2 are calculated. Body text meets AAA at 16:1; no interface text sits below 4.5:1; no control boundary sits below 3:1. Information is never carried by colour alone — status badges, form errors, event availability and payment outcomes all carry text plus shape.

**Keyboard.** Every interactive element is reachable and operable by keyboard in a logical order. Focus indicators are never suppressed. A skip-to-content link is the first focusable element on every page. Modals and the mobile menu trap focus and restore it on close. **The entire signup flow must be completable by keyboard alone** — this is an explicit acceptance criterion (PRD AC-NFR).

**Structure.** One `h1` per page, headings in order with no skipped levels, landmark regions (`header`, `nav`, `main`, `footer`), lists marked up as lists, tables with `<th scope>` and captions.

**Forms.** Every field has a persistently visible `<label>`. Help text and errors are wired with `aria-describedby`. Errors set `aria-invalid` and are described in text. Long forms show an error summary at the top with links to each field. Required state is conveyed in text, not by an asterisk alone.

**Dynamic content.** Polling status (J-04, E-02, M-08) uses `aria-live="polite"`. Toasts use `role="status"`. Destructive confirmations use `role="alertdialog"`.

**Media.** Alt text is required at upload as a validation rule, not a suggestion (PRD NFR 6.5). Decorative images carry empty alt. No text baked into images.

**Targets.** 44px minimum with 8px separation, per 2.5.5 at AAA — worth exceeding AA here given the mobile-first audience.

**Zoom and reflow.** Usable at 200% zoom and at 320px width with no horizontal scrolling and no loss of function.

**Testing.** Automated axe-core in CI, plus a manual keyboard-only and screen-reader pass over the signup flow, portal dashboard and one news post before launch. Automated tooling catches roughly a third of real issues; the manual pass is what makes the AA claim honest.

---

## 11. Always / Never

### Always

1. Left-align text. Ragged right.
2. Use tabular figures for money, dates, membership numbers and counts.
3. Show the exact amount in the button that takes payment.
4. Give status three signals: text, shape, colour.
5. Keep the visible label above every form field.
6. Underline links in prose — colour alone fails for colour-blind readers.
7. Confirm financial and lifecycle outcomes on screen *and* by email.
8. State what a disabled action needs in order to become available.
9. Give every empty state either an action or deletion — never a bare empty box.
10. Explain why a read-only field is read-only and who can change it.
11. Keep one primary action per screen region.
12. Ship a visible focus ring on everything focusable.
13. Use sentence case everywhere, including buttons and table headers.
14. Preserve the user's input through any error.
15. State accepted file formats and size limits **above** the file picker, not only in the error afterwards.

### Never

1. Never use gradients, glassmorphism or blur panels.
2. Never give every card the same border-radius, border and shadow.
3. Never use `--flag-green` for body text, button fills or status.
4. Never style Expired as an error.
5. Never use a toggle switch for consent.
6. Never use a placeholder as a label.
7. Never use colour as the only carrier of meaning.
8. Never animate on scroll.
9. Never put member data in a horizontally scrolling table on mobile.
10. Never use all-caps tracked-out eyebrow labels.
11. Never set type below 12px.
12. Never use a toast for a payment, membership or destructive outcome.
13. Never remove or restyle focus indicators to a colour change alone.
14. Never centre body copy or use justified text.
15. Never say "Submit". Name the outcome.
16. Never tell someone their payment failed while it is still pending.
17. Never show a member the Join call to action.
18. Never bundle multiple consent purposes into one checkbox.
19. Never show the Membership Record panel to a pending or rejected applicant — it is reserved for issued membership.
20. Never use the same neutral treatment for "In review" and "Expired". They mean opposite things.

---

## 12. Flagged Conflicts

Design decisions that collide with the journey, the stack or accessibility. Each needs a decision.

| ID | Conflict | Severity |
|---|---|---|
| **F-1** | **The admin panel is Filament (TRD §2.1), which ships its own design system.** Applying this brief to the admin surface deeply means fighting the framework and losing most of the productivity that justified choosing it. **Recommendation: theme Filament with the colour tokens, typefaces and radius values only, and accept its component patterns as-is.** The member-facing product carries the full design system; admin carries the brand. Trying to make them identical will cost weeks and is the single most likely source of schedule overrun in this project. | **High — decide before build** |
| **F-2** | Brand green and success green occupy the same hue. A green success alert next to a green primary button competes for the same meaning. Resolved here by giving success a tinted background with a left border and an icon, and by keeping filled green exclusive to primary buttons — but it must be held to strictly, or the interface turns into undifferentiated green. | Moderate |
| **F-3** | Green and amber are the classic deuteranopia confusion pair, and they carry Active versus Expiring soon — the two states that drive renewal revenue. Colour alone would be a real failure for roughly one in twelve male users. Mitigated by the mandatory shape signal in §2.4; that mitigation is not optional. | **High** |
| **F-4** | `--flag-green` at 4.58:1 clears AA for normal text with almost no margin and fails AAA. Restricted here to identity marks and rules. If brand guidance later demands it as a text or button colour, the palette must change, not the standard. | Moderate |
| **F-5** | The payment-confirming screen (J-04) relies on a spinner to signal progress, which reduced-motion users will not see. The `aria-live` text alternative must carry identical information. A silent static dot would leave a user unsure whether their payment is processing — during the most anxious moment in the product. | **High** |
| **F-6** | Consent as a toggle switch would be visually cleaner but implies a default-on state. NDPA requires unticked, unbundled opt-in. **Toggles are prohibited for consent.** Flagged because this will be raised as a visual improvement at some point. | Moderate |
| **F-7** | Read-only profile fields (M-02) styled with the HTML `disabled` attribute would fail: disabled controls are skipped by some screen readers and typically fail contrast. Use `readonly` with `--surface` fill and an explanation instead. | Moderate |
| **F-8** | **Cookie consent (FR-12.1) versus the home page's eight-second credibility job.** A blocking modal on first visit buries the hero, hurts LCP and greets a prospective member with a legal interruption. **Recommendation: a bottom-anchored non-blocking banner, with Accept and Reject given equal visual weight** — unequal weighting is a dark pattern and indefensible for this client in particular. | Moderate |
| **F-9** | Showing visitors the member price alongside the non-member price on P-08 is a strong conversion device but edges toward retail pricing psychology for a professional body. Recommended as one plain line of text rather than a struck-through comparison. Needs the association's view. | Minor |
| **F-10** | Two families plus a width axis risks the 1.5 MB page-weight and 2.5s LCP budgets (PRD NFR 6.3). Held to a **180 KB font budget** with Latin subsetting. If it is exceeded, drop the Literata italic first, then reduce Archivo to two static weights. **Do not solve it by adding a third family for the record panel.** | Moderate |
| **F-11** | **Dark mode is not in scope.** The PRD never mentions it and every token above assumes a light surface. Flagged so it is not introduced mid-build, which would double the palette work and invalidate every contrast figure in §2.2. | Minor |
| **F-12** | The Membership Record panel's authority depends on scarcity. If it is reused as a general panel style — for events, for news, for admin widgets — it stops reading as a document and the design loses its one distinctive element. **Three placements only.** | Moderate |
| **F-14** | **The record panel cannot serve one of its own three placements as specified.** J-07 and M-01 are browser surfaces; the receipt is a dompdf PDF, which supports a narrow CSS subset and will not carry the specified border treatment or the Archivo width axis. A **separate print variant** is required, styled for dompdf, built alongside the receipt in Phase 8 rather than in the Phase 2 component library (audit IG-5). | Moderate |
| **F-15** | The file-upload control has no precedent in this system and no analogue elsewhere in the product. It needs its full state set including a reduced-motion progress path, or a slow upload on 4G reads as a frozen page — to somebody who has already paid. | Moderate |
| ~~F-13~~ | **RESOLVED.** PRD Q3 answered yes. The fifth status is specified in §2.1 and §2.4; the review panel in §5.4; the upload control in §5.2. | — |
