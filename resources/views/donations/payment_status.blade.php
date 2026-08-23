@extends('layouts.app')

@section('title', 'Donation Confirmation & Receipt | ABVHPS')
@section('meta_description', 'Official transaction confirmation and 80G tax donation receipt statement for contributions made to Akhanda Bharatha Viswa Hindu Parirakshana Samiti.')

@section('content')
@php
    $status = strtolower($donation->payment_status ?? 'pending');
    $isPaid = $status === 'paid';
    $isPending = in_array($status, ['pending', 'processing']);
    $isFailed = in_array($status, ['failed', 'cancelled', 'expired']);
    $gatewayLabel = match(strtolower($donation->payment_gateway ?? 'manual')) {
        'cashfree' => 'Cashfree Payments',
        'razorpay' => 'Razorpay Payments',
        default => 'Payment Gateway'
    };
    $txRef = $donation->gateway_payment_id ?? $donation->gateway_order_id ?? $donation->payment_reference ?? ('ABVHPS-TX-' . $donation->id);
    $paidTimestamp = $donation->paid_at 
        ? \Carbon\Carbon::parse($donation->paid_at)->timezone('Asia/Kolkata')->format('d M Y, h:i A') 
        : \Carbon\Carbon::parse($donation->created_at)->timezone('Asia/Kolkata')->format('d M Y, h:i A');
@endphp

