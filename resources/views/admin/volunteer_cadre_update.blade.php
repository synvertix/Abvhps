@extends('layouts.app')

@section('title', 'Volunteer Approval & Cadre Assignment | ABVHPS')

@section('content')
<div class="flex h-screen bg-[#111827] overflow-hidden font-sans text-gray-300">
    
    @include('admin.partials.sidebar')

    <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-gray-100">
        
        <!-- Header Bar -->
        <header class="bg-white border-b border-gray-200 shadow-xs px-6 py-3.5 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-2">
                @include('admin.partials.header_button')
                <span class="text-sm font-black text-brandGray uppercase tracking-wider">System View:</span>
                <span class="bg-orange-100 text-brandOrange text-[9px] font-black px-2.5 py-0.5 rounded border border-orange-200 tracking-widest uppercase shadow-sm">Volunteer Approval Details</span>
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
                <a href="{{ route('admin.volunteers.index') }}" class="hover:text-brandOrange transition">Volunteers</a>
                <span>-</span>
                <span class="bg-brandOrange text-white text-[11px] font-black px-3 py-1 rounded shadow-sm uppercase tracking-wide">
                    Cadre Assignment
                </span>
            </div>

            <!-- Page Title & Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-black text-brandGray tracking-tight">Volunteer Approval Details &amp; Cadre Scope</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Assign 6-level President authorization scope and canonical geographic boundaries.</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.volunteers.index') }}" class="bg-gray-800 hover:bg-gray-900 text-white font-black text-[10px] px-4 py-2 rounded-lg shadow-sm uppercase tracking-wide transition">
                        ← Back to List
                    </a>
                </div>
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
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 max-w-4xl">
                <form action="{{ route('admin.volunteers.cadreUpdate', $volunteer->id) }}" method="POST" class="space-y-6">
                    @csrf

                    {{-- Volunteer Context Header Card --}}
                    <div class="bg-amber-50/50 border border-amber-200/80 rounded-xl p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            @if($volunteer->membership?->photo_path)
                                <img src="{{ asset('storage/' . $volunteer->membership->photo_path) }}" alt="Photo" class="w-12 h-12 rounded-xl object-cover border border-amber-300 shadow-sm">
                            @else
                                <div class="w-12 h-12 rounded-xl bg-orange-200 text-orange-800 flex items-center justify-center font-black text-base border border-amber-300">
                                    {{ substr($volunteer->full_name, 0, 1) }}
                                </div>
                            @endif
                            <div>
                                <h3 class="font-black text-gray-900 text-sm uppercase">{{ $volunteer->full_name }}</h3>
                                <div class="flex flex-wrap items-center gap-2 mt-0.5 text-[11px] text-gray-600 font-medium">
                                    <span>Mem ID: <strong class="font-mono">{{ implode(' ', str_split($volunteer->membership_id, 4)) }}</strong></span>
                                    <span>•</span>
                                    <span>Vol ID: <strong class="font-mono text-orange-700">{{ $volunteer->volunteer_id ?? 'Pending Approval' }}</strong></span>
                                </div>
                            </div>
                        </div>

                        <div class="text-right">
                            <span class="text-[10px] font-black uppercase tracking-wider text-gray-500 block">Mapping Status</span>
                            <span class="inline-block mt-0.5 px-2.5 py-0.5 text-[10px] font-black rounded-full uppercase tracking-wider {{ $volunteer->geo_mapping_status === 'verified' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : ($volunteer->geo_mapping_status === 'needs_review' ? 'bg-rose-100 text-rose-800 border border-rose-300' : 'bg-amber-100 text-amber-800 border border-amber-300') }}">
                                {{ $volunteer->geo_mapping_status ?? 'unmapped' }}
                            </span>
                        </div>
                    </div>

                    {{-- Legacy Geographics Reference --}}
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-3 text-[11px] text-gray-600">
                        <span class="font-bold text-gray-800 uppercase block text-[10px] tracking-wider mb-1">Legacy Registration Geography (Reference):</span>
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                            <div><span class="text-gray-400">State:</span> {{ $volunteer->resolved_state ?: '—' }}</div>
                            <div><span class="text-gray-400">District:</span> {{ $volunteer->resolved_district ?: '—' }}</div>
                            <div><span class="text-gray-400">Assembly:</span> {{ $volunteer->resolved_assembly_segment ?: '—' }}</div>
                            <div><span class="text-gray-400">Mandal:</span> {{ $volunteer->resolved_mandal ?: '—' }}</div>
                            <div><span class="text-gray-400">Panchayat:</span> {{ $volunteer->resolved_grama_panchayat ?: '—' }}</div>
                        </div>
                    </div>

                    {{-- Section 1: Approval Status & Cadre Level --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Status -->
                        <div>
                            <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-1.5">Volunteer Status *</label>
                            <select name="status" required class="w-full bg-white border border-gray-300 rounded-lg px-3.5 py-2.5 text-xs font-bold text-gray-800 focus:outline-none focus:border-brandOrange">
                                <option value="approved" {{ old('status', $volunteer->status) === 'approved' ? 'selected' : '' }}>Approved (Active Clearance)</option>
                                <option value="rejected" {{ old('status', $volunteer->status) === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="pending" {{ old('status', $volunteer->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                            </select>
                        </div>

                        <!-- Cadre Level -->
                        <div>
                            <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-1.5">Cadre Authorization Level *</label>
                            <select name="cadre_level" id="cadre_level_select" required class="w-full bg-white border border-gray-300 rounded-lg px-3.5 py-2.5 text-xs font-bold text-gray-800 focus:outline-none focus:border-brandOrange">
                                @foreach($cadreLevels as $key => $label)
                                    <option value="{{ $key }}" {{ old('cadre_level', $volunteer->cadre_level) === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Section 2: Cascading Canonical Geographic Selectors --}}
                    <div class="border-t border-gray-200 pt-4 space-y-4" id="geo_jurisdiction_section">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-black text-gray-800 uppercase tracking-wider">Canonical Geographic Jurisdiction</h4>
                            <span class="text-[10px] text-gray-400 font-medium" id="geo_guidance_text">Select required tiers based on cadre level</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <!-- Canonical State -->
                            <div id="geo_state_group">
                                <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1" id="state_label">1. Canonical State</label>
                                <select name="state_id" id="state_select" class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-xs font-medium text-gray-800 focus:outline-none focus:border-brandOrange">
                                    <option value="">-- Select State --</option>
                                    @foreach($states as $st)
                                        <option value="{{ $st->id }}" {{ (int)old('state_id', $prefilledStateId) === $st->id ? 'selected' : '' }}>
                                            {{ $st->name }} ({{ $st->code ?? 'IN' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Canonical District -->
                            <div id="geo_district_group">
                                <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1" id="district_label">2. Canonical District</label>
                                <select name="district_id" id="district_select" class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-xs font-medium text-gray-800 focus:outline-none focus:border-brandOrange">
                                    <option value="">-- Select District --</option>
                                    @foreach($districts as $dst)
                                        <option value="{{ $dst->id }}" {{ (int)old('district_id', $prefilledDistrictId) === $dst->id ? 'selected' : '' }}>
                                            {{ $dst->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Canonical Assembly Segment -->
                            <div id="geo_assembly_group">
                                <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1" id="assembly_label">3. Assembly Segment</label>
                                <select name="assembly_segment_id" id="assembly_select" class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-xs font-medium text-gray-800 focus:outline-none focus:border-brandOrange">
                                    <option value="">-- Select Assembly Segment --</option>
                                    @foreach($assemblySegments as $asm)
                                        <option value="{{ $asm->id }}" {{ (int)old('assembly_segment_id', $prefilledAssemblyId) === $asm->id ? 'selected' : '' }}>
                                            {{ $asm->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Canonical Mandal -->
                            <div id="geo_mandal_group">
                                <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1" id="mandal_label">4. Canonical Mandal</label>
                                <select name="mandal_id" id="mandal_select" class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-xs font-medium text-gray-800 focus:outline-none focus:border-brandOrange">
                                    <option value="">-- Select Mandal --</option>
                                    @foreach($mandals as $mdl)
                                        <option value="{{ $mdl->id }}" {{ (int)old('mandal_id', $prefilledMandalId) === $mdl->id ? 'selected' : '' }}>
                                            {{ $mdl->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Canonical Grama Panchayat -->
                            <div id="geo_panchayat_group">
                                <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1" id="panchayat_label">5. Grama Panchayat</label>
                                <select name="panchayat_id" id="panchayat_select" class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-xs font-medium text-gray-800 focus:outline-none focus:border-brandOrange">
                                    <option value="">-- Select Panchayat --</option>
                                    @foreach($panchayats as $pan)
                                        <option value="{{ $pan->id }}" {{ (int)old('panchayat_id', $prefilledPanchayatId) === $pan->id ? 'selected' : '' }}>
                                            {{ $pan->name }} {{ $pan->pincode ? "({$pan->pincode})" : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Section 3: Custom Display Labels (Legacy Compatibility) --}}
                    <div class="border-t border-gray-200 pt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-1.5">Custom Cadre Title (Optional)</label>
                            <input type="text" name="cadre" class="w-full border border-gray-300 rounded-lg px-3.5 py-2 text-xs font-medium text-gray-900 focus:outline-none focus:border-brandOrange" placeholder="e.g. Panchayat President" value="{{ old('cadre', $volunteer->cadre) }}">
                        </div>

                        <div>
                            <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-1.5">Locality Display Text (Optional)</label>
                            <input type="text" name="locality" class="w-full border border-gray-300 rounded-lg px-3.5 py-2 text-xs font-medium text-gray-900 focus:outline-none focus:border-brandOrange" placeholder="e.g. Akkalareddypalli, Porumamilla" value="{{ old('locality', $volunteer->locality ?: $volunteer->jurisdiction_summary) }}">
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-6 border-t border-gray-200 flex items-center justify-end gap-3">
                        <a href="{{ route('admin.volunteers.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-black text-xs px-6 py-2.5 rounded-lg uppercase tracking-wider transition">
                            Cancel
                        </a>
                        <button type="submit" class="bg-brandOrange hover:bg-orange-700 text-white font-black text-xs px-8 py-2.5 rounded-lg shadow-sm uppercase tracking-wider transition flex items-center gap-1.5 cursor-pointer">
                            <span>💾</span> Verify &amp; Save Cadre Scope
                        </button>
                    </div>
                </form>
            </div>

        </main>
    </div>
</div>

{{-- Cascading Dynamic Hierarchy Javascript Engine --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const cadreSelect = document.getElementById('cadre_level_select');
    const stateGroup = document.getElementById('geo_state_group');
    const districtGroup = document.getElementById('geo_district_group');
    const assemblyGroup = document.getElementById('geo_assembly_group');
    const mandalGroup = document.getElementById('geo_mandal_group');
    const panchayatGroup = document.getElementById('geo_panchayat_group');

    const stateSelect = document.getElementById('state_select');
    const districtSelect = document.getElementById('district_select');
    const assemblySelect = document.getElementById('assembly_select');
    const mandalSelect = document.getElementById('mandal_select');
    const panchayatSelect = document.getElementById('panchayat_select');

    function updateCadreVisibility() {
        const cadre = cadreSelect.value;

        // Reset visibility
        stateGroup.style.display = 'block';
        districtGroup.style.display = 'block';
        assemblyGroup.style.display = 'block';
        mandalGroup.style.display = 'block';
        panchayatGroup.style.display = 'block';

        if (cadre === 'national_president') {
            stateGroup.style.display = 'none';
            districtGroup.style.display = 'none';
            assemblyGroup.style.display = 'none';
            mandalGroup.style.display = 'none';
            panchayatGroup.style.display = 'none';
        } else if (cadre === 'state_president') {
            districtGroup.style.display = 'none';
            assemblyGroup.style.display = 'none';
            mandalGroup.style.display = 'none';
            panchayatGroup.style.display = 'none';
        } else if (cadre === 'district_president') {
            assemblyGroup.style.display = 'none';
            mandalGroup.style.display = 'none';
            panchayatGroup.style.display = 'none';
        } else if (cadre === 'assembly_president') {
            mandalGroup.style.display = 'none';
            panchayatGroup.style.display = 'none';
        } else if (cadre === 'mandal_president') {
            panchayatGroup.style.display = 'none';
        }
    }

    if (cadreSelect) {
        cadreSelect.addEventListener('change', updateCadreVisibility);
        updateCadreVisibility();
    }

    // 1. State Change -> Load Districts
    stateSelect.addEventListener('change', function () {
        const stateId = this.value;
        districtSelect.innerHTML = '<option value="">-- Loading Districts... --</option>';
        assemblySelect.innerHTML = '<option value="">-- Select Assembly Segment --</option>';
        mandalSelect.innerHTML = '<option value="">-- Select Mandal --</option>';
        panchayatSelect.innerHTML = '<option value="">-- Select Panchayat --</option>';

        if (!stateId) {
            districtSelect.innerHTML = '<option value="">-- Select District --</option>';
            return;
        }

        fetch(`{{ route('admin.geo.hierarchy') }}?state_id=${stateId}`)
            .then(res => res.json())
            .then(data => {
                districtSelect.innerHTML = '<option value="">-- Select District --</option>';
                if (data.districts) {
                    data.districts.forEach(d => {
                        districtSelect.innerHTML += `<option value="${d.id}">${d.name}</option>`;
                    });
                }
            })
            .catch(() => {
                districtSelect.innerHTML = '<option value="">-- Error Loading Districts --</option>';
            });
    });

    // 2. District Change -> Load Assembly Segments & Mandals
    districtSelect.addEventListener('change', function () {
        const districtId = this.value;
        assemblySelect.innerHTML = '<option value="">-- Loading Assembly Segments... --</option>';
        mandalSelect.innerHTML = '<option value="">-- Loading Mandals... --</option>';
        panchayatSelect.innerHTML = '<option value="">-- Select Panchayat --</option>';

        if (!districtId) {
            assemblySelect.innerHTML = '<option value="">-- Select Assembly Segment --</option>';
            mandalSelect.innerHTML = '<option value="">-- Select Mandal --</option>';
            return;
        }

        fetch(`{{ route('admin.geo.hierarchy') }}?district_id=${districtId}`)
            .then(res => res.json())
            .then(data => {
                assemblySelect.innerHTML = '<option value="">-- Select Assembly Segment --</option>';
                if (data.assembly_segments) {
                    data.assembly_segments.forEach(a => {
                        assemblySelect.innerHTML += `<option value="${a.id}">${a.name}</option>`;
                    });
                }
                mandalSelect.innerHTML = '<option value="">-- Select Mandal --</option>';
                if (data.mandals) {
                    data.mandals.forEach(m => {
                        mandalSelect.innerHTML += `<option value="${m.id}">${m.name}</option>`;
                    });
                }
            });
    });

    // 2b. Assembly Change -> Load Mandals under that Assembly
    assemblySelect.addEventListener('change', function () {
        const assemblyId = this.value;
        mandalSelect.innerHTML = '<option value="">-- Loading Mandals... --</option>';
        panchayatSelect.innerHTML = '<option value="">-- Select Panchayat --</option>';

        if (!assemblyId) {
            const districtId = districtSelect.value;
            if (districtId) {
                fetch(`{{ route('admin.geo.hierarchy') }}?district_id=${districtId}`)
                    .then(res => res.json())
                    .then(data => {
                        mandalSelect.innerHTML = '<option value="">-- Select Mandal --</option>';
                        if (data.mandals) {
                            data.mandals.forEach(m => {
                                mandalSelect.innerHTML += `<option value="${m.id}">${m.name}</option>`;
                            });
                        }
                    });
            } else {
                mandalSelect.innerHTML = '<option value="">-- Select Mandal --</option>';
            }
            return;
        }

        fetch(`{{ route('admin.geo.hierarchy') }}?assembly_segment_id=${assemblyId}`)
            .then(res => res.json())
            .then(data => {
                mandalSelect.innerHTML = '<option value="">-- Select Mandal --</option>';
                if (data.mandals) {
                    data.mandals.forEach(m => {
                        mandalSelect.innerHTML += `<option value="${m.id}">${m.name}</option>`;
                    });
                }
            });
    });

    // 3. Mandal Change -> Load Panchayats
    mandalSelect.addEventListener('change', function () {
        const mandalId = this.value;
        panchayatSelect.innerHTML = '<option value="">-- Loading Panchayats... --</option>';

        if (!mandalId) {
            panchayatSelect.innerHTML = '<option value="">-- Select Panchayat --</option>';
            return;
        }

        fetch(`{{ route('admin.geo.hierarchy') }}?mandal_id=${mandalId}`)
            .then(res => res.json())
            .then(data => {
                panchayatSelect.innerHTML = '<option value="">-- Select Panchayat --</option>';
                if (data.panchayats) {
                    data.panchayats.forEach(p => {
                        panchayatSelect.innerHTML += `<option value="${p.id}">${p.name} ${p.pincode ? '(' + p.pincode + ')' : ''}</option>`;
                    });
                }
            });
    });
});
</script>
@endsection
