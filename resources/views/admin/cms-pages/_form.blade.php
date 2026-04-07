@php
    /** @var \App\Models\CmsPage $page */
@endphp

<div class="space-y-6">
    <div class="grid gap-6 sm:grid-cols-2">
        <div>
            <x-input-label for="slug" value="{{ __('URL slug') }}" />
            <x-text-input id="slug" class="block mt-1 w-full font-mono text-sm" type="text" name="slug" :value="old('slug', $page->slug)" required
                pattern="[a-z0-9]+(-[a-z0-9]+)*"
                :title="__('Slug pattern hint')" />
            <x-input-error :messages="$errors->get('slug')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="locale" value="{{ __('Language') }}" />
            <select id="locale" name="locale" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                <option value="en" @selected(old('locale', $page->locale) === 'en')>{{ __('Language English') }}</option>
                <option value="ar" @selected(old('locale', $page->locale) === 'ar')>{{ __('Language Arabic') }}</option>
            </select>
            <x-input-error :messages="$errors->get('locale')" class="mt-2" />
        </div>
    </div>

    <div>
        <x-input-label for="title" value="{{ __('Title') }}" />
        <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title', $page->title)" required />
        <x-input-error :messages="$errors->get('title')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="meta_description" value="{{ __('Meta description') }}" />
        <textarea id="meta_description" name="meta_description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('meta_description', $page->meta_description) }}</textarea>
        <x-input-error :messages="$errors->get('meta_description')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="og_image_upload" value="{{ __('Upload share image (Open Graph)') }}" />
        <input id="og_image_upload" name="og_image_upload" type="file" accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif" class="mt-1 block w-full text-sm text-gray-600" />
        <p class="mt-1 text-xs text-gray-500">{{ __('CMS share image upload help') }}</p>
        <x-input-error :messages="$errors->get('og_image_upload')" class="mt-2" />
        @php($ogPreviewUrl = filled(old('og_image', $page->og_image)) ? \App\Models\CmsPage::resolveShareImageUrl(old('og_image', $page->og_image)) : null)
        @if ($ogPreviewUrl)
            <div class="mt-3">
                <p class="text-xs font-medium text-gray-600">{{ __('Current share image preview') }}</p>
                <img src="{{ $ogPreviewUrl }}" alt="" class="mt-1 max-h-36 max-w-full rounded-md border border-gray-200 object-contain" />
            </div>
        @endif
        @if ($page->exists && old('og_image', $page->og_image))
            <label class="mt-3 inline-flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="remove_og_image" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('remove_og_image')) />
                {{ __('Remove share image') }}
            </label>
        @endif
        <div class="mt-6 border-t border-gray-100 pt-4">
            <x-input-label for="og_image" value="{{ __('Share image URL (optional)') }}" />
            <x-text-input id="og_image" class="block mt-1 w-full font-mono text-sm" type="text" name="og_image" :value="old('og_image', $page->og_image)"
                placeholder="https://… or /images/og.jpg" />
            <p class="mt-1 text-xs text-gray-500">{{ __('HTTPS URL or site path (e.g. /images/og.jpg). ~1200×630. Empty uses DEFAULT_OG_IMAGE_URL if set.') }}</p>
            <x-input-error :messages="$errors->get('og_image')" class="mt-2" />
        </div>
    </div>

    <div>
        <x-input-label for="excerpt" value="{{ __('Excerpt') }}" />
        <textarea id="excerpt" name="excerpt" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('excerpt', $page->excerpt) }}</textarea>
        <x-input-error :messages="$errors->get('excerpt')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="body" value="{{ __('Body (Markdown)') }}" />
        <textarea id="body" name="body" rows="16" class="mt-1 block w-full rounded-md border-gray-300 font-mono text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('body', $page->body) }}</textarea>
        <x-input-error :messages="$errors->get('body')" class="mt-2" />
    </div>

    <div class="rounded-md border border-amber-100 bg-amber-50/80 p-4">
        <label class="inline-flex items-start gap-3">
            <input type="checkbox" name="allow_partial_locale_publish" value="1" class="mt-1 rounded border-gray-300 text-amber-700 shadow-sm focus:ring-amber-500"
                @checked(old('allow_partial_locale_publish', false)) />
            <span>
                <span class="block text-sm font-medium text-gray-800">{{ __('Allow partial locale publish') }}</span>
                <span class="mt-0.5 block text-xs text-gray-600">{{ __('Allow partial locale publish help') }}</span>
            </span>
        </label>
    </div>

    <div class="grid gap-6 sm:grid-cols-2">
        <div>
            <x-input-label for="status" value="{{ __('Status') }}" />
            <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                @foreach ([
                    \App\Models\CmsPage::STATUS_DRAFT => __('Draft'),
                    \App\Models\CmsPage::STATUS_IN_REVIEW => __('In review'),
                    \App\Models\CmsPage::STATUS_PUBLISHED => __('Published'),
                    \App\Models\CmsPage::STATUS_ARCHIVED => __('Archived'),
                ] as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $page->status ?? \App\Models\CmsPage::STATUS_DRAFT) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('status')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="published_at" value="{{ __('Published at') }}" />
            <x-text-input id="published_at" class="block mt-1 w-full" type="datetime-local" name="published_at"
                :value="old('published_at', $page->published_at?->format('Y-m-d\TH:i'))" />
            <p class="mt-1 text-xs text-gray-500">{{ __('Required for public visibility when status is Published.') }}</p>
            <x-input-error :messages="$errors->get('published_at')" class="mt-2" />
        </div>
    </div>

    <div class="rounded-md border border-gray-200 bg-gray-50 p-4">
        <input type="hidden" name="show_on_home" value="0" />
        <label class="inline-flex items-start gap-3">
            <input type="checkbox" name="show_on_home" value="1" class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                @checked(old('show_on_home', $page->show_on_home ?? false)) />
            <span>
                <span class="block text-sm font-medium text-gray-700">{{ __('Show on home page') }}</span>
                <span class="mt-0.5 block text-xs text-gray-500">{{ __('When published, this page can appear in the featured strip on the home page.') }}</span>
            </span>
        </label>
        <x-input-error :messages="$errors->get('show_on_home')" class="mt-2" />
    </div>

    <div class="rounded-md border border-gray-200 bg-gray-50 p-4">
        <input type="hidden" name="show_on_programs" value="0" />
        <label class="inline-flex items-start gap-3">
            <input type="checkbox" name="show_on_programs" value="1" class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                @checked(old('show_on_programs', $page->show_on_programs ?? false)) />
            <span>
                <span class="block text-sm font-medium text-gray-700">{{ __('Show on programs page') }}</span>
                <span class="mt-0.5 block text-xs text-gray-500">{{ __('When published, list this page in the “Featured program pages” grid on /programs (not for institutional slugs).') }}</span>
            </span>
        </label>
        <x-input-error :messages="$errors->get('show_on_programs')" class="mt-2" />
    </div>

    <div class="rounded-md border border-gray-200 bg-gray-50 p-4">
        <input type="hidden" name="show_on_media" value="0" />
        <label class="inline-flex items-start gap-3">
            <input type="checkbox" name="show_on_media" value="1" class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                @checked(old('show_on_media', $page->show_on_media ?? true)) />
            <span>
                <span class="block text-sm font-medium text-gray-700">{{ __('Show in media center') }}</span>
                <span class="mt-0.5 block text-xs text-gray-500">{{ __('When published, list this page in the Media center “Our news” stream and home “Latest news” teasers (institutional slugs never appear there).') }}</span>
            </span>
        </label>
        <x-input-error :messages="$errors->get('show_on_media')" class="mt-2" />
    </div>

    <div class="rounded-md border border-gray-200 bg-gray-50 p-4">
        <input type="hidden" name="show_in_gallery" value="0" />
        <label class="inline-flex items-start gap-3">
            <input type="checkbox" name="show_in_gallery" value="1" class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                @checked(old('show_in_gallery', $page->show_in_gallery ?? false)) />
            <span>
                <span class="block text-sm font-medium text-gray-700">{{ __('Show in gallery') }}</span>
                <span class="mt-0.5 block text-xs text-gray-500">{{ __('Show in gallery hint') }}</span>
            </span>
        </label>
        <x-input-error :messages="$errors->get('show_in_gallery')" class="mt-2" />
    </div>
</div>
