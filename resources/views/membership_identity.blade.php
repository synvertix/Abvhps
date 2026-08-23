@extends('layouts.app')

@section('title', 'Membership Identity Verification | ABVHPS')
@section('meta_description', 'Verify your government official identity document (Aadhaar, PAN, Voter ID, Driving Licence, or Passport) to complete your ABVHPS membership.')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-orange-50/40 via-amber-50/20 to-gray-50 py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto space-y-6">

        {{-- Step Progression Timeline --}}
        <div class="bg-white/80 backdrop-blur-md rounded-2xl border border-orange-100 shadow-sm p-4 sm:p-5">
            <div class="grid grid-cols-4 gap-2 text-center text-xs font-bold">
                <div class="flex flex-col items-center gap-1 text-emerald-700">
                    <span class="w-7 h-7 rounded-full bg-emerald-100 border-2 border-emerald-500 flex items-center justify-center text-xs">✓</span>
                    <span class="text-[11px]">Mobile OTP</span>
                </div>
                <div class="flex flex-col items-center gap-1 text-emerald-700">
                    <span class="w-7 h-7 rounded-full bg-emerald-100 border-2 border-emerald-500 flex items-center justify-center text-xs">✓</span>
                    <span class="text-[11px]">Payment</span>
                </div>
                <div class="flex flex-col items-center gap-1 text-brandOrange">
                    <span class="w-7 h-7 rounded-full bg-orange-500 text-white shadow-md flex items-center justify-center text-xs ring-4 ring-orange-100">3</span>
                    <span class="text-[11px] font-extrabold uppercase tracking-wide">Identity Gate</span>
                </div>
                <div class="flex flex-col items-center gap-1 text-gray-400">
                    <span class="w-7 h-7 rounded-full bg-gray-100 border border-gray-300 flex items-center justify-center text-xs">4</span>
                    <span class="text-[11px]">Application</span>
                </div>
            </div>
        </div>

        {{-- Header Hero --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-xl overflow-hidden">
            <div class="bg-gradient-to-r from-brandOrange via-orange-600 to-amber-600 p-6 sm:p-8 text-white text-center">
                <span class="inline-block bg-white/20 backdrop-blur-sm text-orange-100 text-[10px] font-black px-3.5 py-1 rounded-full uppercase tracking-widest mb-2 border border-white/30">
                    Official Verification Gate
                </span>
                <h1 class="text-xl sm:text-3xl font-black uppercase tracking-tight text-white">
                    Verify Official Identity Document
                </h1>
                <p class="text-xs sm:text-sm text-orange-100 max-w-xl mx-auto font-medium mt-1">
                    To maintain the sanctity and integrity of ABVHPS membership, please verify <strong>any ONE</strong> of the five official government identity methods below.
                </p>
            </div>

            <div class="p-6 sm:p-8 space-y-6">

                {{-- Global Alert Banner --}}
                <div id="alert_box" class="hidden p-4 rounded-2xl text-xs font-semibold"></div>

                {{-- Success Container (shown once any method succeeds) --}}
                <div id="success_container" class="@if(isset($member) && $member->hasVerifiedIdentity()) block @else hidden @endif bg-emerald-50 border-2 border-emerald-300 rounded-2xl p-6 text-center space-y-3">
                    <div class="w-12 h-12 rounded-full bg-emerald-500 text-white text-2xl flex items-center justify-center mx-auto shadow-md">
                        ✓
                    </div>
                    <h3 class="text-base font-black text-emerald-900 uppercase">
                        Identity Successfully Verified!
                    </h3>
                    <p class="text-xs text-emerald-800">
                        Verified Name: <strong id="verified_name_display" class="font-bold text-gray-900 uppercase">{{ $member->identity_verified_name ?? ($member->full_name ?? '') }}</strong>
                    </p>
                    <div class="pt-2">
                        <a href="{{ url('/membership/application') }}" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-black px-8 py-3.5 rounded-xl uppercase tracking-wider shadow-lg transition transform hover:scale-105">
                            <span>Continue to Membership Application</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>

                {{-- Identity Verification Options Accordion --}}
                <div id="options_container" class="@if(isset($member) && $member->hasVerifiedIdentity()) hidden @else block @endif space-y-4">
                    
                    {{-- Option 1: Aadhaar via DigiLocker --}}
                    <div class="border-2 border-gray-200 hover:border-orange-300 rounded-2xl p-5 transition bg-gray-50/50">
                        <div class="flex items-center justify-between cursor-pointer" onclick="toggleMethod('aadhaar')">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">🆔</span>
                                <div>
                                    <h3 class="text-sm font-black text-gray-900 uppercase">1. Aadhaar Card (DigiLocker)</h3>
                                    <p class="text-[11px] text-gray-500">Fast paperless verification via government DigiLocker portal</p>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-brandOrange uppercase bg-orange-100 px-3 py-1 rounded-full">Recommended</span>
                        </div>

                        <div id="method_aadhaar" class="mt-4 pt-4 border-t border-gray-200 space-y-3">
                            <p class="text-xs text-gray-600">
                                You will be redirected to the secure government DigiLocker portal to authenticate your Aadhaar card seamlessly.
                            </p>
                            <form method="POST" action="{{ route('membership.aadhaar.start') }}" onsubmit="startAadhaarFlow(event)">
                                @csrf
                                <input type="hidden" name="user_flow" value="web">
                                <button type="submit" id="btn_aadhaar_start" class="w-full bg-brandOrange hover:bg-orange-600 text-white text-xs sm:text-sm font-black py-3 px-4 rounded-xl uppercase tracking-wider shadow-md transition flex items-center justify-center gap-2">
                                    <span>🔐 Proceed with DigiLocker Aadhaar</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Option 2: PAN Card --}}
                    <div class="border-2 border-gray-200 hover:border-orange-300 rounded-2xl p-5 transition bg-gray-50/50">
                        <div class="flex items-center justify-between cursor-pointer" onclick="toggleMethod('pan')">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">💳</span>
                                <div>
                                    <h3 class="text-sm font-black text-gray-900 uppercase">2. Permanent Account Number (PAN)</h3>
                                    <p class="text-[11px] text-gray-500">Instant verification via Income Tax Department</p>
                                </div>
                            </div>
                            <span class="text-xs text-gray-400 font-bold">&darr;</span>
                        </div>

                        <form id="form_pan" onsubmit="submitPanVerification(event)" class="mt-4 pt-4 border-t border-gray-200 space-y-3">
                            @csrf
                            <div>
                                <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wide mb-1">
                                    PAN Card Number (10 Alphanumeric Characters) <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="pan_number" name="pan_number" maxlength="10" required
                                       pattern="[A-Za-z]{5}[0-9]{4}[A-Za-z]{1}"
                                       class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl text-sm font-mono uppercase font-bold text-gray-900 focus:outline-none focus:border-brandOrange transition"
                                       placeholder="ABCDE1234F"
                                       oninput="this.value = this.value.toUpperCase()">
                            </div>
                            <button type="submit" id="btn_pan_submit" class="w-full bg-brandOrange hover:bg-orange-600 text-white text-xs sm:text-sm font-black py-3 px-4 rounded-xl uppercase tracking-wider shadow-md transition flex items-center justify-center gap-2">
                                <span>Verify PAN Card</span>
                            </button>
                        </form>
                    </div>

                    {{-- Option 3: Voter ID (EPIC) --}}
                    <div class="border-2 border-gray-200 hover:border-orange-300 rounded-2xl p-5 transition bg-gray-50/50">
                        <div class="flex items-center justify-between cursor-pointer" onclick="toggleMethod('voter')">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">🗳️</span>
                                <div>
                                    <h3 class="text-sm font-black text-gray-900 uppercase">3. Voter ID Card (EPIC)</h3>
                                    <p class="text-[11px] text-gray-500">Election Commission of India Electoral Photo ID</p>
                                </div>
                            </div>
                            <span class="text-xs text-gray-400 font-bold">&darr;</span>
                        </div>

                        <form id="form_voter" onsubmit="submitVoterVerification(event)" class="hidden mt-4 pt-4 border-t border-gray-200 space-y-3">
                            @csrf
                            <div>
                                <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wide mb-1">
                                    Voter ID / EPIC Number <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="voter_id" name="voter_id" maxlength="30" required
                                       class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl text-sm font-mono uppercase font-bold text-gray-900 focus:outline-none focus:border-brandOrange transition"
                                       placeholder="e.g. ABC1234567"
                                       oninput="this.value = this.value.toUpperCase()">
                            </div>
                            <button type="submit" id="btn_voter_submit" class="w-full bg-brandOrange hover:bg-orange-600 text-white text-xs sm:text-sm font-black py-3 px-4 rounded-xl uppercase tracking-wider shadow-md transition flex items-center justify-center gap-2">
                                <span>Verify Voter ID</span>
                            </button>
                        </form>
                    </div>

                    {{-- Option 4: Driving Licence --}}
                    <div class="border-2 border-gray-200 hover:border-orange-300 rounded-2xl p-5 transition bg-gray-50/50">
                        <div class="flex items-center justify-between cursor-pointer" onclick="toggleMethod('dl')">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">🚗</span>
                                <div>
                                    <h3 class="text-sm font-black text-gray-900 uppercase">4. Driving Licence</h3>
                                    <p class="text-[11px] text-gray-500">Ministry of Road Transport &amp; Highways record</p>
                                </div>
                            </div>
                            <span class="text-xs text-gray-400 font-bold">&darr;</span>
                        </div>

                        <form id="form_dl" onsubmit="submitDlVerification(event)" class="hidden mt-4 pt-4 border-t border-gray-200 space-y-3">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wide mb-1">
                                        DL Number <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="dl_number" name="dl_number" maxlength="30" required
                                           class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl text-sm font-mono uppercase font-bold text-gray-900 focus:outline-none focus:border-brandOrange transition"
                                           placeholder="e.g. DL-0120110012345"
                                           oninput="this.value = this.value.toUpperCase()">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wide mb-1">
                                        Date of Birth <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" id="dl_dob" name="dob" required
                                           class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl text-sm font-bold text-gray-900 focus:outline-none focus:border-brandOrange transition">
                                </div>
                            </div>
                            <button type="submit" id="btn_dl_submit" class="w-full bg-brandOrange hover:bg-orange-600 text-white text-xs sm:text-sm font-black py-3 px-4 rounded-xl uppercase tracking-wider shadow-md transition flex items-center justify-center gap-2">
                                <span>Verify Driving Licence</span>
                            </button>
                        </form>
                    </div>

                    {{-- Option 5: Passport --}}
                    <div class="border-2 border-gray-200 hover:border-orange-300 rounded-2xl p-5 transition bg-gray-50/50">
                        <div class="flex items-center justify-between cursor-pointer" onclick="toggleMethod('passport')">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">✈️</span>
                                <div>
                                    <h3 class="text-sm font-black text-gray-900 uppercase">5. Indian Passport</h3>
                                    <p class="text-[11px] text-gray-500">Passport Seva / Ministry of External Affairs verification</p>
                                </div>
                            </div>
                            <span class="text-xs text-gray-400 font-bold">&darr;</span>
                        </div>

                        <form id="form_passport" onsubmit="submitPassportVerification(event)" class="hidden mt-4 pt-4 border-t border-gray-200 space-y-3">
                            @csrf
                            <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 text-[11px] text-blue-900 font-medium">
                                ℹ️ Please enter the <strong>Passport File Number</strong> (typically 12–15 alphanumeric characters located on the back cover or acknowledgement slip), not the booklet number.
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wide mb-1">
                                        Passport File Number <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="passport_file_number" name="file_number" maxlength="20" required
                                           class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl text-sm font-mono uppercase font-bold text-gray-900 focus:outline-none focus:border-brandOrange transition"
                                           placeholder="e.g. HY1234567890123"
                                           oninput="this.value = this.value.toUpperCase()">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wide mb-1">
                                        Date of Birth <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" id="passport_dob" name="dob" required
                                           class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl text-sm font-bold text-gray-900 focus:outline-none focus:border-brandOrange transition">
                                </div>
                            </div>
                            <button type="submit" id="btn_passport_submit" class="w-full bg-brandOrange hover:bg-orange-600 text-white text-xs sm:text-sm font-black py-3 px-4 rounded-xl uppercase tracking-wider shadow-md transition flex items-center justify-center gap-2">
                                <span>Verify Passport</span>
                            </button>
                        </form>
                    </div>

                </div>

            </div>
        </div>

    </div>
