{{-- Inline leadership (governance) block for About page; anchor #leadership for old /leadership links. --}}
@php
    $board = __('site.leadership_board');
    $board = is_array($board) ? $board : [];
@endphp
<section id="leadership" class="mt-16 border-t border-slate-200 pt-16" aria-labelledby="leadership-heading">
    <header class="max-w-3xl">
        <p class="text-xs font-bold uppercase tracking-wider text-emerald-800">{{ __('Governance') }}</p>
        <h2 id="leadership-heading" class="public-page-title mt-3">{{ __('Leadership') }}</h2>
        <p class="mt-6 text-lg leading-relaxed text-slate-600">{{ __('site.leadership_intro') }}</p>
        <div class="mt-6">
            <x-copy-filtered-list-url-button class="max-sm:[&_button]:w-full" test-id="leadership-copy-page-url" />
        </div>
    </header>

    <ul class="mt-12 grid list-none gap-6 sm:grid-cols-2 lg:grid-cols-3" role="list">
        @foreach ($board as $member)
            <li>
                <article class="card-surface flex h-full flex-col p-6 sm:p-7">
                    <div class="flex items-start gap-4">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-700 to-teal-900 text-lg font-bold text-white shadow-card ring-1 ring-emerald-950/10" aria-hidden="true">
                            {{ \Illuminate\Support\Str::substr($member['name'] ?? '?', 0, 1) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="font-display text-lg font-bold text-emerald-950">{{ $member['name'] ?? '' }}</h3>
                            <p class="mt-1 text-sm font-semibold text-emerald-800">{{ $member['role'] ?? '' }}</p>
                        </div>
                    </div>
                    <p class="mt-5 text-sm leading-relaxed text-slate-600">{{ $member['bio'] ?? '' }}</p>
                </article>
            </li>
        @endforeach
    </ul>

    <p class="mt-12 max-w-3xl text-sm text-slate-500">{{ __('site.leadership_footnote') }}</p>
</section>
