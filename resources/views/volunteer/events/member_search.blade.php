@extends('layouts.app')

@section('title', 'Member Search Desk | ABVHPS Volunteer Portal')

@section('content')
<div class="bg-gray-50 min-h-screen pb-16">

    {{-- Header Banner --}}
    <div class="bg-gradient-to-r from-orange-900 via-orange-800 to-amber-900 text-white py-10 px-4 shadow-md border-b-4 border-yellow-500">
        <div class="max-w-4xl mx-auto flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <span class="bg-yellow-400 text-orange-950 text-[10px] font-black px-3.5 py-1 rounded-full uppercase tracking-widest inline-block shadow mb-2">
                    Volunteer Tools &bull; Registry Lookup
                </span>
                <h1 class="text-2xl md:text-3xl font-black uppercase tracking-wide text-white">
                    Search Member by Membership ID
                </h1>
                <p class="text-xs text-yellow-200/90 font-medium mt-0.5">
                    Exact Membership ID lookup with minimal privacy-safe profile verification
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('volunteer.events.index') }}"
                   class="bg-white/15 hover:bg-white/25 border border-white/20 text-white text-xs font-bold px-4 py-2 rounded-xl uppercase tracking-wider transition min-h-[44px] inline-flex items-center">
                    &larr; Events
                </a>
                <a href="{{ route('volunteer.dashboard') }}"
                   class="bg-white/15 hover:bg-white/25 border border-white/20 text-white text-xs font-bold px-4 py-2 rounded-xl uppercase tracking-wider transition min-h-[44px] inline-flex items-center">
                    Dashboard
                </a>
            </div>
        </div>
    </div>

    {{-- Main Container --}}
    <div class="max-w-4xl mx-auto px-4 sm:px-6 mt-8 space-y-6">

        {{-- Privacy Notice Alert --}}
        <div class="p-4 bg-orange-50/80 border border-orange-200 text-orange-950 rounded-2xl text-xs space-y-1">
            <div class="font-black uppercase tracking-wide flex items-center gap-1.5 text-brandOrange">
                <span>🛡️</span> Privacy &amp; Data Security Notice
            </div>
            <p class="text-[11px] text-orange-900/80 leading-relaxed font-medium">
                This search desk enforces exact Membership ID lookups. In accordance with privacy and security policies, sensitive information (such as phone numbers, emails, government IDs, and payment details) is strictly hidden.
            </p>
        </div>

        {{-- Search Card --}}
        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 sm:p-8 space-y-6">
            <div>
                <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">
                    Enter Exact 12-Digit Membership ID <span class="text-rose-500">*</span>
                </label>
                <div class="flex flex-col sm:flex-row gap-3">
                    <input type="text" id="exactSearchInput" maxlength="12"
                           placeholder="e.g. 688688688688"
                           class="flex-1 px-4 py-3.5 rounded-xl border border-gray-300 focus:border-brandOrange focus:ring-2 focus:ring-orange-200 font-mono text-base font-black tracking-wider uppercase transition">
                    <button type="button" id="exactSearchBtn" onclick="runExactSearch()"
                            class="bg-brandOrange hover:bg-orange-600 text-white text-xs font-black px-8 py-3.5 rounded-xl shadow-md uppercase tracking-wider transition cursor-pointer flex items-center justify-center gap-2">
                        <span id="searchSpinner" class="hidden animate-spin">⏳</span>
                        <span>Search Member</span>
                    </button>
                </div>
            </div>

            {{-- Dynamic Search States --}}
            <div id="searchOutput" class="pt-4 border-t border-gray-100">
                <div class="py-8 text-center text-gray-400 space-y-2">
                    <span class="text-3xl block">🔍</span>
                    <span class="text-xs font-bold uppercase tracking-wider block">Ready for Search</span>
                    <p class="text-[11px] text-gray-400 max-w-sm mx-auto">
                        Type the complete 12-digit Membership ID above to check active status and link to your events.
                    </p>
                </div>
            </div>
        </div>

        {{-- Recent Events Reference --}}
        @if($myEvents->count() > 0)
            <div class="bg-white rounded-3xl border border-gray-200 p-6 space-y-3">
                <h3 class="font-black text-gray-800 text-xs uppercase tracking-wider flex items-center gap-2">
                    <span>📅</span> Your Active Events for Quick Linking
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    @foreach($myEvents->take(4) as $ev)
                        <a href="{{ route('volunteer.events.show', $ev->id) }}"
                           class="p-3 bg-gray-50 hover:bg-orange-50/50 rounded-xl border border-gray-200 transition flex items-center justify-between">
                            <div>
                                <span class="font-bold text-gray-900 block truncate">{{ $ev->title }}</span>
                                <span class="text-[10px] text-gray-500">{{ $ev->event_date->format('d M Y') }} &bull; {{ ucfirst($ev->status) }}</span>
                            </div>
                            <span class="text-brandOrange font-bold">&rarr;</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

    </div>

