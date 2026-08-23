@extends('layouts.app')

@section('title', $unitName . ' ' . $unitType . ' Detail | ABVHPS Volunteer Hierarchy')

@section('content')
<div class="bg-gray-50 min-h-screen pb-16">

    {{-- Official Header Banner --}}
    <div class="bg-gradient-to-r from-orange-900 via-orange-800 to-amber-900 text-white py-8 px-4 shadow-md border-b-4 border-yellow-500">
        <div class="max-w-6xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
            <div>
                <span class="bg-yellow-400 text-orange-950 text-[10px] font-black px-3.5 py-1 rounded-full uppercase tracking-widest inline-block shadow mb-2">
                    {{ $unitType }} Jurisdictional View
                </span>
                <h1 class="text-2xl md:text-3xl font-black uppercase tracking-wide text-white">
                    {{ $unitName }}
                </h1>
                <p class="text-xs text-yellow-200/90 font-medium mt-0.5">
                    {{ $jurisdictionSummary }} &bull; ABVHPS Cadre Operations
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

        {{-- Unit Leadership Card --}}
        <div class="bg-white p-6 rounded-3xl border border-gray-200 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-orange-100 text-brandOrange flex items-center justify-center font-black text-xl border border-orange-200 shadow-xs">
                    {{ substr($president?->full_name ?? $unitName, 0, 1) }}
                </div>
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 block">{{ $unitType }} President</span>
                    <h2 class="text-lg font-black text-gray-900 uppercase">
                        {{ $president?->full_name ?? 'Not Assigned' }}
                    </h2>
                    <div class="flex items-center gap-2 text-xs text-gray-600 mt-0.5">
                        <span>Contact:</span>
                        <strong class="font-mono text-gray-900">{{ $president?->phone ?? '—' }}</strong>
                        @if($president)
                            <span>&bull;</span>
                            <span>Vol ID: <strong class="font-mono text-orange-700">{{ $president->volunteer_id }}</strong></span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="text-left sm:text-right">
                <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 block">Assignment Status</span>
                <span class="inline-block mt-1 px-3 py-1 text-xs font-black rounded-full uppercase tracking-wider {{ $president ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-gray-100 text-gray-600 border border-gray-300' }}">
                    {{ $president ? 'Active Clearance' : 'Vacant' }}
                </span>
            </div>
        </div>

        {{-- Summary Statistics Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
            {{-- Registered Members --}}
            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-xs">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Registered Members</span>
                <div class="text-2xl font-black text-gray-900 mt-1">
                    {{ number_format($stats['members_count'] ?? 0) }}
                </div>
                <span class="text-[9px] text-gray-500 block mt-0.5">Canonical memberships</span>
            </div>

            {{-- Approved Volunteers --}}
            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-xs">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Volunteers</span>
                <div class="text-2xl font-black text-orange-600 mt-1">
                    {{ number_format($stats['volunteers_count'] ?? 0) }}
                </div>
                <span class="text-[9px] text-gray-500 block mt-0.5">Active cadre</span>
            </div>

            {{-- Subordinate Units --}}
            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-xs">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Subordinate Units</span>
                <div class="text-2xl font-black text-brandOrange mt-1">
                    {{ number_format($subordinates->count()) }}
                </div>
                <span class="text-[9px] text-gray-500 block mt-0.5">Lower territories</span>
            </div>

            {{-- Total Events --}}
            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-xs">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Total Events</span>
                <div class="text-2xl font-black text-blue-700 mt-1">
                    {{ number_format($stats['events_count'] ?? 0) }}
                </div>
                <span class="text-[9px] text-gray-500 block mt-0.5">Service activities</span>
            </div>

            {{-- Participants --}}
            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-xs">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Participants</span>
                <div class="text-2xl font-black text-purple-700 mt-1">
                    {{ number_format($stats['participants_count'] ?? 0) }}
                </div>
                <span class="text-[9px] text-gray-500 block mt-0.5">Attended members</span>
            </div>

            {{-- Beneficiaries --}}
            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-xs">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Beneficiaries</span>
                <div class="text-2xl font-black text-emerald-700 mt-1">
                    {{ number_format($stats['beneficiaries_count'] ?? 0) }}
                </div>
                <span class="text-[9px] text-gray-500 block mt-0.5">Seva recipients</span>
            </div>
        </div>

        {{-- Subordinate Hierarchy Drill-down Table --}}
        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="font-black text-gray-900 text-sm uppercase tracking-wide flex items-center gap-2">
                    <span>📋</span> {{ $childUnitLabel }}
                </h3>
                <span class="text-[10px] bg-orange-100 text-brandOrange font-bold px-2.5 py-0.5 rounded-full uppercase">
                    Click unit name to drill down
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs text-gray-700">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 font-black uppercase text-[10px] tracking-wider">
                            <th class="p-3">Territory Name</th>
                            <th class="p-3">President Name</th>
                            <th class="p-3">Contact</th>
                            <th class="p-3 text-center">Members</th>
                            <th class="p-3 text-center">Events</th>
                            <th class="p-3 text-center">Beneficiaries</th>
                            <th class="p-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($subordinates as $sub)
                            <tr class="hover:bg-orange-50/40 font-medium transition">
                                <td class="p-3">
                                    <a href="{{ $sub['detail_route'] }}"
                                       class="font-black text-brandOrange hover:text-orange-700 hover:underline uppercase inline-flex items-center gap-1">
                                        <span>{{ $sub['unit_name'] }}</span>
                                        <span class="text-xs">&rarr;</span>
                                    </a>
                                </td>
                                <td class="p-3 font-bold uppercase {{ $sub['is_assigned'] ? 'text-gray-900' : 'text-gray-400 italic' }}">
                                    {{ $sub['president_name'] }}
                                </td>
                                <td class="p-3 font-mono font-bold text-gray-800">{{ $sub['contact_phone'] }}</td>
                                <td class="p-3 text-center font-bold text-gray-900">{{ number_format($sub['members_count'] ?? 0) }}</td>
                                <td class="p-3 text-center font-bold text-gray-900">{{ number_format($sub['events_count'] ?? 0) }}</td>
                                <td class="p-3 text-center font-bold text-emerald-700">{{ number_format($sub['beneficiaries_count'] ?? 0) }}</td>
                                <td class="p-3 text-center">
                                    <span class="inline-block px-2.5 py-0.5 text-[9px] font-black rounded-full uppercase {{ $sub['is_assigned'] ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-500' }}">
                                        {{ $sub['is_assigned'] ? 'Active' : 'Vacant' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-6 text-center text-gray-400 font-medium">
                                    No subordinate units configured under this territory yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection
