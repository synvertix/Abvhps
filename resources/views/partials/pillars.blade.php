{{-- Vision · Mission · Goal — shared by the home page and the About page (text comes from config/abvhps.php) --}}
<div class="max-w-6xl mx-auto">
    <div class="text-center mb-8 sm:mb-10">
        <span class="text-[10px] sm:text-[11px] font-black text-[#B8860B] uppercase tracking-[0.3em]">{{ __('Vision · Mission · Goal') }}</span>
        <h2 class="text-2xl sm:text-3xl font-extrabold text-brandGray mt-2">{{ __('The Sacred Purpose Behind Our Seva') }}</h2>
        <div class="flex items-center justify-center gap-2 mt-3 text-[#D4A017]" aria-hidden="true">
            <span class="h-px w-14 bg-gradient-to-r from-transparent to-[#D4A017]"></span>
            <span class="text-[10px]">&#9670;</span>
            <span class="h-px w-14 bg-gradient-to-l from-transparent to-[#D4A017]"></span>
        </div>
        <p class="text-sm text-gray-600 max-w-xl mx-auto mt-3 leading-relaxed">{{ __('Rooted in Dharma, driven by Seva — three promises we hold to, together with every member, volunteer and well-wisher.') }}</p>
    </div>

    @php
        $pillarCards = [
            ['title' => 'Our Vision',  'text' => config('abvhps.copy.vision'),  'icon' => 'vision'],
            ['title' => 'Our Mission', 'text' => config('abvhps.copy.mission'), 'icon' => 'mission'],
            ['title' => 'The Goal',    'text' => config('abvhps.copy.goal'),    'icon' => 'goal'],
        ];
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($pillarCards as $card)
            <div class="vmg-card group relative overflow-hidden bg-white rounded-2xl border border-amber-200/70 shadow-sm hover:shadow-xl hover:shadow-amber-900/10 hover:-translate-y-1 transition duration-300 p-7 text-center">
                <span class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-transparent via-[#D4A017] to-transparent" aria-hidden="true"></span>
                <svg class="absolute -right-12 -bottom-12 w-44 h-44 text-[#D4A017]/10 group-hover:rotate-12 transition-transform duration-700" viewBox="0 0 200 200" aria-hidden="true"><use href="#abvhps-mandala" width="200" height="200"/></svg>
                <div class="relative">
                    <div class="mx-auto mb-4 w-14 h-14 rounded-full bg-gradient-to-br from-[#FFF3D1] to-[#FBE3A1] ring-2 ring-[#D4A017]/50 ring-offset-2 ring-offset-white flex items-center justify-center text-[#B8860B]">
                        @if($card['icon'] === 'vision')
                            <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true">
                                <circle cx="12" cy="12" r="4"/>
                                <path d="M12 2.5v2.8M12 18.7v2.8M2.5 12h2.8M18.7 12h2.8M5.3 5.3l2 2M16.7 16.7l2 2M18.7 5.3l-2 2M7.3 16.7l-2 2"/>
                            </svg>
                        @elseif($card['icon'] === 'mission')
                            <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 20.5l-1.2-1.1C6 15.2 3 12.5 3 9.1 3 6.5 5 4.5 7.6 4.5c1.7 0 3.3.8 4.4 2.1 1.1-1.3 2.7-2.1 4.4-2.1C19 4.5 21 6.5 21 9.1c0 3.4-3 6.1-7.8 10.3L12 20.5z"/>
                            </svg>
                        @else
                            <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 3c2.6 3 4 5.4 4 7.6A4 4 0 0 1 12 14.6a4 4 0 0 1-4-4C8 8.4 9.4 6 12 3z"/>
                                <path d="M4 17.5h16l-1.6 3H5.6L4 17.5z"/>
                            </svg>
                        @endif
                    </div>
                    <h3 class="font-extrabold text-brandGray text-xl">{{ __($card['title']) }}</h3>
                    <div class="flex items-center justify-center gap-2 my-3 text-[#D4A017]" aria-hidden="true">
                        <span class="h-px w-8 bg-current opacity-60"></span><span class="text-[8px]">&#9670;</span><span class="h-px w-8 bg-current opacity-60"></span>
                    </div>
                    <p class="text-sm text-gray-600 leading-7">{{ __($card['text']) }}</p>
                </div>
            </div>
        @endforeach
    </div>
</div>
