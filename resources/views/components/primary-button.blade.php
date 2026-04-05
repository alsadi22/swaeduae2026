<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center rounded-xl border border-transparent bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition duration-200 ease-out-soft hover:bg-emerald-800 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 active:scale-[0.99] disabled:opacity-50']) }}>
    {{ $slot }}
</button>
