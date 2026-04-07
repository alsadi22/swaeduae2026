{{ __('Mail data erasure intro', ['app' => config('app.name')]) }}

- {{ __('Account ID') }}: {{ $user->id }}
- {{ __('Name') }}: {{ $user->name }}
- {{ __('Email') }}: {{ $user->email }}
- {{ __('Locale preferred') }}: {{ $user->locale_preferred ?? '—' }}

@if (filled($optionalMessage))
{{ __('Message') }}
{{ $optionalMessage }}

@endif
{{ __('Mail data erasure footer') }}
