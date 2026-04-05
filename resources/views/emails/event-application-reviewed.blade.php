<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #1c1917;">
    <p>{{ __('Hello :name,', ['name' => $application->user->name]) }}</p>

    @if ($outcome === \App\Mail\EventApplicationReviewedMail::OUTCOME_APPROVED)
        <p>{{ __('Mail body event application approved', ['event' => $application->event?->title_en ?? __('Event')]) }}</p>
        <p>{{ __('Mail body event application approved next') }}</p>
    @else
        <p>{{ __('Mail body event application rejected', ['event' => $application->event?->title_en ?? __('Event')]) }}</p>
        @if (filled($application->review_note))
            <p style="margin-top: 1rem; padding: 0.75rem 1rem; background: #fafaf9; border-left: 3px solid #78716c; white-space: pre-wrap;">{{ $application->review_note }}</p>
        @endif
    @endif

    @if ($application->event)
        <p style="margin-top: 1.5rem;">
            <a href="{{ route('volunteer.opportunities.show', array_merge(['event' => $application->event], \App\Support\PublicLocale::queryForUser($application->user)), true) }}" style="color: #047857; font-weight: 600;">{{ __('View opportunity') }}</a>
        </p>
    @endif

    <p style="margin-top: 1.5rem; font-size: 0.875rem; color: #78716c;">{{ config('app.name') }}</p>
</body>
</html>
