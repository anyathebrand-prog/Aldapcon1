{{--
    Email layout — FR-10.1, AC-F10, UI brief §2 and §3.

    AC-F10 requires these to render correctly on mobile Gmail and Outlook.
    That constraint drives every decision here, and most of them are the
    opposite of what the web design system does:

    - TABLES for layout, not flexbox or grid. Outlook on Windows renders
      through Word, which supports neither.
    - INLINE styles, not classes. Gmail strips <style> blocks in some clients,
      so anything that matters must survive without them.
    - SYSTEM fonts, not Literata and Archivo. A webfont in email is a request
      most clients block, and the fallback then looks accidental rather than
      chosen. The web design system does not apply here, and pretending it
      does produces mail that looks broken.
    - No images. Most clients block them by default, so an email whose meaning
      depends on one arrives meaningless — and a payment confirmation is the
      worst place for that.

    The palette is still the brief's (§2.1), because colour survives where
    layout does not.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subject ?? 'ALDAPCON' }}</title>
</head>
<body style="margin:0; padding:0; background-color:#EFF3F0;">

    {{-- Preheader: the grey line a mail client shows beside the subject.
         Left empty and hidden, it fills with whatever text comes first —
         usually a link or a legal footer. --}}
    @isset($preheader)
        <div style="display:none; max-height:0; overflow:hidden; opacity:0;">
            {{ $preheader }}
        </div>
    @endisset

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
           style="background-color:#EFF3F0; padding:24px 12px;">
        <tr>
            <td align="center">

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                       style="max-width:560px; background-color:#FFFFFF; border:1px solid #D3DDD7;">

                    {{-- Masthead. The flag-green rule beneath it is the one
                         identity mark carried into email (§2.3). --}}
                    <tr>
                        <td style="background-color:#0A3B29; padding:20px 24px;">
                            <span style="font-family:'Segoe UI',system-ui,-apple-system,sans-serif;
                                         font-size:18px; font-weight:600; color:#FFFFFF; letter-spacing:0.01em;">
                                ALDAPCON
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#008751; height:3px; line-height:3px; font-size:0;">&nbsp;</td>
                    </tr>

                    <tr>
                        <td style="padding:32px 24px;
                                   font-family:'Segoe UI',system-ui,-apple-system,sans-serif;
                                   font-size:16px; line-height:1.55; color:#14201A;">
                            {{ $slot }}
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 24px 28px;">
                            <hr style="border:0; border-top:1px solid #D3DDD7; margin:0 0 20px;">

                            <p style="margin:0 0 10px;
                                      font-family:'Segoe UI',system-ui,-apple-system,sans-serif;
                                      font-size:13px; line-height:1.5; color:#4E5F56;">
                                Association of Data Protection Compliance Organizations of Nigeria
                            </p>

                            {{-- A human route out of every email. App Flow §5:
                                 no message may leave somebody without a next
                                 step. --}}
                            <p style="margin:0;
                                      font-family:'Segoe UI',system-ui,-apple-system,sans-serif;
                                      font-size:13px; line-height:1.5; color:#4E5F56;">
                                Questions?
                                <a href="mailto:{{ $associationEmail ?? 'info@aldapcon.org.ng' }}"
                                   style="color:#046A44;">{{ $associationEmail ?? 'info@aldapcon.org.ng' }}</a>
                            </p>

                            {{-- FR-10.3 — marketing email carries an
                                 unsubscribe link; transactional email does
                                 not, and unsubscribing from marketing must
                                 never stop transactional mail. --}}
                            @isset($unsubscribeUrl)
                                <p style="margin:14px 0 0;
                                          font-family:'Segoe UI',system-ui,-apple-system,sans-serif;
                                          font-size:12px; line-height:1.5; color:#4E5F56;">
                                    <a href="{{ $unsubscribeUrl }}" style="color:#4E5F56;">Unsubscribe from the newsletter</a>.
                                    You will still receive emails about your membership.
                                </p>
                            @endisset
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>
</body>
</html>
