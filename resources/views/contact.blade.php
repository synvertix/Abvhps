@extends('layouts.app')

@section('title', 'Contact Us | ABVHPS')
@section('meta_description', 'Get in touch with the Akhanda Bharatha Viswa Hindu Parirakshana Samiti central office for memberships, donations, or seva inquiries.')

@section('content')
<div class="bg-gray-50 min-h-screen py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-12">

    @php
        $contactBanner = \App\Models\Banner::getBannerForPage('contact');
    @endphp

    @if($contactBanner && !empty($contactBanner->desktop_banner))
        <x-page-banner 
            page="contact" 
            default-title="Get In Touch With ABVHPS" 
            default-subtitle="Have questions regarding Sanathana Dharma Seva, membership, volunteer enrollment, or general inquiries? Our central coordination desk is here to assist."
            badge="Direct Communication Portal"
            min-height="280px"
        />
    @else
        <!-- Page Header -->
        <div class="text-center space-y-3">
            <span class="bg-orange-100 text-brandOrange text-xs font-black px-3.5 py-1 rounded-full uppercase tracking-widest border border-orange-200">
                Direct Communication Portal
            </span>
            <h1 class="text-3xl md:text-5xl font-black text-gray-900 uppercase tracking-tight">
                Get In Touch With <span class="text-brandOrange">ABVHPS</span>
            </h1>
            <p class="text-sm md:text-base text-gray-600 max-w-2xl mx-auto">
                Have questions regarding Sanathana Dharma Seva, membership, volunteer enrollment, or general inquiries? Our central coordination desk is here to assist.
            </p>
        </div>
    @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <!-- Left Info Panel (5 Columns) -->
            <div class="lg:col-span-5 bg-brandDarkGray text-white rounded-2xl p-5 sm:p-8 space-y-6 shadow-xl relative overflow-hidden">
                <div class="space-y-2">
                    <h3 class="text-xl font-black uppercase text-brandOrange">Central Headquarters</h3>
                    <p class="text-xs text-gray-400 leading-relaxed">
                        Akhanda Bharatha Viswa Hindu Parirakshana Samiti central administrative node desk.
                    </p>
                </div>

                <div class="space-y-4 text-xs font-medium">
                    <div class="flex items-start gap-3 bg-gray-800/80 p-4 rounded-xl border border-gray-700">
                        <span class="text-xl text-brandOrange">📍</span>
                        <div>
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Physical Address</span>
                            <span class="text-gray-200 leading-relaxed block mt-0.5">{{ $contactAddress }}</span>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 bg-gray-800/80 p-4 rounded-xl border border-gray-700">
                        <span class="text-xl text-brandOrange">📞</span>
                        <div>
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Central Helpline</span>
                            <a href="tel:{{ $contactPhone }}" class="text-gray-200 font-mono font-bold hover:text-brandOrange transition block mt-0.5">{{ $contactPhone }}</a>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 bg-gray-800/80 p-4 rounded-xl border border-gray-700">
                        <span class="text-xl text-brandOrange">✉️</span>
                        <div>
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Official Email</span>
                            <a href="mailto:{{ $contactEmail }}" class="text-gray-200 font-mono font-bold hover:text-brandOrange transition block mt-0.5">{{ $contactEmail }}</a>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-800">
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block mb-2">Connect Across Social Channels:</span>
                    <div class="flex gap-3 text-xs font-bold text-gray-300">
                        <a href="#" class="hover:text-brandOrange transition">Facebook</a> •
                        <a href="#" class="hover:text-brandOrange transition">Twitter</a> •
                        <a href="#" class="hover:text-brandOrange transition">YouTube</a>
                    </div>
                </div>
            </div>

            <!-- Right Contact Form (7 Columns) -->
            <div class="lg:col-span-7 bg-white rounded-2xl p-5 sm:p-8 border border-gray-200 shadow-md">
                <div class="mb-6">
                    <h3 class="text-xl font-black text-gray-900 uppercase">Send Us A Message</h3>
                    <p class="text-xs text-gray-500 mt-1">Please fill out your details below and our team will get back to you.</p>
                </div>

                <div id="contact-alert" class="hidden mb-6 p-4 rounded-xl text-xs font-bold"></div>

                <form id="contact-form" class="space-y-4 text-xs">
                    @csrf

                    <!-- Honeypot Anti-Spam Trap (Hidden from real users) -->
                    <div style="display:none !important; visibility:hidden !important; position:absolute !important; left:-9999px;">
                        <input type="text" name="website_trap_honeypot" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-black text-gray-700 uppercase mb-1">Your Full Name *</label>
                            <input type="text" name="name" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 font-bold text-gray-800 focus:ring-2 focus:ring-brandOrange outline-none" placeholder="e.g. Sri Rama Sharma">
                        </div>
                        <div>
                            <label class="block font-black text-gray-700 uppercase mb-1">Your Email Address *</label>
                            <input type="email" name="email" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 font-bold text-gray-800 focus:ring-2 focus:ring-brandOrange outline-none" placeholder="e.g. devotee@example.com">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-black text-gray-700 uppercase mb-1">Contact Phone Number</label>
                            <input type="tel" name="phone" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 font-bold text-gray-800 focus:ring-2 focus:ring-brandOrange outline-none" placeholder="e.g. 9876543210">
                        </div>
                        <div>
                            <label class="block font-black text-gray-700 uppercase mb-1">Inquiry Subject</label>
                            <input type="text" name="subject" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 font-bold text-gray-800 focus:ring-2 focus:ring-brandOrange outline-none" placeholder="e.g. Seva Volunteering Inquiry">
                        </div>
                    </div>

                    <div>
                        <label class="block font-black text-gray-700 uppercase mb-1">Your Message / Query *</label>
                        <textarea name="message" rows="5" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 font-semibold text-gray-800 focus:ring-2 focus:ring-brandOrange outline-none" placeholder="Please write your detailed query here (Note: external links and web addresses are filtered for security)..."></textarea>
                    </div>

                    @include('partials.legal-consent', ['type' => 'contact'])
                    <div class="pt-2">
                        <button type="submit" id="contact-submit-btn" class="w-full bg-brandOrange hover:bg-orange-700 text-white font-black text-xs py-3.5 rounded-xl shadow-md uppercase tracking-wider transition flex items-center justify-center gap-2">
                            <span>✉️</span> <span>Submit Inquiry</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
document.getElementById('contact-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('contact-submit-btn');
    const alertBox = document.getElementById('contact-alert');
    
    btn.disabled = true;
    btn.innerHTML = '<span>⏳ Sending...</span>';
    alertBox.className = 'hidden';

    try {
        const formData = new FormData(this);
        const res = await fetch("{{ route('public.contact.submit') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const data = await res.json();

        if (res.ok && data.success) {
            alertBox.className = 'block mb-6 p-4 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200';
            alertBox.innerHTML = '✓ ' + data.message;
            this.reset();
        } else {
            alertBox.className = 'block mb-6 p-4 rounded-xl text-xs font-bold bg-rose-50 text-rose-800 border border-rose-200';
            alertBox.innerHTML = '⚠️ ' + (data.message || 'Please check your inputs and try again.');
        }
    } catch (err) {
        alertBox.className = 'block mb-6 p-4 rounded-xl text-xs font-bold bg-rose-50 text-rose-800 border border-rose-200';
        alertBox.innerHTML = '⚠️ An unexpected network error occurred. Please try again.';
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<span>✉️</span> <span>Submit Inquiry</span>';
    }
});
</script>
@endsection
