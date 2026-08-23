{{-- ABVHPS CENTRAL ADMINISTRATIVE SIDEBAR & MOBILE DRAWER (UNIFIED DESIGN SYSTEM) --}}
@php
    $currentRoute = Route::currentRouteName();
    $isDashboard = request()->routeIs('admin.dashboard');
    $isTeam = request()->routeIs('admin.team.*') || request()->routeIs('admin.our_team.*');
    $isDonations = request()->routeIs('admin.donations.*') || request()->routeIs('admin.donation.*');
    $isBlogs = request()->routeIs('admin.blogs.*') || request()->routeIs('admin.blog.*');
    $isGallery = request()->routeIs('admin.gallery.*');
    $isSupport = request()->routeIs('admin.support.*') || request()->routeIs('admin.our_support.*') || request()->routeIs('admin.our_supports.*');
    $isApprovedMembers = request()->routeIs('admin.membership.ledger') || (request()->routeIs('admin.membership.*') && !request()->routeIs('admin.membership.pending'));
    $isPendingMembers = request()->routeIs('admin.membership.pending');
    $isVolunteers = request()->routeIs('admin.volunteers.*');
    $isVolunteerEvents = request()->routeIs('admin.volunteer_events.*');
    $isRudrasena = request()->routeIs('admin.rudrasena.*');
    $isLocalGateways = request()->routeIs('admin.local_gateways.*');
    $isExams = request()->routeIs('admin.exams.*');
    $isFundraising = request()->routeIs('admin.fundraising.*');
    $isContacts = request()->routeIs('admin.contacts.*');
    $isCertificates = request()->routeIs('admin.certificates.*');
    $isSettings = request()->routeIs('admin.settings.*');
    $isBanner = request()->routeIs('admin.banner.*') || request()->routeIs('admin.banners.*');
@endphp

<style>
    /* Scoped Navigation Scrollbar */
    .admin-nav-scrollbar::-webkit-scrollbar {
        width: 5px;
    }
    .admin-nav-scrollbar::-webkit-scrollbar-track {
        background: #111827;
    }
    .admin-nav-scrollbar::-webkit-scrollbar-thumb {
        background: #f97316;
        border-radius: 4px;
    }
    .admin-nav-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #ea580c;
    }
    .admin-nav-scrollbar {
        scrollbar-width: thin;
        scrollbar-color: #f97316 #111827;
    }

    /* Motion Accessibility */
    @media (prefers-reduced-motion: reduce) {
        #admin-mobile-drawer,
        #admin-drawer-backdrop,
        .admin-nav-row {
            transition: none !important;
            transform: none !important;
        }
    }
</style>

