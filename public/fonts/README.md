# Fonts

Self-hosted per TRD §7.1. Nothing is fetched from a third-party font CDN at
runtime — a request to `fonts.gstatic.com` on every page load would leak
visitor IP addresses to Google on a data protection association's website, and
would add a DNS lookup and a connection to the critical path on Nigerian 4G.

## Files

| File | Family | Axes | Size |
|---|---|---|---|
| `archivo-latin-var.woff2` | Archivo | `wght` 400–700, `wdth` 62.5–125 | 88 KB |
| `literata-latin-var.woff2` | Literata | `wght` 400–600, `opsz` 7–72 | 84 KB |

**Total: 172 KB against the 180 KB budget** (UI brief §3.1, F-10). Roughly 8 KB
of headroom — enough, but not enough to add anything.

Both are the Latin subset as served by Google Fonts (`U+0000-00FF` plus common
punctuation and symbols), downloaded once and committed. Full-range files would
carry Cyrillic, Greek and Vietnamese that this site will never render.

## Why these two files and no more

UI brief §3.1 asks for Literata at 400 and 600, and Archivo across 400–700 with
the width axis. Two **variable** files deliver all of that: every weight in the
range comes from one file, and Archivo's `wdth` axis is what gives the
Membership Record panel its Expanded treatment **without introducing a third
family** — which F-10 prohibits explicitly.

**No italic is included.** F-10 names Literata italic as the first thing to drop
if the budget is threatened, and the budget is met without it. If italic is
later required, it must be measured, not assumed.

If the budget is ever exceeded, the order of retreat is fixed by F-10:

1. Drop Literata italic (not present, so no saving available)
2. Reduce Archivo to two static weights
3. **Never** add a third family for the record panel

## Licence

Both families are licensed under the **SIL Open Font License 1.1**, which
permits self-hosting and redistribution. Sources:

- Archivo — <https://fonts.google.com/specimen/Archivo>
- Literata — <https://fonts.google.com/specimen/Literata>

## Updating

Re-download the Latin subset from the Google Fonts CSS API with a modern
browser user-agent (the API serves WOFF2 only to clients that advertise support
for it), then **re-measure the total before committing**. The budget is a
launch gate, not a guideline.
