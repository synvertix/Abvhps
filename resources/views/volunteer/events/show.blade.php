@extends('layouts.app')

@section('title', $event->title . ' | Event Details | ABVHPS Volunteer Portal')

@section('content')
<div class="bg-gray-50 min-h-screen pb-16">

    {{-- Header Banner --}}
    <div class="bg-gradient-to-r from-orange-900 via-orange-800 to-amber-900 text-white py-10 px-4 shadow-md border-b-4 border-yellow-500">
        <div class="max-w-6xl mx-auto flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="bg-yellow-400 text-orange-950 text-[10px] font-black px-3.5 py-1 rounded-full uppercase tracking-widest inline-block shadow">
                        {{ $event->event_type }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border {{ $event->status_badge_class }}">
                        {{ strtoupper($event->status) }}
                    </span>
                </div>
                <h1 class="text-2xl md:text-3xl font-black uppercase tracking-wide text-white">
                    {{ $event->title }}
                </h1>
                <p class="text-xs text-yellow-200/90 font-medium mt-0.5">
                    🗓️ {{ $event->event_date->format('d M Y') }} &bull; 📍 {{ implode(', ', array_filter([$event->venue, $event->village, $event->mandal, $event->district])) ?: 'Location specified' }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if($event->status === 'upcoming')
                    <a href="{{ route('volunteer.events.edit', $event->id) }}"
                       class="bg-white/15 hover:bg-white/25 border border-white/20 text-white text-xs font-bold px-4 py-2 rounded-xl uppercase tracking-wider transition min-h-[44px] inline-flex items-center">
                        Edit Event
                    </a>
                @endif
                <a href="{{ route('volunteer.events.index') }}"
                   class="bg-white/15 hover:bg-white/25 border border-white/20 text-white text-xs font-bold px-4 py-2 rounded-xl uppercase tracking-wider transition min-h-[44px] inline-flex items-center">
                    &larr; All Events
                </a>
            </div>
        </div>
    </div>

    {{-- Main Container --}}
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 space-y-6">

        {{-- Flash Alerts --}}
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-2xl text-xs font-bold shadow-sm flex items-center justify-between">
                <span>✓ {{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" class="text-emerald-600 font-black">×</button>
            </div>
        @endif
        @if(session('error'))
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-900 rounded-2xl text-xs font-bold shadow-sm flex items-center justify-between">
                <span>✕ {{ session('error') }}</span>
                <button onclick="this.parentElement.remove()" class="text-rose-600 font-black">×</button>
            </div>
        @endif

        {{-- Event Overview & Metrics Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Left: Event Dossier --}}
            <div class="lg:col-span-2 bg-white rounded-3xl border border-gray-200 shadow-sm p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="font-black text-gray-800 text-sm uppercase tracking-wide flex items-center gap-2">
                        <span>🪔</span> Event Information
                    </h3>
                    <span class="text-xs font-bold text-gray-500">
                        Organizer ID: <span class="font-mono text-brandOrange font-black">{{ $volunteer->volunteer_id }}</span>
                    </span>
                </div>

                @if($event->description)
                    <div>
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block mb-1">Description</span>
                        <p class="text-xs text-gray-700 leading-relaxed font-medium">
                            {{ $event->description }}
                        </p>
                    </div>
                @endif

                @if($event->outcome)
                    <div class="p-4 bg-amber-50/70 rounded-2xl border border-amber-200 space-y-1">
                        <span class="text-[10px] font-black text-amber-900 uppercase tracking-wider block">🏆 Work Conducted &amp; Outcome</span>
                        <p class="text-xs text-amber-950 font-medium leading-relaxed">
                            {{ $event->outcome }}
                        </p>
                    </div>
                @endif

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2 text-xs border-t border-gray-100">
                    <div class="bg-gray-50 p-3 rounded-xl">
                        <span class="text-[9px] font-bold text-gray-400 uppercase block">Event Date</span>
                        <span class="font-bold text-gray-900">{{ $event->event_date->format('d M Y') }}</span>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-xl">
                        <span class="text-[9px] font-bold text-gray-400 uppercase block">Timings</span>
                        <span class="font-bold text-gray-900">
                            {{ $event->start_time ? $event->start_time . ($event->end_time ? ' - ' . $event->end_time : '') : 'Full Day' }}
                        </span>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-xl">
                        <span class="text-[9px] font-bold text-gray-400 uppercase block">Mandal</span>
                        <span class="font-bold text-gray-900 truncate block">{{ $event->mandal ?? '—' }}</span>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-xl">
                        <span class="text-[9px] font-bold text-gray-400 uppercase block">District</span>
                        <span class="font-bold text-gray-900 truncate block">{{ $event->district ?? '—' }}</span>
                    </div>
                </div>
            </div>

            {{-- Right: Participant Metrics Summary --}}
            <div class="bg-gradient-to-br from-orange-50 to-amber-50 rounded-3xl border border-orange-200 p-6 flex flex-col justify-between space-y-4">
                <div>
                    <h3 class="font-black text-orange-950 text-sm uppercase tracking-wide mb-1 flex items-center gap-2">
                        <span>👥</span> Seva Participation
                    </h3>
                    <p class="text-xs text-orange-900/80">
                        Summary of registered devotees, active participants, and beneficiaries recorded for this event.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-white p-4 rounded-2xl border border-orange-200 shadow-xs text-center">
                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-wider block">Participants</span>
                        <span class="font-mono text-2xl font-black text-gray-900 mt-1 block">{{ $event->participants_count }}</span>
                        <span class="text-[9px] text-gray-500 font-semibold">Total Linked</span>
                    </div>
                    <div class="bg-white p-4 rounded-2xl border border-orange-200 shadow-xs text-center">
                        <span class="text-[9px] font-black text-brandOrange uppercase tracking-wider block">Beneficiaries</span>
                        <span class="font-mono text-2xl font-black text-brandOrange mt-1 block">{{ $event->beneficiaries_count }}</span>
                        <span class="text-[9px] text-orange-600 font-semibold">Received Benefit</span>
                    </div>
                </div>

                <a href="#add-member-section"
                   class="w-full bg-brandOrange hover:bg-orange-600 text-white font-black text-xs py-3 px-4 rounded-xl shadow-xs transition text-center uppercase tracking-wider block">
                    ➕ Link Member to Event
                </a>
            </div>

        </div>

        {{-- Add Member / Beneficiary Section --}}
        <div id="add-member-section" class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 sm:p-8 space-y-6">
            <div class="border-b border-gray-100 pb-4">
                <span class="text-[10px] bg-orange-100 text-brandOrange font-black px-2.5 py-0.5 rounded uppercase tracking-wider inline-block mb-1">
                    Participant Linking
                </span>
                <h2 class="text-base font-black text-gray-900 uppercase tracking-wide flex items-center gap-2">
                    <span>🔍</span> Search &amp; Add Member by Membership ID
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Lookup exact 12-digit Membership ID. Member profile details are strictly verified and protected.
                </p>
            </div>

            {{-- Step 1: Exact Membership ID Search Box --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div class="md:col-span-2">
                    <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-1.5">
                        Enter Exact Membership ID <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex gap-2">
                        <input type="text" id="memberSearchInput" maxlength="12"
                               placeholder="e.g. 688688688688"
                               class="flex-1 px-4 py-3 rounded-xl border border-gray-300 focus:border-brandOrange focus:ring-2 focus:ring-orange-200 text-sm font-mono font-bold uppercase transition">
                        <button type="button" id="memberSearchBtn" onclick="performMemberLookup()"
                                class="bg-gray-900 hover:bg-gray-800 text-white text-xs font-black px-5 py-3 rounded-xl shadow uppercase tracking-wider transition flex items-center gap-1.5 cursor-pointer">
                            <span id="searchSpinner" class="hidden animate-spin">⏳</span>
                            <span>Search</span>
                        </button>
                    </div>
                </div>

                {{-- Quick Lookup Result Card --}}
                <div id="searchResultBox" class="p-3 bg-gray-50 rounded-2xl border border-gray-200 text-xs min-h-[52px] flex items-center">
                    <span class="text-gray-400 font-semibold text-[11px]">Enter Membership ID to verify member...</span>
                </div>
            </div>

            {{-- Step 2: Member Linking Form (Revealed/Filled on valid lookup) --}}
            <form action="{{ route('volunteer.events.add_member', $event->id) }}" method="POST" enctype="multipart/form-data" class="pt-4 border-t border-gray-100 space-y-4">
                @csrf

                <input type="hidden" name="membership_id" id="confirmedMembershipId" value="{{ old('membership_id') }}">

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- Selected Member Preview Tag --}}
                    <div class="sm:col-span-2 lg:col-span-4 p-3 bg-orange-50/70 border border-orange-200 rounded-2xl flex items-center justify-between text-xs" id="confirmedMemberBanner" style="{{ old('membership_id') ? '' : 'display: none;' }}">
                        <div class="flex items-center gap-3">
                            <span class="text-lg">✓</span>
                            <div>
                                <span class="font-black text-orange-950 uppercase block" id="confirmedMemberName">
                                    {{ old('member_name_preview', 'Selected Member') }}
                                </span>
                                <span class="text-[10px] text-orange-800/80 font-mono" id="confirmedMemberIdLabel">
                                    ID: {{ old('membership_id') }}
                                </span>
                            </div>
                        </div>
                        <span class="text-[10px] bg-emerald-600 text-white font-black px-2.5 py-0.5 rounded-full uppercase">
                            Active Member
                        </span>
                    </div>

                    {{-- Participation Type --}}
                    <div>
                        <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-1.5">
                            Participation Type <span class="text-rose-500">*</span>
                        </label>
                        <select name="participation_type" required
                                class="w-full px-3 py-2.5 rounded-xl border border-gray-300 focus:border-brandOrange text-xs font-semibold bg-white">
                            @foreach($participationTypes as $k => $l)
                                <option value="{{ $k }}" {{ old('participation_type') === $k ? 'selected' : '' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Participation Status --}}
                    <div>
                        <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-1.5">
                            Participation Status <span class="text-rose-500">*</span>
                        </label>
                        <select name="participation_status" required
                                class="w-full px-3 py-2.5 rounded-xl border border-gray-300 focus:border-brandOrange text-xs font-semibold bg-white">
                            @foreach($participationStatuses as $k => $l)
                                <option value="{{ $k }}" {{ old('participation_status', 'participated') === $k ? 'selected' : '' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Proof Image Upload (Max 5MB -> Strict <= 2KB stored) --}}
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-1">
                            Proof Image (Optional)
                            <span class="text-[10px] text-emerald-700 font-bold ml-1 bg-emerald-50 px-2 py-0.5 rounded">Auto-compressed strictly &le; 2 KB</span>
                        </label>
                        <input type="file" name="proof_image" accept="image/jpeg,image/png,image/webp,image/jpg"
                               class="w-full px-3 py-2 text-xs rounded-xl border border-gray-300 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-orange-50 file:text-brandOrange hover:file:bg-orange-100 transition">
                        <span class="text-[10px] text-gray-500 block mt-0.5">Accepts JPG/PNG/WebP up to 5 MB. Server converts to tiny <=2KB thumbnail.</span>
                    </div>

                    {{-- Benefit Details --}}
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-1.5">
                            Benefit / Service Received
                        </label>
                        <input type="text" name="benefit_details" value="{{ old('benefit_details') }}" maxlength="2000"
                               placeholder="e.g. Received Annadanam prasadam packet / Eye checkup / Akshara kit"
                               class="w-full px-3 py-2.5 rounded-xl border border-gray-300 text-xs font-medium">
                    </div>

                    {{-- Notes --}}
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-1.5">
                            Internal Volunteer Notes
                        </label>
                        <input type="text" name="notes" value="{{ old('notes') }}" maxlength="2000"
                               placeholder="e.g. Accompanied by family members / First time participant"
                               class="w-full px-3 py-2.5 rounded-xl border border-gray-300 text-xs font-medium">
                    </div>
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit" id="addMemberSubmitBtn"
                            class="bg-brandOrange hover:bg-orange-600 text-white text-xs font-black px-6 py-3 rounded-xl shadow-md uppercase tracking-wider transition cursor-pointer">
                        ➕ Add Member to Event Roster
                    </button>
                </div>
            </form>
        </div>

        {{-- Participating & Benefited Members Roster Table --}}
        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm overflow-hidden space-y-4 p-6 sm:p-8">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 border-b border-gray-100 pb-4">
                <div>
                    <h2 class="text-base font-black text-gray-900 uppercase tracking-wide flex items-center gap-2">
                        <span>📋</span> Participating &amp; Benefited Members ({{ $event->eventMembers->count() }})
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Roster of all members linked to this specific seva event with verification proofs.
                    </p>
                </div>
            </div>

            @if($event->eventMembers->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600 uppercase text-[10px] font-black tracking-wider border-b border-gray-200">
                                <th class="py-3 px-4">#</th>
                                <th class="py-3 px-4">Member Info</th>
                                <th class="py-3 px-4">Type</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4">Benefit / Notes</th>
                                <th class="py-3 px-4 text-center">Proof (Max 2KB)</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($event->eventMembers as $index => $em)
                                <tr class="hover:bg-orange-50/30 transition">
                                    <td class="py-3.5 px-4 font-bold text-gray-400">{{ $index + 1 }}</td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-black text-gray-900 uppercase">
                                            {{ $em->membership->full_name ?? 'Member' }}
                                        </div>
                                        <div class="font-mono text-[11px] text-brandOrange font-bold tracking-wider">
                                            {{ implode(' ', str_split($em->membership_id, 4)) }}
                                        </div>
                                        @if($em->membership?->district)
                                            <div class="text-[10px] text-gray-500 font-medium">
                                                📍 {{ $em->membership->district }}, {{ $em->membership->state ?? 'AP' }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider {{ $em->participation_type === 'beneficiary' ? 'bg-orange-100 text-brandOrange' : 'bg-gray-100 text-gray-700' }}">
                                            {{ ucfirst(str_replace('_', ' ', $em->participation_type)) }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-black uppercase {{ $em->participation_status === 'benefited' ? 'bg-emerald-100 text-emerald-800' : ($em->participation_status === 'participated' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700') }}">
                                            {{ ucfirst($em->participation_status) }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 max-w-xs">
                                        @if($em->benefit_details)
                                            <div class="font-bold text-gray-900 truncate" title="{{ $em->benefit_details }}">
                                                {{ $em->benefit_details }}
                                            </div>
                                        @endif
                                        @if($em->notes)
                                            <div class="text-[10px] text-gray-500 italic truncate" title="{{ $em->notes }}">
                                                Note: {{ $em->notes }}
                                            </div>
                                        @endif
                                        @if(!$em->benefit_details && !$em->notes)
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        @if($em->proof_image_path)
                                            <div class="inline-flex flex-col items-center gap-1">
                                                <button type="button" onclick="showProofModal('{{ asset('storage/' . $em->proof_image_path) }}', '{{ $em->membership->full_name ?? $em->membership_id }}', '{{ $em->formatted_proof_size }}')"
                                                        class="group relative block rounded-lg overflow-hidden border-2 border-orange-300 shadow-xs hover:scale-105 transition cursor-pointer">
                                                    <img src="{{ asset('storage/' . $em->proof_image_path) }}"
                                                         class="w-12 h-12 object-cover" alt="Proof thumbnail">
                                                    <span class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-[10px]">
                                                        🔍
                                                    </span>
                                                </button>
                                                <span class="text-[9px] font-mono font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded">
                                                    {{ $em->formatted_proof_size }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-[10px] text-gray-400 italic">No proof</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="inline-flex items-center gap-1">
                                            <button type="button" onclick="openEditMemberModal({{ json_encode($em) }})"
                                                    class="p-2 text-gray-600 hover:text-brandOrange hover:bg-orange-50 rounded-lg transition" title="Edit Participant">
                                                ✏️
                                            </button>
                                            <form action="{{ route('volunteer.events.remove_member', [$event->id, $em->id]) }}" method="POST"
                                                  onsubmit="return confirm('Remove member {{ $em->membership_id }} from this event?')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition cursor-pointer" title="Remove Member">
                                                    🗑️
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="py-10 text-center space-y-2">
                    <span class="text-2xl">👥</span>
                    <h3 class="font-bold text-gray-700 text-sm">No members linked to this event yet</h3>
                    <p class="text-xs text-gray-500 max-w-sm mx-auto">
                        Use the search tool above to find active members by their 12-digit Membership ID and record their participation or seva benefits.
                    </p>
                </div>
            @endif
        </div>

    </div>

</div>

{{-- Proof Image Zoom Modal --}}
<div id="proofModal" class="fixed inset-0 bg-black/70 backdrop-blur-xs z-50 hidden items-center justify-center p-4" onclick="closeProofModal()">
    <div class="bg-white rounded-3xl max-w-sm w-full p-6 space-y-4 shadow-2xl text-center" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <div>
                <h4 class="font-black text-gray-900 text-sm uppercase" id="proofModalTitle">Beneficiary Proof</h4>
                <span class="text-[10px] text-emerald-700 font-mono font-bold" id="proofModalSize">Stored Tiny Thumbnail &le; 2 KB</span>
            </div>
            <button type="button" onclick="closeProofModal()" class="text-gray-400 hover:text-gray-700 font-black text-lg">&times;</button>
        </div>
        <div class="p-4 bg-gray-50 rounded-2xl flex items-center justify-center">
            <img id="proofModalImg" src="" class="max-w-[200px] max-h-[200px] rounded-xl border border-gray-300 shadow-sm object-contain" alt="Proof Zoom">
        </div>
        <p class="text-[11px] text-gray-500 leading-relaxed">
            Ultra-compressed tiny identification thumbnail strictly verified under 2048 bytes.
        </p>
        <button type="button" onclick="closeProofModal()" class="w-full bg-gray-900 hover:bg-gray-800 text-white font-bold text-xs py-2.5 rounded-xl uppercase tracking-wider">
            Close
        </button>
    </div>
</div>

{{-- Edit Participant Modal --}}
<div id="editParticipantModal" class="fixed inset-0 bg-black/70 backdrop-blur-xs z-50 hidden items-center justify-center p-4" onclick="closeEditModal()">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-2xl" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <h4 class="font-black text-gray-900 text-sm uppercase">Update Participant / Beneficiary</h4>
            <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-700 font-black text-lg">&times;</button>
        </div>

        <form id="editMemberForm" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
            @csrf
            <div>
                <span class="block text-[10px] font-bold text-gray-400 uppercase">Membership ID</span>
                <span class="font-mono font-black text-gray-900 text-sm" id="editModalMembershipId"></span>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-black text-gray-700 uppercase mb-1">Participation Type</label>
                    <select name="participation_type" id="editParticipationType" required class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white text-xs">
                        @foreach($participationTypes as $k => $l)
                            <option value="{{ $k }}">{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-black text-gray-700 uppercase mb-1">Status</label>
                    <select name="participation_status" id="editParticipationStatus" required class="w-full px-3 py-2 rounded-xl border border-gray-300 bg-white text-xs">
                        @foreach($participationStatuses as $k => $l)
                            <option value="{{ $k }}">{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-black text-gray-700 uppercase mb-1">Benefit Received</label>
                <input type="text" name="benefit_details" id="editBenefitDetails" class="w-full px-3 py-2 rounded-xl border border-gray-300 text-xs">
            </div>

            <div>
                <label class="block font-black text-gray-700 uppercase mb-1">Notes</label>
                <input type="text" name="notes" id="editNotes" class="w-full px-3 py-2 rounded-xl border border-gray-300 text-xs">
            </div>

            <div>
                <label class="block font-black text-gray-700 uppercase mb-1">
                    Replace Proof Image
                    <span class="text-emerald-700 text-[10px]">(Strict &le; 2 KB)</span>
                </label>
                <input type="file" name="proof_image" accept="image/jpeg,image/png,image/webp,image/jpg" class="w-full text-xs">
            </div>

            <div class="pt-3 border-t border-gray-100 flex justify-end gap-2">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 rounded-xl text-gray-600 bg-gray-100 font-bold uppercase tracking-wider text-[11px]">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl text-white bg-brandOrange hover:bg-orange-600 font-black uppercase tracking-wider text-[11px]">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function performMemberLookup() {
    const input = document.getElementById('memberSearchInput');
    const rawVal = input.value.trim();
    const resultBox = document.getElementById('searchResultBox');
    const spinner = document.getElementById('searchSpinner');

    if (!rawVal) {
        resultBox.innerHTML = '<span class="text-rose-600 font-bold text-[11px]">Please enter a 12-digit Membership ID.</span>';
        return;
    }

    spinner.classList.remove('hidden');
    resultBox.innerHTML = '<span class="text-gray-500 font-semibold text-[11px]">Verifying Membership ID on server...</span>';

    fetch('{{ route('volunteer.member_search.lookup') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ membership_id: rawVal })
    })
    .then(res => res.json())
    .then(data => {
        spinner.classList.add('hidden');
        if (data.success && data.member) {
            const m = data.member;
            resultBox.innerHTML = `
                <div class="flex items-center justify-between w-full">
                    <div>
                        <div class="font-black text-emerald-800 uppercase text-[11px]">✓ Member Found</div>
                        <div class="font-bold text-gray-900">${m.full_name} (${m.membership_id})</div>
                        <div class="text-[10px] text-gray-500 font-medium">📍 ${m.district}, ${m.state}</div>
                    </div>
                    <button type="button" onclick="confirmMemberSelection('${m.membership_id}', '${m.full_name.replace(/'/g, "\\'")}')"
                            class="bg-emerald-600 hover:bg-emerald-700 text-white font-black text-[10px] px-3 py-1.5 rounded-lg uppercase tracking-wider cursor-pointer">
                        Select
                    </button>
                </div>
            `;
            confirmMemberSelection(m.membership_id, m.full_name);
        } else {
            resultBox.innerHTML = '<span class="text-rose-600 font-bold text-[11px]">✕ ' + (data.message || 'Member not found.') + '</span>';
            document.getElementById('confirmedMembershipId').value = '';
            document.getElementById('confirmedMemberBanner').style.display = 'none';
        }
    })
    .catch(err => {
        spinner.classList.add('hidden');
        resultBox.innerHTML = '<span class="text-rose-600 font-bold text-[11px]">Lookup failed. Please check credentials.</span>';
    });
}

function confirmMemberSelection(membershipId, memberName) {
    document.getElementById('confirmedMembershipId').value = membershipId;
    document.getElementById('confirmedMemberName').innerText = memberName;
    document.getElementById('confirmedMemberIdLabel').innerText = 'Membership ID: ' + membershipId;
    document.getElementById('confirmedMemberBanner').style.display = 'flex';
}

function showProofModal(imgUrl, memberName, size) {
    document.getElementById('proofModalImg').src = imgUrl;
    document.getElementById('proofModalTitle').innerText = memberName + ' — Proof';
    document.getElementById('proofModalSize').innerText = 'Stored Size: ' + size + ' (Strict <= 2 KB)';
    const modal = document.getElementById('proofModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeProofModal() {
    const modal = document.getElementById('proofModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function openEditMemberModal(em) {
    document.getElementById('editModalMembershipId').innerText = em.membership_id;
    document.getElementById('editParticipationType').value = em.participation_type || 'participant';
    document.getElementById('editParticipationStatus').value = em.participation_status || 'participated';
    document.getElementById('editBenefitDetails').value = em.benefit_details || '';
    document.getElementById('editNotes').value = em.notes || '';
    document.getElementById('editMemberForm').action = '/volunteer/events/{{ $event->id }}/members/' + em.id + '/update';

    const modal = document.getElementById('editParticipantModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeEditModal() {
    const modal = document.getElementById('editParticipantModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>
@endsection
