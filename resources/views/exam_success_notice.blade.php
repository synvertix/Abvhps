<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Confirmation & Hall Ticket | ABVHPS</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style type="text/tailwindcss">
        @theme {
            --color-brandOrange: #E65100;
            --color-brandDark: #1A1A1A;
            --color-brandGreen: #15803D;
        }
        @media print {
            body { background: white !important; padding: 0 !important; }
            body * { visibility: hidden; }
            #printable_hall_ticket, #printable_hall_ticket * { visibility: visible; }
            #printable_hall_ticket { 
                position: absolute; 
                left: 0; 
                top: 0; 
                width: 100%; 
                border: 2px solid #E65100 !important; 
                box-shadow: none !important;
                border-radius: 0 !important;
            }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="bg-slate-50 text-gray-800 font-sans antialiased min-h-screen py-8 sm:py-12 px-4 sm:px-6 lg:px-8">

    <div class="max-w-5xl mx-auto space-y-6">

        {{-- ======================================================== --}}
        {{-- SECTION 1: SUCCESS CONFIRMATION HEADER (NO-PRINT)        --}}
        {{-- ======================================================== --}}
        <div class="no-print bg-white border border-gray-200/90 rounded-2xl p-6 sm:p-8 shadow-xs text-center relative overflow-hidden">
            
            <!-- Circular ABVHPS Logo -->
            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full overflow-hidden bg-white border-2 border-brandOrange shadow-sm mx-auto mb-3 flex items-center justify-center p-0.5 shrink-0">
                <img src="{{ asset('images/ABVHPS_LOGO.jpg') }}" class="w-full h-full object-cover rounded-full" alt="ABVHPS Logo">
            </div>

            <div class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 text-sm font-bold mb-2 border border-emerald-200">
                ✓
            </div>
            
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-gray-900 uppercase tracking-tight">
                Application Submitted Successfully
            </h1>
            <p class="text-xs sm:text-sm text-gray-600 font-medium max-w-xl mx-auto mt-2">
                Your application for <span class="font-bold text-gray-900">{{ $examSettings->exam_title ?? 'Sanathana Dharma Examination' }}</span> has been successfully recorded in the central database registry.
            </p>
            <div class="mt-3 inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-semibold px-3 py-1 rounded-full">
                <span>✓</span>
                <span>Application Secured & Verified!</span>
            </div>
        </div>

        {{-- ======================================================== --}}
        {{-- SECTION 2 & 3: OFFICIAL HALL TICKET NUMBER + REMINDER    --}}
        {{-- ======================================================== --}}
        <div class="no-print grid grid-cols-1 md:grid-cols-3 gap-6 items-stretch">
            
            <!-- Hall Ticket Number Card (2 cols on md+) -->
            <div class="md:col-span-2 bg-white border-2 border-orange-200/90 rounded-2xl p-6 sm:p-7 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                        <span class="text-orange-600">🎟️</span>
                        <span>Your Official Hall Ticket Number</span>
                    </div>

                    <!-- Clean 32px-42px Hall Ticket Typography -->
                    <div class="font-mono font-bold text-3xl sm:text-4xl text-gray-900 tracking-wider my-2.5 select-all break-all">
                        {{ $application->hall_ticket_number ?? 'APP-' . $application->id }}
                    </div>

                    <div class="flex flex-wrap items-center gap-3 text-xs font-semibold text-emerald-700 pt-1">
                        <span class="inline-flex items-center gap-1">✓ Unique</span>
                        <span class="text-gray-300">·</span>
                        <span class="inline-flex items-center gap-1">✓ Verified</span>
                        <span class="text-gray-300">·</span>
                        <span class="inline-flex items-center gap-1">✓ Permanent</span>
                    </div>
                </div>

                <p class="text-xs text-gray-500 font-medium mt-4 pt-3 border-t border-gray-100">
                    Please keep this Hall Ticket Number safe. You will need it to check your examination results.
                </p>
            </div>

            <!-- Important Reminder Card (1 col) -->
            <div class="bg-amber-50/70 border border-amber-200/90 rounded-2xl p-6 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="text-[11px] font-bold text-amber-900 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                        <span>⚠️</span>
                        <span>Important Notice</span>
                    </div>
                    <p class="text-xs text-amber-900 font-medium leading-relaxed">
                        Please keep your Hall Ticket Number safe. You will need it for examination results and other examination-related activities.
                    </p>
                </div>

                <div class="mt-4 pt-3 border-t border-amber-200/60 text-[11px] text-amber-800 font-semibold flex items-center gap-1">
                    <span>🔒</span>
                    <span>Official Candidate Identification</span>
                </div>
            </div>
        </div>

        {{-- ======================================================== --}}
        {{-- SECTION 4: APPLICATION SUMMARY (NO INTERNAL ID EXPOSED)  --}}
        {{-- ======================================================== --}}
        <div class="no-print bg-white border border-gray-200/80 rounded-2xl p-6 shadow-xs">
            <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100">
                Application Summary
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4 text-xs">
                <div>
                    <span class="text-[10px] uppercase font-bold text-gray-400 block">Exam Applied</span>
                    <span class="font-bold text-gray-900 truncate block">{{ $examSettings->exam_title ?? 'Sanathana Dharma Exam' }}</span>
                </div>

                <div>
                    <span class="text-[10px] uppercase font-bold text-gray-400 block">Exam Type</span>
                    <span class="font-semibold text-gray-800">
                        {{ $examSettings->exam_type_label ?? 'Standard' }}
                    </span>
                </div>

                <div>
                    <span class="text-[10px] uppercase font-bold text-gray-400 block">Hall Ticket Number</span>
                    <span class="font-mono font-bold text-orange-700 text-sm">{{ $application->hall_ticket_number ?? 'N/A' }}</span>
                </div>

                <div>
                    <span class="text-[10px] uppercase font-bold text-gray-400 block">Status</span>
                    <span class="font-bold text-emerald-700 uppercase">
                        ✓ {{ $application->payment_status ?? 'Success' }}
                    </span>
                </div>

                <div>
                    <span class="text-[10px] uppercase font-bold text-gray-400 block">Submitted On</span>
                    <span class="font-semibold text-gray-800">
                        {{ $application->created_at ? date('d-M-Y', strtotime($application->created_at)) : date('d-M-Y') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- ======================================================== --}}
        {{-- SECTION 5: OFFICIAL PRINTABLE DIGITAL HALL TICKET CARD   --}}
        {{-- ======================================================== --}}
        <div id="printable_hall_ticket" class="bg-white border-2 border-orange-500 rounded-2xl shadow-md overflow-hidden relative">
            
            <!-- Header Ribbon -->
            <div class="bg-gradient-to-r from-orange-600 via-orange-500 to-amber-600 p-5 text-white text-center border-b-4 border-amber-400">
                
                <!-- Circular Logo in Ribbon -->
                <div class="w-14 h-14 rounded-full overflow-hidden bg-white border-2 border-amber-300 shadow-xs mx-auto mb-2 flex items-center justify-center p-0.5">
                    <img src="{{ asset('images/ABVHPS_LOGO.jpg') }}" class="w-full h-full object-cover rounded-full" alt="ABVHPS Logo">
                </div>

                <h2 class="text-base sm:text-lg font-black uppercase tracking-wider">
                    AKHANDA BHARATHA VISWA HINDU PARIRAKSHANA SAMITI
                </h2>
                <p class="text-amber-100 text-xs font-bold uppercase tracking-widest mt-0.5">
                    Official Examination Hall Ticket &amp; Admit Card
                </p>
                <div class="inline-block mt-2 bg-amber-400 text-gray-900 text-[10px] font-black px-3 py-0.5 rounded-full uppercase tracking-wider shadow-xs">
                    {{ $examSettings->exam_title ?? 'Sanathana Dharma Exam' }}
                </div>
            </div>

            <!-- Main Admit Card Grid -->
            <div class="p-6 sm:p-8 space-y-6">
                
                <!-- Candidate Identity Block -->
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-6 items-center pb-6 border-b border-gray-200">
                    <!-- Photo Stamp Frame -->
                    <div class="sm:col-span-1 flex flex-col items-center justify-center">
                        @if(!empty($application->photo_path))
                            <img src="{{ asset('storage/' . $application->photo_path) }}" class="w-32 h-32 object-cover rounded-xl border-2 border-orange-400 shadow-sm bg-white" alt="Candidate Photo">
                        @else
                            <div class="w-32 h-32 bg-gray-100 rounded-xl border-2 border-dashed border-gray-300 flex items-center justify-center text-gray-400 text-xs font-bold text-center p-2">
                                Stamp Photo View
                            </div>
                        @endif
                        <span class="text-[9px] text-gray-400 font-bold uppercase tracking-widest mt-1.5">Official Photo</span>
                    </div>

                    <!-- Candidate Credentials -->
                    <div class="sm:col-span-3 space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-2 pb-2 border-b border-gray-100">
                            <div>
                                <span class="text-[10px] uppercase font-black text-gray-400 tracking-wider block">Candidate Full Name</span>
                                <span class="text-lg font-black text-gray-900 uppercase">{{ $application->full_name }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] uppercase font-black text-orange-600 tracking-wider block">Official Hall Ticket Number</span>
                                <span class="font-mono font-black text-orange-700 tracking-widest text-base sm:text-xl bg-orange-50 border border-orange-200 px-3 py-1 rounded-lg inline-block shadow-xs">
                                    {{ $application->hall_ticket_number ?? 'N/A' }}
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs pt-1">
                            <div>
                                <span class="text-[10px] uppercase font-bold text-gray-400 block">Date of Birth</span>
                                <span class="font-bold text-gray-800">{{ $application->dob ? date('d-M-Y', strtotime($application->dob)) : 'N/A' }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] uppercase font-bold text-gray-400 block">Mobile Number</span>
                                <span class="font-mono font-bold text-gray-800">
                                    @if(!empty($application->mobile))
                                        {{ substr($application->mobile, 0, 2) . '******' . substr($application->mobile, -2) }}
                                    @else
                                        N/A
                                    @endif
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs pt-2 border-t border-gray-100">
                            <div>
                                <span class="text-[10px] uppercase font-bold text-gray-400 block">School / Institution</span>
                                <span class="font-medium text-gray-800">{{ $application->school_college_name ?? 'N/A' }} ({{ $application->class_section ?? 'N/A' }})</span>
                            </div>
                            <div>
                                <span class="text-[10px] uppercase font-bold text-gray-400 block">Parent / Guardian ABVHPS ID</span>
                                <span class="font-mono font-bold text-gray-800">
                                    @if($application->guardian_type === 'parents')
                                        {{ $application->father_membership_id ?? $application->father_name ?? 'Verified Parent' }}
                                    @else
                                        {{ $application->guardian_mobile_or_id ?? $application->guardian_name ?? 'Verified Guardian' }}
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Examination Spec Details Block -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 bg-orange-50/60 p-4 rounded-xl border border-orange-200/80 text-xs">
                    <div>
                        <span class="text-[10px] uppercase font-black text-gray-500 block">Examination Title</span>
                        <span class="font-bold text-gray-900 block mt-0.5">{{ $examSettings->exam_title ?? 'Sanathana Dharma Exam' }}</span>
                    </div>

                    <div>
                        <span class="text-[10px] uppercase font-black text-gray-500 block">Exam Format / Type</span>
                        <div class="mt-0.5">
                            @php
                                $examType = $examSettings->exam_type ?? null;
                            @endphp
                            @if($examType === 'mcq')
                                <span class="bg-purple-100 text-purple-800 border border-purple-200 text-[10px] font-black px-2 py-0.5 rounded uppercase">
                                    📊 MCQ
                                </span>
                            @elseif($examType === 'theory')
                                <span class="bg-indigo-100 text-indigo-800 border border-indigo-200 text-[10px] font-black px-2 py-0.5 rounded uppercase">
                                    📝 Theory
                                </span>
                            @elseif($examType === 'both')
                                <span class="bg-amber-100 text-amber-900 border border-amber-300 text-[10px] font-black px-2 py-0.5 rounded uppercase">
                                    📑 Both (Theory + MCQ)
                                </span>
                            @else
                                <span class="bg-gray-100 text-gray-700 border border-gray-200 text-[10px] font-black px-2 py-0.5 rounded uppercase">
                                    Standard Examination
                                </span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <span class="text-[10px] uppercase font-black text-gray-500 block">Fee Paid</span>
                        <span class="font-mono font-black text-emerald-700 block mt-0.5">₹{{ number_format($application->amount_paid ?? 41.00, 2) }} (Confirmed)</span>
                    </div>

                    <div>
                        <span class="text-[10px] uppercase font-black text-gray-500 block">Exam Center Location</span>
                        <span class="font-bold text-gray-900 block mt-0.5">📍 {{ $examSettings->exam_center_location ?? 'Main Center, Porumamilla' }}</span>
                    </div>

                    <div>
                        <span class="text-[10px] uppercase font-black text-gray-500 block">Exam Date &amp; Time</span>
                        <span class="font-bold text-gray-900 block mt-0.5">📅 {{ isset($examSettings->exam_date_time) ? date('d-M-Y h:i A', strtotime($examSettings->exam_date_time)) : '12-Oct-2026' }}</span>
                    </div>

                    <div>
                        <span class="text-[10px] uppercase font-black text-gray-500 block">Registration Timestamp</span>
                        <span class="font-mono text-gray-700 block mt-0.5">{{ $application->created_at ? date('d-M-Y h:i A', strtotime($application->created_at)) : date('d-M-Y') }}</span>
                    </div>
                </div>

                <!-- SECTION 6: PRIZES & AWARDS (DYNAMIC) -->
                @php
                    $prizes = $examSettings->prizes_list ?? [];
                @endphp
                @if(!empty($prizes))
                    <div class="bg-gradient-to-r from-orange-50 to-amber-50 p-4 rounded-xl border border-orange-200 text-xs">
                        <span class="text-[10px] font-black text-orange-950 uppercase tracking-wider block mb-2">🏆 Prizes &amp; Awards Matrix:</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                            @foreach($prizes as $prize)
                                <div class="bg-white px-2.5 py-1.5 rounded-lg border border-orange-200 text-[11px] font-bold text-gray-800 shadow-xs flex items-center gap-1.5">
                                    <span class="text-orange-600">🏆</span>
                                    <span class="truncate">{{ $prize }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Candidate Instructions Block -->
                <div class="bg-gray-50 p-3.5 rounded-xl border border-gray-200 text-xs text-gray-700 space-y-1.5">
                    <div class="font-black text-gray-900 uppercase text-[11px] flex items-center gap-1.5">
                        <span>📌</span> IMPORTANT CANDIDATE INSTRUCTIONS
                    </div>
                    <ul class="list-disc pl-4 space-y-1 text-[11px]">
                        <li>Candidates must carry this printed Hall Ticket along with their original School/College ID card or valid identification proof to the exam hall.</li>
                        <li>Candidates should report to the examination center at least 30 minutes before the scheduled examination time.</li>
                        <li>Candidates must follow all instructions given by the examination staff and invigilators throughout the examination session.</li>
                        @if(!empty($examSettings->guidelines))
                            <li>{{ $examSettings->guidelines }}</li>
                        @endif
                    </ul>
                </div>

                <!-- Examination Restrictions Block -->
                <div class="bg-amber-50/70 p-3.5 rounded-xl border-2 border-amber-300 text-xs text-gray-800 space-y-1.5">
                    <div class="font-black text-amber-950 uppercase text-[11px] flex items-center gap-1.5">
                        <span>⚠️</span> IMPORTANT EXAMINATION RESTRICTIONS
                    </div>
                    <ul class="list-disc pl-4 space-y-1 text-[11px] text-amber-950 font-medium">
                        <li><strong>Mobile phones</strong> are strictly prohibited inside the examination hall.</li>
                        <li><strong>Smart watches</strong>, smart bands, Bluetooth devices, and other wearable electronic gadgets are not permitted.</li>
                        <li><strong>Earphones</strong>, headphones, wireless earbuds, and other audio devices are strictly prohibited.</li>
                        <li><strong>Tablets</strong>, laptops, cameras, and other electronic devices are not permitted inside the examination hall.</li>
                        <li><strong>Calculators</strong> are not permitted unless specifically authorized for the particular examination.</li>
                        <li>Candidates must not carry unauthorized electronic or communication devices into the examination hall.</li>
                        <li>Any prohibited electronic device found with a candidate may result in removal from the examination process, subject to applicable examination rules.</li>
                    </ul>
                </div>
            </div>

            <!-- Footer Bar -->
            <div class="bg-gray-900 text-gray-400 px-6 py-3 border-t border-gray-800 text-[10px] flex items-center justify-between">
                <span>🔒 Verification Token: {{ $application->payment_transaction_id ?? 'TXN-SECURED' }}</span>
                <span class="font-mono">ABVHPS CENTRAL EXAM DESK</span>
            </div>
        </div>

        {{-- ======================================================== --}}
        {{-- SECTION 7: QUICK ACTIONS (NO-PRINT)                      --}}
        {{-- ======================================================== --}}
        <div class="no-print bg-white border border-gray-200/80 rounded-2xl p-6 shadow-xs space-y-4">
            <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                Quick Actions
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                <!-- Print / Download Hall Ticket -->
                <button onclick="window.print()" class="bg-gradient-to-r from-orange-600 to-amber-600 hover:from-orange-700 hover:to-amber-700 text-white font-bold text-xs p-4 rounded-xl shadow-xs transition duration-150 flex flex-col items-center text-center justify-center gap-1.5 cursor-pointer">
                    <span class="text-lg">🖨️</span>
                    <span class="font-extrabold uppercase tracking-wide">Print / Download Admit Card</span>
                    <span class="text-[10px] text-amber-100 font-normal">Official printable hall ticket</span>
                </button>
                
                @if(!empty($examSettings->syllabus_pdf_path))
                    <!-- Syllabus Download Link -->
                    <a href="{{ route('exam.download_syllabus', ['id' => $application->id]) }}" class="bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs p-4 rounded-xl shadow-xs transition duration-150 flex flex-col items-center text-center justify-center gap-1.5">
                        <span class="text-lg">📚</span>
                        <span class="font-extrabold uppercase tracking-wide">Download Syllabus PDF</span>
                        <span class="text-[10px] text-slate-300 font-normal">Exam curriculum document</span>
                    </a>
                @endif

                <!-- Return Home -->
                <a href="/" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold text-xs p-4 rounded-xl border border-gray-200 transition duration-150 flex flex-col items-center text-center justify-center gap-1.5">
                    <span class="text-lg">🏛️</span>
                    <span class="font-extrabold uppercase tracking-wide">Home Portal</span>
                    <span class="text-[10px] text-gray-500 font-normal">Return to official website</span>
                </a>
            </div>
        </div>

        {{-- ======================================================== --}}
        {{-- SECTION 8: NOTIFICATION INFORMATION (NO-PRINT)           --}}
        {{-- ======================================================== --}}
        <div class="no-print bg-blue-50/70 border border-blue-200/80 rounded-xl p-4 text-xs text-blue-900 flex items-center gap-3">
            <span class="text-lg">ℹ️</span>
            <span>You will receive your examination details and updates through the available notification channels.</span>
        </div>

        {{-- ======================================================== --}}
        {{-- SECTION 9: THANK YOU & INSTITUTIONAL FOOTER (NO-PRINT)   --}}
        {{-- ======================================================== --}}
        <div class="no-print text-center py-6 space-y-1.5 text-xs text-gray-500">
            <!-- Circular ABVHPS Logo in Footer -->
            <div class="w-12 h-12 rounded-full overflow-hidden bg-white border border-gray-200 shadow-xs mx-auto mb-2 flex items-center justify-center p-0.5">
                <img src="{{ asset('images/ABVHPS_LOGO.jpg') }}" class="w-full h-full object-cover rounded-full" alt="ABVHPS Logo">
            </div>
            
            <div class="font-bold text-gray-800 text-sm">Thank You!</div>
            <div>We wish you all the best for your examination.</div>
            <div class="font-mono text-[10px] text-gray-400 font-semibold tracking-wider uppercase pt-1">
                ABVHPS CENTRAL EXAM DESK
            </div>
        </div>

    </div>

</body>
</html>
