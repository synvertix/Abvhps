@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100/60 flex flex-col md:flex-row select-none">

    <!-- BLOCK 1: MASTER ADMINISTRATIVE LEFT SIDEBAR -->
    @include('admin.partials.sidebar')

    <!-- BLOCK 2: MASTER MAIN WORKSPACE VIEWPORT DESK -->
    <div class="flex-1 flex flex-col overflow-hidden">

        <!-- Workspace Top Status Banner Navbar -->
        <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                @include('admin.partials.header_button')
                <span class="text-sm font-black text-brandGray uppercase tracking-wider">System View:</span>
                <span class="bg-orange-100 text-brandOrange text-[9px] font-black px-2.5 py-0.5 rounded border border-orange-200 tracking-widest uppercase shadow-sm">Edit Blog</span>
            </div>
            <div class="text-right text-[10px] font-mono font-black text-gray-500">
                System Sync: {{ \Carbon\Carbon::now()->format('d-M-Y H:i') }} IST
            </div>
        </header>

        <!-- Dynamic Content Workspace Container -->
        <main class="flex-1 overflow-y-auto p-6 space-y-6">

            <!-- Header Title and Back Link -->
            <div class="flex justify-between items-center">
                <h3 class="text-xs font-black text-brandGray uppercase tracking-wider flex items-center gap-1.5">
                    📰 Edit Blog Post Form
                </h3>
                <a href="{{ route('admin.blogs.index') }}" class="bg-gray-800 hover:bg-gray-900 text-white font-black text-[10px] px-4 py-2 rounded-lg shadow-sm uppercase tracking-wide transition">
                    ← Back To List
                </a>
            </div>

            <!-- Error Alerts Block -->
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-xs font-semibold">
                    <ul class="list-disc pl-4 space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Main Input Form Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <form action="{{ route('admin.blogs.update', $blog->id) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    <!-- Input 1: Blog Title Name -->
                    <div>
                        <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5 tracking-wider">Blog Name / Article Title *</label>
                        <input type="text" name="title" required class="w-full border border-gray-300 rounded-lg px-4 py-2 text-xs font-semibold focus:outline-none focus:border-brandOrange" placeholder="Enter full blog post heading title" value="{{ old('title', $blog->title) }}">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Input 2: Main Big Image -->
                        <div>
                            <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5 tracking-wider">Change Main Full Size Image (Optional - JPG, PNG - Max 2MB)</label>
                            <input type="file" name="image" class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-xs font-semibold focus:outline-none focus:border-brandOrange bg-gray-50">
                            @if($blog->image_path)
                                <div class="mt-2 flex items-center gap-2">
                                    <span class="text-[10px] text-gray-500 font-semibold">Current Main Image:</span>
                                    <img src="{{ asset('storage/' . $blog->image_path) }}" alt="Current Main" class="w-16 h-10 object-cover rounded border border-gray-300">
                                </div>
                            @endif
                        </div>

                        <!-- Input 3: Small Preview Thumbnail Image -->
                        <div>
                            <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5 tracking-wider">Change Thumbnail Image (Optional - JPG, PNG - Max 1MB)</label>
                            <input type="file" name="thumbnail" class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-xs font-semibold focus:outline-none focus:border-brandOrange bg-gray-50">
                            @if($blog->thumbnail_path)
                                <div class="mt-2 flex items-center gap-2">
                                    <span class="text-[10px] text-gray-500 font-semibold">Current Thumbnail:</span>
                                    <img src="{{ asset('storage/' . $blog->thumbnail_path) }}" alt="Current Thumb" class="w-10 h-10 object-cover rounded border border-gray-300">
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Input 4: Status Select Dropdown -->
                    <div>
                        <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5 tracking-wider">Publish Status *</label>
                        <select name="status" required class="w-full md:w-64 border border-gray-300 rounded-lg px-4 py-2 text-xs font-semibold focus:outline-none focus:border-brandOrange bg-white">
                            <option value="active" {{ old('status', $blog->status) == 'active' ? 'selected' : '' }}>Active / Publish Now</option>
                            <option value="draft" {{ old('status', $blog->status) == 'draft' ? 'selected' : '' }}>Draft / Keep Hidden</option>
                        </select>
                    </div>

                    <!-- Input 5: Rich Text / Full Description Content Body -->
                    <div>
                        <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5 tracking-wider">Blog Article Description Body Content *</label>
                        <textarea name="content" required rows="10" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-xs font-semibold focus:outline-none focus:border-brandOrange" placeholder="Write comprehensive religious descriptive details here...">{{ old('content', $blog->content) }}</textarea>
                    </div>

                    <!-- Submit Buttons Action Desk -->
                    <div class="pt-4 border-t border-gray-200 flex gap-2 justify-end">
                        <a href="{{ route('admin.blogs.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-black text-[11px] px-5 py-2.5 rounded-lg uppercase tracking-wide transition border border-gray-300">
                            Cancel
                        </a>
                        <button type="submit" class="bg-brandOrange hover:bg-orange-700 text-white font-black text-[11px] px-6 py-2.5 rounded-lg shadow-sm uppercase tracking-wide transition">
                            Update Blog Article
                        </button>
                    </div>
                </form>
            </div>

        </main> <!-- END WORKSPACE CONTAINER -->
    </div> <!-- END MAIN WORKSPACE VIEWPORT DESK -->
</div> <!-- END MIN-H-SCREEN CONTAINER -->
@endsection
