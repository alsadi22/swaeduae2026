<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #1c1917;">
    <p>{{ __('Hello :name,', ['name' => $recipient->name]) }}</p>

    @if ($approved)
        <p>{{ __('Mail body organization approved', ['org' => $organization->nameForLocale()]) }}</p>
        <p>{{ __('Mail body organization approved next') }}</p>
    @else
        <p>{{ __('Mail body organization rejected', ['org' => $organization->nameForLocale()]) }}</p>
        @if (filled($organization->verification_review_note))
            <p style="margin-top: 1rem; padding: 0.75rem 1rem; background: #fafaf9; border-left: 3px solid #78716c; white-space: pre-wrap;">{{ $organization->verification_review_note }}</p>
        @endif
    @endif

    <p style="margin-top: 1.5rem;">
        <a href="{{ route('organization.dashboard', \App\Support\PublicLocale::queryForUser($recipient), true) }}" style="color: #047857; font-weight: 600;">{{ __('Organization dashboard') }}</a>
    </p>

    <p style="margin-top: 1.5rem; font-size: 0.875rem; color: #78716c;">{{ config('app.name') }}</p>
</body>
</html>
