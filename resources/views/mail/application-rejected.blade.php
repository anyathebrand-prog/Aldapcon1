<x-mail.layout :subject="'About your ALDAPCON application'" :preheader="$preheader">

    <p style="margin:0 0 20px;">Dear {{ $applicant->full_name }},</p>

    {{-- Plainly, and early. Burying a refusal under three paragraphs of
         preamble is worse than saying it. --}}
    <p style="margin:0 0 20px;">
        We are unable to approve your ALDAPCON membership application at this time.
    </p>

    {{-- FR-3.13.4 makes the reason mandatory, and the database enforces it, so
         there is always something specific here rather than a form letter. --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
           style="background-color:#FAE7E5; border-left:4px solid #A32218; margin:0 0 24px;">
        <tr>
            <td style="padding:16px 20px;">
                <p style="margin:0 0 6px;
                          font-family:'Segoe UI',system-ui,-apple-system,sans-serif;
                          font-size:13px; color:#8A1F16;">Reason given</p>
                <p style="margin:0;
                          font-family:'Segoe UI',system-ui,-apple-system,sans-serif;
                          font-size:16px; line-height:1.55; color:#8A1F16;">
                    {{ $applicant->review_reason }}
                </p>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 20px;">
        If you believe this decision is mistaken, or if you can supply the
        missing information, please reply to this email and we will look at it
        again. A refusal is not permanent — you are welcome to apply again.
    </p>

    {{-- B-6 / PRD Q12 is unanswered (App Flow G-16). This says what is true
         rather than inventing a refund policy, and the association is told to
         answer it before this email can ever be sent in anger. --}}
    <p style="margin:0 0 20px;">
        The association will be in touch separately regarding the fee you paid.
    </p>

    <p style="margin:0;">
        The ALDAPCON secretariat
    </p>

</x-mail.layout>
