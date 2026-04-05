{{ __('Hello :name,', ['name' => $recipient->name]) }}

@if ($approved)
{{ __('Mail body organization approved', ['org' => $organization->nameForLocale()]) }}
{{ __('Mail body organization approved next') }}
@else
{{ __('Mail body organization rejected', ['org' => $organization->nameForLocale()]) }}
@if (filled($organization->verification_review_note))
---
{{ $organization->verification_review_note }}
@endif
@endif

{{ __('Organization dashboard') }}: {{ route('organization.dashboard') }}

— {{ config('app.name') }}
