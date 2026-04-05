@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full rounded-lg border-s-4 border-emerald-600 bg-emerald-50 py-3 ps-3 pe-4 text-start text-base font-semibold text-emerald-900 transition duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40'
            : 'block w-full rounded-lg border-s-4 border-transparent py-3 ps-3 pe-4 text-start text-base font-medium text-slate-600 transition duration-200 hover:border-slate-200 hover:bg-slate-50 hover:text-emerald-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-300/50';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
