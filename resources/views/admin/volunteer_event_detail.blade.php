@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100/60 flex flex-col md:flex-row select-none">

    <!-- MASTER ADMINISTRATIVE SIDEBAR -->
    @include('admin.partials.sidebar')

    <!-- MASTER MAIN WORKSPACE VIEWPORT DESK -->
    <div class="flex-1 flex flex-col overflow-hidden">

        <!-- Top Status Banner -->
        <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                @include('admin.partials.header_button')
                <span class="text-sm font-black text-brandGray uppercase tracking-wider">System View:</span>
                <span class="bg-orange-100 text-brandOrange text-[9px] font-black px-2.5 py-0.5 rounded border border-orange-200 tracking-widest uppercase shadow-sm">
                    Event &amp; Beneficiary Dossier
                </span>
            </div>
            <div class="text-right text-[10px] font-mono font-black text-gray-500">
                System Sync: {{ \Carbon\Carbon::now()->format('d-M-Y H:i') }} IST
            </div>
        </header>

        <!-- Dynamic Content Workspace Container -->
        <main class="flex-1 overflow-y-auto p-6 space-y-6">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-xl text-xs font-bold shadow-sm flex items-center justify-between">
                    <span>✓ {{ session('success') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-emerald-600 font-black">×</button>
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 bg-rose-50 border border-rose-200 text-rose-900 rounded-xl text-xs font-bold shadow-sm flex items-center justify-between">
                    <span>✕ {{ session('error') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-rose-600 font-black">×</button>
                </div>
            @endif

            <!-- Header Action Controls -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h3 class="text-sm font-black text-brandGray uppercase tracking-wider flex items-center gap-2">
                        <span>🪔</span> Event Dossier: {{ $event->title }}
                    </h3>
                    <p class="text-[11px] text-gray-500 font-semibold mt-0.5">
                        Organizer: <span class="font-bold text-gray-800 uppercase">{{ $event->volunteer?->full_name }}</span>
                        | Volunteer ID: <span class="font-mono text-brandOrange font-bold">{{ $event->volunteer?->volunteer_id }}</span>
                        | Status: <span class="uppercase font-bold text-emerald-700">{{ $event->status }}</span>
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.volunteer_events.index') }}" class="bg-gray-800 hover:bg-gray-900 text-white font-black text-[10px] px-4 py-2 rounded-lg shadow-sm uppercase tracking-wide transition flex items-center gap-1.5">
                        <span>←</span> Back To Events Roster
                    </a>
                </div>
            </div>

            <!-- Event Dossier Hero Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-gray-100 pb-4">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-wider px-2.5 py-0.5 rounded-full border {{ $event->status_badge_class }}">
                            {{ strtoupper($event->status) }}
                        </span>
                        <h2 class="text-xl font-black text-gray-900 uppercase tracking-wide mt-1">
                            {{ $event->title }}
                        </h2>
                        <span class="text-xs font-bold text-brandOrange uppercase tracking-wider">
                            {{ $event->event_type }}
                        </span>
                    </div>

                    <div class="flex gap-2">
                        <div class="bg-gray-50 px-4 py-2 rounded-xl border border-gray-200 text-center">
                            <span class="text-[9px] font-black text-gray-400 uppercase block">Participants</span>
                            <span class="font-mono text-lg font-black text-gray-900">{{ $event->participants_count }}</span>
                        </div>
                        <div class="bg-orange-50 px-4 py-2 rounded-xl border border-orange-200 text-center">
                            <span class="text-[9px] font-black text-brandOrange uppercase block">Beneficiaries</span>
                            <span class="font-mono text-lg font-black text-brandOrange">{{ $event->beneficiaries_count }}</span>
                        </div>
                    </div>
                </div>

                <!-- Event Details Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                        <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider">Event Date</span>
                        <span class="font-bold text-gray-900 block mt-0.5">{{ $event->event_date->format('d-M-Y') }}</span>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                        <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider">Schedule</span>
                        <span class="font-bold text-gray-900 block mt-0.5">
                            {{ $event->start_time ? $event->start_time . ($event->end_time ? ' - ' . $event->end_time : '') : 'Full Day Seva' }}
                        </span>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                        <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider">Organizer Name</span>
                        <span class="font-bold text-gray-900 block mt-0.5 uppercase">{{ $event->volunteer?->full_name }}</span>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                        <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider">Volunteer ID</span>
                        <span class="font-mono font-black text-brandOrange block mt-0.5">{{ $event->volunteer?->volunteer_id }}</span>
                    </div>

                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 md:col-span-2 lg:col-span-4">
                        <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider">Jurisdiction &amp; Venue</span>
                        <span class="font-semibold text-gray-800 block mt-0.5">
                            📍 {{ implode(', ', array_filter([$event->venue, $event->village, $event->mandal, $event->district, $event->state])) ?: 'Not specified' }}
                        </span>
                    </div>
                </div>

                @if($event->description)
                    <div>
                        <span class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Event Description</span>
                        <p class="text-xs text-gray-700 leading-relaxed font-medium bg-gray-50/50 p-3 rounded-xl border border-gray-100">
                            {{ $event->description }}
                        </p>
                    </div>
                @endif

                @if($event->outcome)
                    <div>
                        <span class="block text-[10px] font-black text-amber-900 uppercase tracking-wider mb-1">Seva Delivered &amp; Outcome Summary</span>
                        <p class="text-xs text-gray-800 leading-relaxed font-medium bg-amber-50/70 p-3 rounded-xl border border-amber-200">
                            {{ $event->outcome }}
                        </p>
                    </div>
                @endif
            </div>

            <!-- Participating & Benefited Members Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-black text-brandGray uppercase tracking-wider flex items-center gap-2">
                            <span>👥</span> Participating &amp; Benefited Members ({{ $event->eventMembers->count() }})
                        </h4>
                        <p class="text-[10px] text-gray-500 font-semibold mt-0.5">
                            Devotees and beneficiaries linked to this event with verified identification and proof photos
                        </p>
                    </div>
                </div>

                @if($event->eventMembers->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="bg-gray-50 text-gray-600 uppercase text-[9px] font-black tracking-wider border-b border-gray-200">
                                    <th class="py-3 px-3">#</th>
                                    <th class="py-3 px-3">Membership Details</th>
                                    <th class="py-3 px-3">Type</th>
                                    <th class="py-3 px-3">Status</th>
                                    <th class="py-3 px-3">Benefit Details</th>
                                    <th class="py-3 px-3">Notes</th>
                                    <th class="py-3 px-3 text-center">Proof Thumbnail</th>
                                    <th class="py-3 px-3">Added By / Date</th>
                                    <th class="py-3 px-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($event->eventMembers as $index => $em)
                                    <tr class="hover:bg-orange-50/30 transition">
                                        <td class="py-3.5 px-3 font-bold text-gray-400">{{ $index + 1 }}</td>
                                        <td class="py-3.5 px-3">
                                            @if($em->membership)
                                                <a href="{{ route('admin.membership.view', $em->membership->id) }}"
                                                   class="font-black text-gray-900 hover:text-brandOrange uppercase block text-xs">
                                                    {{ $em->membership->full_name }}
                                                </a>
                                            @else
                                                <span class="font-black text-gray-900 uppercase block text-xs">Member Record</span>
                                            @endif
                                            <span class="font-mono text-[11px] text-brandOrange font-bold tracking-wider">
                                                {{ implode(' ', str_split($em->membership_id, 4)) }}
                                            </span>
                                            @if($em->membership?->district)
                                                <div class="text-[10px] text-gray-500">
                                                    📍 {{ $em->membership->district }}, {{ $em->membership->state ?? 'AP' }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-3">
                                            <span class="inline-block px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider {{ $em->participation_type === 'beneficiary' ? 'bg-orange-100 text-brandOrange border border-orange-200' : 'bg-gray-100 text-gray-700' }}">
                                                {{ ucfirst(str_replace('_', ' ', $em->participation_type)) }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-3">
                                            <span class="inline-block px-2 py-0.5 rounded text-[9px] font-black uppercase {{ $em->participation_status === 'benefited' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                                {{ ucfirst($em->participation_status) }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-3 max-w-xs font-semibold text-gray-800">
                                            {{ $em->benefit_details ?? '—' }}
                                        </td>
                                        <td class="py-3.5 px-3 text-[11px] text-gray-500 italic max-w-xs">
                                            {{ $em->notes ?? '—' }}
                                        </td>
                                        <td class="py-3.5 px-3 text-center">
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
                                                    <span class="text-[9px] font-mono font-bold text-emerald-700 bg-emerald-50 px-1 rounded">
                                                        {{ $em->formatted_proof_size }}
                                                    </span>
                                                </div>
                                            @else
                                                <span class="text-[10px] text-gray-400 italic">No proof</span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-3 text-[11px] text-gray-600">
                                            <div>ID: <strong class="font-mono text-gray-900">{{ $em->addedByVolunteer?->volunteer_id ?? 'Volunteer' }}</strong></div>
                                            <div class="text-[10px] text-gray-400">{{ $em->created_at->format('d M Y, h:i A') }}</div>
                                        </td>
                                        <td class="py-3.5 px-3 text-right">
                                            <button type="button" onclick="openReplaceProofModal({{ $em->id }}, '{{ $em->membership_id }}')"
                                                    class="bg-gray-100 hover:bg-orange-100 text-gray-700 hover:text-brandOrange font-bold text-[10px] px-2.5 py-1.5 rounded-lg transition" title="Replace Proof Image">
                                                📷 Replace Proof
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-8 text-center text-gray-400 space-y-1">
                        <span class="text-2xl block">👥</span>
                        <span class="text-xs font-bold uppercase block text-gray-700">No Members Linked Yet</span>
                        <p class="text-[11px] text-gray-500">The organizer volunteer has not yet recorded participating devotees for this event.</p>
                    </div>
                @endif
            </div>

        </main>
    </div>
</div>

{{-- Proof Image Zoom Modal --}}
<div id="proofModal" class="fixed inset-0 bg-black/70 backdrop-blur-xs z-50 hidden items-center justify-center p-4" onclick="closeProofModal()">
    <div class="bg-white rounded-2xl max-w-sm w-full p-6 space-y-4 shadow-2xl text-center" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <div>
                <h4 class="font-black text-gray-900 text-sm uppercase" id="proofModalTitle">Beneficiary Proof</h4>
                <span class="text-[10px] text-emerald-700 font-mono font-bold" id="proofModalSize">Stored Tiny Thumbnail &le; 2 KB</span>
            </div>
            <button type="button" onclick="closeProofModal()" class="text-gray-400 hover:text-gray-700 font-black text-lg">&times;</button>
        </div>
        <div class="p-4 bg-gray-50 rounded-xl flex items-center justify-center">
            <img id="proofModalImg" src="" class="max-w-[200px] max-h-[200px] rounded-lg border border-gray-300 shadow-sm object-contain" alt="Proof Zoom">
        </div>
        <p class="text-[11px] text-gray-500 leading-relaxed">
            Ultra-compressed tiny identification thumbnail strictly verified under 2048 bytes.
        </p>
        <button type="button" onclick="closeProofModal()" class="w-full bg-gray-900 hover:bg-gray-800 text-white font-bold text-xs py-2.5 rounded-xl uppercase tracking-wider">
            Close
        </button>
    </div>
</div>

{{-- Admin Replace Proof Modal --}}
<div id="replaceProofModal" class="fixed inset-0 bg-black/70 backdrop-blur-xs z-50 hidden items-center justify-center p-4" onclick="closeReplaceModal()">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <div>
                <h4 class="font-black text-gray-900 text-sm uppercase">Replace Proof Image</h4>
                <span class="text-[10px] text-emerald-700 font-mono font-bold">Strict &le; 2048 Bytes Compression Enforced</span>
            </div>
            <button type="button" onclick="closeReplaceModal()" class="text-gray-400 hover:text-gray-700 font-black text-lg">&times;</button>
        </div>

        <form id="replaceProofForm" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
            @csrf
            <div>
                <span class="block text-[10px] font-bold text-gray-400 uppercase">Target Membership ID</span>
                <span class="font-mono font-black text-gray-900 text-sm" id="replaceModalMemberId"></span>
            </div>

            <div>
                <label class="block font-black text-gray-700 uppercase mb-1">
                    Select Replacement Photo (Max 5 MB)
                </label>
                <input type="file" name="proof_image" accept="image/jpeg,image/png,image/webp,image/jpg" required class="w-full text-xs">
                <span class="text-[10px] text-gray-500 block mt-1">
                    Admin uploads pass through the same strict TinyProofImageService compression pipeline and must compress below 2 KB.
                </span>
            </div>

            <div class="pt-3 border-t border-gray-100 flex justify-end gap-2">
                <button type="button" onclick="closeReplaceModal()" class="px-4 py-2 rounded-lg text-gray-600 bg-gray-100 font-bold uppercase tracking-wider text-[10px]">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 rounded-lg text-white bg-brandOrange hover:bg-orange-600 font-black uppercase tracking-wider text-[10px]">
                    Compress &amp; Save
                </button>
            </div>
        </form>
    </div>
</div>

<script>
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

function openReplaceProofModal(memberLinkId, membershipId) {
    document.getElementById('replaceModalMemberId').innerText = membershipId;
    document.getElementById('replaceProofForm').action = '/admin/volunteer-events/{{ $event->id }}/members/' + memberLinkId + '/replace-proof';
    const modal = document.getElementById('replaceProofModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeReplaceModal() {
    const modal = document.getElementById('replaceProofModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>
@endsection
