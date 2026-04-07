@php
    $appShellTitle = __('Edit gallery photo').' — '.__('SwaedUAE');
@endphp
<x-admin-layout :title="$appShellTitle" :meta-description="__('site.meta_description')">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit gallery photo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-100 p-6">
                    <p class="text-sm text-gray-600">{{ __('Current image') }}</p>
                    <img src="{{ $image->publicUrl() }}" alt="" class="mt-3 max-h-48 rounded-lg object-contain ring-1 ring-gray-200" />
                </div>
                <form method="post" action="{{ route('admin.gallery-images.update', array_merge(['gallery_image' => $image], $adminLocaleQ)) }}" enctype="multipart/form-data" class="p-6 space-y-6">
                    @csrf
                    @method('PUT')
                    <div>
                        <x-input-label for="image" :value="__('Replace image (optional)')" />
                        <input id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif" class="mt-1 block w-full text-sm text-gray-600" />
                        <x-input-error :messages="$errors->get('image')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="sort_order" :value="__('Sort order')" />
                        <x-text-input id="sort_order" name="sort_order" type="number" class="mt-1 block w-full" required min="0" max="999999" :value="old('sort_order', $image->sort_order)" />
                        <p class="mt-1 text-xs text-gray-500">{{ __('Gallery sort order help') }}</p>
                        <x-input-error :messages="$errors->get('sort_order')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="alt_text_en" :value="__('Alt text (English)')" />
                        <x-text-input id="alt_text_en" name="alt_text_en" type="text" class="mt-1 block w-full" :value="old('alt_text_en', $image->alt_text_en)" />
                        <x-input-error :messages="$errors->get('alt_text_en')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="alt_text_ar" :value="__('Alt text (Arabic)')" />
                        <x-text-input id="alt_text_ar" name="alt_text_ar" type="text" class="mt-1 block w-full" dir="rtl" :value="old('alt_text_ar', $image->alt_text_ar)" />
                        <x-input-error :messages="$errors->get('alt_text_ar')" class="mt-2" />
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="hidden" name="is_visible" value="0" />
                        <input type="checkbox" name="is_visible" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('is_visible', $image->is_visible)) />
                        {{ __('Show on public gallery') }}
                    </label>
                    <div class="flex items-center gap-4 pt-2">
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                        <a href="{{ route('admin.gallery-images.index', $adminLocaleQ) }}" class="text-sm text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
