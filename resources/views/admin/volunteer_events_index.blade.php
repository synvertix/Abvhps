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
                <span class="text-sm font-black text-brandGray uppercase tracking-wider">Volunteer Wing:</span>
                <span class="bg-orange-100 text-brandOrange text-[9px] font-black px-2.5 py-0.5 rounded border border-orange-200 tracking-widest uppercase shadow-sm">
                    Events &amp; Beneficiary Ledger
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

            <!-- Header Title and Metrics -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h3 class="text-sm font-black text-brandGray uppercase tracking-wider flex items-center gap-2">
                        <span>🏆</span> Volunteer Events &amp; Service Activities Roster
                    </h3>
                    <p class="text-[11px] text-gray-500 font-semibold mt-0.5">
                        Central registry of all grassroots seva programs, upcoming initiatives, and beneficiary records
                    </p>
                </div>
            </div>

            <!-- Statistics Overview Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-wider block">Total Events</span>
                    <div class="font-mono text-2xl font-black text-gray-900 mt-1">{{ $stats['total_events'] }}</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-emerald-200 shadow-sm">
                    <span class="text-[9px] font-black text-emerald-700 uppercase tracking-wider block">Conducted</span>
                    <div class="font-mono text-2xl font-black text-emerald-600 mt-1">{{ $stats['conducted_events'] }}</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-blue-200 shadow-sm">
                    <span class="text-[9px] font-black text-blue-700 uppercase tracking-wider block">Upcoming</span>
                    <div class="font-mono text-2xl font-black text-blue-600 mt-1">{{ $stats['upcoming_events'] }}</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-amber-200 shadow-sm">
                    <span class="text-[9px] font-black text-amber-700 uppercase tracking-wider block">Total Participants</span>
                    <div class="font-mono text-2xl font-black text-amber-600 mt-1">{{ $stats['total_participants'] }}</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-orange-200 shadow-sm col-span-2 sm:col-span-1">
                    <span class="text-[9px] font-black text-brandOrange uppercase tracking-wider block">Beneficiaries</span>
                    <div class="font-mono text-2xl font-black text-brandOrange mt-1">{{ $stats['total_beneficiaries'] }}</div>
                </div>
            </div>

            <!-- Filter and Search Bar -->
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                <form action="{{ route('admin.volunteer_events.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1">
                        <input type="text" name="search" value="{{ $search }}"
                               placeholder="Search by event title, location, volunteer name or ID..."
                               class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs font-semibold focus:border-brandOrange">
                    </div>

                    <div>
                        <select name="status" class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs font-semibold bg-white">
                            <option value="">-- All Statuses --</option>
                            <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="upcoming" {{ $status === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                            <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <button type="submit" class="bg-brandOrange hover:bg-orange-600 text-white text-xs font-black px-5 py-2.5 rounded-lg uppercase tracking-wider transition cursor-pointer">
                        Filter
                    </button>
                    @if($search || $status || $volunteerFilter)
                        <a href="{{ route('admin.volunteer_events.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold px-4 py-2.5 rounded-lg uppercase tracking-wider transition flex items-center justify-center">
                            Reset
                        </a>
                    @endif
                </form>
            </div>

            <!-- Events Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                @if($events->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="bg-gray-50 text-gray-600 uppercase text-[10px] font-black tracking-wider border-b border-gray-200">
                                    <th class="py-3.5 px-4">#</th>
                                    <th class="py-3.5 px-4">Event Details</th>
                                    <th class="py-3.5 px-4">Organizer Volunteer</th>
                                    <th class="py-3.5 px-4">Date &amp; Location</th>
                                    <th class="py-3.5 px-4 text-center">Status</th>
                                    <th class="py-3.5 px-4 text-center">Participants</th>
                                    <th class="py-3.5 px-4 text-center">Beneficiaries</th>
                                    <th class="py-3.5 px-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($events as $index => $event)
                                    <tr class="hover:bg-orange-50/40 transition">
                                        <td class="py-3.5 px-4 font-bold text-gray-400">
                                            {{ $events->firstItem() + $index }}
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <a href="{{ route('admin.volunteer_events.show', $event->id) }}" class="font-black text-gray-900 hover:text-brandOrange uppercase block text-xs">
                                                {{ $event->title }}
                                            </a>
                                            <span class="text-[10px] text-brandOrange font-bold uppercase tracking-wider">
                                                {{ $event->event_type }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <div class="font-bold text-gray-900 uppercase">
                                                {{ $event->volunteer?->full_name ?? 'Volunteer' }}
                                            </div>
                                            <div class="font-mono text-[11px] text-brandOrange font-bold">
                                                ID: {{ $event->volunteer?->volunteer_id ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <div class="font-bold text-gray-800">
                                                🗓️ {{ $event->event_date->format('d M Y') }}
                                            </div>
                                            <div class="text-[10px] text-gray-500 truncate max-w-xs font-medium">
                                                📍 {{ implode(', ', array_filter([$event->venue, $event->village, $event->mandal, $event->district])) ?: '—' }}
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider border {{ $event->status_badge_class }}">
                                                {{ strtoupper($event->status) }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-center font-mono font-bold text-gray-900">
                                            {{ $event->participants_count }}
                                        </td>
                                        <td class="py-3.5 px-4 text-center font-mono font-black text-brandOrange">
                                            {{ $event->beneficiaries_count }}
                                        </td>
                                        <td class="py-3.5 px-4 text-right">
                                            <div class="inline-flex items-center gap-1.5">
                                                <a href="{{ route('admin.volunteer_events.show', $event->id) }}"
                                                   class="bg-gray-800 hover:bg-gray-900 text-white font-black text-[10px] px-3 py-1.5 rounded-lg uppercase tracking-wider transition">
                                                    View Dossier &rarr;
                                                </a>
                                                <form action="{{ route('admin.volunteer_events.delete', $event->id) }}" method="POST"
                                                      onsubmit="return confirm('Permanently delete event: {{ addslashes($event->title) }}?')" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition cursor-pointer" title="Delete Event">
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

                    <div class="p-4 border-t border-gray-100">
                        {{ $events->links() }}
                    </div>
                @else
                    <div class="p-12 text-center text-gray-400 space-y-2">
                        <span class="text-3xl block">🪔</span>
                        <span class="text-xs font-bold uppercase block text-gray-700">No Volunteer Events Found</span>
                        <p class="text-[11px] text-gray-500">No events matched your current search filters.</p>
                    </div>
                @endif
            </div>

        </main>
    </div>
</div>
@endsection
