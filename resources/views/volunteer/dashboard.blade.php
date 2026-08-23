@extends('layouts.app')

@section('title', 'Volunteer Portal Dashboard | ABVHPS')

@section('content')
<div class="bg-gray-50 min-h-screen pb-16">

    {{-- Official Header Banner --}}
    <div class="bg-gradient-to-r from-orange-900 via-orange-800 to-amber-900 text-white py-10 px-4 shadow-md border-b-4 border-yellow-500">
        <div class="max-w-6xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
            <div>
                <span class="bg-yellow-400 text-orange-950 text-[10px] font-black px-3.5 py-1 rounded-full uppercase tracking-widest inline-block shadow mb-2">
                    Verified Volunteer Portal
                </span>
                <h1 class="text-2xl md:text-3xl font-black uppercase tracking-wide text-white">
                    Namaste, {{ $volunteer->full_name }}
                </h1>
                <p class="text-xs text-yellow-200/90 font-medium mt-0.5">
                    Akhanda Bharatha Viswa Hindu Parirakshana Samiti &mdash; Cadre Operations Desk
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                <a href="{{ route('volunteer.change_password') }}"
                   class="bg-white/15 hover:bg-white/25 border border-white/20 text-white text-xs font-bold px-4 py-2 rounded-xl uppercase tracking-wider transition min-h-[44px] inline-flex items-center">
                    Change Password
                </a>
                <form action="{{ route('volunteer.logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                            class="bg-rose-600 hover:bg-rose-700 text-white text-xs font-black px-4 py-2 rounded-xl shadow uppercase tracking-wider transition cursor-pointer min-h-[44px] inline-flex items-center">
                        Sign Out
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Main Dashboard Body --}}
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 space-y-6">

        {{-- Flash Alerts --}}
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-2xl text-xs font-bold shadow-sm flex items-center justify-between">
                <span>✓ {{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" class="text-emerald-600 font-black">×</button>
            </div>
        @endif

        {{-- Metrics Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

            {{-- 6-Digit Official Volunteer ID --}}
            <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Official Volunteer ID</span>
                <div class="font-mono text-2xl font-black text-orange-600 mt-1 tracking-widest">
                    {{ $volunteer->volunteer_id ?? ($volunteer->volunteer_login_id ?? 'Pending') }}
                </div>
                <span class="text-[10px] text-gray-500 mt-0.5 block">Unique 6-digit numeric ID</span>
            </div>

            {{-- 12-Digit Membership ID --}}
            <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Membership ID</span>
                <div class="font-mono text-xl font-black text-gray-900 mt-1 tracking-wider">
                    {{ implode(' ', str_split($volunteer->membership_id, 4)) }}
                </div>
                <span class="text-[10px] text-gray-500 mt-0.5 block">Central master registry</span>
            </div>

            {{-- Cadre / Designation --}}
            <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Assigned Cadre</span>
                <div class="text-lg font-black text-brandOrange mt-1 uppercase truncate">
                    {{ $volunteer->cadre_label }}
                </div>
                <span class="text-[10px] text-gray-500 mt-0.5 block">Sanathana Dharma Wing</span>
            </div>

            {{-- Account Status --}}
            <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Account Status</span>
                <div class="text-lg font-black text-emerald-700 mt-1 uppercase flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Approved &amp; Active
                </div>
                <span class="text-[10px] text-emerald-600 mt-0.5 block">Central clearance verified</span>
            </div>

        </div>

        {{-- 6-Level President Jurisdictional Hierarchy Table (Directly on Main Dashboard) --}}
        @if($isPresident)
            <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 space-y-4">
                @if($volunteer->cadre_level === 'panchayat_president')
                    {{-- 1. Panchayat President: Panchayat Details Card/Table --}}
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <h3 class="font-black text-gray-800 text-sm uppercase tracking-wide flex items-center gap-2">
                            <span>🏛️</span> PANCHAYAT DETAILS
                        </h3>
                        <span class="text-[10px] bg-emerald-100 text-emerald-800 font-bold px-2.5 py-0.5 rounded-full uppercase">
                            Own Jurisdiction
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs text-gray-700">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 font-black uppercase text-[10px] tracking-wider">
                                    <th class="p-3">Panchayat Name</th>
                                    <th class="p-3">Panchayat President Name</th>
                                    <th class="p-3">Contact Number</th>
                                    <th class="p-3">Mandal</th>
                                    <th class="p-3">Assembly Segment</th>
                                    <th class="p-3">District</th>
                                    <th class="p-3">State</th>
                                    <th class="p-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subordinateUnits as $unit)
                                    <tr class="border-b border-gray-100 hover:bg-orange-50/40 font-medium transition">
                                        <td class="p-3 font-bold text-brandOrange uppercase">
                                            @if(!empty($unit['detail_route']) && $unit['detail_route'] !== '#')
                                                <a href="{{ $unit['detail_route'] }}" class="hover:underline flex items-center gap-1">
                                                    <span>{{ $unit['unit_name'] }}</span>
                                                    <span class="text-xs">&rarr;</span>
                                                </a>
                                            @else
                                                {{ $unit['unit_name'] }}
                                            @endif
                                        </td>
                                        <td class="p-3 font-bold text-gray-900 uppercase">{{ $unit['president_name'] }}</td>
                                        <td class="p-3 font-mono font-bold text-gray-800">{{ $unit['contact_phone'] }}</td>
                                        <td class="p-3 uppercase">{{ $unit['mandal_name'] ?? '—' }}</td>
                                        <td class="p-3 uppercase">{{ $unit['assembly_name'] ?? '—' }}</td>
                                        <td class="p-3 uppercase">{{ $unit['district_name'] ?? '—' }}</td>
                                        <td class="p-3 uppercase">{{ $unit['state_name'] ?? '—' }}</td>
                                        <td class="p-3 text-center">
                                            <span class="inline-block px-2.5 py-0.5 text-[9px] font-black rounded-full uppercase bg-emerald-100 text-emerald-800">
                                                {{ $unit['status'] ?? 'Active' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                @elseif($volunteer->cadre_level === 'mandal_president')
                    {{-- 2. Mandal President: Panchayats Under Your Mandal Table --}}
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <h3 class="font-black text-gray-800 text-sm uppercase tracking-wide flex items-center gap-2">
                            <span>🏘️</span> PANCHAYATS UNDER YOUR MANDAL
                        </h3>
                        <span class="text-[10px] bg-orange-100 text-brandOrange font-bold px-2.5 py-0.5 rounded-full uppercase">
                            Mandal Scope: {{ $volunteer->mandalRelation?->name ?? $volunteer->resolved_mandal }}
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs text-gray-700">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 font-black uppercase text-[10px] tracking-wider">
                                    <th class="p-3">Panchayat Name</th>
                                    <th class="p-3">Panchayat President Name</th>
                                    <th class="p-3">Contact Number</th>
                                    <th class="p-3 text-center">Members</th>
                                    <th class="p-3 text-center">Events</th>
                                    <th class="p-3 text-center">Beneficiaries</th>
                                    <th class="p-3 text-center">Status / Details</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($subordinateUnits as $unit)
                                    <tr class="hover:bg-orange-50/40 font-medium transition">
                                        <td class="p-3 font-bold text-brandOrange uppercase">
                                            <a href="{{ $unit['detail_route'] }}" class="hover:text-orange-700 hover:underline inline-flex items-center gap-1">
                                                <span>{{ $unit['unit_name'] }}</span>
                                                <span class="text-xs">&rarr;</span>
                                            </a>
                                        </td>
                                        <td class="p-3 font-bold uppercase {{ $unit['is_assigned'] ? 'text-gray-900' : 'text-gray-400 italic' }}">
                                            {{ $unit['president_name'] }}
                                        </td>
                                        <td class="p-3 font-mono font-bold text-gray-800">{{ $unit['contact_phone'] }}</td>
                                        <td class="p-3 text-center font-bold text-gray-900">{{ number_format($unit['members_count'] ?? 0) }}</td>
                                        <td class="p-3 text-center font-bold text-gray-900">{{ number_format($unit['events_count'] ?? 0) }}</td>
                                        <td class="p-3 text-center font-bold text-emerald-700">{{ number_format($unit['beneficiaries_count'] ?? 0) }}</td>
                                        <td class="p-3 text-center">
                                            <span class="inline-block px-2.5 py-0.5 text-[9px] font-black rounded-full uppercase {{ $unit['is_assigned'] ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-500' }}">
                                                {{ $unit['is_assigned'] ? 'Active' : 'Vacant' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="p-4 text-center text-gray-400 font-medium">No Panchayats configured under this Mandal yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                @elseif($volunteer->cadre_level === 'assembly_president')
                    {{-- 3. Assembly Segment President: Mandals Under Your Assembly Segment Table --}}
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <h3 class="font-black text-gray-800 text-sm uppercase tracking-wide flex items-center gap-2">
                            <span>🏛️</span> MANDALS UNDER YOUR ASSEMBLY SEGMENT
                        </h3>
                        <span class="text-[10px] bg-orange-100 text-brandOrange font-bold px-2.5 py-0.5 rounded-full uppercase">
                            Assembly Scope: {{ $volunteer->assemblySegmentRelation?->name ?? $volunteer->resolved_assembly_segment }}
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs text-gray-700">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 font-black uppercase text-[10px] tracking-wider">
                                    <th class="p-3">Mandal Name</th>
                                    <th class="p-3">Mandal President Name</th>
                                    <th class="p-3">Contact Number</th>
                                    <th class="p-3 text-center">Members</th>
                                    <th class="p-3 text-center">Events</th>
                                    <th class="p-3 text-center">Beneficiaries</th>
                                    <th class="p-3 text-center">Panchayats</th>
                                    <th class="p-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($subordinateUnits as $unit)
                                    <tr class="hover:bg-orange-50/40 font-medium transition">
                                        <td class="p-3 font-bold text-brandOrange uppercase">
                                            <a href="{{ $unit['detail_route'] }}" class="hover:text-orange-700 hover:underline inline-flex items-center gap-1">
                                                <span>{{ $unit['unit_name'] }}</span>
                                                <span class="text-xs">&rarr;</span>
                                            </a>
                                        </td>
                                        <td class="p-3 font-bold uppercase {{ $unit['is_assigned'] ? 'text-gray-900' : 'text-gray-400 italic' }}">
                                            {{ $unit['president_name'] }}
                                        </td>
                                        <td class="p-3 font-mono font-bold text-gray-800">{{ $unit['contact_phone'] }}</td>
                                        <td class="p-3 text-center font-bold text-gray-900">{{ number_format($unit['members_count'] ?? 0) }}</td>
                                        <td class="p-3 text-center font-bold text-gray-900">{{ number_format($unit['events_count'] ?? 0) }}</td>
                                        <td class="p-3 text-center font-bold text-emerald-700">{{ number_format($unit['beneficiaries_count'] ?? 0) }}</td>
                                        <td class="p-3 text-center font-bold text-gray-700">{{ $unit['child_count'] ?? 0 }}</td>
                                        <td class="p-3 text-center">
                                            <span class="inline-block px-2.5 py-0.5 text-[9px] font-black rounded-full uppercase {{ $unit['is_assigned'] ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-500' }}">
                                                {{ $unit['is_assigned'] ? 'Active' : 'Vacant' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="p-4 text-center text-gray-400 font-medium">No Mandals configured under this Assembly Segment yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                @elseif($volunteer->cadre_level === 'district_president')
                    {{-- 4. District President: Assembly Segments Under Your District Table --}}
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <h3 class="font-black text-gray-800 text-sm uppercase tracking-wide flex items-center gap-2">
                            <span>🗺️</span> ASSEMBLY SEGMENTS UNDER YOUR DISTRICT
                        </h3>
                        <span class="text-[10px] bg-orange-100 text-brandOrange font-bold px-2.5 py-0.5 rounded-full uppercase">
                            District Scope: {{ $volunteer->districtRelation?->name ?? $volunteer->resolved_district }}
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs text-gray-700">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 font-black uppercase text-[10px] tracking-wider">
                                    <th class="p-3">Assembly Segment</th>
                                    <th class="p-3">Assembly President Name</th>
                                    <th class="p-3">Contact Number</th>
                                    <th class="p-3 text-center">Members</th>
                                    <th class="p-3 text-center">Events</th>
                                    <th class="p-3 text-center">Beneficiaries</th>
                                    <th class="p-3 text-center">Mandals</th>
                                    <th class="p-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($subordinateUnits as $unit)
                                    <tr class="hover:bg-orange-50/40 font-medium transition">
                                        <td class="p-3 font-bold text-brandOrange uppercase">
                                            <a href="{{ $unit['detail_route'] }}" class="hover:text-orange-700 hover:underline inline-flex items-center gap-1">
                                                <span>{{ $unit['unit_name'] }}</span>
                                                <span class="text-xs">&rarr;</span>
                                            </a>
                                        </td>
                                        <td class="p-3 font-bold uppercase {{ $unit['is_assigned'] ? 'text-gray-900' : 'text-gray-400 italic' }}">
                                            {{ $unit['president_name'] }}
                                        </td>
                                        <td class="p-3 font-mono font-bold text-gray-800">{{ $unit['contact_phone'] }}</td>
                                        <td class="p-3 text-center font-bold text-gray-900">{{ number_format($unit['members_count'] ?? 0) }}</td>
                                        <td class="p-3 text-center font-bold text-gray-900">{{ number_format($unit['events_count'] ?? 0) }}</td>
                                        <td class="p-3 text-center font-bold text-emerald-700">{{ number_format($unit['beneficiaries_count'] ?? 0) }}</td>
                                        <td class="p-3 text-center font-bold text-gray-700">{{ $unit['child_count'] ?? 0 }}</td>
                                        <td class="p-3 text-center">
                                            <span class="inline-block px-2.5 py-0.5 text-[9px] font-black rounded-full uppercase {{ $unit['is_assigned'] ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-500' }}">
                                                {{ $unit['is_assigned'] ? 'Active' : 'Vacant' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="p-4 text-center text-gray-400 font-medium">No Assembly Segments configured under this District yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                @elseif($volunteer->cadre_level === 'state_president')
                    {{-- 5. State President: Districts Under Your State Table --}}
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <h3 class="font-black text-gray-800 text-sm uppercase tracking-wide flex items-center gap-2">
                            <span>🏛️</span> DISTRICTS UNDER YOUR STATE
                        </h3>
                        <span class="text-[10px] bg-orange-100 text-brandOrange font-bold px-2.5 py-0.5 rounded-full uppercase">
                            State Scope: {{ $volunteer->stateRelation?->name ?? $volunteer->resolved_state }}
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs text-gray-700">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 font-black uppercase text-[10px] tracking-wider">
                                    <th class="p-3">District Name</th>
                                    <th class="p-3">District President Name</th>
                                    <th class="p-3">Contact Number</th>
                                    <th class="p-3 text-center">Members</th>
                                    <th class="p-3 text-center">Events</th>
                                    <th class="p-3 text-center">Beneficiaries</th>
                                    <th class="p-3 text-center">Assembly Segments</th>
                                    <th class="p-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($subordinateUnits as $unit)
                                    <tr class="hover:bg-orange-50/40 font-medium transition">
                                        <td class="p-3 font-bold text-brandOrange uppercase">
                                            <a href="{{ $unit['detail_route'] }}" class="hover:text-orange-700 hover:underline inline-flex items-center gap-1">
                                                <span>{{ $unit['unit_name'] }}</span>
                                                <span class="text-xs">&rarr;</span>
                                            </a>
                                        </td>
                                        <td class="p-3 font-bold uppercase {{ $unit['is_assigned'] ? 'text-gray-900' : 'text-gray-400 italic' }}">
                                            {{ $unit['president_name'] }}
                                        </td>
                                        <td class="p-3 font-mono font-bold text-gray-800">{{ $unit['contact_phone'] }}</td>
                                        <td class="p-3 text-center font-bold text-gray-900">{{ number_format($unit['members_count'] ?? 0) }}</td>
                                        <td class="p-3 text-center font-bold text-gray-900">{{ number_format($unit['events_count'] ?? 0) }}</td>
                                        <td class="p-3 text-center font-bold text-emerald-700">{{ number_format($unit['beneficiaries_count'] ?? 0) }}</td>
                                        <td class="p-3 text-center font-bold text-gray-700">{{ $unit['child_count'] ?? 0 }}</td>
                                        <td class="p-3 text-center">
                                            <span class="inline-block px-2.5 py-0.5 text-[9px] font-black rounded-full uppercase {{ $unit['is_assigned'] ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-500' }}">
                                                {{ $unit['is_assigned'] ? 'Active' : 'Vacant' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="p-4 text-center text-gray-400 font-medium">No Districts configured under this State yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                @elseif($volunteer->cadre_level === 'national_president')
                    {{-- 6. National President: State President Directory Table --}}
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <h3 class="font-black text-gray-800 text-sm uppercase tracking-wide flex items-center gap-2">
                            <span>🇮🇳</span> STATE PRESIDENT DIRECTORY
                        </h3>
                        <span class="text-[10px] bg-orange-100 text-brandOrange font-bold px-2.5 py-0.5 rounded-full uppercase">
                            National Scope: All India
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs text-gray-700">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 font-black uppercase text-[10px] tracking-wider">
                                    <th class="p-3">State Name</th>
                                    <th class="p-3">State President Name</th>
                                    <th class="p-3">Contact Number</th>
                                    <th class="p-3 text-center">Members</th>
                                    <th class="p-3 text-center">Events</th>
                                    <th class="p-3 text-center">Beneficiaries</th>
                                    <th class="p-3 text-center">Districts</th>
                                    <th class="p-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($subordinateUnits as $unit)
                                    <tr class="hover:bg-orange-50/40 font-medium transition">
                                        <td class="p-3 font-bold text-brandOrange uppercase">
                                            <a href="{{ $unit['detail_route'] }}" class="hover:text-orange-700 hover:underline inline-flex items-center gap-1">
                                                <span>{{ $unit['unit_name'] }}</span>
                                                <span class="text-xs">&rarr;</span>
                                            </a>
                                        </td>
                                        <td class="p-3 font-bold uppercase {{ $unit['is_assigned'] ? 'text-gray-900' : 'text-gray-400 italic' }}">
                                            {{ $unit['president_name'] }}
                                        </td>
                                        <td class="p-3 font-mono font-bold text-gray-800">{{ $unit['contact_phone'] }}</td>
                                        <td class="p-3 text-center font-bold text-gray-900">{{ number_format($unit['members_count'] ?? 0) }}</td>
                                        <td class="p-3 text-center font-bold text-gray-900">{{ number_format($unit['events_count'] ?? 0) }}</td>
                                        <td class="p-3 text-center font-bold text-emerald-700">{{ number_format($unit['beneficiaries_count'] ?? 0) }}</td>
                                        <td class="p-3 text-center font-bold text-gray-700">{{ $unit['child_count'] ?? 0 }}</td>
                                        <td class="p-3 text-center">
                                            <span class="inline-block px-2.5 py-0.5 text-[9px] font-black rounded-full uppercase {{ $unit['is_assigned'] ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-500' }}">
                                                {{ $unit['is_assigned'] ? 'Active' : 'Vacant' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="p-4 text-center text-gray-400 font-medium">No States configured yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif

        {{-- Regional Assignment & Profile Summary --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Left: Profile Dossier --}}
            <div class="lg:col-span-2 bg-white rounded-3xl border border-gray-200 shadow-sm p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="font-black text-gray-800 text-sm uppercase tracking-wide flex items-center gap-2">
                        <span>📍</span> Regional Deployment &amp; Jurisdictional Scope
                    </h3>
                    <span class="text-[10px] bg-orange-100 text-brandOrange font-bold px-2 py-0.5 rounded">
                        Active Assignment
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="p-3 bg-gray-50 rounded-xl">
                        <span class="text-[10px] font-bold text-gray-400 uppercase block mb-0.5">Country</span>
                        <span class="font-bold text-gray-900">{{ $volunteer->resolved_country }}</span>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-xl">
                        <span class="text-[10px] font-bold text-gray-400 uppercase block mb-0.5">State</span>
                        <span class="font-bold text-gray-900">{{ $volunteer->resolved_state ?? 'Andhra Pradesh' }}</span>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-xl">
                        <span class="text-[10px] font-bold text-gray-400 uppercase block mb-0.5">District</span>
                        <span class="font-bold text-gray-900">{{ $volunteer->resolved_district ?? '—' }}</span>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-xl">
                        <span class="text-[10px] font-bold text-gray-400 uppercase block mb-0.5">Taluk / Assembly Segment</span>
                        <span class="font-bold text-gray-900">{{ $volunteer->resolved_assembly_segment ?? '—' }}</span>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-xl">
                        <span class="text-[10px] font-bold text-gray-400 uppercase block mb-0.5">Mandal</span>
                        <span class="font-bold text-gray-900">{{ $volunteer->resolved_mandal ?? '—' }}</span>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-xl">
                        <span class="text-[10px] font-bold text-gray-400 uppercase block mb-0.5">Grama Panchayat</span>
                        <span class="font-bold text-gray-900">{{ $volunteer->resolved_grama_panchayat ?? '—' }}</span>
                    </div>
                </div>

                <div class="pt-2 border-t border-gray-100 flex flex-wrap items-center justify-between text-xs text-gray-500 gap-2">
                    <div>
                        <strong>Membership Reference:</strong> <span class="font-mono">{{ $volunteer->membership_id }}</span>
                    </div>
                    <div>
                        <strong>Registered Email:</strong> <span>{{ $volunteer->email }}</span>
                    </div>
                </div>
            </div>

            {{-- Right: Quick Action Hub --}}
            <div class="bg-gradient-to-br from-orange-50 to-amber-50 rounded-3xl border border-orange-200 p-6 flex flex-col justify-between space-y-4">
                <div>
                    <h3 class="font-black text-orange-950 text-sm uppercase tracking-wide mb-1 flex items-center gap-2">
                        <img src="{{ asset('images/ABVHPS_LOGO.jpg') }}" class="w-5 h-5 rounded-full object-cover border border-orange-500 inline-block" alt="ABVHPS Logo"> Volunteer Operations
                    </h3>
                    <p class="text-xs text-orange-800/80 leading-relaxed">
                        Access your official digital credentials, public directory listing, and personal security settings.
                    </p>
                </div>

                <div class="space-y-2">
                    <a href="{{ route('volunteer.events.index') }}"
                       class="w-full bg-brandOrange hover:bg-orange-600 text-white font-black text-xs py-2.5 px-4 rounded-xl shadow-xs transition flex items-center justify-between">
                        <span>Events &amp; Service Activities</span>
                        <span>&rarr;</span>
                    </a>

                    <a href="{{ route('volunteer.member_search') }}"
                       class="w-full bg-white hover:bg-orange-100 text-brandOrange border border-orange-300 font-bold text-xs py-2.5 px-4 rounded-xl shadow-xs transition flex items-center justify-between">
                        <span>Search Member by ID</span>
                        <span>&rarr;</span>
                    </a>

                    <a href="{{ route('public.team', ['search' => $volunteer->volunteer_id]) }}"
                       target="_blank"
                       class="w-full bg-white hover:bg-orange-100 text-brandOrange border border-orange-300 font-bold text-xs py-2.5 px-4 rounded-xl shadow-xs transition flex items-center justify-between">
                        <span>View in Public Directory</span>
                        <span>&rarr;</span>
                    </a>

                    <a href="{{ route('volunteer.profile') }}"
                       class="w-full bg-white hover:bg-orange-100 text-gray-800 border border-orange-200 font-bold text-xs py-2.5 px-4 rounded-xl shadow-xs transition flex items-center justify-between">
                        <span>View Full Dossier</span>
                        <span>&rarr;</span>
                    </a>

                    <a href="{{ route('volunteer.change_password') }}"
                       class="w-full bg-white hover:bg-orange-100 text-gray-800 border border-orange-200 font-bold text-xs py-2.5 px-4 rounded-xl shadow-xs transition flex items-center justify-between">
                        <span>Manage Password</span>
                        <span>&rarr;</span>
                    </a>
                </div>

                <div class="pt-2 border-t border-orange-200/60 text-center">
                    <span class="text-[10px] text-orange-900/60 font-semibold">
                        ABVHPS Sanathana Dharma Seva Wing
                    </span>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
