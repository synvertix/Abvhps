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
                <span class="text-sm font-black text-brandGray uppercase tracking-wider">Geographic Desk:</span>
                <span class="bg-emerald-100 text-emerald-800 text-[9px] font-black px-2.5 py-0.5 rounded border border-emerald-200 tracking-widest uppercase shadow-sm">Canonical Mapping &amp; Conflict Review</span>
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
                    Geographic Review Desk
                </span>
            </div>

            <!-- Page Title & Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-xl font-black text-brandGray tracking-tight">Geographic Mapping &amp; Cadre Conflict Review</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Audit, verify canonical 5-tier geography and resolve cadre/district alias conflicts.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="document.getElementById('aliasModal').classList.remove('hidden')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-black text-[10px] px-4 py-2 rounded-lg shadow-sm uppercase tracking-wide transition flex items-center gap-1.5 cursor-pointer">
                        <span>➕</span> Approve Geographic Alias
                    </button>
                    <a href="{{ route('admin.volunteers.index') }}" class="bg-gray-800 hover:bg-gray-900 text-white font-black text-[10px] px-4 py-2 rounded-lg shadow-sm uppercase tracking-wide transition">
                        ← Volunteer List
                    </a>
                </div>
            </div>

            <!-- Flash & Error Alerts -->
            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-xl text-xs font-bold shadow-sm flex items-center justify-between">
                    <span>✓ {{ session('success') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-emerald-600 font-black">×</button>
                </div>
            @endif

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

            <!-- Summary KPI Metric Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                <a href="{{ route('admin.geo_review.index', ['status' => 'all', 'type' => $typeFilter]) }}" class="bg-white p-4 rounded-xl border {{ $statusFilter === 'all' ? 'border-brandOrange ring-2 ring-brandOrange/20' : 'border-gray-200' }} shadow-sm transition hover:border-brandOrange">
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Total Scanned</span>
                    <div class="text-2xl font-black text-gray-900 mt-1">{{ $counts['total'] }}</div>
                    <span class="text-[10px] text-gray-500 block mt-0.5">Volunteers &amp; Members</span>
                </a>

                <a href="{{ route('admin.geo_review.index', ['status' => 'needs_review', 'type' => $typeFilter]) }}" class="bg-white p-4 rounded-xl border {{ $statusFilter === 'needs_review' ? 'border-rose-500 ring-2 ring-rose-500/20' : 'border-gray-200' }} shadow-sm transition hover:border-rose-500">
                    <span class="text-[10px] font-black text-rose-500 uppercase tracking-wider block">Needs Review</span>
                    <div class="text-2xl font-black text-rose-600 mt-1">{{ $counts['needs_review'] }}</div>
                    <span class="text-[10px] text-rose-500/80 block mt-0.5">Conflicts &amp; Aliases</span>
                </a>

                <a href="{{ route('admin.geo_review.index', ['status' => 'matched', 'type' => $typeFilter]) }}" class="bg-white p-4 rounded-xl border {{ $statusFilter === 'matched' ? 'border-blue-500 ring-2 ring-blue-500/20' : 'border-gray-200' }} shadow-sm transition hover:border-blue-500">
                    <span class="text-[10px] font-black text-blue-500 uppercase tracking-wider block">Matched (Pending)</span>
                    <div class="text-2xl font-black text-blue-600 mt-1">{{ $counts['matched'] }}</div>
                    <span class="text-[10px] text-blue-500/80 block mt-0.5">Awaiting Admin sign-off</span>
                </a>

                <a href="{{ route('admin.geo_review.index', ['status' => 'verified', 'type' => $typeFilter]) }}" class="bg-white p-4 rounded-xl border {{ $statusFilter === 'verified' ? 'border-emerald-500 ring-2 ring-emerald-500/20' : 'border-gray-200' }} shadow-sm transition hover:border-emerald-500">
                    <span class="text-[10px] font-black text-emerald-600 uppercase tracking-wider block">Verified</span>
                    <div class="text-2xl font-black text-emerald-600 mt-1">{{ $counts['verified'] }}</div>
                    <span class="text-[10px] text-emerald-600/80 block mt-0.5">Active canonical scope</span>
                </a>

                <a href="{{ route('admin.geo_review.index', ['status' => 'unmapped', 'type' => $typeFilter]) }}" class="bg-white p-4 rounded-xl border {{ $statusFilter === 'unmapped' ? 'border-amber-500 ring-2 ring-amber-500/20' : 'border-gray-200' }} shadow-sm transition hover:border-amber-500">
                    <span class="text-[10px] font-black text-amber-600 uppercase tracking-wider block">Unmapped</span>
                    <div class="text-2xl font-black text-amber-600 mt-1">{{ $counts['unmapped'] }}</div>
                    <span class="text-[10px] text-amber-600/80 block mt-0.5">Missing geo master</span>
                </a>
            </div>

            <!-- Approved Aliases Drawer / Quick Bar -->
            @if($aliases->count() > 0)
                <div class="bg-indigo-50/60 border border-indigo-200 rounded-xl p-4">
                    <h4 class="text-xs font-black text-indigo-900 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                        <span>🏷️</span> Approved Geographic Aliases Active:
                    </h4>
                    <div class="flex flex-wrap gap-2">
                        @foreach($aliases as $al)
                            <span class="bg-white text-indigo-900 text-xs font-bold px-3 py-1 rounded-lg border border-indigo-300 shadow-sm">
                                <strong>{{ $al->alias_name }}</strong> &rarr; Canonical #{{ $al->canonical_id }} ({{ ucfirst($al->entity_type) }})
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Main Records Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                    <h3 class="font-black text-gray-800 text-sm uppercase tracking-wide">Registry Records Audit</h3>
                    
                    {{-- Type Filter Buttons --}}
                    <div class="flex items-center gap-1 bg-gray-100 p-1 rounded-lg text-xs font-bold">
                        <a href="{{ route('admin.geo_review.index', ['status' => $statusFilter, 'type' => 'all']) }}" class="px-3 py-1 rounded {{ $typeFilter === 'all' ? 'bg-white shadow-sm text-brandOrange font-black' : 'text-gray-600 hover:text-gray-900' }}">All</a>
                        <a href="{{ route('admin.geo_review.index', ['status' => $statusFilter, 'type' => 'volunteer']) }}" class="px-3 py-1 rounded {{ $typeFilter === 'volunteer' ? 'bg-white shadow-sm text-brandOrange font-black' : 'text-gray-600 hover:text-gray-900' }}">Volunteers</a>
                        <a href="{{ route('admin.geo_review.index', ['status' => $statusFilter, 'type' => 'membership']) }}" class="px-3 py-1 rounded {{ $typeFilter === 'membership' ? 'bg-white shadow-sm text-brandOrange font-black' : 'text-gray-600 hover:text-gray-900' }}">Memberships</a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 border-b border-gray-200 text-gray-500 uppercase text-[10px] font-black tracking-wider">
                            <tr>
                                <th class="py-3.5 px-4">Entity &amp; ID</th>
                                <th class="py-3.5 px-4">Full Name</th>
                                <th class="py-3.5 px-4">Legacy Geographic Registration</th>
                                <th class="py-3.5 px-4">Legacy Cadre / Role</th>
                                <th class="py-3.5 px-4">Status &amp; Canonical Scope</th>
                                <th class="py-3.5 px-4">Conflict Notes</th>
                                <th class="py-3.5 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-gray-700">
                            @forelse($records as $rec)
                                <tr class="hover:bg-gray-50/80 transition">
                                    {{-- Entity & ID --}}
                                    <td class="py-3.5 px-4">
                                        <span class="inline-block px-2 py-0.5 text-[9px] font-black rounded uppercase tracking-wider {{ $rec['type'] === 'Volunteer' ? 'bg-orange-100 text-brandOrange border border-orange-200' : 'bg-blue-100 text-blue-800 border border-blue-200' }}">
                                            {{ $rec['type'] }}
                                        </span>
                                        <div class="font-mono font-black text-gray-900 mt-1">{{ $rec['record_id'] }}</div>
                                    </td>

                                    {{-- Full Name --}}
                                    <td class="py-3.5 px-4 font-bold text-gray-900 uppercase">
                                        {{ $rec['full_name'] }}
                                    </td>

                                    {{-- Legacy Geography --}}
                                    <td class="py-3.5 px-4">
                                        <div class="space-y-0.5 text-[11px]">
                                            <div><span class="text-gray-400">State:</span> {{ $rec['legacy_state'] }}</div>
                                            <div><span class="text-gray-400">Dist:</span> <strong class="{{ strtolower($rec['legacy_district']) === 'cuddapah' ? 'text-rose-600 font-black' : '' }}">{{ $rec['legacy_district'] }}</strong></div>
                                            <div><span class="text-gray-400">Asm/Mdl:</span> {{ $rec['legacy_assembly'] }} / {{ $rec['legacy_mandal'] }}</div>
                                            <div><span class="text-gray-400">GP:</span> {{ $rec['legacy_panchayat'] }}</div>
                                        </div>
                                    </td>

                                    {{-- Legacy Cadre / Role --}}
                                    <td class="py-3.5 px-4">
                                        @if($rec['type'] === 'Volunteer')
                                            <div class="font-bold text-gray-800">{{ $rec['legacy_cadre'] }}</div>
                                            <div class="text-[10px] text-gray-500 font-mono">role: {{ $rec['legacy_role'] }}</div>
                                            @if($rec['cadre_level'])
                                                <div class="text-[10px] text-emerald-700 font-black mt-1">Scope: {{ \App\Models\Volunteer::cadreLevelToPublicTitle($rec['cadre_level']) }}</div>
                                            @endif
                                        @else
                                            <span class="text-gray-400 text-[11px]">—</span>
                                        @endif
                                    </td>

                                    {{-- Status & Canonical Scope --}}
                                    <td class="py-3.5 px-4">
                                        <span class="inline-block px-2 py-0.5 text-[10px] font-black rounded-full uppercase tracking-wider {{ $rec['geo_mapping_status'] === 'verified' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : ($rec['geo_mapping_status'] === 'needs_review' ? 'bg-rose-100 text-rose-800 border border-rose-300' : 'bg-amber-100 text-amber-800 border border-amber-300') }}">
                                            {{ $rec['geo_mapping_status'] }}
                                        </span>
                                        <div class="text-[10px] text-gray-500 mt-1 max-w-[200px] truncate" title="{{ $rec['mapped_summary'] }}">
                                            {{ $rec['mapped_summary'] }}
                                        </div>
                                    </td>

                                    {{-- Conflict Notes --}}
                                    <td class="py-3.5 px-4 text-[11px] text-gray-600 max-w-[220px]">
                                        {{ $rec['notes'] }}
                                    </td>

                                    {{-- Actions --}}
                                    <td class="py-3.5 px-4 text-right">
                                        @if($rec['type'] === 'Volunteer')
                                            <a href="{{ route('admin.volunteers.cadreEdit', $rec['id']) }}" class="inline-flex items-center gap-1 bg-brandOrange hover:bg-orange-700 text-white font-black text-[10px] px-3 py-1.5 rounded-lg shadow-sm uppercase tracking-wider transition">
                                                <span>⚙️</span> Assign Cadre
                                            </a>
                                        @else
                                            <button onclick="openMembershipVerifyModal({{ json_encode($rec) }})" class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white font-black text-[10px] px-3 py-1.5 rounded-lg shadow-sm uppercase tracking-wider transition cursor-pointer">
                                                <span>✓</span> Verify Geo
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-gray-400 font-medium">
                                        No records found matching this filter criteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>
</div>

{{-- MODAL 1: Approve Geographic Alias Modal --}}
<div id="aliasModal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl">
        <div class="flex items-center justify-between border-b border-gray-200 pb-3">
            <h3 class="font-black text-gray-900 text-sm uppercase tracking-wide">Approve Geographic Alias</h3>
            <button onclick="document.getElementById('aliasModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-700 font-black text-lg">×</button>
        </div>

        <form action="{{ route('admin.geo_review.alias') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-1">Entity Type *</label>
                <select name="entity_type" required class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-xs font-bold text-gray-800">
                    <option value="district" selected>District (e.g. Cuddapah &rarr; YSR Kadapa)</option>
                    <option value="assembly_segment">Assembly Segment</option>
                    <option value="mandal">Mandal</option>
                    <option value="panchayat">Grama Panchayat</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-1">Legacy Alias String *</label>
                <input type="text" name="alias_name" required value="Cuddapah" placeholder="e.g. Cuddapah" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs font-bold text-gray-900">
            </div>

            <div>
                <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-1">Parent State *</label>
                <select name="state_id" required class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-xs font-bold text-gray-800">
                    @foreach($states as $st)
                        <option value="{{ $st->id }}" {{ $st->name === 'Andhra Pradesh' ? 'selected' : '' }}>{{ $st->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-1">Maps To Canonical District *</label>
                <select name="canonical_id" required class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-xs font-bold text-gray-800">
                    @foreach($districts as $dst)
                        <option value="{{ $dst->id }}" {{ $dst->name === 'YSR Kadapa' ? 'selected' : '' }}>{{ $dst->name }} (#{{ $dst->id }})</option>
                    @endforeach
                </select>
            </div>

            <div class="pt-3 border-t border-gray-200 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('aliasModal').classList.add('hidden')" class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 font-black text-xs uppercase">Cancel</button>
                <button type="submit" class="px-6 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs uppercase shadow-sm">Save &amp; Approve Alias</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 2: Membership Verify Modal --}}
<div id="membershipModal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl">
        <div class="flex items-center justify-between border-b border-gray-200 pb-3">
            <h3 class="font-black text-gray-900 text-sm uppercase tracking-wide" id="membModalTitle">Verify Membership Geography</h3>
            <button onclick="document.getElementById('membershipModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-700 font-black text-lg">×</button>
        </div>

        <form id="membForm" action="" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-1">State</label>
                <select name="state_id" class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-xs font-bold text-gray-800">
                    @foreach($states as $st)
                        <option value="{{ $st->id }}">{{ $st->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-1">District</label>
                <select name="district_id" class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-xs font-bold text-gray-800">
                    @foreach($districts as $dst)
                        <option value="{{ $dst->id }}">{{ $dst->name }}</option>
                    @endforeach
                </select>
            </div>

            <input type="hidden" name="confirm_verified" value="1">

            <div class="pt-3 border-t border-gray-200 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('membershipModal').classList.add('hidden')" class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 font-black text-xs uppercase">Cancel</button>
                <button type="submit" class="px-6 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs uppercase shadow-sm">Confirm Verified Status</button>
            </div>
        </form>
    </div>
</div>

<script>
function openMembershipVerifyModal(rec) {
    document.getElementById('membModalTitle').innerText = `Verify Geography: ${rec.record_id} (${rec.full_name})`;
    document.getElementById('membForm').action = `{{ url('/admin/geo-mapping-review/update/membership') }}/${rec.id}`;
    document.getElementById('membershipModal').classList.remove('hidden');
}
</script>
@endsection
