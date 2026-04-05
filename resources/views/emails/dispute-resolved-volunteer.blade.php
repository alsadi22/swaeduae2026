<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #1c1917;">
    @php
        $volunteer = $dispute->attendance?->user;
        $event = $dispute->attendance?->event;
        $mailLocaleQ = \App\Support\PublicLocale::queryForUser($volunteer);
    @endphp
    <p>{{ __('Hello :name,', ['name' => $volunteer?->name ?? __('Volunteer')]) }}</p>

    @if ($dispute->status === \App\Models\Dispute::STATUS_RESOLVED)
        <p>{{ __('Mail body dispute resolved', ['event' => $event?->title_en ?? __('Event')]) }}</p>
    @else
        <p>{{ __('Mail body dispute dismissed', ['event' => $event?->title_en ?? __('Event')]) }}</p>
    @endif

    @if (filled($dispute->resolution_note))
        <p style="margin-top: 1rem; padding: 0.75rem 1rem; background: #fafaf9; border-left: 3px solid #78716c; white-space: pre-wrap;">{{ $dispute->resolution_note }}</p>
    @endif

    @if ($event)
        <p style="margin-top: 1.5rem;">
            <a href="{{ route('volunteer.opportunities.show', array_merge(['event' => $event], $mailLocaleQ), true) }}" style="color: #047857; font-weight: 600;">{{ __('View opportunity') }}</a>
        </p>
        <p style="margin-top: 1rem;">
            <a href="{{ route('dashboard.attendance.index', $mailLocaleQ, true) }}" style="color: #047857; font-weight: 600;">{{ __('My attendance') }}</a>
        </p>
    @endif

    <p style="margin-top: 1.5rem; font-size: 0.875rem; color: #78716c;">{{ config('app.name') }}</p>
</body>
</html>
