@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100/60 flex flex-col md:flex-row select-none">
    
    <!-- BLOCK 1: MASTER ADMINISTRATIVE LEFT SIDEBAR CONTROLLER -->
    @include('admin.partials.sidebar')

    <!-- BLOCK 2: MASTER MAIN WORKSPACE VIEWPORT DESK -->
    <div class="flex-1 flex flex-col overflow-hidden">
        
        <!-- Workspace Top Status Banner Navbar -->
        <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                @include('admin.partials.header_button')
                <span class="text-sm font-black text-brandGray uppercase tracking-wider">System View:</span>
                <span class="bg-orange-100 text-brandOrange text-[9px] font-black px-2.5 py-0.5 rounded border border-orange-200 tracking-widest uppercase shadow-sm">Membership Grid</span>
            </div>
            <div class="text-right text-[10px] font-mono font-black text-gray-500">
                System Sync: {{ \Carbon\Carbon::now()->format('d-M-Y H:i') }} IST
            </div>
        </header>

        <!-- Dynamic Content Workspace Container -->
        <main class="flex-1 overflow-y-auto p-6 space-y-6">
            
            <!-- Header Title -->
            <div class="flex justify-between items-center">
                <h3 class="text-xs font-black text-brandGray uppercase tracking-wider flex items-center gap-1.5">
                    👥 Approved Lifetime Membership Ledger Matrix
                </h3>
            </div>

            <!-- Flash Status Messages -->
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl text-xs font-bold shadow-sm flex items-center gap-2">
                    <span class="text-base">✓</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-xs font-bold shadow-sm flex items-center gap-2">
                    <span class="text-base">⚠️</span>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Search Toolbar Matrix -->
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                <form action="{{ route('admin.membership.ledger') }}" method="GET" class="flex gap-2">
                    <input type="text" name="search" class="flex-1 border border-gray-300 rounded-lg px-4 py-2 text-xs font-semibold focus:outline-none focus:border-brandOrange" placeholder="Search by Member ID, Name, Phone Number, or District..." value="{{ $searchQuery ?? '' }}">
                    <button type="submit" class="bg-brandOrange hover:bg-orange-700 text-white font-black text-[11px] px-5 py-2 rounded-lg shadow-sm uppercase tracking-wide transition">
                        Search Matrix
                    </button>
                </form>
            </div>
            <!-- Central Ledger Table Grid -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-left text-xs font-semibold text-gray-700">
                        <thead class="bg-gray-100 text-[10px] font-black uppercase text-gray-600 tracking-wider text-center">
                            <tr>
                                <th class="px-4 py-3">S.No</th>
                                <th class="px-6 py-3 text-left">Member Roster Account Profile</th>
                                <th class="px-6 py-3">Numeric Membership ID</th>
                                <th class="px-6 py-3 text-left">Registered Mobile / Email</th>
                                <th class="px-4 py-3">Type</th>
                                <th class="px-4 py-3">View</th>
                                <th class="px-4 py-3">ID</th>
                                <th class="px-4 py-3">Edit</th>
                                <th class="px-4 py-3">Delete</th>
                            </tr>
                        </thead>
                                                <tbody class="divide-y divide-gray-200 bg-white text-center">
                            @forelse($members as $index => $member)
                                <tr class="hover:bg-gray-50/60 transition-colors">
                                    <td class="px-4 py-3.5 text-gray-500 font-mono">
                                        {{ $loop->iteration }}
                                    </td>
                                    <td class="px-6 py-3.5 text-left font-bold text-gray-900 uppercase tracking-wide flex items-center gap-3">
                                        @if($member->photo_path)
                                            <img src="{{ asset('storage/' . $member->photo_path) }}" class="w-8 h-10 rounded border object-cover" alt="Member Photo">
                                        @else
                                            <div class="w-8 h-10 rounded bg-gray-100 border flex items-center justify-content-center text-gray-400 text-[10px]">No Img</div>
                                        @endif
                                        <div>
                                            <span class="block">{{ $member->full_name }}</span>
                                            <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                                @if($member->hasVerifiedIdentity())
                                                    <span class="text-[9px] font-black text-emerald-700 bg-emerald-50 border border-emerald-200 px-1.5 py-0.5 rounded uppercase">
                                                        {{ $member->getIdentityBadgeLabel() }}
                                                    </span>
                                                    <span class="text-[10px] font-mono text-gray-400 normal-case tracking-normal">{{ $member->getIdentityDocumentMaskedLabel() }}</span>
                                                @else
                                                    <span class="text-[9px] font-bold text-amber-700 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded uppercase">
                                                        Identity Pending
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3.5 font-mono text-brandOrange font-black text-sm tracking-wider">
                                        {{ $member->membership_id ? implode(' ', str_split($member->membership_id, 4)) : 'PENDING' }}
                                    </td>
                                    <td class="px-6 py-3.5 text-left">
                                        <div class="font-mono text-gray-900 font-bold">{{ $member->phone }}</div>
                                        <div class="text-[10px] text-gray-400 font-normal normal-case font-mono">{{ $member->email ?? 'no email' }}</div>
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <span class="bg-green-50 text-green-600 text-[9px] font-black px-2 py-0.5 rounded border border-green-100 uppercase tracking-wider">
                                            LIFETIME
                                        </span>
                                    </td>
                                    
                                    <!-- 👇 New Connected Action Buttons Block Starts Here -->
                                    <td class="px-2 py-3.5">
                                        <a href="{{ route('admin.membership.view', $member->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white font-black text-[9px] px-3 py-1 rounded shadow-sm uppercase transition block text-center">
                                            View
                                        </a>
                                    </td>
                                    <td class="px-2 py-3.5">
                                        <a href="{{ route('admin.membership.idcard', $member->id) }}" target="_blank" class="bg-yellow-500 hover:bg-yellow-600 text-white font-black text-[9px] px-3 py-1 rounded shadow-sm uppercase transition block text-center">
                                            Id
                                        </a>
                                    </td>
                                    <td class="px-2 py-3.5">
                                        <a href="{{ route('admin.membership.edit', $member->id) }}" class="bg-orange-500 hover:bg-orange-600 text-white font-black text-[9px] px-3 py-1 rounded shadow-sm uppercase transition block text-center">
                                            Edit
                                        </a>
                                    </td>
                                    <td class="px-2 py-3.5">
                                        <form action="{{ route('admin.membership.delete', $member->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this member permanently?');" class="block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bg-rose-500 hover:bg-rose-600 text-white font-black text-[9px] px-3 py-1 rounded shadow-sm uppercase transition w-full">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                    <!-- 👆 New Connected Action Buttons Block Ends Here -->
                                    
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-10 text-center font-bold text-gray-400 uppercase tracking-wider">
                                        No active membership records found inside the matrix.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>
            </div>

            <!-- Pagination Grid Node -->
            @if($members->hasPages())
                <div class="p-4 bg-white rounded-xl border border-gray-200 flex justify-center shadow-sm">
                    {{ $members->appends(['search' => $searchQuery])->links() }}
                </div>
            @endif

        </main>
    </div>
</div>
@endsection
