@extends('layouts.app')

@section('title', 'Grama Seva Dal Youth Network | ABVHPS')
@section('meta_description', 'Join Grama Seva Dal to serve local village communities through cleanliness drives, education, and village temple care.')

@section('content')
 <!-- Fixed Layer Flow Container mapping directly below the Master Menu Bar -->
  <div class="relative max-w-5xl mx-auto py-10 px-4 sm:px-6 lg:px-8 z-10">
    
    <!-- CENTRAL GRAMA SEVA DAL REGISTRATION DESK WORKSPACE CONTAINER -->
    <div id="grama_seva_dal_form_panel" class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        
        <!-- Header Badge -->
        <div class="text-center bg-brandDarkGray text-white p-6 sm:p-8 border-b-4 border-brandOrange">
            <span class="text-3xl sm:text-4xl block mb-1 drop-shadow">🌱</span>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-black tracking-wider uppercase text-brandOrange">GRAMA SEVA DAL RECRUITMENT DESK</h1>
            <p class="text-gray-300 mt-1 font-medium text-xs md:text-sm tracking-wide">Akhanda Bharatha Viswa Hindu Parirakshana Samiti</p>
        </div>

        <form id="grama_seva_dal_main_form" onsubmit="executeGramaSevaDalSubmission(event)" class="p-6 sm:p-8 md:p-10 space-y-8">
            @csrf

            <!-- SECTION 1: REGIONAL LOCATION DEMOGRAPHICS -->
            <div class="border-b border-gray-200 pb-2 mb-6">
                <h3 class="text-base font-bold text-gray-900 uppercase tracking-wide">
                    Section 1: Regional Location Demographics
                </h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 items-end">
                <div class="flex flex-col justify-end">
                    <label class="block text-[13px] font-semibold text-gray-700 mb-1.5 uppercase leading-snug min-h-[36px] flex items-end">
                        <span>State <span class="text-orange-600">*</span></span>
                    </label>
                    <input type="text" name="state" required class="w-full h-12 border border-gray-300 rounded-lg px-3.5 text-sm font-medium text-gray-800 focus:border-brandOrange focus:ring-2 focus:ring-orange-100 outline-none transition bg-white" placeholder="E.g. Andhra Pradesh">
                </div>
                <div class="flex flex-col justify-end">
                    <label class="block text-[13px] font-semibold text-gray-700 mb-1.5 uppercase leading-snug min-h-[36px] flex items-end">
                        <span>District <span class="text-orange-600">*</span></span>
                    </label>
                    <input type="text" name="district" required class="w-full h-12 border border-gray-300 rounded-lg px-3.5 text-sm font-medium text-gray-800 focus:border-brandOrange focus:ring-2 focus:ring-orange-100 outline-none transition bg-white" placeholder="E.g. YSR Kadapa">
                </div>
                <div class="flex flex-col justify-end">
                    <label class="block text-[13px] font-semibold text-gray-700 mb-1.5 uppercase leading-snug min-h-[36px] flex items-end">
                        <span>Mandal <span class="text-orange-600">*</span></span>
                    </label>
                    <input type="text" name="mandal" required class="w-full h-12 border border-gray-300 rounded-lg px-3.5 text-sm font-medium text-gray-800 focus:border-brandOrange focus:ring-2 focus:ring-orange-100 outline-none transition bg-white" placeholder="E.g. Porumamilla">
                </div>
                <div class="flex flex-col justify-end">
                    <label class="block text-[13px] font-semibold text-gray-700 mb-1.5 uppercase leading-snug min-h-[36px] flex items-end">
                        <span>Village / Grama Panchayat <span class="text-orange-600">*</span></span>
                    </label>
                    <input type="text" name="village_or_gp" required class="w-full h-12 border border-gray-300 rounded-lg px-3.5 text-sm font-medium text-gray-800 focus:border-brandOrange focus:ring-2 focus:ring-orange-100 outline-none transition bg-white" placeholder="Enter Village Name">
                </div>
            </div>

            <!-- SECTION 2: COMMANDING TEAM LEADER APPOINTMENT GATE -->
            <div class="border-b border-gray-200 pb-1 pt-2">
                <h3 class="text-sm font-black text-brandGray uppercase tracking-wide">Section 2: Team Leader / President Designation</h3>
            </div>

            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Leader 12-Digit Membership ID *</label>
                        <input type="text" id="leader_lookup_id" maxlength="12" class="w-full border border-gray-300 rounded px-3 py-2 font-mono text-base tracking-widest focus:ring-2 focus:ring-brandOrange outline-none bg-white shadow-inner" placeholder="915XXXXXXXXX">
                    </div>
                    <div>
                        <button type="button" onclick="triggerTeamLeaderLookup()" class="w-full bg-brandDarkGray hover:bg-opacity-95 text-white font-bold py-2.5 px-4 rounded text-xs transition shadow-sm uppercase tracking-wider h-[42px] cursor-pointer">
                            Verify & Lock Leader
                        </button>
                    </div>
                </div>

                <!-- Hidden parameters to pass verified leader credentials securely -->
                <input type="hidden" name="leader_membership_id" id="bound_leader_id">
                <input type="hidden" name="leader_name" id="bound_leader_name">
                <input type="hidden" name="leader_mobile" id="bound_leader_mobile">

                <!-- Dynamic Leader Profile Locked Display Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2 border-t border-gray-200/60 hidden" id="leader_profile_display_box">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-0.5">Designated Leader Full Name</label>
                        <input type="text" id="display_leader_name" readonly class="w-full bg-gray-100 border border-gray-300 rounded px-3 py-1.5 text-sm font-bold text-gray-700 outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-0.5">Leader Registered Mobile</label>
                        <input type="text" id="display_leader_mobile" readonly class="w-full bg-gray-100 border border-gray-300 rounded px-3 py-1.5 text-sm font-bold text-gray-700 outline-none">
                    </div>
                </div>
            </div>
            <!-- SECTION 3: DYNAMIC YOUTH SERVICE FORCE ROSTER REGISTRY -->
            <div class="border-b border-gray-200 pb-1 pt-2">
                <h3 class="text-sm font-black text-brandGray uppercase tracking-wide">Section 3: Active Youth Service Force Roster</h3>
            </div>

            <div class="bg-orange-50/40 p-4 rounded-lg border border-dashed border-brandOrange/40">
                <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Add Youth Member via 12-Digit Membership ID</label>
                <div class="flex flex-col sm:flex-row gap-3">
                    <input type="text" id="youth_lookup_id" maxlength="12" class="w-full sm:max-w-md border border-gray-300 rounded px-3 py-2 font-mono text-base tracking-widest focus:ring-2 focus:ring-brandOrange outline-none bg-white" placeholder="915XXXXXXXXX">
                    <button type="button" onclick="triggerIndividualYouthLookup()" class="bg-brandOrange hover:bg-opacity-95 text-white font-black text-xs px-6 py-2 rounded shadow uppercase tracking-wider cursor-pointer h-[42px] whitespace-nowrap">
                        Add Youth to Force
                    </button>
                </div>
            </div>

            <!-- THE LIVE ACTIVE YOUTH RECRUITMENT GRID TABLE -->
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
                    <tbody id="youth_roster_table_body" class="bg-white divide-y divide-gray-200">
                        <tr id="empty_dal_fallback_row">
                            <td colspan="6" class="px-4 py-8 text-center font-bold text-gray-400 uppercase tracking-wide">No youth assets added to this village service dal yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- SECTION 4: ABVHPS OFFICIAL GRAMA SEVA DAL SERVICE PLEDGE -->
            <div class="bg-gray-50 p-4 rounded-lg border border-l-4 border-brandOrange/40 space-y-3">
                <h4 class="text-xs font-black text-brandGray uppercase tracking-wider flex items-center gap-1">
                    🌱 ABVHPS Grama Seva Dal Service Pledge
                </h4>
                <p class="text-xs text-gray-700 leading-relaxed font-medium bg-white p-3 rounded border border-gray-200">
                    This designated village committee force is established strictly to execute voluntary environmental protection drives, tree plantations, local cleanliness activities, and the sacred restoration of ancient village temples, tanks, and water bodies (Koneru & Cheruvulu).
                </p>
                <div class="pt-1">
                    <label class="inline-flex items-start cursor-pointer select-none">
                        <input type="checkbox" name="charter_accepted" value="1" required class="form-checkbox text-brandOrange focus:ring-brandOrange h-4 w-4 rounded border-gray-300 mt-0.5">
                        <span class="ml-2.5 text-xs font-bold text-gray-900 leading-tight">
                            I hereby accept and authorize the official village service pledge charters specified by ABVHPS. *
                        </span>
                    </label>
                </div>
            </div>

            <!-- Main Submission Dispatch Action Button -->
            @include('partials.legal-consent', ['type' => 'wing'])
            <div class="text-center pt-2">
                <button type="submit" id="btn_dal_submit" class="bg-brandOrange hover:bg-opacity-90 text-white font-black text-sm py-3 px-12 rounded-lg shadow uppercase tracking-wider w-full sm:w-auto cursor-pointer">
                    Submit & Generate Official Service Charter
                </button>
            </div>
        </form>
    </div>
    <!-- ====================================================================== -->
    <!-- 🔱 THE MASTER ABVHPS OFFICIAL GRAMA SEVA DAL CHARTER VIEWPORT (HIDDEN) -->
    <!-- ====================================================================== -->
    <div id="abvhps_gong_charter_panel" class="hidden bg-white p-6 md:p-12 rounded-xl shadow-2xl border-8 border-double border-emerald-600 max-w-4xl mx-auto my-6 relative overflow-hidden select-none animate-scaleUp">
        
        <!-- Intricate Vintage Background Corner Graphics Simulation (Emerald theme for Environment/Village Service) -->
        <div class="absolute top-0 left-0 w-24 h-24 border-t-4 border-l-4 border-emerald-600/30 rounded-tl-lg m-4 pointer-events-none"></div>
        <div class="absolute top-0 right-0 w-24 h-24 border-t-4 border-r-4 border-emerald-600/30 rounded-tr-lg m-4 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-24 h-24 border-b-4 border-l-4 border-emerald-600/30 rounded-bl-lg m-4 pointer-events-none"></div>
        <div class="absolute bottom-0 right-0 w-24 h-24 border-b-4 border-r-4 border-emerald-600/30 rounded-br-lg m-4 pointer-events-none"></div>

        <!-- Charter Master Core Layout Layout -->
        <div class="border-2 border-emerald-600/40 p-6 md:p-8 rounded-lg space-y-6 bg-emerald-50/10">
            
            
            <div class="text-center space-y-2 border-b-2 border-dashed border-emerald-600/30 pb-4">
                <div class="w-16 h-16 rounded-full overflow-hidden bg-white border-2 border-emerald-600/40 shadow-sm mx-auto flex items-center justify-center p-0.5">
                    <img src="{{ asset('images/ABVHPS_LOGO.jpg') }}" class="w-full h-full object-cover rounded-full" alt="ABVHPS Logo">
                </div>
                <h2 class="text-xl md:text-3xl font-black tracking-widest text-brandGray">AKHANDA BHARATHA VISWA HINDU PARIRAKSHANA SAMITI</h2>
                <span class="inline-block bg-emerald-600 text-white text-[10px] font-black px-4 py-1 rounded tracking-widest uppercase shadow-sm">Official Rural Reconstruction & Service Wing</span>
            </div>

            <!-- 2. Master Title Inscription Header -->
            <div class="text-center space-y-1 py-2">
                <h3 class="text-2xl md:text-4xl font-extrabold text-emerald-700 tracking-wide uppercase font-serif drop-shadow-sm">OFFICIAL GRAMA SEVA DAL CHARTER</h3>
                <div class="flex items-center justify-center gap-1.5 text-gray-500 text-[11px] font-bold">
                    <span>Charter Tracking Reference ID:</span>
                    <span id="charter_display_reg_id" class="font-mono font-black text-brandGray tracking-wider bg-gray-100 px-2 py-0.5 rounded">ABVHPS-GSD-XXX</span>
                </div>
            </div>

            <!-- 3. Primary Bounds Regional Demographics Meta Deck -->
            <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm grid grid-cols-1 md:grid-cols-2 gap-4 text-center">
                <div class="border-b md:border-b-0 md:border-r border-gray-100 pb-2 md:pb-0 flex flex-col justify-center">
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Assigned Village Jurisdiction</span>
                    <span id="charter_display_village" class="text-base font-black text-brandGray uppercase tracking-wide">PORUMAMILLA GP</span>
                    <span id="charter_display_regional_path" class="text-[10px] font-bold text-gray-500">Mandal, District, State</span>
                </div>
                <div class="flex flex-col justify-center">
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Designated Commanding Leader / President</span>
                    <span id="charter_display_leader_name" class="text-base font-black text-emerald-700 uppercase tracking-wide">—</span>
                    <span id="charter_display_leader_meta" class="text-[10px] font-mono font-bold text-gray-500">ID: — | Mob: —</span>
                </div>
            </div>

            <!-- 4. Consolidated Active Service Force Table Array Grid -->
            <div class="space-y-2">
                <span class="text-[10px] font-black text-brandGray uppercase tracking-wider block flex items-center gap-1">
                    🌱 Mobilized Active Service Force Roster Matrix
                </span>
                <div class="border border-emerald-600/20 rounded-lg overflow-hidden bg-white shadow-md">
                    <table class="min-w-full divide-y divide-gray-200 text-left text-[11px] font-bold text-gray-800">
                        <thead class="bg-emerald-50/60 text-[9px] font-black uppercase text-brandGray tracking-wider border-b border-emerald-600/20">
                            <tr>
                                <th class="px-4 py-2.5 text-center">Force Photo</th>
                                <th class="px-4 py-2.5">Full Name</th>
                                <th class="px-4 py-2.5">Membership ID</th>
                                <th class="px-4 py-2.5">Mobile Registry</th>
                                <th class="px-4 py-2.5 text-center">Deployment Status Seal</th>
                            </tr>
                        </thead>
                        <tbody id="charter_members_table_body" class="divide-y divide-gray-100 bg-white">
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
                        <span class="text-[6px] font-black text-red-600/80 uppercase tracking-tighter">ABVHPS CENTRAL</span>
                        <span class="text-[9px] font-black text-red-600 uppercase tracking-wider border-y border-red-600/60 my-0.5 px-1">ACTIVE FORCE</span>
                        <span class="text-[5px] font-bold text-red-600/80 uppercase tracking-widest">OFFICIAL SERVICE SEAL</span>
                    </div>
                </div>

                <!-- Right Hand Side Authorized Signatory Executive Dashboard -->
                <div class="text-right space-y-1 bg-white/40 p-3 rounded border border-gray-100 shadow-inner">
                    <div class="font-serif italic font-bold text-gray-800 text-sm border-b border-gray-300 pb-0.5 tracking-wide px-2 select-none pointer-events-none">
                        Swami Ji Signature
                    </div>
                    <span class="text-[9px] font-black text-brandGray uppercase block tracking-widest">Authorized Signatory</span>
                    <span class="text-[8px] font-bold text-gray-400 uppercase block tracking-wider">Central Executive Rural Board</span>
                </div>
            </div>
        </div>

        <!-- Top Right Action Print Deck Menu Button -->
        <div class="mt-6 text-center">
            <button onclick="window.print()" class="bg-brandDarkGray hover:bg-opacity-95 text-white font-black text-xs py-2.5 px-8 rounded shadow uppercase tracking-wider cursor-pointer transition">
                Print Official Service Charter
            </button>
        </div>
    </div>
    
