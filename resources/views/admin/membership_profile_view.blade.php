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
                <span class="bg-orange-100 text-brandOrange text-[9px] font-black px-2.5 py-0.5 rounded border border-orange-200 tracking-widest uppercase shadow-sm">Member Profile Detail</span>
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
                        <span>👤</span> Member Dossier: {{ $member->full_name }}
                    </h3>
                    <p class="text-[11px] text-gray-500 font-semibold mt-0.5">Numeric ID: <span class="font-mono text-brandOrange font-bold">{{ $formattedId }}</span></p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.membership.ledger') }}" class="bg-gray-800 hover:bg-gray-900 text-white font-black text-[10px] px-4 py-2 rounded-lg shadow-sm uppercase tracking-wide transition flex items-center gap-1.5">
                        <span>←</span> Back To List
                    </a>
                    <a href="{{ route('admin.membership.idcard', $member->id) }}" target="_blank" class="bg-yellow-500 hover:bg-yellow-600 text-white font-black text-[10px] px-4 py-2 rounded-lg shadow-sm uppercase tracking-wide transition flex items-center gap-1.5">
                        <span>🖨️</span> View / Print ID Card
                    </a>
                    <a href="{{ route('admin.membership.edit', $member->id) }}" class="bg-brandOrange hover:bg-orange-700 text-white font-black text-[10px] px-4 py-2 rounded-lg shadow-sm uppercase tracking-wide transition flex items-center gap-1.5">
                        <span>✏️</span> Edit Profile
                    </a>
                </div>
            </div>

            <!-- Profile Summary Hero Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
                    <!-- Member Photo Frame -->
                    <div class="relative shrink-0">
                        @if($member->photo_path)
                            <img src="{{ asset('storage/' . $member->photo_path) }}" class="w-32 h-36 object-cover rounded-xl border-4 border-brandOrange shadow-md" alt="Member Photo">
                        @else
                            <div class="w-32 h-36 bg-gray-100 rounded-xl border-2 border-dashed border-gray-300 flex flex-col items-center justify-center text-gray-400">
                                <span class="text-3xl mb-1">👤</span>
                                <span class="text-[10px] font-bold uppercase">No Photo</span>
                            </div>
                        @endif
                        <span class="absolute -bottom-2.5 left-1/2 -translate-x-1/2 bg-green-600 text-white text-[8px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider shadow">
                            APPROVED
                        </span>
                    </div>

                    <!-- Member Core Overview -->
                    <div class="flex-1 text-center md:text-left space-y-2">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-2">
                            <div>
                                <h2 class="text-xl font-black text-gray-900 uppercase tracking-wide">{{ $member->full_name }}</h2>
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Father / Husband: <span class="text-gray-800">{{ $member->father_or_husband_name ?? 'N/A' }}</span></p>
                            </div>
                            <div class="flex flex-wrap gap-1.5 justify-center md:justify-end">
                                <span class="bg-orange-50 text-brandOrange border border-orange-200 text-[10px] font-black px-3 py-1 rounded-md tracking-wider uppercase">
                                    {{ $formattedId }}
                                </span>
                                @if($member->blood_group)
                                    <span class="bg-red-50 text-red-600 border border-red-200 text-[10px] font-black px-2.5 py-1 rounded-md uppercase">
                                        🩸 {{ $member->blood_group }}
                                    </span>
                                @endif
                                <span class="bg-green-50 text-green-700 border border-green-200 text-[10px] font-black px-2.5 py-1 rounded-md uppercase">
                                    LIFETIME MEMBER
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-3 border-t border-gray-100 text-xs">
                            <div class="bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                                <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider">Contact Phone</span>
                                <span class="font-mono font-black text-gray-800">{{ $member->phone }}</span>
                            </div>
                            <div class="bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                                <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider">Email Address</span>
                                <span class="font-mono font-semibold text-gray-800 truncate block">{{ $member->email ?? 'Not Provided' }}</span>
                            </div>
                            <div class="bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                                <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider">Identity Document</span>
                                <span class="font-mono font-bold text-gray-800">{{ $member->getIdentityDocumentMaskedLabel() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail Information Dossier Sections -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Section 1: Personal & Cultural Dossier -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                    <div class="flex items-center gap-2 border-b border-gray-100 pb-3">
                        <div class="w-5 h-5 rounded-full overflow-hidden bg-white border border-brandOrange shadow-xs flex items-center justify-center p-0.5 shrink-0">
                            <img src="{{ asset('images/ABVHPS_LOGO.jpg') }}" class="w-full h-full object-cover rounded-full" alt="ABVHPS Logo">
                        </div>
                        <h4 class="text-xs font-black text-brandGray uppercase tracking-wider">Personal & Cultural Details</h4>
                    </div>

                    <div class="space-y-2.5 text-xs">
                        <div class="flex justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500 font-semibold uppercase text-[10px]">Full Name:</span>
                            <span class="font-bold text-gray-900 uppercase">{{ $member->full_name }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500 font-semibold uppercase text-[10px]">Father / Husband Name:</span>
                            <span class="font-bold text-gray-900 uppercase">{{ $member->father_or_husband_name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500 font-semibold uppercase text-[10px]">Gender:</span>
                            <span class="font-bold text-gray-900 uppercase">{{ $member->gender ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500 font-semibold uppercase text-[10px]">Date of Birth:</span>
                            <span class="font-bold text-gray-900 uppercase">{{ $member->dob ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500 font-semibold uppercase text-[10px]">Gotram:</span>
                            <span class="font-bold text-gray-900 uppercase">{{ $member->gotram ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500 font-semibold uppercase text-[10px]">Occupation:</span>
                            <span class="font-bold text-gray-900 uppercase">{{ $member->occupation ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500 font-semibold uppercase text-[10px]">Blood Group:</span>
                            <span class="font-bold text-red-600">{{ $member->blood_group ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500 font-semibold uppercase text-[10px]">Identity Document:</span>
                            <span class="font-mono font-bold text-gray-900">{{ $member->getIdentityDocumentMaskedLabel() }}</span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-gray-500 font-semibold uppercase text-[10px]">Primary Mobile:</span>
                            <span class="font-mono font-bold text-gray-900">{{ $member->phone }}</span>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Administrative Jurisdiction & Address -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                    <div class="flex items-center gap-2 border-b border-gray-100 pb-3">
                        <span class="text-lg">🏡</span>
                        <h4 class="text-xs font-black text-brandGray uppercase tracking-wider">Jurisdiction & Location Hierarchy</h4>
                    </div>

                    <div class="space-y-2.5 text-xs">
                        <div class="flex justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500 font-semibold uppercase text-[10px]">Grama Panchayat:</span>
                            <span class="font-bold text-gray-900 uppercase">{{ $member->grama_panchayat ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500 font-semibold uppercase text-[10px]">Mandal / Taluk:</span>
                            <span class="font-bold text-gray-900 uppercase">{{ $member->mandal ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500 font-semibold uppercase text-[10px]">Assembly Segment:</span>
                            <span class="font-bold text-gray-900 uppercase">{{ $member->assembly_segment ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500 font-semibold uppercase text-[10px]">District:</span>
                            <span class="font-bold text-gray-900 uppercase">{{ $member->district ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500 font-semibold uppercase text-[10px]">State & Country:</span>
                            <span class="font-bold text-gray-900 uppercase">{{ $member->state ?? 'N/A' }}, {{ $member->country ?? 'India' }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500 font-semibold uppercase text-[10px]">Postal Pincode:</span>
                            <span class="font-mono font-bold text-brandOrange">{{ $member->pincode ?? 'N/A' }}</span>
                        </div>
                        <div class="py-1">
                            <span class="block text-gray-500 font-semibold uppercase text-[10px] mb-0.5">Permanent / Present Address:</span>
                            <p class="text-gray-800 font-semibold text-[11px] leading-relaxed">
                                {{ $member->permanent_address ?? ($member->present_address ?? ($member->grama_panchayat . ', ' . $member->mandal . ', ' . $member->district . ', ' . $member->state . ' - ' . $member->pincode)) }}
                            </p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Section 3: Official Identity Verification Desk -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">🪪</span>
                        <h4 class="text-xs font-black text-brandGray uppercase tracking-wider">Official Identity Verification</h4>
                    </div>
                    @if($member->hasVerifiedIdentity())
                        <span class="bg-emerald-100 text-emerald-800 text-[10px] font-black px-2.5 py-0.5 rounded border border-emerald-200 uppercase tracking-wider">
                            {{ $member->getIdentityBadgeLabel() }}
                        </span>
                    @else
                        <span class="bg-amber-100 text-amber-800 text-[10px] font-black px-2.5 py-0.5 rounded border border-amber-200 uppercase tracking-wider">
                            Identity Pending
                        </span>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-xs">
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                        <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider">Verification Status</span>
                        <span class="font-bold text-gray-900 block mt-1">
                            @if($member->hasVerifiedIdentity())
                                <span class="text-emerald-600 font-black">Verified ✓</span>
                            @else
                                <span class="text-amber-600 font-black">Pending ⏳</span>
                            @endif
                        </span>
                    </div>

                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                        <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider">Verification Method</span>
                        <span class="font-bold text-gray-900 block mt-1">{{ $member->getIdentityMethodLabel() }}</span>
                    </div>

                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                        <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider">Verified Legal Name</span>
                        <span class="font-bold text-gray-900 block mt-1 uppercase">{{ $member->identity_verified_name ?? ($member->full_name ?? 'N/A') }}</span>
                    </div>

                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                        <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider">Document (Masked)</span>
                        <span class="font-mono font-bold text-gray-900 block mt-1">{{ $member->getIdentityDocumentMaskedLabel() }}</span>
                    </div>

                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                        <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider">Verification Provider</span>
                        <span class="font-bold text-gray-900 block mt-1">{{ $member->getIdentityVerificationProviderLabel() }}</span>
                    </div>

                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                        <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider">Verified Timestamp</span>
                        <span class="font-mono text-gray-700 block mt-1 text-[11px]">{{ $member->getIdentityVerifiedAtFormatted() ?? 'N/A' }}</span>
                    </div>

                    @if(!empty($member->identity_verification_reference_id) || !empty($member->aadhaar_verification_ref))
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 sm:col-span-2 md:col-span-3">
                        <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider">Verification Reference ID</span>
                        <span class="font-mono text-gray-700 block mt-1 text-[11px]">{{ $member->identity_verification_reference_id ?? $member->aadhaar_verification_ref }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Section 4: Payment & Security Audit Matrix -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                <div class="flex items-center gap-2 border-b border-gray-100 pb-3">
                    <span class="text-lg">🛡️</span>
                    <h4 class="text-xs font-black text-brandGray uppercase tracking-wider">Membership Fee &amp; Payment Audit Matrix</h4>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                        <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider">Payment Status</span>
                        <span class="inline-block mt-1 bg-green-100 text-green-800 text-[10px] font-black px-2.5 py-0.5 rounded border border-green-200 uppercase">
                            ✓ {{ strtoupper($member->payment_status ?? 'SUCCESS') }}
                        </span>
                    </div>

                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                        <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider">Fee Amount</span>
                        <span class="font-mono font-bold text-gray-900 block mt-1">₹{{ number_format((float)($member->payment_amount ?? 1.00), 2) }}</span>
                    </div>

                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                        <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider">Payment Gateway</span>
                        <span class="font-bold text-gray-900 block mt-1 uppercase">{{ ucfirst($member->payment_gateway ?? 'Razorpay') }}</span>
                    </div>

                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                        <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider">Payment Transaction ID</span>
                        <span class="font-mono font-bold text-gray-900 block mt-1 truncate">{{ $member->payment_id ?? 'TXN-SYSTEM' }}</span>
                    </div>

                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                        <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider">Payment Verified At</span>
                        <span class="font-mono text-gray-700 block mt-1 text-[11px]">
                            {{ $member->payment_verified_at ? \Carbon\Carbon::parse($member->payment_verified_at)->timezone('Asia/Kolkata')->format('d-M-Y h:i A') . ' IST' : 'N/A' }}
                        </span>
                    </div>

                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                        <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider">Registered Timestamp</span>
                        <span class="font-mono text-gray-700 block mt-1 text-[11px]">
                            {{ $member->created_at ? \Carbon\Carbon::parse($member->created_at)->timezone('Asia/Kolkata')->format('d-M-Y h:i A') . ' IST' : 'N/A' }}
                        </span>
                    </div>

                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 sm:col-span-2">
                        <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider">Last Profile Update</span>
                        <span class="font-mono text-gray-700 block mt-1 text-[11px]">
                            {{ $member->updated_at ? \Carbon\Carbon::parse($member->updated_at)->timezone('Asia/Kolkata')->format('d-M-Y h:i A') . ' IST' : 'N/A' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Section 5: Event Participation & Seva Benefits History -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">🏆</span>
                        <div>
                            <h4 class="text-xs font-black text-brandGray uppercase tracking-wider">Event Participation &amp; Benefits History</h4>
                            <span class="text-[10px] text-gray-500 font-semibold">Track records of all grassroots volunteer programs this member participated in or received benefits from</span>
                        </div>
                    </div>
                    <span class="bg-orange-50 text-brandOrange border border-orange-200 text-[10px] font-black px-2.5 py-0.5 rounded">
                        {{ $member->eventParticipations->count() }} Records
                    </span>
                </div>

                @if($member->eventParticipations->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="bg-gray-50 text-gray-600 uppercase text-[9px] font-black tracking-wider border-b border-gray-200">
                                    <th class="py-2.5 px-3">#</th>
                                    <th class="py-2.5 px-3">Event Name &amp; Type</th>
                                    <th class="py-2.5 px-3">Date</th>
                                    <th class="py-2.5 px-3">Organizer Volunteer</th>
                                    <th class="py-2.5 px-3">Type</th>
                                    <th class="py-2.5 px-3">Status</th>
                                    <th class="py-2.5 px-3">Benefit Details</th>
                                    <th class="py-2.5 px-3 text-center">Proof Thumbnail</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($member->eventParticipations as $idx => $ep)
                                    <tr class="hover:bg-orange-50/30 transition">
                                        <td class="py-3 px-3 font-bold text-gray-400">{{ $idx + 1 }}</td>
                                        <td class="py-3 px-3">
                                            @if($ep->event)
                                                <a href="{{ route('admin.volunteer_events.show', $ep->event->id) }}" class="font-black text-gray-900 hover:text-brandOrange uppercase block">
                                                    {{ $ep->event->title }}
                                                </a>
                                                <span class="text-[10px] text-brandOrange font-bold uppercase">
                                                    {{ $ep->event->event_type }}
                                                </span>
                                            @else
                                                <span class="font-bold text-gray-800">Event #{{ $ep->volunteer_event_id }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3 font-bold text-gray-700 whitespace-nowrap">
                                            {{ $ep->event?->event_date ? $ep->event->event_date->format('d M Y') : '—' }}
                                        </td>
                                        <td class="py-3 px-3">
                                            <div class="font-bold text-gray-900 uppercase">
                                                {{ $ep->addedByVolunteer?->full_name ?? ($ep->event?->volunteer?->full_name ?? 'Volunteer') }}
                                            </div>
                                            <div class="font-mono text-[10px] text-brandOrange font-bold">
                                                ID: {{ $ep->addedByVolunteer?->volunteer_id ?? ($ep->event?->volunteer?->volunteer_id ?? 'N/A') }}
                                            </div>
                                        </td>
                                        <td class="py-3 px-3">
                                            <span class="inline-block px-2 py-0.5 rounded text-[9px] font-black uppercase {{ $ep->participation_type === 'beneficiary' ? 'bg-orange-100 text-brandOrange' : 'bg-gray-100 text-gray-700' }}">
                                                {{ ucfirst(str_replace('_', ' ', $ep->participation_type)) }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-3">
                                            <span class="inline-block px-2 py-0.5 rounded text-[9px] font-black uppercase {{ $ep->participation_status === 'benefited' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                                {{ ucfirst($ep->participation_status) }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 font-medium text-gray-800 max-w-xs">
                                            {{ $ep->benefit_details ?? '—' }}
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            @if($ep->proof_image_path)
                                                <div class="inline-flex flex-col items-center gap-0.5">
                                                    <img src="{{ asset('storage/' . $ep->proof_image_path) }}"
                                                         class="w-10 h-10 object-cover rounded-lg border border-orange-200 shadow-xs" alt="Proof thumbnail">
                                                    <span class="text-[9px] font-mono text-emerald-700 font-bold">
                                                        {{ $ep->formatted_proof_size }}
                                                    </span>
                                                </div>
                                            @else
                                                <span class="text-[10px] text-gray-400 italic">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-6 text-center text-gray-400 space-y-1">
                        <span class="text-xl block">📋</span>
                        <span class="text-xs font-bold uppercase text-gray-600 block">No Event Records Yet</span>
                        <p class="text-[11px] text-gray-500">This member has not yet been linked to any volunteer-conducted events.</p>
                    </div>
                @endif
            </div>

        </main>
    </div>
</div>
@endsection
