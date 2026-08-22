<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- 1. Primary Page Title & Meta Description --}}
    <title>@yield('title', 'ABVHPS | Akhanda Bharatha Viswa Hindu Parirakshana Samiti')</title>
    <meta name="description" content="@yield('meta_description', 'Akhanda Bharatha Viswa Hindu Parirakshana Samiti (ABVHPS) is dedicated to preserving Sanatana Dharma, constructing temples, expanding goshalas, Annapurna daily meals, and community empowerment across India.')">
    <meta name="robots" content="@yield('meta_robots', 'index, follow')">

    {{-- 2. Canonical URL --}}
    <link rel="canonical" href="@yield('canonical_url', url()->current())">
    <link rel="icon" type="image/png" href="{{ asset('images/logo_abvhps.png') }}">

    {{-- 3. Open Graph / Facebook / WhatsApp Metadata --}}
    <meta property="og:site_name" content="ABVHPS">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('og_title', 'ABVHPS | Akhanda Bharatha Viswa Hindu Parirakshana Samiti')">
    <meta property="og:description" content="@yield('og_description', 'Akhanda Bharatha Viswa Hindu Parirakshana Samiti (ABVHPS) is dedicated to preserving Sanatana Dharma, constructing temples, expanding goshalas, and community empowerment across India.')">
    <meta property="og:url" content="@yield('og_url', url()->current())">
    <meta property="og:image" content="@yield('og_image', asset('images/ABVHPS_LOGO.jpg'))">
    <meta property="og:image:alt" content="@yield('og_image_alt', 'ABVHPS Emblem')">

    {{-- 4. Twitter / X Card Metadata --}}
    <meta name="twitter:card" content="@yield('twitter_card', 'summary_large_image')">
    <meta name="twitter:title" content="@yield('twitter_title', 'ABVHPS | Akhanda Bharatha Viswa Hindu Parirakshana Samiti')">
    <meta name="twitter:description" content="@yield('twitter_description', 'Official Portal of Akhanda Bharatha Viswa Hindu Parirakshana Samiti.')">
    <meta name="twitter:image" content="@yield('twitter_image', asset('images/ABVHPS_LOGO.jpg'))">

    {{-- 5. Schema.org JSON-LD Structured Data (Organization & WebSite) --}}
    @php
        $seoAppUrl = rtrim(config('app.url', 'https://abvhps.org'), '/');
        $schemaData = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    '@id' => $seoAppUrl . '/#organization',
                    'name' => 'Akhanda Bharatha Viswa Hindu Parirakshana Samiti',
                    'alternateName' => 'ABVHPS',
                    'url' => $seoAppUrl,
                    'logo' => asset('images/ABVHPS_LOGO.jpg'),
                    'contactPoint' => [
                        '@type' => 'ContactPoint',
                        'telephone' => \App\Models\SiteSetting::get('contact_phone', '+91 8884933379'),
                        'contactType' => 'customer service',
                        'email' => \App\Models\SiteSetting::get('contact_email', 'info@abvhps.org'),
                        'areaServed' => 'IN',
                        'availableLanguage' => ['en', 'te', 'hi']
                    ]
                ],
                [
                    '@type' => 'WebSite',
                    '@id' => $seoAppUrl . '/#website',
                    'url' => $seoAppUrl,
                    'name' => 'ABVHPS Official Portal',
                    'publisher' => [
                        '@id' => $seoAppUrl . '/#organization'
                    ]
                ]
            ]
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>

    <!-- Tailwind CSS v4 Browser/Play CDN Link -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style type="text/tailwindcss">
        @theme {
            --color-brandOrange: #FF6600;
            --color-brandGray: #4A4A4A;
            --color-brandDarkGray: #1A1A1A;
            --color-brandLightOrange: #FFF5EE;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans">

    <!-- 1. Top Header -->
    <header class="bg-brandGray text-white text-[11px] sm:text-xs py-2 px-4">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row justify-between items-center gap-2">
            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-x-4 gap-y-1">
                <span>📞 {{ \App\Models\SiteSetting::get('contact_phone', '+91 8884933379') }}</span>
                <span>✉️ {{ \App\Models\SiteSetting::get('contact_email', 'info@abvhps.org') }}</span>
            </div>
        </div>
    </header>

    <!-- 2. Main Navigation Bar with 12 Menu Items -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 flex justify-between items-center py-2.5 sm:py-3">
            <a href="/" class="flex items-center gap-3.5 group shrink-0">
                <!-- Circular Emblem (Enlarged 64px / w-16 h-16) -->
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full flex items-center justify-center overflow-hidden bg-orange-50/90 border-2 border-brandOrange shadow group-hover:border-orange-600 transition shrink-0 p-1">
                    <img src="{{ asset('images/logo_abvhps.png') }}" class="w-full h-full object-contain" alt="ABVHPS Emblem">
                </div>
                <!-- Stylized Wordmark Graphic (Enlarged to h-12 sm:h-14) -->
                <img src="{{ asset('images/logo.png') }}" class="h-11 sm:h-14 w-auto max-w-[170px] sm:max-w-[240px] object-contain shrink-0 transition group-hover:opacity-95" alt="Akhanda Bharata - Viswa Hindu Parirakshana Samiti">
            </a>

            <!-- Mobile Hamburger Button (< 1280px / xl) -->
            <button type="button" id="public-mobile-menu-btn" onclick="togglePublicMobileMenu()" class="xl:hidden flex items-center justify-center p-2.5 rounded-xl bg-orange-50 hover:bg-orange-100 text-brandOrange border border-orange-200 transition focus:outline-none focus:ring-2 focus:ring-brandOrange cursor-pointer min-w-[44px] min-h-[44px]" aria-label="Open navigation" aria-expanded="false" aria-controls="public-mobile-drawer">
                <svg id="public-hamburger-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>

            <!-- 12 Menu Navigation Links (Desktop >= 1280px / xl) -->
            <div class="hidden xl:flex items-center gap-4 font-semibold text-sm text-brandGray">
                <a href="/" class="hover:text-brandOrange transition">Home</a>
                <a href="/about" class="hover:text-brandOrange transition">About</a>
                <a href="{{ route('public.team') }}" class="nav-link">Our Team</a>
                <a href="/gallery" class="hover:text-brandOrange transition">Gallery</a>
                <a href="/membership" class="hover:text-brandOrange transition">Membership</a>
                <a href="/volunteer" class="hover:text-brandOrange transition">Volunteer</a>

                <!-- Fixed Exam Sub-Menu Dropdown Desk with Notice Board -->
                <div class="relative group py-2">
                    <button class="hover:text-brandOrange transition cursor-pointer flex items-center gap-1 focus:outline-none">
                        <span>Exam</span>
                        <span class="text-xs text-gray-400">▼</span>
                    </button>
                    <div class="absolute left-0 pt-2 w-48 hidden group-hover:block z-50 top-full">
                        <div class="bg-white border border-gray-200 rounded-lg shadow-xl py-1">
                            <a href="{{ route('public.exams_board') }}" class="block px-4 py-2 text-gray-700 hover:bg-brandLightOrange hover:text-brandOrange font-bold transition text-xs border-b border-gray-100">
                                📋 Exams Notice Board
                            </a>
                            <a href="{{ route('exam.form') }}" class="block px-4 py-2 text-gray-700 hover:bg-brandLightOrange hover:text-brandOrange font-medium transition text-xs">
                                Apply Online
                            </a>
                            <a href="{{ route('exam.results_portal') }}" class="block px-4 py-2 text-gray-700 hover:bg-brandLightOrange hover:text-brandOrange font-medium transition text-xs border-t border-gray-100">
                                View Results
                            </a>
                        </div>
                    </div>
                </div>

                <!-- GLOBAL OUR WINGS DROPDOWN DESK SYSTEM -->
                <div class="relative inline-block text-left group">
                    <button type="button" class="inline-flex items-center gap-1 font-bold text-gray-700 hover:text-brandOrange transition uppercase cursor-pointer py-2">
                        <span>OUR WINGS</span>
                        <svg class="w-4 h-4 transition transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    
                    <div class="absolute left-0 w-56 bg-white border border-gray-200 rounded-lg shadow-xl py-1 z-50 hidden group-hover:block transition animate-fadeIn">
                        <a href="{{ route('rudrasena.form') }}" class="flex items-center gap-2 px-4 py-2.5 text-xs font-black text-gray-700 hover:bg-orange-50 hover:text-brandOrange transition border-b border-gray-100">
                            <span>🔱</span> RUDRASENA DAL
                        </a>
                        <a href="{{ route('kalabrundam.form') }}" class="flex items-center gap-2 px-4 py-2.5 text-xs font-black text-gray-700 hover:bg-orange-50 hover:text-brandOrange transition border-b border-gray-100">
                            <span>🪘</span> KALA BRUNDAM
                        </a>
                        <a href="{{ route('gramasevadal.form') }}" class="flex items-center gap-2 px-4 py-2.5 text-xs font-black text-gray-700 hover:bg-orange-50 hover:text-brandOrange transition border-b border-gray-100">
                            <span>🌱</span> GRAMA SEVA DAL
                        </a>
                        <a href="{{ route('organicfarmers.form') }}" class="flex items-center gap-2 px-4 py-2.5 text-xs font-black text-gray-700 hover:bg-orange-50 hover:text-brandOrange transition">
                            <span>🌾</span> ORGANIC FARMERS
                        </a>
                    </div>
                </div>

                <a href="{{ route('donations.grid') }}" class="hover:text-brandOrange transition">FUNDRAISE</a>
                <a href="{{ route('public.blogs') }}" class="nav-link font-semibold text-gray-700 hover:text-orange-500 transition">Blogs</a>
                <a href="{{ route('public.contact') }}" class="hover:text-brandOrange transition">Contact</a>
                <a href="{{ route('donations.grid') }}" class="bg-brandOrange text-white px-4 py-2 rounded shadow hover:bg-opacity-90 transition">Donation</a>
            </div>

        </div>
    </nav>

    <!-- Scoped Navigation Scrollbar Styles -->
    <style>
        .public-nav-scrollbar::-webkit-scrollbar {
            width: 5px;
        }
        .public-nav-scrollbar::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.4);
        }
        .public-nav-scrollbar::-webkit-scrollbar-thumb {
            background: #f97316;
            border-radius: 4px;
        }
        .public-nav-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #ea580c;
        }
        .public-nav-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: #f97316 rgba(15, 23, 42, 0.4);
        }

        /* Glassmorphic Drawer & Backdrop (Image 1 Exact Visual Match) */
        #public-mobile-backdrop {
            background: rgba(0, 0, 0, 0.38) !important;
            backdrop-filter: blur(6px) !important;
            -webkit-backdrop-filter: blur(6px) !important;
        }

        #public-mobile-drawer {
            background: rgba(22, 30, 46, 0.30) !important;
            backdrop-filter: blur(14px) saturate(120%) !important;
            -webkit-backdrop-filter: blur(14px) saturate(120%) !important;
        }

        .public-nav-row {
            background: rgba(71, 85, 105, 0.20) !important;
            border: 1px solid rgba(255, 255, 255, 0.10) !important;
            backdrop-filter: blur(7px) !important;
            -webkit-backdrop-filter: blur(7px) !important;
            transition: all 0.2s ease;
        }

        .public-nav-row:hover {
            background: rgba(71, 85, 105, 0.38) !important;
            border-color: rgba(255, 255, 255, 0.20) !important;
            color: #ffffff;
        }

        .public-nav-row.is-active {
            background: #f97316 !important;
            border-color: rgba(255, 255, 255, 0.3) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.35);
        }

        .public-submenu-box {
            background: rgba(15, 23, 42, 0.40) !important;
            backdrop-filter: blur(6px) !important;
            -webkit-backdrop-filter: blur(6px) !important;
        }

        @media (prefers-reduced-motion: reduce) {
            #public-mobile-drawer,
            #public-mobile-backdrop,
            .public-nav-row {
                transition: none !important;
                transform: none !important;
            }
        }
    </style>

    <!-- Public Mobile/Tablet Navigation Backdrop (< 1280px / xl) -->
    <div id="public-mobile-backdrop" 
         class="fixed inset-0 z-[70] hidden opacity-0 transition-opacity duration-300 xl:hidden" 
         onclick="togglePublicMobileMenu(false)" 
         aria-hidden="true"></div>

    <!-- Public Mobile/Tablet Navigation Drawer (Translucent Glass Overlay - Image 1 Matched) -->
    <div id="public-mobile-drawer" 
         class="fixed inset-y-0 left-0 w-[min(360px,88vw)] text-white z-[80] shadow-2xl flex flex-col justify-between transform -translate-x-full transition-transform duration-300 ease-out xl:hidden select-none border-r border-white/10" 
         role="dialog" 
         aria-modal="true" 
         aria-label="Public Navigation Menu">
         
        <!-- Header Profile Block with 58-62px Orange Outlined Close Button (Opaque #0b1426) -->
        <div class="px-4 py-4 min-h-[96px] border-b border-white/10 flex items-center justify-between shrink-0 bg-[#0b1426]">
            <a href="/" onclick="togglePublicMobileMenu(false)" class="flex items-center gap-3 overflow-hidden">
                <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-brandOrange shadow-md flex items-center justify-center bg-white p-0.5 shrink-0">
                    <img src="{{ asset('images/logo_abvhps.png') }}" class="w-full h-full object-contain" alt="ABVHPS">
                </div>
                <div class="overflow-hidden">
                    <span class="text-[13px] font-black text-brandOrange uppercase tracking-wider block truncate">ABVHPS CENTRAL</span>
                    <span class="text-[9px] text-gray-400 font-bold uppercase tracking-wider block truncate mt-0.5">Parirakshana Samiti</span>
                </div>
            </a>

            <!-- 58-62px Square Close Button with Bold Orange Outline -->
            <button type="button" 
                    id="public-mobile-close-btn" 
                    onclick="togglePublicMobileMenu(false)" 
                    class="w-[58px] h-[58px] rounded-2xl bg-[#111c2e] border-[2.5px] border-brandOrange text-white hover:bg-brandOrange hover:text-white transition flex items-center justify-center cursor-pointer shadow-lg focus:outline-none focus:ring-2 focus:ring-brandOrange shrink-0" 
                    aria-label="Close navigation">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Navigation List (Translucent Surface with Readable Pill Rows) -->
        <nav class="flex-1 p-3.5 space-y-2 overflow-y-auto public-nav-scrollbar text-[11px] font-extrabold tracking-wider uppercase text-gray-200 min-h-0">
            
            <!-- SECTION 1: EXPLORE SAMITI -->
            <div class="pt-1 pb-1.5 border-b border-slate-600/40 text-[9.5px] text-brandOrange font-black tracking-widest uppercase">
                EXPLORE SAMITI
            </div>

            <a href="/" 
               onclick="togglePublicMobileMenu(false)" 
               class="public-nav-row flex items-center gap-3 px-3.5 py-2.5 rounded-xl min-h-[48px] text-gray-200 shadow-xs {{ request()->is('/') ? 'is-active' : '' }}"
               @if(request()->is('/')) aria-current="page" @endif>
                <span class="text-sm shrink-0">🏠</span> 
                <span class="truncate">HOME</span>
            </a>

            <a href="/about" 
               onclick="togglePublicMobileMenu(false)" 
               class="public-nav-row flex items-center gap-3 px-3.5 py-2.5 rounded-xl min-h-[48px] text-gray-200 shadow-xs {{ request()->is('about*') ? 'is-active' : '' }}"
               @if(request()->is('about*')) aria-current="page" @endif>
                <span class="text-sm shrink-0">📖</span> 
                <span class="truncate">ABOUT US</span>
            </a>

            <a href="{{ route('public.team') }}" 
               onclick="togglePublicMobileMenu(false)" 
               class="public-nav-row flex items-center gap-3 px-3.5 py-2.5 rounded-xl min-h-[48px] text-gray-200 shadow-xs {{ request()->routeIs('public.team*') ? 'is-active' : '' }}"
               @if(request()->routeIs('public.team*')) aria-current="page" @endif>
                <span class="text-sm shrink-0">👥</span> 
                <span class="truncate">OUR TEAM</span>
            </a>

            <a href="/gallery" 
               onclick="togglePublicMobileMenu(false)" 
               class="public-nav-row flex items-center gap-3 px-3.5 py-2.5 rounded-xl min-h-[48px] text-gray-200 shadow-xs {{ request()->is('gallery*') ? 'is-active' : '' }}"
               @if(request()->is('gallery*')) aria-current="page" @endif>
                <span class="text-sm shrink-0">🖼️</span> 
                <span class="truncate">MEDIA GALLERY</span>
            </a>

            <a href="/membership" 
               onclick="togglePublicMobileMenu(false)" 
               class="public-nav-row flex items-center gap-3 px-3.5 py-2.5 rounded-xl min-h-[48px] text-gray-200 shadow-xs {{ request()->is('membership*') ? 'is-active' : '' }}"
               @if(request()->is('membership*')) aria-current="page" @endif>
                <span class="text-sm shrink-0">💳</span> 
                <span class="truncate">MEMBERSHIP PORTAL</span>
            </a>

            <a href="/volunteer" 
               onclick="togglePublicMobileMenu(false)" 
               class="public-nav-row flex items-center gap-3 px-3.5 py-2.5 rounded-xl min-h-[48px] text-gray-200 shadow-xs {{ request()->is('volunteer*') ? 'is-active' : '' }}"
               @if(request()->is('volunteer*')) aria-current="page" @endif>
                <span class="text-sm shrink-0">🤝</span> 
                <span class="truncate">VOLUNTEER CADRE</span>
            </a>

            <!-- SECTION 2: ACADEMICS & SERVICES -->
            <div class="pt-3 pb-1.5 border-b border-slate-600/40 text-[9.5px] text-brandOrange font-black tracking-widest uppercase">
                ACADEMICS & SERVICES
            </div>

            <!-- Accordion 1: Exam -->
            <div class="rounded-xl overflow-hidden shadow-xs">
                <button type="button" 
                        onclick="togglePublicSubmenu('public-exam-submenu', 'public-exam-arrow')" 
                        class="public-nav-row w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-gray-200 hover:text-brandOrange transition focus:outline-none cursor-pointer min-h-[48px]">
                    <span class="flex items-center gap-3 font-extrabold text-[11px] uppercase tracking-wider">
                        <span class="text-sm">📝</span> <span>EXAMS INFO & RESULTS</span>
                    </span>
                    <svg id="public-exam-arrow" class="w-4 h-4 transition-transform duration-200 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="public-exam-submenu" 
                     class="public-submenu-box hidden px-2.5 pb-2 pt-1.5 space-y-1 rounded-b-xl border-x border-b border-white/10 text-[10.5px] font-bold mt-1">
                    <a href="{{ route('public.exams_board') }}" 
                       onclick="togglePublicMobileMenu(false)" 
                       class="block px-3 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-slate-700/70 transition border-b border-gray-800/60">
                        📋 EXAMS NOTICE BOARD
                    </a>
                    <a href="{{ route('exam.form') }}" 
                       onclick="togglePublicMobileMenu(false)" 
                       class="block px-3 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-slate-700/70 transition border-b border-gray-800/60">
                        ✍️ APPLY ONLINE
                    </a>
                    <a href="{{ route('exam.results_portal') }}" 
                       onclick="togglePublicMobileMenu(false)" 
                       class="block px-3 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-slate-700/70 transition">
                        🏆 VIEW RESULTS
                    </a>
                </div>
            </div>

            <!-- SECTION 3: OUR WINGS -->
            <div class="pt-3 pb-1.5 border-b border-slate-600/40 text-[9.5px] text-brandOrange font-black tracking-widest uppercase">
                OUR WINGS SUBSYSTEMS
            </div>

            <!-- Accordion 2: Our Wings -->
            <div class="rounded-xl overflow-hidden shadow-xs">
                <button type="button" 
                        onclick="togglePublicSubmenu('public-wings-submenu', 'public-wings-arrow')" 
                        class="public-nav-row w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-gray-200 hover:text-brandOrange transition focus:outline-none cursor-pointer min-h-[48px]">
                    <span class="flex items-center gap-3 font-extrabold text-[11px] uppercase tracking-wider">
                        <span class="text-sm">🚩</span> <span>OUR WINGS</span>
                    </span>
                    <svg id="public-wings-arrow" class="w-4 h-4 transition-transform duration-200 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="public-wings-submenu" 
                     class="public-submenu-box hidden px-2.5 pb-2 pt-1.5 space-y-1 rounded-b-xl border-x border-b border-white/10 text-[10.5px] font-bold mt-1">
                    <a href="{{ route('rudrasena.form') }}" 
                       onclick="togglePublicMobileMenu(false)" 
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-slate-700/70 transition border-b border-gray-800/60">
                        <span>🔱</span> RUDRASENA DAL
                    </a>
                    <a href="{{ route('kalabrundam.form') }}" 
                       onclick="togglePublicMobileMenu(false)" 
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-slate-700/70 transition border-b border-gray-800/60">
                        <span>🪘</span> KALA BRUNDAM
                    </a>
                    <a href="{{ route('gramasevadal.form') }}" 
                       onclick="togglePublicMobileMenu(false)" 
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-slate-700/70 transition border-b border-gray-800/60">
                        <span>🌱</span> GRAMA SEVA DAL
                    </a>
                    <a href="{{ route('organicfarmers.form') }}" 
                       onclick="togglePublicMobileMenu(false)" 
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-slate-700/70 transition">
                        <span>🌾</span> ORGANIC FARMERS
                    </a>
                </div>
            </div>

            <!-- SECTION 4: COMMUNITY & SUPPORT -->
            <div class="pt-3 pb-1.5 border-b border-slate-600/40 text-[9.5px] text-brandOrange font-black tracking-widest uppercase">
                COMMUNITY & SUPPORT
            </div>

            <a href="{{ route('donations.grid') }}" 
               onclick="togglePublicMobileMenu(false)" 
               class="public-nav-row flex items-center gap-3 px-3.5 py-2.5 rounded-xl min-h-[48px] text-gray-200 shadow-xs {{ request()->routeIs('donations.*') ? 'is-active' : '' }}"
               @if(request()->routeIs('donations.*')) aria-current="page" @endif>
                <span class="text-sm shrink-0">💰</span> 
                <span class="truncate">FUNDRAISE CAMPAIGNS</span>
            </a>

            <a href="{{ route('public.blogs') }}" 
               onclick="togglePublicMobileMenu(false)" 
               class="public-nav-row flex items-center gap-3 px-3.5 py-2.5 rounded-xl min-h-[48px] text-gray-200 shadow-xs {{ request()->routeIs('public.blogs*') ? 'is-active' : '' }}"
               @if(request()->routeIs('public.blogs*')) aria-current="page" @endif>
                <span class="text-sm shrink-0">📰</span> 
                <span class="truncate">BLOGS & UPDATES</span>
            </a>

            <a href="{{ route('public.contact') }}" 
               onclick="togglePublicMobileMenu(false)" 
               class="public-nav-row flex items-center gap-3 px-3.5 py-2.5 rounded-xl min-h-[48px] text-gray-200 shadow-xs {{ request()->routeIs('public.contact*') ? 'is-active' : '' }}"
               @if(request()->routeIs('public.contact*')) aria-current="page" @endif>
                <span class="text-sm shrink-0">📩</span> 
                <span class="truncate">CONTACT US</span>
            </a>

            <!-- CTA: MAKE A DONATION -->
            <div class="pt-2 pb-1">
                <a href="{{ route('donations.grid') }}" 
                   onclick="togglePublicMobileMenu(false)" 
                   class="w-full bg-brandOrange hover:bg-orange-600 text-white font-black text-center py-2.5 min-h-[48px] rounded-xl shadow-md transition flex items-center justify-center gap-2 uppercase tracking-wider text-xs border border-orange-400/50 cursor-pointer">
                    <span class="text-base">🙏</span> MAKE A DONATION
                </a>
            </div>
        </nav>

        <!-- Footer of Drawer (Opaque #0b1426) -->
        <div class="p-3.5 border-t border-white/10 space-y-1.5 text-[10px] shrink-0 bg-[#0b1426]">
            <div class="text-gray-400 text-[9px] space-y-0.5 font-bold">
                <div>📞 {{ \App\Models\SiteSetting::get('contact_phone', '+91 8884933379') }}</div>
                <div>✉️ {{ \App\Models\SiteSetting::get('contact_email', 'info@abvhps.org') }}</div>
            </div>
            <div class="text-center text-[8px] font-black text-gray-500 tracking-wider pt-1 border-t border-white/10">
                ABVHPS CENTRAL PORTAL V2.0
            </div>
        </div>
    </div>

    <!-- 3. Main Content Area -->
    <main>
        @yield('content')
    </main>

    <!-- 4. Footer Component -->
    <footer class="bg-brandDarkGray text-gray-300 pt-10 pb-4 px-4 mt-12">
        <div class="max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-6 border-b border-gray-700 pb-8">
            <div>
                <h3 class="text-white font-bold text-lg mb-4 text-brandOrange">About ABVHPS</h3>
                <p class="text-sm leading-relaxed">
                    {{ \App\Models\SiteSetting::get('footer_about', 'Dedicated to preserving and promoting Hindu culture and values worldwide under the behest of Rajaguru Sri Sri Sri Subrahmanneswara Swamy Garu.') }}
                </p>
            </div>
            <div>
                <h3 class="text-white font-bold text-lg mb-4 text-brandOrange">Quick Links</h3>
                <div class="grid grid-cols-1 gap-1.5 text-sm">
                    <a href="/about" class="hover:text-white">About Us</a>
                    <a href="/membership" class="hover:text-white">Membership</a>
                    <a href="/volunteer" class="hover:text-white">Volunteer</a>
                    <a href="/donation" class="hover:text-white">Donation</a>
                    <a href="{{ route('public.contact') }}" class="hover:text-white">Contact Us</a>
                    <a href="{{ route('public.certificates') }}" class="hover:text-white">80G / 12A</a>
                </div>
            </div>
            <div>
                <h3 class="text-white font-bold text-lg mb-4 text-brandOrange">Our Wings</h3>
                <div class="space-y-1.5 text-sm">
                    <a href="{{ route('rudrasena.form') }}" class="hover:text-white block">🔱 Rudrasena Dal</a>
                    <a href="{{ route('kalabrundam.form') }}" class="hover:text-white block">🪘 Kala Brundam</a>
                    <a href="{{ route('gramasevadal.form') }}" class="hover:text-white block">🌱 Grama Seva Dal</a>
                    <a href="{{ route('organicfarmers.form') }}" class="hover:text-white block font-bold text-emerald-400">🌾 Organic Farmers</a>
                </div>
            </div>
            <div>
                <h3 class="text-white font-bold text-lg mb-4 text-brandOrange">Services & Exams</h3>
                <div class="space-y-1.5 text-sm">
                    <a href="{{ route('public.exams_board') }}" class="hover:text-white block">Exams Notice Board</a>
                    <a href="{{ route('exam.form') }}" class="hover:text-white block">Exam Application</a>
                    <a href="{{ route('exam.results_portal') }}" class="hover:text-white block">Check Results</a>
                    <a href="{{ route('donations.grid') }}" class="hover:text-white block">Fundraise Campaigns</a>
                </div>
            </div>
            <div>
                <h3 class="text-white font-bold text-lg mb-4 text-brandOrange">Contact Us</h3>
                <p class="text-sm leading-relaxed mb-2">
                    {{ \App\Models\SiteSetting::get('contact_address', 'Survey No:1826, Shanmukhapuram, Akkalareddy Palli Village and Post, Porumamilla Mandalam, Kadapa, A.P - 516193') }}
                </p>
                <div class="text-xs font-mono text-gray-400 space-y-1">
                    <div>📞 {{ \App\Models\SiteSetting::get('contact_phone', '+91 8884933379') }}</div>
                    <div>✉️ {{ \App\Models\SiteSetting::get('contact_email', 'info@abvhps.org') }}</div>
                </div>
            </div>
        </div>
        <div class="text-center text-xs text-gray-500 pt-4">
            &copy; {{ date('Y') }} ABVHPS. All Rights Reserved.
        </div>
    </footer>

    <!-- Floating WhatsApp Quick Connect Button -->
    <x-whatsapp-floating-button />

    <script>
        (function() {
            var lastFocusedPublicElem = null;

            window.togglePublicMobileMenu = function(forceState) {
                var drawer = document.getElementById('public-mobile-drawer');
                var backdrop = document.getElementById('public-mobile-backdrop');
                var menuBtn = document.getElementById('public-mobile-menu-btn');
                var closeBtn = document.getElementById('public-mobile-close-btn');

                if (!drawer || !backdrop) return;

                var isOpen = !drawer.classList.contains('-translate-x-full');
                var shouldOpen = typeof forceState === 'boolean' ? forceState : !isOpen;

                if (shouldOpen) {
                    lastFocusedPublicElem = document.activeElement;
                    backdrop.classList.remove('hidden');
                    setTimeout(function() {
                        backdrop.classList.remove('opacity-0');
                        backdrop.classList.add('opacity-100');
                        drawer.classList.remove('-translate-x-full');
                        drawer.classList.add('translate-x-0');
                    }, 10);

                    document.body.style.overflow = 'hidden';
                    if (menuBtn) menuBtn.setAttribute('aria-expanded', 'true');
                    if (closeBtn) setTimeout(function() { closeBtn.focus(); }, 150);
                } else {
                    drawer.classList.remove('translate-x-0');
                    drawer.classList.add('-translate-x-full');
                    backdrop.classList.remove('opacity-100');
                    backdrop.classList.add('opacity-0');

                    setTimeout(function() {
                        backdrop.classList.add('hidden');
                        document.body.style.overflow = '';
                    }, 300);

                    if (menuBtn) menuBtn.setAttribute('aria-expanded', 'false');
                    if (lastFocusedPublicElem && typeof lastFocusedPublicElem.focus === 'function') {
                        lastFocusedPublicElem.focus();
                    }
                }
            };

            window.togglePublicSubmenu = function(submenuId, arrowId) {
                var submenu = document.getElementById(submenuId);
                var arrow = document.getElementById(arrowId);
                if (!submenu) return;

                var isHidden = submenu.classList.contains('hidden');
                if (isHidden) {
                    submenu.classList.remove('hidden');
                    if (arrow) arrow.classList.add('rotate-180');
                } else {
                    submenu.classList.add('hidden');
                    if (arrow) arrow.classList.remove('rotate-180');
                }
            };

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' || e.keyCode === 27) {
                    var drawer = document.getElementById('public-mobile-drawer');
                    if (drawer && !drawer.classList.contains('-translate-x-full')) {
                        window.togglePublicMobileMenu(false);
                    }
                }
            });
        })();
    </script>
</body>
</html>
