@extends('layouts.app')
@php
    $isFeatured = isset($featuredCampaign) && $featuredCampaign;
@endphp

@if($isFeatured)
    @section('title', 'ABVHPS — ' . $featuredCampaign->title)
    @section('meta_description', \Illuminate\Support\Str::limit(strip_tags($featuredCampaign->description ?? 'Support ' . $featuredCampaign->title . ' under ABVHPS.'), 150))
    @section('canonical_url', $featuredCampaign->public_url)
    @section('og_title', 'ABVHPS — ' . $featuredCampaign->title)
    @section('og_description', \Illuminate\Support\Str::limit(strip_tags($featuredCampaign->description ?? 'Support this ABVHPS fundraising campaign.'), 150))
    @section('og_url', $featuredCampaign->public_url)
    @section('og_image', $featuredCampaign->public_image_url)
    @section('twitter_card', 'summary_large_image')
    @section('twitter_title', 'ABVHPS — ' . $featuredCampaign->title)
    @section('twitter_description', \Illuminate\Support\Str::limit(strip_tags($featuredCampaign->description ?? 'Support this ABVHPS fundraising campaign.'), 150))
    @section('twitter_image', $featuredCampaign->public_image_url)
@else
    @section('title', 'Dharma Seva Fundraising Campaigns | ABVHPS')
    @section('meta_description', 'Support active ABVHPS fundraising initiatives for temple construction, goshala developments, and sacred deity consecration across India.')
@endif

