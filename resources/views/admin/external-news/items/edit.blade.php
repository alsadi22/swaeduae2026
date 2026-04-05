<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Review external item') }}</h2>
            <a href="{{ route('admin.external-news-items.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">{{ __('Back to list') }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800" role="status">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="rounded-md bg-red-50 p-4 text-sm text-red-800" role="alert">
                    <ul class="list-disc ps-5">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white p-6 shadow-sm sm:rounded-lg space-y-4">
                <p class="text-sm text-gray-600">{{ __('Original title') }}</p>
                <p class="font-medium text-gray-900">{{ $item->original_title }}</p>
                @if ($item->original_summary)
                    <p class="text-sm text-gray-600 mt-2">{{ __('Imported summary') }}</p>
                    <p class="text-sm text-gray-800">{{ $item->original_summary }}</p>
                @endif
                <div class="flex flex-wrap gap-3 pt-2 text-sm">
                    <span class="rounded-full bg-slate-100 px-2 py-0.5">{{ __('Source') }}: {{ $item->source->name }}</span>
                    <span class="rounded-full bg-slate-100 px-2 py-0.5">{{ __('Status') }}: {{ $item->status }}</span>
                    @if ($item->external_url)
                        <a href="{{ $item->external_url }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-indigo-600 hover:text-indigo-900">{{ __('Open original article') }}</a>
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                @if ($item->status === 'pending_review')
                    <form method="post" action="{{ route('admin.external-news-items.approve', $item) }}">
                        @csrf
                        <button type="submit" class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-800 ring-1 ring-gray-300 hover:bg-gray-50">{{ __('Approve') }}</button>
                    </form>
                @endif
                @if (in_array($item->status, ['pending_review', 'approved'], true))
                    <form method="post" action="{{ route('admin.external-news-items.publish', $item) }}">
                        @csrf
                        <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">{{ __('Publish') }}</button>
                    </form>
                @endif
                @if ($item->status === 'published')
                    <form method="post" action="{{ route('admin.external-news-items.unpublish', $item) }}">
                        @csrf
                        <button type="submit" class="rounded-md bg-amber-100 px-4 py-2 text-sm font-semibold text-amber-900 hover:bg-amber-200">{{ __('Unpublish') }}</button>
                    </form>
                @endif
                @if ($item->status !== 'published')
                    <form method="post" action="{{ route('admin.external-news-items.reject', $item) }}" onsubmit="return confirm(@json(__('Reject this item?')));">
                        @csrf
                        <button type="submit" class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">{{ __('Reject') }}</button>
                    </form>
                @endif
            </div>

            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <h3 class="text-sm font-semibold text-gray-900">{{ __('Display & visibility') }}</h3>
                <form method="post" action="{{ route('admin.external-news-items.update', $item) }}" class="mt-6 space-y-5">
                    @csrf
                    @method('put')
                    <div>
                        <x-input-label for="nti_en" :value="__('Display title (English)')" />
                        <x-text-input id="nti_en" name="normalized_title_en" type="text" class="mt-1 block w-full" :value="old('normalized_title_en', $item->normalized_title_en)" />
                    </div>
                    <div>
                        <x-input-label for="nti_ar" :value="__('Display title (Arabic)')" />
                        <x-text-input id="nti_ar" name="normalized_title_ar" type="text" class="mt-1 block w-full" dir="rtl" :value="old('normalized_title_ar', $item->normalized_title_ar)" />
                    </div>
                    <div>
                        <x-input-label for="ns_en" :value="__('Display summary (English)')" />
                        <textarea id="ns_en" name="normalized_summary_en" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('normalized_summary_en', $item->normalized_summary_en) }}</textarea>
                    </div>
                    <div>
                        <x-input-label for="ns_ar" :value="__('Display summary (Arabic)')" />
                        <textarea id="ns_ar" name="normalized_summary_ar" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" dir="rtl">{{ old('normalized_summary_ar', $item->normalized_summary_ar) }}</textarea>
                    </div>
                    <div>
                        <x-input-label for="local_img" :value="__('Feature image URL (optional)')" />
                        <x-text-input id="local_img" name="local_feature_image" type="url" class="mt-1 block w-full" :value="old('local_feature_image', $item->local_feature_image)" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <input type="hidden" name="is_featured" value="0">
                            <input id="f_feat" type="checkbox" name="is_featured" value="1" class="rounded border-gray-300" {{ old('is_featured', $item->is_featured) ? 'checked' : '' }}>
                            <x-input-label for="f_feat" :value="__('Featured (sort boost)')" class="!mb-0" />
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="hidden" name="show_on_home" value="0">
                            <input id="f_home" type="checkbox" name="show_on_home" value="1" class="rounded border-gray-300" {{ old('show_on_home', $item->show_on_home) ? 'checked' : '' }}>
                            <x-input-label for="f_home" :value="__('Show on homepage (when published)')" class="!mb-0" />
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="hidden" name="show_in_media_center" value="0">
                            <input id="f_mc" type="checkbox" name="show_in_media_center" value="1" class="rounded border-gray-300" {{ old('show_in_media_center', $item->show_in_media_center) ? 'checked' : '' }}>
                            <x-input-label for="f_mc" :value="__('Show in media center (when published)')" class="!mb-0" />
                        </div>
                    </div>
                    <x-primary-button>{{ __('Save changes') }}</x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
