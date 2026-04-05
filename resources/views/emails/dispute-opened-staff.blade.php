<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #1c1917;">
    <p>{{ __('Mail body dispute opened staff intro') }}</p>

    <ul style="margin: 1rem 0; padding-left: 1.25rem;">
        <li><strong>{{ __('Event') }}:</strong> {{ $dispute->attendance?->event?->title_en ?? '—' }}</li>
        <li><strong>{{ __('Volunteer') }}:</strong> {{ $dispute->attendance?->user?->name ?? '—' }} ({{ $dispute->attendance?->user?->email ?? '—' }})</li>
        <li><strong>{{ __('Dispute ID') }}:</strong> {{ $dispute->id }}</li>
    </ul>

    <p style="margin-top: 1rem;"><strong>{{ __('Description') }}:</strong></p>
    <p style="margin-top: 0.5rem; padding: 0.75rem 1rem; background: #fafaf9; border-left: 3px solid #78716c; white-space: pre-wrap;">{{ $dispute->description }}</p>

    <p style="margin-top: 1.5rem;">
        <a href="{{ route('admin.disputes.show', $dispute, true) }}" style="color: #047857; font-weight: 600;">{{ __('Review dispute in admin') }}</a>
    </p>

    <p style="margin-top: 1.5rem; font-size: 0.875rem; color: #78716c;">{{ config('app.name') }}</p>
</body>
</html>
