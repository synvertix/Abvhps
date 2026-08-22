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
                <span class="bg-amber-100 text-amber-800 text-[9px] font-black px-2.5 py-0.5 rounded border border-amber-200 tracking-widest uppercase shadow-sm">Pending Incomplete Applications</span>
            </div>
            <div class="text-right text-[10px] font-mono font-black text-gray-500">
                System Sync: {{ \Carbon\Carbon::now()->format('d-M-Y H:i') }} IST
            </div>
        </header>

        <!-- Dynamic Content Workspace Container -->
        <main class="flex-1 overflow-y-auto p-6 space-y-6">
            
            <!-- Header Title -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                <div>
                    <h3 class="text-xs font-black text-brandGray uppercase tracking-wider flex items-center gap-1.5">
                        ⏳ Pending Incomplete Membership Applications (Paid Membership Fee)
                    </h3>
                    <p class="text-[11px] text-gray-500 font-semibold mt-0.5">Devotees who paid the membership fee but dropped off before submitting final profile details.</p>
                </div>
                <div class="text-xs font-bold text-amber-700 bg-amber-50 border border-amber-200 px-3 py-1.5 rounded-lg flex items-center gap-1.5">
                    <span>💡</span> Re-verifying phone on <a href="{{ url('/membership') }}" target="_blank" class="underline font-black hover:text-brandOrange">/membership</a> auto-resumes the application!
                </div>
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
                <form action="{{ route('admin.membership.pending') }}" method="GET" class="flex gap-2">
                    <input type="text" name="search" class="flex-1 border border-gray-300 rounded-lg px-4 py-2 text-xs font-semibold focus:outline-none focus:border-brandOrange" placeholder="Search by Member ID, Phone Number, or Transaction ID..." value="{{ $searchQuery ?? '' }}">
                    <button type="submit" class="bg-brandOrange hover:bg-orange-700 text-white font-black text-[11px] px-5 py-2 rounded-lg shadow-sm uppercase tracking-wide transition">
                        Search Matrix
                    </button>
                    @if(!empty($searchQuery))
                        <a href="{{ route('admin.membership.pending') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-black text-[11px] px-4 py-2 rounded-lg uppercase tracking-wide transition border border-gray-300 flex items-center">
                            Clear
                        </a>
                    @endif
                </form>
            </div>

            <!-- Central Pending Ledger Table Grid -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-left text-xs font-semibold text-gray-700">
                        <thead class="bg-gray-100 text-[10px] font-black uppercase text-gray-600 tracking-wider text-center">
                            <tr>
                                <th class="px-4 py-3">S.No</th>
                                <th class="px-6 py-3 text-left">Member Account Status</th>
                                <th class="px-6 py-3">Numeric Membership ID</th>
                                <th class="px-6 py-3 text-left">Registered Phone</th>
                                <th class="px-4 py-3">Fee Status</th>
                                <th class="px-4 py-3">Txn Token</th>
                                <th class="px-4 py-3">Paid Date</th>
                                <th class="px-6 py-3 text-center">Resume & Follow-up Actions</th>
                                <th class="px-4 py-3">Delete</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white text-center">
                            @forelse($members as $index => $member)
                                <tr class="hover:bg-amber-50/30 transition-colors">
                                    <td class="px-4 py-3.5 text-gray-500 font-mono">
                                        {{ $loop->iteration }}
                                    </td>
                                    
                                    <!-- Member Profile Status / Placeholder -->
                                    <td class="px-6 py-3.5 text-left flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-amber-100 border border-amber-200 flex items-center justify-center text-amber-700 text-sm font-bold shrink-0">
                                            ⏳
                                        </div>
                                        <div>
                                            <span class="font-bold text-gray-700 uppercase tracking-wide block">
                                                {{ $member->full_name ?: 'Details Not Submitted' }}
                                            </span>
                                            <span class="text-[9px] font-bold text-amber-600 uppercase tracking-wider bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200 inline-block mt-0.5">
                                                Form Incomplete (Stage 2)
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Numeric Membership ID -->
                                    <td class="px-6 py-3.5 font-mono text-brandOrange font-black text-sm tracking-wider">
                                        {{ $member->membership_id ? implode(' ', str_split($member->membership_id, 4)) : 'PENDING' }}
                                    </td>

                                    <!-- Registered Mobile -->
                                    <td class="px-6 py-3.5 text-left">
                                        <div class="font-mono text-gray-900 font-bold text-xs flex items-center gap-1.5">
                                            <span>📱</span> {{ $member->phone }}
                                        </div>
                                        <div class="text-[10px] text-gray-400 font-mono">{{ $member->email ?? 'No email on file' }}</div>
                                    </td>

                                    <!-- Payment Fee Status -->
                                    <td class="px-4 py-3.5">
                                        <span class="bg-emerald-100 text-emerald-800 text-[9px] font-black px-2 py-0.5 rounded border border-emerald-200 uppercase tracking-wider">
                                            ✓ PAID
                                        </span>
                                    </td>

                                    <!-- Txn Token -->
                                    <td class="px-4 py-3.5 font-mono text-[10px] text-gray-600 font-bold">
                                        {{ $member->payment_id ?? 'TXN-DIRECT' }}
                                    </td>

                                    <!-- Paid Date -->
                                    <td class="px-4 py-3.5 font-mono text-[10px] text-gray-500">
                                        {{ $member->created_at ? \Carbon\Carbon::parse($member->created_at)->format('d-M-Y H:i') : 'N/A' }}
                                    </td>

                                    <!-- Follow-up Action Buttons -->
                                    <td class="px-6 py-3.5">
                                        <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                            <!-- WhatsApp Reminder Link -->
                                            @php
                                                $waText = "Namaste Devotee! You have completed the ABVHPS Membership Fee (ID: " . $member->membership_id . "). Please login with your mobile number (" . $member->phone . ") at " . url('/membership') . " to submit your photograph and complete your lifetime membership card.";
                                                $waUrl = "https://api.whatsapp.com/send?phone=91" . $member->phone . "&text=" . urlencode($waText);
                                            @endphp
                                            <a href="{{ $waUrl }}" target="_blank" class="bg-emerald-600 hover:bg-emerald-700 text-white font-black text-[9px] px-2.5 py-1 rounded shadow-sm uppercase transition flex items-center gap-1" title="Send WhatsApp Reminder to Member">
                                                <span>💬</span> WhatsApp
                                            </a>

                                            <!-- SMS / Call Action -->
                                            <a href="tel:{{ $member->phone }}" class="bg-blue-600 hover:bg-blue-700 text-white font-black text-[9px] px-2.5 py-1 rounded shadow-sm uppercase transition flex items-center gap-1" title="Call Devotee Phone">
                                                <span>📞</span> Call
                                            </a>

                                            <!-- Copy Resume Portal Link -->
                                            <button onclick="navigator.clipboard.writeText('{{ url('/membership') }}'); alert('Resume Portal Link copied to clipboard:\n{{ url('/membership') }}\n\nShare this with {{ $member->phone }} to resume login with OTP.');" class="bg-gray-700 hover:bg-gray-800 text-white font-black text-[9px] px-2.5 py-1 rounded shadow-sm uppercase transition flex items-center gap-1" title="Copy Resume Link">
                                                <span>🔗</span> Copy Link
                                            </button>
                                        </div>
                                    </td>

                                    <!-- Delete Row Action -->
                                    <td class="px-4 py-3.5">
                                        <form action="{{ route('admin.membership.delete', $member->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this pending record permanently?');" class="block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bg-rose-500 hover:bg-rose-600 text-white font-black text-[9px] px-2.5 py-1 rounded shadow-sm uppercase transition">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-12 text-center font-bold text-gray-400 uppercase tracking-wider">
                                        <span class="text-2xl block mb-1">🎉</span>
                                        No pending incomplete applications found in the queue. All paid members have completed their registrations!
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
