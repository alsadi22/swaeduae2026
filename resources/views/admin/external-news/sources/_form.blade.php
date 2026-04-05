@php
    /** @var \App\Models\ExternalNewsSource $source */
@endphp

<div class="space-y-6">
    <div>
        <x-input-label for="ens_name" :value="__('Name')" />
        <x-text-input id="ens_name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $source->name)" required />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>
    <div>
        <x-input-label for="ens_slug" :value="__('Slug (optional)')" />
        <x-text-input id="ens_slug" name="slug" type="text" class="mt-1 block w-full font-mono text-sm" :value="old('slug', $source->slug)" placeholder="ministry-example" />
        <x-input-error class="mt-2" :messages="$errors->get('slug')" />
    </div>
    <div>
        <x-input-label for="ens_type" :value="__('Source type')" />
        <select id="ens_type" name="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
            @foreach ([\App\Models\ExternalNewsSource::TYPE_RSS => 'RSS', \App\Models\ExternalNewsSource::TYPE_ATOM => 'Atom', \App\Models\ExternalNewsSource::TYPE_API => 'API (later)', \App\Models\ExternalNewsSource::TYPE_HTML_PARSER => 'HTML parser (later)', \App\Models\ExternalNewsSource::TYPE_MANUAL => 'Manual'] as $val => $label)
                <option value="{{ $val }}" {{ old('type', $source->type) === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('type')" />
    </div>
    <div>
        <x-input-label for="ens_endpoint" :value="__('Feed / endpoint URL')" />
        <x-text-input id="ens_endpoint" name="endpoint_url" type="url" class="mt-1 block w-full" :value="old('endpoint_url', $source->endpoint_url)" />
        <x-input-error class="mt-2" :messages="$errors->get('endpoint_url')" />
    </div>
    <div>
        <x-input-label for="ens_website" :value="__('Website URL (optional)')" />
        <x-text-input id="ens_website" name="website_url" type="url" class="mt-1 block w-full" :value="old('website_url', $source->website_url)" />
        <x-input-error class="mt-2" :messages="$errors->get('website_url')" />
    </div>
    <div>
        <x-input-label for="ens_logo" :value="__('Logo URL (optional)')" />
        <x-text-input id="ens_logo" name="source_logo" type="url" class="mt-1 block w-full" :value="old('source_logo', $source->source_logo)" />
        <x-input-error class="mt-2" :messages="$errors->get('source_logo')" />
    </div>
    <div>
        <x-input-label for="ens_label_en" :value="__('Source label (English)')" />
        <x-text-input id="ens_label_en" name="label_en" type="text" class="mt-1 block w-full" :value="old('label_en', $source->label_en)" required />
        <x-input-error class="mt-2" :messages="$errors->get('label_en')" />
    </div>
    <div>
        <x-input-label for="ens_label_ar" :value="__('Source label (Arabic)')" />
        <x-text-input id="ens_label_ar" name="label_ar" type="text" class="mt-1 block w-full" dir="rtl" :value="old('label_ar', $source->label_ar)" required />
        <x-input-error class="mt-2" :messages="$errors->get('label_ar')" />
    </div>
    <div>
        <x-input-label for="ens_interval" :value="__('Fetch interval (minutes)')" />
        <x-text-input id="ens_interval" name="fetch_interval_minutes" type="number" min="5" max="10080" class="mt-1 block w-32" :value="old('fetch_interval_minutes', $source->fetch_interval_minutes ?: 360)" required />
        <x-input-error class="mt-2" :messages="$errors->get('fetch_interval_minutes')" />
    </div>
    <div>
        <x-input-label for="ens_priority" :value="__('Priority (higher first)')" />
        <x-text-input id="ens_priority" name="priority" type="number" min="0" max="1000" class="mt-1 block w-32" :value="old('priority', $source->priority ?? 0)" />
        <x-input-error class="mt-2" :messages="$errors->get('priority')" />
    </div>
    <div class="flex items-center gap-2">
        <input type="hidden" name="is_active" value="0">
        <input id="ens_active" type="checkbox" name="is_active" value="1" class="rounded border-gray-300" {{ old('is_active', $source->exists ? ($source->is_active ? '1' : '0') : '1') === '1' ? 'checked' : '' }}>
        <x-input-label for="ens_active" :value="__('Active')" class="!mb-0" />
    </div>
</div>
