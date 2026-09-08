{{--
    500 — UI brief §7; App Flow P-16, §5

    A plain apology, the association's email address, and NO STACK TRACE.
    APP_DEBUG=false in production is a launch gate (TRD §9); this page is what
    the visitor sees when it holds.

    App Flow §5: no error message anywhere is permitted to leave the user
    without a next step. So there is a route out and a human to contact.

    Deliberately does NOT extend the public layout. If the failure is in a
    view component, a database lookup for navigation, or the Vite manifest,
    rendering the full chrome would fail a second time and produce a blank
    page instead of an apology. Everything here is self-contained, with system
    fonts and inline styles.
--}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Something went wrong — ALDAPCON</title>
    <style>
        :root { color-scheme: light; }
        body {
            margin: 0;
            background: #FAFBFA;
            color: #14201A;
            font-family: "Segoe UI", system-ui, -apple-system, sans-serif;
            line-height: 1.55;
        }
        .bar { background: #0A3B29; color: #fff; padding: 18px 16px; font-weight: 600; }
        .rule { height: 3px; background: #008751; }
        .wrap { max-width: 680px; margin: 0 auto; padding: 40px 16px; }
        h1 { font-family: Georgia, "Times New Roman", serif; font-size: 27px; line-height: 1.18; color: #055537; margin: 0 0 20px; }
        p { margin: 0 0 16px; max-width: 60ch; }
        a { color: #046A44; }
        .muted { color: #4E5F56; font-size: 14px; }
    </style>
</head>
<body>
    <div class="bar">ALDAPCON</div>
    <div class="rule"></div>

    <div class="wrap">
        <h1>Something went wrong at our end</h1>

        <p>
            This is a fault on our side, not with anything you did. Nothing you
            were doing has been lost because of this page.
        </p>

        <p>
            If you were in the middle of a payment, do not pay again. Contact us
            with the reference you were shown and we will confirm what happened.
        </p>

        <p>
            Email <a href="mailto:info@aldapcon.org.ng">info@aldapcon.org.ng</a>,
            or <a href="/">return to the home page</a>.
        </p>

        <p class="muted">Our team has been notified automatically.</p>
    </div>
</body>
</html>
