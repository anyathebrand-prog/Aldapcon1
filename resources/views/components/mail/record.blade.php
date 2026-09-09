{{--
    A bordered detail block for membership facts — UI brief §1.3 adapted.

    NOT the Membership Record panel. That has three placements only (F-12) and
    the receipt PDF already needs its own print variant (F-14); a third
    rendering in HTML email would dilute the one distinctive element in the
    design system.

    This is a plain bordered table of label/value pairs, using tabular figures
    where the client supports them.
--}}
@props(['rows'])

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
       style="border:2px solid #0A3B29; margin:0 0 24px;">
    <tr>
        <td style="background-color:#008751; height:3px; line-height:3px; font-size:0;">&nbsp;</td>
    </tr>
    @foreach ($rows as $label => $value)
        <tr>
            <td style="padding:{{ $loop->first ? '18px' : '0' }} 20px {{ $loop->last ? '18px' : '14px' }};">
                <p style="margin:0 0 4px;
                          font-family:'Segoe UI',system-ui,-apple-system,sans-serif;
                          font-size:13px; color:#4E5F56;">{{ $label }}</p>
                <p style="margin:0;
                          font-family:'Segoe UI',system-ui,-apple-system,sans-serif;
                          font-size:16px; font-weight:600; color:#14201A;
                          font-variant-numeric:tabular-nums;">{{ $value }}</p>
            </td>
        </tr>
    @endforeach
</table>
