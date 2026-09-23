@extends('layouts.app')

@section('title', 'Kala Brundham Cultural Network | ABVHPS')
@section('meta_description', 'Register your bhajana mandali, kirtan group, and traditional cultural arts team with the ABVHPS Kala Brundham network.')

@section('content')
<div class="max-w-4xl mx-auto py-10 px-4">
    
    <!-- CENTRAL KALA BRUNDAM REGISTRATION DESK WORKSPACE CONTAINER -->
    <div id="kala_brundam_form_panel" class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
        
        <!-- Header Badge -->
        <div class="text-center bg-brandDarkGray text-white p-6 border-b-4 border-brandOrange">
            <span class="text-4xl block mb-1 drop-shadow">🪘</span>
            <h1 class="text-xl md:text-3xl font-black tracking-wider uppercase text-brandOrange">KALA BRUNDAM CULTURAL REGISTRATION</h1>
            <p class="text-gray-300 mt-1 font-medium text-xs md:text-sm tracking-wide">Akhanda Bharatha Viswa Hindu Parirakshana Samiti</p>
        </div>

        <form id="kala_brundam_main_form" onsubmit="executeKalaBrundamSubmission(event)" class="p-6 md:p-8 space-y-6">
            @csrf

            <!-- SECTION 1: CULTURAL TEAM PROFILE PARAMETERS -->
            <div class="border-b border-gray-200 pb-1">
                <h3 class="text-sm font-black text-brandGray uppercase tracking-wide">Section 1: Cultural Team Configuration</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase">Team Core Type *</label>
                    <select name="team_type" id="team_core_type" onchange="toggleCustomTypeBox(this.value)" required class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm font-semibold text-gray-800 focus:ring-2 focus:ring-brandOrange outline-none">
                        <option value="Bhajana">Bhajana</option>
                        <option value="Chekka Bhajana">Chekka Bhajana</option>
                        <option value="Kolatamu">Kolatamu</option>
                        <option value="Nrityamu">Natyamu</option>
                        <option value="Others">Others (Specify Below)</option>
                    </select>
                </div>
                <div id="custom_type_container" class="hidden">
                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase text-brandOrange">Specify Other Art Type *</label>
                    <input type="text" name="custom_type_spec" id="custom_type_spec" class="w-full border border-brandOrange rounded px-3 py-1.5 text-sm font-semibold text-gray-800 outline-none shadow-sm" placeholder="Enter manual art form">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase">Team Name *</label>
                    <input type="text" name="team_name" required class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm font-semibold text-gray-800 focus:ring-2 focus:ring-brandOrange outline-none" placeholder="Enter Team Name (E.g. Sri Rama Bhajana Brundam)">
                </div>
                <div class="md:col-span-3">
                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase">Team Locality / Station *</label>
                    <input type="text" name="location" required class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm font-semibold text-gray-800 focus:ring-2 focus:ring-brandOrange outline-none" placeholder="Enter Operational Area (E.g. Porumamilla, Kadapa District)">
                </div>
            </div>
            <!-- SECTION 2: DYNAMIC TEAM MEMBERS ASSEMBLY GATE -->
            <div class="border-b border-gray-200 pb-1 pt-2">
                <h3 class="text-sm font-black text-brandGray uppercase tracking-wide">Section 2: Team Members Roster Registry</h3>
            </div>

            <div class="bg-orange-50/40 p-4 rounded-lg border border-dashed border-brandOrange/40">
                <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Add Member via 12-Digit Membership ID</label>
                <div class="flex flex-col sm:flex-row gap-3">
                    <input type="text" id="member_lookup_id" maxlength="12" class="w-full sm:max-w-md border border-gray-300 rounded px-3 py-2 font-mono text-base tracking-widest focus:ring-2 focus:ring-brandOrange outline-none bg-white" placeholder="915XXXXXXXXX">
                    <button type="button" onclick="triggerIndividualMemberLookup()" class="bg-brandOrange hover:bg-opacity-95 text-white font-black text-xs px-6 py-2 rounded shadow uppercase tracking-wider cursor-pointer h-[42px] whitespace-nowrap">
                        Add Member to Team
                    </button>
                </div>
            </div>

            <!-- THE LIVE TEAM REGISTRY GRID TABLE -->
            <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-inner bg-gray-50/50">
                <table class="min-w-full divide-y divide-gray-200 text-left text-xs font-semibold text-gray-700">
                    <thead class="bg-gray-100 text-[10px] font-black uppercase text-gray-600 tracking-wider">
                        <tr>
                            <th class="px-4 py-3 text-center">Photo</th>
                            <th class="px-4 py-3">Full Name</th>
                            <th class="px-4 py-3">Membership ID</th>
                            <th class="px-4 py-3">Age</th>
                            <th class="px-4 py-3">Mobile Number</th>
                            <th class="px-4 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="team_roster_table_body" class="bg-white divide-y divide-gray-200">
                        <tr id="empty_roster_fallback_row">
                            <td colspan="6" class="px-4 py-8 text-center font-bold text-gray-400 uppercase tracking-wide">No members added to this cultural team yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- SECTION 3: SANATHANA DHARMA CULTURAL DEDICATED DISCLAIMER -->
            <div class="bg-gray-50 p-4 rounded-lg border border-l-4 border-brandOrange/40 space-y-3">
                <h4 class="text-xs font-black text-brandGray uppercase tracking-wider flex items-center gap-1">
                    📜 ABVHPS Cultural Dedicated Disclaimer
                </h4>
                <p class="text-xs text-gray-700 leading-relaxed font-medium bg-white p-3 rounded border border-gray-200">
                    We are appointing this designated cultural team solely for the purpose of promoting, developing, and propagating Sanathana Dharma values and awareness. Through this specific verified team, the organization shall strictly conduct and execute devotional and cultural programs only.
                </p>
                <div class="pt-1">
                    <label class="inline-flex items-start cursor-pointer select-none">
                        <input type="checkbox" name="disclaimer_accepted" value="1" required class="form-checkbox text-brandOrange focus:ring-brandOrange h-4 w-4 rounded border-gray-300 mt-0.5">
                        <span class="ml-2.5 text-xs font-bold text-gray-900 leading-tight">
                            I hereby accept and acknowledge the official cultural disclaimer directives specified by ABVHPS. *
                        </span>
                    </label>
                </div>
            </div>

            <!-- Main Submission Dispatch Grid -->
            @include('partials.legal-consent', ['type' => 'wing'])
            <div class="text-center pt-2">
                <button type="submit" id="btn_team_submit" class="bg-brandOrange hover:bg-opacity-90 text-white font-black text-sm py-3 px-12 rounded-lg shadow uppercase tracking-wider w-full sm:w-auto cursor-pointer">
                    Submit & Generate Certified Team Certificate
                </button>
            </div>
        </form>
    </div>
    <!-- ====================================================================== -->
    <!-- 🔱 THE MASTER ABVHPS CERTIFIED TEAM CERTIFICATE VIEWPORT (HIDDEN BY DEFAULT) -->
    <!-- ====================================================================== -->
    <div id="abvhps_certified_certificate_panel" class="hidden bg-white p-6 md:p-12 rounded-xl shadow-2xl border-8 border-double border-brandOrange max-w-4xl mx-auto my-6 relative overflow-hidden select-none animate-scaleUp">
        
        <!-- Intricate Vintage Background Corner Graphics Simulation -->
        <div class="absolute top-0 left-0 w-24 h-24 border-t-4 border-l-4 border-brandOrange/30 rounded-tl-lg m-4 pointer-events-none"></div>
        <div class="absolute top-0 right-0 w-24 h-24 border-t-4 border-r-4 border-brandOrange/30 rounded-tr-lg m-4 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-24 h-24 border-b-4 border-l-4 border-brandOrange/30 rounded-bl-lg m-4 pointer-events-none"></div>
        <div class="absolute bottom-0 right-0 w-24 h-24 border-b-4 border-r-4 border-brandOrange/30 rounded-br-lg m-4 pointer-events-none"></div>

        <!-- Certificate Master Core Layout Layout -->
        <div class="border-2 border-brandOrange/40 p-6 md:p-8 rounded-lg space-y-6 bg-orange-50/10">
            
            
            <div class="text-center space-y-2 border-b-2 border-dashed border-brandOrange/30 pb-4">
                <div class="w-16 h-16 rounded-full overflow-hidden bg-white border-2 border-brandOrange shadow-sm mx-auto flex items-center justify-center p-0.5">
                    <img src="{{ asset('images/ABVHPS_LOGO.jpg') }}" class="w-full h-full object-cover rounded-full" alt="ABVHPS Logo">
                </div>
                <h2 class="text-xl md:text-3xl font-black tracking-widest text-brandGray">AKHANDA BHARATHA VISWA HINDU PARIRAKSHANA SAMITI</h2>
                <span class="inline-block bg-brandOrange text-white text-[10px] font-black px-4 py-1 rounded tracking-widest uppercase shadow-sm">Official Cultural Affiliation Wing</span>
            </div>

            <!-- 2. Master Title Inscription Header -->
            <div class="text-center space-y-1 py-2">
                <h3 class="text-2xl md:text-4xl font-extrabold text-brandOrange tracking-wide uppercase font-serif drop-shadow-sm">CERTIFIED TEAM CERTIFICATE</h3>
                <div class="flex items-center justify-center gap-1.5 text-gray-500 text-[11px] font-bold">
                    <span>Registration Token:</span>
                    <span id="cert_display_reg_id" class="font-mono font-black text-brandGray tracking-wider bg-gray-100 px-2 py-0.5 rounded">ABVHPS-KB-XXX</span>
                </div>
            </div>

            <!-- 3. Primary Bounds Team Context Meta Deck -->
            <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                <div class="border-b md:border-b-0 md:border-r border-gray-100 pb-2 md:pb-0">
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Affiliated Team Type</span>
                    <span id="cert_display_type" class="text-sm font-black text-brandGray uppercase tracking-wide">BHAJANA BRUNDAM</span>
                </div>
                <div class="border-b md:border-b-0 md:border-r border-gray-100 pb-2 md:pb-0">
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Official Team Name</span>
                    <span id="cert_display_name" class="text-sm font-black text-brandOrange uppercase tracking-wide">SRI RAMA BHAJANA MANDALI</span>
                </div>
                <div>
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Operational Locality Station</span>
                    <span id="cert_display_location" class="text-sm font-black text-brandGray uppercase tracking-wide">PORUMAMILLA, KADAPA</span>
                </div>
            </div>

            <!-- 4. Consolidated Verified Members Table Array Grid -->
            <div class="space-y-2">
                <span class="text-[10px] font-black text-brandGray uppercase tracking-wider block flex items-center gap-1">
                    📋 Verified Active Roster Ranks Matrix
                </span>
                <div class="border border-brandOrange/20 rounded-lg overflow-hidden bg-white shadow-md">
                    <table class="min-w-full divide-y divide-gray-200 text-left text-[11px] font-bold text-gray-800">
                        <thead class="bg-orange-50/60 text-[9px] font-black uppercase text-brandGray tracking-wider border-b border-brandOrange/20">
                            <tr>
                                <th class="px-4 py-2.5 text-center">Rank Photo</th>
                                <th class="px-4 py-2.5">Full Name</th>
                                <th class="px-4 py-2.5">Membership ID</th>
                                <th class="px-4 py-2.5">Mobile Registry</th>
                                <th class="px-4 py-2.5 text-center">Status Security Seal</th>
                            </tr>
                        </thead>
                        <tbody id="cert_members_table_body" class="divide-y divide-gray-100 bg-white">
                            <!-- Injected live Rows from javascript loop structure -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 5. Bottom Legal Attestation Seal & Authorized Signatory Block -->
            <div class="pt-6 border-t border-gray-100 flex flex-row justify-between items-center gap-4">
                
                <!-- Left Hand Side Central Official Verification Stamp Seal Graphic -->
                <div class="flex items-center justify-center">
                    <div class="w-20 h-20 border-4 border-dashed border-red-600/70 rounded-full flex flex-col items-center justify-center transform -rotate-12 select-none pointer-events-none p-1 bg-white shadow-sm">
                        <span class="text-[7px] font-black text-red-600/80 uppercase tracking-tighter">ABVHPS INDIA</span>
                        <span class="text-[10px] font-black text-red-600 uppercase tracking-wider border-y border-red-600/60 my-0.5 px-1">CERTIFIED</span>
                        <span class="text-[6px] font-bold text-red-600/80 uppercase tracking-widest">OFFICIAL SEAL</span>
                    </div>
                </div>

                <!-- Right Hand Side Authorized Signatory Executive Dashboard -->
                <div class="text-right space-y-1 bg-white/40 p-3 rounded border border-gray-100 shadow-inner">
                    <div class="font-serif italic font-bold text-gray-800 text-sm border-b border-gray-300 pb-0.5 tracking-wide px-2 select-none pointer-events-none">
                        Swami Ji Signature
                    </div>
                    <span class="text-[9px] font-black text-brandGray uppercase block tracking-widest">Authorized Signatory</span>
                    <span class="text-[8px] font-bold text-gray-400 uppercase block tracking-wider">Central Cultural Board Executive</span>
                </div>
            </div>
        </div>

        <!-- Top Right Action Print Deck Menu Button -->
        <div class="mt-6 text-center">
            <button onclick="window.print()" class="bg-brandDarkGray hover:bg-opacity-95 text-white font-black text-xs py-2.5 px-8 rounded shadow uppercase tracking-wider cursor-pointer transition">
                Print Certified Team Document
            </button>
        </div>
    </div>
