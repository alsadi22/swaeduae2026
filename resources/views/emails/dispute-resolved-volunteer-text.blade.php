@php
    $volunteer = $dispute->attendance?->user;
    $event = $dispute->attendance?->event;
    $mailLocaleQ = [];
    if (is_string($volunteer?->locale_preferred) && in_array($volunteer->locale_preferred, ['en', 'ar'], true)) {
        $mailLocaleQ['lang'] = $volunteer->locale_preferred;
    }
@endphp
{{ __('Hello :name,', ['name' => $volunteer?->name ?? __('Volunteer')]) }}

@if ($dispute->status === \App\Models\Dispute::STATUS_RESOLVED)
{{ __('Mail body dispute resolved', ['event' => $event?->title_en ?? __('Event')]) }}
@else
{{ __('Mail body dispute dismissed', ['event' => $event?->title_en ?? __('Event')]) }}
@endif

@if (filled($dispute->resolution_note))
---
{{ $dispute->resolution_note }}
@endif

@if ($event)
{{ __('View opportunity') }}: {{ route('volunteer.opportunities.show', array_merge(['event' => $event], $mailLocaleQ), true) }}
{{ __('My attendance') }}: {{ route('dashboard.attendance.index', $mailLocaleQ, true) }}
@endif

— {{ config('app.name') }}
