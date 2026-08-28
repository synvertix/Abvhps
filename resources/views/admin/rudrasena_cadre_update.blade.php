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
                <span class="bg-orange-100 text-brandOrange text-[9px] font-black px-2.5 py-0.5 rounded border border-orange-200 tracking-widest uppercase shadow-sm">Rudrasena Approval Details</span>
            </div>
            <div class="text-right text-[10px] font-mono font-black text-gray-500">
                System Sync: {{ \Carbon\Carbon::now()->format('d-M-Y H:i') }} IST
            </div>
        </header>

        <!-- Dynamic Content Workspace Container -->
        <main class="flex-1 overflow-y-auto p-6 space-y-6">
            
            <!-- Breadcrumb Navigation Bar -->
            <div class="flex items-center gap-2 text-xs font-bold text-gray-500 border-b border-gray-200 pb-3">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-brandOrange transition">Home</a>
                <span>-</span>
                <a href="{{ route('admin.rudrasena.index') }}" class="bg-brandOrange text-white text-[11px] font-black px-3 py-1 rounded shadow-sm uppercase tracking-wide">
                    Rudrasena
                </a>
            </div>

            <!-- Page Title & Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-black text-brandGray tracking-tight">Rudrasena Approval & Cadre Assignment</h2>
                    <p class="text-xs text-gray-400 font-semibold mt-0.5">Assign Cadre, Locality, and Verify ID sequence.</p>
                </div>
                <a href="{{ route('admin.rudrasena.index') }}" class="bg-gray-800 hover:bg-gray-900 text-white font-black text-[10px] px-4 py-2 rounded-lg shadow-sm uppercase tracking-wide transition">
                    ← Back to List
                </a>
            </div>

            <!-- Error Alerts Block -->
            @if(isset($errors) && $errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-xs font-semibold shadow-sm">
                    <div class="font-black mb-1">Please correct the following errors:</div>
                    <ul class="list-disc pl-4 space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Main Input Form Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 max-w-3xl">
                <form action="{{ route('admin.rudrasena.update', $member->id) }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- 1. Name of Member -->
                    <div>
                        <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">Name of Rudrasena Member</label>
                        <input type="text" readonly class="w-full bg-gray-100 border border-gray-300 rounded-lg px-4 py-2.5 text-xs font-black text-gray-800 uppercase focus:outline-none cursor-not-allowed" value="{{ $member->full_name }}">
                        <p class="text-[10px] text-gray-400 font-semibold mt-1">Membership ID: {{ implode(' ', str_split($member->membership_id, 4)) }} | Volunteer Type: {{ $member->volunteer_type ?? 'Standard' }}</p>
                    </div>

                    <!-- 1B. Date of Birth & Age Verification Indicator -->
                    @php
                        $calcAge = null;
                        $isAgeEligible = false;
                        if (!empty($member->dob)) {
                            try {
                                $calcAge = \App\Services\RudrasenaEligibilityService::calculateAge($member->dob);
                                $isAgeEligible = \App\Services\RudrasenaEligibilityService::isAgeEligible($member->dob);
                            } catch (\Throwable $e) {}
                        }
                    @endphp
                    <div class="p-3.5 rounded-lg border {{ $isAgeEligible ? 'bg-emerald-50 border-emerald-200' : 'bg-rose-50 border-rose-200' }}">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-gray-700">Date of Birth & Age:</span>
                            <span class="font-black {{ $isAgeEligible ? 'text-emerald-800' : 'text-rose-800' }}">
                                {{ $member->dob ?: 'Missing DOB' }}
                                @if($calcAge !== null)
                                    ({{ $calcAge }} Years Old) — {{ $isAgeEligible ? 'Eligible (24–44 Years)' : 'Ineligible (Requires 24–44)' }}
                                @else
                                    — Ineligible (Missing DOB)
                                @endif
                            </span>
                        </div>
                    </div>

                    <!-- 2. Status -->
                    <div>
                        <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">Verification Status *</label>
                        <select name="status" required class="w-full bg-white border border-gray-300 rounded-lg px-4 py-2.5 text-xs font-bold text-gray-800 focus:outline-none focus:border-brandOrange">
                            <option value="Verified" {{ old('status', $member->status) === 'verified' ? 'selected' : '' }}>Verified</option>
                            <option value="Rejected" {{ old('status', $member->status) === 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="Pending" {{ old('status', $member->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                        </select>
                        <p class="text-[10px] text-gray-400 font-medium mt-1">Setting to "Verified" generates a sequential ID (e.g. RS0001, RS0002...).</p>
                    </div>

                    <!-- 3. Volunteer Cadder -->
                    <div>
                        <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">Assigned Cadder *</label>
                        <input type="text" name="assigned_cadder" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-xs font-semibold text-gray-900 focus:outline-none focus:border-brandOrange" placeholder="e.g. Disaster Relief Commander, Mandal Leader" value="{{ old('assigned_cadder', $member->assigned_cadder) }}">
                    </div>

                    <!-- 4. Volunteer Locality -->
                    <div>
                        <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">Assigned Locality *</label>
                        <input type="text" name="assigned_locality" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-xs font-semibold text-gray-900 focus:outline-none focus:border-brandOrange" placeholder="e.g. Porumamilla, Kadapa, Badvel" value="{{ old('assigned_locality', $member->assigned_locality ?: ($member->mandal ?? ($member->district ?? 'HQ'))) }}">
                    </div>

                    <!-- Existing ID display if already verified -->
                    @if($member->rudrasena_id)
                        <div class="p-3 bg-emerald-50 rounded-lg border border-emerald-200">
                            <span class="block text-[10px] font-black text-emerald-700 uppercase tracking-wider">Current Assigned ID:</span>
                            <span class="text-sm font-mono font-black text-emerald-900">{{ $member->rudrasena_id }}</span>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="pt-4 border-t border-gray-200 flex items-center justify-end gap-3">
                        <a href="{{ route('admin.rudrasena.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-black text-xs px-6 py-2.5 rounded-lg uppercase tracking-wider transition">
                            Cancel
                        </a>
                        <button type="submit" class="bg-brandOrange hover:bg-orange-700 text-white font-black text-xs px-8 py-2.5 rounded-lg shadow-sm uppercase tracking-wider transition flex items-center gap-1.5">
                            <span>💾</span> Save Changes
                        </button>
                    </div>
                </form>
            </div>

        </main>
    </div>
</div>
@endsection
