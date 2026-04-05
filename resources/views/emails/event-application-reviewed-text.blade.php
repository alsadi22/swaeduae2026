{{ __('Hello :name,', ['name' => $application->user->name]) }}

@if ($outcome === \App\Mail\EventApplicationReviewedMail::OUTCOME_APPROVED)
{{ __('Mail body event application approved', ['event' => $application->event?->title_en ?? __('Event')]) }}
{{ __('Mail body event application approved next') }}
@else
{{ __('Mail body event application rejected', ['event' => $application->event?->title_en ?? __('Event')]) }}
@if (filled($application->review_note))
---
{{ $application->review_note }}
@endif
@endif

@if ($application->event)
{{ __('View opportunity') }}: {{ route('volunteer.opportunities.show', $application->event) }}
@endif

— {{ config('app.name') }}