<!-- CORE KALA BRUNDAM EXPERT JAVASCRIPT PIPELINES -->
<script>
    const ajaxHeaders = {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    };

    // Global memory array matrix to track added team member ids locally and prevent fraud duplications
    let activeTeamRosterIds = [];

    /**
     * TOGGLE CUSTOM INPUT SPECIFICATION FOR OTHERS DROP-DOWN VALUE
     */
    function toggleCustomTypeBox(value) {
        const container = document.getElementById('custom_type_container');
        const inputSpec = document.getElementById('custom_type_spec');
        
        if (value === 'Others') {
            container.classList.remove('hidden');
            inputSpec.required = true;
        } else {
            container.classList.add('hidden');
            inputSpec.required = false;
            inputSpec.value = '';
        }
    }

    /**
     * LIVE MEMEBERS EXTRACTOR ENGINE LINKED DIRECTLY VIA MEMBERSHIP ID
     */
    async function triggerIndividualMemberLookup() {
        const lookupField = document.getElementById('member_lookup_id');
        const memberId = lookupField.value;
        const tbody = document.getElementById('team_roster_table_body');
        const fallbackRow = document.getElementById('empty_roster_fallback_row');

        if (!memberId || memberId.length !== 12) {
            alert('Please enter a valid 12-digit central membership ID.');
            return;
        }

        // Anti-Fraud Duplication Layer Check
        if (activeTeamRosterIds.includes(memberId)) {
            alert('This member is already incorporated inside your active cultural roster registry.');
            return;
        }

        try {
            let response = await fetch("{{ route('kalabrundam.fetch_member') }}", {
                method: 'POST',
                headers: ajaxHeaders,
                body: JSON.stringify({ membership_id: memberId })
            });
            let result = await response.json();

            if (result.success) {
                if (fallbackRow) fallbackRow.remove();

                const index = activeTeamRosterIds.length;
                activeTeamRosterIds.push(memberId);

                // Inject record rows securely with mapped input bounds for array submission
                const rowHtml = `
                    <tr id="roster_row_${memberId}" class="hover:bg-gray-50/60 animate-fadeIn">
                        <td class="px-4 py-2.5 text-center">
                            <img src="${result.member.photo_url}" class="w-10 h-10 object-cover rounded-md mx-auto shadow-sm border border-gray-200">
                            <input type="hidden" name="members[${index}][photo_url]" value="${result.member.photo_url}">
                        </td>
                        <td class="px-4 py-2.5 font-bold text-gray-900">
                            ${result.member.full_name}
                            <input type="hidden" name="members[${index}][full_name]" value="${result.member.full_name}">
                        </td>
                        <td class="px-4 py-2.5 font-mono tracking-wider text-brandOrange">
                            ${result.member.membership_id}
                            <input type="hidden" name="members[${index}][membership_id]" value="${result.member.membership_id}">
                        </td>
                        <td class="px-4 py-2.5 text-gray-500">
                            ${result.member.age} Yrs
                            <input type="hidden" name="members[${index}][age]" value="${result.member.age}">
                        </td>
                        <td class="px-4 py-2.5 font-medium text-gray-600">
                            ${result.member.mobile}
                            <input type="hidden" name="members[${index}][mobile]" value="${result.member.mobile}">
                        </td>
                        <td class="px-4 py-2.5 text-center">
                            <button type="button" onclick="removeMemberFromLocalRoster('${memberId}')" class="text-red-500 hover:text-red-700 font-black text-xs uppercase tracking-wide cursor-pointer transition">Remove</button>
                        </td>
                    </tr>
                `;
                tbody.insertAdjacentHTML('beforeend', rowHtml);
                lookupField.value = ''; // Clean input for next entries
            } else {
                alert(result.message);
            }
        } catch (error) {
            alert('Database network synchronizer communication failure.');
        }
    }

    /**
     * LOCAL ROSTER CLEANUP REMOVAL NODE
     */
    function removeMemberFromLocalRoster(memberId) {
        document.getElementById(`roster_row_${memberId}`).remove();
        activeTeamRosterIds = activeTeamRosterIds.filter(id => id !== memberId);

        if (activeTeamRosterIds.length === 0) {
            const tbody = document.getElementById('team_roster_table_body');
            tbody.innerHTML = `
                <tr id="empty_roster_fallback_row">
                    <td colspan="6" class="px-4 py-8 text-center font-bold text-gray-400 uppercase tracking-wide">No members added to this cultural team yet.</td>
                </tr>
            `;
        }
    }

    /**
     * MULTI-LAYER INGESTION DISPATCHER & LIVE CERTIFICATE VIEWPORT ENGINE
     */
    async function executeKalaBrundamSubmission(event) {
        event.preventDefault();

        if (activeTeamRosterIds.length === 0) {
            alert('Team Roster Error: Cultural teams must contain at least 1 verified member before generating a certificate.');
            return;
        }

        const submitBtn = document.getElementById('btn_team_submit');
        const formElement = document.getElementById('kala_brundam_main_form');
        const formData = new FormData(formElement);

        submitBtn.disabled = true;
        submitBtn.innerText = "Generating Certificate...";

        try {
            let response = await fetch("{{ route('kalabrundam.submit') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            });
            let result = await response.json();
            submitBtn.disabled = false;
            submitBtn.innerText = "Submit & Generate Certified Team Certificate";

            if (result.success) {
                // Hide the main registration panel workdesk
                document.getElementById('kala_brundam_form_panel').classList.add('hidden');

                // Dynamic binding values into the certificate template vectors
                document.getElementById('cert_display_reg_id').innerText = result.team_id;
                
                const typeText = result.team.team_type === 'Others' ? result.team.custom_type_spec : result.team.team_type;
                document.getElementById('cert_display_type').innerText = typeText;
                document.getElementById('cert_display_name').innerText = result.team.team_name;
                document.getElementById('cert_display_location').innerText = result.team.location;

                // Build verified members array grid inside the live certificate layout
                const certTableBody = document.getElementById('cert_members_table_body');
                certTableBody.innerHTML = ''; // Clean initialization
                
                result.members.forEach(member => {
                    const fallbackImg = 'https://placeholder.com';
                    const memberPhoto = member.photo_path ? `/storage/${member.photo_path}` : fallbackImg;
                    
                    const certRowHtml = `
                        <tr class="hover:bg-orange-50/20">
                            <td class="px-4 py-2 text-center">
                                <img src="${memberPhoto}" class="w-8 h-8 object-cover rounded border border-gray-200 mx-auto shadow-sm">
                            </td>
                            <td class="px-4 py-2 font-black text-gray-900">${member.full_name}</td>
                            <td class="px-4 py-2 font-mono text-brandOrange tracking-wide">${member.membership_id}</td>
                            <td class="px-4 py-2 text-gray-600 font-medium">${member.mobile}</td>
                            <td class="px-4 py-2 text-center">
                                <span class="bg-green-100 text-green-700 text-[9px] font-black px-2.5 py-0.5 rounded border border-green-200 uppercase tracking-wider shadow-sm flex items-center justify-center gap-0.5 max-w-[85px] mx-auto">
                                    <span>✓</span> Verified
                                </span>
                            </td>
                        </tr>
                    `;
                    certTableBody.insertAdjacentHTML('beforeend', certRowHtml);
                });

                // Unroll the master certificate viewport panel smoothly
                document.getElementById('abvhps_certified_certificate_panel').classList.remove('hidden');
                window.scrollTo({ top: 0, behavior: 'smooth' });

            } else {
                alert('Submission Error: ' + (result.message || 'Please check all required fields.'));
            }
        } catch (error) {
            submitBtn.disabled = false;
            submitBtn.innerText = "Submit & Generate Certified Team Certificate";
            console.error("Kala Brundam submission error:", error);
            alert('Network error submitting application. Please verify your connection and try again.');
        }
    }
</script>
@endsection
