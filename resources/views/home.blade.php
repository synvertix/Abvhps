@extends('layouts.app')

@section('title', 'ABVHPS | Akhanda Bharatha Viswa Hindu Parirakshana Samiti')
@section('meta_description', 'Akhanda Bharatha Viswa Hindu Parirakshana Samiti (ABVHPS) is dedicated to preserving Sanatana Dharma, constructing temples, protecting goshalas, Annapurna seva, and community empowerment across India.')

@section('content')
    @php
        $homeBanner = \App\Models\Banner::getBannerForPage('home');
    @endphp

    @if($homeBanner && !empty($homeBanner->desktop_banner))
        {{-- Dynamic Home Page Banner Configured via Admin --}}
        <div class="relative w-full overflow-hidden bg-gray-900 min-h-[420px] md:h-[450px] flex items-center justify-center border-b-4 border-brandOrange shadow-md"
             data-banner-page="home">
            <picture class="absolute inset-0 w-full h-full">
                @if(!empty($homeBanner->mobile_banner))
                    <source media="(max-width: 640px)" srcset="{{ asset('storage/' . $homeBanner->mobile_banner) }}">
                @endif
                <source media="(min-width: 641px)" srcset="{{ asset('storage/' . $homeBanner->desktop_banner) }}">
                <img src="{{ asset('storage/' . $homeBanner->desktop_banner) }}"
                     alt="{{ $homeBanner->title ?? 'ABVHPS Home' }}"
                     class="w-full h-full object-cover object-center"
                     style="z-index: 0;">
            </picture>

            {{-- Subtle dark overlay for text readability --}}
            <div class="absolute inset-0 pointer-events-none" style="background: rgba(5, 15, 30, 0.42); z-index: 1;"></div>

            {{-- Banner Text Content --}}
            <div class="relative z-10 flex flex-col justify-center items-center text-center px-4 max-w-4xl mx-auto py-12">
                @if($homeBanner->title)
                    <h2 class="text-white text-3xl md:text-5xl font-extrabold mb-4 drop-shadow-md uppercase tracking-wide">
                        {{ $homeBanner->title }}
                    </h2>
                @endif
                @if($homeBanner->subtitle)
                    <p class="text-brandLightOrange text-base md:text-xl max-w-2xl drop-shadow-sm font-medium">
                        {{ $homeBanner->subtitle }}
                    </p>
                @endif
            </div>
        </div>
    @else
        <!-- 1. Hero Section — Video Background with Rotating Slides -->
        <div class="relative w-full overflow-hidden bg-gradient-to-br from-[#2a1204] via-[#4a1f06] to-[#0f172a] h-[460px] sm:h-[480px]" data-banner-page="home" id="hero-slider" role="region" aria-roledescription="carousel" aria-label="ABVHPS highlights">

            {{-- Background Video (falls back to the warm gradient above if it cannot load) --}}
            <video
                class="absolute inset-0 w-full h-full object-cover object-center"
                style="z-index: 0;"
                autoplay
                muted
                loop
                playsinline
                preload="metadata"
                aria-hidden="true"
            >
                <source src="{{ asset('images/hero.mp4') }}" type="video/mp4">
            </video>

            {{-- Warm dark overlay for text readability --}}
            <div class="absolute inset-0" style="background: linear-gradient(180deg, rgba(20, 8, 2, 0.55) 0%, rgba(20, 8, 2, 0.48) 45%, rgba(20, 8, 2, 0.62) 100%); z-index: 1;"></div>

            {{-- Slowly turning golden mandala watermark --}}
            <svg class="hero-mandala absolute left-1/2 top-1/2 w-[40rem] h-[40rem] text-[#FFE7A3]/[0.09] pointer-events-none" style="z-index: 1;" viewBox="0 0 200 200" aria-hidden="true"><use href="#abvhps-mandala" width="200" height="200"/></svg>

            {{-- Slides --}}
            @foreach($sliders as $index => $slider)
                <div class="hero-slide absolute inset-0 transition-opacity duration-1000 ease-in-out {{ $index == 0 ? 'opacity-100' : 'opacity-0 pointer-events-none' }}"
                     id="slide-{{ $index }}" data-hero-slide role="group" aria-roledescription="slide" aria-label="{{ $index + 1 }} of {{ count($sliders) }}" @if($index != 0) aria-hidden="true" @endif style="z-index: 2;">
                    @if(!empty($slider['image_url']))
                        <img src="{{ $slider['image_url'] }}" alt="" class="absolute inset-0 w-full h-full object-cover" onerror="this.remove()" @if($index != 0) loading="lazy" @endif>
                        <div class="absolute inset-0 bg-black/35"></div>
                    @endif
                    <div class="absolute inset-0 flex flex-col justify-center items-center text-center px-6 sm:px-10">
                        <span class="hero-rise inline-flex items-center gap-2 text-[10px] sm:text-xs font-black uppercase tracking-[0.3em] text-[#FFE7A3] mb-4" style="--d: 0ms">
                            <span class="h-px w-8 sm:w-12 bg-gradient-to-r from-transparent to-[#FFE7A3]"></span>
                            <span>&#2384; ABVHPS</span>
                            <span class="h-px w-8 sm:w-12 bg-gradient-to-l from-transparent to-[#FFE7A3]"></span>
                        </span>
                        @if($index == 0)
                            <h1 class="hero-rise text-white text-3xl sm:text-4xl md:text-5xl font-extrabold mb-4 drop-shadow-lg max-w-4xl leading-tight" style="--d: 120ms">{{ __($slider['title']) }}</h1>
                        @else
                            <h2 class="hero-rise text-white text-3xl sm:text-4xl md:text-5xl font-extrabold mb-4 drop-shadow-lg max-w-4xl leading-tight" style="--d: 120ms">{{ __($slider['title']) }}</h2>
                        @endif
                        <p class="hero-rise text-brandLightOrange text-base md:text-xl max-w-2xl drop-shadow-md leading-relaxed" style="--d: 240ms">{{ __($slider['subtitle']) }}</p>
                        @if(!empty($slider['cta_label']) && !empty($slider['cta_url']))
                            <a href="{{ $slider['cta_url'] }}" class="hero-rise mt-7 inline-flex items-center gap-2 bg-gradient-to-r from-[#E8890C] to-[#FF6600] hover:from-[#F5A524] hover:to-[#FF7A1A] text-white text-xs sm:text-sm font-black uppercase tracking-wider px-6 py-3 rounded-full shadow-lg shadow-black/30 ring-1 ring-[#FFE7A3]/60 transition focus:outline-none focus:ring-2 focus:ring-white" style="--d: 360ms">
                                <span>{{ __($slider['cta_label']) }}</span><span aria-hidden="true">&rarr;</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach

            @if(count($sliders) > 1)
                {{-- Previous / Next --}}
                <button type="button" id="hero-prev" class="absolute left-2 sm:left-5 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/30 hover:bg-black/55 text-white flex items-center justify-center backdrop-blur-sm ring-1 ring-white/25 transition focus:outline-none focus:ring-2 focus:ring-[#FFE7A3]" style="z-index: 5;" aria-label="Previous slide">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg>
                </button>
                <button type="button" id="hero-next" class="absolute right-2 sm:right-5 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/30 hover:bg-black/55 text-white flex items-center justify-center backdrop-blur-sm ring-1 ring-white/25 transition focus:outline-none focus:ring-2 focus:ring-[#FFE7A3]" style="z-index: 5;" aria-label="Next slide">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 5l7 7-7 7"/></svg>
                </button>

                {{-- Dots --}}
                <div class="absolute bottom-6 left-0 right-0 flex items-center justify-center gap-2.5" style="z-index: 5;" id="hero-dots">
                    @foreach($sliders as $index => $slider)
                        <button type="button" class="hero-dot h-2.5 rounded-full transition-all duration-300 {{ $index == 0 ? 'w-7 bg-[#FFE7A3]' : 'w-2.5 bg-white/55 hover:bg-white' }}" data-hero-dot="{{ $index }}" aria-label="Go to slide {{ $index + 1 }}"></button>
                    @endforeach
                </div>
            @endif

            {{-- Gold base line --}}
            <div class="absolute bottom-0 inset-x-0 h-1" style="z-index: 6; background: linear-gradient(90deg, #B8860B, #F6D77B 25%, #FF6600 50%, #F6D77B 75%, #B8860B);"></div>

            <style>
                .hero-mandala { transform: translate(-50%, -50%); animation: heroMandala 160s linear infinite; }
                @keyframes heroMandala { to { transform: translate(-50%, -50%) rotate(360deg); } }
                /* Text rises in each time a slide becomes active */
                .hero-slide .hero-rise { opacity: 0; transform: translateY(16px); }
                .hero-slide.opacity-100 .hero-rise { opacity: 1; transform: none; transition: opacity .8s ease var(--d, 0ms), transform .8s ease var(--d, 0ms); }
                @media (prefers-reduced-motion: reduce) {
                    .hero-mandala { animation: none; }
                    .hero-slide .hero-rise { opacity: 1; transform: none; transition: none; }
                }
            </style>
        </div>
    @endif


