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
                <span class="bg-orange-100 text-brandOrange text-[9px] font-black px-2.5 py-0.5 rounded border border-orange-200 tracking-widest uppercase shadow-sm">Edit Project</span>
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
                    🌱 Edit Service Project Form
                </h3>
                <a href="{{ route('admin.support.index') }}" class="bg-gray-800 hover:bg-gray-900 text-white font-black text-[10px] px-4 py-2 rounded-lg shadow-sm uppercase tracking-wide transition">
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
                <form action="{{ route('admin.support.update', $support->id) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Input 1: Project Name -->
                        <div>
                            <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5 tracking-wider">Project Name * (e.g. ANNAPURNA, GOSAMRAKSHANA)</label>
                            <input type="text" name="name" required class="w-full border border-gray-300 rounded-lg px-4 py-2 text-xs font-semibold focus:outline-none focus:border-brandOrange" placeholder="Enter service project name" value="{{ old('name', $support->name) }}">
                        </div>

                        <!-- Input 2: Sort Order -->
                        <div>
                            <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5 tracking-wider">Display Sort Order * (1, 2, 3...)</label>
                            <input type="number" name="sort_order" required min="1" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-xs font-semibold focus:outline-none focus:border-brandOrange" placeholder="Example: 1 for top priority" value="{{ old('sort_order', $support->sort_order) }}">
                        </div>

                        <!-- Input 3: Status Dropdown -->
                        <div>
                            <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5 tracking-wider">Website Visibility Status *</label>
                            <select name="status" required class="w-full border border-gray-300 rounded-lg px-4 py-2 text-xs font-semibold focus:outline-none focus:border-brandOrange bg-white">
                                <option value="show" {{ old('status', $support->status) == 'show' ? 'selected' : '' }}>Show on Home Page</option>
                                <option value="hide" {{ old('status', $support->status) == 'hide' ? 'selected' : '' }}>Hide / Keep Draft</option>
                            </select>
                        </div>

                        <!-- Input 4: Project Image File -->
                        <div>
                            <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5 tracking-wider">Change Project Photo / Banner (Optional - JPG, PNG - Max 2MB)</label>
                            <input type="file" name="image" class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-xs font-semibold focus:outline-none focus:border-brandOrange bg-gray-50">
                            @if($support->image_path)
                                <div class="mt-2 flex items-center gap-2">
                                    <span class="text-[10px] text-gray-500 font-semibold">Current Image:</span>
                                    <img src="{{ asset('storage/' . $support->image_path) }}" alt="Current Image" class="w-16 h-10 object-cover rounded border border-gray-300">
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Input 5: Short Description Info -->
                    <div>
                        <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5 tracking-wider">Short Project Description Info *</label>
                        <textarea name="short_info" required rows="4" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-xs font-semibold focus:outline-none focus:border-brandOrange" placeholder="Write a short summary about the purpose and goals of this project for the home page...">{{ old('short_info', $support->short_info) }}</textarea>
                    </div>

                    <!-- Submit Buttons Action Desk -->
                    <div class="pt-4 border-t border-gray-200 flex gap-2 justify-end">
                        <a href="{{ route('admin.support.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-black text-[11px] px-5 py-2.5 rounded-lg uppercase tracking-wide transition border border-gray-300">
                            Cancel
                        </a>
                        <button type="submit" class="bg-brandOrange hover:bg-orange-700 text-white font-black text-[11px] px-6 py-2.5 rounded-lg shadow-sm uppercase tracking-wide transition">
                            Update Project
                        </button>
                    </div>
                </form>
            </div>

        </main> <!-- END WORKSPACE CONTAINER -->
    </div> <!-- END MAIN WORKSPACE VIEWPORT DESK -->
</div> <!-- END MIN-H-SCREEN CONTAINER -->
@endsection
