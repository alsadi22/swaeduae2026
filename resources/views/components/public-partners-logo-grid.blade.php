@props([
    'partners' => [],
])

@if (count($partners) > 0)
    <ul {{ $attributes->merge(['class' => 'flex flex-wrap items-center justify-center gap-8 sm:gap-12']) }}>
        @foreach ($partners as $partner)
            @php
                $plabel = app()->getLocale() === 'ar' && ! empty($partner['label_ar']) ? $partner['label_ar'] : ($partner['label'] ?? '');
                $purl = $partner['url'] ?? '#';
                $plogo = $partner['logo'] ?? '';
            @endphp
            <li>
                <a href="{{ $purl }}" class="group flex flex-col items-center gap-2 text-center focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2" target="_blank" rel="noopener noreferrer">
                    @if ($plogo !== '')
                        <span class="flex h-16 w-40 items-center justify-center rounded-xl bg-slate-50 px-3 py-2 ring-1 ring-slate-200/80 transition group-hover:bg-white group-hover:shadow-sm sm:h-20 sm:w-48">
                            <img src="{{ \Illuminate\Support\Str::startsWith($plogo, ['http://', 'https://']) ? $plogo : url($plogo) }}" alt="{{ $plabel }}" class="max-h-14 max-w-full object-contain opacity-90 group-hover:opacity-100" width="160" height="64" loading="lazy" decoding="async">
                        </span>
                    @endif
                    <span class="max-w-[10rem] text-xs font-semibold text-slate-600 group-hover:text-emerald-900">{{ $plabel }}</span>
                </a>
            </li>
        @endforeach
    </ul>
@endif