@section('content')
<div class="bg-gray-50 min-h-screen pb-20">

    @php
        $fundraiseBanner = \App\Models\Banner::getBannerForPage('fundraise');
    @endphp

    {{-- Official Header Hero Banner --}}
    <div class="text-white border-b-4 border-brandOrange shadow-md relative overflow-hidden flex items-center justify-center"
         style="min-height: 380px; @if(!$fundraiseBanner) background-image: url('{{ asset('images/fundraise_bg.png') }}'); background-size: cover; background-repeat: no-repeat; background-position: center center; @endif"
         data-banner-page="fundraise">

        @if($fundraiseBanner && !empty($fundraiseBanner->desktop_banner))
            <picture class="absolute inset-0 w-full h-full">
                @if(!empty($fundraiseBanner->mobile_banner))
                    <source media="(max-width: 640px)" srcset="{{ asset('storage/' . $fundraiseBanner->mobile_banner) }}">
                @endif
                <source media="(min-width: 641px)" srcset="{{ asset('storage/' . $fundraiseBanner->desktop_banner) }}">
                <img src="{{ asset('storage/' . $fundraiseBanner->desktop_banner) }}"
                     alt="{{ $fundraiseBanner->title ?? 'Dharma Seva Fundraising Desk' }}"
                     class="w-full h-full object-cover object-center"
                     style="z-index: 0;">
            </picture>
        @endif

        {{-- Protective vignette / overlay --}}
        <div class="absolute inset-0 pointer-events-none"
             style="background: rgba(5, 15, 30, @if($fundraiseBanner) 0.45 @else 0.20 @endif); z-index: 1;"></div>

        {{-- Hero Content --}}
        <div class="relative z-10 flex items-center justify-center py-12 sm:py-16 px-4 w-full">
            <div class="text-center max-w-3xl mx-auto space-y-3">
                <div class="inline-block w-full rounded-3xl px-6 py-6"
                     style="background: rgba(255,255,255,0.09); backdrop-filter: blur(4px);">

                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full overflow-hidden bg-white border-2 border-brandOrange shadow-lg mx-auto flex items-center justify-center p-1 shrink-0 mb-3">
                        <img src="{{ asset('images/ABVHPS_LOGO.jpg') }}" class="w-full h-full object-cover rounded-full" alt="ABVHPS Logo">
                    </div>

                    <span class="bg-orange-500/25 text-orange-200 text-[10px] sm:text-[11px] font-black px-4 py-1 rounded-full uppercase tracking-widest inline-block border border-orange-400/40 mb-1"
                          style="text-shadow: 0 1px 3px rgba(0,0,0,0.5);">
                        {{ ($fundraiseBanner && $fundraiseBanner->page_name) ? $fundraiseBanner->page_name : 'Support ABVHPS' }}
                    </span>

                    <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-black uppercase tracking-wide text-white"
                        style="text-shadow: 0 2px 8px rgba(0,0,0,0.55), 0 1px 2px rgba(0,0,0,0.4);">
                        {{ ($fundraiseBanner && !empty($fundraiseBanner->title)) ? $fundraiseBanner->title : 'Sanatana Dharma Seva & Community Fund' }}
                    </h1>

                    <p class="text-xs sm:text-sm md:text-base max-w-2xl mx-auto font-medium leading-relaxed mt-2 text-gray-100"
                       style="text-shadow: 0 1px 4px rgba(0,0,0,0.5);">
                        {{ ($fundraiseBanner && !empty($fundraiseBanner->subtitle)) ? $fundraiseBanner->subtitle : 'Join hands with Akhanda Bharatha Viswa Hindu Parirakshana Samiti to build temples, protect goshalas, support education, empower rural youth, and preserve our eternal heritage.' }}
                    </p>

                    <div class="pt-4 flex flex-wrap items-center justify-center gap-3">
                        <a href="#donate_form_section" class="bg-brandOrange hover:bg-orange-600 text-white text-xs sm:text-sm font-black px-6 py-3 rounded-full shadow-lg uppercase tracking-wider transition transform hover:scale-105 min-h-[44px] flex items-center justify-center">
                            🕉️ Make a Contribution
                        </a>
                        <a href="#active_campaigns_section" class="bg-white/20 hover:bg-white/30 text-white text-xs sm:text-sm font-bold px-5 py-3 rounded-full backdrop-blur-sm uppercase tracking-wider transition min-h-[44px] flex items-center justify-center">
                            View Active Causes ({{ $campaigns->count() }})
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Pillar Highlights: Temple, Sanatana Dharma, Community, Education, Social Welfare --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 relative z-20">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
            <div class="bg-white p-3.5 sm:p-4 rounded-2xl shadow-md border border-gray-100 text-center hover:border-brandOrange transition">
                <span class="text-2xl sm:text-3xl block mb-1">🛕</span>
                <h4 class="text-[11px] sm:text-xs font-black text-brandGray uppercase">Temple Renovation</h4>
                <p class="text-[9px] text-gray-500 font-semibold mt-0.5">Reviving ancient holy shrines</p>
            </div>
            <div class="bg-white p-3.5 sm:p-4 rounded-2xl shadow-md border border-gray-100 text-center hover:border-brandOrange transition">
                <span class="text-2xl sm:text-3xl block mb-1">🕉️</span>
                <h4 class="text-[11px] sm:text-xs font-black text-brandGray uppercase">Dharma Preservation</h4>
                <p class="text-[9px] text-gray-500 font-semibold mt-0.5">Vedic culture & traditions</p>
            </div>
            <div class="bg-white p-3.5 sm:p-4 rounded-2xl shadow-md border border-gray-100 text-center hover:border-brandOrange transition">
                <span class="text-2xl sm:text-3xl block mb-1">🐄</span>
                <h4 class="text-[11px] sm:text-xs font-black text-brandGray uppercase">Goshala Seva</h4>
                <p class="text-[9px] text-gray-500 font-semibold mt-0.5">Sacred cow protection</p>
            </div>
            <div class="bg-white p-3.5 sm:p-4 rounded-2xl shadow-md border border-gray-100 text-center hover:border-brandOrange transition">
                <span class="text-2xl sm:text-3xl block mb-1">📚</span>
                <h4 class="text-[11px] sm:text-xs font-black text-brandGray uppercase">Youth Education</h4>
                <p class="text-[9px] text-gray-500 font-semibold mt-0.5">Scholarships & training</p>
            </div>
            <div class="bg-white p-3.5 sm:p-4 rounded-2xl shadow-md border border-gray-100 text-center hover:border-brandOrange transition col-span-2 sm:col-span-1">
                <span class="text-2xl sm:text-3xl block mb-1">🤝</span>
                <h4 class="text-[11px] sm:text-xs font-black text-brandGray uppercase">Social Welfare</h4>
                <p class="text-[9px] text-gray-500 font-semibold mt-0.5">Annadhanam & rural relief</p>
            </div>
        </div>
    </div>

    <!-- ====================================================================== -->
    <!-- SECTION 1: ACTIVE FUNDRAISING CAMPAIGNS GRID -->
    <!-- ====================================================================== -->
    <div id="active_campaigns_section" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12 sm:mt-16">
        
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-8 gap-2">
            <div>
                <span class="text-[10px] sm:text-[11px] font-black text-brandOrange uppercase tracking-widest">Ongoing Holy Missions</span>
                <h2 class="text-xl sm:text-2xl md:text-3xl font-black text-brandDarkGray uppercase tracking-tight">Active Fundraising Campaigns</h2>
            </div>
            <p class="text-xs text-gray-500 font-medium max-w-sm sm:text-right">
                Directly select any ongoing campaign to support with 100% transparent tracking and instant 80G receipt.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        
        @forelse($campaigns as $campaign)
            <!-- INDIVIDUAL CAMPAIGN SECURED CARD NODE -->
            <div id="campaign_{{ $campaign->id }}" class="bg-white rounded-3xl shadow-lg border @if(isset($featuredCampaign) && $featuredCampaign && $featuredCampaign->id === $campaign->id) border-brandOrange ring-4 ring-brandOrange/20 @else border-gray-100 @endif overflow-hidden flex flex-col justify-between transform hover:scale-[1.01] transition-all duration-300">
                
                <div>
                    <!-- Header Context Meta Badge Structure -->
                    <div class="p-4 bg-gray-50/80 border-b border-gray-100 flex justify-between items-center">
                        <span class="bg-orange-100 text-brandOrange text-[9px] font-black px-3 py-1 rounded-full uppercase tracking-wider shadow-sm">
                            Active Cause
                        </span>
                        <div class="flex items-center gap-1.5 text-[10px] font-bold text-gray-400">
                            <span>Ends:</span>
                            <span class="font-mono text-gray-700 bg-white border border-gray-200 px-2 py-0.5 rounded-md font-semibold">{{ \Carbon\Carbon::parse($campaign->end_date)->format('d-M-Y') }}</span>
                        </div>
                    </div>

                    <!-- Core Campaign Title -->
                    <div class="px-5 pt-4">
                        <h3 class="text-sm md:text-base font-black text-brandDarkGray tracking-wide uppercase line-clamp-2 min-h-[44px]">
                            {{ $campaign->title }}
                        </h3>
                    </div>

                    <!-- MULTI-MEDIA VIEWPORT DESK -->
                    <div class="px-5 py-3 space-y-3">
                        
                        <!-- A. INTERACTIVE MULTI-PHOTO CAROUSEL DISPLAY -->
                        <div class="relative w-full h-52 bg-gray-100 rounded-2xl overflow-hidden border border-gray-200 group/slider shadow-inner">
                            <div class="absolute inset-0 flex transition-transform duration-500 ease-in-out" id="carousel_track_{{ $campaign->id }}">
                                <div class="w-full h-full flex-shrink-0">
                                    <img src="{{ asset('storage/' . $campaign->cover_image) }}" class="w-full h-full object-cover" alt="{{ $campaign->title }}">
                                </div>
                                @if($campaign->image_1)
                                    <div class="w-full h-full flex-shrink-0"><img src="{{ asset('storage/' . $campaign->image_1) }}" class="w-full h-full object-cover" alt="{{ $campaign->title }} Image 1"></div>
                                @endif
                                @if($campaign->image_2)
                                    <div class="w-full h-full flex-shrink-0"><img src="{{ asset('storage/' . $campaign->image_2) }}" class="w-full h-full object-cover" alt="{{ $campaign->title }} Image 2"></div>
                                @endif
                                @if($campaign->image_3)
                                    <div class="w-full h-full flex-shrink-0"><img src="{{ asset('storage/' . $campaign->image_3) }}" class="w-full h-full object-cover" alt="{{ $campaign->title }} Image 3"></div>
                                @endif
                                @if($campaign->image_4)
                                    <div class="w-full h-full flex-shrink-0"><img src="{{ asset('storage/' . $campaign->image_4) }}" class="w-full h-full object-cover" alt="{{ $campaign->title }} Image 4"></div>
                                @endif
                            </div>

                            <!-- Slider Arrow Anchors Desktop Indicators -->
                            <button type="button" onclick="moveCarouselSlider({{ $campaign->id }}, -1)" class="absolute left-2 top-1/2 -translate-y-1/2 bg-black/50 hover:bg-black/80 text-white w-8 h-8 rounded-full text-sm font-black flex items-center justify-center opacity-80 hover:opacity-100 transition cursor-pointer select-none" aria-label="Previous image">‹</button>
                            <button type="button" onclick="moveCarouselSlider({{ $campaign->id }}, 1)" class="absolute right-2 top-1/2 -translate-y-1/2 bg-black/50 hover:bg-black/80 text-white w-8 h-8 rounded-full text-sm font-black flex items-center justify-center opacity-80 hover:opacity-100 transition cursor-pointer select-none" aria-label="Next image">›</button>
                        </div>

                        <!-- B. EMERGENCY FIELD EXPLAINER VIDEO PLAYER ENGINE -->
                        @if($campaign->video_path)
                            <div class="space-y-1">
                                <span class="text-[9px] font-black text-brandOrange tracking-wider uppercase flex items-center gap-1">🎥 Live Field Briefing Video:</span>
                                <div class="rounded-2xl overflow-hidden border border-gray-200 bg-black shadow h-36 relative">
                                    <video class="w-full h-full object-contain" controls preload="metadata">
                                        <source src="{{ asset('storage/' . $campaign->video_path) }}" type="video/mp4">
                                        Your browser does not support integrated video playback.
                                    </video>
                                </div>
                            </div>
                        @endif

                        <!-- Description -->
                        <div class="pt-1">
                            <p class="text-xs font-medium text-gray-600 leading-relaxed line-clamp-3">
                                {{ $campaign->description }}
                            </p>
                        </div>
                    </div>

                    <!-- FINANCIAL LEDGER MATRIX & PROGRESS BAR DESK -->
                    <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/50 space-y-3">
                        <div>
                            <div class="flex justify-between items-center text-[10px] font-black text-brandGray uppercase tracking-wide mb-1.5">
                                <span>Secured Contribution</span>
                                <span class="text-brandOrange font-mono text-xs font-bold">{{ $campaign->progress_percent }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 h-2.5 rounded-full overflow-hidden shadow-inner">
                                <div class="bg-gradient-to-r from-orange-500 to-amber-500 h-full rounded-full transition-all duration-500" style="width: {{ $campaign->progress_percent }}%"></div>
                            </div>
                        </div>

                        <!-- Numeric Indicators -->
                        <div class="grid grid-cols-2 gap-2 text-left pt-1">
                            <div class="border-r border-gray-200/80 pr-2">
                                <span class="text-[9px] font-black text-gray-400 uppercase tracking-wider block">Raised Amount</span>
                                <span class="text-xs md:text-sm font-black font-mono text-emerald-600">{{ \App\Models\FundraisingCampaign::formatIndianCurrency($campaign->raised_amount) }}</span>
                            </div>
                            <div class="pl-2">
                                <span class="text-[9px] font-black text-gray-400 uppercase tracking-wider block">Target Budget</span>
                                <span class="text-xs md:text-sm font-black font-mono text-brandDarkGray">{{ \App\Models\FundraisingCampaign::formatIndianCurrency($campaign->target_amount) }}</span>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- CARD FOOTER: SOCIAL SHARING + DIRECT DONATE TRIGGER -->
                <div class="p-4 bg-gray-50 border-t border-gray-100 space-y-2.5">
                    
                    <!-- Social Share Action Buttons -->
                    <div class="grid grid-cols-3 gap-1.5">
                        <a href="{{ $campaign->whatsapp_share_url ?? $campaign->whatsapp_share }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center gap-1 bg-[#25D366] hover:bg-[#20ba59] text-white text-[10px] sm:text-[11px] font-black py-2 px-2 rounded-xl shadow-sm uppercase tracking-wider transition min-h-[38px]" aria-label="Share on WhatsApp">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.694.072-2.176-.543-1.894-.787-3.111-2.724-3.206-2.85-.095-.125-.769-1.025-.769-1.954 0-.93.486-1.385.66-1.575.174-.189.38-.238.508-.238.127 0 .253.002.364.007.117.006.275-.044.429.327.16.386.547 1.332.595 1.43.048.098.08.213.016.338-.064.126-.096.205-.19.316-.095.111-.2.247-.286.332-.095.095-.194.198-.083.389.111.19.493.814 1.057 1.317.725.646 1.337.846 1.528.941.19.095.302.08.413-.048.111-.127.476-.556.603-.746.127-.19.254-.158.428-.095.175.063 1.111.524 1.301.62.19.095.317.143.365.222.048.079.048.46-.096.865z"/></svg>
                            <span>WhatsApp</span>
                        </a>

                        <a href="{{ $campaign->facebook_share_url ?? $campaign->facebook_share }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center gap-1 bg-[#1877F2] hover:bg-[#1565cc] text-white text-[10px] sm:text-[11px] font-black py-2 px-2 rounded-xl shadow-sm uppercase tracking-wider transition min-h-[38px]" aria-label="Share on Facebook">
                            <span class="text-xs">fb</span>
                            <span>Share</span>
                        </a>

                        <button type="button" onclick="copyCampaignLink('{{ $campaign->public_url }}', this)" class="flex items-center justify-center gap-1 bg-gray-200 hover:bg-gray-300 text-brandGray text-[10px] sm:text-[11px] font-black py-2 px-2 rounded-xl uppercase tracking-wider transition min-h-[38px] cursor-pointer" aria-label="Copy Campaign Link">
                            <span>🔗</span>
                            <span class="copy-btn-text">Copy</span>
                        </button>
                    </div>

                    <!-- Direct Donate Trigger for This Specific Campaign -->
                    <button type="button" onclick="selectCampaignAndScroll({{ $campaign->id }}, '{{ addslashes($campaign->title) }}')" class="w-full bg-brandOrange hover:bg-orange-600 text-white font-black text-center py-2.5 px-4 rounded-xl text-xs shadow-md uppercase tracking-wider transition transform hover:scale-[1.01] flex items-center justify-center gap-2 min-h-[44px] cursor-pointer">
                        <span>🕉️ Donate to This Cause</span>
                    </button>
                </div>

            </div>
        @empty
            <div class="col-span-1 md:col-span-2 lg:col-span-3 text-center bg-white border border-gray-200 rounded-3xl p-12 shadow-sm">
                <span class="text-5xl block mb-2">🌾</span>
                <h3 class="text-base font-black text-gray-400 uppercase tracking-wider">No Active Service Campaigns At Present</h3>
                <p class="text-xs font-semibold text-gray-400 mt-1">You can still contribute to the general Sanatana Dharma Protection fund using the donation form below.</p>
            </div>
        @endforelse

        </div>
    </div>


    <!-- ====================================================================== -->
    <!-- SECTION 2: PRODUCTION DONATION FORM WITH CASHFREE & RAZORPAY -->
    <!-- ====================================================================== -->
    <div id="donate_form_section" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mt-16 sm:mt-24">
        
        <div class="bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden">
            
            <!-- Form Header -->
            <div class="bg-gradient-to-r from-brandOrange to-orange-600 p-6 sm:p-8 text-white text-center relative">
                <div class="w-14 h-14 rounded-full bg-white/20 backdrop-blur-sm border-2 border-white/40 flex items-center justify-center mx-auto mb-2 text-2xl">
                    🙏
                </div>
                <span class="text-[10px] font-black text-orange-100 uppercase tracking-widest bg-black/20 px-3.5 py-0.5 rounded-full inline-block mb-1">
                    Direct Online Contribution Portal
                </span>
                <h2 class="text-xl sm:text-3xl font-black uppercase tracking-tight text-white">
                    Support ABVHPS Seva Initiatives
                </h2>
                <p class="text-xs sm:text-sm text-orange-100 max-w-xl mx-auto font-medium mt-1">
                    Contribute securely using UPI, Debit/Credit Card, Net Banking, or Wallets with instant 80G receipt generation.
                </p>
            </div>

            <!-- Form Content -->
            <form id="donation_form" onsubmit="handleDonationSubmit(event)" class="p-6 sm:p-10 space-y-8">
                @csrf

                <!-- STEP 1: AMOUNT SELECTION -->
                <div class="space-y-3">
                    <label class="block text-xs font-black text-brandDarkGray uppercase tracking-wider flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-brandOrange text-white text-[10px] flex items-center justify-center font-bold">1</span>
                        <span>Donation Amount (INR)</span>
                        <span class="text-red-500">*</span>
                    </label>

                    <!-- Custom Amount Input Group -->
                    <div class="mt-2 flex items-center w-full bg-gray-50 border-2 border-gray-200 rounded-2xl transition focus-within:border-brandOrange focus-within:bg-white">
                        <span aria-hidden="true" class="flex items-center justify-center pl-4 pr-1 text-base sm:text-base font-bold text-gray-500 leading-none select-none shrink-0">
                            ₹
                        </span>
                        <input type="number" id="donation_amount" name="amount" min="1" max="500000" step="1" required
                               aria-label="Donation amount in Indian Rupees"
                               class="flex-1 min-w-0 border-0 bg-transparent py-3 sm:py-3.5 pr-4 text-base sm:text-base font-black font-mono text-gray-900 focus:ring-0 focus:outline-none placeholder:font-sans placeholder:text-xs sm:placeholder:text-sm placeholder:font-normal placeholder:text-gray-400"
                               placeholder="Enter amount from ₹1 to ₹5,00,000"
                               oninput="handleCustomAmountInput(this.value)">
                    </div>
                    <p class="text-[10px] text-gray-400 font-semibold mt-1 pl-1">
                        * Server-side amount validation enforced. Minimum ₹1 • Maximum ₹5,00,000 per transaction.
                    </p>
                </div>

                <!-- STEP 2: DEDICATED CAUSE / CAMPAIGN SELECTION -->
                <div class="space-y-3">
                    <label class="block text-xs font-black text-brandDarkGray uppercase tracking-wider flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-brandOrange text-white text-[10px] flex items-center justify-center font-bold">2</span>
                        <span>Designate Cause (Optional)</span>
                    </label>

                    <select id="campaign_id" name="campaign_id" class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-2xl text-xs sm:text-sm font-bold text-gray-800 focus:outline-none focus:border-brandOrange focus:bg-white transition min-h-[48px]">
                        <option value="">🕉️ General Seva Fund (ABVHPS Central Welfare)</option>
                        @foreach($campaigns as $camp)
                            <option value="{{ $camp->id }}" @if(isset($featuredCampaign) && $featuredCampaign && $featuredCampaign->id === $camp->id) selected @endif>
                                🌺 {{ $camp->title }} (Target: {{ \App\Models\FundraisingCampaign::formatIndianCurrency($camp->target_amount) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- STEP 3: DONOR PERSONAL DETAILS -->
                <div class="space-y-4">
                    <label class="block text-xs font-black text-brandDarkGray uppercase tracking-wider flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-brandOrange text-white text-[10px] flex items-center justify-center font-bold">3</span>
                        <span>Devotee / Donor Information</span>
                    </label>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wide mb-1">
                                Full Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="donor_name" name="donor_name" required maxlength="100"
                                   class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-2xl text-xs sm:text-sm font-semibold text-gray-900 focus:outline-none focus:border-brandOrange focus:bg-white transition min-h-[44px]"
                                   placeholder="Sri Rama Bhakta">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wide mb-1">
                                Mobile Number (for receipt SMS) <span class="text-red-500">*</span>
                            </label>
                            <input type="tel" id="donor_phone" name="phone" required pattern="[0-9]{10,13}" maxlength="13"
                                   class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-2xl text-xs sm:text-sm font-semibold font-mono text-gray-900 focus:outline-none focus:border-brandOrange focus:bg-white transition min-h-[44px]"
                                   placeholder="10-digit mobile number">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wide mb-1">
                                Email Address (for digital receipt) <span class="text-red-500">*</span>
                            </label>
                            <input type="email" id="donor_email" name="email" required maxlength="150"
                                   class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-2xl text-xs sm:text-sm font-semibold text-gray-900 focus:outline-none focus:border-brandOrange focus:bg-white transition min-h-[44px]"
                                   placeholder="devotee@example.com">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wide mb-1">
                                PAN Card Number (Optional for 80G Tax Exemption)
                            </label>
                            <input type="text" id="donor_pan" name="pan_number" maxlength="10"
                                   class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-2xl text-xs sm:text-sm font-semibold font-mono uppercase text-gray-900 focus:outline-none focus:border-brandOrange focus:bg-white transition min-h-[44px]"
                                   placeholder="ABCDE1234F">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wide mb-1">
                                Gotra / Guardian Name (Optional for Puja Sankalpam)
                            </label>
                            <input type="text" id="donor_guardian" name="guardian" maxlength="100"
                                   class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-2xl text-xs sm:text-sm font-semibold text-gray-900 focus:outline-none focus:border-brandOrange focus:bg-white transition min-h-[44px]"
                                   placeholder="Father / Husband / Gotra details">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wide mb-1">
                                Devotional Note / Sankalpam Message (Optional)
                            </label>
                            <textarea id="donor_message" name="message" rows="2" maxlength="500"
                                      class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-2xl text-xs sm:text-sm font-semibold text-gray-900 focus:outline-none focus:border-brandOrange focus:bg-white transition"
                                      placeholder="Any special prayer, prayer request, or note for the samiti..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- STEP 4: PAYMENT GATEWAY SELECTION -->
                <div class="space-y-3">
                    <label class="block text-xs font-black text-brandDarkGray uppercase tracking-wider flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-brandOrange text-white text-[10px] flex items-center justify-center font-bold">4</span>
                        <span>Select Payment Gateway</span>
                        <span class="text-red-500">*</span>
                    </label>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- Option A: Cashfree -->
                        <label class="gateway-option-card relative flex items-center p-4 rounded-2xl border-2 cursor-pointer transition select-none border-brandOrange bg-orange-50/60 ring-2 ring-brandOrange/20" id="gw_card_cashfree">
                            <input type="radio" name="payment_gateway" value="cashfree" checked onchange="handleGatewayChange('cashfree')" class="sr-only">
                            <div class="flex items-center gap-3 w-full">
                                <div class="w-5 h-5 rounded-full border-2 border-brandOrange flex items-center justify-center shrink-0">
                                    <div class="w-2.5 h-2.5 rounded-full bg-brandOrange" id="gw_dot_cashfree"></div>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs sm:text-sm font-black text-gray-900 uppercase">Cashfree Payments</span>
                                        <span class="text-[9px] font-black bg-orange-200 text-orange-900 px-2 py-0.5 rounded uppercase">Instant</span>
                                    </div>
                                    <span class="text-[10px] text-gray-500 font-semibold block mt-0.5">UPI, Google Pay, PhonePe, Cards, NetBanking</span>
                                </div>
                            </div>
                        </label>

                        <!-- Option B: Razorpay -->
                        <label class="gateway-option-card relative flex items-center p-4 rounded-2xl border-2 cursor-pointer transition select-none border-gray-200 bg-gray-50/50 hover:border-orange-300" id="gw_card_razorpay">
                            <input type="radio" name="payment_gateway" value="razorpay" onchange="handleGatewayChange('razorpay')" class="sr-only">
                            <div class="flex items-center gap-3 w-full">
                                <div class="w-5 h-5 rounded-full border-2 border-gray-400 flex items-center justify-center shrink-0">
                                    <div class="w-2.5 h-2.5 rounded-full bg-transparent" id="gw_dot_razorpay"></div>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs sm:text-sm font-black text-gray-900 uppercase">Razorpay Payments</span>
                                        <span class="text-[9px] font-black bg-blue-100 text-blue-900 px-2 py-0.5 rounded uppercase">Verified</span>
                                    </div>
                                    <span class="text-[10px] text-gray-500 font-semibold block mt-0.5">UPI, QR Code, Credit/Debit Cards, NetBanking</span>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- SUBMIT BUTTON & LOADER -->
                <div class="pt-4 space-y-3">
                    <button type="submit" id="submit_donation_btn" class="w-full bg-brandOrange hover:bg-orange-600 text-white font-black text-sm sm:text-base py-4 px-6 rounded-2xl shadow-xl shadow-orange-500/25 uppercase tracking-wider transition transform hover:scale-[1.01] flex items-center justify-center gap-2 min-h-[52px] cursor-pointer">
                        <span id="btn_icon">🔒</span>
                        <span id="btn_label">Proceed to Contribute</span>
                    </button>

                    <div class="flex flex-wrap items-center justify-center gap-4 text-[10px] text-gray-500 font-semibold pt-1">
                        <span class="flex items-center gap-1">🛡️ 256-Bit SSL Encrypted</span>
                        <span class="flex items-center gap-1">📜 Section 80G Tax Deductible</span>
                        <span class="flex items-center gap-1">🧾 Instant Official Receipt</span>
                    </div>
                </div>

            </form>

        </div>

    </div>

</div>

<!-- ====================================================================== -->
<!-- TOAST NOTIFICATION POPUP -->
<!-- ====================================================================== -->
<div id="toast_notification" class="fixed bottom-6 right-6 z-50 transform translate-y-24 opacity-0 transition-all duration-300 max-w-sm w-full bg-gray-900 text-white p-4 rounded-2xl shadow-2xl flex items-center gap-3 border border-gray-700">
    <div id="toast_icon" class="text-xl shrink-0">✅</div>
    <div class="flex-1">
        <h4 id="toast_title" class="text-xs font-black uppercase tracking-wider text-orange-400">Notice</h4>
        <p id="toast_msg" class="text-xs font-medium text-gray-200 mt-0.5">Notification text</p>
    </div>
</div>

<!-- ====================================================================== -->
<!-- LOAD PAYMENT GATEWAY CLIENT SDKS (CSP Permitted) -->
<!-- ====================================================================== -->
<!-- 1. Cashfree PG SDK v3 -->
<script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script>
<!-- 2. Razorpay Checkout SDK -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<!-- ====================================================================== -->
<!-- CLIENT-SIDE INTERACTIVE PAYMENT & CAROUSEL SCRIPT -->
<!-- ====================================================================== -->
<script>
    const activeCarouselIndicesMap = {};

    function moveCarouselSlider(campaignId, direction) {
        const track = document.getElementById(`carousel_track_${campaignId}`);
        if (!track) return;
        const totalSlidesCount = track.children.length;
        if (totalSlidesCount <= 1) return;

        if (activeCarouselIndicesMap[campaignId] === undefined) {
            activeCarouselIndicesMap[campaignId] = 0;
        }

        let currentActiveIndex = activeCarouselIndicesMap[campaignId] + direction;
        if (currentActiveIndex >= totalSlidesCount) currentActiveIndex = 0;
        else if (currentActiveIndex < 0) currentActiveIndex = totalSlidesCount - 1;

        activeCarouselIndicesMap[campaignId] = currentActiveIndex;
        track.style.transform = `translateX(-${currentActiveIndex * 100}%)`;
    }

    function copyCampaignLink(url, btn) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(url).then(() => {
                showToast('Link Copied!', 'Campaign link copied to clipboard. Share it with your friends and family.', '🔗');
                const textSpan = btn.querySelector('.copy-btn-text');
                if (textSpan) {
                    const orig = textSpan.innerText;
                    textSpan.innerText = 'Copied!';
                    setTimeout(() => textSpan.innerText = orig, 2000);
                }
            }).catch(() => {
                prompt('Copy this link:', url);
            });
        } else {
            prompt('Copy this link:', url);
        }
    }

    function selectCampaignAndScroll(campaignId, campaignTitle) {
        const select = document.getElementById('campaign_id');
        if (select) {
            select.value = campaignId;
        }
        const formSection = document.getElementById('donate_form_section');
        if (formSection) {
            formSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            showToast('Cause Selected', `Designated to: ${campaignTitle}`, '🌺');
        }
    }

    function handleCustomAmountInput(value) {
        const val = parseFloat(value) || 0;
        updateButtonLabel(val);
    }

    function updateButtonLabel(amount) {
        const btnLabel = document.getElementById('btn_label');
        if (btnLabel) {
            if (amount && amount > 0) {
                btnLabel.innerText = `Proceed to Contribute ₹${amount.toLocaleString('en-IN')}`;
            } else {
                btnLabel.innerText = 'Proceed to Contribute';
            }
        }
    }

    let selectedGateway = 'cashfree';

    function handleGatewayChange(gw) {
        selectedGateway = gw;
        const cardCf = document.getElementById('gw_card_cashfree');
        const cardRzp = document.getElementById('gw_card_razorpay');
        const dotCf = document.getElementById('gw_dot_cashfree');
        const dotRzp = document.getElementById('gw_dot_razorpay');

        if (gw === 'cashfree') {
            cardCf.className = 'gateway-option-card relative flex items-center p-4 rounded-2xl border-2 cursor-pointer transition select-none border-brandOrange bg-orange-50/60 ring-2 ring-brandOrange/20';
            cardRzp.className = 'gateway-option-card relative flex items-center p-4 rounded-2xl border-2 cursor-pointer transition select-none border-gray-200 bg-gray-50/50 hover:border-orange-300';
            dotCf.className = 'w-2.5 h-2.5 rounded-full bg-brandOrange';
            dotRzp.className = 'w-2.5 h-2.5 rounded-full bg-transparent';
        } else {
            cardRzp.className = 'gateway-option-card relative flex items-center p-4 rounded-2xl border-2 cursor-pointer transition select-none border-brandOrange bg-orange-50/60 ring-2 ring-brandOrange/20';
            cardCf.className = 'gateway-option-card relative flex items-center p-4 rounded-2xl border-2 cursor-pointer transition select-none border-gray-200 bg-gray-50/50 hover:border-orange-300';
            dotRzp.className = 'w-2.5 h-2.5 rounded-full bg-brandOrange';
            dotCf.className = 'w-2.5 h-2.5 rounded-full bg-transparent';
        }
    }

    function showToast(title, message, icon = 'ℹ️') {
        const toast = document.getElementById('toast_notification');
        const tTitle = document.getElementById('toast_title');
        const tMsg = document.getElementById('toast_msg');
        const tIcon = document.getElementById('toast_icon');
        if (!toast) return;

        tTitle.innerText = title;
        tMsg.innerText = message;
        tIcon.innerText = icon;

        toast.classList.remove('translate-y-24', 'opacity-0');
        toast.classList.add('translate-y-0', 'opacity-100');

        setTimeout(() => {
            toast.classList.remove('translate-y-0', 'opacity-100');
            toast.classList.add('translate-y-24', 'opacity-0');
        }, 4500);
    }

    // =========================================================================
    // MAIN SECURE PAYMENT DISPATCH HANDLER
    // =========================================================================
    async function handleDonationSubmit(event) {
        event.preventDefault();

        const submitBtn = document.getElementById('submit_donation_btn');
        const btnIcon = document.getElementById('btn_icon');
        const btnLabel = document.getElementById('btn_label');

        const name = document.getElementById('donor_name').value.trim();
        const phone = document.getElementById('donor_phone').value.trim();
        const email = document.getElementById('donor_email').value.trim();
        const amount = parseFloat(document.getElementById('donation_amount').value);
        const pan = document.getElementById('donor_pan').value.trim();
        const guardian = document.getElementById('donor_guardian').value.trim();
        const campaignId = document.getElementById('campaign_id').value;
        const message = document.getElementById('donor_message').value.trim();

        if (!name || !phone || !email || !amount) {
            showToast('Missing Details', 'Please fill all required fields before proceeding.', '⚠️');
            return;
        }

        if (amount < 1 || amount > 500000) {
            showToast('Invalid Amount', 'Contribution amount must be between ₹1 and ₹5,00,000.', '⚠️');
            return;
        }

        // Lock button
        submitBtn.disabled = true;
        btnIcon.innerText = '⏳';
        btnLabel.innerText = 'Initiating Secure Gateway Session...';

        const payload = {
            donor_name: name,
            phone: phone,
            email: email,
            amount: amount,
            pan_number: pan || null,
            guardian: guardian || null,
            campaign_id: campaignId ? parseInt(campaignId) : null,
            message: message || null,
        };

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

        try {
            if (selectedGateway === 'cashfree') {
                // Cashfree Flow
                const res = await fetch('{{ route("donations.initiate_cashfree") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();
                if (!data.success) {
                    throw new Error(data.message || 'Failed to initialize Cashfree session.');
                }

                const sessionId = data.session_data?.payment_session_id;

                if (data.is_simulated || !sessionId) {
                    showToast('Simulation Mode', 'Directing to confirmation...', '⚡');
                    window.location.href = `{{ url('/donations/cashfree-return') }}?order_id=${data.session_data.order_id}&donation_id=${data.donation_id}`;
                    return;
                }

                // Open Cashfree PG Checkout
                const cashfree = Cashfree({ mode: '{{ config("services.cashfree.environment", "sandbox") }}' === 'production' ? 'production' : 'sandbox' });
                cashfree.checkout({
                    paymentSessionId: sessionId,
                    redirectTarget: '_self'
                });

            } else {
                // Razorpay Flow
                const res = await fetch('{{ route("donations.initiate_razorpay") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();
                if (!data.success) {
                    throw new Error(data.message || 'Failed to initialize Razorpay order.');
                }

                const session = data.session_data;

                if (data.is_simulated || !session.key_id || session.key_id === 'rzp_test_simulation') {
                    showToast('Simulation Mode', 'Verifying transaction...', '⚡');
                    // Direct simulated verification
                    const verifyRes = await fetch('{{ route("donations.verify_razorpay") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            donation_id: data.donation_id,
                            razorpay_payment_id: 'SIM_PAY_' + Date.now(),
                            razorpay_signature: 'SIM_SIGNATURE'
                        })
                    });
                    const verifyData = await verifyRes.json();
                    window.location.href = `{{ url('/donations/status') }}/${data.donation_id}`;
                    return;
                }

                // Open Official Razorpay Checkout Modal
                const options = {
                    key: session.key_id, // Public Key only
                    amount: session.amount_paise,
                    currency: session.currency || 'INR',
                    name: 'ABVHPS CENTRAL BOARD',
                    description: 'Sanatana Dharma Contribution',
                    image: '{{ asset("images/ABVHPS_LOGO.jpg") }}',
                    order_id: session.razorpay_order_id,
                    prefill: {
                        name: session.donor_name,
                        email: session.donor_email,
                        contact: session.donor_phone
                    },
                    theme: {
                        color: '#FF6600'
                    },
                    handler: async function (response) {
                        btnLabel.innerText = 'Verifying Payment Authenticity...';
                        try {
                            const verifyRes = await fetch('{{ route("donations.verify_razorpay") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken
                                },
                                body: JSON.stringify({
                                    donation_id: data.donation_id,
                                    razorpay_payment_id: response.razorpay_payment_id,
                                    razorpay_signature: response.razorpay_signature
                                })
                            });
                            const verifyData = await verifyRes.json();
                            if (verifyData.success) {
                                window.location.href = `{{ url('/donations/status') }}/${data.donation_id}`;
                            } else {
                                throw new Error(verifyData.message || 'Payment verification failed.');
                            }
                        } catch (err) {
                            showToast('Verification Error', err.message || 'Could not verify payment.', '❌');
                            window.location.href = `{{ url('/donations/status') }}/${data.donation_id}`;
                        }
                    },
                    modal: {
                        ondismiss: function () {
                            submitBtn.disabled = false;
                            btnIcon.innerText = '🔒';
                            updateButtonLabel(amount);
                            showToast('Payment Cancelled', 'You cancelled the payment. You can retry at any time.', 'ℹ️');
                        }
                    }
                };

                const rzp = new Razorpay(options);
                rzp.open();
            }
        } catch (error) {
            submitBtn.disabled = false;
            btnIcon.innerText = '🔒';
            updateButtonLabel(amount);
            showToast('Payment Error', error.message || 'Unable to communicate with payment gateway. Please try again.', '❌');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const featuredId = @json(isset($featuredCampaign) && $featuredCampaign ? $featuredCampaign->id : null);
        if (featuredId) {
            const el = document.getElementById('campaign_' + featuredId);
            if (el) {
                setTimeout(() => {
                    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 300);
            }
        }
    });
</script>
@endsection
