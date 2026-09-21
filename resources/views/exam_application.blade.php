@extends('layouts.app')

@section('content')
@if(!$examSettings)
<div class="max-w-3xl mx-auto py-16 px-4">
    <div class="bg-white rounded-2xl border border-gray-200 p-10 text-center shadow-sm">
        <div class="text-4xl mb-3">📋</div>
        <h2 class="text-lg font-black uppercase text-gray-800 tracking-wide">No Active Examinations Available</h2>
        <p class="text-xs text-gray-500 max-w-md mx-auto mt-2">
            There are currently no active examination registrations open. Please check back later for upcoming examination announcements.
        </p>
        <div class="mt-6 flex items-center justify-center gap-3">
            <a href="{{ route('public.home') }}" class="bg-brandOrange text-white text-xs font-black px-5 py-2.5 rounded-lg uppercase shadow hover:bg-orange-700 transition">
                Return to Home
            </a>
            <a href="{{ route('public.exams_board') }}" class="bg-gray-100 text-gray-700 text-xs font-bold px-5 py-2.5 rounded-lg uppercase hover:bg-gray-200 transition">
                Exams Notice Board
            </a>
        </div>
    </div>
</div>
@else
<div class="bg-gray-50 min-h-screen pb-16">

    {{-- Official Unified Header Banner --}}
    <div class="bg-gray-900 text-white py-10 sm:py-12 px-4 border-b-4 border-brandOrange text-center shadow-md">
        <div class="max-w-4xl mx-auto space-y-3">
            <div class="w-16 h-16 rounded-full overflow-hidden bg-white border-2 border-brandOrange shadow mx-auto flex items-center justify-center p-0.5 shrink-0">
                <img src="{{ asset('images/ABVHPS_LOGO.jpg') }}" class="w-full h-full object-cover rounded-full" alt="ABVHPS Logo">
            </div>
            <span class="bg-orange-500/15 text-orange-400 text-[10px] sm:text-[11px] font-black px-3.5 py-1 rounded-full uppercase tracking-widest inline-block border border-orange-500/30">
                ABVHPS Examination Desk
            </span>
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold uppercase tracking-wide text-white">
                Sanatana Dharma Examinations
            </h1>
            <p class="text-xs sm:text-sm text-gray-300 max-w-2xl mx-auto font-medium leading-relaxed">
                Official examination application and candidate information centre
            </p>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 space-y-6">
        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            
            {{-- Examination Context Header --}}
            <div class="bg-gray-800 text-white p-5 sm:p-6 border-b-2 border-brandOrange flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-center sm:text-left">
                <div>
                    <span class="text-[10px] font-bold text-orange-400 uppercase tracking-widest block mb-0.5">Active Examination Cycle</span>
                    <h2 class="text-lg sm:text-xl font-black text-white uppercase tracking-tight">{{ $examSettings->exam_title }}</h2>
                </div>
                <div>
                    @php
                        $examType = $examSettings->exam_type ?? null;
                    @endphp
                    @if($examType === 'theory')
                        <span class="bg-indigo-950 text-indigo-200 border border-indigo-500/40 px-3.5 py-1.5 rounded-full font-black text-xs uppercase tracking-wider inline-block">
                            📝 Theory Exam
                        </span>
                    @elseif($examType === 'mcq')
                        <span class="bg-purple-950 text-purple-200 border border-purple-500/40 px-3.5 py-1.5 rounded-full font-black text-xs uppercase tracking-wider inline-block">
                            📊 MCQ Exam
                        </span>
                    @elseif($examType === 'both')
                        <span class="bg-amber-950 text-amber-200 border border-amber-500/40 px-3.5 py-1.5 rounded-full font-black text-xs uppercase tracking-wider inline-block">
                            📑 Both (Theory + MCQ)
                        </span>
                    @endif
                </div>
            </div>

            @if(isset($availableExams) && count($availableExams) > 1)
                <!-- Multi-Exam Selection Strip -->
                <div class="bg-gray-900 text-white p-3 px-5 border-b border-gray-700 flex flex-wrap items-center justify-between gap-2 text-xs">
                    <span class="font-bold text-gray-300">🎯 Select Examination:</span>
                    <div class="flex flex-wrap gap-2">
                        @foreach($availableExams as $avExam)
                            <a href="{{ route('exam.form', ['exam_id' => $avExam->id]) }}" class="px-3 py-1 rounded-lg text-xs font-black uppercase transition {{ $examSettings->id == $avExam->id ? 'bg-brandOrange text-white shadow' : 'bg-gray-800 text-gray-300 hover:bg-gray-700' }}">
                                {{ $avExam->exam_title }} ({{ $avExam->exam_type_label ?? 'Exam' }})
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Prize Showcase Area (Dynamic per selected ExamSetting) -->
            @php
                $prizesList = $examSettings->prizes_list ?? [];
            @endphp
            @if(!empty($prizesList))
                <div class="bg-orange-50/50 p-5 border-b border-orange-200/80">
                    <h3 class="text-xs font-black uppercase text-orange-950 tracking-wider flex items-center gap-1.5 mb-3">
                        <span>🏆</span> Prizes &amp; Awards
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5">
                        @foreach($prizesList as $prize)
                            <div class="bg-white px-3.5 py-2.5 rounded-lg border border-orange-200 text-xs font-bold text-gray-800 shadow-xs flex items-center gap-2">
                                <span class="text-brandOrange">🏆</span>
                                <span class="truncate">{{ $prize }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Center Details Notification Dashboard -->
            <div class="bg-amber-50/60 px-6 py-3 border-b border-amber-200/60 text-xs font-bold text-gray-800 flex flex-wrap items-center justify-between gap-3">
                <div>📍 Center: <span class="text-gray-900 font-semibold">{{ $examSettings->exam_center_location }}</span></div>
                <div>📅 Date: <span class="text-gray-900 font-semibold">{{ date('d-M-Y h:i A', strtotime($examSettings->exam_date_time)) }}</span></div>
                <div>💰 Fee: <span class="font-mono text-orange-700 font-black">₹{{ number_format($examSettings->application_fee ?? 41.00, 2) }}</span></div>
                @if(!empty($examSettings->syllabus_pdf_path))
                    <div>
                        <a href="{{ route('exam.download_syllabus', ['id' => $examSettings->id]) }}" target="_blank" class="text-blue-700 hover:text-blue-900 underline flex items-center gap-1 font-bold">
                            <span>📥</span> Download Syllabus PDF
                        </a>
                    </div>
                @endif
            </div>

            <!-- Main Form Container Desk -->
            <div class="p-6 md:p-8">
        
        <!-- STAGE 1: GATEWAY SECURITY FOR EMAIL OTP VERIFICATION -->
        <div id="email_gate_section" class="bg-gray-50 p-6 rounded-lg border-2 border-dashed border-gray-300 mb-6">
            <h2 class="text-base font-bold text-gray-800 mb-1">Step 1: Secure Email Authentication Desk</h2>
            <p class="text-xs text-gray-600 mb-3">Please verify your email address to unlock the official registration form.</p>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 mb-1">Email Address *</label>
                    <input type="email" id="auth_email" class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:ring-2 focus:ring-orange-500 outline-none" placeholder="Enter student email address">
                </div>
                <div>
                    <button type="button" id="btn_send_otp" onclick="triggerEmailOtp()" class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-1.5 px-4 rounded text-sm transition shadow">Send Code</button>
                </div>
            </div>

            <div id="otp_input_wrapper" class="mt-4 pt-4 border-t border-gray-200 hidden">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 mb-1">6-Digit Verification Token *</label>
                        <input type="text" id="verification_otp" maxlength="6" class="w-full border border-gray-300 rounded px-3 py-1.5 text-center font-mono text-base tracking-widest focus:ring-2 focus:ring-orange-500 outline-none" placeholder="000000">
                    </div>
                    <div>
                        <button type="button" id="btn_verify_otp" onclick="validateEmailOtp()" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-1.5 px-4 rounded text-sm transition shadow">Verify Token</button>
                    </div>
                </div>
            </div>
            <div id="gate_message" class="text-xs font-semibold mt-2 hidden"></div>
        </div>

        <form id="main_exam_application_form" onsubmit="executeFinalSubmission(event)" enctype="multipart/form-data" class="hidden space-y-6">
            @csrf
            <!-- Dynamic Binding Parameters -->
            <input type="hidden" name="exam_setting_id" id="bound_exam_setting_id" value="{{ $examSettings->id ?? 1 }}">
            <input type="hidden" name="email" id="bound_verified_email">
            <input type="hidden" name="payment_transaction_id" id="bound_txn_id">

            <div class="border-b border-gray-200 pb-2">
                <h2 class="text-lg font-bold text-orange-800">Step 2: Candidate Registration Registry</h2>
                <p class="text-xs text-gray-500">All information fields listed below are strictly mandatory parameters.</p>
            </div>

            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Full Name (As per records) *</label>
                    <input type="text" name="full_name" required class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:ring-1 focus:ring-orange-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Date of Birth *</label>
                    <input type="date" name="dob" required class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:ring-1 focus:ring-orange-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Mobile Number (Active WhatsApp) *</label>
                    <input type="text" name="mobile" required class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:ring-1 focus:ring-orange-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Aadhaar Card Number</label>
                    <input type="text" name="aadhaar_no" maxlength="12" class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:ring-1 focus:ring-orange-500 outline-none" placeholder="0000 0000 0000">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Complete Residential Address *</label>
                <textarea name="address" rows="2" required class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:ring-1 focus:ring-orange-500 outline-none"></textarea>
            </div>

            <!-- Educational Profile Desk -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 mb-1">School / College Name *</label>
                    <input type="text" name="school_college_name" required class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:ring-1 focus:ring-orange-500 outline-none" placeholder="Enter institution name">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Class & Section</label>
                    <input type="text" name="class_section" class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:ring-1 focus:ring-orange-500 outline-none" placeholder="e.g. 10th Class - A">
                </div>
            </div>

            <!-- STAGE 3: MANDATORY PARENT / GUARDIAN MEMBERSHIP DESK -->
            <div class="bg-orange-50 p-4 rounded-lg border border-orange-200 space-y-3">
                <h3 class="text-sm font-bold text-orange-950 border-b border-orange-200 pb-1">Step 3: Family Affiliation & Membership Grid</h3>
                
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Select Family Support Structure *</label>
                    <div class="flex gap-4 text-xs">
                        <label class="inline-flex items-center font-semibold text-gray-800">
                            <input type="radio" name="guardian_type" value="parents" checked onchange="toggleFamilyDesk(this.value)" class="form-radio text-orange-600 focus:ring-orange-500 h-3 w-3">
                            <span class="ml-1.5">Living Parents (Father & Mother)</span>
                        </label>
                        <label class="inline-flex items-center font-semibold text-gray-800">
                            <input type="radio" name="guardian_type" value="guardian" onchange="toggleFamilyDesk(this.value)" class="form-radio text-orange-600 focus:ring-orange-500 h-3 w-3">
                            <span class="ml-1.5">Guardian (సంరక్షకుడు)</span>
                        </label>
                    </div>
                </div>

                <!-- SUB-GRID A: LIVING PARENTS MEMBERSHIP REGISTRY -->
                <div id="family_parents_grid" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div id="father_box" class="bg-white p-4 rounded-xl border border-gray-200 space-y-2 transition-all">
                        <div class="flex items-center justify-between">
                            <label class="block text-[10px] font-black uppercase text-gray-600">Father 12-Digit ABVHPS ID *</label>
                            <span id="father_status_badge" class="text-[9px] font-black uppercase px-2 py-0.5 rounded bg-gray-100 text-gray-500">Unverified</span>
                        </div>
                        <div class="flex gap-2">
                            <input type="text" id="father_id" name="father_membership_id" maxlength="12" oninput="resetMemberVerification('father')" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs font-mono font-bold text-gray-800 outline-none focus:ring-2 focus:ring-brandOrange" placeholder="e.g. 602505286340">
                            <button type="button" id="btn_verify_father" onclick="verifyMember('father')" class="bg-brandDarkGray hover:bg-black text-white text-[10px] font-black px-3 py-2 rounded-lg shadow uppercase transition whitespace-nowrap">
                                Verify
                            </button>
                        </div>
                        <input type="text" id="father_name" name="father_name" readonly class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-xs font-bold text-gray-800 outline-none" placeholder="Father Name (Auto-filled upon verification)">
                        <div id="father_feedback" class="text-[10px] font-semibold text-gray-400">
                            Enter 12-digit ABVHPS Member ID and click Verify.
                        </div>
                    </div>

                    <div id="mother_box" class="bg-white p-4 rounded-xl border border-gray-200 space-y-2 transition-all">
                        <div class="flex items-center justify-between">
                            <label class="block text-[10px] font-black uppercase text-gray-600">Mother 12-Digit ABVHPS ID *</label>
                            <span id="mother_status_badge" class="text-[9px] font-black uppercase px-2 py-0.5 rounded bg-gray-100 text-gray-500">Unverified</span>
                        </div>
                        <div class="flex gap-2">
                            <input type="text" id="mother_id" name="mother_membership_id" maxlength="12" oninput="resetMemberVerification('mother')" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs font-mono font-bold text-gray-800 outline-none focus:ring-2 focus:ring-brandOrange" placeholder="e.g. 602505286340">
                            <button type="button" id="btn_verify_mother" onclick="verifyMember('mother')" class="bg-brandDarkGray hover:bg-black text-white text-[10px] font-black px-3 py-2 rounded-lg shadow uppercase transition whitespace-nowrap">
                                Verify
                            </button>
                        </div>
                        <input type="text" id="mother_name" name="mother_name" readonly class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-xs font-bold text-gray-800 outline-none" placeholder="Mother Name (Auto-filled upon verification)">
                        <div id="mother_feedback" class="text-[10px] font-semibold text-gray-400">
                            Enter 12-digit ABVHPS Member ID and click Verify.
                        </div>
                    </div>
                </div>

                <!-- SUB-GRID B: INDEPENDENT GUARDIAN MATRICES (HIDDEN BY DEFAULT) -->
                <div id="family_guardian_grid" class="grid grid-cols-1 md:grid-cols-3 gap-4 hidden">
                    <div id="guardian_box" class="bg-white p-4 rounded-xl border border-gray-200 space-y-2 md:col-span-2 transition-all">
                        <div class="flex items-center justify-between">
                            <label class="block text-[10px] font-black uppercase text-gray-600">Guardian 12-Digit ABVHPS ID *</label>
                            <span id="guardian_status_badge" class="text-[9px] font-black uppercase px-2 py-0.5 rounded bg-gray-100 text-gray-500">Unverified</span>
                        </div>
                        <div class="flex gap-2">
                            <input type="text" id="guardian_id_or_phone" name="guardian_mobile_or_id" maxlength="12" oninput="resetMemberVerification('guardian')" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs font-mono font-bold text-gray-800 outline-none focus:ring-2 focus:ring-brandOrange" placeholder="12-Digit ABVHPS ID">
                            <button type="button" id="btn_verify_guardian" onclick="verifyMember('guardian')" class="bg-brandDarkGray hover:bg-black text-white text-[10px] font-black px-3 py-2 rounded-lg shadow uppercase transition whitespace-nowrap">
                                Verify
                            </button>
                        </div>
                        <input type="text" id="guardian_name" name="guardian_name" readonly class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-xs font-bold text-gray-800 outline-none" placeholder="Guardian Name (Auto-filled upon verification)">
                        <div id="guardian_feedback" class="text-[10px] font-semibold text-gray-400">
                            Enter 12-digit Guardian ID and click Verify.
                        </div>
                    </div>
                    <div class="bg-white p-4 rounded-xl border border-gray-200 space-y-2">
                        <label class="block text-[10px] font-black uppercase text-gray-600">Relationship to Candidate *</label>
                        <input type="text" id="guardian_relationship" name="guardian_relationship" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs font-bold text-gray-800 outline-none" placeholder="e.g. Grandfather, Uncle">
                    </div>
                </div>
            </div>

            <!-- STAGE 4: EVIDENCE UPLOADS PACKETS -->
            <div class="space-y-4">
                <div class="border-b border-gray-200 pb-2">
                    <h2 class="text-base font-bold text-gray-800">Step 4: Mandatory Document Evidence Uploads</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Passport Photo *</label>
                        <input type="file" name="photo" required class="w-full border border-gray-300 rounded text-xs p-1 bg-gray-50">
                        <p class="text-xxs text-gray-400 mt-1">Note: Optimized to 100x100 Stamp View (1KB-2KB).</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">School ID / HM Letter *</label>
                        <input type="file" name="id_card_or_signature" required class="w-full border border-gray-300 rounded text-xs p-1 bg-gray-50">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Aadhaar Card Copy</label>
                        <input type="file" name="aadhaar" class="w-full border border-gray-300 rounded text-xs p-1 bg-gray-50">
                    </div>
                </div>
            </div>

            <!-- STAGE 5: ANTI-FRAUD PAYMENT CHANNELS & TERMINAL DISPATCH ANCHOR -->
            <div class="bg-yellow-50 p-6 rounded-lg border border-yellow-300 space-y-4 text-center">
                <p class="text-sm font-bold text-orange-950">Registration Processing Fee: <span class="text-lg text-orange-600">₹41.00 INR</span></p>
                
                <div id="verification_warning_banner" class="hidden p-3 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 text-xs font-bold">
                    ⚠️ Mandatory Requirement: You must verify both parents' (or guardian's) ABVHPS IDs before proceeding to payment.
                </div>

                <div class="flex justify-center">
                    <button type="button" id="btn_gateway_pay" onclick="simulateGatewayPipeline()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg shadow transition transform hover:scale-102">
                        Pay ₹41.00 via Secured Payment Gateway
                    </button>
                </div>

                @include('partials.legal-consent', ['type' => 'exam'])
                <div id="final_submit_wrapper" class="hidden animate-bounce mt-2">
                    <p class="text-green-700 text-xs font-bold mb-2">🎉 Payment Captured Successfully! Terminal Submissions Unlocked.</p>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-extrabold text-base py-2.5 px-10 rounded-lg shadow-md transition transform hover:scale-102">
                        SUBMIT OFFICIAL APPLICATION & GENERATE HALL TICKET
                    </button>
                </div>
            </div>
        </form>
    </div>
        </div>
    </div>
