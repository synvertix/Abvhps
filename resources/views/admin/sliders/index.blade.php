@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100/60 flex flex-col md:flex-row select-none">

    @include('admin.partials.sidebar')

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                @include('admin.partials.header_button')
                <span class="text-sm font-black text-brandGray uppercase tracking-wider">System View:</span>
                <span class="bg-orange-100 text-brandOrange text-[9px] font-black px-2.5 py-0.5 rounded border border-orange-200 tracking-widest uppercase shadow-sm">Hero Slides</span>
            </div>
            <div class="text-right text-[10px] font-mono font-black text-gray-500">
                System Sync: {{ \Carbon\Carbon::now()->format('d-M-Y H:i') }} IST
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-6 space-y-6">
            <div class="flex justify-between items-center">
                <h3 class="text-xs font-black text-brandGray uppercase tracking-wider flex items-center gap-1.5">
                    🎞️ Home Page Hero Slides
                </h3>
                <a href="{{ route('admin.sliders.create') }}" class="bg-brandOrange hover:bg-orange-700 text-white font-black text-[10px] px-4 py-2 rounded-lg shadow-sm uppercase tracking-wide transition">
                    + Add New Slide
                </a>
            </div>

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-xs font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-xl text-xs font-semibold leading-relaxed">
                Active slides rotate on the home page hero (lowest order first). Slides without an image show over the hero video.
                If no slide is active, the website automatically shows its 3 built-in devotional slides, so the hero is never empty.
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-left text-xs font-semibold text-gray-700">
                        <thead class="bg-gray-100 text-[10px] font-black uppercase text-gray-600 tracking-wider text-center">
                            <tr>
                                <th class="px-4 py-3">Order</th>
                                <th class="px-4 py-3">Image</th>
                                <th class="px-4 py-3 text-left">Title / Subtitle</th>
                                <th class="px-4 py-3 text-left">Button</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white text-center">
                            @forelse($sliders as $slider)
                                <tr class="hover:bg-gray-50/60 transition-colors">
                                    <td class="px-4 py-3.5 font-mono text-brandOrange font-black text-sm">{{ $slider->sort_order }}</td>
                                    <td class="px-4 py-3.5">
                                        @if(!empty($slider->image_path))
                                            <img src="{{ asset('storage/' . $slider->image_path) }}" class="w-20 h-12 rounded-lg border object-cover mx-auto shadow-sm" alt="Slide image" onerror="this.replaceWith(Object.assign(document.createElement('span'),{className:'text-[9px] text-rose-500 font-black',textContent:'Image missing'}))">
                                        @else
                                            <div class="w-20 h-12 rounded-lg bg-gray-100 border flex items-center justify-center text-gray-400 text-[9px] mx-auto">Video only</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-left max-w-sm">
                                        <div class="font-bold text-gray-900 normal-case">{{ $slider->title }}</div>
                                        <div class="text-gray-500 font-medium normal-case truncate">{{ $slider->subtitle }}</div>
                                    </td>
                                    <td class="px-4 py-3.5 text-left normal-case">
                                        @if($slider->cta_label)
                                            <span class="font-bold text-gray-800">{{ $slider->cta_label }}</span>
                                            <span class="block text-[10px] text-gray-400 font-mono truncate max-w-[180px]">{{ $slider->cta_url }}</span>
                                        @else
                                            <span class="text-gray-400">&mdash;</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5">
                                        @if($slider->is_active)
                                            <span class="bg-green-50 text-green-600 text-[9px] font-black px-2.5 py-0.5 rounded border border-green-100 uppercase tracking-wider">Visible</span>
                                        @else
                                            <span class="bg-gray-50 text-gray-500 text-[9px] font-black px-2.5 py-0.5 rounded border border-gray-200 uppercase tracking-wider">Hidden</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3.5">
                                        <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                            <a href="{{ route('admin.sliders.edit', $slider->id) }}" class="bg-orange-500 hover:bg-orange-600 text-white font-black text-[9px] px-3 py-1 rounded shadow-sm uppercase transition">Edit</a>
                                            <form action="{{ route('admin.sliders.toggle', $slider->id) }}" method="POST" class="inline-block">
                                                @csrf
                                                <button type="submit" class="bg-slate-600 hover:bg-slate-700 text-white font-black text-[9px] px-3 py-1 rounded shadow-sm uppercase transition">
                                                    {{ $slider->is_active ? 'Hide' : 'Show' }}
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.sliders.destroy', $slider->id) }}" method="POST" onsubmit="return confirm('Delete this hero slide permanently?');" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="bg-rose-500 hover:bg-rose-600 text-white font-black text-[9px] px-3 py-1 rounded shadow-sm uppercase transition">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center font-bold text-gray-400 uppercase tracking-wider">
                                        No custom slides yet &mdash; the 3 built-in slides are showing on the home page.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection
