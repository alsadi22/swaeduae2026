@php
    $appShellTitle = __('Add gallery photo').' — '.__('SwaedUAE');
@endphp
<x-admin-layout :title="$appShellTitle" :meta-description="__('site.meta_description')">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add gallery photo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="post" action="{{ route('admin.gallery-images.store', $adminLocaleQ) }}" enctype="multipart/form-data" class="p-6 space-y-6">
                    @csrf
                    <div>
                        <x-input-label for="image" :value="__('Image file')" />
                        <input id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif" required class="mt-1 block w-full text-sm text-gray-600" />
                        <p class="mt-1 text-xs text-gray-500">{{ __('Gallery upload hint') }}</p>
                        <x-input-error :messages="$errors->get('image')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="alt_text_en" :value="__('Alt text (English)')" />
                        <x-text-input id="alt_text_en" name="alt_text_en" type="text" class="mt-1 block w-full" :value="old('alt_text_en')" />
                        <x-input-error :messages="$errors->get('alt_text_en')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="alt_text_ar" :value="__('Alt text (Arabic)')" />
                        <x-text-input id="alt_text_ar" name="alt_text_ar" type="text" class="mt-1 block w-full" dir="rtl" :value="old('alt_text_ar')" />
                        <x-input-error :messages="$errors->get('alt_text_ar')" class="mt-2" />
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="hidden" name="is_visible" value="0" />
                        <input type="checkbox" name="is_visible" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('is_visible', true)) />
                        {{ __('Show on public gallery') }}
                    </label>
                    <div class="flex items-center gap-4 pt-2">
                        <x-primary-button>{{ __('Upload') }}</x-primary-button>
                        <a href="{{ route('admin.gallery-images.index', $adminLocaleQ) }}" class="text-sm text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
