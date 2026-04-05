{{ __('A new organization has registered and is pending verification.') }}

{{ __('Organization') }}: {{ $organization->name_en }}@if ($organization->name_ar) / {{ $organization->name_ar }}@endif
{{ __('Representative') }}: {{ $registeringUser->name }}
{{ __('Email') }}: {{ $registeringUser->email }}
{{ __('Phone') }}: {{ $registeringUser->phone ?? '—' }}
{{ __('Locale') }}: {{ $registeringUser->locale_preferred ?? '—' }}
