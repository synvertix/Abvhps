@extends('layouts.app')

@section('title', 'Policy Center | Terms, Privacy & Trust Governance | ABVHPS')
@section('meta_description', 'Official Policy Center of Akhanda Bharatha Viswa Hindu Parirakshana Samiti (ABVHPS) covering Terms and Conditions, Privacy Policy, Refund Policy, 80G Tax Exemption, and Grievance Redressal.')

@section('content')
<div class="bg-gray-50 min-h-screen py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto space-y-10">

        <!-- Page Header Banner -->
        <div class="text-center space-y-3 pt-4">
            <span class="inline-flex items-center gap-1.5 bg-orange-100 text-brandOrange text-xs font-black px-3.5 py-1 rounded-full uppercase tracking-widest border border-orange-200 shadow-xs">
                <span>⚖️</span>
                <span>Trust Governance & Legal Compliance</span>
            </span>
            <h1 class="text-3xl md:text-5xl font-black text-gray-900 uppercase tracking-tight">
                ABVHPS <span class="text-brandOrange">Policy Center</span>
            </h1>
            <p class="text-sm md:text-base text-gray-600 max-w-2xl mx-auto leading-relaxed">
                Transparency, ethical accountability, and devotional trust are the cornerstones of Akhanda Bharatha Viswa Hindu Parirakshana Samiti. Review our official institutional policies below.
            </p>
        </div>

        <!-- Quick Jump Navigation Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
            <a href="#terms-and-conditions" class="p-3 bg-white rounded-xl border border-gray-200 shadow-xs hover:border-brandOrange hover:bg-orange-50/50 transition text-center group">
                <div class="text-2xl mb-1 group-hover:scale-110 transition-transform">📜</div>
                <div class="text-xs font-black text-gray-800 uppercase tracking-tight group-hover:text-brandOrange">Terms of Use</div>
            </a>
            <a href="#privacy-policy" class="p-3 bg-white rounded-xl border border-gray-200 shadow-xs hover:border-brandOrange hover:bg-orange-50/50 transition text-center group">
                <div class="text-2xl mb-1 group-hover:scale-110 transition-transform">🔒</div>
                <div class="text-xs font-black text-gray-800 uppercase tracking-tight group-hover:text-brandOrange">Privacy Policy</div>
            </a>
            <a href="#refund-policy" class="p-3 bg-white rounded-xl border border-gray-200 shadow-xs hover:border-brandOrange hover:bg-orange-50/50 transition text-center group">
                <div class="text-2xl mb-1 group-hover:scale-110 transition-transform">💳</div>
                <div class="text-xs font-black text-gray-800 uppercase tracking-tight group-hover:text-brandOrange">Refund Policy</div>
            </a>
            <a href="#tax-exemption" class="p-3 bg-white rounded-xl border border-gray-200 shadow-xs hover:border-brandOrange hover:bg-orange-50/50 transition text-center group">
                <div class="text-2xl mb-1 group-hover:scale-110 transition-transform">🏛️</div>
                <div class="text-xs font-black text-gray-800 uppercase tracking-tight group-hover:text-brandOrange">80G & 12A Status</div>
            </a>
            <a href="#grievance-redressal" class="p-3 bg-white rounded-xl border border-gray-200 shadow-xs hover:border-brandOrange hover:bg-orange-50/50 transition text-center group col-span-2 sm:col-span-1">
                <div class="text-2xl mb-1 group-hover:scale-110 transition-transform">🤝</div>
                <div class="text-xs font-black text-gray-800 uppercase tracking-tight group-hover:text-brandOrange">Grievance Desk</div>
            </a>
        </div>

        <!-- Section 1: Terms and Conditions -->
        <div id="terms-and-conditions" class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-8 shadow-sm space-y-4">
            <div class="flex items-center gap-3 border-b border-gray-100 pb-4">
                <span class="w-10 h-10 rounded-xl bg-orange-100 text-brandOrange flex items-center justify-center text-lg font-black shrink-0">📜</span>
                <div>
                    <h2 class="text-lg sm:text-xl font-black text-gray-900 uppercase tracking-tight">Terms and Conditions of Service</h2>
                    <p class="text-xs text-gray-500 font-medium">Effective Date: January 1, 2024 | Version 2.0</p>
                </div>
            </div>
            <div class="space-y-3 text-xs sm:text-sm text-gray-700 leading-relaxed font-normal">
                <p>
                    Welcome to the official portal of <strong>Akhanda Bharatha Viswa Hindu Parirakshana Samiti (ABVHPS)</strong>. By accessing, browsing, or utilizing this website, users, donors, members, and volunteers agree to be bound by these Terms of Service.
                </p>
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 space-y-2">
                    <h4 class="font-bold text-gray-900 uppercase text-xs">1. Organization Nature & Charitable Objects</h4>
                    <p class="text-xs text-gray-600">
                        ABVHPS is a legally constituted public religious and charitable trust registered in Andhra Pradesh under the Indian Trusts Act, dedicated to Gau Samrakshana, Vedic preservation, temple revival, free Annadanam, and rural social welfare.
                    </p>
                </div>
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 space-y-2">
                    <h4 class="font-bold text-gray-900 uppercase text-xs">2. Voluntary Donations & Purpose Allocation</h4>
                    <p class="text-xs text-gray-600">
                        All financial contributions made through this website are voluntary donations toward the chosen seva project or the general community fund. Donors receive an automated official digital receipt upon successful gateway settlement.
                    </p>
                </div>
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 space-y-2">
                    <h4 class="font-bold text-gray-900 uppercase text-xs">3. Use of Digital Services</h4>
                    <p class="text-xs text-gray-600">
                        Users agree not to misuse any portal services, falsify identity during membership or exam applications, or attempt unauthorized interference with gateway sessions or backend databases.
                    </p>
                </div>
            </div>
        </div>

        <!-- Section 2: Privacy Policy -->
        <div id="privacy-policy" class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-8 shadow-sm space-y-4">
            <div class="flex items-center gap-3 border-b border-gray-100 pb-4">
                <span class="w-10 h-10 rounded-xl bg-orange-100 text-brandOrange flex items-center justify-center text-lg font-black shrink-0">🔒</span>
                <div>
                    <h2 class="text-lg sm:text-xl font-black text-gray-900 uppercase tracking-tight">Privacy & Data Protection Policy</h2>
                    <p class="text-xs text-gray-500 font-medium">Compliance with Digital Personal Data Protection (DPDP) Standards</p>
                </div>
            </div>
            <div class="space-y-3 text-xs sm:text-sm text-gray-700 leading-relaxed font-normal">
                <p>
                    ABVHPS respects your privacy and is committed to protecting all donor, applicant, and volunteer personal information.
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 space-y-1.5">
                        <h4 class="font-bold text-gray-900 uppercase text-xs">Data Collection Scope</h4>
                        <p class="text-xs text-gray-600">
                            We collect donor contact details (name, email, phone number) and PAN (exclusively for generating statutory 80G tax exemption certificates) as required by the Income Tax Department of India.
                        </p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 space-y-1.5">
                        <h4 class="font-bold text-gray-900 uppercase text-xs">No Third-Party Sharing</h4>
                        <p class="text-xs text-gray-600">
                            ABVHPS does not sell, trade, rent, or lease personal information to any third parties or marketing companies. Data is used exclusively for receipts, identity credentials, and seva updates.
                        </p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 space-y-1.5">
                        <h4 class="font-bold text-gray-900 uppercase text-xs">Payment Information Security</h4>
                        <p class="text-xs text-gray-600">
                            Financial credentials (credit/debit card numbers, UPI PINs, CVVs, net banking credentials) are processed directly by RBI-authorized payment gateways (Razorpay & Cashfree) over 256-bit SSL encryption. ABVHPS never stores sensitive banking credentials.
                        </p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 space-y-1.5">
                        <h4 class="font-bold text-gray-900 uppercase text-xs">Data Retention & Rights</h4>
                        <p class="text-xs text-gray-600">
                            Donors and members may request corrections or review of their stored contact records at any time by contacting the grievance desk.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Refund & Cancellation Policy -->
        <div id="refund-policy" class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-8 shadow-sm space-y-4">
            <div class="flex items-center gap-3 border-b border-gray-100 pb-4">
                <span class="w-10 h-10 rounded-xl bg-orange-100 text-brandOrange flex items-center justify-center text-lg font-black shrink-0">💳</span>
                <div>
                    <h2 class="text-lg sm:text-xl font-black text-gray-900 uppercase tracking-tight">Refund & Cancellation Policy</h2>
                    <p class="text-xs text-gray-500 font-medium">For Contributions, Registrations, and Donations</p>
                </div>
            </div>
            <div class="space-y-3 text-xs sm:text-sm text-gray-700 leading-relaxed font-normal">
                <p>
                    Because contributions made to ABVHPS are charitable donations directly committed to ongoing religious, educational, and disaster relief operations, refunds are subject to specific guidelines:
                </p>
                <div class="space-y-2 bg-gray-50 p-4 rounded-xl border border-gray-200">
                    <div class="flex items-start gap-2">
                        <span class="text-brandOrange font-bold">•</span>
                        <p class="text-xs text-gray-700"><strong>Accidental or Duplicate Debits:</strong> In case of technical errors resulting in multiple deductions for the same donation or payment transaction, the donor should notify ABVHPS within <strong>7 working days</strong> with transaction reference IDs.</p>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-brandOrange font-bold">•</span>
                        <p class="text-xs text-gray-700"><strong>Verification & Reversal:</strong> Upon verification by our accounts desk and payment gateway reconciliations, genuine duplicate debits will be reversed back to the original source bank/card within <strong>5 to 10 banking working days</strong>.</p>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-brandOrange font-bold">•</span>
                        <p class="text-xs text-gray-700"><strong>Issued 80G Certificates:</strong> Once an official Form 10BE or 80G tax exemption receipt has been filed with the Income Tax Department, donation amounts cannot be refunded under statutory tax regulations.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 4: 80G & 12A Tax Exemption & Statutory Compliance -->
        <div id="tax-exemption" class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-8 shadow-sm space-y-4">
            <div class="flex items-center gap-3 border-b border-gray-100 pb-4">
                <span class="w-10 h-10 rounded-xl bg-orange-100 text-brandOrange flex items-center justify-center text-lg font-black shrink-0">🏛️</span>
                <div>
                    <h2 class="text-lg sm:text-xl font-black text-gray-900 uppercase tracking-tight">Section 80G Tax Exemption & Legal Recognition</h2>
                    <p class="text-xs text-gray-500 font-medium">Income Tax Act of India, 1961</p>
                </div>
            </div>
            <div class="space-y-4 text-xs sm:text-sm text-gray-700 leading-relaxed font-normal">
                <p>
                    Donations to Akhanda Bharatha Viswa Hindu Parirakshana Samiti are eligible for tax exemption benefits under Section 80G of the Income Tax Act, 1961.
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="p-4 bg-orange-50/60 rounded-xl border border-orange-200 space-y-1">
                        <span class="text-[10px] font-black uppercase text-brandOrange tracking-wider">Section 12A</span>
                        <h4 class="font-black text-gray-900 text-sm">Charitable Trust Exemption</h4>
                        <p class="text-xs text-gray-600">Registered as an eligible non-profit charitable and religious entity.</p>
                    </div>
                    <div class="p-4 bg-orange-50/60 rounded-xl border border-orange-200 space-y-1">
                        <span class="text-[10px] font-black uppercase text-brandOrange tracking-wider">Section 80G</span>
                        <h4 class="font-black text-gray-900 text-sm">50% Tax Deduction</h4>
                        <p class="text-xs text-gray-600">Donors with valid PAN are eligible for 50% deduction on taxable income.</p>
                    </div>
                    <div class="p-4 bg-orange-50/60 rounded-xl border border-orange-200 space-y-1">
                        <span class="text-[10px] font-black uppercase text-brandOrange tracking-wider">Digital Certificates</span>
                        <h4 class="font-black text-gray-900 text-sm">Official Orders Available</h4>
                        <p class="text-xs text-gray-600">View copies of government sanction orders on our compliance page.</p>
                    </div>
                </div>
                <div class="pt-2">
                    <a href="{{ route('public.certificates') }}" class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-wider text-brandOrange hover:text-orange-700 bg-orange-50 px-4 py-2.5 rounded-xl border border-orange-200 transition">
                        <span>📜</span>
                        <span>View Statutory 80G & 12A Certificates</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Section 5: Grievance Redressal & Support -->
        <div id="grievance-redressal" class="bg-brandDarkGray text-white rounded-2xl p-6 sm:p-8 shadow-xl space-y-6">
            <div class="flex items-center gap-3 border-b border-gray-700 pb-4">
                <span class="w-10 h-10 rounded-xl bg-orange-500/20 text-brandOrange flex items-center justify-center text-lg font-black shrink-0 border border-orange-500/30">🤝</span>
                <div>
                    <h2 class="text-lg sm:text-xl font-black text-white uppercase tracking-tight">Grievance Redressal & Support Desk</h2>
                    <p class="text-xs text-gray-400 font-medium">Direct resolution channel for all donor, volunteer, and public queries</p>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs font-medium">
                <div class="bg-gray-800/80 p-4 rounded-xl border border-gray-700 space-y-1">
                    <span class="text-gray-400 text-[10px] font-black uppercase tracking-wider block">Central Support Email</span>
                    <a href="mailto:{{ \App\Models\SiteSetting::get('contact_email', 'info@abvhps.org') }}" class="text-orange-400 font-bold hover:underline block text-sm">
                        {{ \App\Models\SiteSetting::get('contact_email', 'info@abvhps.org') }}
                    </a>
                    <span class="text-[10px] text-gray-400 block pt-1">Response time: within 24 to 48 hours</span>
                </div>
                <div class="bg-gray-800/80 p-4 rounded-xl border border-gray-700 space-y-1">
                    <span class="text-gray-400 text-[10px] font-black uppercase tracking-wider block">Helpline / WhatsApp</span>
                    <a href="tel:{{ \App\Models\SiteSetting::get('contact_phone', '+91 8884933379') }}" class="text-orange-400 font-mono font-bold hover:underline block text-sm">
                        {{ \App\Models\SiteSetting::get('contact_phone', '+91 8884933379') }}
                    </a>
                    <span class="text-[10px] text-gray-400 block pt-1">Monday – Saturday (9:00 AM – 6:00 PM IST)</span>
                </div>
                <div class="bg-gray-800/80 p-4 rounded-xl border border-gray-700 space-y-1">
                    <span class="text-gray-400 text-[10px] font-black uppercase tracking-wider block">Office Location</span>
                    <span class="text-gray-200 leading-relaxed block text-xs">
                        {{ \App\Models\SiteSetting::get('contact_address', 'Survey No:1826, Shanmukhapuram, Akkalareddy Palli, Porumamilla, Kadapa, AP - 516193') }}
                    </span>
                </div>
            </div>
            <div class="pt-2 flex flex-wrap items-center gap-3">
                <a href="{{ route('public.contact') }}" class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-wider text-white bg-brandOrange hover:bg-orange-600 px-5 py-3 rounded-xl transition shadow-md">
                    <span>Contact Grievance Officer</span>
                    <span>&rarr;</span>
                </a>
                <a href="{{ route('donations.grid') }}" class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-wider text-gray-300 hover:text-white bg-gray-800 px-5 py-3 rounded-xl border border-gray-700 transition">
                    <span>Back to Fundraise Seva</span>
                </a>
            </div>
        </div>

    </div>
</div>
@endsection
