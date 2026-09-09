{{--
    Email button — UI brief §5.1 adapted for mail.

    A bordered table cell, not a styled <a>. Outlook renders through Word and
    ignores padding on an anchor, so a CSS button collapses to underlined text
    at the exact moment somebody needs to click it.

    The label states the outcome, as everywhere else (§5.1) — never "Click
    here".
--}}
@props(['url', 'label'])

<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:24px 0;">
    <tr>
        <td style="background-color:#046A44; border-radius:5px;">
            <a href="{{ $url }}"
               style="display:inline-block; padding:14px 24px;
                      font-family:'Segoe UI',system-ui,-apple-system,sans-serif;
                      font-size:16px; font-weight:600; color:#FFFFFF;
                      text-decoration:none;">
                {{ $label }}
            </a>
        </td>
    </tr>
</table>

{{-- The same address in plain text underneath.
     Some clients rewrite or strip the anchor, and a member who cannot click
     must still be able to copy. For a registration link — the one thing
     standing between a payment and a membership — that fallback is not
     optional. --}}
<p style="margin:0 0 24px;
          font-family:'Segoe UI',system-ui,-apple-system,sans-serif;
          font-size:13px; line-height:1.5; color:#4E5F56; word-break:break-all;">
    If the button does not work, copy this address into your browser:<br>
    <span style="color:#046A44;">{{ $url }}</span>
</p>
