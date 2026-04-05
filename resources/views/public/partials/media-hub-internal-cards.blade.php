@foreach ($pages as $page)
    <article class="card-surface flex flex-col overflow-hidden">
        <div class="h-32 bg-gradient-to-br from-emerald-100 via-slate-50 to-amber-50"></div>
        <div class="flex flex-1 flex-col p-5">
            <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">{{ __('Our article') }}</p>
            <h2 class="font-display mt-2 font-bold text-slate-900">{{ $page->title }}</h2>
            @if ($page->excerpt)
                <p class="mt-2 text-sm text-slate-600">{{ $page->excerpt }}</p>
            @endif
            <div class="mt-auto pt-4">
                <a href="{{ $page->absolutePublicUrl() }}" class="text-sm font-bold text-emerald-800 hover:underline">{{ __('Read more') }} →</a>
            </div>
        </div>
    </article>
@endforeach