{{-- Sanskrit shloka strip: sets the devotional tone right below the hero --}}
<section class="relative overflow-hidden bg-gradient-to-r from-[#FFF1D0] via-[#FFF9EC] to-[#FFF1D0] border-b border-amber-200/80" id="shloka-strip" aria-label="Sanskrit shloka">
    <svg class="absolute -left-10 top-1/2 -translate-y-1/2 w-44 h-44 text-[#D4A017]/[0.16] pointer-events-none" viewBox="0 0 200 200" aria-hidden="true"><use href="#abvhps-mandala" width="200" height="200"/></svg>
    <svg class="absolute -right-10 top-1/2 -translate-y-1/2 w-44 h-44 text-[#D4A017]/[0.16] pointer-events-none" viewBox="0 0 200 200" aria-hidden="true"><use href="#abvhps-mandala" width="200" height="200"/></svg>
    <div class="relative max-w-5xl mx-auto px-4 py-5 sm:py-6 text-center">
        <p lang="sa" class="text-xl sm:text-2xl font-extrabold text-[#8A5A00] tracking-wide" style="font-family: 'Noto Sans Devanagari', 'Nirmala UI', 'Mangal', serif;">&#2405; &#2343;&#2352;&#2381;&#2350;&#2379; &#2352;&#2325;&#2381;&#2359;&#2340;&#2367; &#2352;&#2325;&#2381;&#2359;&#2367;&#2340;&#2307; &#2405;</p>
        <p class="text-xs sm:text-sm text-gray-600 mt-1.5 font-semibold">{{ __('Dharma protects those who protect it.') }}</p>
    </div>
</section>

{{-- Official Latest Announcements Desk --}}
@if(isset($publishedExams) && $publishedExams->isNotEmpty())
<section class="bg-amber-50 border-y border-amber-200 py-3 px-4">
    <div class="max-w-6xl mx-auto flex flex-col md:flex-row items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-2 text-amber-900 font-bold">
            <span class="bg-amber-600 text-white text-[10px] uppercase tracking-wider font-black px-2 py-0.5 rounded">{{ __('Announcement') }}</span>
            <span>📢 Examination Results Announced:</span>
            <span class="font-normal text-gray-800">
                @foreach($publishedExams as $pExam)
                    <strong class="font-bold">{{ $pExam->exam_title }}</strong>@if(!$loop->last), @endif
                @endforeach
                — results are now available.
            </span>
        </div>
        <a href="{{ route('exam.results_portal') }}"
           class="bg-amber-700 hover:bg-amber-800 text-white font-black text-[11px] px-3.5 py-1.5 rounded uppercase tracking-wider transition whitespace-nowrap">
            {{ __('View Results') }} →
        </a>
    </div>
</section>
@endif

