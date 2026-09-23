@extends('layouts.app')

@section('title', 'About Us | ABVHPS')
@section('meta_description', 'Learn about the divine origin, vision, and mission of Akhanda Bharatha Viswa Hindu Parirakshana Samiti founded under the guidance of Sri Sri Sri Subrahmanneswara Swamy Garu.')

@section('content')
<!-- 1. Hero / Page Banner Section -->
<x-page-banner 
    page="about" 
    default-title="About ABVHPS" 
    default-subtitle="Akhanda Bharatha Viswa Hindu Parirakshana Samiti — Preserving Sanathana Dharma and Empowering Communities"
    badge="About Our Organization"
    min-height="280px"
/>

<div class="py-12 px-4 bg-gray-50">
    <div class="max-w-5xl mx-auto space-y-8">

        <!-- 1b. Our Divine Origin -->
        <div class="bg-white p-6 sm:p-8 rounded-lg border border-gray-200 shadow-sm space-y-4">
            <h2 class="text-xs font-bold text-brandGray uppercase tracking-wider border-b border-gray-200 pb-2">
                {{ __('Our Divine Origin') }}
            </h2>
            <div class="space-y-3 text-sm text-brandGray leading-relaxed">
                <p>{!! __(config('abvhps.copy.origin_1'), ['guru' => '<strong>' . e(config('abvhps.copy.guru')) . '</strong>']) !!}</p>
                <p>{{ __(config('abvhps.copy.origin_2')) }}</p>
            </div>
        </div>

        <!-- 1c. Vision, Mission & Goal -->
        <div class="rounded-2xl bg-gradient-to-b from-[#FFF8EC] to-[#FFFDF8] border border-amber-200/70 p-6 sm:p-10">
            @include('partials.pillars')
        </div>

        <!-- 2. Our Mission Section -->
        <div class="bg-white p-6 sm:p-8 rounded-lg border border-gray-200 shadow-sm space-y-4">
            <h2 class="text-xs font-bold text-brandGray uppercase tracking-wider border-b border-gray-200 pb-2">
                {{ __('Our Mission in Action') }}
            </h2>
            <div class="space-y-3 text-sm text-brandGray leading-relaxed">
                <p>
                    Akhanda Bharatha Viswa Hindu Parirakshana Samiti (ABVHPS) is dedicated to safeguarding, nurturing, and propagating the timeless principles of Sanathana Dharma across every village, mandal, and district. Through proactive grassroots initiatives, we unite communities to preserve cultural heritage, temple welfare, and traditional values.
                </p>
                <p>
                    Our mission encompasses selfless service (Seva), educational support for underprivileged youth, comprehensive healthcare camps, Gau Samrakshana (cow protection), and rural empowerment through specialized wings like Rudrasena, Kala Brundam, Grama Seva Dal, and Organic Farmers support desks.
                </p>
            </div>
        </div>

        <!-- 3. Our Values Section -->
        <div class="bg-gray-50 p-6 sm:p-8 rounded-lg border border-gray-200 space-y-6">
            <h2 class="text-xs font-bold text-brandGray uppercase tracking-wider border-b border-gray-200 pb-2">
                Our Core Values
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Value Card 1 -->
                <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm flex flex-col justify-between space-y-3">
                    <div class="space-y-2">
                        <div class="w-10 h-10 rounded-full overflow-hidden bg-white border border-brandOrange flex items-center justify-center p-0.5 shrink-0 shadow-xs">
                            <img src="{{ asset('images/ABVHPS_LOGO.jpg') }}" class="w-full h-full object-cover rounded-full" alt="ABVHPS Logo">
                        </div>
                        <h3 class="text-xs font-bold text-brandGray uppercase tracking-wider">Dharma Rakshana</h3>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Upholding the timeless heritage, sacred temples, and spiritual practices of Sanathana Dharma with utmost reverence.
                        </p>
                    </div>
                </div>

                <!-- Value Card 2 -->
                <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm flex flex-col justify-between space-y-3">
                    <div class="space-y-2">
                        <div class="w-10 h-10 rounded-full bg-brandLightOrange flex items-center justify-center text-brandOrange text-lg font-bold border border-orange-200">
                            🤝
                        </div>
                        <h3 class="text-xs font-bold text-brandGray uppercase tracking-wider">Nishkama Seva</h3>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Serving the needy, promoting social welfare, and providing humanitarian assistance without any expectation of personal gain.
                        </p>
                    </div>
                </div>

                <!-- Value Card 3 -->
                <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm flex flex-col justify-between space-y-3">
                    <div class="space-y-2">
                        <div class="w-10 h-10 rounded-full bg-brandLightOrange flex items-center justify-center text-brandOrange text-lg font-bold border border-orange-200">
                            🌱
                        </div>
                        <h3 class="text-xs font-bold text-brandGray uppercase tracking-wider">Grama Vikas</h3>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Empowering rural communities through sustainable agriculture, environmental conservation, and local youth development.
                        </p>
                    </div>
                </div>

                <!-- Value Card 4 -->
                <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm flex flex-col justify-between space-y-3">
                    <div class="space-y-2">
                        <div class="w-10 h-10 rounded-full bg-brandLightOrange flex items-center justify-center text-brandOrange text-lg font-bold border border-orange-200">
                            🛡️
                        </div>
                        <h3 class="text-xs font-bold text-brandGray uppercase tracking-wider">Unity & Integrity</h3>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Fostering national integrity, social harmony, and collective brotherhood among all sections of society.
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
