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
                <span class="bg-orange-100 text-brandOrange text-[9px] font-black px-2.5 py-0.5 rounded border border-orange-200 tracking-widest uppercase shadow-sm">Edit Member Record</span>
            </div>
            <div class="text-right text-[10px] font-mono font-black text-gray-500">
                System Sync: {{ \Carbon\Carbon::now()->format('d-M-Y H:i') }} IST
            </div>
        </header>

        <!-- Dynamic Content Workspace Container -->
        <main class="flex-1 overflow-y-auto p-6 space-y-6">
            
            <!-- Header Title and Back Link -->
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-xs font-black text-brandGray uppercase tracking-wider flex items-center gap-1.5">
                        ✏️ Edit Member Profile: {{ $member->full_name }}
                    </h3>
                    <p class="text-[11px] text-gray-500 font-semibold mt-0.5">Membership ID: <span class="font-mono text-brandOrange font-bold">{{ $member->membership_id }}</span></p>
                </div>
                <a href="{{ route('admin.membership.ledger') }}" class="bg-gray-800 hover:bg-gray-900 text-white font-black text-[10px] px-4 py-2 rounded-lg shadow-sm uppercase tracking-wide transition flex items-center gap-1">
                    ← Back To List
                </a>
            </div>

            <!-- Error Alerts Block -->
            @if(isset($errors) && $errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-xs font-semibold shadow-sm">
                    <div class="font-black mb-1">Please correct the following errors:</div>
                    <ul class="list-disc pl-4 space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Main Input Form Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <form action="{{ route('admin.membership.update', $member->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <!-- Section A: Primary Identity Information -->
                    <div>
                        <h4 class="text-xs font-black text-brandOrange uppercase tracking-wider border-b border-orange-100 pb-2 mb-4 flex items-center gap-1.5">
                            <span>🪪</span> Section A: Personal & Identity Metrics
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Full Name -->
                            <div>
                                <label class="block text-[11px] font-black uppercase text-gray-600 mb-1.5 tracking-wider">Full Name *</label>
                                <input type="text" name="full_name" required class="w-full border border-gray-300 rounded-lg px-4 py-2 text-xs font-semibold focus:outline-none focus:border-brandOrange" placeholder="Enter Full Name" value="{{ old('full_name', $member->full_name) }}">
                            </div>

                            <!-- Father / Husband Name -->
                            <div>
                                <label class="block text-[11px] font-black uppercase text-gray-600 mb-1.5 tracking-wider">Father / Husband Name *</label>
                                <input type="text" name="father_or_husband_name" required class="w-full border border-gray-300 rounded-lg px-4 py-2 text-xs font-semibold focus:outline-none focus:border-brandOrange" placeholder="Enter Father or Husband Name" value="{{ old('father_or_husband_name', $member->father_or_husband_name) }}">
                            </div>

                            <!-- Mobile Phone -->
                            <div>
                                <label class="block text-[11px] font-black uppercase text-gray-600 mb-1.5 tracking-wider">Mobile Phone (10 Digits) *</label>
                                <input type="text" name="phone" required maxlength="10" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-xs font-semibold font-mono focus:outline-none focus:border-brandOrange" placeholder="10 Digit Phone Number" value="{{ old('phone', $member->phone) }}">
                            </div>

                            <!-- Verified Identity Document (Read-Only) -->
                            <div>
                                <label class="block text-[11px] font-black uppercase text-gray-600 mb-1.5 tracking-wider">
                                    Identity Document ({{ $member->getIdentityMethodLabel() }})
                                </label>
                                <div class="w-full bg-gray-100 border border-gray-300 rounded-lg px-4 py-2 text-xs font-semibold text-gray-700 flex items-center justify-between">
                                    <span class="font-mono">{{ $member->getIdentityDocumentMaskedLabel() }}</span>
                                    <span class="text-[9px] font-black {{ $member->hasVerifiedIdentity() ? 'text-emerald-700' : 'text-amber-700' }}">
                                        {{ $member->hasVerifiedIdentity() ? 'Verified ✓' : 'Pending' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Email Address -->
                            <div>
                                <label class="block text-[11px] font-black uppercase text-gray-600 mb-1.5 tracking-wider">Email Address (Optional)</label>
                                <input type="email" name="email" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-xs font-semibold focus:outline-none focus:border-brandOrange" placeholder="example@domain.com" value="{{ old('email', $member->email) }}">
                            </div>

                            <!-- Gender -->
                            <div>
                                <label class="block text-[11px] font-black uppercase text-gray-600 mb-1.5 tracking-wider">Gender *</label>
                                <select name="gender" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-xs font-semibold focus:outline-none focus:border-brandOrange bg-white">
                                    <option value="">-- Select Gender --</option>
                                    @foreach(['Male', 'Female', 'Other'] as $gen)
                                        <option value="{{ $gen }}" {{ old('gender', $member->gender) == $gen ? 'selected' : '' }}>{{ $gen }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Date of Birth -->
                            <div>
                                <label class="block text-[11px] font-black uppercase text-gray-600 mb-1.5 tracking-wider">Date of Birth</label>
                                <input type="date" name="dob" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-xs font-semibold focus:outline-none focus:border-brandOrange" value="{{ old('dob', $member->dob) }}">
                            </div>

                            <!-- Blood Group -->
                            <div>
                                <label class="block text-[11px] font-black uppercase text-gray-600 mb-1.5 tracking-wider">Blood Group</label>
                                <select name="blood_group" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-xs font-semibold focus:outline-none focus:border-brandOrange bg-white">
                                    <option value="">-- Select Blood Group --</option>
                                    @foreach(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'] as $bg)
                                        <option value="{{ $bg }}" {{ old('blood_group', $member->blood_group) == $bg ? 'selected' : '' }}>{{ $bg }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Gotram -->
                            <div>
                                <label class="block text-[11px] font-black uppercase text-gray-600 mb-1.5 tracking-wider">Gotram *</label>
                                <input type="text" name="gotram" required class="w-full border border-gray-300 rounded-lg px-4 py-2 text-xs font-semibold focus:outline-none focus:border-brandOrange" placeholder="Enter Gotram" value="{{ old('gotram', $member->gotram) }}">
                            </div>

                            <!-- Occupation -->
                            <div>
                                <label class="block text-[11px] font-black uppercase text-gray-600 mb-1.5 tracking-wider">Occupation *</label>
                                <input type="text" name="occupation" required class="w-full border border-gray-300 rounded-lg px-4 py-2 text-xs font-semibold focus:outline-none focus:border-brandOrange" placeholder="Enter Occupation" value="{{ old('occupation', $member->occupation) }}">
                            </div>

                            <!-- Profile Photo -->
                            <div>
                                <label class="block text-[11px] font-black uppercase text-gray-600 mb-1.5 tracking-wider">Profile Photo (JPG, PNG - Max 2MB)</label>
                                <div class="flex items-center gap-3">
                                    @if($member->photo_path)
                                        <img src="{{ asset('storage/' . $member->photo_path) }}" class="w-10 h-12 object-cover rounded border border-gray-300 shadow-sm shrink-0" alt="Current Photo">
                                    @endif
                                    <input type="file" name="photo" class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-xs font-semibold focus:outline-none focus:border-brandOrange bg-gray-50">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section B: Geographical & Address Metrics -->
                    <div>
                        <h4 class="text-xs font-black text-brandOrange uppercase tracking-wider border-b border-orange-100 pb-2 mb-4 flex items-center gap-1.5">
                            <span>🏡</span> Section B: Address & Administrative Location
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Grama Panchayat -->
                            <div>
                                <label class="block text-[11px] font-black uppercase text-gray-600 mb-1.5 tracking-wider">Grama Panchayat *</label>
                                <input type="text" name="grama_panchayat" required class="w-full border border-gray-300 rounded-lg px-4 py-2 text-xs font-semibold focus:outline-none focus:border-brandOrange" placeholder="Enter Grama Panchayat" value="{{ old('grama_panchayat', $member->grama_panchayat) }}">
                            </div>

                            <!-- Mandal -->
                            <div>
                                <label class="block text-[11px] font-black uppercase text-gray-600 mb-1.5 tracking-wider">Mandal / Taluk *</label>
                                <input type="text" name="mandal" required class="w-full border border-gray-300 rounded-lg px-4 py-2 text-xs font-semibold focus:outline-none focus:border-brandOrange" placeholder="Enter Mandal" value="{{ old('mandal', $member->mandal) }}">
                            </div>

                            <!-- Assembly Segment -->
                            <div>
                                <label class="block text-[11px] font-black uppercase text-gray-600 mb-1.5 tracking-wider">Assembly Segment</label>
                                <input type="text" name="assembly_segment" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-xs font-semibold focus:outline-none focus:border-brandOrange" placeholder="Enter Assembly Constituency" value="{{ old('assembly_segment', $member->assembly_segment) }}">
                            </div>

                            <!-- District -->
                            <div>
                                <label class="block text-[11px] font-black uppercase text-gray-600 mb-1.5 tracking-wider">District *</label>
                                <input type="text" name="district" required class="w-full border border-gray-300 rounded-lg px-4 py-2 text-xs font-semibold focus:outline-none focus:border-brandOrange" placeholder="Enter District" value="{{ old('district', $member->district) }}">
                            </div>

                            <!-- State -->
                            <div>
                                <label class="block text-[11px] font-black uppercase text-gray-600 mb-1.5 tracking-wider">State *</label>
                                <input type="text" name="state" required class="w-full border border-gray-300 rounded-lg px-4 py-2 text-xs font-semibold focus:outline-none focus:border-brandOrange" placeholder="Enter State" value="{{ old('state', $member->state) }}">
                            </div>

                            <!-- Country -->
                            <div>
                                <label class="block text-[11px] font-black uppercase text-gray-600 mb-1.5 tracking-wider">Country</label>
                                <input type="text" name="country" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-xs font-semibold focus:outline-none focus:border-brandOrange" placeholder="India" value="{{ old('country', $member->country ?? 'India') }}">
                            </div>

                            <!-- Pincode -->
                            <div>
                                <label class="block text-[11px] font-black uppercase text-gray-600 mb-1.5 tracking-wider">Pincode (6 Digits) *</label>
                                <input type="text" name="pincode" required maxlength="6" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-xs font-semibold font-mono focus:outline-none focus:border-brandOrange" placeholder="6 Digit Pincode" value="{{ old('pincode', $member->pincode) }}">
                            </div>

                            <!-- Permanent Address -->
                            <div class="md:col-span-2">
                                <label class="block text-[11px] font-black uppercase text-gray-600 mb-1.5 tracking-wider">Permanent Address Details</label>
                                <textarea name="permanent_address" rows="2" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-xs font-semibold focus:outline-none focus:border-brandOrange" placeholder="Door No, Street, Landmark...">{{ old('permanent_address', $member->permanent_address) }}</textarea>
                            </div>

                            <!-- Present Address -->
                            <div class="md:col-span-3">
                                <label class="block text-[11px] font-black uppercase text-gray-600 mb-1.5 tracking-wider">Present Address Details (If different)</label>
                                <textarea name="present_address" rows="2" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-xs font-semibold focus:outline-none focus:border-brandOrange" placeholder="Present residence address details...">{{ old('present_address', $member->present_address) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons Action Desk -->
                    <div class="pt-4 border-t border-gray-200 flex gap-3 justify-end">
                        <a href="{{ route('admin.membership.ledger') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-black text-[11px] px-5 py-2.5 rounded-lg uppercase tracking-wide transition border border-gray-300">
                            Cancel
                        </a>
                        <button type="submit" class="bg-brandOrange hover:bg-orange-700 text-white font-black text-[11px] px-6 py-2.5 rounded-lg shadow-sm uppercase tracking-wide transition flex items-center gap-1.5">
                            <span>💾</span> Save & Update Member
                        </button>
                    </div>
                </form>
            </div>

        </main>
    </div>
</div>
@endsection
