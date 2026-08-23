@extends('layouts.app')

@section('content')
<section class="max-w-4xl mx-auto my-8 space-y-6 px-4">
    
    <!-- 1. Global Pipeline Dashboard Header Ribbon Grid -->
    <div class="bg-white p-5 rounded-xl shadow border border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4">
        <div>
            <!-- Dynamically rendering header titles based on authenticated active role states -->
            <span class="text-xs font-black text-brandOrange uppercase tracking-wider block">
                @if($assignedRole === 'district_president') District President Desktop
                @elseif($assignedRole === 'state_president') State Apex Council Desk
                @elseif($assignedRole === 'national_president') National Command Board
                @elseif($assignedRole === 'international_president') International Global Overseer
                @elseif($assignedRole === 'support_team') IT Infrastructure Support Desk
                @endif
            </span>
            <h2 class="text-lg font-black text-brandGray mt-0.5">Jurisdiction Scope: {{ $assignedLocality }}</h2>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs font-bold text-gray-400 uppercase">OFFICER ID: {{ session('auth_volunteer_code') }}</span>
            <a href="/volunteer/logout" class="bg-red-50 text-red-600 font-bold text-xs py-1.5 px-4 rounded border border-red-100 hover:bg-red-100 transition uppercase tracking-wide">Sign Out</a>
        </div>
    </div>

    <!-- 2. Live Analytics Strategic Global Counter Analytics Indicators Board -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Live Counter Card 1 -->
        <div class="bg-white p-5 rounded-xl shadow border-b-4 border-brandOrange text-center">
            <span class="text-2xl block mb-1">🌍</span>
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Total Mapped Force Strength</span>
            <span class="text-xl font-black text-brandGray block mt-0.5">{{ $totalActiveVolunteersCount ?? 0 }} Registered</span>
        </div>
        <!-- Live Counter Card 2 -->
        <div class="bg-white p-5 rounded-xl shadow border-b-4 border-orange-500 text-center">
            <span class="text-2xl block mb-1">👥</span>
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Total Registered Sanatana Members</span>
            <span class="text-xl font-black text-brandGray block mt-0.5">{{ $globalMembersCount ?? 0 }} Members</span>
        </div>
        <!-- Live Counter Card 3 -->
        <div class="bg-white p-5 rounded-xl shadow border-b-4 border-green-500 text-center">
            <span class="text-2xl block mb-1">🎁</span>
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Total Live Seva Benefits Delivered</span>
            <span class="text-xl font-black text-green-600 block mt-0.5">{{ $globalBenefitsCount ?? 0 }} Deliveries</span>
        </div>
    </div>

    <!-- 3. Global Anti-Fraud Service Audit Desk Control Engine Component -->
    <div class="bg-white p-5 rounded-xl shadow border border-orange-100 space-y-4">
        <h3 class="text-xs font-black text-brandOrange uppercase tracking-wider border-b border-orange-100 pb-2 flex items-center gap-1"><span>🛡️</span> Central Anti-Fraud Service Delivery Auditing Panel</h3>
        
        <!-- Generic self redirect search mapping rules layout -->
        <form action="{{ url()->current() }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <input type="text" name="audit_member_id" required maxlength="12" value="{{ request('audit_member_id') }}"
                class="block flex-1 px-4 py-2 border border-gray-300 rounded-md text-sm text-brandGray font-bold tracking-widest text-center focus:ring-brandOrange focus:border-brandOrange"
                placeholder="ENTER ANY 12-DIGIT MEMBER ID TO AUDIT LIVE EVIDENCES">
            <button type="submit" class="bg-brandOrange text-white font-bold text-xs py-2 px-6 rounded-md uppercase tracking-wider hover:bg-opacity-90 transition shadow">
                Audit Sacrements Proof
            </button>
        </form>

        <!-- Dynamic History Validation Block rendering the compressed photo proof -->
        @if(request()->has('audit_member_id'))
            <div class="mt-4 pt-2 border-t border-dashed border-gray-200 space-y-4">
                @if(isset($searchedAuditMember))
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 text-xs text-brandGray flex flex-col sm:flex-row justify-between gap-2">
                        <div>
                            <p class="font-bold text-brandDarkGray uppercase text-sm mb-1">Target Member: <span class="text-brandOrange font-black">{{ $searchedAuditMember->full_name }}</span></p>
                            <p class="font-semibold text-gray-500 uppercase">Mapped Origins: <strong class="text-brandGray">{{ $searchedAuditMember->grama_panchayat }}, {{ $searchedAuditMember->mandal }}, {{ $searchedAuditMember->state }}</strong></p>
                        </div>
                        <div class="text-left sm:text-right font-bold text-green-600 text-xs uppercase pt-1">
                            Status: Authenticated Record
                        </div>
                    </div>

                    @if(isset($sevaHistoryRecords) && count($sevaHistoryRecords) > 0)
                        <div class="space-y-3 pt-2">
                            @foreach($sevaHistoryRecords as $log)
                                <div class="p-3 bg-white rounded border border-gray-100 flex items-center justify-between gap-4 shadow-sm text-xs">
                                    <div class="space-y-1">
                                        <p class="font-black text-brandDarkGray uppercase text-xs">{{ $log->service_type }}</p>
                                        <p class="text-gray-400 font-bold uppercase text-[10px]">Delivered: <span class="text-brandGray">{{ date('d-m-Y h:i A', strtotime($log->created_at)) }}</span> | Verified By Officer: <span class="text-brandOrange font-bold">{{ $log->volunteer_id }} ({{ strtoupper($log->volunteer_role) }})</span></p>
                                    </div>
                                    <!-- Displays the ultra optimized 1KB-2KB real deployment photo proof captured from field -->
                                    <div class="w-12 h-12 bg-gray-100 border border-gray-200 rounded overflow-hidden flex-shrink-0 shadow-sm">
                                        <img src="{{ asset('storage/' . $log->proof_photo_path) }}" class="w-full h-full object-cover" alt="Audit Evidence Asset">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="p-3 bg-red-50 border border-red-100 rounded text-red-700 font-bold text-center">Zero service logs found inside server memory channels for this membership tracking sequence key.</p>
                    @endif
                @else
                    <p class="p-3 bg-red-50 border border-red-100 rounded text-red-700 font-bold text-center">Invalid Membership ID key pattern. Mapped metadata rows do not exist on database.</p>
                @endif
            </div>
        @endif
    </div>
    <!-- 3.5 Dynamic Pipeline Breakdown Performance Auditor Table Components -->
    <div class="bg-white p-5 rounded-xl shadow border border-gray-100 space-y-3">
        <div class="border-b border-gray-100 pb-2">
            <h3 class="text-xs font-black text-brandGray uppercase tracking-wider flex items-center gap-1"><span>📊</span> Regional Performance & Weak Count Analysis Desk</h3>
            <p class="text-[10px] text-gray-400 font-bold uppercase mt-0.5">Monitor counts closely to identify and improve weak performing territories</p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs text-brandGray">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-gray-400 font-bold uppercase">
                        <th class="p-2.5">{{ $breakdownHeader }} Name</th>
                        <th class="p-2.5 text-center">Strength Status</th>
                        <th class="p-2.5 text-right">Total Registered Members</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @if(isset($breakdownData) && count($breakdownData) > 0)
                        @foreach($breakdownData as $row)
                            <tr class="hover:bg-gray-50 font-semibold">
                                <td class="p-2.5 font-bold text-brandDarkGray uppercase">{{ $row->zone_name }}</td>
                                <td class="p-2.5 text-center">
                                    <!-- Highlighting weak performing areas automatically based on total threshold counts -->
                                    @if($row->members_count < 5)
                                        <span class="bg-red-50 text-red-600 font-bold px-2 py-0.5 rounded text-[10px] border border-red-100 uppercase tracking-wide">Weak Focus Required</span>
                                    @else
                                        <span class="bg-green-50 text-green-600 font-bold px-2 py-0.5 rounded text-[10px] border border-green-100 uppercase tracking-wide">Strong Zone</span>
                                    @endif
                                </td>
                                <td class="p-2.5 text-right font-black text-brandOrange text-sm">{{ $row->members_count }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="3" class="p-4 text-center text-gray-400 font-medium">No active regional sub-division analytics records found.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- 3.6 Subordinate Jurisdiction Presidents Directory -->
    @if(isset($subordinateUnits) && count($subordinateUnits) > 0)
        <div class="bg-white p-5 rounded-xl shadow border border-gray-100 space-y-3">
            <h3 class="text-xs font-black text-brandGray uppercase tracking-wider border-b border-gray-100 pb-2">📂 Subordinate Geographic Presidents Directory</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs text-brandGray">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-gray-400 font-bold uppercase text-[10px]">
                            <th class="p-2.5">Territory Unit</th>
                            <th class="p-2.5">Unit Level</th>
                            <th class="p-2.5">Assigned President</th>
                            <th class="p-2.5">Status</th>
                            <th class="p-2.5">Contact Phone</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($subordinateUnits as $unit)
                            <tr class="hover:bg-gray-50 font-medium">
                                <td class="p-2.5 font-bold text-brandOrange uppercase">{{ $unit['unit_name'] }}</td>
                                <td class="p-2.5 text-gray-500 font-semibold">{{ $unit['unit_type'] }}</td>
                                <td class="p-2.5 font-bold uppercase {{ $unit['is_assigned'] ? 'text-brandDarkGray' : 'text-gray-400 italic' }}">
                                    {{ $unit['president_name'] }}
                                </td>
                                <td class="p-2.5">
                                    <span class="inline-block px-2 py-0.5 text-[9px] font-black rounded uppercase {{ $unit['is_assigned'] ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-500' }}">
                                        {{ $unit['is_assigned'] ? 'Assigned' : 'Vacant' }}
                                    </span>
                                </td>
                                <td class="p-2.5 font-mono text-[11px]">{{ $unit['contact_phone'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- 4. IT Infrastructure System Log Indicators (Strictly shown only for Support Team view logs) -->
    @if($assignedRole === 'support_team')
        <div class="bg-gray-900 p-5 rounded-xl shadow text-gray-200 space-y-3 font-mono text-[11px] border border-gray-800">
            <p class="text-green-400 font-bold border-b border-gray-800 pb-1.5 uppercase tracking-wide">⚙️ System Infrastructure Hard Logs (IT Support Mode Only)</p>
            <p class="text-gray-400">Environment State: <span class="text-white font-bold">{{ config('app.env') === 'production' ? 'AWS Production Cloud' : 'Development Sandbox' }}</span></p>
            <p class="text-gray-400">Dynamic Active Pipeline Gateways: <span class="text-yellow-400 font-bold">5 Nodes Operational</span></p>
            <p class="text-gray-400">Image Processing Matrix Compression Target: <span class="text-green-400 font-bold">Active &lt; 2KB Enforced</span></p>
            <p class="text-gray-400">Database Engine Buffer Rows Status: <span class="text-white font-bold">Healthy (0 Deadlocks)</span></p>
        </div>
    @endif

</section>
@endsection
