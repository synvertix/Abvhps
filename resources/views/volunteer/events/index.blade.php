@extends('layouts.app')

@section('title', 'Events & Service Activities | ABVHPS Volunteer Portal')

@section('content')
<div class="bg-gray-50 min-h-screen pb-16">

    {{-- Official Header Banner --}}
    <div class="bg-gradient-to-r from-orange-900 via-orange-800 to-amber-900 text-white py-10 px-4 shadow-md border-b-4 border-yellow-500">
        <div class="max-w-6xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
            <div>
                <span class="bg-yellow-400 text-orange-950 text-[10px] font-black px-3.5 py-1 rounded-full uppercase tracking-widest inline-block shadow mb-2">
                    Volunteer Portal &bull; Seva Desk
                </span>
                <h1 class="text-2xl md:text-3xl font-black uppercase tracking-wide text-white">
                    Events &amp; Service Activities
                </h1>
                <p class="text-xs text-yellow-200/90 font-medium mt-0.5">
                    Manage conducted seva programs, report beneficiaries, and organize upcoming events
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                <a href="{{ route('volunteer.events.create') }}"
                   class="bg-yellow-500 hover:bg-yellow-400 text-orange-950 text-xs font-black px-4 py-2 rounded-xl shadow uppercase tracking-wider transition min-h-[44px] inline-flex items-center">
                    Create New Event
                </a>
                <a href="{{ route('volunteer.member_search') }}"
                   class="bg-white/15 hover:bg-white/25 border border-white/20 text-white text-xs font-bold px-4 py-2 rounded-xl uppercase tracking-wider transition min-h-[44px] inline-flex items-center">
                    Member Search
                </a>
                <a href="{{ route('volunteer.dashboard') }}"
                   class="bg-white/15 hover:bg-white/25 border border-white/20 text-white text-xs font-bold px-4 py-2 rounded-xl uppercase tracking-wider transition min-h-[44px] inline-flex items-center">
                    &larr; Dashboard
                </a>
            </div>
        </div>
    </div>

    {{-- Main Container --}}
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 space-y-6">

        {{-- Flash Alerts --}}
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-2xl text-xs font-bold shadow-sm flex items-center justify-between">
                <span>✓ {{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" class="text-emerald-600 font-black">×</button>
            </div>
        @endif
        @if(session('error'))
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-900 rounded-2xl text-xs font-bold shadow-sm flex items-center justify-between">
                <span>✕ {{ session('error') }}</span>
                <button onclick="this.parentElement.remove()" class="text-rose-600 font-black">×</button>
            </div>
        @endif

        {{-- Statistics Matrix --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-xs">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Total Events</span>
                <div class="font-mono text-2xl font-black text-gray-900 mt-1">{{ $stats['total'] }}</div>
                <span class="text-[10px] text-gray-500 mt-0.5 block">Organized by you</span>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-emerald-200 shadow-xs">
                <span class="text-[10px] font-black text-emerald-700 uppercase tracking-wider block">Conducted</span>
                <div class="font-mono text-2xl font-black text-emerald-600 mt-1">{{ $stats['conducted'] }}</div>
                <span class="text-[10px] text-emerald-600 mt-0.5 block">Completed seva</span>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-blue-200 shadow-xs">
                <span class="text-[10px] font-black text-blue-700 uppercase tracking-wider block">Upcoming</span>
                <div class="font-mono text-2xl font-black text-blue-600 mt-1">{{ $stats['upcoming'] }}</div>
                <span class="text-[10px] text-blue-600 mt-0.5 block">Scheduled ahead</span>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-amber-200 shadow-xs">
                <span class="text-[10px] font-black text-amber-700 uppercase tracking-wider block">Participants</span>
                <div class="font-mono text-2xl font-black text-amber-600 mt-1">{{ $stats['participants'] }}</div>
                <span class="text-[10px] text-amber-600 mt-0.5 block">Active attendees</span>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-orange-200 shadow-xs col-span-2 sm:col-span-1">
                <span class="text-[10px] font-black text-brandOrange uppercase tracking-wider block">Beneficiaries</span>
                <div class="font-mono text-2xl font-black text-brandOrange mt-1">{{ $stats['beneficiaries'] }}</div>
                <span class="text-[10px] text-orange-600 mt-0.5 block">Received seva benefit</span>
            </div>
        </div>

        {{-- Navigation Tabs --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-xs p-1.5 flex flex-wrap items-center gap-1.5">
            <a href="{{ route('volunteer.events.index', ['tab' => 'conducted']) }}"
               class="flex-1 sm:flex-initial text-center px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition {{ $tab === 'conducted' || $tab === 'completed' ? 'bg-brandOrange text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100' }}">
                Conducted Events ({{ $stats['conducted'] }})
            </a>
            <a href="{{ route('volunteer.events.index', ['tab' => 'upcoming']) }}"
               class="flex-1 sm:flex-initial text-center px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition {{ $tab === 'upcoming' ? 'bg-brandOrange text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100' }}">
                Upcoming Events ({{ $stats['upcoming'] }})
            </a>
            <a href="{{ route('volunteer.events.index', ['tab' => 'all']) }}"
               class="flex-1 sm:flex-initial text-center px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition {{ $tab === 'all' ? 'bg-brandOrange text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100' }}">
                All Events ({{ $stats['total'] }})
            </a>
            <a href="{{ route('volunteer.events.create') }}"
               class="ml-auto text-center px-4 py-2.5 rounded-xl text-xs font-bold text-brandOrange hover:bg-orange-50 transition border border-orange-200 inline-flex items-center">
                Create Event
            </a>
        </div>

        {{-- Events Roster List --}}
        @if($events->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($events as $event)
                    <div class="bg-white rounded-3xl border border-gray-200 shadow-sm hover:shadow-md transition flex flex-col justify-between overflow-hidden">
                        {{-- Card Header --}}
                        <div class="p-6 space-y-3">
                            <div class="flex items-start justify-between gap-2">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border {{ $event->status_badge_class }}">
                                    {{ strtoupper($event->status) }}
                                </span>
                                <span class="text-xs font-bold text-gray-500 flex items-center gap-1">
                                    <span>🗓️</span> {{ $event->event_date->format('d M Y') }}
                                </span>
                            </div>

                            <div>
                                <h3 class="font-black text-gray-900 text-base leading-snug line-clamp-2">
                                    {{ $event->title }}
                                </h3>
                                <span class="inline-block text-[11px] font-bold text-brandOrange mt-1 uppercase tracking-wider">
                                    {{ $event->event_type }}
                                </span>
                            </div>

                            @if($event->description)
                                <p class="text-xs text-gray-600 line-clamp-2 leading-relaxed">
                                    {{ $event->description }}
                                </p>
                            @endif

                            {{-- Location Pill --}}
                            <div class="bg-gray-50 p-2.5 rounded-xl text-[11px] text-gray-600 flex items-center gap-1.5 truncate">
                                <span>📍</span>
                                <span class="truncate font-medium">
                                    {{ implode(', ', array_filter([$event->venue, $event->village, $event->mandal, $event->district])) ?: 'Location specified' }}
                                </span>
                            </div>
                        </div>

                        {{-- Card Footer & Counts --}}
                        <div class="bg-gray-50/80 px-6 py-4 border-t border-gray-100 flex items-center justify-between gap-2">
                            <div class="flex items-center gap-3 text-xs">
                                <div>
                                    <span class="text-[9px] font-black text-gray-400 uppercase block">Participants</span>
                                    <span class="font-mono font-black text-gray-900">{{ $event->participants_count }}</span>
                                </div>
                                <div class="border-l border-gray-200 pl-3">
                                    <span class="text-[9px] font-black text-orange-400 uppercase block">Beneficiaries</span>
                                    <span class="font-mono font-black text-brandOrange">{{ $event->beneficiaries_count }}</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('volunteer.events.show', $event->id) }}"
                                   class="bg-brandOrange hover:bg-orange-600 text-white font-black text-[11px] px-3.5 py-2 rounded-xl shadow-xs transition">
                                    Manage &rarr;
                                </a>
                                @if($event->status === 'upcoming')
                                    <a href="{{ route('volunteer.events.edit', $event->id) }}"
                                       class="bg-white hover:bg-gray-100 text-gray-700 border border-gray-200 font-bold text-[11px] px-2.5 py-2 rounded-xl transition">
                                        ✏️
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="pt-4">
                {{ $events->links() }}
            </div>
        @else
            <div class="bg-white rounded-3xl border-2 border-dashed border-gray-200 p-12 text-center space-y-4">
                <div class="w-16 h-16 bg-orange-50 text-brandOrange rounded-full mx-auto flex items-center justify-center text-2xl shadow-inner">
                    🪔
                </div>
                <div class="space-y-1">
                    <h3 class="font-black text-gray-800 text-base uppercase tracking-wider">No {{ ucfirst($tab) }} Events Found</h3>
                    <p class="text-xs text-gray-500 max-w-md mx-auto">
                        @if($tab === 'upcoming')
                            You have no scheduled upcoming events. Create one to organize your future community service activities!
                        @else
                            You haven't recorded any completed seva events yet. Report your service activities and link participating members.
                        @endif
                    </p>
                </div>
                <a href="{{ route('volunteer.events.create') }}"
                   class="bg-brandOrange hover:bg-orange-600 text-white font-black text-xs px-6 py-3 rounded-2xl shadow-md uppercase tracking-wider transition inline-flex items-center gap-2">
                    <span>➕</span> Create Your First Event
                </a>
            </div>
        @endif

    </div>

</div>
@endsection
