@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center rounded-lg bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-900 shadow-sm ring-1 ring-emerald-100/90 transition duration-200 ease-out-soft focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/50'
            : 'inline-flex items-center rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 transition duration-200 ease-out-soft hover:bg-slate-100/80 hover:text-emerald-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/30';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
