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
                <span class="bg-orange-100 text-brandOrange text-[9px] font-black px-2.5 py-0.5 rounded border border-orange-200 tracking-widest uppercase shadow-sm">Edit Leader</span>
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
                    👥 Edit Leader Profile Form
                </h3>
                <a href="{{ route('admin.our_team.index') }}" class="bg-gray-800 hover:bg-gray-900 text-white font-black text-[10px] px-4 py-2 rounded-lg shadow-sm uppercase tracking-wide transition">
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
                <form action="{{ route('admin.our_team.update', $member->id) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Input 1: 12-Digit Membership ID -->
                        <div>
                            <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5 tracking-wider">Membership ID (12 Digits - Optional)</label>
                            <input type="text" name="membership_id" maxlength="12" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-xs font-semibold focus:outline-none focus:border-brandOrange" placeholder="Example: 202684930215" value="{{ old('membership_id', $member->membership_id) }}">
                        </div>

                        <!-- Input 2: Full Name -->
                        <div>
                            <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5 tracking-wider">Leader Full Name *</label>
                            <input type="text" name="name" required class="w-full border border-gray-300 rounded-lg px-4 py-2 text-xs font-semibold focus:outline-none focus:border-brandOrange" placeholder="Enter full official name" value="{{ old('name', $member->name) }}">
                        </div>

                        <!-- Input 3: Cadre Level Dropdown -->
                        <div>
                            <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5 tracking-wider">Committee Level *</label>
                            <select name="cadre_level" required class="w-full border border-gray-300 rounded-lg px-4 py-2 text-xs font-semibold focus:outline-none focus:border-brandOrange bg-white">
                                <option value="grama_panchayat" {{ old('cadre_level', $member->cadre_level) == 'grama_panchayat' ? 'selected' : '' }}>Grama Panchayat Level</option>
                                <option value="mandal_level" {{ old('cadre_level', $member->cadre_level) == 'mandal_level' ? 'selected' : '' }}>Mandal Level Committee</option>
                                <option value="assembly_segment" {{ old('cadre_level', $member->cadre_level) == 'assembly_segment' ? 'selected' : '' }}>Assembly Segment Team</option>
                                <option value="district_level" {{ old('cadre_level', $member->cadre_level) == 'district_level' ? 'selected' : '' }}>District Level Committee</option>
                                <option value="state_level" {{ old('cadre_level', $member->cadre_level) == 'state_level' ? 'selected' : '' }}>State Level Committee</option>
                                <option value="national_level" {{ old('cadre_level', $member->cadre_level) == 'national_level' ? 'selected' : '' }}>National Level Committee</option>
                                <option value="international_level" {{ old('cadre_level', $member->cadre_level) == 'international_level' ? 'selected' : '' }}>International Level Wing</option>
                            </select>
                        </div>

                        <!-- Input 4: Designation / Role -->
                        <div>
                            <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5 tracking-wider">Designation / Role Name *</label>
                            <input type="text" name="designation" required class="w-full border border-gray-300 rounded-lg px-4 py-2 text-xs font-semibold focus:outline-none focus:border-brandOrange" placeholder="Example: President, Secretary, Member" value="{{ old('designation', $member->designation) }}">
                        </div>

                        <!-- Input 5: Locality / Region -->
                        <div>
                            <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5 tracking-wider">Jurisdiction Locality / Region *</label>
                            <input type="text" name="locality" required class="w-full border border-gray-300 rounded-lg px-4 py-2 text-xs font-semibold focus:outline-none focus:border-brandOrange" placeholder="Example: Palakollu, Kadapa, Hyderabad" value="{{ old('locality', $member->locality) }}">
                        </div>

                        <!-- Input 6: Profile Photo Image -->
                        <div>
                            <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5 tracking-wider">Change Profile Photo (Optional - JPG, PNG - Max 2MB)</label>
                            <input type="file" name="image" class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-xs font-semibold focus:outline-none focus:border-brandOrange bg-gray-50">
                            @if($member->image_path)
                                <div class="mt-2 flex items-center gap-2">
                                    <span class="text-[10px] text-gray-500 font-semibold">Current Photo:</span>
                                    <img src="{{ asset('storage/' . $member->image_path) }}" alt="Current Photo" class="w-10 h-10 object-cover rounded border border-gray-300">
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Submit Buttons Action Desk -->
                    <div class="pt-4 border-t border-gray-200 flex gap-2 justify-end">
                        <a href="{{ route('admin.our_team.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-black text-[11px] px-5 py-2.5 rounded-lg uppercase tracking-wide transition border border-gray-300">
                            Cancel
                        </a>
                        <button type="submit" class="bg-brandOrange hover:bg-orange-700 text-white font-black text-[11px] px-6 py-2.5 rounded-lg shadow-sm uppercase tracking-wide transition">
                            Update Leader Profile
                        </button>
                    </div>
                </form>
            </div>

        </main> <!-- END WORKSPACE CONTAINER -->
    </div> <!-- END MAIN WORKSPACE VIEWPORT DESK -->
</div> <!-- END MIN-H-SCREEN CONTAINER -->
@endsection