{{-- ========================================================= --}}
{{-- 1. DESKTOP SIDEBAR (>= 768px: Persistent Left Sidebar)    --}}
{{-- ========================================================= --}}
<aside class="hidden md:flex md:w-64 lg:w-72 bg-[#1f2937] text-white flex-col justify-between shadow-2xl shrink-0 select-none border-r-2 border-brandOrange/40 z-30" aria-label="Admin Desktop Navigation">
    <!-- Header Profile Block -->
    <div class="p-4 border-b border-gray-800/90 flex items-center gap-3 bg-[#0b1426] shrink-0">
        <div class="w-11 h-11 rounded-full overflow-hidden border-2 border-brandOrange shadow-md flex items-center justify-center bg-white p-0.5 shrink-0">
            <img src="{{ asset('images/ABVHPS_LOGO.jpg') }}" class="w-full h-full object-cover rounded-full" alt="ABVHPS">
        </div>
        <div class="overflow-hidden">
            <h2 class="text-xs font-black tracking-widest text-brandOrange uppercase truncate">ABVHPS CENTRAL</h2>
            <span class="text-[9px] text-gray-400 font-bold uppercase tracking-wider block truncate">Admin Control Desk</span>
        </div>
    </div>

    <!-- Navigation Scroll Body -->
    <nav class="flex-1 p-3 space-y-1.5 overflow-y-auto admin-nav-scrollbar text-[11px] font-extrabold tracking-wider uppercase text-gray-200">
        
        <!-- DASHBOARD HOME -->
        <a href="{{ route('admin.dashboard') }}" 
           class="admin-nav-row flex items-center gap-2.5 px-3 py-2.5 rounded-xl border transition-all duration-200 min-h-[44px] {{ $isDashboard ? 'bg-brandOrange text-white border-orange-400/50 shadow-md shadow-orange-950/40 font-black' : 'bg-[#374151] border-gray-700/60 hover:bg-[#4b5563] hover:text-white hover:border-gray-500/60 hover:translate-x-1 shadow-xs text-gray-200' }}"
           @if($isDashboard) aria-current="page" @endif>
            <span class="text-sm shrink-0">📊</span> 
            <span class="truncate">DASHBOARD HOME</span>
        </a>

        <!-- CATEGORY: WINGS SUBSYSTEMS -->
        <div class="pt-3 pb-1 border-b border-orange-500/20 text-[9px] text-brandOrange font-black tracking-widest uppercase">
            WINGS SUBSYSTEMS
        </div>

        <a href="{{ route('admin.team.index') }}" 
           class="admin-nav-row flex items-center gap-2.5 px-3 py-2.5 rounded-xl border transition-all duration-200 min-h-[44px] {{ $isTeam ? 'bg-brandOrange text-white border-orange-400/50 shadow-md shadow-orange-950/40 font-black' : 'bg-[#374151] border-gray-700/60 hover:bg-[#4b5563] hover:text-white hover:border-gray-500/60 hover:translate-x-1 shadow-xs text-gray-200' }}"
           @if($isTeam) aria-current="page" @endif>
            <span class="text-sm shrink-0">👥</span> 
            <span class="truncate">OUR TEAM</span>
        </a>

        <a href="{{ route('admin.donations.index') }}" 
           class="admin-nav-row flex items-center gap-2.5 px-3 py-2.5 rounded-xl border transition-all duration-200 min-h-[44px] {{ $isDonations ? 'bg-brandOrange text-white border-orange-400/50 shadow-md shadow-orange-950/40 font-black' : 'bg-[#374151] border-gray-700/60 hover:bg-[#4b5563] hover:text-white hover:border-gray-500/60 hover:translate-x-1 shadow-xs text-gray-200' }}"
           @if($isDonations) aria-current="page" @endif>
            <span class="text-sm shrink-0">💰</span> 
            <span class="truncate">DONATIONS LEDGER</span>
        </a>

        <a href="{{ route('admin.blogs.index') }}" 
           class="admin-nav-row flex items-center gap-2.5 px-3 py-2.5 rounded-xl border transition-all duration-200 min-h-[44px] {{ $isBlogs ? 'bg-brandOrange text-white border-orange-400/50 shadow-md shadow-orange-950/40 font-black' : 'bg-[#374151] border-gray-700/60 hover:bg-[#4b5563] hover:text-white hover:border-gray-500/60 hover:translate-x-1 shadow-xs text-gray-200' }}"
           @if($isBlogs) aria-current="page" @endif>
            <span class="text-sm shrink-0">📰</span> 
            <span class="truncate">BLOGS MANAGER</span>
        </a>

        <a href="{{ route('admin.gallery.index') }}" 
           class="admin-nav-row flex items-center gap-2.5 px-3 py-2.5 rounded-xl border transition-all duration-200 min-h-[44px] {{ $isGallery ? 'bg-brandOrange text-white border-orange-400/50 shadow-md shadow-orange-950/40 font-black' : 'bg-[#374151] border-gray-700/60 hover:bg-[#4b5563] hover:text-white hover:border-gray-500/60 hover:translate-x-1 shadow-xs text-gray-200' }}"
           @if($isGallery) aria-current="page" @endif>
            <span class="text-sm shrink-0">🖼️</span> 
            <span class="truncate">MEDIA GALLERY</span>
        </a>

        <a href="{{ route('admin.support.index') }}" 
           class="admin-nav-row flex items-center gap-2.5 px-3 py-2.5 rounded-xl border transition-all duration-200 min-h-[44px] {{ $isSupport ? 'bg-brandOrange text-white border-orange-400/50 shadow-md shadow-orange-950/40 font-black' : 'bg-[#374151] border-gray-700/60 hover:bg-[#4b5563] hover:text-white hover:border-gray-500/60 hover:translate-x-1 shadow-xs text-gray-200' }}"
           @if($isSupport) aria-current="page" @endif>
            <span class="text-sm shrink-0">🌱</span> 
            <span class="truncate">OUR SUPPORT CORES</span>
        </a>

        <!-- CATEGORY: MEMBERSHIP & CADRES -->
        <div class="pt-3 pb-1 border-b border-orange-500/20 text-[9px] text-brandOrange font-black tracking-widest uppercase">
            MEMBERSHIP & CADRES
        </div>

        <a href="{{ route('admin.membership.ledger') }}" 
           class="admin-nav-row flex items-center gap-2.5 px-3 py-2.5 rounded-xl border transition-all duration-200 min-h-[44px] {{ $isApprovedMembers ? 'bg-brandOrange text-white border-orange-400/50 shadow-md shadow-orange-950/40 font-black' : 'bg-[#374151] border-gray-700/60 hover:bg-[#4b5563] hover:text-white hover:border-gray-500/60 hover:translate-x-1 shadow-xs text-gray-200' }}"
           @if($isApprovedMembers) aria-current="page" @endif>
            <span class="text-sm shrink-0">💳</span> 
            <span class="truncate">APPROVED MEMBERSHIP</span>
        </a>

        <a href="{{ route('admin.membership.pending') }}" 
           class="admin-nav-row flex items-center gap-2.5 px-3 py-2.5 rounded-xl border transition-all duration-200 min-h-[44px] {{ $isPendingMembers ? 'bg-brandOrange text-white border-orange-400/50 shadow-md shadow-orange-950/40 font-black' : 'bg-[#374151] border-gray-700/60 hover:bg-[#4b5563] hover:text-white hover:border-gray-500/60 hover:translate-x-1 shadow-xs text-gray-200' }}"
           @if($isPendingMembers) aria-current="page" @endif>
            <span class="text-sm shrink-0">⏳</span> 
            <span class="truncate">PENDING MEMBERSHIP LIST</span>
        </a>

        <a href="{{ route('admin.volunteers.index') }}" 
           class="admin-nav-row flex items-center gap-2.5 px-3 py-2.5 rounded-xl border transition-all duration-200 min-h-[44px] {{ $isVolunteers ? 'bg-brandOrange text-white border-orange-400/50 shadow-md shadow-orange-950/40 font-black' : 'bg-[#374151] border-gray-700/60 hover:bg-[#4b5563] hover:text-white hover:border-gray-500/60 hover:translate-x-1 shadow-xs text-gray-200' }}"
           @if($isVolunteers) aria-current="page" @endif>
            <span class="text-sm shrink-0">🤝</span> 
            <span class="truncate">VOLUNTEER DESK</span>
        </a>

        <a href="{{ route('admin.volunteer_events.index') }}"
           class="admin-nav-row flex items-center gap-2.5 px-3 py-2.5 rounded-xl border transition-all duration-200 min-h-[44px] {{ $isVolunteerEvents ? 'bg-brandOrange text-white border-orange-400/50 shadow-md shadow-orange-950/40 font-black' : 'bg-[#374151] border-gray-700/60 hover:bg-[#4b5563] hover:text-white hover:border-gray-500/60 hover:translate-x-1 shadow-xs text-gray-200' }}"
           @if($isVolunteerEvents) aria-current="page" @endif>
            <span class="text-sm shrink-0">🏆</span>
            <span class="truncate">VOLUNTEER EVENTS</span>
        </a>

        <a href="{{ route('admin.rudrasena.index') }}" 
           class="admin-nav-row flex items-center gap-2.5 px-3 py-2.5 rounded-xl border transition-all duration-200 min-h-[44px] {{ $isRudrasena ? 'bg-brandOrange text-white border-orange-400/50 shadow-md shadow-orange-950/40 font-black' : 'bg-[#374151] border-gray-700/60 hover:bg-[#4b5563] hover:text-white hover:border-gray-500/60 hover:translate-x-1 shadow-xs text-gray-200' }}"
           @if($isRudrasena) aria-current="page" @endif>
            <span class="text-sm shrink-0">🔱</span> 
            <span class="truncate">RUDRASENA</span>
        </a>

        <a href="{{ route('admin.local_gateways.index') }}" 
           class="admin-nav-row flex items-center gap-2.5 px-3 py-2.5 rounded-xl border transition-all duration-200 min-h-[44px] {{ $isLocalGateways ? 'bg-brandOrange text-white border-orange-400/50 shadow-md shadow-orange-950/40 font-black' : 'bg-[#374151] border-gray-700/60 hover:bg-[#4b5563] hover:text-white hover:border-gray-500/60 hover:translate-x-1 shadow-xs text-gray-200' }}"
           @if($isLocalGateways) aria-current="page" @endif>
            <span class="text-sm shrink-0">🏡</span> 
            <span class="truncate">LOCAL GP GATEWAYS</span>
        </a>

        <!-- CATEGORY: SERVICES & CORES -->
        <div class="pt-3 pb-1 border-b border-orange-500/20 text-[9px] text-brandOrange font-black tracking-widest uppercase">
            SERVICES & CORES
        </div>

        <a href="{{ route('admin.exams.index') }}" 
           class="admin-nav-row flex items-center gap-2.5 px-3 py-2.5 rounded-xl border transition-all duration-200 min-h-[44px] {{ $isExams ? 'bg-brandOrange text-white border-orange-400/50 shadow-md shadow-orange-950/40 font-black' : 'bg-[#374151] border-gray-700/60 hover:bg-[#4b5563] hover:text-white hover:border-gray-500/60 hover:translate-x-1 shadow-xs text-gray-200' }}"
           @if($isExams) aria-current="page" @endif>
            <span class="text-sm shrink-0">📝</span> 
            <span class="truncate">EXAMS INFO BOARD</span>
        </a>

        <a href="{{ route('admin.fundraising.index') }}" 
           class="admin-nav-row flex items-center gap-2.5 px-3 py-2.5 rounded-xl border transition-all duration-200 min-h-[44px] {{ $isFundraising ? 'bg-brandOrange text-white border-orange-400/50 shadow-md shadow-orange-950/40 font-black' : 'bg-[#374151] border-gray-700/60 hover:bg-[#4b5563] hover:text-white hover:border-gray-500/60 hover:translate-x-1 shadow-xs text-gray-200' }}"
           @if($isFundraising) aria-current="page" @endif>
            <span class="text-sm shrink-0">📢</span> 
            <span class="truncate">FUNDRAISING MATRICES</span>
        </a>

        <a href="{{ route('admin.contacts.index') }}" 
           class="admin-nav-row flex items-center gap-2.5 px-3 py-2.5 rounded-xl border transition-all duration-200 min-h-[44px] {{ $isContacts ? 'bg-brandOrange text-white border-orange-400/50 shadow-md shadow-orange-950/40 font-black' : 'bg-[#374151] border-gray-700/60 hover:bg-[#4b5563] hover:text-white hover:border-gray-500/60 hover:translate-x-1 shadow-xs text-gray-200' }}"
           @if($isContacts) aria-current="page" @endif>
            <span class="text-sm shrink-0">📩</span> 
            <span class="truncate">CONTACT FORMS AUDIT</span>
        </a>

        <a href="{{ route('admin.certificates.index') }}" 
           class="admin-nav-row flex items-center gap-2.5 px-3 py-2.5 rounded-xl border transition-all duration-200 min-h-[44px] {{ $isCertificates ? 'bg-brandOrange text-white border-orange-400/50 shadow-md shadow-orange-950/40 font-black' : 'bg-[#374151] border-gray-700/60 hover:bg-[#4b5563] hover:text-white hover:border-gray-500/60 hover:translate-x-1 shadow-xs text-gray-200' }}"
           @if($isCertificates) aria-current="page" @endif>
            <span class="text-sm shrink-0">📜</span> 
            <span class="truncate">TAX CERTIFICATES</span>
        </a>

        <a href="{{ route('admin.settings.index') }}" 
           class="admin-nav-row flex items-center gap-2.5 px-3 py-2.5 rounded-xl border transition-all duration-200 min-h-[44px] {{ $isSettings ? 'bg-brandOrange text-white border-orange-400/50 shadow-md shadow-orange-950/40 font-black' : 'bg-[#374151] border-gray-700/60 hover:bg-[#4b5563] hover:text-white hover:border-gray-500/60 hover:translate-x-1 shadow-xs text-gray-200' }}"
           @if($isSettings) aria-current="page" @endif>
            <span class="text-sm shrink-0">⚙️</span> 
            <span class="truncate">SITE GLOBAL SETTINGS</span>
        </a>

        <a href="{{ route('admin.banner.index') }}" 
           class="admin-nav-row flex items-center gap-2.5 px-3 py-2.5 rounded-xl border transition-all duration-200 min-h-[44px] {{ $isBanner ? 'bg-brandOrange text-white border-orange-400/50 shadow-md shadow-orange-950/40 font-black' : 'bg-[#374151] border-gray-700/60 hover:bg-[#4b5563] hover:text-white hover:border-gray-500/60 hover:translate-x-1 shadow-xs text-gray-200' }}"
           @if($isBanner) aria-current="page" @endif>
            <span class="text-sm shrink-0">🚩</span> 
            <span class="truncate">BANNER MANAGEMENT</span>
        </a>

        <!-- WHATSAPP INTEGRATION -->
        <a href="{{ \App\Models\SiteSetting::getWhatsAppUrl() }}" 
           target="_blank" 
           rel="noopener noreferrer" 
           class="admin-nav-row flex items-center gap-2.5 px-3 py-2.5 rounded-xl border border-emerald-600/30 bg-emerald-950/40 text-emerald-400 hover:bg-emerald-800 hover:text-white hover:translate-x-1 transition-all duration-200 min-h-[44px] font-bold shadow-xs">
            <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.972.531 1.776.813 2.796.813 3.183 0 5.768-2.587 5.769-5.766.001-3.182-2.585-5.77-5.769-5.77zm3.377 8.239c-.144.405-.837.774-1.17.824-.312.045-.694.076-2.155-.529-1.803-.746-2.956-2.58-3.045-2.7-.091-.12-1.222-1.625-1.222-3.099 0-1.474.773-2.197 1.047-2.496.275-.299.598-.374.797-.374.199 0 .399.002.573.01.184.01.432-.07.674.512.25.599.852 2.079.927 2.23.075.15.125.326.025.525-.099.199-.15.324-.298.499-.15.175-.316.39-.45.524-.15.15-.306.314-.132.613.175.299.776 1.28 1.666 2.072 1.144 1.02 2.11 1.335 2.41 1.485.3.15.474.125.65-.075.174-.2.748-.873.948-1.173.199-.3.399-.25.674-.15.275.1 1.748.824 2.048.974.3.15.499.225.574.35.074.125.074.724-.07 1.129zM12 2C6.477 2 2 6.477 2 12c0 1.891.524 3.662 1.436 5.178L2 22l4.958-1.3c1.47.839 3.167 1.3 4.978 1.3 5.523 0 10-4.477 10-10S17.523 2 12 2zm0 18.167c-1.637 0-3.17-.492-4.455-1.336l-.319-.208-2.946.772.786-2.871-.227-.361A8.125 8.125 0 013.833 12c0-4.503 3.664-8.167 8.167-8.167 4.503 0 8.167 3.664 8.167 8.167 0 4.503-3.664 8.167-8.167 8.167z"/></svg>
            <span class="truncate">WHATSAPP ({{ substr(\App\Models\SiteSetting::getNormalizedWhatsAppNumber(), -10) }})</span>
        </a>

        <!-- SIGN OUT ACTION -->
        @if(auth()->guard('web')->check())
        <div class="pt-2 border-t border-gray-800/80">
            <form action="{{ route('admin.logout') }}" method="POST" class="w-full">
                @csrf
                <button type="submit" class="admin-nav-row w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl border border-rose-600/30 bg-rose-950/30 text-rose-300 hover:bg-rose-900 hover:text-white transition-all duration-200 min-h-[44px] font-black tracking-wider cursor-pointer shadow-xs">
                    <span class="text-sm shrink-0">🚪</span> 
                    <span class="truncate">SIGN OUT</span>
                </button>
            </form>
        </div>
        @endif
    </nav>

    <!-- Footer Identity Block -->
    <div class="p-3 bg-[#0b1426] border-t border-gray-800/90 text-center text-[9px] font-black text-gray-400 tracking-wider shrink-0">
        ABVHPS SECURITY CORE V2.0
    </div>
