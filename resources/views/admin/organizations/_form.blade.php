@if ($errors->any())
    <div class="rounded-md bg-red-50 p-4 text-sm text-red-800 mb-6" role="alert">
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="space-y-4">
    <div>
        <x-input-label for="name_en" :value="__('Name (English)')" />
        <x-text-input id="name_en" name="name_en" type="text" class="mt-1 block w-full" :value="old('name_en', $organization->name_en)" required autofocus />
        <x-input-error :messages="$errors->get('name_en')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="name_ar" :value="__('Name (Arabic)')" />
        <x-text-input id="name_ar" name="name_ar" type="text" class="mt-1 block w-full" :value="old('name_ar', $organization->name_ar)" />
        <x-input-error :messages="$errors->get('name_ar')" class="mt-2" />
    </div>
</div>