</div>
<!-- CORE JAVASCRIPT PIPELINES -->
<script>
    // Setup central headers payload mapping security tokens
    const ajaxHeaders = {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    };

    let isFatherVerified = false;
    let isMotherVerified = false;
    let isGuardianVerified = false;

    function toggleFamilyDesk(targetValue) {
        const pGrid = document.getElementById('family_parents_grid');
        const gGrid = document.getElementById('family_guardian_grid');

        if (targetValue === 'parents') {
            pGrid.classList.remove('hidden');
            gGrid.classList.add('hidden');
            document.getElementById('guardian_name').required = false;
            document.getElementById('guardian_relationship').required = false;
        } else {
            pGrid.classList.add('hidden');
            gGrid.classList.remove('hidden');
            document.getElementById('guardian_name').required = true;
            document.getElementById('guardian_relationship').required = true;
        }
        checkVerificationGateState();
    }

    function resetMemberVerification(nodeRole) {
        if (nodeRole === 'father') {
            isFatherVerified = false;
            document.getElementById('father_name').value = '';
            document.getElementById('father_box').className = "bg-white p-4 rounded-xl border border-gray-200 space-y-2 transition-all";
            document.getElementById('father_status_badge').className = "text-[9px] font-black uppercase px-2 py-0.5 rounded bg-gray-100 text-gray-500";
            document.getElementById('father_status_badge').innerText = "Unverified";
            document.getElementById('father_feedback').className = "text-[10px] font-semibold text-gray-400";
            document.getElementById('father_feedback').innerText = "ID changed. Please click Verify.";
        } else if (nodeRole === 'mother') {
            isMotherVerified = false;
            document.getElementById('mother_name').value = '';
            document.getElementById('mother_box').className = "bg-white p-4 rounded-xl border border-gray-200 space-y-2 transition-all";
            document.getElementById('mother_status_badge').className = "text-[9px] font-black uppercase px-2 py-0.5 rounded bg-gray-100 text-gray-500";
            document.getElementById('mother_status_badge').innerText = "Unverified";
            document.getElementById('mother_feedback').className = "text-[10px] font-semibold text-gray-400";
            document.getElementById('mother_feedback').innerText = "ID changed. Please click Verify.";
        } else if (nodeRole === 'guardian') {
            isGuardianVerified = false;
            document.getElementById('guardian_name').value = '';
            document.getElementById('guardian_box').className = "bg-white p-4 rounded-xl border border-gray-200 space-y-2 md:col-span-2 transition-all";
            document.getElementById('guardian_status_badge').className = "text-[9px] font-black uppercase px-2 py-0.5 rounded bg-gray-100 text-gray-500";
            document.getElementById('guardian_status_badge').innerText = "Unverified";
            document.getElementById('guardian_feedback').className = "text-[10px] font-semibold text-gray-400";
            document.getElementById('guardian_feedback').innerText = "ID changed. Please click Verify.";
        }
        checkVerificationGateState();
    }

    function checkVerificationGateState() {
        const guardianType = document.querySelector('input[name="guardian_type"]:checked').value;
        const warningBanner = document.getElementById('verification_warning_banner');

        if (guardianType === 'parents') {
            if (isFatherVerified && isMotherVerified) {
                warningBanner.classList.add('hidden');
                return true;
            } else {
                return false;
            }
        } else {
            if (isGuardianVerified) {
                warningBanner.classList.add('hidden');
                return true;
            } else {
                return false;
            }
        }
    }

    async function triggerEmailOtp() {
        const email = document.getElementById('auth_email').value;
        const gateMsg = document.getElementById('gate_message');
        
        if (!email) {
            alert('Please enter a valid email address.');
            return;
        }

        gateMsg.className = "text-xs font-semibold mt-2 text-blue-600 block";
        gateMsg.innerText = "Sending 6-digit verification code to your email...";

        try {
            let response = await fetch("{{ route('exam.send_otp') }}", {
                method: 'POST',
                headers: ajaxHeaders,
                body: JSON.stringify({ email: email })
            });
            let result = await response.json();

            if (result.success) {
                gateMsg.className = "text-xs font-semibold mt-2 text-green-600 block";
                gateMsg.innerText = result.message;
                document.getElementById('otp_input_wrapper').classList.remove('hidden');
            } else {
                gateMsg.className = "text-xs font-semibold mt-2 text-red-600 block";
                gateMsg.innerText = result.message || 'Unable to send verification OTP.';
            }
        } catch (error) {
            console.error("OTP Send Error:", error);
            gateMsg.className = "text-xs font-semibold mt-2 text-red-600 block";
            gateMsg.innerText = "Unable to connect to the server. Please check your internet connection.";
        }
    }

    async function validateEmailOtp() {
        const otp = document.getElementById('verification_otp').value;
        const gateMsg = document.getElementById('gate_message');

        if (!otp) {
            alert('Please enter the 6-digit verification code.');
            return;
        }

        try {
            let response = await fetch("{{ route('exam.verify_otp') }}", {
                method: 'POST',
                headers: ajaxHeaders,
                body: JSON.stringify({ otp: otp })
            });
            let result = await response.json();

            if (result.success) {
                gateMsg.className = "text-xs font-semibold mt-2 text-green-700 block";
                gateMsg.innerText = result.message;
                
                // Bind values and unroll registration workspace
                document.getElementById('bound_verified_email').value = result.email;
                document.getElementById('auth_email').disabled = true;
                document.getElementById('btn_send_otp').disabled = true;
                document.getElementById('otp_input_wrapper').classList.add('hidden');
                
                document.getElementById('main_exam_application_form').classList.remove('hidden');
            } else {
                gateMsg.className = "text-xs font-semibold mt-2 text-red-600 block";
                gateMsg.innerText = result.message || 'Invalid or expired OTP.';
            }
        } catch (error) {
            console.error("OTP Verification Error:", error);
            gateMsg.className = "text-xs font-semibold mt-2 text-red-600 block";
            gateMsg.innerText = "Unable to verify code. Please try again.";
        }
    }

    async function verifyMember(nodeRole) {
        let elementId = nodeRole === 'father' ? 'father_id' : (nodeRole === 'mother' ? 'mother_id' : 'guardian_id_or_phone');
        let inputVal = document.getElementById(elementId).value;

        if (!inputVal || inputVal.length < 6) {
            alert('Please enter a valid 6 to 12 digit ABVHPS Member ID.');
            return;
        }

        const btn = document.getElementById('btn_verify_' + nodeRole);
        btn.disabled = true;
        btn.innerText = '...';

        try {
            let response = await fetch("{{ route('exam.check_membership') }}", {
                method: 'POST',
                headers: ajaxHeaders,
                body: JSON.stringify({ membership_id: inputVal })
            });
            let result = await response.json();

            if (result.status === 'valid') {
                if (nodeRole === 'father') {
                    isFatherVerified = true;
                    document.getElementById('father_name').value = result.name;
                    document.getElementById('father_box').className = "bg-emerald-50/40 p-4 rounded-xl border-2 border-emerald-500 space-y-2 transition-all";
                    document.getElementById('father_status_badge').className = "text-[9px] font-black uppercase px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 border border-emerald-300";
                    document.getElementById('father_status_badge').innerText = "✓ Verified";
                    document.getElementById('father_feedback').className = "text-[10px] font-bold text-emerald-700";
                    document.getElementById('father_feedback').innerText = "✓ Identity Confirmed: " + result.name;
                } else if (nodeRole === 'mother') {
                    isMotherVerified = true;
                    document.getElementById('mother_name').value = result.name;
                    document.getElementById('mother_box').className = "bg-emerald-50/40 p-4 rounded-xl border-2 border-emerald-500 space-y-2 transition-all";
                    document.getElementById('mother_status_badge').className = "text-[9px] font-black uppercase px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 border border-emerald-300";
                    document.getElementById('mother_status_badge').innerText = "✓ Verified";
                    document.getElementById('mother_feedback').className = "text-[10px] font-bold text-emerald-700";
                    document.getElementById('mother_feedback').innerText = "✓ Identity Confirmed: " + result.name;
                } else if (nodeRole === 'guardian') {
                    isGuardianVerified = true;
                    document.getElementById('guardian_name').value = result.name;
                    document.getElementById('guardian_box').className = "bg-emerald-50/40 p-4 rounded-xl border-2 border-emerald-500 space-y-2 md:col-span-2 transition-all";
                    document.getElementById('guardian_status_badge').className = "text-[9px] font-black uppercase px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 border border-emerald-300";
                    document.getElementById('guardian_status_badge').innerText = "✓ Verified";
                    document.getElementById('guardian_feedback').className = "text-[10px] font-bold text-emerald-700";
                    document.getElementById('guardian_feedback').innerText = "✓ Identity Confirmed: " + result.name;
                }
            } else {
                if (nodeRole === 'father') {
                    isFatherVerified = false;
                    document.getElementById('father_name').value = '';
                    document.getElementById('father_box').className = "bg-rose-50/40 p-4 rounded-xl border-2 border-rose-400 space-y-2 transition-all";
                    document.getElementById('father_status_badge').className = "text-[9px] font-black uppercase px-2 py-0.5 rounded bg-rose-100 text-rose-800 border border-rose-300";
                    document.getElementById('father_status_badge').innerText = "✗ Not Found";
                    document.getElementById('father_feedback').className = "text-[10px] font-bold text-rose-600";
                    document.getElementById('father_feedback').innerText = "⚠️ " + (result.message || 'ID not found — not a registered ABVHPS member');
                } else if (nodeRole === 'mother') {
                    isMotherVerified = false;
                    document.getElementById('mother_name').value = '';
                    document.getElementById('mother_box').className = "bg-rose-50/40 p-4 rounded-xl border-2 border-rose-400 space-y-2 transition-all";
                    document.getElementById('mother_status_badge').className = "text-[9px] font-black uppercase px-2 py-0.5 rounded bg-rose-100 text-rose-800 border border-rose-300";
                    document.getElementById('mother_status_badge').innerText = "✗ Not Found";
                    document.getElementById('mother_feedback').className = "text-[10px] font-bold text-rose-600";
                    document.getElementById('mother_feedback').innerText = "⚠️ " + (result.message || 'ID not found — not a registered ABVHPS member');
                } else if (nodeRole === 'guardian') {
                    isGuardianVerified = false;
                    document.getElementById('guardian_name').value = '';
                    document.getElementById('guardian_box').className = "bg-rose-50/40 p-4 rounded-xl border-2 border-rose-400 space-y-2 md:col-span-2 transition-all";
                    document.getElementById('guardian_status_badge').className = "text-[9px] font-black uppercase px-2 py-0.5 rounded bg-rose-100 text-rose-800 border border-rose-300";
                    document.getElementById('guardian_status_badge').innerText = "✗ Not Found";
                    document.getElementById('guardian_feedback').className = "text-[10px] font-bold text-rose-600";
                    document.getElementById('guardian_feedback').innerText = "⚠️ " + (result.message || 'ID not found — not a registered ABVHPS member');
                }
            }
        } catch (error) {
            console.error("Member Verification Error:", error);
            alert('Unable to connect to the membership verification registry. Please try again.');
        } finally {
            btn.disabled = false;
            btn.innerText = 'Verify';
            checkVerificationGateState();
        }
    }

    async function simulateGatewayPipeline() {
        const guardianType = document.querySelector('input[name="guardian_type"]:checked').value;
        const warningBanner = document.getElementById('verification_warning_banner');

        if (guardianType === 'parents') {
            if (!isFatherVerified || !isMotherVerified) {
                warningBanner.classList.remove('hidden');
                warningBanner.innerText = "⚠️ Mandatory Requirement: BOTH Father and Mother ABVHPS IDs must be successfully verified before proceeding to payment.";
                warningBanner.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
        } else {
            if (!isGuardianVerified) {
                warningBanner.classList.remove('hidden');
                warningBanner.innerText = "⚠️ Mandatory Requirement: Guardian ABVHPS ID must be successfully verified before proceeding to payment.";
                warningBanner.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
        }

        warningBanner.classList.add('hidden');

        try {
            let response = await fetch("{{ route('exam.process_payment') }}", {
                method: 'POST',
                headers: ajaxHeaders,
                body: JSON.stringify({
                    execution_flag: true,
                    guardian_type: guardianType,
                    father_membership_id: document.getElementById('father_id').value,
                    mother_membership_id: document.getElementById('mother_id').value,
                    guardian_mobile_or_id: document.getElementById('guardian_id_or_phone').value
                })
            });
            let result = await response.json();

            if (result.success) {
                document.getElementById('bound_txn_id').value = result.transaction_id;
                document.getElementById('btn_gateway_pay').classList.add('hidden');
                document.getElementById('final_submit_wrapper').classList.remove('hidden');
            } else {
                warningBanner.classList.remove('hidden');
                warningBanner.innerText = "⚠️ " + (result.message || 'Payment processing failed.');
            }
        } catch (error) {
            console.error("Payment Processing Error:", error);
            alert('Payment processing failed. Please try again.');
        }
    }

    async function executeFinalSubmission(event) {
        event.preventDefault();

        const guardianType = document.querySelector('input[name="guardian_type"]:checked').value;
        if (guardianType === 'parents') {
            if (!isFatherVerified || !isMotherVerified) {
                alert('Mandatory Verification Gate: Both Father and Mother ABVHPS IDs must be verified registered members before submitting.');
                return;
            }
        } else {
            if (!isGuardianVerified) {
                alert('Mandatory Verification Gate: Guardian ABVHPS ID must be verified before submitting.');
                return;
            }
        }

        const formElement = document.getElementById('main_exam_application_form');
        const dataPacket = new FormData(formElement);
        const submitBtn = formElement.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerText = "Submitting Application...";
        }

        try {
            let response = await fetch("{{ route('exam.submit') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: dataPacket
            });

            let result = null;
            try {
                result = await response.json();
            } catch (parseErr) {
                console.error("JSON parsing error on response:", parseErr);
            }

            if (response.ok && result && result.success) {
                alert(result.message || 'Application submitted successfully!');
                window.location.href = result.redirect_url;
            } else {
                let errorMsg = 'Something went wrong submitting your application. Please check your details and try again.';
                if (result) {
                    if (result.message) {
                        errorMsg = result.message;
                    }
                    if (result.errors) {
                        const errorList = Object.values(result.errors).flat().join('\n• ');
                        errorMsg += '\n\n• ' + errorList;
                    }
                }
                alert(errorMsg);
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerText = "SUBMIT OFFICIAL APPLICATION & GENERATE HALL TICKET";
                }
            }
        } catch (error) {
            console.error("Application Submission Exception:", error);
            alert("Network connection issue. Please verify your connection and try again.");
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerText = "SUBMIT OFFICIAL APPLICATION & GENERATE HALL TICKET";
            }
        }
    }
</script>
@endif
@endsection