</div>

<script>
const myEvents = @json($myEvents);

function runExactSearch() {
    const input = document.getElementById('exactSearchInput');
    const rawVal = input.value.trim();
    const output = document.getElementById('searchOutput');
    const spinner = document.getElementById('searchSpinner');

    if (!rawVal) {
        output.innerHTML = `
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs font-bold text-center">
                Please enter a 12-digit Membership ID.
            </div>
        `;
        return;
    }

    spinner.classList.remove('hidden');
    output.innerHTML = `
        <div class="py-8 text-center text-gray-500 text-xs font-bold">
            Verifying server database records...
        </div>
    `;

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
            let eventsOptions = '<option value="">-- Select one of your events --</option>';
            myEvents.forEach(ev => {
                eventsOptions += `<option value="${ev.id}">${ev.title} (${ev.event_date.substring(0, 10)})</option>`;
            });

            output.innerHTML = `
                <div class="bg-emerald-50/70 border-2 border-emerald-200 rounded-2xl p-6 space-y-4">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-emerald-200 pb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-xl font-bold shadow-xs">
                                ✓
                            </div>
                            <div>
                                <span class="text-[10px] bg-emerald-600 text-white font-black px-2 py-0.5 rounded uppercase">
                                    Member Found
                                </span>
                                <h3 class="font-black text-gray-900 text-lg uppercase mt-0.5">
                                    ${m.full_name}
                                </h3>
                                <span class="font-mono text-xs font-black text-brandOrange tracking-wider">
                                    ID: ${m.membership_id}
                                </span>
                            </div>
                        </div>

                        <div class="text-left sm:text-right text-xs">
                            <span class="text-gray-500 font-medium block">Status: <strong class="text-emerald-700">Active</strong></span>
                            <span class="text-gray-500 font-medium block">Region: <strong>${m.district}, ${m.state}</strong></span>
                        </div>
                    </div>

                    ${myEvents.length > 0 ? `
                        <div class="bg-white p-4 rounded-xl border border-emerald-200 space-y-3">
                            <h4 class="font-black text-gray-800 text-xs uppercase tracking-wide">
                                ➕ Quick Link to an Event
                            </h4>
                            <div class="flex flex-col sm:flex-row gap-2">
                                <select id="targetEventSelect" class="flex-1 px-3 py-2 rounded-xl border border-gray-300 text-xs font-semibold bg-white">
                                    ${eventsOptions}
                                </select>
                                <button type="button" onclick="goToEventAdd('${m.membership_id}')"
                                        class="bg-brandOrange hover:bg-orange-600 text-white font-black text-xs px-5 py-2.5 rounded-xl uppercase tracking-wider transition cursor-pointer">
                                    Add to Event &rarr;
                                </button>
                            </div>
                        </div>
                    ` : `
                        <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-900 font-medium">
                            You have no events created yet. <a href="{{ route('volunteer.events.create') }}" class="font-black text-brandOrange underline">Create an event</a> to link this member.
                        </div>
                    `}
                </div>
            `;
        } else {
            output.innerHTML = `
                <div class="p-6 bg-rose-50 border border-rose-200 rounded-2xl text-center space-y-2">
                    <span class="text-2xl block">✕</span>
                    <h4 class="font-black text-rose-900 text-sm uppercase">Member Not Found</h4>
                    <p class="text-xs text-rose-700 font-medium max-w-sm mx-auto">
                        No active registered member matched the Membership ID: <strong class="font-mono">${rawVal}</strong>. Please verify the ID number.
                    </p>
                </div>
            `;
        }
    })
    .catch(err => {
        spinner.classList.add('hidden');
        output.innerHTML = `
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs font-bold text-center">
                Search service temporarily unavailable. Please try again.
            </div>
        `;
    });
}

function goToEventAdd(membershipId) {
    const sel = document.getElementById('targetEventSelect');
    if (!sel || !sel.value) {
        alert('Please select an event from the dropdown.');
        return;
    }
    window.location.href = '/volunteer/events/' + sel.value + '#add-member-section';
}
</script>
@endsection
