@extends('layouts.app')

@section('title', 'Organic Farmers Agriculture Network | ABVHPS')
@section('meta_description', 'Join the ABVHPS Organic Farmers initiative promoting cow-based traditional natural farming and desi seed preservation.')

@section('content')
<div class="max-w-4xl mx-auto py-10 px-4">
    
    <!-- CENTRAL ORGANIC FARMERS REGISTRATION DESK WORKSPACE CONTAINER -->
    <div id="organic_farmer_form_panel" class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
        
        <!-- Header Badge (Nature-Rich Green Theme for Pure Agriculture) -->
        <div class="text-center bg-emerald-800 text-white p-6 border-b-4 border-emerald-500">
            <span class="text-4xl block mb-1 drop-shadow">🌾</span>
            <h1 class="text-xl md:text-3xl font-black tracking-wider uppercase text-emerald-300">ORGANIC FARMERS REGISTRY DESK</h1>
            <p class="text-emerald-100 mt-1 font-medium text-xs md:text-sm tracking-wide">Akhanda Bharatha Viswa Hindu Parirakshana Samiti</p>
        </div>

        <form id="organic_farmer_main_form" onsubmit="executeOrganicFarmerSubmission(event)" class="p-6 md:p-8 space-y-6">
            @csrf

            <!-- SECTION 1: FARMER IDENTITY LOOKUP GATEWAY -->
            <div class="border-b border-gray-200 pb-1">
                <h3 class="text-sm font-black text-brandGray uppercase tracking-wide">Section 1: Farmer Membership Verification</h3>
            </div>

            <div class="bg-emerald-50/30 p-5 rounded-lg border border-dashed border-emerald-600/30 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Enter Farmer 12-Digit Membership ID *</label>
                        <input type="text" id="farmer_lookup_id" maxlength="12" class="w-full border border-gray-300 rounded px-3 py-2 font-mono text-base tracking-widest focus:ring-2 focus:ring-emerald-600 outline-none bg-white shadow-inner" placeholder="915XXXXXXXXX">
                    </div>
                    <div>
                        <button type="button" onclick="verifyFarmerIdentity()" id="btn_verify_farmer" class="w-full bg-emerald-700 hover:bg-emerald-800 text-white font-bold py-2.5 px-4 rounded text-xs uppercase tracking-wider transition shadow cursor-pointer">
                            Verify Farmer ID
                        </button>
                    </div>
                </div>

                <!-- Hidden parameters to pass verified farmer credentials securely -->
                <input type="hidden" name="membership_id" id="bound_farmer_membership_id">
                <input type="hidden" name="farmer_name" id="bound_farmer_name">
                <input type="hidden" name="farmer_mobile" id="bound_farmer_mobile">

                <!-- Dynamic Farmer Profile Locked Display Grid -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 pt-4 border-t border-gray-200/60 hidden items-center" id="farmer_profile_display_box">
                    <div class="md:col-span-2 text-center">
                        <img id="display_farmer_photo" src="https://placeholder.com" class="w-16 h-16 object-cover rounded-full mx-auto shadow-md border-2 border-emerald-600/20">
                    </div>
                    <div class="md:col-span-5">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-0.5">Verified Farmer Full Name</label>
                        <input type="text" id="display_farmer_name" readonly class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-1.5 text-sm font-bold text-gray-700 outline-none">
                    </div>
                    <div class="md:col-span-5">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-0.5">Registered Mobile Registry</label>
                        <input type="text" id="display_farmer_mobile" readonly class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-1.5 text-sm font-bold text-gray-700 outline-none">
                    </div>
                </div>
            </div>
            <!-- MAIN REGISTRATION DATA CONTAINERS PANEL (HIDDEN BY DEFAULT UNTIL MEMBERSHIP CLEARENCE) -->
            <div id="organic_farmer_data_panel" class="hidden space-y-6 animate-fadeIn">

                <!-- SECTION 2: AGRICULTURAL LAND & COW INFRASTRUCTURE METRICS -->
                <div class="border-b border-gray-200 pb-1 pt-2">
                    <h3 class="text-sm font-black text-brandGray uppercase tracking-wide">Section 2: Agricultural & Cow Infrastructure</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1 uppercase">Total Land Size (In Acres) *</label>
                        <input type="number" name="land_size_acres" step="0.01" min="0.01" max="999.99" required class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm font-semibold text-gray-800 focus:ring-2 focus:ring-emerald-600 outline-none" placeholder="E.g. 2.50">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1 uppercase">Primary Water Source *</label>
                        <select name="water_source" required class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm font-semibold text-gray-800 focus:ring-2 focus:ring-emerald-600 outline-none">
                            <option value="Borewell">Borewell (బోరు బావి)</option>
                            <option value="Open Well">Open Well (చేద బావి)</option>
                            <option value="Canal Inflow">Canal Inflow (కాలువ నీరు)</option>
                            <option value="Rainfed Only">Rainfed Only (వర్షాధారం)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1 uppercase">Indigenous (Desi) Cows Count *</label>
                        <input type="number" name="indigenous_cows_count" min="0" required class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm font-semibold text-gray-800 focus:ring-2 focus:ring-emerald-600 outline-none" placeholder="Enter number of desi cows">
                    </div>
                </div>

                <!-- Natural Fertilizer Organic Infrastructure Vectors -->
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <label class="inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" name="uses_jeevamrutham" value="1" class="form-checkbox text-emerald-600 focus:ring-emerald-500 h-4 w-4 rounded border-gray-300">
                        <span class="ml-2.5 text-xs font-bold text-gray-700">
                            I prepare and regularly utilize Liquid Jeevamrutham (జీవామృతం) in my fields.
                        </span>
                    </label>
                    <label class="inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" name="uses_ghana_jeevamrutham" value="1" class="form-checkbox text-emerald-600 focus:ring-emerald-500 h-4 w-4 rounded border-gray-300">
                        <span class="ml-2.5 text-xs font-bold text-gray-700">
                            I prepare and regularly utilize Solid Ghana Jeevamrutham (ఘనజీవామృతం) in my fields.
                        </span>
                    </label>
                </div>
                <!-- SECTION 3: CERTIFIED CROPS MATRIX SELECTION GATE -->
                <div class="border-b border-gray-200 pb-1 pt-2">
                    <h3 class="text-sm font-black text-brandGray uppercase tracking-wide">Section 3: Nature Certified Crops Matrix</h3>
                </div>

                <div class="bg-emerald-50/20 p-4 rounded-lg border border-dashed border-emerald-600/30 grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                    <div class="sm:col-span-5">
                        <label class="block text-xs font-bold text-gray-700 mb-1 uppercase">Select Certified Crop Category *</label>
                        <select id="crop_selector_name" class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm font-semibold text-gray-800 focus:ring-2 focus:ring-emerald-600 outline-none bg-white">
                            <option value="Traditional Paddy (వరి)">Traditional Paddy (వరి)</option>
                            <option value="Pulses / Lentils (పప్పుధాన్యాలు)">Pulses / Lentils (పప్పుధాన్యాలు)</option>
                            <option value="Organic Vegetables (కూరగాయలు)">Organic Vegetables (కూరగాయలు)</option>
                            <option value="Natural Fruits / Orchards (పండ్ల తోటలు)">Natural Fruits / Orchards (పండ్ల తోటలు)</option>
                            <option value="Millets / Siridhanyalu (చిరుధాన్యాలు)">Millets / Siridhanyalu (చిరుధాన్యాలు)</option>
                        </select>
                    </div>
                    <div class="sm:col-span-4">
                        <label class="block text-xs font-bold text-gray-700 mb-1 uppercase">Specific Variety (Optional)</label>
                        <input type="text" id="crop_selector_variety" class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm font-semibold text-gray-800 focus:ring-2 focus:ring-emerald-600 outline-none bg-white" placeholder="E.g. Navara, Black Rice, Desi">
                    </div>
                    <div class="sm:col-span-3">
                        <button type="button" onclick="triggerLocalCropAddition()" class="w-full bg-emerald-700 hover:bg-emerald-800 text-white font-black text-xs py-2 px-4 rounded shadow uppercase tracking-wider h-[38px] cursor-pointer whitespace-nowrap">
                            Add Crop to Certificate
                        </button>
                    </div>
                </div>

                <!-- THE LIVE REGISTERED CROPS GRID TABLE LAYER -->
                <div class="border border-gray-200 rounded-lg overflow-hidden bg-white shadow-inner">
                    <table class="min-w-full divide-y divide-gray-200 text-left text-xs font-semibold text-gray-700">
                        <thead class="bg-gray-100 text-[10px] font-black uppercase text-gray-600 tracking-wider">
                            <tr>
                                <th class="px-4 py-3">Registered Crop Category</th>
                                <th class="px-4 py-3">Specific Variety Token</th>
                                <th class="px-4 py-3 text-center">Status Seal</th>
                                <th class="px-4 py-3 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="farmer_crops_table_body" class="divide-y divide-gray-200 bg-white">
                            <tr id="empty_crops_fallback_row">
                                <td colspan="4" class="px-4 py-6 text-center font-bold text-gray-400 uppercase tracking-wide">No agriculture crops registered for certification yet.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- SECTION 4: THE HOLY PURE ORGANIC OATH PLEDGE (MANDATORY CONSENT) -->
                <div class="bg-gray-50 p-5 rounded-lg border-2 border-emerald-600/20 space-y-3">
                    <h4 class="text-xs font-black text-emerald-800 uppercase tracking-wider flex items-center gap-1">
                        🌾 4. Pure Organic Agriculture Oath Pledge
                    </h4>
                    <p class="text-xs text-gray-700 leading-relaxed font-medium bg-white p-3 rounded border border-gray-200 shadow-inner">
                        I hereby declare and affirm before the central cultural board of ABVHPS that I shall strictly refrain from utilizando chemical fertilizers, artificial hormones, toxic pesticides, or weedicides in my registered land size coordinates. I pledge to cultivate my agricultural crops utilizing cow-based nature-friendly organic manure processes only.
                    </p>
                    <div class="pt-1 border-t border-gray-200/60">
                        <label class="inline-flex items-start cursor-pointer select-none">
                            <input type="checkbox" name="organic_oath_accepted" value="1" required class="form-checkbox text-emerald-600 focus:ring-emerald-500 h-4 w-4 rounded border-gray-300 mt-0.5">
                            <span class="ml-2.5 text-xs font-bold text-gray-900 leading-tight">
                                I solemnly affirm, accept, and bind myself to the holy organic agriculture oath specified above. *
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Core Submission Dispatch Action Anchor Button -->
                @include('partials.legal-consent', ['type' => 'wing'])
                <div class="text-center pt-2">
                    <button type="submit" id="btn_farmer_submit" class="bg-emerald-700 hover:bg-emerald-800 text-white font-black text-sm py-3 px-12 rounded-lg shadow uppercase tracking-wider w-full sm:w-auto cursor-pointer">
                        Submit & Generate Organic Producer Certificate
                    </button>
                </div>
            </div> <!-- END DATA PANEL CONTROLLER -->
        </form>
    </div> <!-- END FORM WORKSPACE PANEL -->
    <!-- ====================================================================== -->
    <!-- 🔱 THE MASTER ABVHPS ORGANIC FARMER MEMBERSHIP CERTIFICATE VIEWPORT -->
    <!-- ====================================================================== -->
    <div id="abvhps_organic_certificate_panel" class="hidden bg-white p-6 md:p-12 rounded-xl shadow-2xl border-8 border-double border-emerald-700 max-w-4xl mx-auto my-6 relative overflow-hidden select-none animate-scaleUp">
        
        <!-- Intricate Vintage Background Corner Graphics Simulation (Nature Green Theme) -->
        <div class="absolute top-0 left-0 w-24 h-24 border-t-4 border-l-4 border-emerald-700/30 rounded-tl-lg m-4 pointer-events-none"></div>
        <div class="absolute top-0 right-0 w-24 h-24 border-t-4 border-r-4 border-emerald-700/30 rounded-tr-lg m-4 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-24 h-24 border-b-4 border-l-4 border-emerald-700/30 rounded-bl-lg m-4 pointer-events-none"></div>
        <div class="absolute bottom-0 right-0 w-24 h-24 border-b-4 border-r-4 border-emerald-700/30 rounded-br-lg m-4 pointer-events-none"></div>

        <!-- Certificate Master Core Layout Layout -->
        <div class="border-2 border-emerald-700/40 p-6 md:p-8 rounded-lg space-y-6 bg-emerald-50/10">
            
            
            <div class="text-center space-y-2 border-b-2 border-dashed border-emerald-700/30 pb-4">
                <div class="w-16 h-16 rounded-full overflow-hidden bg-white border-2 border-emerald-700/40 shadow-sm mx-auto flex items-center justify-center p-0.5">
                    <img src="{{ asset('images/ABVHPS_LOGO.jpg') }}" class="w-full h-full object-cover rounded-full" alt="ABVHPS Logo">
                </div>
                <h2 class="text-xl md:text-3xl font-black tracking-widest text-brandGray">AKHANDA BHARATHA VISWA HINDU PARIRAKSHANA SAMITI</h2>
                <span class="inline-block bg-emerald-700 text-white text-[10px] font-black px-4 py-1 rounded tracking-widest uppercase shadow-sm">Official Cow-Based Natural Agriculture Wing</span>
            </div>

            <!-- 2. Master Title Inscription Header -->
            <div class="text-center space-y-1 py-2">
                <h3 class="text-2xl md:text-4xl font-extrabold text-emerald-800 tracking-wide uppercase font-serif drop-shadow-sm">ORGANIC FARMER MEMBERSHIP CERTIFICATE</h3>
                <div class="flex items-center justify-center gap-1.5 text-gray-500 text-[11px] font-bold">
                    <span>Certificate Tracking Reference ID:</span>
                    <span id="cert_display_farmer_reg_id" class="font-mono font-black text-brandGray tracking-wider bg-gray-100 px-2 py-0.5 rounded">ABVHPS-OF-XXX</span>
                </div>
            </div>

            <!-- 3. Primary Bounds Farmer Context Profile Deck -->
            <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                <div class="md:col-span-2 text-center border-b md:border-b-0 md:border-r border-gray-100 pb-3 md:pb-0">
                    <img id="cert_display_photo" src="https://placeholder.com" class="w-16 h-16 object-cover rounded-md mx-auto border-2 border-emerald-600/30 shadow-sm">
                </div>
                <div class="md:col-span-5 border-b md:border-b-0 md:border-r border-gray-100 pb-3 md:pb-0 px-2">
                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-wider block">Registered Farmer Producer</span>
                    <span id="cert_display_farmer_name" class="text-base font-black text-brandGray uppercase block tracking-wide">—</span>
                    <span id="cert_display_farmer_meta" class="text-[10px] font-mono font-bold text-gray-500">Membership ID: — | Mob: —</span>
                </div>
                <div class="md:col-span-5 grid grid-cols-2 gap-2 text-center px-2">
                    <div>
                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-wider block">Total Land Coordinate</span>
                        <span id="cert_display_land" class="text-sm font-black text-emerald-700 uppercase tracking-wide">5.00 ACRES</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-wider block">Desi Cows Force</span>
                        <span id="cert_display_cows" class="text-sm font-black text-brandGray uppercase tracking-wide">3 COWS</span>
                    </div>
                </div>
            </div>

            <!-- Infrastructure Verification Flags Info Labels Tag -->
            <div class="flex flex-wrap items-center justify-center gap-3 text-[10px] font-bold text-emerald-800">
                <div id="cert_badge_jeeva" class="bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-full shadow-sm hidden">✓ Uses Liquid Jeevamrutham</div>
                <div id="cert_badge_ghana" class="bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-full shadow-sm hidden">✓ Uses Solid Ghana Jeevamrutham</div>
                <div class="bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-full shadow-sm">✓ Mapped Water Source: <span id="cert_display_water" class="font-black uppercase">Borewell</span></div>
            </div>

            <!-- 4. Consolidated Nature Certified Crops Table Array Grid -->
            <div class="space-y-2">
                <span class="text-[10px] font-black text-brandGray uppercase tracking-wider block flex items-center gap-1">
                    📋 Certified Pure Organic Produce Matrix Roster
                </span>
                <div class="border border-emerald-600/20 rounded-lg overflow-hidden bg-white shadow-md">
                    <table class="min-w-full divide-y divide-gray-200 text-left text-[11px] font-bold text-gray-800">
                        <thead class="bg-emerald-50/60 text-[9px] font-black uppercase text-brandGray tracking-wider border-b border-emerald-600/20">
                            <tr>
                                <th class="px-6 py-2.5">Registered Crop Category</th>
                                <th class="px-6 py-2.5">Specific Variety Token Tag</th>
                                <th class="px-6 py-2.5 text-center">Status Security Seal Mark</th>
                            </tr>
                        </thead>
                        <tbody id="cert_crops_table_body" class="divide-y divide-gray-100 bg-white">
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
                        <span class="text-[6px] font-black text-red-600/80 uppercase tracking-tighter">ABVHPS INDIA</span>
                        <span class="text-[9px] font-black text-red-600 uppercase tracking-wider border-y border-red-600/60 my-0.5 px-1">PURE ORGANIC</span>
                        <span class="text-[5px] font-bold text-red-600/80 uppercase tracking-widest">VERIFIED PRODUCER</span>
                    </div>
                </div>

                <!-- Right Hand Side Authorized Signatory Executive Dashboard -->
                <div class="text-right space-y-1 bg-white/40 p-3 rounded border border-gray-100 shadow-inner">
                    <div class="font-serif italic font-bold text-gray-800 text-sm border-b border-gray-300 pb-0.5 tracking-wide px-2 select-none pointer-events-none">
                        Swami Ji Signature
                    </div>
                    <span class="text-[9px] font-black text-brandGray uppercase block tracking-widest">Authorized Signatory</span>
                    <span class="text-[8px] font-bold text-gray-400 uppercase block tracking-wider">Central Executive Agriculture Board</span>
                </div>
            </div>
        </div>

        <!-- Top Right Action Print Deck Menu Button -->
        <div class="mt-6 text-center">
            <button onclick="window.print()" class="bg-brandDarkGray hover:bg-opacity-95 text-white font-black text-xs py-2.5 px-8 rounded shadow uppercase tracking-wider cursor-pointer transition">
                Print Official Green Certificate
            </button>
        </div>
    </div>
