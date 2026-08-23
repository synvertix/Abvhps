@extends('layouts.app')

@section('title', 'Create / Report Volunteer Event | ABVHPS')

@section('content')
<div class="bg-gray-50 min-h-screen pb-16">

    {{-- Header Banner --}}
    <div class="bg-gradient-to-r from-orange-900 via-orange-800 to-amber-900 text-white py-10 px-4 shadow-md border-b-4 border-yellow-500">
        <div class="max-w-4xl mx-auto flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <span class="bg-yellow-400 text-orange-950 text-[10px] font-black px-3.5 py-1 rounded-full uppercase tracking-widest inline-block shadow mb-2">
                    Event Registration Desk
                </span>
                <h1 class="text-2xl md:text-3xl font-black uppercase tracking-wide text-white">
                    Create / Report Event
                </h1>
                <p class="text-xs text-yellow-200/90 font-medium mt-0.5">
                    Record upcoming or conducted Sanathana Dharma Seva &amp; Community programs
                </p>
            </div>

            <a href="{{ route('volunteer.events.index') }}"
               class="bg-white/15 hover:bg-white/25 border border-white/20 text-white text-xs font-bold px-4 py-2 rounded-xl uppercase tracking-wider transition min-h-[44px] inline-flex items-center">
                &larr; Back to Events
            </a>
        </div>
    </div>

    {{-- Form Body --}}
    <div class="max-w-4xl mx-auto px-4 sm:px-6 mt-8">

        @if($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-900 rounded-2xl text-xs font-bold shadow-sm mb-6 space-y-1">
                <div class="font-black uppercase">Please correct the following errors:</div>
                <ul class="list-disc list-inside font-normal space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('volunteer.events.store') }}" method="POST" class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 sm:p-8 space-y-6">
            @csrf

            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-base font-black text-gray-900 uppercase tracking-wide flex items-center gap-2">
                    <span>🪔</span> Event Information
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Provide complete details about the seva program, schedule, and jurisdictional location.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                {{-- Event Title --}}
                <div class="sm:col-span-2">
                    <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-1.5">
                        Event Title <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="title" value="{{ old('title') }}" required maxlength="255"
                           placeholder="e.g., Free Annadanam Seva Camp / Goshala Seva Program"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-brandOrange focus:ring-2 focus:ring-orange-200 text-sm font-semibold transition">
                </div>

                {{-- Event / Service Type --}}
                <div>
                    <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-1.5">
                        Event / Service Type <span class="text-rose-500">*</span>
                    </label>
                    <select name="event_type" required
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-brandOrange focus:ring-2 focus:ring-orange-200 text-sm font-semibold transition bg-white">
                        <option value="">-- Select Service Type --</option>
                        @foreach($serviceTypes as $key => $label)
                            <option value="{{ $key }}" {{ old('event_type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Event Status --}}
                <div>
                    <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-1.5">
                        Event Status <span class="text-rose-500">*</span>
                    </label>
                    <select name="status" required
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-brandOrange focus:ring-2 focus:ring-orange-200 text-sm font-semibold transition bg-white">
                        <option value="upcoming" {{ old('status', 'upcoming') === 'upcoming' ? 'selected' : '' }}>Upcoming Event</option>
                        <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Completed / Conducted Event</option>
                        <option value="cancelled" {{ old('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                {{-- Event Date --}}
                <div>
                    <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-1.5">
                        Event Date <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="event_date" value="{{ old('event_date', date('Y-m-d')) }}" required
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-brandOrange focus:ring-2 focus:ring-orange-200 text-sm font-semibold transition">
                </div>

                {{-- Timings (Optional) --}}
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-1.5">Start Time</label>
                        <input type="text" name="start_time" value="{{ old('start_time') }}" placeholder="e.g. 09:00 AM" maxlength="20"
                               class="w-full px-3 py-3 rounded-xl border border-gray-300 focus:border-brandOrange focus:ring-2 focus:ring-orange-200 text-xs font-semibold transition">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-1.5">End Time</label>
                        <input type="text" name="end_time" value="{{ old('end_time') }}" placeholder="e.g. 05:00 PM" maxlength="20"
                               class="w-full px-3 py-3 rounded-xl border border-gray-300 focus:border-brandOrange focus:ring-2 focus:ring-orange-200 text-xs font-semibold transition">
                    </div>
                </div>

                {{-- Description --}}
                <div class="sm:col-span-2">
                    <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-1.5">
                        Description &amp; Objective
                    </label>
                    <textarea name="description" rows="3" maxlength="5000"
                              placeholder="Describe the purpose, background, and planned seva activities..."
                              class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-brandOrange focus:ring-2 focus:ring-orange-200 text-xs font-medium transition">{{ old('description') }}</textarea>
                </div>

            </div>

            {{-- Location Details --}}
            <div class="border-t border-gray-100 pt-6 space-y-4">
                <h3 class="text-xs font-black text-gray-900 uppercase tracking-wider flex items-center gap-2">
                    <span>📍</span> Location &amp; Venue Details
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Venue / Specific Address</label>
                        <input type="text" name="venue" value="{{ old('venue') }}" placeholder="e.g. Sri Rama Temple Hall, Main Bazar" maxlength="255"
                               class="w-full px-3 py-2.5 rounded-xl border border-gray-300 text-xs font-semibold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Village / Grama Panchayat</label>
                        <input type="text" name="village" value="{{ old('village', $volunteer->resolved_grama_panchayat) }}" maxlength="100"
                               class="w-full px-3 py-2.5 rounded-xl border border-gray-300 text-xs font-semibold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Mandal</label>
                        <input type="text" name="mandal" value="{{ old('mandal', $volunteer->resolved_mandal) }}" maxlength="100"
                               class="w-full px-3 py-2.5 rounded-xl border border-gray-300 text-xs font-semibold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">District</label>
                        <input type="text" name="district" value="{{ old('district', $volunteer->resolved_district) }}" maxlength="100"
                               class="w-full px-3 py-2.5 rounded-xl border border-gray-300 text-xs font-semibold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">State</label>
                        <input type="text" name="state" value="{{ old('state', $volunteer->resolved_state ?? 'Andhra Pradesh') }}" maxlength="100"
                               class="w-full px-3 py-2.5 rounded-xl border border-gray-300 text-xs font-semibold">
                    </div>
                </div>
            </div>

            {{-- Outcome / Summary (Optional / For Completed Events) --}}
            <div class="border-t border-gray-100 pt-6 space-y-2">
                <label class="block text-xs font-black text-gray-700 uppercase tracking-wider">
                    Work / Service Conducted &amp; Outcome Summary
                </label>
                <p class="text-[11px] text-gray-500">
                    For completed events, summarize the actual service delivered, community feedback, and milestones.
                </p>
                <textarea name="outcome" rows="3" maxlength="5000"
                          placeholder="e.g. Distributed 500 meal packets to devotees; 120 villagers attended medical checkup..."
                          class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-brandOrange focus:ring-2 focus:ring-orange-200 text-xs font-medium transition">{{ old('outcome') }}</textarea>
            </div>

            {{-- Form Actions --}}
            <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                <a href="{{ route('volunteer.events.index') }}"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold px-5 py-3 rounded-xl uppercase tracking-wider transition">
                    Cancel
                </a>
                <button type="submit"
                        class="bg-brandOrange hover:bg-orange-600 text-white text-xs font-black px-6 py-3 rounded-xl shadow-md uppercase tracking-wider transition cursor-pointer">
                    Save &amp; Continue &rarr;
                </button>
            </div>

        </form>

    </div>

</div>
@endsection
