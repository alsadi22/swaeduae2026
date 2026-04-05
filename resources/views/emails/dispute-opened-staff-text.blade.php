{{ __('Mail body dispute opened staff intro') }}

- {{ __('Event') }}: {{ $dispute->attendance?->event?->title_en ?? '—' }}
- {{ __('Volunteer') }}: {{ $dispute->attendance?->user?->name ?? '—' }} ({{ $dispute->attendance?->user?->email ?? '—' }})
- {{ __('Dispute ID') }}: {{ $dispute->id }}

{{ __('Description') }}:
{{ $dispute->description }}

{{ __('Review dispute in admin') }}: {{ route('admin.disputes.show', array_merge(['dispute' => $dispute], \App\Support\PublicLocale::query()), true) }}

— {{ config('app.name') }}
