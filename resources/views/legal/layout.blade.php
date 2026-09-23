@extends('layouts.app')

@section('title', $docTitle . ' | ABVHPS')
@section('meta_description', $docDescription)

@php
    $documents = [
        'legal.privacy'           => ['Privacy Policy', 'How we collect, use, share and protect your personal data'],
        'legal.terms'             => ['Terms & Conditions', 'Rules for using the website, app, memberships, volunteering and exams'],
        'legal.refund'            => ['Refund & Cancellation Policy', 'Donations, membership fees, exam fees and failed payments'],
        'legal.donation_payments' => ['Donation & Payment Policy', 'Donations, 80G receipts and Razorpay / Cashfree payments'],
        'legal.account_deletion'  => ['Account & Data Deletion', 'Delete your data or exercise your privacy rights'],
    ];
@endphp

@section('content')
<div class="bg-gradient-to-b from-[#FFF4DC] to-[#FFFBF3] border-b border-amber-200/70">
    <div class="max-w-6xl mx-auto px-4 py-10 sm:py-14 text-center">
        <span class="text-[10px] sm:text-[11px] font-black text-[#B8860B] uppercase tracking-[0.3em]">ABVHPS &middot; Legal</span>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-brandGray mt-2">{{ $docTitle }}</h1>
        <p class="text-sm text-gray-600 max-w-2xl mx-auto mt-3">{{ $docDescription }}</p>
        @unless(!empty($hideMeta))
            <p class="mt-4 inline-flex flex-wrap items-center justify-center gap-x-3 gap-y-1 text-[11px] font-bold uppercase tracking-wider text-gray-500">
                <span>Version {{ $legal['version'] }}</span><span aria-hidden="true">&middot;</span>
                <span>Effective {{ $legal['effective'] }}</span><span aria-hidden="true">&middot;</span>
                <span>Last updated {{ $legal['updated'] }}</span>
            </p>
        @endunless
    </div>
</div>

<div class="bg-[#FFFBF3] py-8 sm:py-12 px-4">
    <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-4 gap-8">
        <aside class="lg:col-span-1 print:hidden">
            <div class="lg:sticky lg:top-28 space-y-6">
                <nav aria-label="On this page" class="rounded-2xl border border-amber-200/70 bg-white p-5 shadow-sm">
                    <h2 class="text-[11px] font-black uppercase tracking-[0.2em] text-[#B8860B] mb-3">On this page</h2>
                    <ol id="legal-toc" class="space-y-1.5 text-[13px] font-semibold text-gray-700 max-h-[46vh] overflow-y-auto pr-1"></ol>
                </nav>
                <nav aria-label="Other policies" class="rounded-2xl border border-amber-200/70 bg-white p-5 shadow-sm">
                    <h2 class="text-[11px] font-black uppercase tracking-[0.2em] text-[#B8860B] mb-3">All policies</h2>
                    <ul class="space-y-1.5 text-[13px] font-semibold">
                        @foreach($documents as $routeName => [$label])
                            <li>
                                <a href="{{ route($routeName) }}" class="{{ request()->routeIs($routeName) ? 'text-brandOrange' : 'text-gray-700 hover:text-brandOrange' }}" @if(request()->routeIs($routeName)) aria-current="page" @endif>{{ $label }}</a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
                <button type="button" onclick="window.print()" class="w-full rounded-xl border border-amber-300 bg-white px-4 py-2.5 text-xs font-black uppercase tracking-wider text-[#8A5A00] hover:bg-amber-50 transition">
                    Print / Save as PDF
                </button>
            </div>
        </aside>

        <article class="lg:col-span-3 rounded-2xl border border-amber-200/70 bg-white p-6 sm:p-10 shadow-sm legal-prose" id="legal-document">
            @hasSection('summary')
                <div class="mb-8 rounded-xl border border-[#E9C46A]/70 bg-gradient-to-br from-[#FFF6E0] to-white p-5 sm:p-6">
                    <h2 class="!mt-0 !mb-2 text-sm font-black uppercase tracking-[0.2em] text-[#8A5A00]">In short</h2>
                    @yield('summary')
                </div>
            @endif

            @yield('legal')

            <section class="legal-section" id="contact-grievance" data-toc="Contact &amp; Grievance Officer">
                <h2>Contact us &amp; Grievance Officer</h2>
                <p>If you have a question, a complaint or a request about this document, or about your data, please write to us:</p>
                <address class="not-italic rounded-xl bg-amber-50/60 border border-amber-200/70 p-4 text-sm leading-7 text-gray-700">
                    <strong>{{ $legal['entity'] }} ({{ $legal['short'] }})</strong><br>
                    {{ $legal['registration'] }}<br>
                    {{ $contact['address'] }}<br>
                    E-mail: <a href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a> &nbsp;|&nbsp; Phone / WhatsApp: <a href="tel:{{ preg_replace('/[^0-9+]/', '', $contact['phone']) }}">{{ $contact['phone'] }}</a><br>
                    Website: <a href="{{ $legal['website'] }}">{{ $legal['website'] }}</a>
                </address>
                <p class="mt-4"><strong>Grievance Officer</strong>@if(!empty($officer['name'])) &mdash; {{ $officer['name'] }}@endif, {{ $legal['short'] }}. Write to <a href="mailto:{{ $officer['email'] }}">{{ $officer['email'] }}</a>. We acknowledge every complaint within {{ $legal['acknowledge_days'] }} working days and aim to resolve it within {{ $legal['resolve_days'] }} days.</p>
            </section>
        </article>
    </div>
