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
                    🔑 Change Password
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
                        <span>🏆 Events &amp; Service Activities</span>
                        <span>&rarr;</span>
                    </a>

                    <a href="{{ route('volunteer.member_search') }}"
                       class="w-full bg-white hover:bg-orange-100 text-brandOrange border border-orange-300 font-bold text-xs py-2.5 px-4 rounded-xl shadow-xs transition flex items-center justify-between">
                        <span>🔍 Search Member by ID</span>
                        <span>&rarr;</span>
                    </a>

                    <a href="{{ route('volunteer.member_data') }}"
                       class="w-full bg-white hover:bg-orange-100 text-gray-800 border border-orange-200 font-bold text-xs py-2.5 px-4 rounded-xl shadow-xs transition flex items-center justify-between">
                        <span>📋 Area-wise Member Data</span>
                        <span>&rarr;</span>
                    </a>

                    <a href="{{ route('public.team', ['search' => $volunteer->volunteer_id]) }}"
                       target="_blank"
                       class="w-full bg-white hover:bg-orange-100 text-brandOrange border border-orange-300 font-bold text-xs py-2.5 px-4 rounded-xl shadow-xs transition flex items-center justify-between">
                        <span>👥 View in Public Directory</span>
                        <span>&rarr;</span>
                    </a>

                    <a href="{{ route('volunteer.profile') }}"
                       class="w-full bg-white hover:bg-orange-100 text-gray-800 border border-orange-200 font-bold text-xs py-2.5 px-4 rounded-xl shadow-xs transition flex items-center justify-between">
                        <span>📄 View Full Dossier</span>
                        <span>&rarr;</span>
                    </a>

                    <a href="{{ route('volunteer.change_password') }}"
                       class="w-full bg-white hover:bg-orange-100 text-gray-800 border border-orange-200 font-bold text-xs py-2.5 px-4 rounded-xl shadow-xs transition flex items-center justify-between">
                        <span>🔑 Manage Password</span>
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
