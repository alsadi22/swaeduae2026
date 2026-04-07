@php
    $appShellTitle = __('Site settings').' — '.__('SwaedUAE');
    $logoUrl = $setting->headerLogoPublicUrl();
@endphp
<x-admin-layout :title="$appShellTitle" :meta-description="__('site.meta_description')">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Site settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-2 border-b border-gray-100 text-sm text-gray-600">
                    <p>{{ __('Site settings intro') }}</p>
                </div>
                @if (session('status'))
                    <div class="mx-6 mt-4 rounded-md bg-green-50 p-4 text-sm text-green-800" role="status">
                        {{ session('status') }}
                    </div>
                @endif
                <form method="post" action="{{ route('admin.site-settings.update', $adminLocaleQ) }}" enctype="multipart/form-data" class="p-6 space-y-8">
                    @csrf
                    @method('PUT')

                    <div>
                        <h3 class="text-sm font-bold text-gray-900">{{ __('Home hero') }}</h3>
                        <p class="mt-1 text-xs text-gray-500">{{ __('Home hero help') }}</p>
                        <div class="mt-4 space-y-4">
                            <div>
                                <x-input-label for="hero_mission_en" :value="__('Hero headline (English)')" />
                                <textarea id="hero_mission_en" name="hero_mission_en" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('hero_mission_en', $setting->hero_mission_en) }}</textarea>
                                <x-input-error :messages="$errors->get('hero_mission_en')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="hero_mission_ar" :value="__('Hero headline (Arabic)')" />
                                <textarea id="hero_mission_ar" name="hero_mission_ar" rows="3" dir="rtl" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('hero_mission_ar', $setting->hero_mission_ar) }}</textarea>
                                <x-input-error :messages="$errors->get('hero_mission_ar')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="hero_subline_en" :value="__('Hero subline (English)')" />
                                <textarea id="hero_subline_en" name="hero_subline_en" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('hero_subline_en', $setting->hero_subline_en) }}</textarea>
                                <x-input-error :messages="$errors->get('hero_subline_en')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="hero_subline_ar" :value="__('Hero subline (Arabic)')" />
                                <textarea id="hero_subline_ar" name="hero_subline_ar" rows="3" dir="rtl" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('hero_subline_ar', $setting->hero_subline_ar) }}</textarea>
                                <x-input-error :messages="$errors->get('hero_subline_ar')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-bold text-gray-900">{{ __('Header logo') }}</h3>
                        <p class="mt-1 text-xs text-gray-500">{{ __('Header logo help') }}</p>
                        @if ($logoUrl)
                            <div class="mt-4 flex items-center gap-4">
                                <img src="{{ $logoUrl }}" alt="" class="h-16 w-auto max-w-xs rounded-lg border border-gray-200 object-contain bg-white p-2" />
                            </div>
                            <label class="mt-4 inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="remove_header_logo" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('remove_header_logo')) />
                                {{ __('Remove header logo (use default icon)') }}
                            </label>
                        @endif
                        <div class="mt-4">
                            <x-input-label for="header_logo" :value="__('Upload logo image')" />
                            <input id="header_logo" name="header_logo" type="file" accept=".jpg,.jpeg,.png,.webp,.gif,.svg,image/jpeg,image/png,image/webp,image/gif,image/svg+xml" class="mt-1 block w-full text-sm text-gray-600" />
                            <x-input-error :messages="$errors->get('header_logo')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex items-center gap-4 border-t border-gray-100 pt-6">
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                        <a href="{{ route('admin.cms-pages.index', $adminLocaleQ) }}" class="text-sm text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