</div>

<style>
    .legal-prose { color: #374151; font-size: 15px; line-height: 1.8; }
    .legal-prose .legal-section { margin-top: 2.25rem; scroll-margin-top: 7rem; }
    .legal-prose h2 { font-size: 1.25rem; font-weight: 800; color: #4A4A4A; margin: 0 0 .75rem; padding-bottom: .5rem; border-bottom: 1px solid #F1E3BC; }
    .legal-prose h3 { font-size: 1rem; font-weight: 800; color: #4A4A4A; margin: 1.25rem 0 .4rem; }
    .legal-prose p { margin: .7rem 0; }
    .legal-prose ul, .legal-prose ol.legal-list { margin: .7rem 0 .7rem 1.25rem; padding: 0; }
    .legal-prose ul { list-style: disc; }
    .legal-prose ol.legal-list { list-style: decimal; }
    .legal-prose li { margin: .35rem 0; padding-left: .2rem; }
    .legal-prose a { color: #C2410C; font-weight: 600; text-decoration: underline; text-underline-offset: 2px; }
    .legal-prose table { width: 100%; border-collapse: collapse; font-size: 13.5px; margin: 1rem 0; }
    .legal-prose th { background: #FFF3D6; color: #6B4A00; text-align: left; font-weight: 800; }
    .legal-prose th, .legal-prose td { border: 1px solid #F0DDAE; padding: .55rem .7rem; vertical-align: top; }
    .legal-prose .table-wrap { overflow-x: auto; }
    .legal-prose .note { border-left: 4px solid #D4A017; background: #FFF8E6; padding: .8rem 1rem; border-radius: .5rem; margin: 1rem 0; font-size: 14px; }
    #legal-toc a { display: block; padding: .15rem 0; }
    #legal-toc a:hover, #legal-toc a.is-active { color: #C2410C; }
    @media print {
        header, nav.sticky, footer, .print\:hidden, aside { display: none !important; }
        .legal-prose { font-size: 12.5px; }
        body { background: #fff !important; }
    }
</style>
<script>
    // Build the "On this page" list from the section headings (the document stays fully readable without JavaScript).
    (function () {
        var list = document.getElementById('legal-toc');
        if (!list) return;
        document.querySelectorAll('#legal-document .legal-section').forEach(function (sec, i) {
            var h = sec.querySelector('h2');
            if (!h) return;
            if (!sec.id) sec.id = 'section-' + (i + 1);
            var li = document.createElement('li');
            var a = document.createElement('a');
            a.href = '#' + sec.id;
            a.textContent = h.textContent;
            li.appendChild(a);
            list.appendChild(li);
        });
    })();
</script>
@endsection
