<x-mail::layout :subject="'Your ALDAPCON membership is active'" :preheader="$preheader">

    <p style="margin:0 0 20px;">Dear {{ $membership->user->full_name }},</p>

    <p style="margin:0 0 20px;">
        Your certificate has been verified and your membership is now active.
        Welcome to the association.
    </p>

    {{-- The membership number in full.
         App Flow J-07: neither this email nor the welcome screen should be
         the sole source of it, because either can be missed. --}}
    <x-mail::components.record :rows="[
        'Membership number' => $membership->membership_number,
        'Category' => $membership->category->name,
        'Valid until' => $membership->expires_at->timezone('Africa/Lagos')->format('j F Y'),
    ]" />

    <x-mail::components.button :url="url('/portal')" label="Go to my portal" />

    <p style="margin:0 0 20px;">
        Your membership runs for twelve months from today. We will remind you
        before it expires — you do not need to keep track of the date yourself.
    </p>

    <p style="margin:0;">
        The ALDAPCON secretariat
    </p>

</x-mail::layout>
