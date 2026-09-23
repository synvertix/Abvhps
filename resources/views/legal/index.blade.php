@extends('layouts.app')

@section('title', 'Legal & Policies | ABVHPS')
@section('meta_description', 'Privacy Policy, Terms & Conditions, Refund & Cancellation Policy, Donation & Payment Policy and Account & Data Deletion of ABVHPS.')

@php
    $cards = [
        ['legal.privacy', 'Privacy Policy', 'What personal data we collect for membership, volunteering, wings, exams and donations, why, who sees it, how long we keep it, and your rights.', '🔒'],
        ['legal.terms', 'Terms & Conditions', 'Rules for using the website and app, joining as a member or volunteer, applying to wings, taking exams and paying.', '📜'],
        ['legal.refund', 'Refund & Cancellation Policy', 'When donations, membership fees and exam fees can be refunded, and what to do if a payment fails or is charged twice.', '↩️'],
        ['legal.donation_payments', 'Donation & Payment Policy', 'How donations are used, 80G receipts, Razorpay and Cashfree payments, security and fraud safety.', '🙏'],
        ['legal.account_deletion', 'Account & Data Deletion', 'Delete your account and data, or ask to see, correct or stop using it. Also used by the mobile app.', '🗑️'],
    ];
@endphp

@section('content')
<div class="bg-gradient-to-b from-[#FFF4DC] to-[#FFFBF3] border-b border-amber-200/70">
    <div class="max-w-6xl mx-auto px-4 py-10 sm:py-14 text-center">
        <span class="text-[10px] sm:text-[11px] font-black text-[#B8860B] uppercase tracking-[0.3em]">ABVHPS &middot; Legal Centre</span>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-brandGray mt-2">Policies that protect you and our seva</h1>
        <p class="text-sm text-gray-600 max-w-2xl mx-auto mt-3">Clear, honest terms for everyone who joins, volunteers, studies, donates or uses our website and app. Current version {{ $legal['version'] }}, effective {{ $legal['effective'] }}.</p>
    </div>
</div>

<div class="bg-[#FFFBF3] py-10 px-4">
    <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-5">
        @foreach($cards as [$routeName, $title, $text, $icon])
            <a href="{{ route($routeName) }}" class="group rounded-2xl border border-amber-200/70 bg-white p-6 shadow-sm hover:-translate-y-1 hover:shadow-xl hover:shadow-amber-900/10 transition duration-300">
                <span class="text-2xl" aria-hidden="true">{{ $icon }}</span>
                <h2 class="mt-2 text-lg font-extrabold text-brandGray group-hover:text-brandOrange transition">{{ $title }}</h2>
                <p class="mt-1.5 text-sm text-gray-600 leading-relaxed">{{ $text }}</p>
                <span class="mt-3 inline-block text-xs font-black uppercase tracking-wider text-brandOrange">Read &rarr;</span>
            </a>
        @endforeach
    </div>
    <p class="max-w-3xl mx-auto mt-8 text-center text-xs text-gray-500">Questions? Write to <a class="font-bold text-brandOrange" href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a> or call {{ $contact['phone'] }}. English is the governing version of these documents.</p>
</div>
@endsection
