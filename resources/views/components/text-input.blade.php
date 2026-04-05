@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500']) }}>
