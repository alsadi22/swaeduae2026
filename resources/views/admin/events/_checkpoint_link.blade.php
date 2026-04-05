<div class="mt-8 overflow-hidden border border-gray-200 bg-gray-50/80 shadow-sm sm:rounded-lg">
    <div class="border-b border-gray-200 px-6 py-4">
        <h3 class="text-sm font-semibold text-gray-900">{{ __('Attendance checkpoint & QR') }}</h3>
        <p class="mt-1 text-xs text-gray-600">{{ __('Attendance checkpoint hint') }}</p>
    </div>
    <div class="space-y-4 p-6 text-sm text-gray-700">
        <div>
            <span class="font-semibold text-gray-900">{{ __('Event UUID') }}</span>
            <p class="mt-1 break-all font-mono text-xs text-gray-800" id="event-uuid-value">{{ $event->uuid }}</p>
            <button type="button" class="mt-2 text-xs font-semibold text-indigo-700 hover:text-indigo-900" onclick="navigator.clipboard.writeText(document.getElementById('event-uuid-value').textContent.trim())">{{ __('Copy UUID') }}</button>
        </div>

        <form method="post" action="{{ route($checkpointSignedUrlRoute ?? 'admin.events.checkpoint-signed-url', $event) }}" class="flex flex-wrap items-end gap-3 border-t border-gray-200 pt-4">
            @csrf
            <div>
                <x-input-label for="checkpoint_link_days" :value="__('Signed link validity (days)')" />
                <x-text-input id="checkpoint_link_days" name="days" type="number" min="1" max="365" class="mt-1 block w-28" :value="old('days', 7)" />
                <x-input-error :messages="$errors->get('days')" class="mt-1" />
            </div>
            <x-primary-button type="submit">{{ __('Generate signed checkpoint URL') }}</x-primary-button>
        </form>

        <p class="text-xs text-gray-600">
            {{ __('CLI equivalent') }}:
            <code class="rounded bg-white px-1 py-0.5 font-mono text-[11px] text-gray-800">php artisan swaeduae:attendance-link {{ $event->uuid }}</code>
        </p>

        @if (session('checkpoint_signed_url'))
            <div class="rounded-md border border-indigo-200 bg-white p-4">
                <p class="text-xs font-semibold text-gray-900">{{ __('Signed URL (encode in QR or share)') }}</p>
                <p class="mt-2 break-all font-mono text-[11px] leading-relaxed text-gray-800" id="checkpoint-signed-url">{{ session('checkpoint_signed_url') }}</p>
                <button type="button" class="mt-2 text-xs font-semibold text-indigo-700 hover:text-indigo-900" onclick="navigator.clipboard.writeText(document.getElementById('checkpoint-signed-url').textContent.trim())">{{ __('Copy URL') }}</button>
            </div>
        @endif
    </div>
</div>
