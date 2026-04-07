@props([
    'testId' => 'copy-filtered-list-url',
    'label' => null,
    'copiedLabel' => null,
])
@php
    $buttonLabel = $label ?? __('Copy link to filtered list');
    $buttonCopiedLabel = $copiedLabel ?? __('Link copied');
@endphp
<div {{ $attributes->merge(['class' => 'inline-flex']) }} x-data="{ copied: false, copiedMessage: {{ \Illuminate\Support\Js::from($buttonCopiedLabel) }} }">
    <button
        type="button"
        data-testid="{{ $testId }}"
        aria-label="{{ $buttonLabel }}"
        class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:border-emerald-200 hover:text-emerald-900"
        @click="navigator.clipboard.writeText(window.location.href).then(() => { copied = true; setTimeout(() => copied = false, 2000); }).catch(() => {})"
    >
        <span x-show="!copied">{{ $buttonLabel }}</span>
        <span x-show="copied" x-cloak>{{ $buttonCopiedLabel }}</span>
    </button>
    <span class="sr-only" role="status" aria-live="polite" x-text="copied ? copiedMessage : ''"></span>
</div>
