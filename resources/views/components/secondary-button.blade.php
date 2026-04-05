<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center rounded-xl border border-slate-300/90 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition duration-200 ease-out-soft hover:border-slate-400 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40 focus-visible:ring-offset-2 active:scale-[0.99] disabled:opacity-40']) }}>
    {{ $slot }}
</button>
