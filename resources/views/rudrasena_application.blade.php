@extends('layouts.app')

@section('title', 'Rudra Sena Dal Sacred Registration | ABVHPS')
@section('meta_description', 'Register for Rudra Sena, the youth volunteer brigade dedicated to the protection of Hindu temples, cultural traditions, and dharma.')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-gray-900 via-gray-800 to-gray-900 py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        
        <!-- HERO / HEADER SECTION -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-gray-950 via-gray-900 to-gray-950 border border-amber-500/30 shadow-2xl p-6 sm:p-10 mb-8 text-center">
            <!-- Background Decorative Trident Glow -->
            <div class="absolute -top-10 left-1/2 -translate-x-1/2 w-64 h-64 bg-orange-600/10 rounded-full blur-3xl pointer-events-none"></div>
            
            <div class="relative z-10">
                <div class="w-16 h-16 rounded-full overflow-hidden bg-white border-2 border-amber-400 shadow-lg mx-auto mb-4 flex items-center justify-center p-0.5">
                    <img src="{{ asset('images/ABVHPS_LOGO.jpg') }}" class="w-full h-full object-cover rounded-full" alt="ABVHPS Logo">
                </div>
                <div class="inline-block mb-2">
                    <span class="bg-orange-500/10 text-orange-400 text-[11px] font-black px-3.5 py-1 rounded-full uppercase tracking-widest border border-orange-500/30">
                        Official Volunteer Application
                    </span>
                </div>
                <h1 class="text-2xl sm:text-4xl font-black text-white tracking-tight uppercase">
                    RUDRA SENA <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-400 to-amber-400">WING</span>
                </h1>
                <p class="mt-2 text-sm sm:text-base text-gray-300 font-medium max-w-xl mx-auto">
                    Join the dedicated emergency relief and disaster response mission. Serve society with pride, discipline, and purpose.
                </p>
                <p class="text-[11px] text-gray-400 font-semibold tracking-wide uppercase mt-1">
                    Akhanda Bharatha Viswa Hindu Parirakshana Samiti
                </p>
            </div>
        </div>

        <!-- PROGRESS MATRIX BAR (VISUAL) -->
        <div class="bg-gray-900/90 backdrop-blur border border-gray-800 rounded-xl p-4 mb-8 shadow-md">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center text-xs font-bold">
                <div id="prog_step_1" class="flex items-center justify-center gap-2 p-2 rounded-lg bg-orange-500/10 text-orange-400 border border-orange-500/30 transition">
                    <span class="w-5 h-5 rounded-full bg-orange-500 text-white flex items-center justify-center text-[10px] font-black">1</span>
                    <span class="tracking-wide">Verification</span>
                </div>
                <div id="prog_step_2" class="flex items-center justify-center gap-2 p-2 rounded-lg bg-gray-800/50 text-gray-400 border border-gray-700/50 transition">
                    <span class="w-5 h-5 rounded-full bg-gray-700 text-gray-300 flex items-center justify-center text-[10px] font-black">2</span>
                    <span class="tracking-wide">Profile & Cadre</span>
                </div>
                <div id="prog_step_3" class="flex items-center justify-center gap-2 p-2 rounded-lg bg-gray-800/50 text-gray-400 border border-gray-700/50 transition">
                    <span class="w-5 h-5 rounded-full bg-gray-700 text-gray-300 flex items-center justify-center text-[10px] font-black">3</span>
                    <span class="tracking-wide">Bank & Nominee</span>
                </div>
                <div id="prog_step_4" class="flex items-center justify-center gap-2 p-2 rounded-lg bg-gray-800/50 text-gray-400 border border-gray-700/50 transition">
                    <span class="w-5 h-5 rounded-full bg-gray-700 text-gray-300 flex items-center justify-center text-[10px] font-black">4</span>
                    <span class="tracking-wide">Documents & Terms</span>
                </div>
            </div>
        </div>

        <!-- MAIN APPLICATION CARD -->
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-200/80 overflow-hidden">
            
            <!-- STAGE 1: MEMBERSHIP VALIDATION GATE -->
            <div id="membership_gate_section" class="p-6 sm:p-8 bg-gradient-to-b from-orange-50/60 to-white border-b border-orange-100">
                <div class="flex items-start gap-4 mb-5">
                    <div class="w-10 h-10 rounded-xl bg-orange-500 text-white flex items-center justify-center text-lg shrink-0 shadow-md">
                        🛡️
                    </div>
                    <div>
                        <span class="text-[10px] font-black tracking-widest uppercase text-orange-600 bg-orange-100 px-2 py-0.5 rounded">Prerequisite Clearance</span>
                        <h2 class="text-lg sm:text-xl font-black text-gray-900 tracking-tight mt-0.5">
                            Core Membership Verification Gate
                        </h2>
                        <p class="text-xs text-gray-600 mt-1 leading-relaxed">
                            Only verified, registered members of ABVHPS (Age 24–44 years) are eligible to enlist into the Rudrasena Wing.
                        </p>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-xl border border-orange-200 shadow-sm">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                        <div class="sm:col-span-2">
                            <label for="lookup_membership_id" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Enter 12-Digit Membership ID <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 text-sm font-mono">💳</span>
                                <input type="text" id="lookup_membership_id" maxlength="12" class="w-full pl-9 pr-4 py-2.5 bg-gray-50/50 border border-gray-300 rounded-lg font-mono text-base font-bold text-gray-900 tracking-widest focus:ring-2 focus:ring-orange-500 focus:border-orange-500 focus:bg-white transition outline-none" placeholder="915XXXXXXXXX">
                            </div>
                        </div>
                        <div>
                            <button type="button" id="btn_verify_member" onclick="triggerMembershipLookup()" class="w-full bg-gradient-to-r from-orange-600 to-amber-600 hover:from-orange-700 hover:to-amber-700 text-white font-black py-2.5 px-4 rounded-lg text-xs uppercase tracking-wider shadow-md hover:shadow-lg transition duration-200 flex items-center justify-center gap-2 h-[44px] cursor-pointer">
                                <span>Verify Membership</span>
                                <span>&rarr;</span>
                            </button>
                        </div>
                    </div>
                    
                    <div id="gate_response_msg" class="text-xs font-bold mt-3.5 hidden p-3 rounded-lg border"></div>
                </div>
            </div>

            <!-- MAIN REGISTRATION FORM -->
            <form id="rudrasena_registration_form" onsubmit="executeRudrasenaSubmission(event)" enctype="multipart/form-data" class="hidden p-6 sm:p-10 space-y-10 animate-fadeIn">
                @csrf
                <!-- Auto-Bound Hidden Input Parameters -->
                <input type="hidden" name="membership_id" id="bound_membership_id">
                <input type="hidden" name="dob" id="bound_dob">
                <input type="hidden" name="age" id="bound_age">

                <!-- SECTION A: PRIMARY IDENTITY PROFILE -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3 pb-3 border-b border-gray-200">
                        <span class="w-7 h-7 rounded-lg bg-orange-100 text-orange-700 flex items-center justify-center font-black text-xs">A</span>
                        <div>
                            <h3 class="text-sm sm:text-base font-black text-gray-900 tracking-tight uppercase">Identity Profile & Cadre Allocation</h3>
                            <p class="text-[11px] text-gray-500 font-semibold">Verified personal metrics from your central membership profile.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="display_full_name" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Full Name</label>
                            <input type="text" name="full_name" id="display_full_name" readonly class="w-full bg-gray-100/80 border border-gray-300 rounded-lg px-3.5 py-2 text-xs font-bold text-gray-800 outline-none cursor-not-allowed">
                        </div>

                        <div>
                            <label for="display_mobile" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Active WhatsApp Mobile</label>
                            <input type="text" name="mobile" id="display_mobile" readonly class="w-full bg-gray-100/80 border border-gray-300 rounded-lg px-3.5 py-2 text-xs font-bold text-gray-800 outline-none cursor-not-allowed">
                        </div>

                        <div>
                            <label for="display_email" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Email Address <span class="text-red-500">*</span></label>
                            <input type="email" name="email" id="display_email" required class="w-full bg-white border border-gray-300 rounded-lg px-3.5 py-2 text-xs font-bold text-gray-900 outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition">
                            <span class="text-[10px] text-gray-500 mt-0.5 block">Official ID Card & credentials will be dispatched to this email.</span>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="display_blood" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Blood Group</label>
                                <input type="text" name="blood_group" id="display_blood" readonly class="w-full bg-gray-100/80 border border-gray-300 rounded-lg px-3 py-2 text-xs font-bold text-red-600 outline-none cursor-not-allowed">
                            </div>
                            <div>
                                <label for="display_gotram" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Gotram</label>
                                <input type="text" name="gotram" id="display_gotram" readonly class="w-full bg-gray-100/80 border border-gray-300 rounded-lg px-3 py-2 text-xs font-bold text-gray-800 outline-none cursor-not-allowed">
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="volunteer_type" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Volunteer Deployment Cadre <span class="text-red-500">*</span></label>
                            <select name="volunteer_type" id="volunteer_type" required class="w-full bg-white border border-gray-300 rounded-lg px-3.5 py-2.5 text-xs font-bold text-gray-900 outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition cursor-pointer">
                                <option value="">-- Select Volunteer Type / Deployment Preference --</option>
                                <option value="Full-Time Volunteer">Full-Time Volunteer (Dedicated Seva & Relief Operations)</option>
                                <option value="Part-Time Volunteer">Part-Time Volunteer (Weekend / Flexible Schedule)</option>
                                <option value="Emergency Response">Emergency Response (Disaster & Calamity Ground Force)</option>
                                <option value="Event-Based Volunteer">Event-Based Volunteer (Festivals & Religious Conclaves)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- SECTION B: BANK ACCOUNT VERIFICATION -->
                <div class="space-y-4 pt-2">
                    <div class="flex items-center gap-3 pb-3 border-b border-gray-200">
                        <span class="w-7 h-7 rounded-lg bg-orange-100 text-orange-700 flex items-center justify-center font-black text-xs">B</span>
                        <div>
                            <h3 class="text-sm sm:text-base font-black text-gray-900 tracking-tight uppercase">Bank Account Verification</h3>
                            <p class="text-[11px] text-gray-500 font-semibold">Strictly for direct insurance disbursement and institutional allowances.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Account Holder Name <span class="text-red-500">*</span></label>
                            <input type="text" name="bank_holder_name" required class="w-full border border-gray-300 rounded-lg px-3.5 py-2 text-xs font-semibold text-gray-900 focus:ring-2 focus:ring-orange-500 outline-none transition" placeholder="As printed in Bank Passbook">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Account Number <span class="text-red-500">*</span></label>
                            <input type="text" name="bank_account_number" required class="w-full border border-gray-300 rounded-lg px-3.5 py-2 text-xs font-mono font-bold text-gray-900 focus:ring-2 focus:ring-orange-500 outline-none transition" placeholder="Enter Bank Account Number">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Bank IFSC Code <span class="text-red-500">*</span></label>
                            <input type="text" name="bank_ifsc_code" required class="w-full border border-gray-300 rounded-lg px-3.5 py-2 text-xs font-mono font-bold text-gray-900 focus:ring-2 focus:ring-orange-500 outline-none uppercase transition" placeholder="e.g. SBIN0001234">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Bank Name & Branch <span class="text-red-500">*</span></label>
                            <input type="text" name="bank_name_branch" required class="w-full border border-gray-300 rounded-lg px-3.5 py-2 text-xs font-semibold text-gray-900 focus:ring-2 focus:ring-orange-500 outline-none transition" placeholder="e.g. SBI, Porumamilla Branch">
                        </div>
                    </div>
                </div>

                <!-- SECTION C: INSURANCE NOMINEE DETAILS -->
                <div class="space-y-4 pt-2">
                    <div class="flex items-center gap-3 pb-3 border-b border-gray-200">
                        <span class="w-7 h-7 rounded-lg bg-orange-100 text-orange-700 flex items-center justify-center font-black text-xs">C</span>
                        <div>
                            <h3 class="text-sm sm:text-base font-black text-gray-900 tracking-tight uppercase">Insurance Nominee Details</h3>
                            <p class="text-[11px] text-gray-500 font-semibold">Designated recipient for the institutional ₹25 Lakh accident cover policy.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Nominee Full Name <span class="text-red-500">*</span></label>
                            <input type="text" name="nominee_name" required class="w-full border border-gray-300 rounded-lg px-3.5 py-2 text-xs font-semibold text-gray-900 focus:ring-2 focus:ring-orange-500 outline-none transition" placeholder="Full name of nominee">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Relationship <span class="text-red-500">*</span></label>
                            <input type="text" name="nominee_relation" required class="w-full border border-gray-300 rounded-lg px-3.5 py-2 text-xs font-semibold text-gray-900 focus:ring-2 focus:ring-orange-500 outline-none transition" placeholder="e.g. Wife, Mother, Father">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Nominee Age <span class="text-red-500">*</span></label>
                            <input type="number" name="nominee_age" required min="1" max="120" class="w-full border border-gray-300 rounded-lg px-3.5 py-2 text-xs font-semibold text-gray-900 focus:ring-2 focus:ring-orange-500 outline-none transition" placeholder="Age">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Nominee Contact Number <span class="text-red-500">*</span></label>
                            <input type="text" name="nominee_contact" required class="w-full border border-gray-300 rounded-lg px-3.5 py-2 text-xs font-mono font-semibold text-gray-900 focus:ring-2 focus:ring-orange-500 outline-none transition" placeholder="10-digit mobile number">
                        </div>
                    </div>
                </div>

                <!-- SECTION D: FAMILY MEMBERS TREE -->
                <div class="space-y-4 pt-2">
                    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-2 pb-3 border-b border-gray-200">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-lg bg-orange-100 text-orange-700 flex items-center justify-center font-black text-xs">D</span>
                            <div>
                                <h3 class="text-sm sm:text-base font-black text-gray-900 tracking-tight uppercase">Family Unit Structural Tree</h3>
                                <p class="text-[11px] text-gray-500 font-semibold">List dependent family members (optional up to 6 members).</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <label for="family_members_count" class="text-xs font-black text-orange-600 uppercase">Members Count:</label>
                            <select id="family_members_count" onchange="renderDynamicFamilyRows(this.value)" class="border border-orange-300 bg-orange-50/80 font-bold rounded-lg px-3 py-1.5 text-xs text-gray-800 focus:ring-2 focus:ring-orange-500 outline-none cursor-pointer">
                                <option value="0">None / 0</option>
                                <option value="1">1 Member</option>
                                <option value="2">2 Members</option>
                                <option value="3">3 Members</option>
                                <option value="4">4 Members</option>
                                <option value="5">5 Members</option>
                                <option value="6">6 Members</option>
                            </select>
                        </div>
                    </div>

                    <!-- Dynamic Family Rows Container -->
                    <div id="dynamic_family_rows_container" class="space-y-3"></div>
                </div>

                <!-- SECTION E: LEGAL DOCUMENTS UPLOAD -->
                <div class="space-y-4 pt-2">
                    <div class="flex items-center gap-3 pb-3 border-b border-gray-200">
                        <span class="w-7 h-7 rounded-lg bg-orange-100 text-orange-700 flex items-center justify-center font-black text-xs">E</span>
                        <div>
                            <h3 class="text-sm sm:text-base font-black text-gray-900 tracking-tight uppercase">Verified Document Packets Upload</h3>
                            <p class="text-[11px] text-gray-500 font-semibold">Upload clear scanned copies or photos (Max 2MB per document, PNG/JPG).</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-200/90 shadow-sm hover:border-orange-300 transition">
                            <label class="block text-xs font-black text-gray-800 uppercase mb-1">
                                1. Health Fitness Declaration <span class="text-red-500">*</span>
                            </label>
                            <input type="file" name="document_health_declaration" required accept="image/*" class="w-full text-xs p-1.5 bg-white border border-gray-300 rounded-lg file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 cursor-pointer">
                            <span class="text-[10px] text-gray-500 font-semibold mt-1 block">Certified physical fitness sheet signed by a medical practitioner.</span>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-200/90 shadow-sm hover:border-orange-300 transition">
                            <label class="block text-xs font-black text-gray-800 uppercase mb-1">
                                2. Family Consent Declaration <span class="text-red-500">*</span>
                            </label>
                            <input type="file" name="document_family_declaration" required accept="image/*" class="w-full text-xs p-1.5 bg-white border border-gray-300 rounded-lg file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 cursor-pointer">
                            <span class="text-[10px] text-gray-500 font-semibold mt-1 block">Family consent document signed with 2 witness signatures.</span>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-200/90 shadow-sm hover:border-orange-300 transition">
                            <label class="block text-xs font-black text-gray-800 uppercase mb-1">
                                3. Government ID Proof Copy <span class="text-red-500">*</span>
                            </label>
                            <input type="file" name="document_id_proof" required accept="image/*" class="w-full text-xs p-1.5 bg-white border border-gray-300 rounded-lg file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 cursor-pointer">
                            <span class="text-[10px] text-gray-500 font-semibold mt-1 block">Clear image copy of Aadhaar Card, Voter ID, or Passport.</span>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-200/90 shadow-sm hover:border-orange-300 transition">
                            <label class="block text-xs font-black text-gray-800 uppercase mb-1">
                                4. Bank Passbook / Cheque Copy <span class="text-red-500">*</span>
                            </label>
                            <input type="file" name="document_bank_proof" required accept="image/*" class="w-full text-xs p-1.5 bg-white border border-gray-300 rounded-lg file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 cursor-pointer">
                            <span class="text-[10px] text-gray-500 font-semibold mt-1 block">Bank passbook front page or cancelled cheque copy.</span>
                        </div>
                    </div>
                </div>

                <!-- SECTION F: LEGAL DISCLAIMER & TERMS AND CONDITIONS -->
                <div class="bg-gradient-to-br from-gray-900 to-gray-950 text-white p-6 sm:p-8 rounded-2xl border border-orange-500/30 shadow-xl space-y-6">
                    
                    <!-- 1. Official Legal Disclaimer -->
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="text-base">⚠️</span>
                            <h4 class="text-xs sm:text-sm font-black uppercase text-orange-400 tracking-wider">
                                1. Official Legal Disclaimer & Operational Scope
                            </h4>
                        </div>
                        <div class="bg-gray-800/80 p-4 rounded-xl border border-gray-700 text-xs text-gray-300 leading-relaxed font-medium shadow-inner">
                            The registered members of ABVHPS joining the Rudrasena Wing will be deployed solely for voluntary emergency support and relief operations during accidents or natural calamities. The organization does not enforce mandatory deployment; participation is completely voluntary and based on the absolute consent of the member. ABVHPS shall not be held legally responsible for any incidents or casualties during operations. However, on humanitarian grounds, the organization will facilitate the alignment of the designated ₹25 Lakh accident insurance for the family. No compensation shall be granted if a member expires due to personal reasons or health illnesses.
                        </div>
                    </div>

                    <!-- 2. Terms & Conditions -->
                    <div class="space-y-2.5">
                        <div class="flex items-center gap-2">
                            <span class="text-base">📋</span>
                            <h4 class="text-xs sm:text-sm font-black uppercase text-amber-400 tracking-wider">
                                2. Enlistment Terms & Voluntary Undertaking
                            </h4>
                        </div>
                        <div class="space-y-2 text-xs text-gray-300 font-medium">
                            <div class="bg-gray-800/60 p-3 rounded-lg border border-gray-700/60 flex items-start gap-2.5">
                                <span class="text-orange-400 font-bold">•</span>
                                <span>I am enrolling in the Rudrasena Dal with my absolute willingness and personal consent without any external force or pressure. I have obtained formal clearance and permission from my family members for this decision.</span>
                            </div>
                            <div class="bg-gray-800/60 p-3 rounded-lg border border-gray-700/60 flex items-start gap-2.5">
                                <span class="text-orange-400 font-bold">•</span>
                                <span>Under any circumstances or operational eventualities, neither I nor my family members shall hold ABVHPS or its management liable or responsible. I am affirming this statement with complete awareness and sound mind.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Consent Checkbox -->
                    <div class="pt-4 border-t border-gray-800">
                        <label class="inline-flex items-start gap-3 cursor-pointer select-none">
                            <input type="checkbox" name="disclaimer_accepted" value="1" required class="h-5 w-5 rounded border-gray-600 bg-gray-800 text-orange-500 focus:ring-orange-500 focus:ring-offset-gray-900 mt-0.5 cursor-pointer">
                            <span class="text-xs font-bold text-white leading-relaxed">
                                I have thoroughly read, understood, and hereby accept all the official disclaimer directives, enlistment terms, and operational conditions specified above. <span class="text-orange-400">*</span>
                            </span>
                        </label>
                    </div>
                </div>

                <!-- SUBMIT DISPATCH BUTTON -->
                <div class="text-center pt-4">
                    <button type="submit" id="btn_rudrasena_submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-3 bg-gradient-to-r from-orange-600 via-orange-500 to-amber-600 hover:from-orange-700 hover:to-amber-700 text-white font-black text-sm py-4 px-12 rounded-xl shadow-xl hover:shadow-orange-500/25 transition duration-200 transform hover:-translate-y-0.5 uppercase tracking-wider cursor-pointer">
                        <span>Submit Rudrasena Application</span>
                        <span>🛡️</span>
                    </button>
                    <p class="text-[11px] text-gray-500 font-semibold mt-2">
                        🔒 Secured by ABVHPS Central Security Matrix V2.0
                    </p>
                </div>
            </form>
        </div>

    </div>
