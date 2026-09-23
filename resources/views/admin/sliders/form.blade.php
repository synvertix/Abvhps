@extends('layouts.app')

@php
    $isEdit = !empty($slider);
    $action = $isEdit ? route('admin.sliders.update', $slider->id) : route('admin.sliders.store');
@endphp

@section('content')
<div class="min-h-screen bg-gray-100/60 flex flex-col md:flex-row select-none">

    @include('admin.partials.sidebar')

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                @include('admin.partials.header_button')
                <span class="text-sm font-black text-brandGray uppercase tracking-wider">System View:</span>
                <span class="bg-orange-100 text-brandOrange text-[9px] font-black px-2.5 py-0.5 rounded border border-orange-200 tracking-widest uppercase shadow-sm">{{ $isEdit ? 'Edit Hero Slide' : 'New Hero Slide' }}</span>
            </div>
            <a href="{{ route('admin.sliders.index') }}" class="text-[10px] font-black text-gray-500 hover:text-brandOrange uppercase tracking-wider">&larr; Back to slides</a>
        </header>

        <main class="flex-1 overflow-y-auto p-6 space-y-6 max-w-3xl">
            @if($errors->any())
                <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-xs font-semibold">
                    <ul class="list-disc pl-4 space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5 tracking-wider">Title *</label>
                        <input type="text" name="title" required maxlength="150" value="{{ old('title', $slider->title ?? '') }}" placeholder="e.g. Serve with Devotion" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-xs font-semibold focus:outline-none focus:border-brandOrange">
                    </div>

                    <div>
                        <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5 tracking-wider">Subtitle</label>
                        <textarea name="subtitle" rows="2" maxlength="300" placeholder="One or two lines shown under the title" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-xs font-semibold focus:outline-none focus:border-brandOrange">{{ old('subtitle', $slider->subtitle ?? '') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5 tracking-wider">Button Text (optional)</label>
                            <input type="text" name="cta_label" maxlength="60" value="{{ old('cta_label', $slider->cta_label ?? '') }}" placeholder="e.g. Make a Donation" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-xs font-semibold focus:outline-none focus:border-brandOrange">
                        </div>
                        <div>
                            <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5 tracking-wider">Button Link</label>
                            <input type="text" name="cta_url" maxlength="255" value="{{ old('cta_url', $slider->cta_url ?? '') }}" placeholder="/donations  or  https://..." class="w-full border border-gray-300 rounded-lg px-4 py-2 text-xs font-semibold focus:outline-none focus:border-brandOrange">
                            <p class="text-[10px] text-gray-400 mt-1">A page on this site (starting with /) or a secure https:// link. Fill both button fields or neither.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5 tracking-wider">Display Order * (1 = first)</label>
                            <input type="number" name="sort_order" required min="1" max="999" value="{{ old('sort_order', $nextOrder) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-xs font-semibold focus:outline-none focus:border-brandOrange">
                        </div>
                        <div class="flex items-end">
                            <label class="inline-flex items-center gap-2 text-xs font-bold text-gray-700 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-brandOrange focus:ring-brandOrange" {{ old('is_active', $isEdit ? $slider->is_active : true) ? 'checked' : '' }}>
                                Visible on the home page
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5 tracking-wider">Background Image (optional &middot; JPG / PNG / WebP &middot; max 3 MB)</label>
                        @if($isEdit && !empty($slider->image_path))
                            <div class="flex items-center gap-3 mb-2">
                                <img src="{{ asset('storage/' . $slider->image_path) }}" class="w-28 h-16 rounded-lg border object-cover shadow-sm" alt="Current slide image" onerror="this.style.display='none'">
                                <label class="inline-flex items-center gap-2 text-[11px] font-bold text-rose-600 cursor-pointer">
                                    <input type="checkbox" name="remove_image" value="1" class="rounded border-gray-300"> Remove current image
                                </label>
                            </div>
                        @endif
                        <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="block w-full text-xs text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-[11px] file:font-black file:bg-orange-50 file:text-brandOrange hover:file:bg-orange-100">
                        <p class="text-[10px] text-gray-400 mt-1">Wide images (about 1600&times;600) look best. Leave empty to show the text over the hero video.</p>
                    </div>

                    <div class="pt-2 flex items-center gap-3">
                        <button type="submit" class="bg-brandOrange hover:bg-orange-700 text-white font-black text-[11px] px-6 py-2.5 rounded-lg shadow-sm uppercase tracking-wide transition">
                            {{ $isEdit ? 'Save Changes' : 'Add Slide' }}
                        </button>
                        <a href="{{ route('admin.sliders.index') }}" class="text-[11px] font-black text-gray-500 hover:text-gray-800 uppercase tracking-wider">Cancel</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>
@endsection
