<x-mail.layout :subject="'We have received your ALDAPCON application'" :preheader="$preheader">

    <p style="margin:0 0 20px;">Dear {{ $applicant->full_name }},</p>

    {{-- The two facts that matter most to somebody who has just paid: the
         money arrived, and the document arrived. --}}
    <p style="margin:0 0 20px;">
        Your payment has been received and your NDPC licence certificate has been
        stored securely. Your application is now with the association for review.
    </p>

    <x-mail.record :rows="[
        'Application reference' => $applicant->uuid,
        'Membership category' => $applicant->category->name,
        'Submitted' => $applicant->submitted_at?->timezone('Africa/Lagos')->format('j F Y'),
    ]" />

    <p style="margin:0 0 20px;">
        An administrator will check your certificate against your licence number.
        We will email you as soon as a decision is made, whether or not the
        application is approved.
    </p>

    {{-- No membership number here, and no Membership Record panel.
         UI brief §11 never-19: that panel is reserved for issued membership,
         and showing one now would tell somebody they are a member when they
         are not. --}}
    <p style="margin:0 0 20px;">
        You can log in at any time to check the status of your application.
    </p>

    <p style="margin:0;">
        The ALDAPCON secretariat
    </p>

</x-mail.layout>