</div>

<!-- CLIENT JAVASCRIPT ENGINE -->
<script>
    const ajaxHeaders = {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    };

    /**
     * DYNAMIC FAMILY ROWS REPEATER GENERATOR ENGINE (UP TO 6 NODES)
     */
    function renderDynamicFamilyRows(count) {
        const container = document.getElementById('dynamic_family_rows_container');
        container.innerHTML = '';
        
        const rowCount = parseInt(count);
        if (rowCount === 0) return;

        for (let i = 0; i < rowCount; i++) {
            const rowHtml = `
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 grid grid-cols-1 sm:grid-cols-12 gap-3 items-center animate-fadeIn shadow-sm">
                    <div class="sm:col-span-1 text-center">
                        <span class="bg-orange-600 text-white text-xs font-black w-6 h-6 rounded-full inline-flex items-center justify-center shadow-sm">${i + 1}</span>
                    </div>
                    <div class="sm:col-span-4">
                        <label class="block text-[10px] font-bold text-gray-600 uppercase mb-1">Member Full Name *</label>
                        <input type="text" name="family[${i}][name]" required class="w-full bg-white border border-gray-300 rounded-lg px-3 py-1.5 text-xs font-semibold text-gray-900 focus:ring-2 focus:ring-orange-500 outline-none transition" placeholder="Full name">
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-[10px] font-bold text-gray-600 uppercase mb-1">Relationship *</label>
                        <input type="text" name="family[${i}][relation]" required class="w-full bg-white border border-gray-300 rounded-lg px-3 py-1.5 text-xs font-semibold text-gray-900 focus:ring-2 focus:ring-orange-500 outline-none transition" placeholder="e.g. Son, Daughter">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-[10px] font-bold text-gray-600 uppercase mb-1">Age *</label>
                        <input type="number" name="family[${i}][age]" required min="1" max="120" class="w-full bg-white border border-gray-300 rounded-lg px-3 py-1.5 text-xs font-semibold text-gray-900 focus:ring-2 focus:ring-orange-500 outline-none transition" placeholder="Age">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-[10px] font-bold text-gray-600 uppercase mb-1">Gender *</label>
                        <select name="family[${i}][gender]" required class="w-full bg-white border border-gray-300 rounded-lg px-2 py-1.5 text-xs font-semibold text-gray-900 focus:ring-2 focus:ring-orange-500 outline-none transition cursor-pointer">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', rowHtml);
        }
    }

    /**
     * SECURE CENTRAL MEMBERSHIP LOOKUP PIPELINE
     */
    async function triggerMembershipLookup() {
        const memberId = document.getElementById('lookup_membership_id').value.trim();
        const respMsg = document.getElementById('gate_response_msg');
        const verifyBtn = document.getElementById('btn_verify_member');
        const mainForm = document.getElementById('rudrasena_registration_form');
        const step2Badge = document.getElementById('prog_step_2');
        const step3Badge = document.getElementById('prog_step_3');
        const step4Badge = document.getElementById('prog_step_4');

        if (!memberId || memberId.length !== 12) {
            respMsg.className = "text-xs font-bold mt-3.5 text-rose-700 bg-rose-50 border border-rose-200 p-3 rounded-lg block";
            respMsg.innerText = '⚠️ Please enter a valid 12-digit core membership ID.';
            return;
        }

        verifyBtn.disabled = true;
        verifyBtn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Verifying...</span>
        `;
        respMsg.classList.add('hidden');
        mainForm.classList.add('hidden');

        try {
            let response = await fetch("{{ route('rudrasena.verify_member') }}", {
                method: 'POST',
                headers: ajaxHeaders,
                body: JSON.stringify({ membership_id: memberId })
            });
            let result = await response.json();
            verifyBtn.disabled = false;
            verifyBtn.innerHTML = `<span>Verify Membership</span> <span>&rarr;</span>`;

            if (result.success) {
                respMsg.className = "text-xs font-bold mt-3.5 text-emerald-800 bg-emerald-50 border border-emerald-200 p-3.5 rounded-lg block shadow-sm flex items-center gap-2";
                respMsg.innerHTML = `<span class="text-emerald-600 text-sm">✓</span> <span>${result.message}</span>`;

                // Bind immutable tracking variables securely
                document.getElementById('bound_membership_id').value = result.member.membership_id;
                document.getElementById('bound_dob').value = result.member.dob;
                document.getElementById('bound_age').value = result.member.age;

                document.getElementById('display_full_name').value = result.member.full_name;
                document.getElementById('display_mobile').value = result.member.mobile;
                
                // Smart Email Unlocking Fallback Logic Engine
                const emailInput = document.getElementById('display_email');
                if (!result.member.email || result.member.email === 'N/A') {
                    emailInput.value = '';
                    emailInput.readOnly = false;
                    emailInput.placeholder = "Provide working email to receive your ID Card";
                } else {
                    emailInput.value = result.member.email;
                    emailInput.readOnly = true;
                }
                
                document.getElementById('display_blood').value = result.member.blood_group;
                document.getElementById('display_gotram').value = result.member.gotram;

                // Update progress step indicators
                if (step2Badge) step2Badge.className = 'flex items-center justify-center gap-2 p-2 rounded-lg bg-orange-500/10 text-orange-400 border border-orange-500/30 transition';
                if (step3Badge) step3Badge.className = 'flex items-center justify-center gap-2 p-2 rounded-lg bg-orange-500/10 text-orange-400 border border-orange-500/30 transition';
                if (step4Badge) step4Badge.className = 'flex items-center justify-center gap-2 p-2 rounded-lg bg-orange-500/10 text-orange-400 border border-orange-500/30 transition';

                // Unroll the main form smoothly
                mainForm.classList.remove('hidden');
                mainForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                respMsg.className = "text-xs font-bold mt-3.5 text-rose-800 bg-rose-50 border border-rose-200 p-3.5 rounded-lg block shadow-sm flex items-center gap-2";
                respMsg.innerHTML = `<span class="text-rose-600 text-sm">⚠️</span> <span>${result.message}</span>`;
            }
        } catch (error) {
            verifyBtn.disabled = false;
            verifyBtn.innerHTML = `<span>Verify Membership</span> <span>&rarr;</span>`;
            console.error("Verification error:", error);
            respMsg.className = "text-xs font-bold mt-3.5 text-rose-800 bg-rose-50 border border-rose-200 p-3.5 rounded-lg block";
            respMsg.innerText = '⚠️ Network error querying central nodes. Please check your connection and try again.';
            respMsg.classList.remove('hidden');
        }
    }

    /**
     * MULTI-PART RELATIONAL RECORD DEPLOYMENT SYSTEM
     */
    async function executeRudrasenaSubmission(event) {
        event.preventDefault();
        
        const submitBtn = document.getElementById('btn_rudrasena_submit');
        const formElement = document.getElementById('rudrasena_registration_form');
        const packetData = new FormData(formElement);

        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Submitting Rudrasena Application...</span>
        `;

        try {
            let response = await fetch("{{ route('rudrasena.submit') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: packetData
            });
            let result = await response.json();
            submitBtn.disabled = false;
            submitBtn.innerHTML = `<span>Submit Rudrasena Application</span> <span>🛡️</span>`;

            if (result.success) {
                alert(result.message || '🎉 Application submitted successfully!');
                formElement.reset();
                document.getElementById('membership_gate_section').classList.add('hidden');
                formElement.classList.add('hidden');
                window.location.href = "/";
            } else {
                alert('Submission Error: ' + (result.message || 'Please check all required fields.'));
            }
        } catch (error) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = `<span>Submit Rudrasena Application</span> <span>🛡️</span>`;
            console.error("Submission error:", error);
            alert('Network error submitting application. Please verify your connection and try again.');
        }
    }
</script>
@endsection
