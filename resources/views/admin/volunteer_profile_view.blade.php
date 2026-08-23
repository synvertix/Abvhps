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
                <span class="bg-orange-100 text-brandOrange text-[9px] font-black px-2.5 py-0.5 rounded border border-orange-200 tracking-widest uppercase shadow-sm">Volunteer Profile Dossier</span>
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
                        <span>🤝</span> Volunteer Dossier: {{ $volunteer->member_full_name ?? 'Volunteer' }}
                    </h3>
                    <p class="text-[11px] text-gray-500 font-semibold mt-0.5">
                        Membership ID: <span class="font-mono text-brandOrange font-bold">{{ implode(' ', str_split($volunteer->membership_id, 4)) }}</span>
                        @if($volunteer->volunteer_id)
                            | Volunteer ID: <span class="font-mono text-emerald-700 font-bold">{{ $volunteer->volunteer_id }}</span>
                        @else
                            | Volunteer ID: <span class="font-mono text-gray-400 italic">Not Assigned</span>
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.volunteers.index') }}" class="bg-gray-800 hover:bg-gray-900 text-white font-black text-[10px] px-4 py-2 rounded-lg shadow-sm uppercase tracking-wide transition flex items-center gap-1.5">
                        <span>←</span> Back To List
                    </a>
                    @if($volunteer->status === 'approved' && !empty($volunteer->volunteer_id))
                        <a href="{{ route('admin.volunteer.view_card', $volunteer->volunteer_id) }}" target="_blank" class="bg-yellow-500 hover:bg-yellow-600 text-white font-black text-[10px] px-4 py-2 rounded-lg shadow-sm uppercase tracking-wide transition flex items-center gap-1.5">
                            <span>🖨️</span> View / Print ID Card
                        </a>
                    @endif
                    <a href="{{ route('admin.volunteers.edit', $volunteer->id) }}" class="bg-brandOrange hover:bg-orange-700 text-white font-black text-[10px] px-4 py-2 rounded-lg shadow-sm uppercase tracking-wide transition flex items-center gap-1.5">
                        <span>✏️</span> Edit Approval
                    </a>
                </div>
            </div>

            <!-- Profile Summary Hero Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
                    <!-- Member Photo Frame -->
                    <div class="relative shrink-0">
                        @if($volunteer->member_photo_path)
                            <img src="{{ asset('storage/' . $volunteer->member_photo_path) }}" class="w-32 h-36 object-cover rounded-xl border-4 border-brandOrange shadow-md" alt="Member Photo">
                        @else
                            <div class="w-32 h-36 bg-gray-100 rounded-xl border-2 border-dashed border-gray-300 flex flex-col items-center justify-center text-gray-400">
                                <span class="text-3xl mb-1">👤</span>
                                <span class="text-[10px] font-bold uppercase">No Photo</span>
                            </div>
                        @endif
                        <span class="absolute -bottom-2.5 left-1/2 -translate-x-1/2 {{ $volunteer->status === 'approved' ? 'bg-green-600' : ($volunteer->status === 'rejected' ? 'bg-red-600' : 'bg-amber-600') }} text-white text-[8px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider shadow">
                            {{ strtoupper($volunteer->status) }}
                        </span>
                    </div>

                    <!-- Core Overview -->
                    <div class="flex-1 text-center md:text-left space-y-2">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-2">
                            <div>
                                <h2 class="text-xl font-black text-gray-900 uppercase tracking-wide">{{ $volunteer->member_full_name ?? 'Volunteer' }}</h2>
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Cadder: <span class="text-brandOrange font-black">{{ $volunteer->cadre ?? 'Not Assigned' }}</span> | Locality: <span class="text-gray-800">{{ $volunteer->locality ?? 'HQ' }}</span></p>
                            </div>
                            <div class="flex flex-wrap gap-1.5 justify-center md:justify-end">
                                <span class="bg-orange-50 text-brandOrange border border-orange-200 text-[10px] font-black px-3 py-1 rounded-md tracking-wider uppercase">
                                    {{ implode(' ', str_split($volunteer->membership_id, 4)) }}
                                </span>
                                @if($volunteer->member_blood_group)
                                    <span class="bg-red-50 text-red-600 border border-red-200 text-[10px] font-black px-2.5 py-1 rounded-md uppercase">
                                        🩸 {{ $volunteer->member_blood_group }}
                                    </span>
                                @endif
                                <span class="bg-blue-50 text-blue-700 border border-blue-200 text-[10px] font-black px-2.5 py-1 rounded-md uppercase">
                                    VOLUNTEER CADRE
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-5 gap-3 pt-3 border-t border-gray-100 text-xs">
                            <div class="bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                                <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider">Volunteer ID</span>
                                <span class="font-mono font-black text-orange-600 text-sm">{{ $volunteer->volunteer_id ?? ($volunteer->volunteer_login_id ?? 'Pending') }}</span>
                            </div>
                            <div class="bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                                <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider">Contact Phone</span>
                                <span class="font-mono font-black text-gray-800">{{ $volunteer->phone }}</span>
                            </div>
                            <div class="bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                                <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider">Email Address</span>
                                <span class="font-mono font-semibold text-gray-800 truncate block">{{ $volunteer->email }}</span>
                            </div>
                            <div class="bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                                <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider">Voter ID</span>
                                <span class="font-mono font-bold text-gray-800 uppercase">{{ $volunteer->voter_id_number }}</span>
                            </div>
                            <div class="bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                                <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider">Account / Login</span>
                                <span class="font-bold {{ $volunteer->status === 'approved' ? 'text-emerald-700' : 'text-amber-700' }}">
                                    {{ $volunteer->status === 'approved' ? 'ACTIVE (ENABLED)' : 'DISABLED' }}
                                </span>
                            </div>
                        </div>

                        @if($volunteer->status === 'approved')
                            <div class="pt-3 border-t border-gray-100 flex flex-wrap items-center justify-between gap-3">
                                <div class="text-xs text-gray-500">
                                    <span>Welcome Email: </span>
                                    <span class="font-bold text-gray-800 font-mono">{{ $volunteer->welcome_email_sent_at ? 'SENT / LOGGED (' . \Carbon\Carbon::parse($volunteer->welcome_email_sent_at)->format('d-M-Y H:i') . ')' : 'LOGGED' }}</span>
                                </div>
                                <form action="{{ route('admin.volunteers.resendCredentials', $volunteer->id) }}" method="POST" onsubmit="return confirm('Generate fresh temporary password and resend welcome credentials to {{ $volunteer->email }}?');" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-black text-[10px] px-3.5 py-1.5 rounded-lg shadow-sm uppercase tracking-wider transition cursor-pointer">
                                        📩 Resend Welcome Credentials
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Detail Information Sections -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Section 1: Banking & Nominee Details -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                    <div class="flex items-center gap-2 border-b border-gray-100 pb-3">
                        <span class="text-lg">🏦</span>
                        <h4 class="text-xs font-black text-brandGray uppercase tracking-wider">Banking & Nominee Details</h4>
                    </div>

                    <div class="space-y-2.5 text-xs">
                        <div class="flex justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500 font-semibold uppercase text-[10px]">Bank Name:</span>
                            <span class="font-bold text-gray-900">{{ $volunteer->bank_name }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500 font-semibold uppercase text-[10px]">Account Holder:</span>
                            <span class="font-bold text-gray-900">{{ $volunteer->account_holder_name }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500 font-semibold uppercase text-[10px]">Account Number:</span>
                            <span class="font-mono font-bold text-gray-900">{{ $volunteer->account_number }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500 font-semibold uppercase text-[10px]">IFSC Code:</span>
                            <span class="font-mono font-bold text-gray-900">{{ $volunteer->ifsc_code }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500 font-semibold uppercase text-[10px]">Branch Name:</span>
                            <span class="font-bold text-gray-900">{{ $volunteer->branch_name }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500 font-semibold uppercase text-[10px]">Nominee Name:</span>
                            <span class="font-bold text-gray-900">{{ $volunteer->nominee_name }} ({{ $volunteer->nominee_relation }})</span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-gray-500 font-semibold uppercase text-[10px]">Nominee Phone:</span>
                            <span class="font-mono font-bold text-gray-900">{{ $volunteer->nominee_phone }}</span>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Attached Documents -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                    <div class="flex items-center gap-2 border-b border-gray-100 pb-3">
                        <span class="text-lg">📁</span>
                        <h4 class="text-xs font-black text-brandGray uppercase tracking-wider">Uploaded Documents & Proofs</h4>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-100 flex items-center justify-between">
                            <div>
                                <span class="font-black text-gray-800 block text-xs">Self Declaration Document</span>
                                <span class="text-[10px] text-gray-400 font-semibold">Mandatory Volunteer Declaration</span>
                            </div>
                            <a href="{{ asset('storage/' . $volunteer->document_declaration_path) }}" target="_blank" class="bg-brandOrange hover:bg-orange-700 text-white font-black text-[10px] px-3 py-1.5 rounded uppercase transition">
                                View File &rarr;
                            </a>
                        </div>

                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-100 flex items-center justify-between">
                            <div>
                                <span class="font-black text-gray-800 block text-xs">Voter ID Card Copy</span>
                                <span class="text-[10px] text-gray-400 font-semibold">Voter ID: {{ $volunteer->voter_id_number }}</span>
                            </div>
                            <a href="{{ asset('storage/' . $volunteer->document_voter_path) }}" target="_blank" class="bg-brandOrange hover:bg-orange-700 text-white font-black text-[10px] px-3 py-1.5 rounded uppercase transition">
                                View File &rarr;
                            </a>
                        </div>

                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-100 flex items-center justify-between">
                            <div>
                                <span class="font-black text-gray-800 block text-xs">Bank Passbook / Cheque Copy</span>
                                <span class="text-[10px] text-gray-400 font-semibold">{{ $volunteer->bank_name }} - {{ $volunteer->account_number }}</span>
                            </div>
                            <a href="{{ asset('storage/' . $volunteer->document_bank_path) }}" target="_blank" class="bg-brandOrange hover:bg-orange-700 text-white font-black text-[10px] px-3.5 py-1.5 rounded uppercase transition">
                                View File &rarr;
                            </a>
                        </div>
                    </div>
                </div>

            </div>

            @php
                $volunteerEvents = \App\Models\VolunteerEvent::where('volunteer_id', $volunteer->id)->orderBy('event_date', 'desc')->get();
                $conductedCount = $volunteerEvents->where('status', 'completed')->count();
                $upcomingCount = $volunteerEvents->where('status', 'upcoming')->count();
                $eventIds = $volunteerEvents->pluck('id');
                $totalParticipantsCount = \App\Models\VolunteerEventMember::whereIn('volunteer_event_id', $eventIds)
                    ->whereIn('participation_status', ['registered', 'participated', 'benefited'])->count();
                $totalBeneficiariesCount = \App\Models\VolunteerEventMember::whereIn('volunteer_event_id', $eventIds)
                    ->where(function($q) {
                        $q->where('participation_type', 'beneficiary')->orWhere('participation_status', 'benefited');
                    })->count();
            @endphp

            <!-- Section 3: Grassroots Seva & Event Statistics -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">🏆</span>
                        <div>
                            <h4 class="text-xs font-black text-brandGray uppercase tracking-wider">Volunteer Service &amp; Event Statistics</h4>
                            <span class="text-[10px] text-gray-500 font-semibold">Operational summary of all community seva events organized by this volunteer</span>
                        </div>
                    </div>
                    <a href="{{ route('admin.volunteer_events.index', ['volunteer_id' => $volunteer->id]) }}"
                       class="bg-orange-50 hover:bg-orange-100 text-brandOrange border border-orange-200 text-[10px] font-black px-3 py-1 rounded transition">
                        View In Events Roster &rarr;
                    </a>
                </div>

                <!-- Metrics Matrix -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="bg-emerald-50/70 p-3.5 rounded-xl border border-emerald-200">
                        <span class="text-[9px] font-black text-emerald-800 uppercase block">Events Conducted</span>
                        <div class="font-mono text-xl font-black text-emerald-700 mt-0.5">{{ $conductedCount }}</div>
                        <span class="text-[9px] text-emerald-600 font-semibold">Completed</span>
                    </div>

                    <div class="bg-blue-50/70 p-3.5 rounded-xl border border-blue-200">
                        <span class="text-[9px] font-black text-blue-800 uppercase block">Upcoming Events</span>
                        <div class="font-mono text-xl font-black text-blue-700 mt-0.5">{{ $upcomingCount }}</div>
                        <span class="text-[9px] text-blue-600 font-semibold">Scheduled</span>
                    </div>

                    <div class="bg-amber-50/70 p-3.5 rounded-xl border border-amber-200">
                        <span class="text-[9px] font-black text-amber-800 uppercase block">Total Participants</span>
                        <div class="font-mono text-xl font-black text-amber-700 mt-0.5">{{ $totalParticipantsCount }}</div>
                        <span class="text-[9px] text-amber-600 font-semibold">Linked attendees</span>
                    </div>

                    <div class="bg-orange-50/70 p-3.5 rounded-xl border border-orange-200">
                        <span class="text-[9px] font-black text-brandOrange uppercase block">Total Beneficiaries</span>
                        <div class="font-mono text-xl font-black text-brandOrange mt-0.5">{{ $totalBeneficiariesCount }}</div>
                        <span class="text-[9px] text-orange-700 font-semibold">Received seva benefit</span>
                    </div>
                </div>

                <!-- Recent Events Table -->
                @if($volunteerEvents->count() > 0)
                    <div class="overflow-x-auto pt-2">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="bg-gray-50 text-gray-600 uppercase text-[9px] font-black tracking-wider border-b border-gray-200">
                                    <th class="py-2 px-3">Event Title</th>
                                    <th class="py-2 px-3">Type</th>
                                    <th class="py-2 px-3">Date</th>
                                    <th class="py-2 px-3">Location</th>
                                    <th class="py-2 px-3 text-center">Status</th>
                                    <th class="py-2 px-3 text-center">Participants</th>
                                    <th class="py-2 px-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($volunteerEvents->take(5) as $ve)
                                    <tr class="hover:bg-orange-50/30 transition">
                                        <td class="py-2.5 px-3 font-bold text-gray-900 uppercase">
                                            {{ $ve->title }}
                                        </td>
                                        <td class="py-2.5 px-3 text-[11px] text-brandOrange font-bold">
                                            {{ $ve->event_type }}
                                        </td>
                                        <td class="py-2.5 px-3 text-gray-700 whitespace-nowrap">
                                            {{ $ve->event_date->format('d-M-Y') }}
                                        </td>
                                        <td class="py-2.5 px-3 text-gray-600 text-[11px] truncate max-w-xs">
                                            {{ implode(', ', array_filter([$ve->venue, $ve->village, $ve->mandal])) ?: '—' }}
                                        </td>
                                        <td class="py-2.5 px-3 text-center">
                                            <span class="inline-block px-2 py-0.5 rounded text-[9px] font-black uppercase border {{ $ve->status_badge_class }}">
                                                {{ strtoupper($ve->status) }}
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-3 text-center font-mono font-bold text-gray-900">
                                            {{ $ve->participants_count }}
                                        </td>
                                        <td class="py-2.5 px-3 text-right">
                                            <a href="{{ route('admin.volunteer_events.show', $ve->id) }}" class="text-brandOrange hover:underline font-bold text-[10px]">
                                                Open Dossier &rarr;
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-4 text-center text-gray-400 text-xs">
                        No events recorded yet by this volunteer.
                    </div>
                @endif
            </div>

        </main>
    </div>
</div>
@endsection