<!-- CORE ORGANIC FARMERS EXPERT JAVASCRIPT PIPELINES - PART A -->
<script>
    const ajaxHeaders = {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    };

    // Global memory array matrix to track added crops locally and prevent duplications
    let activeRegisteredCrops = [];

    /**
     * SECURE LOOKUP ENGINE FOR FARMER MEMBERSHIP IDENTITY VERIFICATION
     */
    async function triggerFarmerIdentityLookup() {
        const lookupField = document.getElementById('farmer_lookup_id');
        const memberId = lookupField.value;
        const profileBox = document.getElementById('farmer_profile_display_box');
        const dataPanel = document.getElementById('organic_farmer_data_panel');

        if (!memberId || memberId.length !== 12) {
            alert('Please enter a valid 12-digit core membership ID.');
            return;
        }

        try {
            let response = await fetch("{{ route('organicfarmers.fetch_member') }}", {
                method: 'POST',
                headers: ajaxHeaders,
                body: JSON.stringify({ membership_id: memberId })
            });
            let result = await response.json();

            if (result.success) {
                // Bind properties securely into hidden parameters for form dispatcher pipelines
                document.getElementById('bound_farmer_membership_id').value = result.member.membership_id;
                document.getElementById('bound_farmer_name').value = result.member.full_name;
                document.getElementById('bound_farmer_mobile').value = result.member.mobile;

                // Populate layout elements
                document.getElementById('display_farmer_name').value = result.member.full_name;
                document.getElementById('display_farmer_mobile').value = result.member.mobile;
                document.getElementById('display_farmer_photo').src = result.member.photo_url;

                profileBox.classList.remove('hidden');
                dataPanel.classList.remove('hidden'); // Smoothly unrolls the primary asset parameters desk
                alert('🎉 Farmer Identity Verified & Agriculture Profiles Unlocked!');
            } else {
                alert(result.message);
            }
        } catch (error) {
            alert('Central portal master node dataset synchronization failure.');
        }
    }

    /**
     * DYNAMIC LOCAL CROP INGESTION ROW BUILDER MATRIX ENGINE
     */
    function triggerLocalCropAddition() {
        const cropCategory = document.getElementById('crop_selector_name').value;
        const cropVariety = document.getElementById('crop_selector_variety').value.trim();
        const tbody = document.getElementById('farmer_crops_table_body');
        const fallbackRow = document.getElementById('empty_crops_fallback_row');

        // Extract identifier tokens to secure duplicate bounds filtering checks
        const unifiedToken = cropCategory + (cropVariety ? '-' + cropVariety : '');
        
        if (activeRegisteredCrops.includes(unifiedToken)) {
            alert('Roster Duplication: This specific certified crop model is already registered inside your active session roster.');
            return;
        }

        if (fallbackRow) fallbackRow.remove();

        const index = activeRegisteredCrops.length;
        activeRegisteredCrops.push(unifiedToken);

        // Inject row arrays parameters smoothly into local memory grid tree view
        const rowHtml = `
            <tr id="crop_row_${index}" class="hover:bg-gray-50/60 animate-fadeIn">
                <td class="px-6 py-2.5 font-bold text-gray-900">
                    ${cropCategory}
                    <input type="hidden" name="crops[${index}][crop_name]" value="${cropCategory}">
                </td>
                <td class="px-6 py-2.5 font-mono text-emerald-700 tracking-wide">
                    ${cropVariety ? cropVariety : '<span class="text-gray-400 italic">Traditional Desi</span>'}
                    <input type="hidden" name="crops[${index}][variety_spec]" value="${cropVariety}">
                </td>
                <td class="px-6 py-2.5 text-center">
                    <span class="bg-emerald-100 text-emerald-700 text-[9px] font-black px-2 py-0.5 rounded border border-emerald-200 uppercase tracking-wider shadow-sm">Organic Pending</span>
                </td>
                <td class="px-6 py-2.5 text-center">
                    <button type="button" onclick="removeCropFromLocalRoster(${index}, '${unifiedToken}')" class="text-red-500 hover:text-red-700 font-black text-xs uppercase tracking-wide cursor-pointer transition">Remove</button>
                </td>
            </tr>
        `;
        tbody.insertAdjacentHTML('beforeend', rowHtml);
        document.getElementById('crop_selector_variety').value = ''; // Clean input text area desk
    }

    /**
     * LOCAL CROPS ROSTER CLEANUP MATRIX ENGINE REMOVAL
     */
    function removeCropFromLocalRoster(index, token) {
        document.getElementById(`crop_row_${index}`).remove();
        activeRegisteredCrops = activeRegisteredCrops.filter(t => t !== token);

        if (activeRegisteredCrops.length === 0) {
            const tbody = document.getElementById('farmer_crops_table_body');
            tbody.innerHTML = `
                <tr id="empty_crops_fallback_row">
                    <td colspan="4" class="px-4 py-6 text-center font-bold text-gray-400 uppercase tracking-wide">No agriculture crops registered for certification yet.</td>
                </tr>
            `;
        }
    }
    
        /**
     * MULTI-LAYER INGESTION DISPATCHER & LIVE GREEN CERTIFICATE VIEWPORT ENGINE - PART B
     */
    async function executeOrganicFarmerSubmission(event) {
        event.preventDefault();

        // Structural Validations Check
        if (!document.getElementById('bound_farmer_membership_id').value) {
            alert('Validation Refused: Please verify a Farmer Identity first before final submission.');
            return;
        }

        if (activeRegisteredCrops.length === 0) {
            alert('Roster Error: You must register at least 1 certified agricultural crop form before producing a certificate.');
            return;
        }

        const submitBtn = document.getElementById('btn_farmer_submit');
        const formElement = document.getElementById('organic_farmer_main_form');
        const formData = new FormData(formElement);

        submitBtn.disabled = true;
        submitBtn.innerText = "Securing Green Assets Packet...";

        try {
            let response = await fetch("{{ route('organicfarmers.submit') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            });
            let result = await response.json();
            submitBtn.disabled = false;
            submitBtn.innerText = "Submit & Generate Organic Producer Certificate";

            if (result.success) {
                // Hide the main registration workspace panel
                document.getElementById('organic_farmer_form_panel').classList.add('hidden');

                // Dynamic binding values into the certificate vector nodes
                document.getElementById('cert_display_farmer_reg_id').innerText = result.farmer_id;
                document.getElementById('cert_display_farmer_name').innerText = result.farmer.farmer_name;
                document.getElementById('cert_display_farmer_meta').innerText = `Membership ID: ${result.farmer.membership_id} | Mobile: +91 ${result.farmer.farmer_mobile}`;
                
                document.getElementById('cert_display_land').innerText = parseFloat(result.farmer.land_size_acres).toFixed(2) + ' ACRES';
                document.getElementById('cert_display_cows').innerText = result.farmer.indigenous_cows_count + ' COWS';
                document.getElementById('cert_display_water').innerText = result.farmer.water_source;

                // Toggle Jeevamrutham Infrastructure Badges Based on DB Boolean States
                if (result.farmer.uses_jeevamrutham) {
                    document.getElementById('cert_badge_jeeva').classList.remove('hidden');
                }
                if (result.farmer.uses_ghana_jeevamrutham) {
                    document.getElementById('cert_badge_ghana').classList.remove('hidden');
                }

                // Bind farmer profile photo securely
                const farmerLookupPhoto = document.getElementById('display_farmer_photo').src;
                document.getElementById('cert_display_photo').src = farmerLookupPhoto;

                // Build verified crops array grid inside the live certified certificate template layout
                const cropsTableBody = document.getElementById('cert_crops_table_body');
                cropsTableBody.innerHTML = ''; // Clean initialization
                
                result.crops.forEach(crop => {
                    const cropRowHtml = `
                        <tr class="hover:bg-emerald-50/20">
                            <td class="px-6 py-2 font-black text-gray-900">${crop.crop_name}</td>
                            <td class="px-6 py-2 font-mono text-emerald-700 tracking-wide">
                                ${crop.variety_spec ? crop.variety_spec : '<span class="text-gray-400 italic">Traditional Desi</span>'}
                            </td>
                            <td class="px-6 py-2 text-center">
                                <span class="bg-emerald-100 text-emerald-700 text-[10px] font-black px-3 py-0.5 rounded-full border border-emerald-200 uppercase tracking-wider shadow-sm flex items-center justify-center gap-0.5 max-w-[170px] mx-auto">
                                    🌾 Verified Producer
                                </span>
                            </td>
                        </tr>
                    `;
                    cropsTableBody.insertAdjacentHTML('beforeend', cropRowHtml);
                });

                // Unroll the master certificate viewport panel smoothly
                document.getElementById('abvhps_organic_certificate_panel').classList.remove('hidden');
                window.scrollTo({ top: 0, behavior: 'smooth' });

            } else {
                alert('Submission Error: ' + (result.message || 'Please verify all required fields.'));
            }
        } catch (error) {
            submitBtn.disabled = false;
            submitBtn.innerText = "Submit & Generate Organic Producer Certificate";
            console.error("Farmer application submission error:", error);
            alert('Network error submitting application. Please verify your connection and try again.');
        }
    }
</script>
@endsection

