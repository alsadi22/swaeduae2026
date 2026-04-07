@php
    $appShellTitle = __('Gallery photos').' — '.__('SwaedUAE');
@endphp
<x-admin-layout :title="$appShellTitle" :meta-description="__('site.meta_description')">
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Gallery photos') }}
            </h2>
            <a href="{{ route('admin.gallery-images.create', $adminLocaleQ) }}" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold text-white hover:bg-gray-700">
                {{ __('Add photo') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800" role="status">
                    {{ session('status') }}
                </div>
            @endif
            <p class="mb-6 text-sm text-gray-600">{{ __('Gallery photos admin intro') }}</p>

            @if ($images->isEmpty())
                <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-12 text-center text-gray-600">
                    {{ __('No gallery photos yet.') }}
                </div>
            @else
                <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Preview') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Sort order') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Visible') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Alt (EN)') }}</th>
                                <th class="px-4 py-3 text-end text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($images as $img)
                                <tr>
                                    <td class="px-4 py-3">
                                        <img src="{{ $img->publicUrl() }}" alt="" class="h-14 w-20 rounded object-cover ring-1 ring-gray-200" />
                                    </td>
                                    <td class="px-4 py-3 font-mono text-gray-800">{{ $img->sort_order }}</td>
                                    <td class="px-4 py-3">{{ $img->is_visible ? __('Yes') : __('No') }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ \Illuminate\Support\Str::limit($img->alt_text_en ?? '—', 40) }}</td>
                                    <td class="px-4 py-3 text-end space-x-2">
                                        <a href="{{ route('admin.gallery-images.edit', array_merge(['gallery_image' => $img], $adminLocaleQ)) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('Edit') }}</a>
                                        <form action="{{ route('admin.gallery-images.destroy', array_merge(['gallery_image' => $img], $adminLocaleQ)) }}" method="post" class="inline" onsubmit="return confirm(@json(__('Delete this photo?')));">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800">{{ __('Delete') }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">
                    {{ $images->links() }}
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
