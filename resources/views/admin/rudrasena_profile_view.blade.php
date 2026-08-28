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
                <span class="bg-orange-100 text-brandOrange text-[9px] font-black px-2.5 py-0.5 rounded border border-orange-200 tracking-widest uppercase shadow-sm">Rudrasena Member Dossier</span>
            </div>
            <div class="text-right text-[10px] font-mono font-black text-gray-500">
                System Sync: {{ \Carbon\Carbon::now()->format('d-M-Y H:i') }} IST
            </div>
        </header>

        <!-- Dynamic Content Workspace Container -->
        <main class="flex-1 overflow-y-auto p-6 space-y-6">
            
            <!-- Header Title and Action Buttons -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h3 class="text-sm font-black text-brandGray uppercase tracking-wider flex items-center gap-2">
                        <span>🔱</span> Rudrasena Dossier: {{ $member->full_name }}
                    </h3>
                    <p class="text-[11px] text-gray-500 font-semibold mt-0.5">
                        Membership ID: <span class="font-mono text-brandOrange font-bold">{{ implode(' ', str_split($member->membership_id, 4)) }}</span>
                        @if($member->rudrasena_id)
                            | Rudrasena ID: <span class="font-mono text-emerald-700 font-black">{{ $member->rudrasena_id }}</span>
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.rudrasena.index') }}" class="bg-gray-800 hover:bg-gray-900 text-white font-black text-[10px] px-4 py-2 rounded-lg shadow-sm uppercase tracking-wide transition flex items-center gap-1.5">
                        <span>←</span> Back To List
                    </a>
                    <a href="{{ route('admin.rudrasena.view_card', $member->id) }}" target="_blank" class="bg-emerald-600 hover:bg-emerald-700 text-white font-black text-[10px] px-4 py-2 rounded-lg shadow-sm uppercase tracking-wide transition flex items-center gap-1.5">
                        <span>💳</span> View ID Card
                    </a>
                    <a href="{{ route('admin.rudrasena.edit', $member->id) }}" class="bg-brandOrange hover:bg-orange-700 text-white font-black text-[10px] px-4 py-2 rounded-lg shadow-sm uppercase tracking-wide transition flex items-center gap-1.5">
                        <span>✏️</span> Edit Approval
                    </a>
                </div>
            </div>

            <!-- Profile Summary Hero Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
                    <!-- Photo Frame -->
                    <div class="relative shrink-0">
                        @if($member->member_photo_path)
                            <img src="{{ asset('storage/' . $member->member_photo_path) }}" class="w-32 h-36 object-cover rounded-xl border-4 border-brandOrange shadow-md" alt="Member Photo">
                        @else
                            <div class="w-32 h-36 bg-gray-100 rounded-xl border-2 border-dashed border-gray-300 flex flex-col items-center justify-center text-gray-400">
                                <div class="w-10 h-10 rounded-full overflow-hidden bg-white border border-gray-300 flex items-center justify-center p-0.5 mb-1">
                                    <img src="{{ asset('images/ABVHPS_LOGO.jpg') }}" class="w-full h-full object-cover rounded-full" alt="ABVHPS Logo">
                                </div>
                                <span class="text-[10px] font-bold uppercase">Rudrasena</span>
                            </div>
                        @endif
                        <span class="absolute -bottom-2.5 left-1/2 -translate-x-1/2 {{ $member->status === 'verified' ? 'bg-green-600' : ($member->status === 'rejected' ? 'bg-red-600' : 'bg-amber-600') }} text-white text-[8px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider shadow">
                            {{ strtoupper($member->status) }}
                        </span>
                    </div>

                    <!-- Core Overview -->
                    <div class="flex-1 text-center md:text-left space-y-2">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-2">
                            <div>
                                <h2 class="text-xl font-black text-gray-900 uppercase tracking-wide">{{ $member->full_name }}</h2>
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Cadder: <span class="text-brandOrange font-black">{{ $member->assigned_cadder ?: 'Not Assigned' }}</span> | Locality: <span class="text-gray-800">{{ $member->assigned_locality ?: 'HQ' }}</span></p>
                            </div>
                            <div class="flex flex-wrap gap-1.5 justify-center md:justify-end">
                                <span class="bg-orange-50 text-brandOrange border border-orange-200 text-[10px] font-black px-3 py-1 rounded-md tracking-wider uppercase">
                                    {{ implode(' ', str_split($member->membership_id, 4)) }}
                                </span>
                                @if($member->volunteer_type)
                                    <span class="bg-blue-50 text-blue-700 border border-blue-200 text-[10px] font-black px-2.5 py-1 rounded-md uppercase">
                                        {{ $member->volunteer_type }}
                                    </span>
                                @endif
                                @if($member->blood_group)
                                    <span class="bg-red-50 text-red-600 border border-red-200 text-[10px] font-black px-2.5 py-1 rounded-md uppercase">
                                        🩸 {{ $member->blood_group }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 pt-3 border-t border-gray-100 text-xs">
                            <div class="bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                                <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider">Mobile Number</span>
                                <span class="font-mono font-black text-gray-800">{{ $member->mobile }}</span>
                            </div>
                            <div class="bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                                <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider">Email Address</span>
                                <span class="font-mono font-semibold text-gray-800 truncate block">{{ $member->email }}</span>
                            </div>
                            <div class="bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                                <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider">Date of Birth & Age</span>
                                @php
                                    $calculatedAge = null;
                                    $isAgeEligible = false;
                                    if (!empty($member->dob)) {
                                        try {
                                            $calculatedAge = \App\Services\RudrasenaEligibilityService::calculateAge($member->dob);
                                            $isAgeEligible = \App\Services\RudrasenaEligibilityService::isAgeEligible($member->dob);
                                        } catch (\Throwable $e) {}
                                    }
                                @endphp
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="font-bold text-gray-800">{{ $member->dob ?: 'N/A' }}</span>
                                    @if($calculatedAge !== null)
                                        <span class="font-bold text-gray-700">({{ $calculatedAge }} Yrs)</span>
                                        @if($isAgeEligible)
                                            <span class="bg-emerald-100 text-emerald-800 text-[8px] font-black px-1.5 py-0.5 rounded uppercase">Eligible (24–44)</span>
                                        @else
                                            <span class="bg-rose-100 text-rose-800 text-[8px] font-black px-1.5 py-0.5 rounded uppercase">Ineligible (Req 24–44)</span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            <div class="bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                                <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider">Gotram</span>
                                <span class="font-bold text-gray-800">{{ $member->gotram ?: 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail Information Sections -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Section 1: Banking Details -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                    <div class="flex items-center gap-2 border-b border-gray-100 pb-3">
                        <span class="text-lg">🏦</span>
                        <h4 class="text-xs font-black text-brandGray uppercase tracking-wider">Bank Account Information</h4>
                    </div>

                    <div class="space-y-2.5 text-xs">
                        <div class="flex justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500 font-semibold uppercase text-[10px]">Account Holder:</span>
                            <span class="font-bold text-gray-900">{{ $member->bank_holder_name }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500 font-semibold uppercase text-[10px]">Account Number:</span>
                            <span class="font-mono font-bold text-gray-900">{{ $member->bank_account_number }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500 font-semibold uppercase text-[10px]">IFSC Code:</span>
                            <span class="font-mono font-bold text-gray-900">{{ $member->bank_ifsc_code }}</span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-gray-500 font-semibold uppercase text-[10px]">Bank Name & Branch:</span>
                            <span class="font-bold text-gray-900">{{ $member->bank_name_branch }}</span>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Nominee Emergency Details -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                    <div class="flex items-center gap-2 border-b border-gray-100 pb-3">
                        <span class="text-lg">👨‍👩‍👧</span>
                        <h4 class="text-xs font-black text-brandGray uppercase tracking-wider">Insurance Nominee Details</h4>
                    </div>

                    <div class="space-y-2.5 text-xs">
                        <div class="flex justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500 font-semibold uppercase text-[10px]">Nominee Name:</span>
                            <span class="font-bold text-gray-900">{{ $member->nominee_name }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500 font-semibold uppercase text-[10px]">Relationship:</span>
                            <span class="font-bold text-gray-900">{{ $member->nominee_relation }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500 font-semibold uppercase text-[10px]">Nominee Age:</span>
                            <span class="font-bold text-gray-900">{{ $member->nominee_age }} Years</span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-gray-500 font-semibold uppercase text-[10px]">Nominee Contact:</span>
                            <span class="font-mono font-bold text-gray-900">{{ $member->nominee_contact }}</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Section 3: Family Members List -->
            @if(isset($familyDetails) && count($familyDetails) > 0)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                    <div class="flex items-center gap-2 border-b border-gray-100 pb-3">
                        <span class="text-lg">🏡</span>
                        <h4 class="text-xs font-black text-brandGray uppercase tracking-wider">Family Members Tree</h4>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-xs">
                            <thead class="bg-gray-50 text-[10px] font-black uppercase text-gray-600">
                                <tr>
                                    <th class="px-4 py-2 text-left">Member Name</th>
                                    <th class="px-4 py-2 text-left">Relationship</th>
                                    <th class="px-4 py-2 text-center">Age</th>
                                    <th class="px-4 py-2 text-center">Gender</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($familyDetails as $fam)
                                    <tr>
                                        <td class="px-4 py-2.5 font-bold text-gray-900">{{ $fam->member_name }}</td>
                                        <td class="px-4 py-2.5 text-gray-700">{{ $fam->member_relation }}</td>
                                        <td class="px-4 py-2.5 text-center font-mono">{{ $fam->member_age }}</td>
                                        <td class="px-4 py-2.5 text-center">{{ $fam->member_gender }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Section 4: Attached Mandatory Documents -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                <div class="flex items-center gap-2 border-b border-gray-100 pb-3">
                    <span class="text-lg">📁</span>
                    <h4 class="text-xs font-black text-brandGray uppercase tracking-wider">Uploaded Documents & Proofs</h4>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-100 flex flex-col justify-between">
                        <div>
                            <span class="font-black text-gray-800 block text-xs">Health Declaration</span>
                            <span class="text-[10px] text-gray-400 font-semibold">Doctor Fitness Form</span>
                        </div>
                        <a href="{{ asset('storage/' . $member->document_health_declaration) }}" target="_blank" class="mt-2 text-center bg-brandOrange hover:bg-orange-700 text-white font-black text-[9px] py-1 px-2 rounded uppercase transition">
                            View Document &rarr;
                        </a>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-100 flex flex-col justify-between">
                        <div>
                            <span class="font-black text-gray-800 block text-xs">Family Sheet</span>
                            <span class="text-[10px] text-gray-400 font-semibold">Consent + 2 Witnesses</span>
                        </div>
                        <a href="{{ asset('storage/' . $member->document_family_declaration) }}" target="_blank" class="mt-2 text-center bg-brandOrange hover:bg-orange-700 text-white font-black text-[9px] py-1 px-2 rounded uppercase transition">
                            View Document &rarr;
                        </a>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-100 flex flex-col justify-between">
                        <div>
                            <span class="font-black text-gray-800 block text-xs">ID Proof Copy</span>
                            <span class="text-[10px] text-gray-400 font-semibold">Aadhaar / Voter ID</span>
                        </div>
                        <a href="{{ asset('storage/' . $member->document_id_proof) }}" target="_blank" class="mt-2 text-center bg-brandOrange hover:bg-orange-700 text-white font-black text-[9px] py-1 px-2 rounded uppercase transition">
                            View Document &rarr;
                        </a>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-100 flex flex-col justify-between">
                        <div>
                            <span class="font-black text-gray-800 block text-xs">Bank Proof</span>
                            <span class="text-[10px] text-gray-400 font-semibold">Passbook / Cheque</span>
                        </div>
                        <a href="{{ asset('storage/' . $member->document_bank_proof) }}" target="_blank" class="mt-2 text-center bg-brandOrange hover:bg-orange-700 text-white font-black text-[9px] py-1 px-2 rounded uppercase transition">
                            View Document &rarr;
                        </a>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>
@endsection