</aside>

{{-- ========================================================= --}}
{{-- 2. MOBILE DRAWER (< 768px: Slide-Over Offcanvas Drawer)   --}}
{{-- ========================================================= --}}
<div id="admin-drawer-backdrop" 
     class="fixed inset-0 bg-black/65 backdrop-blur-md z-[70] hidden opacity-0 transition-opacity duration-300 md:hidden" 
     onclick="toggleAdminDrawer(false)" 
     aria-hidden="true"></div>

<div id="admin-mobile-drawer" 
     class="fixed inset-y-0 left-0 w-[320px] sm:w-[360px] max-w-[85vw] bg-[#1f2937] text-white z-[80] shadow-2xl flex flex-col justify-between transform -translate-x-full transition-transform duration-300 ease-out md:hidden select-none border-r-2 border-brandOrange/40" 
     role="dialog" 
     aria-modal="true" 
     aria-label="Admin Navigation Menu">
     
    <!-- Header Profile Block with Orange Outlined Close Button -->
    <div class="p-4 border-b border-gray-800/90 flex items-center justify-between bg-[#0b1426] shrink-0">
        <div class="flex items-center gap-3 overflow-hidden">
            <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-brandOrange shadow-md flex items-center justify-center bg-white p-0.5 shrink-0">
                <img src="{{ asset('images/ABVHPS_LOGO.jpg') }}" class="w-full h-full object-cover rounded-full" alt="ABVHPS">
            </div>
            <div class="overflow-hidden">
                <h2 class="text-xs font-black tracking-widest text-brandOrange uppercase truncate">ABVHPS CENTRAL</h2>
                <span class="text-[8px] text-gray-400 font-bold uppercase tracking-wider block truncate">Admin Control Desk</span>
            </div>
        </div>

        <!-- Accessible Square Close Action with Orange Outline -->
        <button type="button" 
                id="admin-drawer-close-btn" 
                onclick="toggleAdminDrawer(false)" 
                class="w-10 h-10 rounded-xl bg-[#1f2937] border-2 border-brandOrange text-white hover:bg-brandOrange hover:text-white transition flex items-center justify-center cursor-pointer shadow-md focus:outline-none focus:ring-2 focus:ring-brandOrange shrink-0" 
                aria-label="Close navigation">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <!-- Navigation Scroll Body (Mobile) -->
    <nav class="flex-1 p-3.5 space-y-1.5 overflow-y-auto admin-nav-scrollbar text-[11px] font-extrabold tracking-wider uppercase text-gray-200 min-h-0">
        
        <a href="{{ route('admin.dashboard') }}" 
           onclick="toggleAdminDrawer(false)" 
           class="admin-nav-row flex items-center gap-2.5 px-3 py-2.5 rounded-xl border transition-all duration-200 min-h-[44px] {{ $isDashboard ? 'bg-brandOrange text-white border-orange-400/50 shadow-md shadow-orange-950/40 font-black' : 'bg-[#374151] border-gray-700/60 hover:bg-[#4b5563] hover:text-white hover:border-gray-500/60 shadow-xs text-gray-200' }}"
           @if($isDashboard) aria-current="page" @endif>
            <span class="text-sm shrink-0">📊</span> 
            <span class="truncate">DASHBOARD HOME</span>
        </a>

        <!-- CATEGORY: WINGS SUBSYSTEMS -->
        <div class="pt-3 pb-1 border-b border-orange-500/20 text-[9px] text-brandOrange font-black tracking-widest uppercase">
            WINGS SUBSYSTEMS
        </div>

        <a href="{{ route('admin.team.index') }}" 
           onclick="toggleAdminDrawer(false)" 
           class="admin-nav-row flex items-center gap-2.5 px-3 py-2.5 rounded-xl border transition-all duration-200 min-h-[44px] {{ $isTeam ? 'bg-brandOrange text-white border-orange-400/50 shadow-md shadow-orange-950/40 font-black' : 'bg-[#374151] border-gray-700/60 hover:bg-[#4b5563] hover:text-white hover:border-gray-500/60 shadow-xs text-gray-200' }}"
           @if($isTeam) aria-current="page" @endif>
            <span class="text-sm shrink-0">👥</span> 
            <span class="truncate">OUR TEAM</span>
        </a>

        <a href="{{ route('admin.donations.index') }}" 
           onclick="toggleAdminDrawer(false)" 
           class="admin-nav-row flex items-center gap-2.5 px-3 py-2.5 rounded-xl border transition-all duration-200 min-h-[44px] {{ $isDonations ? 'bg-brandOrange text-white border-orange-400/50 shadow-md shadow-orange-950/40 font-black' : 'bg-[#374151] border-gray-700/60 hover:bg-[#4b5563] hover:text-white hover:border-gray-500/60 shadow-xs text-gray-200' }}"
           @if($isDonations) aria-current="page" @endif>
            <span class="text-sm shrink-0">💰</span> 
            <span class="truncate">DONATIONS LEDGER</span>
        </a>

        <a href="{{ route('admin.blogs.index') }}" 
           onclick="toggleAdminDrawer(false)" 
           class="admin-nav-row flex items-center gap-2.5 px-3 py-2.5 rounded-xl border transition-all duration-200 min-h-[44px] {{ $isBlogs ? 'bg-brandOrange text-white border-orange-400/50 shadow-md shadow-orange-950/40 font-black' : 'bg-[#374151] border-gray-700/60 hover:bg-[#4b5563] hover:text-white hover:border-gray-500/60 shadow-xs text-gray-200' }}"
           @if($isBlogs) aria-current="page" @endif>
            <span class="text-sm shrink-0">📰</span> 
            <span class="truncate">BLOGS MANAGER</span>
        </a>

        <a href="{{ route('admin.gallery.index') }}" 
           onclick="toggleAdminDrawer(false)" 
           class="admin-nav-row flex items-center gap-2.5 px-3 py-2.5 rounded-xl border transition-all duration-200 min-h-[44px] {{ $isGallery ? 'bg-brandOrange text-white border-orange-400/50 shadow-md shadow-orange-950/40 font-black' : 'bg-[#374151] border-gray-700/60 hover:bg-[#4b5563] hover:text-white hover:border-gray-500/60 shadow-xs text-gray-200' }}"
           @if($isGallery) aria-current="page" @endif>
            <span class="text-sm shrink-0">🖼️</span> 
            <span class="truncate">MEDIA GALLERY</span>
        </a>

        <a href="{{ route('admin.support.index') }}" 
           onclick="toggleAdminDrawer(false)" 
           class="admin-nav-row flex items-center gap-2.5 px-3 py-2.5 rounded-xl border transition-all duration-200 min-h-[44px] {{ $isSupport ? 'bg-brandOrange text-white border-orange-400/50 shadow-md shadow-orange-950/40 font-black' : 'bg-[#374151] border-gray-700/60 hover:bg-[#4b5563] hover:text-white hover:border-gray-500/60 shadow-xs text-gray-200' }}"
           @if($isSupport) aria-current="page" @endif>
            <span class="text-sm shrink-0">🌱</span> 
            <span class="truncate">OUR SUPPORT CORES</span>
        </a>

        <!-- CATEGORY: MEMBERSHIP & CADRES -->
        <div class="pt-3 pb-1 border-b border-orange-500/20 text-[9px] text-brandOrange font-black tracking-widest uppercase">
            MEMBERSHIP & CADRES
        </div>

        <a href="{{ route('admin.membership.ledger') }}" 
           onclick="toggleAdminDrawer(false)" 
           class="admin-nav-row flex items-center gap-2.5 px-3 py-2.5 rounded-xl border transition-all duration-200 min-h-[44px] {{ $isApprovedMembers ? 'bg-brandOrange text-white border-orange-400/50 shadow-md shadow-orange-950/40 font-black' : 'bg-[#374151] border-gray-700/60 hover:bg-[#4b5563] hover:text-white hover:border-gray-500/60 shadow-xs text-gray-200' }}"
           @if($isApprovedMembers) aria-current="page" @endif>
            <span class="text-sm shrink-0">💳</span> 
            <span class="truncate">APPROVED MEMBERSHIP</span>
        </a>

        <a href="{{ route('admin.membership.pending') }}" 
           onclick="toggleAdminDrawer(false)" 
           class="admin-nav-row flex items-center gap-2.5 px-3 py-2.5 rounded-xl border transition-all duration-200 min-h-[44px] {{ $isPendingMembers ? 'bg-brandOrange text-white border-orange-400/50 shadow-md shadow-orange-950/40 font-black' : 'bg-[#374151] border-gray-700/60 hover:bg-[#4b5563] hover:text-white hover:border-gray-500/60 shadow-xs text-gray-200' }}"
           @if($isPendingMembers) aria-current="page" @endif>
            <span class="text-sm shrink-0">⏳</span> 
            <span class="truncate">PENDING MEMBERSHIP LIST</span>
        </a>

        <a href="{{ route('admin.volunteers.index') }}" 
           onclick="toggleAdminDrawer(false)" 
           class="admin-nav-row flex items-center gap-2.5 px-3 py-2.5 rounded-xl border transition-all duration-200 min-h-[44px] {{ $isVolunteers ? 'bg-brandOrange text-white border-orange-400/50 shadow-md shadow-orange-950/40 font-black' : 'bg-[#374151] border-gray-700/60 hover:bg-[#4b5563] hover:text-white hover:border-gray-500/60 shadow-xs text-gray-200' }}"
           @if($isVolunteers) aria-current="page" @endif>
            <span class="text-sm shrink-0">🤝</span> 
            <span class="truncate">VOLUNTEER DESK</span>
        </a>

        <a href="{{ route('admin.volunteer_events.index') }}"
           onclick="toggleAdminDrawer(false)"
           class="admin-nav-row flex items-center gap-2.5 px-3 py-2.5 rounded-xl border transition-all duration-200 min-h-[44px] {{ $isVolunteerEvents ? 'bg-brandOrange text-white border-orange-400/50 shadow-md shadow-orange-950/40 font-black' : 'bg-[#374151] border-gray-700/60 hover:bg-[#4b5563] hover:text-white hover:border-gray-500/60 shadow-xs text-gray-200' }}"
           @if($isVolunteerEvents) aria-current="page" @endif>
            <span class="text-sm shrink-0">🏆</span>
            <span class="truncate">VOLUNTEER EVENTS</span>
        </a>

        <a href="{{ route('admin.rudrasena.index') }}" 
           onclick="toggleAdminDrawer(false)" 
           class="admin-nav-row flex items-center gap-2.5 px-3 py-2.5 rounded-xl border transition-all duration-200 min-h-[44px] {{ $isRudrasena ? 'bg-brandOrange text-white border-orange-400/50 shadow-md shadow-orange-950/40 font-black' : 'bg-[#374151] border-gray-700/60 hover:bg-[#4b5563] hover:text-white hover:border-gray-500/60 shadow-xs text-gray-200' }}"
           @if($isRudrasena) aria-current="page" @endif>
            <span class="text-sm shrink-0">🔱</span> 
            <span class="truncate">RUDRASENA</span>
        </a>

        <a href="{{ route('admin.local_gateways.index') }}" 
           onclick="toggleAdminDrawer(false)" 
           class="admin-nav-row flex items-center gap-2.5 px-3 py-2.5 rounded-xl border transition-all duration-200 min-h-[44px] {{ $isLocalGateways ? 'bg-brandOrange text-white border-orange-400/50 shadow-md shadow-orange-950/40 font-black' : 'bg-[#374151] border-gray-700/60 hover:bg-[#4b5563] hover:text-white hover:border-gray-500/60 shadow-xs text-gray-200' }}"
           @if($isLocalGateways) aria-current="page" @endif>
            <span class="text-sm shrink-0">🏡</span> 
            <span class="truncate">LOCAL GP GATEWAYS</span>
        </a>

        <!-- CATEGORY: SERVICES & CORES -->
        <div class="pt-3 pb-1 border-b border-orange-500/20 text-[9px] text-brandOrange font-black tracking-widest uppercase">
            SERVICES & CORES
        </div>

        <a href="{{ route('admin.exams.index') }}" 
           onclick="toggleAdminDrawer(false)" 
           class="admin-nav-row flex items-center gap-2.5 px-3 py-2.5 rounded-xl border transition-all duration-200 min-h-[44px] {{ $isExams ? 'bg-brandOrange text-white border-orange-400/50 shadow-md shadow-orange-950/40 font-black' : 'bg-[#374151] border-gray-700/60 hover:bg-[#4b5563] hover:text-white hover:border-gray-500/60 shadow-xs text-gray-200' }}"
           @if($isExams) aria-current="page" @endif>
            <span class="text-sm shrink-0">📝</span> 
            <span class="truncate">EXAMS INFO BOARD</span>
        </a>

        <a href="{{ route('admin.fundraising.index') }}" 
           onclick="toggleAdminDrawer(false)" 
           class="admin-nav-row flex items-center gap-2.5 px-3 py-2.5 rounded-xl border transition-all duration-200 min-h-[44px] {{ $isFundraising ? 'bg-brandOrange text-white border-orange-400/50 shadow-md shadow-orange-950/40 font-black' : 'bg-[#374151] border-gray-700/60 hover:bg-[#4b5563] hover:text-white hover:border-gray-500/60 shadow-xs text-gray-200' }}"
           @if($isFundraising) aria-current="page" @endif>
            <span class="text-sm shrink-0">📢</span> 
            <span class="truncate">FUNDRAISING MATRICES</span>
        </a>

        <a href="{{ route('admin.contacts.index') }}" 
           onclick="toggleAdminDrawer(false)" 
           class="admin-nav-row flex items-center gap-2.5 px-3 py-2.5 rounded-xl border transition-all duration-200 min-h-[44px] {{ $isContacts ? 'bg-brandOrange text-white border-orange-400/50 shadow-md shadow-orange-950/40 font-black' : 'bg-[#374151] border-gray-700/60 hover:bg-[#4b5563] hover:text-white hover:border-gray-500/60 shadow-xs text-gray-200' }}"
           @if($isContacts) aria-current="page" @endif>
            <span class="text-sm shrink-0">📩</span> 
            <span class="truncate">CONTACT FORMS AUDIT</span>
        </a>

        <a href="{{ route('admin.certificates.index') }}" 
           onclick="toggleAdminDrawer(false)" 
           class="admin-nav-row flex items-center gap-2.5 px-3 py-2.5 rounded-xl border transition-all duration-200 min-h-[44px] {{ $isCertificates ? 'bg-brandOrange text-white border-orange-400/50 shadow-md shadow-orange-950/40 font-black' : 'bg-[#374151] border-gray-700/60 hover:bg-[#4b5563] hover:text-white hover:border-gray-500/60 shadow-xs text-gray-200' }}"
           @if($isCertificates) aria-current="page" @endif>
            <span class="text-sm shrink-0">📜</span> 
            <span class="truncate">TAX CERTIFICATES</span>
        </a>

        <a href="{{ route('admin.settings.index') }}" 
           onclick="toggleAdminDrawer(false)" 
           class="admin-nav-row flex items-center gap-2.5 px-3 py-2.5 rounded-xl border transition-all duration-200 min-h-[44px] {{ $isSettings ? 'bg-brandOrange text-white border-orange-400/50 shadow-md shadow-orange-950/40 font-black' : 'bg-[#374151] border-gray-700/60 hover:bg-[#4b5563] hover:text-white hover:border-gray-500/60 shadow-xs text-gray-200' }}"
           @if($isSettings) aria-current="page" @endif>
            <span class="text-sm shrink-0">⚙️</span> 
            <span class="truncate">SITE GLOBAL SETTINGS</span>
        </a>

        <a href="{{ route('admin.banner.index') }}" 
           onclick="toggleAdminDrawer(false)" 
           class="admin-nav-row flex items-center gap-2.5 px-3 py-2.5 rounded-xl border transition-all duration-200 min-h-[44px] {{ $isBanner ? 'bg-brandOrange text-white border-orange-400/50 shadow-md shadow-orange-950/40 font-black' : 'bg-[#374151] border-gray-700/60 hover:bg-[#4b5563] hover:text-white hover:border-gray-500/60 shadow-xs text-gray-200' }}"
           @if($isBanner) aria-current="page" @endif>
            <span class="text-sm shrink-0">🚩</span> 
            <span class="truncate">BANNER MANAGEMENT</span>
        </a>

        <!-- WHATSAPP INTEGRATION -->
        <a href="{{ \App\Models\SiteSetting::getWhatsAppUrl() }}" 
           target="_blank" 
           rel="noopener noreferrer" 
           class="admin-nav-row flex items-center gap-2.5 px-3 py-2.5 rounded-xl border border-emerald-600/30 bg-emerald-950/40 text-emerald-400 hover:bg-emerald-800 hover:text-white transition-all duration-200 min-h-[44px] font-bold shadow-xs">
            <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.972.531 1.776.813 2.796.813 3.183 0 5.768-2.587 5.769-5.766.001-3.182-2.585-5.77-5.769-5.77zm3.377 8.239c-.144.405-.837.774-1.17.824-.312.045-.694.076-2.155-.529-1.803-.746-2.956-2.58-3.045-2.7-.091-.12-1.222-1.625-1.222-3.099 0-1.474.773-2.197 1.047-2.496.275-.299.598-.374.797-.374.199 0 .399.002.573.01.184.01.432-.07.674.512.25.599.852 2.079.927 2.23.075.15.125.326.025.525-.099.199-.15.324-.298.499-.15.175-.316.39-.45.524-.15.15-.306.314-.132.613.175.299.776 1.28 1.666 2.072 1.144 1.02 2.11 1.335 2.41 1.485.3.15.474.125.65-.075.174-.2.748-.873.948-1.173.199-.3.399-.25.674-.15.275.1 1.748.824 2.048.974.3.15.499.225.574.35.074.125.074.724-.07 1.129zM12 2C6.477 2 2 6.477 2 12c0 1.891.524 3.662 1.436 5.178L2 22l4.958-1.3c1.47.839 3.167 1.3 4.978 1.3 5.523 0 10-4.477 10-10S17.523 2 12 2zm0 18.167c-1.637 0-3.17-.492-4.455-1.336l-.319-.208-2.946.772.786-2.871-.227-.361A8.125 8.125 0 013.833 12c0-4.503 3.664-8.167 8.167-8.167 4.503 0 8.167 3.664 8.167 8.167 0 4.503-3.664 8.167-8.167 8.167z"/></svg>
            <span class="truncate">WHATSAPP ({{ substr(\App\Models\SiteSetting::getNormalizedWhatsAppNumber(), -10) }})</span>
        </a>

        <!-- SIGN OUT ACTION -->
        @if(auth()->guard('web')->check())
        <div class="pt-2 border-t border-gray-800/80">
            <form action="{{ route('admin.logout') }}" method="POST" class="w-full">
                @csrf
                <button type="submit" class="admin-nav-row w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl border border-rose-600/30 bg-rose-950/30 text-rose-300 hover:bg-rose-900 hover:text-white transition-all duration-200 min-h-[44px] font-black tracking-wider cursor-pointer shadow-xs">
                    <span class="text-sm shrink-0">🚪</span> 
                    <span class="truncate">SIGN OUT</span>
                </button>
            </form>
        </div>
        @endif
    </nav>

    <!-- Footer Identity Block -->
    <div class="p-3 bg-[#0b1426] border-t border-gray-800/90 text-center text-[9px] font-black text-gray-400 tracking-wider shrink-0">
        ABVHPS SECURITY CORE V2.0
    </div>
