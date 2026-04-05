@php
    $volunteer = $dispute->attendance?->user;
    $event = $dispute->attendance?->event;
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
{{ __('View opportunity') }}: {{ route('volunteer.opportunities.show', $event) }}
{{ __('My attendance') }}: {{ route('dashboard.attendance.index') }}
@endif

— {{ config('app.name') }}
