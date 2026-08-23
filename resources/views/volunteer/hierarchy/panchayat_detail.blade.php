@extends('layouts.app')

@section('title', $panchayat->name . ' Grama Panchayat Detail | ABVHPS Volunteer Hierarchy')

@section('content')
<div class="bg-gray-50 min-h-screen pb-16">

    {{-- Official Header Banner --}}
    <div class="bg-gradient-to-r from-orange-900 via-orange-800 to-amber-900 text-white py-8 px-4 shadow-md border-b-4 border-yellow-500">
        <div class="max-w-6xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
            <div>
                <span class="bg-yellow-400 text-orange-950 text-[10px] font-black px-3.5 py-1 rounded-full uppercase tracking-widest inline-block shadow mb-2">
                    Grama Panchayat Operational View
                </span>
                <h1 class="text-2xl md:text-3xl font-black uppercase tracking-wide text-white">
                    {{ $panchayat->name }}
                </h1>
                <p class="text-xs text-yellow-200/90 font-medium mt-0.5">
                    {{ $panchayat->mandal?->name ?? 'Mandal' }}, {{ $panchayat->mandal?->assemblySegment?->name ?? 'Assembly' }}, {{ $panchayat->mandal?->district?->name ?? 'District' }} &bull; ABVHPS Cadre Operations
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ $backUrl }}"
                   class="bg-white/20 hover:bg-white/30 text-white text-xs font-black px-4 py-2 rounded-xl shadow uppercase tracking-wider transition inline-flex items-center gap-1.5 min-h-[44px]">
                    <span>&larr;</span> Back
                </a>
                <a href="{{ route('volunteer.dashboard') }}"
                   class="bg-brandOrange hover:bg-orange-600 text-white text-xs font-black px-4 py-2 rounded-xl shadow uppercase tracking-wider transition inline-flex items-center min-h-[44px]">
                    Main Dashboard
                </a>
            </div>
        </div>
    </div>

    {{-- Main Container --}}
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-6 space-y-6">

        {{-- Breadcrumb Navigation Bar --}}
        <div class="flex items-center gap-2 text-xs font-bold text-gray-500 bg-white p-3 rounded-2xl border border-gray-200 shadow-xs flex-wrap">
            @foreach($breadcrumbs as $index => $bc)
                @if($index > 0)
                    <span class="text-gray-300">&gt;</span>
                @endif
                @if(!empty($bc['url']))
                    <a href="{{ $bc['url'] }}" class="hover:text-brandOrange transition text-gray-600">{{ $bc['title'] }}</a>
                @else
                    <span class="{{ !empty($bc['active']) ? 'text-brandOrange font-black' : 'text-gray-400' }}">{{ $bc['title'] }}</span>
                @endif
            @endforeach
        </div>

        {{-- Panchayat Overview & Leadership Card --}}
        <div class="bg-white p-6 rounded-3xl border border-gray-200 shadow-sm grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left: President Details --}}
            <div class="lg:col-span-2 flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-orange-100 text-brandOrange flex items-center justify-center font-black text-xl border border-orange-200 shadow-xs shrink-0">
                    {{ substr($president?->full_name ?? $panchayat->name, 0, 1) }}
                </div>
                <div class="space-y-1">
                    <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 block">Panchayat President</span>
                    <h2 class="text-lg font-black text-gray-900 uppercase">
                        {{ $president?->full_name ?? 'Not Assigned' }}
                    </h2>
                    <div class="flex flex-wrap items-center gap-3 text-xs text-gray-600">
                        <span>Contact: <strong class="font-mono text-gray-900">{{ $president?->phone ?? '—' }}</strong></span>
                        @if($president)
                            <span>&bull;</span>
                            <span>Vol ID: <strong class="font-mono text-orange-700">{{ $president->volunteer_id }}</strong></span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right: Canonical Territorial Parentage --}}
            <div class="p-4 bg-gray-50 rounded-2xl border border-gray-200 text-xs space-y-1 text-gray-700">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block mb-1">Territorial Scope</span>
                <div><span class="text-gray-400">Mandal:</span> <strong class="uppercase text-gray-900">{{ $panchayat->mandal?->name ?? '—' }}</strong></div>
                <div><span class="text-gray-400">Assembly:</span> <strong class="uppercase text-gray-900">{{ $panchayat->mandal?->assemblySegment?->name ?? '—' }}</strong></div>
                <div><span class="text-gray-400">District:</span> <strong class="uppercase text-gray-900">{{ $panchayat->mandal?->district?->name ?? '—' }}</strong></div>
                <div><span class="text-gray-400">State:</span> <strong class="uppercase text-gray-900">{{ $panchayat->mandal?->district?->state?->name ?? '—' }}</strong></div>
            </div>
        </div>

        {{-- Summary Statistics Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-xs">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Registered Members</span>
                <div class="text-2xl font-black text-gray-900 mt-1">
                    {{ number_format($stats['members_count'] ?? 0) }}
                </div>
                <span class="text-[9px] text-gray-500 block mt-0.5">Panchayat resident members</span>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-xs">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Volunteers</span>
                <div class="text-2xl font-black text-orange-600 mt-1">
                    {{ number_format($stats['volunteers_count'] ?? 0) }}
                </div>
                <span class="text-[9px] text-gray-500 block mt-0.5">Approved &amp; active</span>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-xs">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Total Events</span>
                <div class="text-2xl font-black text-blue-700 mt-1">
                    {{ number_format($stats['events_count'] ?? 0) }}
                </div>
                <span class="text-[9px] text-gray-500 block mt-0.5">Seva initiatives</span>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-xs">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Upcoming Events</span>
                <div class="text-2xl font-black text-amber-600 mt-1">
                    {{ number_format($stats['upcoming_events_count'] ?? 0) }}
                </div>
                <span class="text-[9px] text-gray-500 block mt-0.5">Scheduled seva</span>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-xs">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Participants</span>
                <div class="text-2xl font-black text-purple-700 mt-1">
                    {{ number_format($stats['participants_count'] ?? 0) }}
                </div>
                <span class="text-[9px] text-gray-500 block mt-0.5">Attended members</span>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-xs">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Beneficiaries</span>
                <div class="text-2xl font-black text-emerald-700 mt-1">
                    {{ number_format($stats['beneficiaries_count'] ?? 0) }}
                </div>
                <span class="text-[9px] text-gray-500 block mt-0.5">Seva recipients</span>
            </div>
        </div>

        {{-- Section: Registered Members Table --}}
        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 flex-wrap gap-2">
                <h3 class="font-black text-gray-900 text-sm uppercase tracking-wide flex items-center gap-2">
                    <span>👥</span> Registered Members ({{ number_format($stats['members_count'] ?? 0) }})
                </h3>
                <span class="text-[10px] bg-gray-100 text-gray-600 font-bold px-2.5 py-0.5 rounded-full uppercase">
                    Showing {{ $members->firstItem() ?? 0 }}-{{ $members->lastItem() ?? 0 }} of {{ number_format($members->total()) }}
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs text-gray-700">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 font-black uppercase text-[10px] tracking-wider">
                            <th class="p-3">Membership ID</th>
                            <th class="p-3">Member Name</th>
                            <th class="p-3">Panchayat</th>
                            <th class="p-3">Mandal</th>
                            <th class="p-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($members as $m)
                            <tr class="hover:bg-gray-50 font-medium">
                                <td class="p-3 font-mono font-bold text-gray-900">{{ implode(' ', str_split($m->membership_id, 4)) }}</td>
                                <td class="p-3 font-bold text-gray-900 uppercase">{{ $m->full_name }}</td>
                                <td class="p-3 uppercase text-gray-600">{{ $m->grama_panchayat ?? $panchayat->name }}</td>
                                <td class="p-3 uppercase text-gray-600">{{ $m->mandal ?? ($panchayat->mandal?->name ?? '—') }}</td>
                                <td class="p-3 text-center">
                                    <span class="inline-block px-2.5 py-0.5 text-[9px] font-black rounded-full uppercase {{ $m->payment_status === 'success' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $m->payment_status === 'success' ? 'Active Member' : 'Pending' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-6 text-center text-gray-400 font-medium">
                                    No registered members found for this Grama Panchayat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($members->hasPages())
                <div class="pt-2 border-t border-gray-100">
                    {{ $members->links() }}
                </div>
            @endif
        </div>

        {{-- Section: Volunteers Table --}}
        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 flex-wrap gap-2">
                <h3 class="font-black text-gray-900 text-sm uppercase tracking-wide flex items-center gap-2">
                    <span>🤝</span> Approved Volunteers ({{ number_format($stats['volunteers_count'] ?? 0) }})
                </h3>
                <span class="text-[10px] bg-orange-100 text-brandOrange font-bold px-2.5 py-0.5 rounded-full uppercase">
                    Showing {{ $volunteers->firstItem() ?? 0 }}-{{ $volunteers->lastItem() ?? 0 }} of {{ number_format($volunteers->total()) }}
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs text-gray-700">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 font-black uppercase text-[10px] tracking-wider">
                            <th class="p-3">Volunteer ID</th>
                            <th class="p-3">Volunteer Name</th>
                            <th class="p-3">Cadre / Designation</th>
                            <th class="p-3">Contact</th>
                            <th class="p-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($volunteers as $v)
                            <tr class="hover:bg-gray-50 font-medium">
                                <td class="p-3 font-mono font-black text-orange-700">{{ $v->volunteer_id }}</td>
                                <td class="p-3 font-bold text-gray-900 uppercase">{{ $v->full_name }}</td>
                                <td class="p-3 text-gray-700 font-bold uppercase">{{ $v->cadre_label }}</td>
                                <td class="p-3 font-mono font-bold text-gray-800">{{ $v->phone }}</td>
                                <td class="p-3 text-center">
                                    <span class="inline-block px-2.5 py-0.5 text-[9px] font-black rounded-full uppercase bg-emerald-100 text-emerald-800">
                                        Approved &amp; Active
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-6 text-center text-gray-400 font-medium">
                                    No volunteers assigned to this Grama Panchayat yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($volunteers->hasPages())
                <div class="pt-2 border-t border-gray-100">
                    {{ $volunteers->links() }}
                </div>
            @endif
        </div>

        {{-- Section: Events Table --}}
        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 flex-wrap gap-2">
                <h3 class="font-black text-gray-900 text-sm uppercase tracking-wide flex items-center gap-2">
                    <span>🏆</span> Service Activities &amp; Events ({{ number_format($stats['events_count'] ?? 0) }})
                </h3>
                <span class="text-[10px] bg-blue-100 text-blue-800 font-bold px-2.5 py-0.5 rounded-full uppercase">
                    Showing {{ $events->firstItem() ?? 0 }}-{{ $events->lastItem() ?? 0 }} of {{ number_format($events->total()) }}
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs text-gray-700">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 font-black uppercase text-[10px] tracking-wider">
                            <th class="p-3">Event Title</th>
                            <th class="p-3">Event Type</th>
                            <th class="p-3">Event Date</th>
                            <th class="p-3">Venue / Location</th>
                            <th class="p-3 text-center">Participants</th>
                            <th class="p-3 text-center">Beneficiaries</th>
                            <th class="p-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($events as $e)
                            <tr class="hover:bg-gray-50 font-medium">
                                <td class="p-3 font-bold text-gray-900">
                                    <a href="{{ route('volunteer.events.show', $e->id) }}" class="text-brandOrange hover:underline">
                                        {{ $e->title }}
                                    </a>
                                </td>
                                <td class="p-3 text-gray-600 font-medium">{{ $e->event_type }}</td>
                                <td class="p-3 text-gray-600 font-mono">{{ $e->event_date?->format('d-M-Y') ?? '—' }}</td>
                                <td class="p-3 text-gray-600 truncate max-w-xs">{{ $e->venue ?: '—' }}</td>
                                <td class="p-3 text-center font-bold text-purple-700">{{ $e->participants_count }}</td>
                                <td class="p-3 text-center font-bold text-emerald-700">{{ $e->beneficiaries_count }}</td>
                                <td class="p-3 text-center">
                                    <span class="inline-block px-2.5 py-0.5 text-[9px] font-black rounded-full uppercase {{ $e->status_badge_class }}">
                                        {{ $e->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-6 text-center text-gray-400 font-medium">
                                    No events recorded in this Panchayat yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($events->hasPages())
                <div class="pt-2 border-t border-gray-100">
                    {{ $events->links() }}
                </div>
            @endif
        </div>

    </div>

</div>
@endsection
