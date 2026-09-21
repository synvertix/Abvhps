@extends('layouts.app')

@section('content')
<section class="max-w-3xl mx-auto my-4 sm:my-8 p-4 sm:p-6 bg-white rounded-xl shadow border border-gray-100">
    
    <!-- 1. Form Header Banner Section -->
    <div class="border-b border-gray-100 pb-3 mb-6 text-center">
        <span class="text-xs font-bold text-brandOrange uppercase tracking-wider block">ABVHPS Volunteer Desk</span>
        <h2 class="text-xl font-black text-brandGray mt-1">Volunteer Registration Application</h2>
    </div>

    <!-- 2. Mapped Membership Verification Status Ribbon Card -->
    <div class="mb-6 p-4 bg-brandLightOrange rounded-lg border border-orange-100 flex flex-col sm:flex-row justify-between items-center gap-2">
        <div>
            <span class="text-[11px] font-bold text-gray-500 uppercase block">Verified Membership Reference ID</span>
            <span class="text-lg font-black text-brandOrange tracking-widest">{{ $mappedData['membership_id'] }}</span>
        </div>
        <div class="text-right">
            <span class="text-[11px] font-bold text-gray-500 uppercase block">Registered Phone Tracking</span>
            <span class="text-sm font-bold text-brandGray">+91 {{ $mappedData['phone'] }}</span>
        </div>
    </div>

    <!-- Main Entry Container Handling File Enctypes Securely -->
    <form action="/volunteer/submit-application" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- 3. Section A: Auto-Filled Profile Records (Locked Fields Layout) -->
        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 space-y-4">
            <h3 class="text-xs font-bold text-brandGray uppercase tracking-wider border-b border-gray-200 pb-2">Section A: Personal Profile Mapped Records</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Full Name (Locked from Membership)</label>
                    <input type="text" value="{{ $mappedData['full_name'] }}" readonly 
                        class="block w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-sm text-gray-500 font-semibold uppercase">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Blood Group (Locked from Membership)</label>
                    <input type="text" value="{{ $mappedData['blood_group'] }}" readonly 
                        class="block w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-sm text-red-500 font-bold uppercase">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Permanent Address (Locked from Membership)</label>
                    <textarea readonly rows="2" class="block w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-sm text-gray-500 uppercase">{{ $mappedData['grama_panchayat'] }}, {{ $mappedData['mandal'] }}, {{ $mappedData['assembly_segment'] }}, {{ $mappedData['district'] }}, {{ $mappedData['state'] }} - {{ $mappedData['pincode'] }}</textarea>
                </div>
            </div>
        </div>

                <!-- 4. Section B: Volunteer Core Credentials Layout Row -->
        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 space-y-4">
            <h3 class="text-xs font-bold text-brandGray uppercase tracking-wider border-b border-gray-200 pb-2">Section B: Core Profile Information</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="qualification" class="block text-xs font-bold text-brandGray uppercase mb-1">Qualification</label>
                    <input type="text" id="qualification" name="qualification" required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm text-brandGray focus:ring-brandOrange focus:border-brandOrange"
                        placeholder="E.g. B.Com, Degree">
                </div>
                <div>
                    <label for="voter_id_number" class="block text-xs font-bold text-brandGray uppercase mb-1">Voter ID Card Number</label>
                    <input type="text" id="voter_id_number" name="voter_id_number" required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm text-brandGray focus:ring-brandOrange focus:border-brandOrange uppercase"
                        placeholder="Enter Voter ID Number">
                </div>
                <div>
                    <!-- Email ID is strictly set as a Mandatory input field for volunteers -->
                    <label for="email" class="block text-xs font-bold text-brandGray uppercase mb-1">Email ID (Mandatory)</label>
                    <input type="email" id="email" name="email" required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm text-brandGray focus:ring-brandOrange focus:border-brandOrange"
                        placeholder="name@email.com">
                </div>
            </div>
        </div>

        <!-- 5. Section C: Detailed Bank Account Grid Components -->
        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 space-y-4">
            <h3 class="text-xs font-bold text-brandGray uppercase tracking-wider border-b border-gray-200 pb-2">Section C: Bank Account Verification details</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="bank_name" class="block text-xs font-bold text-brandGray uppercase mb-1">Bank Name</label>
                    <input type="text" id="bank_name" name="bank_name" required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm text-brandGray focus:ring-brandOrange focus:border-brandOrange"
                        placeholder="Enter Bank Name">
                </div>
                <div>
                    <label for="account_holder_name" class="block text-xs font-bold text-brandGray uppercase mb-1">Account Holder Name</label>
                    <input type="text" id="account_holder_name" name="account_holder_name" required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm text-brandGray focus:ring-brandOrange focus:border-brandOrange"
                        placeholder="As per Bank Passbook">
                </div>
                <div>
                    <label for="account_number" class="block text-xs font-bold text-brandGray uppercase mb-1">Account Number</label>
                    <input type="text" id="account_number" name="account_number" required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm text-brandGray focus:ring-brandOrange focus:border-brandOrange"
                        placeholder="Enter Account Number">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="ifsc_code" class="block text-xs font-bold text-brandGray uppercase mb-1">IFSC Code</label>
                        <input type="text" id="ifsc_code" name="ifsc_code" required maxlength="11"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm text-brandGray focus:ring-brandOrange focus:border-brandOrange uppercase"
                            placeholder="SBIN000XXXX">
                    </div>
                    <div>
                        <label for="branch_name" class="block text-xs font-bold text-brandGray uppercase mb-1">Branch Name</label>
                        <input type="text" id="branch_name" name="branch_name" required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm text-brandGray focus:ring-brandOrange focus:border-brandOrange"
                            placeholder="Enter Branch">
                    </div>
                </div>
            </div>
        </div>

        <!-- 6. Section Mapped Nominee Emergency Profile Details Info Row -->
        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 space-y-4">
            <h3 class="text-xs font-bold text-brandGray uppercase tracking-wider border-b border-gray-200 pb-2">Section D: Nominee Information</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="nominee_name" class="block text-xs font-bold text-brandGray uppercase mb-1">Nominee Full Name</label>
                    <input type="text" id="nominee_name" name="nominee_name" required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm text-brandGray focus:ring-brandOrange focus:border-brandOrange"
                        placeholder="Enter Nominee Name">
                </div>
                <div>
                    <label for="nominee_relation" class="block text-xs font-bold text-brandGray uppercase mb-1">Relation</label>
                    <input type="text" id="nominee_relation" name="nominee_relation" required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm text-brandGray focus:ring-brandOrange focus:border-brandOrange"
                        placeholder="E.g. Father, Wife, Mother">
                </div>
                <div>
                    <label for="nominee_phone" class="block text-xs font-bold text-brandGray uppercase mb-1">Nominee Contact Number</label>
                    <input type="tel" id="nominee_phone" name="nominee_phone" required maxlength="10" pattern="[6-9][0-9]{9}"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm text-brandGray focus:ring-brandOrange focus:border-brandOrange"
                        placeholder="10 Digit Mobile Number">
                </div>
            </div>
        </div>

                <!-- 7. Section E: Mandatory Physical Document Attachment Components -->
        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 space-y-4">
            <h3 class="text-xs font-bold text-brandGray uppercase tracking-wider border-b border-gray-200 pb-2">Section E: Required Document Uploads (PDF/Image max 2MB)</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="doc_declaration" class="block text-xs font-bold text-brandGray uppercase mb-1">1. Self Declaration Form</label>
                    <input type="file" id="doc_declaration" name="doc_declaration" accept="image/*,application/pdf" required
                        class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-orange-100 file:text-brandOrange hover:file:bg-orange-200 cursor-pointer">
                </div>
                <div>
                    <label for="doc_voter" class="block text-xs font-bold text-brandGray uppercase mb-1">2. Voter ID Card Copy</label>
                    <input type="file" id="doc_voter" name="doc_voter" accept="image/*,application/pdf" required
                        class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-orange-100 file:text-brandOrange hover:file:bg-orange-200 cursor-pointer">
                </div>
                <div>
                    <label for="doc_bank" class="block text-xs font-bold text-brandGray uppercase mb-1">3. Bank Passbook / Statement</label>
                    <input type="file" id="doc_bank" name="doc_bank" accept="image/*,application/pdf" required
                        class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-orange-100 file:text-brandOrange hover:file:bg-orange-200 cursor-pointer">
                </div>
            </div>
        </div>

        <!-- 8. Section F: Powerful Volunteer Responsibility Disclaimer Agreement Block -->
        <div class="bg-brandLightOrange p-5 rounded-lg border border-orange-100 text-xs text-gray-700 space-y-4 leading-relaxed">
            <p class="font-bold text-brandOrange uppercase tracking-wider text-[11px]">Volunteer Declaration & Sacred Duty Oath</p>
            <p class="text-gray-600 font-medium">
                I understand that selection as an ABVHPS Volunteer is a sacred responsibility to serve the nation and protect Sanatana Dharma. I declare that all bank, nominee, and personal documentation provided by me are completely authentic and legally valid.
            </p>
            
            <div class="space-y-3 pt-3 border-t border-orange-200/60">
                <!-- Compulsory Checkbox 1 -->
                <label class="flex items-start gap-3 cursor-pointer group">
                    <input type="checkbox" required 
                        class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brandOrange focus:ring-brandOrange cursor-pointer">
                    <span class="text-brandGray font-semibold group-hover:text-brandOrange transition">
                        1. I am voluntarily dedicating my service to Akhanda Bharatha Viswa Hindu Parirakshana Samiti with full consciousness and dedication, without expecting any commercial gains or force from anyone.
                    </span>
                </label>

                <!-- Compulsory Checkbox 2 -->
                <label class="flex items-start gap-3 cursor-pointer group">
                    <input type="checkbox" required 
                        class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brandOrange focus:ring-brandOrange cursor-pointer">
                    <span class="text-brandGray font-semibold group-hover:text-brandOrange transition">
                        2. I take an oath to protect Sanatana Hindu Dharma, sacred cows, poor students education, and public health setups under the divine guidelines of the central committee.
                    </span>
                </label>

                <!-- Compulsory Checkbox 3 -->
                <label class="flex items-start gap-3 cursor-pointer group">
                    <input type="checkbox" required 
                        class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brandOrange focus:ring-brandOrange cursor-pointer">
                    <span class="text-brandGray font-semibold group-hover:text-brandOrange transition">
                        3. I certify that all submitted bank account and identity attachments are genuine. If any document is found fake, the organization holds absolute rights to terminate my enrollment instantly and initiate strict civil or criminal legal actions against me.
                    </span>
                </label>
            </div>
        </div>

        <!-- 9. Final Application Form Submit Trigger Block Component -->
        @include('partials.legal-consent', ['type' => 'volunteer'])
        <div class="pt-2">
            <button type="submit"
                class="w-full flex justify-center py-3 px-4 border border-transparent text-sm font-bold rounded-md text-white bg-brandOrange hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brandOrange shadow-md transition">
                Submit Volunteer Application for Admin Approval
            </button>
        </div>



    </form>
</section>
@endsection