</div>

<script>
    const csrfToken = '{{ csrf_token() }}';

    function toggleMethod(method) {
        const forms = ['form_pan', 'form_voter', 'form_dl', 'form_passport'];
        forms.forEach(f => {
            const el = document.getElementById(f);
            if (el) el.classList.add('hidden');
        });

        const targetId = 'form_' + method;
        const target = document.getElementById(targetId);
        if (target) {
            target.classList.remove('hidden');
        }
    }

    function showAlert(type, message) {
        const alertBox = document.getElementById('alert_box');
        if (!alertBox) return;

        alertBox.className = type === 'success'
            ? 'p-4 rounded-2xl text-xs font-bold bg-emerald-100 text-emerald-900 border border-emerald-200 block'
            : 'p-4 rounded-2xl text-xs font-bold bg-rose-100 text-rose-900 border border-rose-200 block';
        alertBox.innerText = message;
        alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function showVerificationSuccess(verifiedName) {
        document.getElementById('options_container').classList.add('hidden');
        document.getElementById('success_container').classList.remove('hidden');
        document.getElementById('verified_name_display').innerText = verifiedName || 'VERIFIED';
        showAlert('success', 'Identity document verified successfully! You may now proceed.');
    }

    async function submitPanVerification(e) {
        e.preventDefault();
        const btn = document.getElementById('btn_pan_submit');
        const panInput = document.getElementById('pan_number');
        const panVal = panInput.value.trim().toUpperCase();

        if (!panVal) return;

        btn.disabled = true;
        btn.innerHTML = '<span>⏳ Verifying PAN with Provider...</span>';

        try {
            const res = await fetch('{{ route("membership.identity.pan.verify") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ pan_number: panVal })
            });

            const data = await res.json();

            if (res.ok && (data.status === 'success' || data.status === 'already_verified')) {
                showVerificationSuccess(data.verified_name);
            } else {
                showAlert('error', data.message || 'PAN verification failed. Please check the details and try again.');
            }
        } catch (err) {
            showAlert('error', 'Network error communicating with identity service. Please try again.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<span>Verify PAN Card</span>';
        }
    }

    async function submitVoterVerification(e) {
        e.preventDefault();
        const btn = document.getElementById('btn_voter_submit');
        const voterInput = document.getElementById('voter_id');
        const voterVal = voterInput.value.trim().toUpperCase();

        if (!voterVal) return;

        btn.disabled = true;
        btn.innerHTML = '<span>⏳ Verifying Voter ID with Provider...</span>';

        try {
            const res = await fetch('{{ route("membership.identity.voter.verify") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ voter_id: voterVal })
            });

            const data = await res.json();

            if (res.ok && (data.status === 'success' || data.status === 'already_verified')) {
                showVerificationSuccess(data.verified_name);
            } else {
                showAlert('error', data.message || 'Voter ID verification failed. Please check the details.');
            }
        } catch (err) {
            showAlert('error', 'Network error communicating with identity service.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<span>Verify Voter ID</span>';
        }
    }

    async function submitDlVerification(e) {
        e.preventDefault();
        const btn = document.getElementById('btn_dl_submit');
        const dlVal = document.getElementById('dl_number').value.trim().toUpperCase();
        const dobVal = document.getElementById('dl_dob').value.trim();

        if (!dlVal || !dobVal) return;

        btn.disabled = true;
        btn.innerHTML = '<span>⏳ Verifying Driving Licence...</span>';

        try {
            const res = await fetch('{{ route("membership.identity.dl.verify") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ dl_number: dlVal, dob: dobVal })
            });

            const data = await res.json();

            if (res.ok && (data.status === 'success' || data.status === 'already_verified')) {
                showVerificationSuccess(data.verified_name);
            } else {
                showAlert('error', data.message || 'Driving Licence verification failed.');
            }
        } catch (err) {
            showAlert('error', 'Network error communicating with identity service.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<span>Verify Driving Licence</span>';
        }
    }

    async function submitPassportVerification(e) {
        e.preventDefault();
        const btn = document.getElementById('btn_passport_submit');
        const fileVal = document.getElementById('passport_file_number').value.trim().toUpperCase();
        const dobVal = document.getElementById('passport_dob').value.trim();

        if (!fileVal || !dobVal) return;

        btn.disabled = true;
        btn.innerHTML = '<span>⏳ Verifying Passport...</span>';

        try {
            const res = await fetch('{{ route("membership.identity.passport.verify") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ file_number: fileVal, dob: dobVal })
            });

            const data = await res.json();

            if (res.ok && (data.status === 'success' || data.status === 'already_verified')) {
                showVerificationSuccess(data.verified_name);
            } else {
                showAlert('error', data.message || 'Passport verification failed.');
            }
        } catch (err) {
            showAlert('error', 'Network error communicating with identity service.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<span>Verify Passport</span>';
        }
    }

    async function startAadhaarFlow(e) {
        e.preventDefault();
        const btn = document.getElementById('btn_aadhaar_start');
        btn.disabled = true;
        btn.innerHTML = '<span>⏳ Initializing DigiLocker...</span>';

        try {
            const res = await fetch('{{ route("membership.aadhaar.start") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ user_flow: 'web' })
            });

            const data = await res.json();

            if (res.ok && data.status === 'redirect' && data.redirect_url) {
                window.location.href = data.redirect_url;
            } else {
                showAlert('error', data.message || 'DigiLocker initialization failed. Please try again.');
                btn.disabled = false;
                btn.innerHTML = '<span>🔐 Proceed with DigiLocker Aadhaar</span>';
            }
        } catch (err) {
            showAlert('error', 'Network error starting DigiLocker flow.');
            btn.disabled = false;
            btn.innerHTML = '<span>🔐 Proceed with DigiLocker Aadhaar</span>';
        }
    }
</script>
@endsection
