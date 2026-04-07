<p>{{ __('Mail data erasure intro', ['app' => config('app.name')]) }}</p>
<ul>
    <li><strong>{{ __('Account ID') }}:</strong> {{ $user->id }}</li>
    <li><strong>{{ __('Name') }}:</strong> {{ $user->name }}</li>
    <li><strong>{{ __('Email') }}:</strong> {{ $user->email }}</li>
    <li><strong>{{ __('Locale preferred') }}:</strong> {{ $user->locale_preferred ?? '—' }}</li>
</ul>
@if (filled($optionalMessage))
    <p><strong>{{ __('Message') }}</strong></p>
    <p style="white-space: pre-wrap;">{{ $optionalMessage }}</p>
@endif
<p>{{ __('Mail data erasure footer') }}</p>
