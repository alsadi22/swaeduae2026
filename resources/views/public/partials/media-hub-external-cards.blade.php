@foreach ($items as $ext)
    <article class="card-surface flex flex-col overflow-hidden ring-1 ring-amber-100/80">
        @if ($ext->featureImageUrl())
            <div class="h-32 overflow-hidden bg-slate-100">
                <img src="{{ $ext->featureImageUrl() }}" alt="{{ $ext->titleForLocale() }}" class="h-full w-full object-cover" loading="lazy" width="400" height="200">
            </div>
        @else
            <div class="h-32 bg-gradient-to-br from-slate-100 to-amber-50"></div>
        @endif
        <div class="flex flex-1 flex-col p-5">
            <p class="text-xs font-bold uppercase tracking-wider text-amber-800">{{ __('External source') }} · {{ $ext->source->labelForLocale() }}</p>
            <h2 class="font-display mt-2 font-bold text-slate-900">{{ $ext->titleForLocale() }}</h2>
            @if ($ext->summaryForLocale())
                <p class="mt-2 text-sm text-slate-600">{{ \Illuminate\Support\Str::limit($ext->summaryForLocale(), 180) }}</p>
            @endif
            @if ($ext->original_published_at)
                <p class="mt-2 text-xs text-slate-500">{{ __('Original date') }}: {{ $ext->original_published_at->locale(app()->getLocale())->isoFormat('LL') }}</p>
            @endif
            <div class="mt-auto flex flex-wrap gap-3 pt-4">
                <a href="{{ $ext->absolutePublicUrl() }}" class="text-sm font-bold text-emerald-800 hover:underline">{{ __('View details') }}</a>
                @if ($ext->external_url)
                    <a href="{{ $ext->external_url }}" target="_blank" rel="noopener noreferrer" class="text-sm font-bold text-slate-600 hover:text-emerald-900">{{ __('Visit source') }} →</a>
                @endif
            </div>
        </div>
    </article>
@endforeach
