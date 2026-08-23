@extends('layouts.app')

@section('content')
<section class="max-w-4xl mx-auto my-8 space-y-6 px-4">
    
    <!-- 1. Dashboard Header Ribbon Grid -->
    <div class="bg-white p-5 rounded-xl shadow border border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4">
        <div>
            <span class="text-xs font-bold text-brandOrange uppercase tracking-wider block">Assembly Segment Workspace</span>
            <h2 class="text-lg font-black text-brandGray mt-0.5">Assembly Constituency: {{ session('auth_volunteer_locality') }}</h2>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs font-bold text-gray-400 uppercase">Assembly Pres ID: {{ session('auth_volunteer_code') }}</span>
            <a href="/volunteer/logout" class="bg-red-50 text-red-600 font-bold text-xs py-1.5 px-4 rounded border border-red-100 hover:bg-red-100 transition uppercase tracking-wide">Sign Out</a>
        </div>
    </div>

    <!-- 2. Live Analytics Strategic Assembly Indicator Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded-xl shadow border-b-4 border-brandOrange text-center">
            <span class="text-xl block mb-1">🏛️</span>
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Total Mandal Zones</span>
            <span class="text-xl font-black text-brandGray block mt-0.5">{{ $totalAssemblyMandals ?? 7 }} Mandals</span>
        </div>
        <div class="bg-white p-4 rounded-xl shadow border-b-4 border-orange-500 text-center">
            <span class="text-xl block mb-1">👥</span>
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Total Assembly Members</span>
            <span class="text-xl font-black text-brandGray block mt-0.5">{{ $totalAssemblyMembers ?? 0 }} Members</span>
        </div>
        <div class="bg-white p-4 rounded-xl shadow border-b-4 border-green-500 text-center">
            <span class="text-xl block mb-1">🎁</span>
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Total Assembly Seva</span>
            <span class="text-xl font-black text-green-600 block mt-0.5">{{ $totalAssemblyBenefits ?? 0 }} Benefits</span>
        </div>
    </div>

    <!-- 3. Global Anti-Fraud Service Audit Desk Section Components -->
    <div class="bg-white p-5 rounded-xl shadow border border-orange-100 space-y-4">
        <h3 class="text-xs font-black text-brandOrange uppercase tracking-wider border-b border-orange-100 pb-2 flex items-center gap-1"><span>🛡️</span> Constituency Service Audit Control Desk</h3>
        
        <form action="/volunteer/dashboard/assembly" method="GET" class="flex flex-col sm:flex-row gap-3">
            <input type="text" name="audit_member_id" required maxlength="12" value="{{ request('audit_member_id') }}"
                class="block flex-1 px-4 py-2 border border-gray-300 rounded-md text-sm text-brandGray font-bold tracking-widest text-center focus:ring-brandOrange focus:border-brandOrange"
                placeholder="ENTER 12-DIGIT MEMBER ID TO AUDIT SEVA TIMELINE HISTORY">
            <button type="submit" class="bg-brandOrange text-white font-bold text-xs py-2 px-6 rounded-md uppercase tracking-wider hover:bg-opacity-90 transition shadow">
                Audit History Proof
            </button>
        </form>

        @if(request()->has('audit_member_id'))
            <div class="mt-4 pt-2 border-t border-dashed border-gray-200 space-y-4">
                @if(isset($searchedAuditMember))
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 text-xs text-brandGray">
                        <p class="font-bold text-brandDarkGray uppercase text-sm mb-1">Target Member: <span class="text-brandOrange font-black">{{ $searchedAuditMember->full_name }}</span></p>
                        <p class="font-semibold text-gray-500 uppercase">Constituency Address Mapped: <strong class="text-brandGray">{{ $searchedAuditMember->grama_panchayat }}, {{ $searchedAuditMember->mandal }}</strong></p>
                    </div>

                    @if(isset($sevaHistoryRecords) && count($sevaHistoryRecords) > 0)
                        <div class="space-y-3 pt-2">
                            @foreach($sevaHistoryRecords as $log)
                                <div class="p-3 bg-white rounded border border-gray-100 flex items-center justify-between gap-4 shadow-sm text-xs">
                                    <div class="space-y-1">
                                        <p class="font-black text-brandDarkGray uppercase">{{ $log->service_type }}</p>
                                        <p class="text-gray-400 font-bold uppercase text-[10px]">Delivered On: <span class="text-brandGray">{{ date('d-m-Y h:i A', strtotime($log->created_at)) }}</span> | Verified By Volunteer Code: <span class="text-brandOrange font-bold">{{ $log->volunteer_id }}</span></p>
                                    </div>
                                    <div class="w-12 h-12 bg-gray-100 border border-gray-200 rounded overflow-hidden flex-shrink-0 shadow-sm">
                                        <img src="{{ asset('storage/' . $log->proof_photo_path) }}" class="w-full h-full object-cover">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="p-3 bg-red-50 border border-red-100 rounded text-red-700 font-bold text-center">Zero service logs found inside database records tracking for this member code.</p>
                    @endif
                @else
                    <p class="p-3 bg-red-50 border border-red-100 rounded text-red-700 font-bold text-center">Invalid Membership ID reference key entered.</p>
                @endif
            </div>
        @endif
    </div>

    <!-- 4. Section A: Mandal Presidents (Constituency Tier 2 Force) Directory Table -->
    <div class="bg-white p-5 rounded-xl shadow border border-gray-100 space-y-3">
        <h3 class="text-xs font-black text-brandGray uppercase tracking-wider border-b border-gray-100 pb-2">📂 Mandal Presidents Subordinate Directory</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs text-brandGray">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-gray-400 font-bold uppercase text-[10px]">
                        <th class="p-2.5">Mandal Jurisdiction</th>
                        <th class="p-2.5">Mandal President Name</th>
                        <th class="p-2.5">Status</th>
                        <th class="p-2.5">Contact Phone</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @if(isset($subordinateUnits) && count($subordinateUnits) > 0)
                        @foreach($subordinateUnits as $unit)
                            <tr class="hover:bg-gray-50 font-medium">
                                <td class="p-2.5 font-bold text-brandOrange uppercase">{{ $unit['unit_name'] }}</td>
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
                    @elseif(isset($mandalPresidents) && count($mandalPresidents) > 0)
                        @foreach($mandalPresidents as $mp)
                            <tr class="hover:bg-gray-50 font-medium">
                                <td class="p-2.5 font-bold text-brandOrange uppercase">{{ $mp->locality }}</td>
                                <td class="p-2.5 uppercase text-brandDarkGray font-bold">{{ $mp->full_name ?? 'ASSIGNED MANDAL PRES' }}</td>
                                <td class="p-2.5"><span class="inline-block px-2 py-0.5 text-[9px] font-black rounded uppercase bg-emerald-100 text-emerald-800">Assigned</span></td>
                                <td class="p-2.5 font-mono text-[11px]">+91 {{ $mp->phone }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="4" class="p-4 text-center text-gray-400 font-medium">No active mandals mapped inside this constituency boundaries yet.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    <!-- 5. Section C: Assembly Constituency Multi-Village Mass Activity Gallery Component -->
    <div class="bg-white p-5 rounded-xl shadow border border-gray-100 space-y-3">
        <div class="border-b border-gray-100 pb-2">
            <h3 class="text-xs font-black text-brandGray uppercase tracking-wider flex items-center gap-1"><span>🖼️</span> Assembly Constituency Mass Activity Photo Gallery</h3>
            <p class="text-[10px] text-gray-400 font-bold uppercase mt-0.5">Constituency visual audit desk to review entire group activities and photos published from all village boundaries</p>
        </div>
        
        <!-- Live Matrix Display mapping group logs compiled directly from all villages under this assembly segment -->
        @if(isset($assemblyGroupEvents) && count($assemblyGroupEvents) > 0)
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                @foreach($assemblyGroupEvents as $event)
                    <div class="bg-gray-50 border border-gray-200 p-2 rounded shadow-sm text-center space-y-1">
                        <!-- High resolution pixels compressed dynamically down to optimized 30KB-50KB memory targets -->
                        <div class="w-full h-24 bg-gray-200 rounded overflow-hidden shadow-inner border border-gray-300">
                            <img src="{{ asset('storage/' . $event->group_photo_path) }}" class="w-full h-full object-cover" alt="Group Event Album Asset">
                        </div>
                        <p class="font-black text-brandOrange uppercase text-[11px] truncate px-0.5 leading-none pt-1">{{ $event->grama_panchayat }}</p>
                        <p class="font-bold text-brandDarkGray uppercase text-[10px] truncate px-0.5 leading-none">{{ $event->event_title }}</p>
                        <span class="text-[9px] text-gray-400 font-bold uppercase tracking-wider block border-t border-gray-200/60 pt-1">On: {{ date('d-m-Y', strtotime($event->created_at)) }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Fallback Blank Indicator Container Slot -->
            <div class="p-6 bg-gray-50 rounded border border-dashed border-gray-200 text-center text-xs text-gray-400 font-medium">
                No mass activity group events or community albums published from any village council zone under this assembly constituency boundaries yet.
            </div>
        @endif
    </div>

</section>
@endsection
