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
            @php
                $topSocialEnabled = in_array(\App\Models\SiteSetting::get('homepage_social_enabled', '1'), ['1', 'yes', true, 1], true);
                $topSocialLinks = $topSocialEnabled ? \App\Models\SiteSetting::getActiveSocialLinks() : [];
            @endphp
            @if(!empty($topSocialLinks))
                <div class="flex items-center gap-2 sm:gap-2.5 flex-wrap justify-center sm:justify-end" id="top-bar-social-links" aria-label="Social Media Channels">
                    @foreach($topSocialLinks as $platformId => $platform)
                        <a href="{{ $platform['url'] }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           aria-label="{{ $platform['aria_label'] }}"
                           class="w-6 h-6 rounded-full bg-white/10 hover:bg-brandOrange text-white flex items-center justify-center transition duration-150 focus:outline-none focus:ring-1 focus:ring-brandOrange"
                           title="{{ $platform['name'] }}">
                            @if($platformId === 'facebook')
                                <svg class="w-3.5 h-3.5 fill-current shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                            @elseif($platformId === 'instagram')
                                <svg class="w-3.5 h-3.5 fill-current shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                </svg>
                            @elseif($platformId === 'youtube')
                                <svg class="w-3.5 h-3.5 fill-current shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                </svg>
                            @elseif($platformId === 'x')
                                <svg class="w-3.5 h-3.5 fill-current shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                </svg>
                            @elseif($platformId === 'linkedin')
                                <svg class="w-3.5 h-3.5 fill-current shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
                                </svg>
                            @elseif($platformId === 'whatsapp')
                                <svg class="w-3.5 h-3.5 fill-current shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.694.072-2.176-.543-1.894-.787-3.111-2.724-3.206-2.85-.095-.125-.769-1.025-.769-1.954 0-.93.486-1.385.66-1.575.174-.189.38-.238.508-.238.127 0 .253.002.364.007.117.006.275-.044.429.327.16.386.547 1.332.595 1.43.048.098.08.213.016.338-.064.126-.096.205-.19.316-.095.111-.2.247-.286.332-.095.095-.194.198-.083.389.111.19.493.814 1.057 1.317.725.646 1.337.846 1.528.941.19.095.302.08.413-.048.111-.127.476-.556.603-.746.127-.19.254-.158.428-.095.175.063 1.111.524 1.301.62.19.095.317.143.365.222.048.079.048.46-.096.865z"/>
                                </svg>
                            @elseif($platformId === 'telegram')
                                <svg class="w-3.5 h-3.5 fill-current shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.16.16-.295.295-.605.295l.213-3.053 5.56-5.023c.242-.213-.054-.333-.373-.121l-6.871 4.326-2.962-.924c-.643-.204-.657-.643.136-.953l11.57-4.461c.537-.194 1.006.131.832.942z"/>
                                </svg>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif
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
                    <button class="hover:text-brandOrange transition cursor-pointer flex items-center focus:outline-none">
                        <span>Exam</span>
                    </button>
                    <div class="absolute left-0 pt-2 w-48 hidden group-hover:block z-50 top-full">
                        <div class="bg-white border border-gray-200 rounded-lg shadow-xl py-1">
                            <a href="{{ route('public.exams_board') }}" class="block px-4 py-2 text-gray-700 hover:bg-brandLightOrange hover:text-brandOrange font-bold transition text-xs border-b border-gray-100">
                                Exams Notice Board
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
                    <button type="button" class="font-bold text-gray-700 hover:text-brandOrange transition uppercase cursor-pointer py-2">
                        <span>OUR WINGS</span>
                    </button>
                    
                    <div class="absolute left-0 w-56 bg-white border border-gray-200 rounded-lg shadow-xl py-1 z-50 hidden group-hover:block transition animate-fadeIn">
                        <a href="{{ route('rudrasena.form') }}" class="block px-4 py-2.5 text-xs font-black text-gray-700 hover:bg-orange-50 hover:text-brandOrange transition border-b border-gray-100">
                            RUDRASENA DAL
                        </a>
                        <a href="{{ route('kalabrundam.form') }}" class="block px-4 py-2.5 text-xs font-black text-gray-700 hover:bg-orange-50 hover:text-brandOrange transition border-b border-gray-100">
                            KALA BRUNDAM
                        </a>
                        <a href="{{ route('gramasevadal.form') }}" class="block px-4 py-2.5 text-xs font-black text-gray-700 hover:bg-orange-50 hover:text-brandOrange transition border-b border-gray-100">
                            GRAMA SEVA DAL
                        </a>
                        <a href="{{ route('organicfarmers.form') }}" class="block px-4 py-2.5 text-xs font-black text-gray-700 hover:bg-orange-50 hover:text-brandOrange transition">
                            ORGANIC FARMERS
                        </a>
                    </div>
                </div>

                <a href="{{ route('donations.grid') }}" class="hover:text-brandOrange transition">FUNDRAISE</a>
                <a href="{{ route('public.blogs') }}" class="nav-link font-semibold text-gray-700 hover:text-orange-500 transition">Blogs</a>
                <a href="{{ route('public.contact') }}" class="hover:text-brandOrange transition">Contact</a>
                @if(!auth()->guard('web')->check() && !auth()->guard('volunteer')->check())
                <button type="button" onclick="openLoginModal()" class="cursor-pointer font-bold text-gray-700 hover:text-brandOrange transition uppercase inline-flex items-center gap-1.5 py-1.5 px-3 rounded-lg border border-gray-200 hover:border-brandOrange bg-white shadow-xs">
                    <svg class="w-4 h-4 text-brandOrange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                    <span>LOGIN</span>
                </button>
                @endif
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
               class="public-nav-row flex items-center px-3.5 py-2.5 rounded-xl min-h-[48px] text-gray-200 shadow-xs {{ request()->is('/') ? 'is-active' : '' }}"
               @if(request()->is('/')) aria-current="page" @endif>
                <span class="truncate">HOME</span>
            </a>

            <a href="/about" 
               onclick="togglePublicMobileMenu(false)" 
               class="public-nav-row flex items-center px-3.5 py-2.5 rounded-xl min-h-[48px] text-gray-200 shadow-xs {{ request()->is('about*') ? 'is-active' : '' }}"
               @if(request()->is('about*')) aria-current="page" @endif>
                <span class="truncate">ABOUT US</span>
            </a>

            <a href="{{ route('public.team') }}" 
               onclick="togglePublicMobileMenu(false)" 
               class="public-nav-row flex items-center px-3.5 py-2.5 rounded-xl min-h-[48px] text-gray-200 shadow-xs {{ request()->routeIs('public.team*') ? 'is-active' : '' }}"
               @if(request()->routeIs('public.team*')) aria-current="page" @endif>
                <span class="truncate">OUR TEAM</span>
            </a>

            <a href="/gallery" 
               onclick="togglePublicMobileMenu(false)" 
               class="public-nav-row flex items-center px-3.5 py-2.5 rounded-xl min-h-[48px] text-gray-200 shadow-xs {{ request()->is('gallery*') ? 'is-active' : '' }}"
               @if(request()->is('gallery*')) aria-current="page" @endif>
                <span class="truncate">MEDIA GALLERY</span>
            </a>

            <a href="/membership" 
               onclick="togglePublicMobileMenu(false)" 
               class="public-nav-row flex items-center px-3.5 py-2.5 rounded-xl min-h-[48px] text-gray-200 shadow-xs {{ request()->is('membership*') ? 'is-active' : '' }}"
               @if(request()->is('membership*')) aria-current="page" @endif>
                <span class="truncate">MEMBERSHIP PORTAL</span>
            </a>

            <a href="/volunteer" 
               onclick="togglePublicMobileMenu(false)" 
               class="public-nav-row flex items-center px-3.5 py-2.5 rounded-xl min-h-[48px] text-gray-200 shadow-xs {{ request()->is('volunteer*') ? 'is-active' : '' }}"
               @if(request()->is('volunteer*')) aria-current="page" @endif>
                <span class="truncate">VOLUNTEER CADRE</span>
            </a>

            <!-- SECTION 2: ACADEMICS & SERVICES -->
            <div class="pt-3 pb-1.5 border-b border-slate-600/40 text-[9.5px] text-brandOrange font-black tracking-widest uppercase">
                ACADEMICS & SERVICES
            </div>

            <!-- Accordion 1: Exam -->
            <div class="rounded-xl overflow-hidden shadow-xs">
                <button type="button"
                        onclick="togglePublicSubmenu('public-exam-submenu')"
                        class="public-nav-row w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-gray-200 hover:text-brandOrange transition focus:outline-none cursor-pointer min-h-[48px]">
                    <span class="font-extrabold text-[11px] uppercase tracking-wider">
                        EXAMS INFO & RESULTS
                    </span>
                </button>
                <div id="public-exam-submenu" 
                     class="public-submenu-box hidden px-2.5 pb-2 pt-1.5 space-y-1 rounded-b-xl border-x border-b border-white/10 text-[10.5px] font-bold mt-1">
                    <a href="{{ route('public.exams_board') }}" 
                       onclick="togglePublicMobileMenu(false)" 
                       class="block px-3 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-slate-700/70 transition border-b border-gray-800/60">
                        EXAMS NOTICE BOARD
                    </a>
                    <a href="{{ route('exam.form') }}" 
                       onclick="togglePublicMobileMenu(false)" 
                       class="block px-3 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-slate-700/70 transition border-b border-gray-800/60">
                        APPLY ONLINE
                    </a>
                    <a href="{{ route('exam.results_portal') }}" 
                       onclick="togglePublicMobileMenu(false)" 
                       class="block px-3 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-slate-700/70 transition">
                        VIEW RESULTS
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
                        onclick="togglePublicSubmenu('public-wings-submenu')"
                        class="public-nav-row w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-gray-200 hover:text-brandOrange transition focus:outline-none cursor-pointer min-h-[48px]">
                    <span class="font-extrabold text-[11px] uppercase tracking-wider">
                        OUR WINGS
                    </span>
                </button>
                <div id="public-wings-submenu" 
                     class="public-submenu-box hidden px-2.5 pb-2 pt-1.5 space-y-1 rounded-b-xl border-x border-b border-white/10 text-[10.5px] font-bold mt-1">
                    <a href="{{ route('rudrasena.form') }}" 
                       onclick="togglePublicMobileMenu(false)" 
                       class="block px-3 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-slate-700/70 transition border-b border-gray-800/60">
                        RUDRASENA DAL
                    </a>
                    <a href="{{ route('kalabrundam.form') }}" 
                       onclick="togglePublicMobileMenu(false)" 
                       class="block px-3 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-slate-700/70 transition border-b border-gray-800/60">
                        KALA BRUNDAM
                    </a>
                    <a href="{{ route('gramasevadal.form') }}" 
                       onclick="togglePublicMobileMenu(false)" 
                       class="block px-3 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-slate-700/70 transition border-b border-gray-800/60">
                        GRAMA SEVA DAL
                    </a>
                    <a href="{{ route('organicfarmers.form') }}" 
                       onclick="togglePublicMobileMenu(false)" 
                       class="block px-3 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-slate-700/70 transition">
                        ORGANIC FARMERS
                    </a>
                </div>
            </div>

            <!-- SECTION 4: COMMUNITY & SUPPORT -->
            <div class="pt-3 pb-1.5 border-b border-slate-600/40 text-[9.5px] text-brandOrange font-black tracking-widest uppercase">
                COMMUNITY & SUPPORT
            </div>

            <a href="{{ route('donations.grid') }}" 
               onclick="togglePublicMobileMenu(false)" 
               class="public-nav-row flex items-center px-3.5 py-2.5 rounded-xl min-h-[48px] text-gray-200 shadow-xs {{ request()->routeIs('donations.*') ? 'is-active' : '' }}"
               @if(request()->routeIs('donations.*')) aria-current="page" @endif>
                <span class="truncate">FUNDRAISE CAMPAIGNS</span>
            </a>

            <a href="{{ route('public.blogs') }}" 
               onclick="togglePublicMobileMenu(false)" 
               class="public-nav-row flex items-center px-3.5 py-2.5 rounded-xl min-h-[48px] text-gray-200 shadow-xs {{ request()->routeIs('public.blogs*') ? 'is-active' : '' }}"
               @if(request()->routeIs('public.blogs*')) aria-current="page" @endif>
                <span class="truncate">BLOGS & UPDATES</span>
            </a>

            <a href="{{ route('public.contact') }}" 
               onclick="togglePublicMobileMenu(false)" 
               class="public-nav-row flex items-center px-3.5 py-2.5 rounded-xl min-h-[48px] text-gray-200 shadow-xs {{ request()->routeIs('public.contact*') ? 'is-active' : '' }}"
               @if(request()->routeIs('public.contact*')) aria-current="page" @endif>
                <span class="truncate">CONTACT US</span>
            </a>

            <a href="{{ route('public.policy_center') }}" 
               onclick="togglePublicMobileMenu(false)" 
               class="public-nav-row flex items-center px-3.5 py-2.5 rounded-xl min-h-[48px] text-gray-200 shadow-xs {{ request()->routeIs('public.policy_center*') ? 'is-active' : '' }}"
               @if(request()->routeIs('public.policy_center*')) aria-current="page" @endif>
                <span class="truncate">POLICY CENTER</span>
            </a>

            <!-- CTA: LOGIN & MAKE A DONATION -->
            <div class="pt-2 pb-1 space-y-2">
                @if(!auth()->guard('web')->check() && !auth()->guard('volunteer')->check())
                <button type="button"
                   onclick="togglePublicMobileMenu(false); openLoginModal();"
                   class="w-full bg-[#111c2e] hover:bg-black text-brandOrange border border-brandOrange/50 font-black text-center py-2.5 min-h-[48px] rounded-xl shadow-md transition flex items-center justify-center gap-2 uppercase tracking-wider text-xs cursor-pointer">
                    <svg class="w-4 h-4 text-brandOrange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                    <span>LOGIN PORTALS</span>
                </button>
                @endif
                <a href="{{ route('donations.grid') }}" 
                   onclick="togglePublicMobileMenu(false)" 
                   class="w-full bg-brandOrange hover:bg-orange-600 text-white font-black text-center py-2.5 min-h-[48px] rounded-xl shadow-md transition flex items-center justify-center uppercase tracking-wider text-xs border border-orange-400/50 cursor-pointer">
                    MAKE A DONATION
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
        <div class="max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6 border-b border-gray-700 pb-8">
            <div>
                <h3 class="text-white font-bold text-base mb-3 text-brandOrange uppercase tracking-wide">About ABVHPS</h3>
                <p class="text-xs text-gray-300 leading-relaxed">
                    {{ \App\Models\SiteSetting::get('footer_about', 'Dedicated to preserving and promoting Hindu culture and values worldwide under the behest of Rajaguru Sri Sri Sri Subrahmanneswara Swamy Garu.') }}
                </p>
            </div>
            <div>
                <h3 class="text-white font-bold text-base mb-3 text-brandOrange uppercase tracking-wide">Quick Links</h3>
                <div class="grid grid-cols-1 gap-1.5 text-xs">
                    <a href="/about" class="text-gray-300 hover:text-white transition">About Us</a>
                    <a href="/membership" class="text-gray-300 hover:text-white transition">Membership</a>
                    <a href="/volunteer" class="text-gray-300 hover:text-white transition">Volunteer</a>
                    <a href="{{ route('donations.grid') }}" class="text-gray-300 hover:text-white transition">Donation</a>
                    <a href="{{ route('public.certificates') }}" class="text-gray-300 hover:text-white transition">80G / 12A</a>
                    <a href="{{ route('public.blogs') }}" class="text-gray-300 hover:text-white transition">Blogs & Updates</a>
                </div>
            </div>
            <div>
                <h3 class="text-white font-bold text-base mb-3 text-brandOrange uppercase tracking-wide">Our Wings</h3>
                <div class="space-y-1.5 text-xs">
                    <a href="{{ route('rudrasena.form') }}" class="text-gray-300 hover:text-white block transition">Rudrasena Dal</a>
                    <a href="{{ route('kalabrundam.form') }}" class="text-gray-300 hover:text-white block transition">Kala Brundam</a>
                    <a href="{{ route('gramasevadal.form') }}" class="text-gray-300 hover:text-white block transition">Grama Seva Dal</a>
                    <a href="{{ route('organicfarmers.form') }}" class="hover:text-white block font-bold text-emerald-400 transition">Organic Farmers</a>
                    <a href="{{ route('public.exams_board') }}" class="text-gray-300 hover:text-white block transition">Exams Notice Board</a>
                </div>
            </div>
            <div>
                <h3 class="text-white font-bold text-base mb-3 text-brandOrange uppercase tracking-wide">Support</h3>
                <div class="space-y-2 text-xs">
                    <div>
                        <span class="text-[10px] text-gray-400 font-bold uppercase block tracking-wider">Official Support Email</span>
                        <a href="mailto:{{ \App\Models\SiteSetting::get('contact_email', 'info@abvhps.org') }}" class="text-orange-400 font-semibold hover:underline block break-all">
                            {{ \App\Models\SiteSetting::get('contact_email', 'info@abvhps.org') }}
                        </a>
                    </div>
                    <div>
                        <span class="text-[10px] text-gray-400 font-bold uppercase block tracking-wider">Helpline</span>
                        <a href="tel:{{ \App\Models\SiteSetting::get('contact_phone', '+91 8884933379') }}" class="text-gray-300 hover:text-white block font-mono">
                            {{ \App\Models\SiteSetting::get('contact_phone', '+91 8884933379') }}
                        </a>
                    </div>
                    <div class="pt-1">
                        <a href="{{ route('public.policy_center') }}" class="inline-flex items-center gap-1.5 text-xs font-black uppercase tracking-wide text-brandOrange hover:text-orange-300 bg-gray-800/80 hover:bg-gray-800 px-3 py-1.5 rounded-lg border border-orange-500/30 transition shadow-xs">
                            <span>📜</span>
                            <span>Policy Center</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                    <div>
                        <a href="{{ route('public.contact') }}" class="text-gray-400 hover:text-white block text-xs transition">
                            Grievance Redressal
                        </a>
                    </div>
                </div>
            </div>
            <div>
                <h3 class="text-white font-bold text-base mb-3 text-brandOrange uppercase tracking-wide">Contact Us</h3>
                <p class="text-xs text-gray-300 leading-relaxed mb-2">
                    {{ \App\Models\SiteSetting::get('contact_address', 'Survey No:1826, Shanmukhapuram, Akkalareddy Palli Village and Post, Porumamilla Mandalam, Kadapa, A.P - 516193') }}
                </p>
                <div class="text-xs font-mono text-gray-400 space-y-1">
                    <div>📞 {{ \App\Models\SiteSetting::get('contact_phone', '+91 8884933379') }}</div>
                    <div>✉️ {{ \App\Models\SiteSetting::get('contact_email', 'info@abvhps.org') }}</div>
                </div>
            </div>
        </div>
        <div class="max-w-7xl mx-auto pt-4 flex flex-col sm:flex-row items-center justify-between text-xs text-gray-500 gap-2">
            <div>
                &copy; {{ date('Y') }} ABVHPS. All Rights Reserved.
            </div>
            <div class="flex items-center gap-4 text-xs font-medium">
                <a href="{{ route('public.policy_center') }}" class="text-gray-400 hover:text-white transition">Policy Center</a>
                <span class="text-gray-700">|</span>
                <a href="{{ route('public.terms') }}" class="text-gray-400 hover:text-white transition">Terms</a>
                <span class="text-gray-700">|</span>
                <a href="{{ route('public.privacy') }}" class="text-gray-400 hover:text-white transition">Privacy</a>
                <span class="text-gray-700">|</span>
                <a href="{{ route('public.refund_policy') }}" class="text-gray-400 hover:text-white transition">Refunds</a>
            </div>
        </div>
    </footer>

    <!-- Floating WhatsApp Quick Connect Button -->
    <x-whatsapp-floating-button />

    @if(!auth()->guard('web')->check() && !auth()->guard('volunteer')->check())
    <!-- Login Portals Selection Modal -->
    <div id="login-portal-modal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-black/60 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="login-modal-title">
        <div class="relative w-full max-w-xl bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-200" onclick="event.stopPropagation()">
            <!-- Modal Header -->
            <div class="bg-[#0b1426] text-white px-6 py-5 flex items-center justify-between border-b-2 border-brandOrange">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-white p-0.5 border border-brandOrange flex items-center justify-center overflow-hidden shrink-0">
                        <img src="{{ asset('images/logo_abvhps.png') }}" class="w-full h-full object-contain" alt="ABVHPS">
                    </div>
                    <div>
                        <h2 id="login-modal-title" class="text-base sm:text-lg font-extrabold uppercase tracking-wide text-white">Select Login Portal</h2>
                        <p class="text-[11px] text-orange-200">Akhanda Bharatha Viswa Hindu Parirakshana Samiti</p>
                    </div>
                </div>
                <button type="button" onclick="closeLoginModal()" class="w-9 h-9 rounded-xl bg-white/10 hover:bg-brandOrange text-white flex items-center justify-center transition cursor-pointer focus:outline-none focus:ring-2 focus:ring-brandOrange" aria-label="Close modal">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Portals Grid -->
            <div class="p-6 sm:p-8 bg-gray-50">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <!-- Card 1: Admin Portal -->
                    <div class="bg-white rounded-xl p-6 border-2 border-gray-200 hover:border-brandOrange hover:shadow-lg transition flex flex-col justify-between group">
                        <div>
                            <div class="w-12 h-12 rounded-xl bg-orange-100 text-brandOrange flex items-center justify-center mb-4 group-hover:scale-105 transition">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            </div>
                            <h3 class="text-base font-extrabold text-brandGray group-hover:text-brandOrange transition uppercase tracking-wide mb-1">Admin Login</h3>
                            <p class="text-xs text-gray-600 leading-relaxed mb-6">Authorized ABVHPS Administration access.</p>
                        </div>
                        <a href="{{ route('login') }}" class="w-full inline-flex items-center justify-center gap-2 bg-brandGray hover:bg-black text-white text-xs font-black py-3 px-4 rounded-xl shadow-sm uppercase tracking-wider transition">
                            <span>LOGIN AS ADMIN</span>
                            <span>→</span>
                        </a>
                    </div>

                    <!-- Card 2: Volunteer Portal -->
                    <div class="bg-white rounded-xl p-6 border-2 border-orange-200 bg-gradient-to-b from-white to-orange-50/40 hover:border-brandOrange hover:shadow-lg transition flex flex-col justify-between group">
                        <div>
                            <div class="w-12 h-12 rounded-xl bg-orange-500 text-white flex items-center justify-center mb-4 group-hover:scale-105 transition shadow-sm">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                            <h3 class="text-base font-extrabold text-brandOrange uppercase tracking-wide mb-1">Volunteer Login</h3>
                            <p class="text-xs text-gray-600 leading-relaxed mb-6">Approved ABVHPS Volunteers and Presidents.</p>
                        </div>
                        <a href="{{ route('volunteer.login') }}" class="w-full inline-flex items-center justify-center gap-2 bg-brandOrange hover:bg-orange-600 text-white text-xs font-black py-3 px-4 rounded-xl shadow-md uppercase tracking-wider transition">
                            <span>LOGIN AS VOLUNTEER</span>
                            <span>→</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-3.5 bg-white border-t border-gray-200 text-center">
                <span class="text-[11px] text-gray-500">Need help accessing your portal? Contact <strong class="text-gray-700 font-bold">info@abvhps.org</strong></span>
            </div>
        </div>
    </div>
    @endif

    <script>
        (function() {
            var lastFocusedPublicElem = null;

            @if(!auth()->guard('web')->check() && !auth()->guard('volunteer')->check())
            window.openLoginModal = function() {
                var modal = document.getElementById('login-portal-modal');
                if (modal) {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    document.body.style.overflow = 'hidden';
                }
            };

            window.closeLoginModal = function() {
                var modal = document.getElementById('login-portal-modal');
                if (modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    document.body.style.overflow = '';
                }
            };
            @endif

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
                    var modal = document.getElementById('login-portal-modal');
                    if (modal && !modal.classList.contains('hidden')) {
                        if (typeof window.closeLoginModal === 'function') window.closeLoginModal();
                    }
                }
            });

            document.addEventListener('click', function(e) {
                var modal = document.getElementById('login-portal-modal');
                if (modal && e.target === modal) {
                    if (typeof window.closeLoginModal === 'function') window.closeLoginModal();
                }
            });
        })();
    </script>
</body>
</html>