<!-- CORE GRAMA SEVA DAL EXPERT JAVASCRIPT PIPELINES - PART A -->
<script>
    const ajaxHeaders = {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    };

    // Global memory array matrix to track added youth force ids locally and prevent fraud duplications
    let activeYouthForceIds = [];

    /**
     * SECURE LOOKUP ENGINE FOR COMMANDING TEAM LEADER VERIFICATION
     */
    async function triggerTeamLeaderLookup() {
        const leaderId = document.getElementById('leader_lookup_id').value;
        const displayBox = document.getElementById('leader_profile_display_box');

        if (!leaderId || leaderId.length !== 12) {
            alert('Please enter a valid 12-digit core membership ID.');
            return;
        }

        try {
            let response = await fetch("{{ route('gramasevadal.fetch_member') }}", {
                method: 'POST',
                headers: ajaxHeaders,
                body: JSON.stringify({ membership_id: leaderId })
            });
            let result = await response.json();

            if (result.success) {
                // Bind properties securely into hidden parameters for form dispatching
                document.getElementById('bound_leader_id').value = result.member.membership_id;
                document.getElementById('bound_leader_name').value = result.member.full_name;
                document.getElementById('bound_leader_mobile').value = result.member.mobile;

                // Populate displayed elements
                document.getElementById('display_leader_name').value = result.member.full_name;
                document.getElementById('display_leader_mobile').value = result.member.mobile;

                displayBox.classList.remove('hidden');
                alert('🎉 Team Leader Verified & Locked Successfully!');
            } else {
                alert(result.message);
            }
        } catch (error) {
            alert('Database network synchronizer communication failure.');
        }
    }

    /**
     * LIVE YOUTH FORCE EXTRACTOR ENGINE LINKED DIRECTLY VIA MEMBERSHIP ID
     */
    async function triggerIndividualYouthLookup() {
        const lookupField = document.getElementById('youth_lookup_id');
        const memberId = lookupField.value;
        const tbody = document.getElementById('youth_roster_table_body');
        const fallbackRow = document.getElementById('empty_dal_fallback_row');
        const leaderBoundId = document.getElementById('bound_leader_id').value;

        if (!memberId || memberId.length !== 12) {
            alert('Please enter a valid 12-digit central membership ID.');
            return;
        }

        // Leader Safeguard Check
        if (memberId === leaderBoundId) {
            alert('Roster Constraint: The designated Team Leader cannot be added again as a general roster member.');
            return;
        }

        // Anti-Fraud Duplication Layer Check
        if (activeYouthForceIds.includes(memberId)) {
            alert('This youth asset is already incorporated inside your active service force roster.');
            return;
        }

        try {
            let response = await fetch("{{ route('gramasevadal.fetch_member') }}", {
                method: 'POST',
                headers: ajaxHeaders,
                body: JSON.stringify({ membership_id: memberId })
            });
            let result = await response.json();

            if (result.success) {
                if (fallbackRow) fallbackRow.remove();

                const index = activeYouthForceIds.length;
                activeYouthForceIds.push(memberId);

                // Inject record rows securely with mapped input bounds for array submission
                const rowHtml = `
                    <tr id="youth_row_${memberId}" class="hover:bg-gray-50/60 animate-fadeIn">
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
                            <button type="button" onclick="removeYouthFromLocalRoster('${memberId}')" class="text-red-500 hover:text-red-700 font-black text-xs uppercase tracking-wide cursor-pointer transition">Remove</button>
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
     * LOCAL YOUTH ROSTER CLEANUP REMOVAL NODE
     */
    function removeYouthFromLocalRoster(memberId) {
        document.getElementById(`youth_row_${memberId}`).remove();
        activeYouthForceIds = activeYouthForceIds.filter(id => id !== memberId);

        if (activeYouthForceIds.length === 0) {
            const tbody = document.getElementById('youth_roster_table_body');
            tbody.innerHTML = `
                <tr id="empty_dal_fallback_row">
                    <td colspan="6" class="px-4 py-8 text-center font-bold text-gray-400 uppercase tracking-wide">No youth assets added to this village service dal yet.</td>
                </tr>
            `;
        }
    }
        /**
     * MULTI-LAYER INGESTION DISPATCHER & LIVE CHARTER VIEWPORT ENGINE - PART B
     */
    async function executeGramaSevaDalSubmission(event) {
        event.preventDefault();

        // Structural Validations Check
        if (!document.getElementById('bound_leader_id').value) {
            alert('Validation Refused: Please verify and lock a Commanding Team Leader before final submission.');
            return;
        }

        if (activeYouthForceIds.length === 0) {
            alert('Force Roster Error: Village committees must contain at least 1 verified youth member before issuing a charter.');
            return;
        }

        const submitBtn = document.getElementById('btn_dal_submit');
        const formElement = document.getElementById('grama_seva_dal_main_form');
        const formData = new FormData(formElement);

        submitBtn.disabled = true;
        submitBtn.innerText = "Issuing Service Charter...";

        try {
            let response = await fetch("{{ route('gramasevadal.submit') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            });
            let result = await response.json();
            submitBtn.disabled = false;
            submitBtn.innerText = "Submit & Generate Official Service Charter";

            if (result.success) {
                // Hide the main registration workspace panel
                document.getElementById('grama_seva_dal_form_panel').classList.add('hidden');

                // Dynamic binding values into the charter template vectors
                document.getElementById('charter_display_reg_id').innerText = result.charter_id;
                document.getElementById('charter_display_village').innerText = result.dal.village_or_gp + ' COMMITTEE';
                document.getElementById('charter_display_regional_path').innerText = `${result.dal.mandal} Mandal, ${result.dal.district} District, ${result.dal.state}`;
                
                document.getElementById('charter_display_leader_name').innerText = result.dal.leader_name;
                document.getElementById('charter_display_leader_meta').innerText = `Leader ID: ${result.dal.leader_membership_id} | Mobile: +91 ${result.dal.leader_mobile}`;

                // Build verified members array grid inside the live charter certificate template layout
                const charterTableBody = document.getElementById('charter_members_table_body');
                charterTableBody.innerHTML = ''; // Clean initialization
                
                result.members.forEach(member => {
                    const fallbackImg = 'https://placeholder.com';
                    const memberPhoto = member.photo_path ? `/storage/${member.photo_path}` : fallbackImg;
                    
                    const charterRowHtml = `
                        <tr class="hover:bg-emerald-50/20">
                            <td class="px-4 py-2 text-center">
                                <img src="${memberPhoto}" class="w-8 h-8 object-cover rounded border border-gray-200 mx-auto shadow-sm">
                            </td>
                            <td class="px-4 py-2 font-black text-gray-900">${member.full_name}</td>
                            <td class="px-4 py-2 font-mono text-brandOrange tracking-wide">${member.membership_id}</td>
                            <td class="px-4 py-2 text-gray-600 font-medium">${member.mobile}</td>
                            <td class="px-4 py-2 text-center">
                                <span class="bg-emerald-100 text-emerald-700 text-[9px] font-black px-2.5 py-0.5 rounded border border-emerald-200 uppercase tracking-wider shadow-sm flex items-center justify-center gap-0.5 max-w-[110px] mx-auto">
                                    🌱 Active Force
                                </span>
                            </td>
                        </tr>
                    `;
                    charterTableBody.insertAdjacentHTML('beforeend', charterRowHtml);
                });

                // Unroll the master charter viewport panel smoothly
                document.getElementById('abvhps_gong_charter_panel').classList.remove('hidden');
                window.scrollTo({ top: 0, behavior: 'smooth' });

            } else {
                alert('Submission Error: ' + (result.message || 'Please check all required fields.'));
            }
        } catch (error) {
            submitBtn.disabled = false;
            submitBtn.innerText = "Submit & Generate Official Service Charter";
            console.error("Grama Seva Dal submission error:", error);
            alert('Network error submitting application. Please verify your connection and try again.');
        }
    }

  </script>
@endsection