<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #1c1917;">
    <p>{{ __('A new organization has registered and is pending verification.') }}</p>
    <p><strong>{{ __('Organization') }}:</strong> {{ $organization->name_en }}@if ($organization->name_ar) / {{ $organization->name_ar }}@endif</p>
    <p><strong>{{ __('Representative') }}:</strong> {{ $registeringUser->name }}</p>
    <p><strong>{{ __('Email') }}:</strong> {{ $registeringUser->email }}</p>
    <p><strong>{{ __('Phone') }}:</strong> {{ $registeringUser->phone ?? '—' }}</p>
    <p><strong>{{ __('Locale') }}:</strong> {{ $registeringUser->locale_preferred ?? '—' }}</p>

    <p style="margin-top: 1.5rem;">
        <a href="{{ route('admin.organizations.index', \App\Support\PublicLocale::query(), true) }}" style="color: #047857; font-weight: 600;">{{ __('Review pending organizations in admin') }}</a>
    </p>
</body>
</html>
