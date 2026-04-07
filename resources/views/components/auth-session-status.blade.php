@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-green-600', 'role' => 'status', 'aria-live' => 'polite', 'data-testid' => 'auth-flash-status']) }}>
        {{ $status }}
    </div>
@endif