<!-- 2. Organization Origin & Message From Guru Garu -->
<section class="relative overflow-hidden py-16 sm:py-20 px-4 bg-gradient-to-b from-white via-[#FFFAF0] to-white" id="divine-origin">
    {{-- Golden mandala watermarks --}}
    <svg class="absolute -left-40 -top-32 w-[30rem] h-[30rem] text-[#D4A017]/[0.09] pointer-events-none" viewBox="0 0 200 200" aria-hidden="true"><use href="#abvhps-mandala" width="200" height="200"/></svg>
    <svg class="absolute -right-32 -bottom-40 w-[28rem] h-[28rem] text-[#D4A017]/[0.08] pointer-events-none" viewBox="0 0 200 200" aria-hidden="true"><use href="#abvhps-mandala" width="200" height="200"/></svg>

    <div class="relative max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-5 gap-10 lg:gap-14 items-center">
        {{-- Story --}}
        <div class="lg:col-span-3" data-reveal>
            <div class="inline-flex items-center gap-2.5 mb-3">
                <span class="text-lg text-[#B8860B] font-bold" aria-hidden="true">&#2384;</span>
                <span class="text-[#B8860B] font-black tracking-[0.3em] text-[11px] sm:text-xs uppercase">{{ __('Our Divine Origin') }}</span>
                <span class="h-px w-14 bg-gradient-to-r from-[#D4A017] to-transparent" aria-hidden="true"></span>
            </div>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-brandGray leading-tight">{!! __('Why and How :name Was Founded', ['name' => '<span class="text-brandOrange">ABVHPS</span>']) !!}</h2>
            <div class="flex items-center gap-2 mt-4 mb-6 text-[#D4A017]" aria-hidden="true">
                <span class="h-px w-16 bg-gradient-to-r from-[#D4A017] to-transparent"></span>
                <span class="text-[10px]">&#9670;</span>
            </div>

            <p class="text-gray-700 leading-8 text-[15px] sm:text-base mb-4">
                {!! __(config('abvhps.copy.origin_1'), ['guru' => '<strong class="text-brandGray">' . e(config('abvhps.copy.guru')) . '</strong>']) !!}
            </p>
            <p class="text-gray-700 leading-8 text-[15px] sm:text-base">
                {{ __(config('abvhps.copy.origin_2')) }}
            </p>

            <div class="mt-7 grid grid-cols-3 gap-3 sm:gap-4 max-w-xl">
                <div class="rounded-xl border border-[#E9C46A]/60 bg-white/80 px-3 py-3 text-center shadow-sm">
                    <span class="block text-xl sm:text-2xl font-extrabold text-brandOrange leading-none">2023</span>
                    <span class="block text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-gray-500 mt-1.5">{{ __('Founded') }}</span>
                </div>
                <div class="rounded-xl border border-[#E9C46A]/60 bg-white/80 px-3 py-3 text-center shadow-sm">
                    <span class="block text-xl sm:text-2xl font-extrabold text-brandOrange leading-none">20/2023</span>
                    <span class="block text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-gray-500 mt-1.5">{{ __('Registration No.') }}</span>
                </div>
                <div class="rounded-xl border border-[#E9C46A]/60 bg-white/80 px-3 py-3 text-center shadow-sm">
                    <span class="block text-xl sm:text-2xl font-extrabold text-brandOrange leading-none" aria-hidden="true">&#2384;</span>
                    <span class="block text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-gray-500 mt-1.5">{{ __('Charitable Trust') }}</span>
                </div>
            </div>
        </div>

        {{-- Divine Blessings --}}
        <div class="lg:col-span-2" data-reveal style="--d: 150ms">
            <figure class="relative overflow-hidden rounded-3xl border border-[#E9C46A]/70 bg-gradient-to-br from-[#FFF6E0] via-white to-[#FFEFCF] p-7 sm:p-9 shadow-xl shadow-amber-900/10">
                <span class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-[#B8860B] via-[#F6D77B] to-[#B8860B]" aria-hidden="true"></span>
                <svg class="absolute -right-14 -bottom-14 w-56 h-56 text-[#D4A017]/[0.12] pointer-events-none" viewBox="0 0 200 200" aria-hidden="true"><use href="#abvhps-mandala" width="200" height="200"/></svg>

                <div class="relative">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="flex items-center justify-center w-11 h-11 rounded-full bg-gradient-to-br from-[#FFF3D1] to-[#FBE3A1] ring-2 ring-[#D4A017]/50 ring-offset-2 ring-offset-white text-xl text-[#B8860B] font-bold" aria-hidden="true">&#2384;</span>
                        <figcaption class="text-[#B8860B] font-black uppercase tracking-[0.25em] text-[11px] sm:text-xs">{{ __('Divine Blessings') }}</figcaption>
                    </div>

                    <svg class="w-9 h-9 text-[#D4A017]/70 mb-1" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M9.6 5C6.5 6.7 4 9.7 4 13.6V19h6.6v-6.6H7.3c.1-2.1 1.3-3.8 3.5-5L9.6 5zm9 0c-3.1 1.7-5.6 4.7-5.6 8.6V19h6.6v-6.6h-3.3c.1-2.1 1.3-3.8 3.5-5L18.6 5z"/></svg>

                    <blockquote class="font-serif italic text-[17px] sm:text-lg leading-8 text-gray-800">
                        {{ __(config('abvhps.copy.blessing')) }}
                    </blockquote>

                    <div class="flex items-center gap-3 mt-6 pt-5 border-t border-[#D4A017]/30">
                        <span class="h-px w-8 bg-[#D4A017]" aria-hidden="true"></span>
                        <div>
                            <span class="block text-sm font-extrabold text-brandGray leading-tight">{{ config('abvhps.copy.guru') }}</span>
                            <span class="block text-[11px] font-bold uppercase tracking-wider text-[#B8860B] mt-0.5">{{ __('Rajaguru') }}</span>
                        </div>
                    </div>
                </div>
            </figure>
        </div>
    </div>

    <style>
        /* Scroll reveal — items are only hidden once JS has armed them, so no-JS visitors still see everything */
        [data-reveal].reveal-armed { opacity: 0; transform: translateY(22px); }
        [data-reveal].reveal-armed.is-in { opacity: 1; transform: none; transition: opacity .8s ease var(--d, 0ms), transform .8s ease var(--d, 0ms); }
        @media (prefers-reduced-motion: reduce) { [data-reveal].reveal-armed { opacity: 1; transform: none; } }
    </style>
    <script>
        (function () {
            var items = document.querySelectorAll('#divine-origin [data-reveal]');
            if (!items.length || !('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); } });
            }, { threshold: 0.15 });
            items.forEach(function (el) { el.classList.add('reveal-armed'); io.observe(el); });
        })();
    </script>
</section>

<!-- 3. Vision, Mission & Goal Section -->
<section class="py-12 px-4 bg-gradient-to-b from-[#FFF8EC] to-[#FFFDF8] border-t border-amber-200/70 relative @if(!empty($joinStrip['enabled'])) pb-16 sm:pb-20 @endif">
    @include('partials.pillars')

    @if(!empty($joinStrip['enabled']))
    <!-- Floating Upper Layer: Join / Volunteer Strip -->
    <div class="max-w-6xl mx-auto px-0 sm:px-4 -mb-24 sm:-mb-28 mt-8 sm:mt-10 relative z-20" id="homepage-floating-join-strip">
        <div class="bg-white rounded-2xl p-6 sm:p-8 md:p-10 border border-orange-100 shadow-xl shadow-orange-950/10 transition hover:shadow-2xl">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-center">
                <!-- Left Side: Why Join ABVHPS? (7 cols) -->
                <div class="lg:col-span-7 space-y-3">
                    <div class="inline-flex items-center gap-1.5 text-[11px] font-black text-brandOrange uppercase tracking-wider bg-orange-50 px-2.5 py-1 rounded-full border border-orange-200/60">
                        <span>🕉️</span>
                        <span>{{ __('Seva Community') }}</span>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-extrabold text-brandGray uppercase tracking-tight">
                        {{ __($joinStrip['why_heading'] ?? 'WHY JOIN ABVHPS?') }}
                    </h3>
                    <p class="text-xs sm:text-sm text-gray-600 leading-relaxed font-medium">
                        {{ __($joinStrip['why_text'] ?? 'Become part of a service-oriented community committed to Dharma, social service, cultural awareness and organized voluntary service.') }}
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-2 text-[11px] font-bold text-gray-700">
                        <div class="flex items-center gap-1.5">
                            <span class="text-brandOrange text-xs font-black">✓</span>
                            <span>{{ __('Serve the Community') }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-brandOrange text-xs font-black">✓</span>
                            <span>{{ __('Support Dharma Activities') }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-brandOrange text-xs font-black">✓</span>
                            <span>{{ __('Participate in Seva Programs') }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-brandOrange text-xs font-black">✓</span>
                            <span>{{ __('Build Local Leadership') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Right Side: Membership CTA (5 cols) -->
                <div class="lg:col-span-5 bg-gradient-to-br from-orange-50/70 to-amber-50/40 p-5 sm:p-6 rounded-xl border border-orange-200/70 flex flex-col justify-between space-y-4">
                    <div>
                        <span class="text-[10px] font-black text-brandOrange uppercase tracking-wider block">{{ __('ABVHPS MEMBERSHIP') }}</span>
                        <h4 class="text-base sm:text-lg font-extrabold text-gray-900 uppercase tracking-tight mt-0.5">
                            {{ __($joinStrip['member_heading'] ?? 'BECOME AN ABVHPS MEMBER') }}
                        </h4>
                        <p class="text-xs text-gray-600 leading-relaxed mt-1.5 font-medium">
                            {{ __($joinStrip['member_text'] ?? 'Join our growing community and participate in Dharma, Seva, cultural and social initiatives through ABVHPS.') }}
                        </p>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 pt-1">
                        <a href="{{ route('membership.form') }}" class="inline-flex items-center justify-center gap-2 bg-brandOrange hover:bg-orange-600 text-white text-xs font-black py-3 px-5 rounded-xl shadow-md uppercase tracking-wider transition group">
                            <span>{{ __($joinStrip['cta_text'] ?? 'BECOME A MEMBER') }}</span>
                            <span class="group-hover:translate-x-1 transition-transform">→</span>
                        </a>
                        @if(!empty($joinStrip['secondary_cta_text']) && !empty($joinStrip['secondary_cta_url']))
                            <a href="{{ __($joinStrip['secondary_cta_url']) }}" class="inline-flex items-center justify-center text-xs font-bold text-gray-700 hover:text-brandOrange py-2 px-3 transition uppercase tracking-wider">
                                {{ __($joinStrip['secondary_cta_text']) }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</section>

<!-- 4. Live Counter Statistics Strip (Transition Band) -->
@php
    // Show only real, non-zero counters. Zero counters are hidden (never padded with invented numbers);
    // the band is completed with true organisation facts instead.
    $statTiles = [];
    foreach ([['donors', 'Verified Donors'], ['members', 'Registered Members'], ['volunteers', 'Total Volunteers']] as [$statKey, $statLabel]) {
        if ((int) ($liveCounts[$statKey] ?? 0) > 0) {
            $statTiles[] = ['value' => (int) $liveCounts[$statKey], 'label' => $statLabel, 'count' => true];
        }
    }
    if ((int) ($liveCounts['years'] ?? 0) > 0) {
        $statTiles[] = ['value' => (int) $liveCounts['years'], 'label' => 'Years of Service', 'count' => true];
    }
    foreach ([['2023', 'Established'], ['20/2023', 'Registration No.']] as [$factValue, $factLabel]) {
        if (count($statTiles) < 4) {
            $statTiles[] = ['value' => $factValue, 'label' => $factLabel, 'count' => false];
        }
    }
    $statCols = count($statTiles) >= 4 ? 'grid-cols-2 md:grid-cols-4' : 'grid-cols-3';
@endphp
<section class="stats-devotional w-full text-white @if(!empty($joinStrip['enabled'])) pt-20 sm:pt-24 pb-12 @else py-12 @endif px-4 relative overflow-hidden z-10" id="homepage-statistics-strip">
    {{-- Devotional watermarks: slow-turning mandala + a soft second one --}}
    <svg class="stats-mandala absolute -right-28 top-1/2 -translate-y-1/2 w-[34rem] h-[34rem] text-[#FFE7A3]/20 pointer-events-none" viewBox="0 0 200 200" aria-hidden="true"><use href="#abvhps-mandala" width="200" height="200"/></svg>
    <svg class="absolute -left-24 -bottom-28 w-80 h-80 text-white/10 pointer-events-none" viewBox="0 0 200 200" aria-hidden="true"><use href="#abvhps-mandala" width="200" height="200"/></svg>

    <div class="relative z-10 max-w-6xl mx-auto">
        <div class="flex items-center justify-center gap-3 mb-7 text-[#FFE7A3]" aria-hidden="true">
            <span class="h-px w-12 sm:w-28 bg-gradient-to-r from-transparent to-[#FFE7A3]/80"></span>
            <span class="text-[10px] sm:text-xs font-black uppercase tracking-[0.3em] text-white">{{ __('Our Seva in Numbers') }}</span>
            <span class="h-px w-12 sm:w-28 bg-gradient-to-l from-transparent to-[#FFE7A3]/80"></span>
        </div>

        <div class="grid {{ $statCols }} gap-y-8 text-center">
            @foreach($statTiles as $tile)
                <div class="px-2 {{ !$loop->first ? 'md:border-l md:border-[#FFE7A3]/35' : '' }}">
                    <span class="block text-3xl sm:text-4xl font-extrabold mb-1 tracking-tight drop-shadow-[0_2px_6px_rgba(110,35,0,0.35)]" @if($tile['count']) data-count-to="{{ $tile['value'] }}" @endif>{{ $tile['count'] ? number_format($tile['value']) : $tile['value'] }}</span>
                    <span class="text-[11px] sm:text-xs uppercase font-bold tracking-wider text-amber-50/95">{{ __($tile['label']) }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <style>
        /* Deep saffron -> orange -> warm gold, with a soft golden glow, framed by gold hairlines */
        .stats-devotional {
            background:
                radial-gradient(60rem 22rem at 85% -10%, rgba(255, 214, 102, 0.38), transparent 60%),
                radial-gradient(40rem 20rem at 5% 110%, rgba(140, 30, 0, 0.35), transparent 60%),
                linear-gradient(110deg, #C93F00 0%, #F26200 42%, #F28A0F 100%);
        }
        .stats-devotional::before, .stats-devotional::after {
            content: ""; position: absolute; left: 0; right: 0; height: 3px; pointer-events: none;
            background: linear-gradient(90deg, transparent, #F6D77B 20%, #B8860B 50%, #F6D77B 80%, transparent);
        }
        .stats-devotional::before { top: 0; }
        .stats-devotional::after { bottom: 0; }
        .stats-mandala { animation: mandalaSpin 140s linear infinite; }
        @keyframes mandalaSpin { to { transform: translateY(-50%) rotate(360deg); } }
        @media (prefers-reduced-motion: reduce) { .stats-mandala { animation: none; } }
    </style>
    <script>
        // Count-up when the band scrolls into view. The final numbers are already in the HTML, so no-JS visitors see them too.
        (function () {
            var band = document.getElementById('homepage-statistics-strip');
            var nodes = band ? band.querySelectorAll('[data-count-to]') : [];
            if (!nodes.length || !('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
            var fmt = function (n) { return n.toLocaleString('en-IN'); };
            var run = function () {
                nodes.forEach(function (el) {
                    var target = parseInt(el.getAttribute('data-count-to'), 10) || 0, start = null, dur = 1600;
                    var step = function (ts) {
                        if (start === null) start = ts;
                        var p = Math.min((ts - start) / dur, 1), eased = 1 - Math.pow(1 - p, 3);
                        el.textContent = fmt(Math.round(target * eased));
                        if (p < 1) requestAnimationFrame(step);
                    };
                    el.textContent = fmt(0);
                    requestAnimationFrame(step);
                });
            };
            new IntersectionObserver(function (entries, obs) {
                if (entries[0].isIntersecting) { run(); obs.disconnect(); }
            }, { threshold: 0.35 }).observe(band);
        })();
    </script>
</section>

<!-- 5. Fundraising Campaigns (Dynamic Admin Fundraising Campaigns) -->
<section class="py-16 px-4 bg-white border-t border-gray-200">
    <div class="max-w-6xl mx-auto">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-4">
            <div>
                <span class="text-xs font-bold text-brandOrange uppercase tracking-wider block">{{ __('Dharma Seva Initiatives') }}</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-brandGray uppercase tracking-tight mt-1">{{ __('Fundraising Campaigns') }}</h2>
                <p class="text-xs text-gray-500 mt-1">{{ __('Support meaningful initiatives and help us serve communities across India.') }}</p>
                <div class="h-1 w-16 bg-brandOrange mt-3"></div>
            </div>
            @if(isset($fundraisingCampaigns) && $fundraisingCampaigns->isNotEmpty())
                <a href="{{ route('donations.grid') }}" class="bg-brandOrange hover:bg-opacity-90 text-white text-xs font-bold px-4 py-2.5 rounded-lg shadow-sm transition uppercase tracking-wider inline-flex items-center gap-1 shrink-0 self-start sm:self-auto">
                    {{ __('View All Campaigns') }} →
                </a>
            @endif
        </div>

        @if(isset($fundraisingCampaigns) && $fundraisingCampaigns->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($fundraisingCampaigns as $campaign)
                    @php
                        $target = $campaign->target_amount ?? 1;
                        $raised = $campaign->raised_amount ?? 0;
                        $percent = $target > 0 ? min(round(($raised / $target) * 100, 2), 100) : 0;
                    @endphp
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden flex flex-col justify-between hover:shadow-md transition">
                        <div>
                            <!-- Campaign Image & Badges -->
                            <div class="aspect-[16/10] w-full bg-gray-100 overflow-hidden relative">
                                @if(!empty($campaign->cover_image))
                                    <img src="{{ asset('storage/' . $campaign->cover_image) }}" class="w-full h-full object-cover" alt="{{ $campaign->title }}">
                                @elseif(!empty($campaign->image_path))
                                    <img src="{{ asset('storage/' . $campaign->image_path) }}" class="w-full h-full object-cover" alt="{{ $campaign->title }}">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-3xl">🌾</div>
                                @endif
                                <span class="absolute top-2.5 left-2.5 bg-brandOrange text-white text-[9px] font-black px-2.5 py-0.5 rounded-full uppercase shadow-xs">
                                    {{ __('Active Cause') }}
                                </span>
                                @if(!empty($campaign->end_date))
                                    <span class="absolute top-2.5 right-2.5 bg-black/60 backdrop-blur-xs text-white text-[9px] font-bold px-2 py-0.5 rounded uppercase">
                                        Ends: {{ \Carbon\Carbon::parse($campaign->end_date)->format('d-M-Y') }}
                                    </span>
                                @endif
                            </div>

                            <!-- Campaign Details -->
                            <div class="p-5">
                                <h3 class="font-bold text-sm text-brandGray line-clamp-2 uppercase h-10 mb-2">
                                    {{ $campaign->title }}
                                </h3>
                                <p class="text-xs text-gray-500 line-clamp-2 leading-relaxed mb-4">
                                    {{ strip_tags($campaign->description ?? '') }}
                                </p>
                                
                                @if(!empty($campaign->video_path))
                                    <div class="mb-3">
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-brandOrange bg-orange-50 px-2 py-0.5 rounded border border-orange-200">
                                            🎥 Video Briefing Available
                                        </span>
                                    </div>
                                @endif

                                <!-- Progress Bar & Amounts -->
                                <div class="w-full bg-gray-100 h-2.5 rounded-full overflow-hidden mb-2">
                                    <div class="bg-brandOrange h-full rounded-full transition-all duration-500" style="width: {{ $percent }}%"></div>
                                </div>
                                <div class="flex justify-between text-[11px] font-bold text-gray-600">
                                    <span>{{ __('Raised:') }} <strong class="text-brandOrange font-mono">{{ \App\Models\FundraisingCampaign::formatIndianCurrency($raised) }}</strong> ({{ $percent }}%)</span>
                                    <span>{{ __('Target:') }} <strong class="text-gray-900 font-mono">{{ \App\Models\FundraisingCampaign::formatIndianCurrency($target) }}</strong></span>
                                </div>
                            </div>
                        </div>

                        <!-- Action CTA Button & WhatsApp Share -->
                        <div class="p-5 pt-0 space-y-2">
                            <div class="grid grid-cols-2 gap-2">
                                <a href="{{ route('donations.campaign', $campaign->id) }}" class="block w-full bg-brandOrange hover:bg-opacity-90 text-white font-bold text-center py-2.5 px-3 rounded-xl text-xs uppercase tracking-wider transition">
                                    Contribute →
                                </a>
                                <a href="{{ $campaign->whatsapp_share_url }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center gap-1.5 bg-[#25D366] hover:bg-[#20ba59] text-white font-bold text-center py-2.5 px-3 rounded-xl text-xs uppercase tracking-wider shadow-xs transition" aria-label="Share {{ $campaign->title }} on WhatsApp">
                                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24" aria-hidden="true"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.694.072-2.176-.543-1.894-.787-3.111-2.724-3.206-2.85-.095-.125-.769-1.025-.769-1.954 0-.93.486-1.385.66-1.575.174-.189.38-.238.508-.238.127 0 .253.002.364.007.117.006.275-.044.429.327.16.386.547 1.332.595 1.43.048.098.08.213.016.338-.064.126-.096.205-.19.316-.095.111-.2.247-.286.332-.095.095-.194.198-.083.389.111.19.493.814 1.057 1.317.725.646 1.337.846 1.528.941.19.095.302.08.413-.048.111-.127.476-.556.603-.746.127-.19.254-.158.428-.095.175.063 1.111.524 1.301.62.19.095.317.143.365.222.048.079.048.46-.096.865z"/></svg>
                                    <span>{{ __('Share') }}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Clean Empty State -->
            <div class="text-center py-12 bg-gray-50 rounded-2xl border border-gray-200 p-8 max-w-md mx-auto">
                <span class="text-3xl block mb-2">🕉️</span>
                <h3 class="text-sm font-bold text-gray-700 uppercase">{{ __('Fundraising Campaigns') }}</h3>
                <p class="text-xs text-gray-500 mt-1">{{ __('No active fundraising campaigns at the moment.') }}</p>
            </div>
        @endif
    </div>
</section>

<!-- 6. Our Core Service Projects (Managed from Admin Panel / our_supports) -->
<section class="py-16 px-4 bg-gray-50 border-t border-gray-100">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-12">
            <span class="text-xs font-bold text-brandOrange uppercase tracking-wider block">{{ __('Comprehensive Seva Modules') }}</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-brandGray uppercase tracking-tight mt-1">{{ __('Our Core Service Projects') }}</h2>
            <p class="text-xs text-gray-500 mt-1">{{ __('Seva in action — caring for temples, Goshalas, Annapurna meals and children\'s literacy.') }}</p>
            <div class="h-1 w-16 bg-brandOrange mx-auto mt-3"></div>
        </div>

        @if(isset($projects) && count($projects) > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($projects as $project)
                    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition flex flex-col justify-between h-full">
                        <div>
                            <!-- Project Image Component Frame -->
                            <div class="mb-4 aspect-[16/10] w-full bg-gray-50 rounded-lg overflow-hidden border border-gray-100 flex items-center justify-center">
                                @if($project->image_path)
                                    <img src="{{ asset('storage/' . $project->image_path) }}" class="w-full h-full object-cover" alt="{{ $project->name }}">
                                @else
                                    <span class="text-3xl">🌱</span>
                                @endif
                            </div>

                            <!-- Project Official Title Name -->
                            <h3 class="font-bold text-base text-brandGray uppercase tracking-wide mb-2">
                                {{ $project->name }}
                            </h3>

                            <!-- Controlled 3-Line Text Description Fragment -->
                            <p class="text-xs text-gray-500 leading-relaxed mb-4 line-clamp-3 font-medium">
                                {{ strip_tags($project->short_info) }}
                            </p>
                        </div>

                        <!-- Explore Single Core Detail Action Button Fixed Link -->
                        <div class="pt-2 border-t border-gray-50">
                            <a href="{{ route('public.project.show', $project->id) }}" class="text-xs font-black text-brandOrange hover:text-brandGray uppercase tracking-wider inline-flex items-center gap-1 transition">
                                Explore Project <span class="text-sm">→</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-10 bg-white rounded-xl border border-gray-200 p-8 max-w-md mx-auto">
                <span class="text-3xl block mb-2">🌱</span>
                <p class="text-xs text-gray-500 font-medium">{{ __('Core service project records will appear here.') }}</p>
            </div>
        @endif
    </div>
</section>

<!-- 7. Our Supporting Partners / Sponsors Compact Scrolling Marquee Strip -->
@if(!empty($sponsorsStrip['enabled']) && (!empty($sponsorsStrip['partners']) || !empty($sponsorsStrip['sponsors'])))
@php
    $partnersList = !empty($sponsorsStrip['partners']) ? $sponsorsStrip['partners'] : array_map(fn($n) => ['name' => $n, 'logo_path' => null], $sponsorsStrip['sponsors']);
@endphp
<section class="py-5 sm:py-6 bg-gradient-to-b from-slate-50 via-orange-50/20 to-slate-50 border-t border-b border-slate-200/80 overflow-hidden relative" id="homepage-sponsors-strip" aria-label="{{ __($sponsorsStrip['heading'] ?? 'Our Supporting Partners') }}">
    <div class="max-w-6xl mx-auto px-4 mb-3 text-center">
        <span class="text-[9.5px] font-extrabold text-brandOrange uppercase tracking-widest block mb-0.5">{{ __('Collaborations & Trust') }}</span>
        <h2 class="text-base sm:text-lg font-extrabold text-slate-800 uppercase tracking-tight">
            {{ __($sponsorsStrip['heading'] ?? 'OUR SUPPORTING PARTNERS') }}
        </h2>
    </div>

    <!-- Scrolling Marquee Container -->
    <div class="relative w-full overflow-hidden marquee-wrapper group py-1">
        <!-- Subtle edge gradients for smooth fade in/out on desktop -->
        <div class="hidden sm:block absolute left-0 top-0 bottom-0 w-16 sm:w-20 bg-gradient-to-r from-slate-50 via-slate-50/80 to-transparent z-10 pointer-events-none"></div>
        <div class="hidden sm:block absolute right-0 top-0 bottom-0 w-16 sm:w-20 bg-gradient-to-l from-slate-50 via-slate-50/80 to-transparent z-10 pointer-events-none"></div>

        <div class="flex marquee-track group-hover:[animation-play-state:paused] items-center">
            <!-- Group A (Primary) -->
            <div class="flex items-center gap-6 sm:gap-8 shrink-0 marquee-group pr-6 sm:pr-8">
                @foreach($partnersList as $partner)
                    <div class="inline-flex items-center gap-2.5 px-4 py-2 sm:px-5 sm:py-2.5 bg-white/90 hover:bg-white border border-slate-200/90 hover:border-orange-300 rounded-xl shadow-2xs hover:shadow-xs hover:-translate-y-0.5 transition duration-200 group/card shrink-0">
                        @if(!empty($partner['logo_path']))
                            <div class="w-12 sm:w-14 h-7 sm:h-8 flex items-center justify-center shrink-0">
                                <img src="{{ asset('storage/' . $partner['logo_path']) }}" alt="{{ $partner['name'] }} logo" class="max-h-full max-w-full object-contain shrink-0 opacity-85 group-hover/card:opacity-100 transition-opacity">
                            </div>
                        @endif
                        <span class="font-bold text-xs sm:text-sm tracking-wide text-gray-800 group-hover/card:text-brandOrange whitespace-nowrap">
                            {{ $partner['name'] }}
                        </span>
                    </div>
                @endforeach
            </div>

            <!-- Group B (Duplicate - aria-hidden for screen readers) -->
            <div class="flex items-center gap-6 sm:gap-8 shrink-0 marquee-group pr-6 sm:pr-8" aria-hidden="true">
                @foreach($partnersList as $partner)
                    <div class="inline-flex items-center gap-2.5 px-4 py-2 sm:px-5 sm:py-2.5 bg-white/90 hover:bg-white border border-slate-200/90 hover:border-orange-300 rounded-xl shadow-2xs hover:shadow-xs hover:-translate-y-0.5 transition duration-200 group/card shrink-0">
                        @if(!empty($partner['logo_path']))
                            <div class="w-12 sm:w-14 h-7 sm:h-8 flex items-center justify-center shrink-0">
                                <img src="{{ asset('storage/' . $partner['logo_path']) }}" alt="{{ $partner['name'] }} logo" class="max-h-full max-w-full object-contain shrink-0 opacity-85 group-hover/card:opacity-100 transition-opacity">
                            </div>
                        @endif
                        <span class="font-bold text-xs sm:text-sm tracking-wide text-gray-800 group-hover/card:text-brandOrange whitespace-nowrap">
                            {{ $partner['name'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <style>
        .marquee-track {
            display: flex;
            width: max-content;
            animation: partnerMarquee 34s linear infinite;
        }

        @keyframes partnerMarquee {
            0% {
                transform: translateX(0);
            }
            100% {
                transform: translateX(-50%);
            }
        }

        @keyframes sponsorMarquee {
            0% {
                transform: translateX(0);
            }
            100% {
                transform: translateX(-50%);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .marquee-track {
                animation: none !important;
                width: 100% !important;
                flex-wrap: wrap !important;
                justify-content: center !important;
                gap: 0.75rem !important;
            }
            .marquee-group[aria-hidden="true"] {
                display: none !important;
            }
            .marquee-group {
                flex-wrap: wrap !important;
                justify-content: center !important;
                padding-right: 0 !important;
                gap: 0.75rem !important;
            }
        }
    </style>
</section>
@endif

<!-- 8. Connect With ABVHPS / Social Media Channels Strip -->
@if(!empty($socialStrip['enabled']) && !empty($socialStrip['platforms']))
<section class="py-8 sm:py-10 bg-white border-t border-slate-200/80" id="homepage-social-media-strip" aria-label="{{ __($socialStrip['heading'] ?? 'Connect With ABVHPS') }}">
    <div class="max-w-6xl mx-auto px-4 text-center">
        <span class="text-[10px] font-extrabold text-brandOrange uppercase tracking-widest block mb-1">{{ __('Official Channels & Updates') }}</span>
        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-800 uppercase tracking-tight mb-2">
            {{ __($socialStrip['heading'] ?? 'CONNECT WITH ABVHPS') }}
        </h2>
        @if(!empty($socialStrip['subtext']))
            <p class="text-xs sm:text-sm text-gray-600 max-w-2xl mx-auto leading-relaxed font-medium mb-6">
                {{ __($socialStrip['subtext']) }}
            </p>
        @endif

        <!-- Social Media Platform Buttons -->
        <div class="social-reveal-group flex flex-wrap items-center justify-center gap-2.5 sm:gap-3.5 max-w-4xl mx-auto">
            @foreach($socialStrip['platforms'] as $platformId => $platform)
                <a href="{{ $platform['url'] }}"
                   style="--reveal-delay: {{ $loop->index * 90 }}ms"
                   target="_blank"
                   rel="noopener noreferrer"
                   aria-label="{{ $platform['aria_label'] }}"
                   class="social-reveal-item group inline-flex items-center gap-2 px-3.5 py-2 sm:px-4 sm:py-2.5 bg-slate-50 hover:bg-white border {{ $platformId === 'janavedika' ? 'social-featured border-brandOrange/60' : 'border-slate-200' }} hover:border-brandOrange rounded-xl shadow-2xs hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-brandOrange">
                    @if($platformId === 'janavedika')
                    <img src="{{ asset('images/janavedika-logo.png') }}" alt="" width="256" height="147" loading="lazy" decoding="async" class="h-5 sm:h-6 w-auto shrink-0 group-hover:scale-110 transition-transform" aria-hidden="true">
                    @elseif($platformId === 'facebook')
                        <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5 fill-[#1877F2] shrink-0 group-hover:scale-110 transition-transform" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    @elseif($platformId === 'instagram')
                        <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5 fill-[#E4405F] shrink-0 group-hover:scale-110 transition-transform" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                        </svg>
                    @elseif($platformId === 'youtube')
                        <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5 fill-[#FF0000] shrink-0 group-hover:scale-110 transition-transform" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                        </svg>
                    @elseif($platformId === 'x')
                        <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5 fill-slate-900 shrink-0 group-hover:scale-110 transition-transform" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                        </svg>
                    @elseif($platformId === 'linkedin')
                        <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5 fill-[#0A66C2] shrink-0 group-hover:scale-110 transition-transform" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
                        </svg>
                    @elseif($platformId === 'whatsapp')
                        <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5 fill-[#25D366] shrink-0 group-hover:scale-110 transition-transform" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.694.072-2.176-.543-1.894-.787-3.111-2.724-3.206-2.85-.095-.125-.769-1.025-.769-1.954 0-.93.486-1.385.66-1.575.174-.189.38-.238.508-.238.127 0 .253.002.364.007.117.006.275-.044.429.327.16.386.547 1.332.595 1.43.048.098.08.213.016.338-.064.126-.096.205-.19.316-.095.111-.2.247-.286.332-.095.095-.194.198-.083.389.111.19.493.814 1.057 1.317.725.646 1.337.846 1.528.941.19.095.302.08.413-.048.111-.127.476-.556.603-.746.127-.19.254-.158.428-.095.175.063 1.111.524 1.301.62.19.095.317.143.365.222.048.079.048.46-.096.865z"/>
                        </svg>
                    @elseif($platformId === 'telegram')
                        <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5 fill-[#229ED9] shrink-0 group-hover:scale-110 transition-transform" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.16.16-.295.295-.605.295l.213-3.053 5.56-5.023c.242-.213-.054-.333-.373-.121l-6.871 4.326-2.962-.924c-.643-.204-.657-.643.136-.953l11.57-4.461c.537-.194 1.006.131.832.942z"/>
                        </svg>
                    @endif

                    <span class="font-bold text-xs sm:text-sm text-slate-800 group-hover:text-brandOrange transition-colors whitespace-nowrap">
                        {{ $platform['name'] }}
                    </span>
                </a>
            @endforeach
        </div>
    </div>

    <style>
        /* Social strip: staggered reveal. Only hides items when JS marks the group as armed, so no-JS users still see everything. */
        .social-reveal-group.is-armed .social-reveal-item { opacity: 0; transform: translateY(14px); }
        .social-reveal-group.is-armed.is-visible .social-reveal-item {
            opacity: 1; transform: none;
            transition: opacity .5s ease var(--reveal-delay, 0ms), transform .5s ease var(--reveal-delay, 0ms), box-shadow .2s ease, border-color .2s ease;
        }
        /* Featured (Janavedika) button: soft pulsing ring to draw the eye */
        .social-featured { position: relative; }
        .social-featured::after {
            content: ""; position: absolute; inset: -3px; border-radius: 0.9rem; pointer-events: none;
            border: 2px solid rgba(255, 102, 0, .55); opacity: 0; animation: socialPulse 2.6s ease-out 1.2s infinite;
        }
        @keyframes socialPulse { 0% { opacity: .8; transform: scale(.97); } 70%, 100% { opacity: 0; transform: scale(1.08); } }
        @media (prefers-reduced-motion: reduce) {
            .social-reveal-group.is-armed .social-reveal-item { opacity: 1; transform: none; }
            .social-featured::after { animation: none; }
        }
    </style>
    <script>
        (function () {
            var group = document.querySelector('#homepage-social-media-strip .social-reveal-group');
            if (!group || !('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
            group.classList.add('is-armed');
            new IntersectionObserver(function (entries, obs) {
                entries.forEach(function (e) {
                    if (e.isIntersecting) { group.classList.add('is-visible'); obs.disconnect(); }
                });
            }, { threshold: 0.2 }).observe(group);
        })();
    </script>
</section>
@endif

<!-- Hero slider: auto-advance, arrows, dots; pauses on hover/focus and when the tab is hidden -->
<script>
    (function () {
        var hero = document.getElementById('hero-slider');
        if (!hero) return;
        var slides = hero.querySelectorAll('[data-hero-slide]');
        if (slides.length < 2) return;
        var dots = hero.querySelectorAll('[data-hero-dot]');
        var current = 0, timer = null;
        var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        function show(n) {
            n = (n + slides.length) % slides.length;
            slides[current].classList.remove('opacity-100');
            slides[current].classList.add('opacity-0', 'pointer-events-none');
            slides[current].setAttribute('aria-hidden', 'true');
            slides[n].classList.remove('opacity-0', 'pointer-events-none');
            slides[n].classList.add('opacity-100');
            slides[n].removeAttribute('aria-hidden');
            dots.forEach(function (d, i) {
                var on = i === n;
                d.classList.toggle('w-7', on); d.classList.toggle('bg-[#FFE7A3]', on);
                d.classList.toggle('w-2.5', !on); d.classList.toggle('bg-white/55', !on);
            });
            current = n;
        }
        function start() { if (!reduceMotion && !timer) timer = setInterval(function () { show(current + 1); }, 5500); }
        function stop() { clearInterval(timer); timer = null; }

        var prev = document.getElementById('hero-prev'), next = document.getElementById('hero-next');
        if (prev) prev.addEventListener('click', function () { show(current - 1); stop(); start(); });
        if (next) next.addEventListener('click', function () { show(current + 1); stop(); start(); });
        dots.forEach(function (d) {
            d.addEventListener('click', function () { show(parseInt(d.getAttribute('data-hero-dot'), 10)); stop(); start(); });
        });
        hero.addEventListener('mouseenter', stop);
        hero.addEventListener('mouseleave', start);
        hero.addEventListener('focusin', stop);
        hero.addEventListener('focusout', start);
        document.addEventListener('visibilitychange', function () { document.hidden ? stop() : start(); });
        start();
    })();
</script>
@endsection