{{-- Main Page Wrapper with Warm Spiritual Ivory Canvas --}}
<div class="relative min-h-[calc(100vh-140px)] bg-[#FAF8F5] text-slate-800 antialiased overflow-hidden py-5 sm:py-8 px-4 sm:px-6 lg:px-8 selection:bg-orange-500 selection:text-white">

    {{-- Subtle Decorative Sacred Motif Background --}}
    <div class="absolute inset-0 pointer-events-none select-none overflow-hidden opacity-[0.03]" aria-hidden="true">
        <svg class="absolute -top-16 -left-16 w-80 h-80 sm:w-96 sm:h-96 text-orange-950" viewBox="0 0 200 200" fill="currentColor">
            <path d="M100 0 C110 50 150 90 200 100 C150 110 110 150 100 200 C90 150 50 110 0 100 C50 90 90 50 100 0 Z" />
            <circle cx="100" cy="100" r="40" fill="none" stroke="currentColor" stroke-width="3"/>
        </svg>
        <svg class="absolute -bottom-20 -right-20 w-80 h-80 sm:w-96 sm:h-96 text-orange-950" viewBox="0 0 200 200" fill="currentColor">
            <path d="M100 0 C110 50 150 90 200 100 C150 110 110 150 100 200 C90 150 50 110 0 100 C50 90 90 50 100 0 Z" />
            <circle cx="100" cy="100" r="45" fill="none" stroke="currentColor" stroke-width="3"/>
        </svg>
    </div>

    {{-- Soft Ambient Glow --}}
    <div class="absolute top-10 left-1/2 -translate-x-1/2 w-96 h-96 bg-amber-200/20 rounded-full blur-3xl pointer-events-none" aria-hidden="true"></div>

    <div class="relative max-w-4xl mx-auto space-y-4 sm:space-y-6">

        {{-- 1. COMPACT HERO CONFIRMATION COMPOSITION --}}
        <div class="bg-white/95 backdrop-blur-md rounded-2xl sm:rounded-3xl border border-amber-900/10 shadow-[0_10px_30px_-10px_rgba(217,119,6,0.06)] p-5 sm:p-7 lg:p-8 transition-all duration-300">
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 lg:gap-8 items-center">
                
                {{-- Left Column: Success Confirmation & Text --}}
                <div class="lg:col-span-8 text-center lg:text-left space-y-3 sm:space-y-3.5">
                    
                    {{-- Status Tag with Centered Mini Tick --}}
                    <div class="flex items-center justify-center lg:justify-start gap-2.5">
                        {{-- Small Centered Success Seal --}}
                        <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full shrink-0 flex items-center justify-center shadow-xs ring-2
                            @if($isPaid) bg-emerald-500 text-white ring-emerald-100
                            @elseif($isPending) bg-amber-500 text-white ring-amber-100
                            @else bg-rose-500 text-white ring-rose-100 @endif">
                            @if($isPaid)
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path>
                                </svg>
                            @elseif($isPending)
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 animate-spin" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            @else
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            @endif
                        </div>

                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                            @if($isPaid) bg-emerald-50 text-emerald-800 border border-emerald-200
                            @elseif($isPending) bg-amber-50 text-amber-800 border border-amber-200
                            @else bg-rose-50 text-rose-800 border border-rose-200 @endif">
                            <span>
                                @if($isPaid)
                                    Payment Successful
                                @elseif($isPending)
                                    Verification In Progress
                                @else
                                    Payment Incomplete
                                @endif
                            </span>
                            <span class="text-amber-700/50">|</span>
                            <span class="text-[11px] font-medium normal-case">🕉️ ABVHPS</span>
                        </div>
                    </div>

                    {{-- Main Titles --}}
                    <div class="space-y-1">
                        <h1 class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-slate-900 tracking-tight font-serif">
                            @if($isPaid)
                                Thank You!
                            @elseif($isPending)
                                Awaiting Payment Confirmation
                            @else
                                Transaction Incomplete
                            @endif
                        </h1>
                        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed font-normal max-w-lg mx-auto lg:mx-0">
                            @if($isPaid)
                                Your sacred contribution has been received with gratitude. Your support empowers Sanatana Dharma and humanitarian seva across Bharat.
                            @elseif($isPending)
                                We are verifying payment clearance with {{ $gatewayLabel }}. Your status will update once confirmed.
                            @else
                                The transaction could not be completed. No amount was charged or will be automatically refunded by your bank.
                            @endif
                        </p>
                    </div>

                    {{-- Verification Trust Badge --}}
                    @if($isPaid)
                        <div class="pt-1 flex flex-wrap items-center justify-center lg:justify-start gap-3 text-xs text-slate-500">
                            <span class="inline-flex items-center gap-1 text-emerald-700 font-semibold bg-emerald-50 px-2.5 py-0.5 rounded-md border border-emerald-100 text-[11px]">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                Verified via {{ $gatewayLabel }}
                            </span>
                            <span class="inline-flex items-center gap-1 text-slate-500 font-medium text-[11px]">
                                🛡️ 100% Encrypted &amp; Authenticated
                            </span>
                        </div>
                    @endif

                </div>

                {{-- Right Column: Interactive 3D Sacred Emblem (Compact & Alive) --}}
                <div class="lg:col-span-4 flex items-center justify-center">
                    <div class="relative w-40 h-40 sm:w-48 sm:h-48 flex items-center justify-center rounded-2xl bg-gradient-to-b from-amber-50/70 to-orange-50/30 border border-amber-200/50 shadow-inner p-2 group select-none">
                        
                        {{-- Soft Halo --}}
                        <div class="absolute inset-2 rounded-full bg-amber-300/20 blur-lg pointer-events-none"></div>
                        
                        {{-- 3D Canvas Container --}}
                        <div id="abvhps-3d-emblem-container" class="relative z-10 w-full h-full flex items-center justify-center cursor-grab active:cursor-grabbing" title="Interactive 3D Sacred Emblem (Move mouse or drag to rotate)" aria-label="Interactive 3D Sacred Emblem">
                            
                            {{-- Progressive Enhancement Fallback --}}
                            <div id="abvhps-fallback-emblem" class="w-full h-full flex flex-col items-center justify-center text-center p-2">
                                <div class="relative w-28 h-28 sm:w-32 sm:h-32 rounded-full border border-dashed border-amber-400/80 flex items-center justify-center bg-white shadow-xs">
                                    <img src="{{ asset('images/logo_abvhps.png') }}" onerror="this.src='{{ asset('images/ABVHPS_LOGO.jpg') }}'" alt="ABVHPS Emblem" class="w-16 h-16 sm:w-20 sm:h-20 object-contain drop-shadow-xs" />
                                </div>
                            </div>
                            
                            {{-- Canvas Hook for Three.js --}}
                            <canvas id="abvhps-three-canvas" class="hidden absolute inset-0 w-full h-full rounded-xl touch-none"></canvas>
                        </div>

                        {{-- Micro Tag --}}
                        <div class="absolute bottom-1 inset-x-0 text-center pointer-events-none">
                            <span class="inline-block text-[9px] font-semibold text-amber-900/70 uppercase tracking-widest bg-white/90 px-2 py-0.5 rounded-full border border-amber-200/60 shadow-2xs">
                                🕉️ Sacred 3D Seal
                            </span>
                        </div>

                    </div>
                </div>

            </div>

        </div>

        {{-- 2. COMPACT CONFIRMATION TIMELINE (3-Step Lifecycle) --}}
        <div class="bg-white/80 backdrop-blur-sm rounded-xl border border-slate-200/80 px-4 py-3 sm:px-6 sm:py-3.5 shadow-2xs">
            <div class="relative grid grid-cols-3 gap-2 max-w-xl mx-auto items-center">
                
                {{-- Connector Line --}}
                <div class="absolute top-3.5 left-[15%] right-[15%] h-0.5 bg-slate-200 -z-0" aria-hidden="true">
                    <div class="h-full @if($isPaid) bg-emerald-500 w-full @elseif($isPending) bg-amber-500 w-1/2 @else bg-slate-300 w-1/3 @endif transition-all duration-500"></div>
                </div>

                {{-- Step 1 --}}
                <div class="relative flex flex-col items-center text-center space-y-1">
                    <div class="w-7 h-7 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[11px] font-bold shadow-2xs ring-3 ring-white">
                        ✓
                    </div>
                    <span class="text-[11px] font-bold text-slate-800">Submitted</span>
                </div>

                {{-- Step 2 --}}
                <div class="relative flex flex-col items-center text-center space-y-1">
                    <div class="w-7 h-7 rounded-full @if($isPaid) bg-emerald-500 text-white @elseif($isPending) bg-amber-500 text-white animate-pulse @else bg-rose-500 text-white @endif flex items-center justify-center text-[11px] font-bold shadow-2xs ring-3 ring-white">
                        @if($isPaid) ✓ @elseif($isPending) ⟳ @else ✕ @endif
                    </div>
                    <span class="text-[11px] font-bold @if($isPaid) text-slate-800 @elseif($isPending) text-amber-700 @else text-rose-700 @endif">Verified</span>
                </div>

                {{-- Step 3 --}}
                <div class="relative flex flex-col items-center text-center space-y-1">
                    <div class="w-7 h-7 rounded-full @if($isPaid) bg-emerald-500 text-white @elseif($isPending) bg-slate-200 text-slate-400 @else bg-slate-200 text-slate-400 @endif flex items-center justify-center text-[11px] font-bold shadow-2xs ring-3 ring-white">
                        @if($isPaid) ✓ @else 3 @endif
                    </div>
                    <span class="text-[11px] font-bold @if($isPaid) text-slate-800 @else text-slate-400 @endif">80G Ready</span>
                </div>

            </div>
        </div>

        {{-- 3. CORE DONATION & RECEIPT CARDS (2-Column Desktop Grid) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6">
            
            {{-- Left: "Your Contribution" Details Card --}}
            <div class="lg:col-span-7 bg-white rounded-2xl sm:rounded-3xl border border-slate-200/90 shadow-2xs p-5 sm:p-6 space-y-4">
                
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-wider text-amber-600 block">Contribution Overview</span>
                        <h2 class="text-base font-bold text-slate-900 font-serif">Your Sacred Donation</h2>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold @if($isPaid) bg-emerald-100 text-emerald-800 @elseif($isPending) bg-amber-100 text-amber-800 @else bg-rose-100 text-rose-800 @endif uppercase tracking-wider">
                        {{ strtoupper($donation->payment_status ?? 'PENDING') }}
                    </span>
                </div>

                {{-- Amount Spotlight --}}
                <div class="bg-gradient-to-br from-amber-50/80 via-orange-50/40 to-yellow-50/60 rounded-xl p-4 border border-amber-200/70 flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-amber-900/80 block">Contribution Amount</span>
                        <div class="flex items-baseline gap-1 mt-0.5">
                            <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 font-mono tracking-tight">₹{{ number_format((float)$donation->amount, 2) }}</span>
                            <span class="text-[11px] font-semibold text-slate-500 uppercase">INR</span>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 bg-white/90 px-2.5 py-1 rounded-lg border border-emerald-200 shadow-2xs">
                        ✓ Recorded
                    </span>
                </div>

                {{-- Granular Breakdown Table --}}
                <dl class="divide-y divide-slate-100 text-xs space-y-0">
                    
                    {{-- Devotee Name --}}
                    <div class="py-2.5 flex justify-between items-center gap-4">
                        <dt class="font-bold uppercase tracking-wider text-slate-400">Devotee</dt>
                        <dd class="font-bold text-slate-900 uppercase text-right">{{ $donation->name }}</dd>
                    </div>

                    {{-- Dedicated Cause --}}
                    @if($donation->campaign)
                    <div class="py-2.5 flex justify-between items-center gap-4">
                        <dt class="font-bold uppercase tracking-wider text-slate-400">Dedicated Cause</dt>
                        <dd class="font-bold text-amber-900 text-right max-w-[200px] sm:max-w-xs truncate uppercase" title="{{ $donation->campaign->title }}">
                            {{ $donation->campaign->title }}
                        </dd>
                    </div>
                    @endif

                    {{-- Seva / Purpose Message if exists --}}
                    @if($donation->about)
                    <div class="py-2.5 flex justify-between items-center gap-4">
                        <dt class="font-bold uppercase tracking-wider text-slate-400">Seva Purpose</dt>
                        <dd class="font-medium text-slate-700 text-right max-w-[200px] truncate">{{ $donation->about }}</dd>
                    </div>
                    @endif

                    {{-- Payment Channel --}}
                    <div class="py-2.5 flex justify-between items-center gap-4">
                        <dt class="font-bold uppercase tracking-wider text-slate-400">Payment Channel</dt>
                        <dd class="font-semibold text-slate-800 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            {{ $gatewayLabel }}
                        </dd>
                    </div>

                    {{-- Transaction Reference with 1-Click Copy --}}
                    <div class="py-2.5 flex justify-between items-center gap-3">
                        <dt class="font-bold uppercase tracking-wider text-slate-400 shrink-0">Transaction Ref</dt>
                        <dd class="flex items-center gap-1.5 max-w-[200px] sm:max-w-xs">
                            <span id="tx-ref-text" class="font-mono text-[11px] font-semibold text-slate-700 truncate bg-slate-50 px-2 py-0.5 rounded border border-slate-200/80" title="{{ $txRef }}">
                                {{ $txRef }}
                            </span>
                            <button type="button" onclick="copyTransactionRef('{{ $txRef }}')" class="relative p-1 text-slate-500 hover:text-amber-700 hover:bg-amber-50 rounded transition focus:outline-none focus:ring-1 focus:ring-amber-500" title="Copy Reference" aria-label="Copy Reference">
                                <svg id="copy-icon" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                                <span id="copy-feedback" class="hidden absolute -top-7 -left-3 bg-slate-900 text-white text-[9px] font-bold px-1.5 py-0.5 rounded shadow whitespace-nowrap">
                                    Copied!
                                </span>
                            </button>
                        </dd>
                    </div>

                    {{-- Date & Time (IST) --}}
                    <div class="py-2.5 flex justify-between items-center gap-4">
                        <dt class="font-bold uppercase tracking-wider text-slate-400">Date &amp; Time (IST)</dt>
                        <dd class="font-mono text-[11px] font-semibold text-slate-700 text-right">{{ $paidTimestamp }}</dd>
                    </div>

                </dl>

            </div>

            {{-- Right: Official 80G Receipt & Action Hub --}}
            <div class="lg:col-span-5 space-y-4">
                
                {{-- Official Receipt Card --}}
                <div class="bg-gradient-to-b from-white to-amber-50/20 rounded-2xl sm:rounded-3xl border-2 @if($isPaid) border-amber-300/80 @else border-slate-200 @endif shadow-2xs p-5 sm:p-6 space-y-4 relative overflow-hidden">
                    
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-700 flex items-center justify-center shrink-0 border border-amber-300/40">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <span class="text-[9px] font-black uppercase tracking-widest text-amber-700 block">Section 80G Tax Exemption</span>
                            <h3 class="text-sm font-bold text-slate-900">Official Donation Receipt</h3>
                        </div>
                    </div>

                    @if($donation->receipt_number)
                        <div class="bg-white rounded-lg p-3 border border-amber-200/80 shadow-2xs space-y-0.5">
                            <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400">Receipt Voucher Number</span>
                            <div class="font-mono text-xs font-black text-amber-800 break-all select-all">
                                {{ $donation->receipt_number }}
                            </div>
                        </div>
                    @endif

                    <p class="text-[11px] text-slate-600 leading-relaxed font-normal">
                        @if($isPaid)
                            Your official donation receipt is ready for instant download and Section 80G tax archival.
                        @elseif($isPending)
                            The receipt voucher will generate automatically upon gateway confirmation.
                        @else
                            Receipt generation suspended due to incomplete status.
                        @endif
                    </p>

                    {{-- Action Buttons --}}
                    <div class="space-y-2.5 pt-1">
                        @if($isPaid)
                            <a href="{{ route('donations.receipt', $donation->id) }}" class="group w-full bg-gradient-to-r from-orange-600 to-amber-600 hover:from-orange-700 hover:to-amber-700 text-white font-extrabold py-3 px-5 rounded-xl flex items-center justify-center gap-2 shadow-md shadow-orange-600/20 text-xs uppercase tracking-wider transition-all transform hover:-translate-y-0.5 active:translate-y-0 min-h-[44px]">
                                <svg class="w-4 h-4 transition-transform group-hover:-translate-y-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span>Download Official 80G Receipt</span>
                            </a>
                        @elseif($isPending)
                            <button type="button" onclick="window.location.reload()" class="w-full bg-gradient-to-r from-amber-500 to-yellow-600 hover:from-amber-600 hover:to-yellow-700 text-white font-extrabold py-3 px-5 rounded-xl flex items-center justify-center gap-2 shadow-md shadow-amber-500/20 text-xs uppercase tracking-wider transition-all min-h-[44px]">
                                <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                <span>Refresh Status</span>
                            </button>
                        @else
                            <a href="{{ route('donations.grid') }}#donate_form_section" class="w-full bg-gradient-to-r from-orange-600 to-amber-600 hover:from-orange-700 hover:to-amber-700 text-white font-extrabold py-3 px-5 rounded-xl flex items-center justify-center gap-2 shadow-md shadow-orange-600/20 text-xs uppercase tracking-wider transition-all min-h-[44px]">
                                <span>Try Contributing Again</span>
                            </a>
                        @endif

                        <a href="{{ route('donations.grid') }}" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2.5 px-4 rounded-xl flex items-center justify-center text-xs uppercase tracking-wider transition min-h-[40px]">
                            ← Explore All Seva Campaigns
                        </a>
                    </div>

                </div>

                {{-- Legal Notice --}}
                <div class="bg-white/70 rounded-xl p-3 border border-slate-200/60 text-center space-y-0.5">
                    <p class="text-[10px] font-bold text-slate-700">
                        🏛️ Registered Charitable Trust • Section 80G Tax Benefits
                    </p>
                    <p class="text-[9px] text-slate-500 leading-tight">
                        Akhanda Bharatha Viswa Hindu Parirakshana Samiti is eligible for tax exemption under Indian Income Tax Act.
                    </p>
                </div>

                {{-- ABVHPS Official Statutory Compliance Certificates (Paid Donations Only) --}}
                @if($isPaid && isset($activeCertificates) && count($activeCertificates) > 0)
                <div class="bg-white rounded-2xl border border-amber-200 shadow-2xs p-4 sm:p-5 space-y-3">
                    <div class="flex items-center justify-between border-b border-amber-100 pb-2">
                        <div class="flex items-center gap-2">
                            <span class="text-base">📜</span>
                            <h4 class="text-xs font-black uppercase tracking-wider text-slate-900">ABVHPS Compliance Certificates</h4>
                        </div>
                        <span class="text-[9px] font-bold uppercase tracking-wider text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                            Statutory Disclosures
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-600 leading-relaxed">
                        View our official Trust statutory registration, Section 12A exemption, and Section 80G tax compliance documents:
                    </p>
                    <div class="space-y-2 pt-1">
                        @foreach($activeCertificates as $cert)
                            <a href="{{ $cert->file_url }}" target="_blank" rel="noopener noreferrer" class="group flex items-center justify-between p-2.5 rounded-xl border border-slate-200 hover:border-amber-400 bg-slate-50/60 hover:bg-amber-50/40 transition">
                                <div class="space-y-0.5">
                                    <div class="text-xs font-bold text-slate-800 group-hover:text-amber-900">
                                        {{ $cert->title }}
                                    </div>
                                    <div class="text-[10px] text-slate-500 font-mono">
                                        {{ $cert->certificate_type }} @if($cert->document_number) • Reg: {{ $cert->document_number }} @endif
                                    </div>
                                </div>
                                <span class="text-xs text-amber-700 font-bold group-hover:translate-x-0.5 transition-transform">
                                    View &rarr;
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>

        </div>

    </div>

</div>

{{-- Dynamic Scripts for 3D Progressive Enhancement & Clipboard Copying --}}
<script>
    // 1. Transaction Reference Copy to Clipboard
    function copyTransactionRef(text) {
        if (!text) return;
        
        const showSuccess = function() {
            const feedback = document.getElementById('copy-feedback');
            if (feedback) {
                feedback.classList.remove('hidden');
                setTimeout(function() {
                    feedback.classList.add('hidden');
                }, 2000);
            }
        };

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(showSuccess).catch(function() {
                fallbackCopy(text, showSuccess);
            });
        } else {
            fallbackCopy(text, showSuccess);
        }
    }

    function fallbackCopy(text, callback) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
            if (callback) callback();
        } catch (e) {
            console.warn('Copy failed:', e);
        }
        document.body.removeChild(textarea);
    }

    // 2. Interactive Progressive 3D Sacred Emblem (Three.js with live continuous rotation & mouse reactivity)
    (function initThreeEmblem() {
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js';
        script.async = true;
        
        script.onload = function() {
            if (typeof THREE === 'undefined') return;
            
            const container = document.getElementById('abvhps-3d-emblem-container');
            const canvas = document.getElementById('abvhps-three-canvas');
            const fallback = document.getElementById('abvhps-fallback-emblem');
            
            if (!container || !canvas) return;

            try {
                // Dimensions
                let width = container.clientWidth || 180;
                let height = container.clientHeight || 180;

                const scene = new THREE.Scene();
                const camera = new THREE.PerspectiveCamera(45, width / height, 0.1, 100);
                camera.position.z = 4.8;

                const renderer = new THREE.WebGLRenderer({
                    canvas: canvas,
                    alpha: true,
                    antialias: true,
                    powerPreference: "high-performance"
                });
                renderer.setSize(width, height);
                renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

                // Lights
                const ambientLight = new THREE.AmbientLight(0xfff5eb, 1.4);
                scene.add(ambientLight);

                const pointLight1 = new THREE.PointLight(0xffaa00, 2.8, 30);
                pointLight1.position.set(3, 4, 4);
                scene.add(pointLight1);

                const pointLight2 = new THREE.PointLight(0xff4500, 2.0, 30);
                pointLight2.position.set(-3, -3, 3);
                scene.add(pointLight2);

                // Sacred Geometry Group
                const group = new THREE.Group();

                // Outer Gold Torus
                const outerGeo = new THREE.TorusGeometry(1.4, 0.045, 16, 90);
                const goldMat = new THREE.MeshStandardMaterial({
                    color: 0xd97706,
                    metalness: 0.85,
                    roughness: 0.25,
                });
                const outerRing = new THREE.Mesh(outerGeo, goldMat);
                group.add(outerRing);

                // Inner Saffron Torus
                const innerGeo = new THREE.TorusGeometry(1.1, 0.035, 16, 70);
                const saffronMat = new THREE.MeshStandardMaterial({
                    color: 0xff6600,
                    metalness: 0.75,
                    roughness: 0.3,
                });
                const innerRing = new THREE.Mesh(innerGeo, saffronMat);
                innerRing.rotation.x = Math.PI / 4;
                group.add(innerRing);

                // Core Sacred Mandala Star / Icosahedron
                const coreGeo = new THREE.IcosahedronGeometry(0.75, 1);
                const coreMat = new THREE.MeshStandardMaterial({
                    color: 0xf59e0b,
                    metalness: 0.9,
                    roughness: 0.2,
                    wireframe: true
                });
                const core = new THREE.Mesh(coreGeo, coreMat);
                group.add(core);

                // Central Sacred Jewel
                const jewelGeo = new THREE.OctahedronGeometry(0.42, 0);
                const jewelMat = new THREE.MeshStandardMaterial({
                    color: 0xee5d00,
                    metalness: 0.6,
                    roughness: 0.15,
                });
                const jewel = new THREE.Mesh(jewelGeo, jewelMat);
                group.add(jewel);

                // Sparkling Particles
                const particleCount = 20;
                const particleGeo = new THREE.BufferGeometry();
                const particlePositions = new Float32Array(particleCount * 3);
                for (let i = 0; i < particleCount; i++) {
                    const angle = (i / particleCount) * Math.PI * 2;
                    const r = 1.6 + Math.random() * 0.2;
                    particlePositions[i * 3] = Math.cos(angle) * r;
                    particlePositions[i * 3 + 1] = Math.sin(angle) * r;
                    particlePositions[i * 3 + 2] = (Math.random() - 0.5) * 0.3;
                }
                particleGeo.setAttribute('position', new THREE.BufferAttribute(particlePositions, 3));
                const particleMat = new THREE.PointsMaterial({
                    color: 0xfbbf24,
                    size: 0.07,
                    transparent: true,
                    opacity: 0.9
                });
                const particles = new THREE.Points(particleGeo, particleMat);
                group.add(particles);

                scene.add(group);

                // Show canvas and hide fallback
                canvas.classList.remove('hidden');
                if (fallback) fallback.classList.add('hidden');

                // Pointer / Mouse Interactivity
                let targetTiltX = 0;
                let targetTiltY = 0;
                let isDragging = false;
                let lastPointerX = 0;
                let lastPointerY = 0;
                let manualRotationY = 0;

                container.addEventListener('pointerdown', function(e) {
                    isDragging = true;
                    lastPointerX = e.clientX;
                    lastPointerY = e.clientY;
                });

                window.addEventListener('pointerup', function() {
                    isDragging = false;
                });

                window.addEventListener('pointermove', function(e) {
                    const rect = container.getBoundingClientRect();
                    const isInside = (
                        e.clientX >= rect.left &&
                        e.clientX <= rect.right &&
                        e.clientY >= rect.top &&
                        e.clientY <= rect.bottom
                    );

                    if (isDragging) {
                        const deltaX = e.clientX - lastPointerX;
                        const deltaY = e.clientY - lastPointerY;
                        manualRotationY += deltaX * 0.01;
                        targetTiltX += deltaY * 0.01;
                        lastPointerX = e.clientX;
                        lastPointerY = e.clientY;
                    } else if (isInside) {
                        const nx = ((e.clientX - rect.left) / rect.width) * 2 - 1;
                        const ny = -(((e.clientY - rect.top) / rect.height) * 2 - 1);
                        targetTiltY = nx * 0.5;
                        targetTiltX = -ny * 0.4;
                    } else {
                        targetTiltY = 0;
                        targetTiltX = 0;
                    }
                });

                // Active Render & Animation Loop
                let lastTime = performance.now();

                function renderLoop(currentTime) {
                    requestAnimationFrame(renderLoop);

                    const delta = (currentTime - lastTime) * 0.001;
                    lastTime = currentTime;

                    if (!prefersReducedMotion) {
                        // Continuous smooth idle rotation
                        group.rotation.y += 0.012 + manualRotationY * 0.05;
                        innerRing.rotation.x += 0.015;
                        innerRing.rotation.y += 0.008;
                        core.rotation.y -= 0.01;
                        core.rotation.z += 0.006;
                        jewel.rotation.y += 0.02;
                        particles.rotation.z += 0.006;

                        // Lerp tilt smoothly towards mouse target
                        group.rotation.x += (targetTiltX - group.rotation.x) * 0.08;
                        group.rotation.z += (targetTiltY - group.rotation.z) * 0.08;
                        manualRotationY *= 0.92;
                    }

                    renderer.render(scene, camera);
                }

                requestAnimationFrame(renderLoop);

                // Resize handler
                const resizeObserver = new ResizeObserver(function() {
                    const newW = container.clientWidth;
                    const newH = container.clientHeight;
                    if (newW && newH) {
                        camera.aspect = newW / newH;
                        camera.updateProjectionMatrix();
                        renderer.setSize(newW, newH);
                    }
                });
                resizeObserver.observe(container);

            } catch (err) {
                console.warn('Three.js fallback active:', err);
                if (canvas) canvas.classList.add('hidden');
                if (fallback) fallback.classList.remove('hidden');
            }
        };

        script.onerror = function() {
            const fallback = document.getElementById('abvhps-fallback-emblem');
            if (fallback) fallback.classList.remove('hidden');
        };

        document.head.appendChild(script);
    })();
</script>

<style>
    @media (prefers-reduced-motion: reduce) {
        .animate-pulse, .animate-spin {
            animation: none !important;
        }
    }
</style>
@endsection