</div>

<script>
    (function() {
        var lastFocusedAdminElem = null;

        window.toggleAdminDrawer = function(forceState) {
            var drawer = document.getElementById('admin-mobile-drawer');
            var backdrop = document.getElementById('admin-drawer-backdrop');
            var closeBtn = document.getElementById('admin-drawer-close-btn');
            var hamburgerBtns = document.querySelectorAll('[data-admin-drawer-toggle]');

            if (!drawer || !backdrop) return;

            var isOpen = !drawer.classList.contains('-translate-x-full');
            var shouldOpen = typeof forceState === 'boolean' ? forceState : !isOpen;

            if (shouldOpen) {
                lastFocusedAdminElem = document.activeElement;
                backdrop.classList.remove('hidden');
                setTimeout(function() {
                    backdrop.classList.remove('opacity-0');
                    backdrop.classList.add('opacity-100');
                    drawer.classList.remove('-translate-x-full');
                    drawer.classList.add('translate-x-0');
                }, 10);

                document.body.style.overflow = 'hidden';
                hamburgerBtns.forEach(function(btn) { btn.setAttribute('aria-expanded', 'true'); });
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

                hamburgerBtns.forEach(function(btn) { btn.setAttribute('aria-expanded', 'false'); });
                if (lastFocusedAdminElem && typeof lastFocusedAdminElem.focus === 'function') {
                    lastFocusedAdminElem.focus();
                }
            }
        };

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' || e.keyCode === 27) {
                var drawer = document.getElementById('admin-mobile-drawer');
                if (drawer && !drawer.classList.contains('-translate-x-full')) {
                    window.toggleAdminDrawer(false);
                }
            }
        });
    })();
</script>
