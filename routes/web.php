<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RudrasenaController;
use App\Http\Controllers\KalaBrundamController;
use App\Http\Controllers\GramaSevaDalController;
use App\Http\Controllers\OrganicFarmerController;
use App\Http\Controllers\FundraisingController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\OurTeamController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\VolunteerAuthController;
use App\Http\Controllers\VolunteerController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('public.home');
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');

use App\Http\Controllers\MembershipController;

// 1. Membership OTP Verification Process Routes
Route::get('/membership', [MembershipController::class, 'showOtpForm']);
Route::post('/membership/send-otp', [MembershipController::class, 'sendOtp']);
Route::post('/membership/verify-otp', [MembershipController::class, 'verifyOtp']);

// 2. Membership Gateway Payment Process Routes
Route::get('/membership/payment', [MembershipController::class, 'showPaymentPage']);
Route::post('/membership/payment/razorpay/initiate', [MembershipController::class, 'initiateRazorpayPayment'])->name('membership.payment.razorpay.initiate');
Route::post('/membership/payment/razorpay/verify', [MembershipController::class, 'verifyRazorpayPayment'])->name('membership.payment.razorpay.verify');

// 2b. Membership Identity Verification Routes (Any ONE of 5 methods)
Route::get('/membership/identity', [MembershipController::class, 'showIdentityPage'])->name('membership.identity');
Route::post('/membership/identity/pan/verify', [MembershipController::class, 'verifyPanIdentity'])->name('membership.identity.pan.verify')->middleware('throttle:10,1');
Route::post('/membership/identity/voter-id/verify', [MembershipController::class, 'verifyVoterIdIdentity'])->name('membership.identity.voter.verify')->middleware('throttle:10,1');
Route::post('/membership/identity/driving-license/verify', [MembershipController::class, 'verifyDrivingLicenceIdentity'])->name('membership.identity.dl.verify')->middleware('throttle:10,1');
Route::post('/membership/identity/passport/verify', [MembershipController::class, 'verifyPassportIdentity'])->name('membership.identity.passport.verify')->middleware('throttle:10,1');

// 3. Render Membership Final Data Registration Form Desk
Route::get('/membership/application', [MembershipController::class, 'showApplicationForm']);
Route::post('/membership/verify-aadhaar', [MembershipController::class, 'startAadhaarVerification'])->name('membership.verify_aadhaar');
Route::post('/membership/aadhaar/start', [MembershipController::class, 'startAadhaarVerification'])->name('membership.aadhaar.start');
Route::get('/membership/aadhaar/callback', [MembershipController::class, 'handleAadhaarCallback'])->name('membership.aadhaar.callback');
Route::get('/membership/aadhaar/status', [MembershipController::class, 'checkAadhaarStatus'])->name('membership.aadhaar.status');

// 4. Secure Form Submission Desk Mapped to url('/submit-membership')
Route::post('/submit-membership', [MembershipController::class, 'submitApplication']);

// 5. Display and Print Generated Membership PVC ID Card View Screen
Route::get('/membership/view-card', [MembershipController::class, 'viewCard']);

// Volunteer Identity Check Verification Routing Rules
Route::get('/volunteer', [VolunteerController::class, 'showCheckForm'])->name('volunteer.check');
Route::post('/volunteer/verify-membership', [VolunteerController::class, 'verifyMembership'])->name('volunteer.verify');
Route::get('/volunteer/application', [VolunteerController::class, 'showApplicationForm'])->name('volunteer.application');

// Dynamic view verification test endpoint mapping
Route::get('/volunteer/application-placeholder-test', function() {
    return 'Volunteer Form Framework - Pending Configuration Stage';
});

// Volunteer Form Data Submission & Success Routing
Route::post('/volunteer/submit-application', [VolunteerController::class, 'submitApplication']);
Route::get('/volunteer/success-notice', [VolunteerController::class, 'showSuccessNotice']);

// Central Admin Panel Volunteer Desk Routes Configuration Setup (Redirect legacy desk to unified index)
Route::redirect('/admin/volunteer-desk', '/admin/volunteers');
Route::post('/admin/volunteer/approve', [VolunteerController::class, 'updateVolunteerStatus']);
Route::get('/admin/volunteer/view-card/{volunteerIdCode}', [VolunteerController::class, 'viewVolunteerCard'])->name('admin.volunteer.view_card');

// ==========================================
// DEDICATED VOLUNTEER AUTHENTICATION & PORTAL ROUTES
// ==========================================
Route::get('/volunteer/login', [VolunteerAuthController::class, 'showLoginForm'])->name('volunteer.login');
Route::post('/volunteer/login', [VolunteerAuthController::class, 'login'])->name('volunteer.login.submit');
Route::post('/volunteer/process-login', [VolunteerAuthController::class, 'login']);
Route::get('/volunteer/forgot-password', [VolunteerAuthController::class, 'showForgotPasswordForm'])->name('volunteer.forgot_password');
Route::post('/volunteer/forgot-password', [VolunteerAuthController::class, 'sendResetLink'])->name('volunteer.forgot_password.submit');
Route::get('/volunteer/reset-password/{token}', [VolunteerAuthController::class, 'showResetPasswordForm'])->name('volunteer.reset_password');
Route::post('/volunteer/reset-password', [VolunteerAuthController::class, 'resetPassword'])->name('volunteer.reset_password.submit');

Route::middleware('volunteer.auth')->group(function () {
    Route::get('/volunteer/change-password', [VolunteerAuthController::class, 'showChangePasswordForm'])->name('volunteer.change_password');
    Route::post('/volunteer/change-password', [VolunteerAuthController::class, 'changePassword'])->name('volunteer.change_password.submit');
    Route::post('/volunteer/logout', [VolunteerAuthController::class, 'logout'])->name('volunteer.logout');
    Route::get('/volunteer/logout', [VolunteerAuthController::class, 'logout']);

    Route::middleware('volunteer.password')->group(function () {
        Route::get('/volunteer/dashboard', [VolunteerAuthController::class, 'dashboard'])->name('volunteer.dashboard');
        Route::get('/volunteer/profile', [VolunteerAuthController::class, 'profile'])->name('volunteer.profile');
        
        // Volunteer Portal Area-Wise Member Data Explorer & Exports
        Route::get('/volunteer/member-data', [\App\Http\Controllers\VolunteerMemberDataController::class, 'index'])->name('volunteer.member_data');
        Route::get('/volunteer/member-data/areas', [\App\Http\Controllers\VolunteerMemberDataController::class, 'getAreas'])->name('volunteer.member_data.areas');
        Route::post('/volunteer/member-data/search', [\App\Http\Controllers\VolunteerMemberDataController::class, 'search'])->name('volunteer.member_data.search');
        Route::post('/volunteer/member-data/export-pdf', [\App\Http\Controllers\VolunteerMemberDataController::class, 'exportPdf'])->name('volunteer.member_data.export_pdf');
        Route::post('/volunteer/member-data/export-csv', [\App\Http\Controllers\VolunteerMemberDataController::class, 'exportCsv'])->name('volunteer.member_data.export_csv');

        // Volunteer Events & Beneficiary Management
        Route::get('/volunteer/events', [\App\Http\Controllers\VolunteerEventController::class, 'index'])->name('volunteer.events.index');
        Route::get('/volunteer/events/create', [\App\Http\Controllers\VolunteerEventController::class, 'create'])->name('volunteer.events.create');
        Route::post('/volunteer/events', [\App\Http\Controllers\VolunteerEventController::class, 'store'])->name('volunteer.events.store');
        Route::get('/volunteer/events/{id}', [\App\Http\Controllers\VolunteerEventController::class, 'show'])->name('volunteer.events.show');
        Route::get('/volunteer/events/{id}/edit', [\App\Http\Controllers\VolunteerEventController::class, 'edit'])->name('volunteer.events.edit');
        Route::put('/volunteer/events/{id}', [\App\Http\Controllers\VolunteerEventController::class, 'update'])->name('volunteer.events.update');
        Route::post('/volunteer/events/{id}/add-member', [\App\Http\Controllers\VolunteerEventController::class, 'addMember'])->name('volunteer.events.add_member');
        Route::put('/volunteer/events/{id}/members/{memberId}', [\App\Http\Controllers\VolunteerEventController::class, 'updateMember']);
        Route::post('/volunteer/events/{id}/members/{memberId}/update', [\App\Http\Controllers\VolunteerEventController::class, 'updateMember'])->name('volunteer.events.update_member');
        Route::delete('/volunteer/events/{id}/members/{memberId}', [\App\Http\Controllers\VolunteerEventController::class, 'removeMember'])->name('volunteer.events.remove_member');

        // Volunteer Exact Member Search Desk & JSON Lookup
        Route::get('/volunteer/member-search', [\App\Http\Controllers\VolunteerEventController::class, 'memberSearchDesk'])->name('volunteer.member_search');
        Route::post('/volunteer/member-search/lookup', [\App\Http\Controllers\VolunteerEventController::class, 'searchMember'])->name('volunteer.member_search.lookup');
    });
});

// Village President Dashboard Search and Compression Routing Engine Map Links Setup
Route::get('/volunteer/dashboard/village', [VolunteerController::class, 'showVillageDashboard']);
Route::post('/volunteer/dashboard/village/search-member', [VolunteerController::class, 'searchMember']);
Route::post('/volunteer/dashboard/village/deliver-seva', [VolunteerController::class, 'deliverSeva']);

// Mandal President Dashboard Core Mapping Link Routing Rule
Route::get('/volunteer/dashboard/mandal', [VolunteerController::class, 'showMandalDashboard']);

// Assembly Segment President Dashboard Core Mapping Link Routing Rule
Route::get('/volunteer/dashboard/assembly', [VolunteerController::class, 'showAssemblyDashboard']);

// District President Dashboard Core Mapping Link Routing Rule
Route::get('/volunteer/dashboard/district', [VolunteerController::class, 'showDistrictDashboard']);

// Global Master Dashboard Pipeline Mapping Link Routing Rule
Route::get('/volunteer/dashboard/global', [VolunteerController::class, 'showGlobalDashboard']);

// Village President Group Event Album Upload Route Link Setup
Route::post('/volunteer/dashboard/village/upload-group-event', [VolunteerController::class, 'uploadGroupEvent']);


use App\Http\Controllers\ExamController;

// Exam Application System Form Route
Route::get('/exam-application', [ExamController::class, 'showApplicationForm'])->name('exam.form');

// Security & Verification Channels
Route::post('/exam-application/send-otp', [ExamController::class, 'sendEmailOtp'])->name('exam.send_otp');
Route::post('/exam-application/verify-otp', [ExamController::class, 'verifyEmailOtp'])->name('exam.verify_otp');
Route::post('/exam-application/check-membership', [ExamController::class, 'checkMembershipId'])->name('exam.check_membership');

// Anti-Fraud Payment Delivery Engine & Success Handlers
Route::post('/exam-application/process-payment', [ExamController::class, 'processApplicationPayment'])->name('exam.process_payment');
Route::post('/exam-application/submit', [ExamController::class, 'submitFinalApplication'])->name('exam.submit');

// Post-Submission Digital Desks
Route::get('/exam-application/success/{id}', [ExamController::class, 'showSuccessNotice'])->name('exam.success');
Route::get('/exam-application/download-syllabus/{id}', [ExamController::class, 'downloadSyllabusPdf'])->name('exam.download_syllabus');
Route::get('/exam-application/{id}/syllabus', [ExamController::class, 'downloadSyllabusPdf'])->name('exam.download_syllabus.alias');

// Central Exam Results Portal & Winners Showcase Desks
Route::get('/exam-results', [ExamController::class, 'showResultsPortal'])->name('exam.results_portal');
Route::post('/exam-results/search', [ExamController::class, 'searchStudentResult'])->name('exam.results_search');

// Rudrasena Dal Sacred Registration Wing Core Pipelines
Route::get('/rudrasena-apply', [RudrasenaController::class, 'showApplicationDesk'])->name('rudrasena.form');
Route::post('/rudrasena-apply/verify-member', [RudrasenaController::class, 'verifyCoreMembership'])->name('rudrasena.verify_member');
Route::post('/rudrasena-apply/submit', [RudrasenaController::class, 'submitApplicationPacket'])->name('rudrasena.submit');

// Kala Brundam Cultural Network Core Pipelines
Route::get('/kala-brundam-apply', [KalaBrundamController::class, 'showApplicationDesk'])->name('kalabrundam.form');
Route::post('/kala-brundam-apply/fetch-member', [KalaBrundamController::class, 'fetchMemberForTeam'])->name('kalabrundam.fetch_member');
Route::post('/kala-brundam-apply/submit', [KalaBrundamController::class, 'submitTeamPacket'])->name('kalabrundam.submit');

// Grama Seva Dal Youth Network Core Pipelines
Route::get('/grama-seva-dal-apply', [GramaSevaDalController::class, 'showApplicationDesk'])->name('gramasevadal.form');
Route::post('/grama-seva-dal-apply/fetch-member', [GramaSevaDalController::class, 'fetchMemberForDal'])->name('gramasevadal.fetch_member');
Route::post('/grama-seva-dal-apply/submit', [GramaSevaDalController::class, 'submitDalPacket'])->name('gramasevadal.submit');

// Organic Farmers Agriculture Network Core Pipelines
Route::get('/organic-farmers-apply', [OrganicFarmerController::class, 'showApplicationDesk'])->name('organicfarmers.form');
Route::post('/organic-farmers-apply/fetch-member', [OrganicFarmerController::class, 'fetchMemberForFarming'])->name('organicfarmers.fetch_member');
Route::post('/organic-farmers-apply/submit', [OrganicFarmerController::class, 'submitFarmerPacket'])->name('organicfarmers.submit');

// ======================================================================
// 💰 PUBLIC DONATION PAGE — Fundraising Campaigns + Donation Form
// ======================================================================
// GET /donations — main page showing campaigns + donation form
Route::get('/donations', [FundraisingController::class, 'showDonationsGrid'])->name('donations.grid');
// Dedicated single-campaign URL (for social sharing / OG meta)
Route::get('/donations/campaign/{id}', [FundraisingController::class, 'showCampaign'])->name('donations.campaign');

// Payment initiation (server-side order creation — returns JSON)
// CSRF protected — called via JS Fetch with X-CSRF-TOKEN header
Route::post('/donations/initiate-cashfree', [DonationController::class, 'initiateCashfreePayment'])->name('donations.initiate_cashfree');
Route::post('/donations/initiate-razorpay', [DonationController::class, 'initiateRazorpayPayment'])->name('donations.initiate_razorpay');

// Razorpay browser-callback server-side signature verification
Route::post('/donations/verify-razorpay', [DonationController::class, 'verifyRazorpayPayment'])->name('donations.verify_razorpay');

// Payment return redirects (gateway redirects browser here)
Route::get('/donations/cashfree-return', [DonationController::class, 'cashfreeReturn'])->name('donations.cashfree_return');
Route::get('/donations/razorpay-return', [DonationController::class, 'razorpayReturn'])->name('donations.razorpay_return');

// Payment status page (server-driven, no trust in URL params)
Route::get('/donations/status/{id}', [DonationController::class, 'paymentStatus'])->name('donations.status');

// Donation receipt (accessible from status page)
Route::get('/donations/receipt/{id}', [DonationController::class, 'downloadReceipt'])->name('donations.receipt');

// ======================================================================
// 🔔 PAYMENT GATEWAY WEBHOOKS (CSRF excluded — see bootstrap/app.php)
// These endpoints must be registered in Cashfree + Razorpay dashboards:
//   Cashfree:  https://abvhps.org/webhook/cashfree
//   Razorpay:  https://abvhps.org/webhook/razorpay
// ======================================================================
Route::post('/webhook/cashfree', [DonationController::class, 'handleCashfreeWebhook'])->name('webhook.cashfree');
Route::post('/webhook/razorpay', [DonationController::class, 'handleRazorpayWebhook'])->name('webhook.razorpay');

// Admin fundraising create/store (kept here, also duplicated in admin group below)
Route::get('/admin/fundraising/create', [FundraisingController::class, 'showCreateForm'])->name('admin.fundraising.create');
Route::post('/admin/fundraising/store', [FundraisingController::class, 'storeCampaignPacket'])->name('admin.fundraising.store');

// ======================================================================
// 👑 CENTRAL AUTHENTIC ADMINISTRATIVE CONTROL ROUTE PIPELINES
// ======================================================================

// 1. PUBLIC UNPROTECTED GATEWAYS (Accessible without any active login session)
Route::get('/admin', function () {
    return redirect()->route('login');
})->name('admin.entry');
Route::get('/admin/login', [AdminAuthController::class, 'showLoginView'])->name('login');
Route::post('/admin/login', [AdminAuthController::class, 'executeAuthentication'])->name('admin.login.submit');

// PUBLIC ROSTER LOOKUP ENGINE: Accessible to all public devotees and guests globally
Route::get('/admin/our-team', [OurTeamController::class, 'index'])->name('admin.our_team.index');
Route::get('/verify-member/{membership_id}', [\App\Http\Controllers\OurTeamController::class, 'publicLiveVerification'])->name('member.public_verify');


// 2. PROTECTED ADMINISTRATIVE BOARD GATEWAYS (Strictly requires valid logged-in commander session)
Route::middleware(['auth:web'])->prefix('admin')->name('admin.')->group(function () {
    
    // Core Administrative Dashboard Entry Point Node
    Route::get('/dashboard', [AdminDashboardController::class, 'showMasterDashboard'])->name('dashboard');
    Route::post('/logout', [AdminAuthController::class, 'executeSessionTermination'])->name('logout');

    // ------------------------------------------------------------------
    // MODULE 1: OUR TEAM - ADMINISTRATIVE MANIPULATION CORE (SECURE WRITE ACTIONS)
    // ------------------------------------------------------------------
    Route::get('/our-team/create', [OurTeamController::class, 'create'])->name('our_team.create');
    Route::post('/our-team/store', [OurTeamController::class, 'store'])->name('our_team.store');
    Route::get('/our-team/{id}/edit', [OurTeamController::class, 'edit'])->name('our_team.edit');
    Route::post('/our-team/{id}/update', [OurTeamController::class, 'update'])->name('our_team.update');
    Route::post('/our-team/{id}/delete', [OurTeamController::class, 'destroy'])->name('our_team.destroy');

 
    // Public Anti-Fraud QR Verification Gateway lookup link node
    Route::get('/verify-member/{membership_id}', [\App\Http\Controllers\PublicVerificationController::class, 'verifyMembership'])->name('member.public_verify');

});
    
// ======================================================================
// 🌐 PUBLIC ENTITY-SPECIFIC QR VERIFICATION ARCHITECTURE
// ======================================================================
Route::get('/verify/membership/{id}', [\App\Http\Controllers\PublicVerificationController::class, 'verifyMembership'])->name('verify.membership');
Route::get('/verify/volunteer/{id}', [\App\Http\Controllers\PublicVerificationController::class, 'verifyVolunteer'])->name('verify.volunteer');
Route::get('/verify/rudrasena/{id}', [\App\Http\Controllers\PublicVerificationController::class, 'verifyRudrasena'])->name('verify.rudrasena');
Route::get('/verify/exam/{hallTicket}', [\App\Http\Controllers\PublicVerificationController::class, 'verifyExam'])->name('verify.exam');
Route::get('/verify/organic-farmers/{groupId}', [\App\Http\Controllers\PublicVerificationController::class, 'verifyOrganicFarmers'])->name('verify.organic_farmers');
Route::get('/verify/kala-brundham/{groupId}', [\App\Http\Controllers\PublicVerificationController::class, 'verifyKalaBrundham'])->name('verify.kala_brundham');
Route::get('/verify/grama-seva-dal/{groupId}', [\App\Http\Controllers\PublicVerificationController::class, 'verifyGramaSevaDal'])->name('verify.grama_seva_dal');

// Backward-Compatibility Aliases
Route::get('/verify-member/{membership_id}', [\App\Http\Controllers\PublicVerificationController::class, 'verifyMembership']);
Route::get('/verify-volunteer/{id}', [\App\Http\Controllers\PublicVerificationController::class, 'verifyVolunteer']);

   // ======================================================================
// 📜 CENTRAL DONATION LEDGER INDEPENDENT PIPELINES (OUTSIDE GROUP)
// ======================================================================
Route::get('/admin/donations', [\App\Http\Controllers\DonationController::class, 'index'])->name('admin.donation.index');
Route::get('/admin/donations/{id}/receipt', [\App\Http\Controllers\DonationController::class, 'downloadReceipt'])->name('admin.donation.receipt');

// ======================================================================
// 📝 CENTRAL BLOGS MANAGEMENT INDEPENDENT PIPELINES (OUTSIDE GROUP)
// ======================================================================
Route::get('/admin/blogs', [\App\Http\Controllers\BlogController::class, 'index'])->name('admin.blog.index');
Route::get('/admin/blogs/create', [\App\Http\Controllers\BlogController::class, 'create'])->name('admin.blog.create');
Route::post('/admin/blogs/store', [\App\Http\Controllers\BlogController::class, 'store'])->name('admin.blog.store');
Route::get('/admin/blogs/{id}/edit', [\App\Http\Controllers\BlogController::class, 'edit'])->name('admin.blog.edit');
Route::post('/admin/blogs/{id}/update', [\App\Http\Controllers\BlogController::class, 'update'])->name('admin.blog.update');
Route::post('/admin/blogs/{id}/delete', [\App\Http\Controllers\BlogController::class, 'destroy'])->name('admin.blog.destroy');

// ======================================================================
// 🖼️ CENTRAL GALLERY HUB INDEPENDENT PIPELINES (OUTSIDE GROUP)
// ======================================================================
Route::get('/admin/gallery', [\App\Http\Controllers\GalleryController::class, 'index'])->name('admin.gallery.index');
Route::post('/admin/gallery/store', [\App\Http\Controllers\GalleryController::class, 'store'])->name('admin.gallery.store');
Route::post('/admin/gallery/{id}/delete', [\App\Http\Controllers\GalleryController::class, 'destroy'])->name('admin.gallery.destroy');

// ======================================================================
// 🤝 OUR SUPPORT CORE MISSIONS INDEPENDENT PIPELINES (OUTSIDE GROUP)
// ======================================================================
Route::get('/admin/our-supports', [\App\Http\Controllers\OurSupportController::class, 'index'])->name('admin.our_support.index');
Route::get('/admin/our-supports/create', [\App\Http\Controllers\OurSupportController::class, 'create'])->name('admin.our_support.create');
Route::post('/admin/our-supports/store', [\App\Http\Controllers\OurSupportController::class, 'store'])->name('admin.our_support.store');
Route::get('/admin/our-supports/{id}/edit', [\App\Http\Controllers\OurSupportController::class, 'edit'])->name('admin.our_supports.edit');
Route::post('/admin/our-supports/{id}/update', [\App\Http\Controllers\OurSupportController::class, 'update'])->name('admin.our_supports.update');
Route::post('/admin/our-supports/{id}/delete', [\App\Http\Controllers\OurSupportController::class, 'destroy'])->name('admin.our_supports.destroy');

Route::get('/admin/membership-ledger', [App\Http\Controllers\MembershipController::class, 'adminIndex'])->name('admin.membership.ledger')->middleware('auth:web');

    // 🔱 ABVHPS CENTRAL ADMINISTRATIVE PANEL 15 CORE ROUTES MATRIX
    // ----------------------------------------------------------------------
         // 1. Our Team Management Module Routes
    Route::get('/admin/team', [App\Http\Controllers\OurTeamController::class, 'index'])->name('admin.team.index')->middleware('auth:web');
    Route::get('/admin/team/create', [App\Http\Controllers\OurTeamController::class, 'create'])->name('admin.team.create')->middleware('auth:web');
    Route::get('/our-team-members', [App\Http\Controllers\HomeController::class, 'team'])->name('public.team_alias');

        // 2. Donation Ledger Module Routes (Connected to Official Donation Controller)
    Route::get('/admin/donations', [App\Http\Controllers\DonationController::class, 'index'])->name('admin.donations.index')->middleware('auth:web');
    Route::get('/admin/donations/receipt/{id}', [App\Http\Controllers\DonationController::class, 'downloadReceipt'])->name('admin.donations.receipt')->middleware('auth:web');

        // 3. Blogs Management Module Routes (Fixed Route Names)
    Route::get('/admin/blogs', [App\Http\Controllers\BlogController::class, 'index'])->name('admin.blogs.index')->middleware('auth:web');
    Route::get('/admin/blogs/create', [App\Http\Controllers\BlogController::class, 'create'])->name('admin.blogs.create')->middleware('auth:web');
    Route::post('/admin/blogs/store', [App\Http\Controllers\BlogController::class, 'store'])->name('admin.blogs.store')->middleware('auth:web');
    Route::get('/admin/blogs/edit/{id}', [App\Http\Controllers\BlogController::class, 'edit'])->name('admin.blogs.edit')->middleware('auth:web');
    Route::post('/admin/blogs/update/{id}', [App\Http\Controllers\BlogController::class, 'update'])->name('admin.blogs.update')->middleware('auth:web');
    Route::delete('/admin/blogs/delete/{id}', [App\Http\Controllers\BlogController::class, 'destroy'])->name('admin.blogs.delete')->middleware('auth:web');
    
    // 4. Gallery Media Module Routes (Connected to Authentic Gallery Controller)
    Route::get('/admin/gallery', [App\Http\Controllers\GalleryController::class, 'index'])->name('admin.gallery.index')->middleware('auth:web');
    Route::post('/admin/gallery/store', [App\Http\Controllers\GalleryController::class, 'store'])->name('admin.gallery.store')->middleware('auth:web');
    Route::delete('/admin/gallery/delete/{id}', [App\Http\Controllers\GalleryController::class, 'destroy'])->name('admin.gallery.delete')->middleware('auth:web');
    Route::get('/gallery', [App\Http\Controllers\HomeController::class, 'gallery'])->name('public.gallery');

        // 5. Our Support Cores Module Routes (Connected to Authentic OurSupport Controller)
    Route::get('/admin/support', [App\Http\Controllers\OurSupportController::class, 'index'])->name('admin.support.index')->middleware('auth:web');
    Route::get('/admin/support/create', [App\Http\Controllers\OurSupportController::class, 'create'])->name('admin.support.create')->middleware('auth:web');
    Route::post('/admin/support/store', [App\Http\Controllers\OurSupportController::class, 'store'])->name('admin.support.store')->middleware('auth:web');
    Route::get('/admin/support/edit/{id}', [App\Http\Controllers\OurSupportController::class, 'edit'])->name('admin.support.edit')->middleware('auth:web');
    Route::post('/admin/support/update/{id}', [App\Http\Controllers\OurSupportController::class, 'update'])->name('admin.support.update')->middleware('auth:web');
    Route::delete('/admin/support/delete/{id}', [App\Http\Controllers\OurSupportController::class, 'destroy'])->name('admin.support.delete')->middleware('auth:web');

        // 6. Approved Membership Individual Actions Routes
    Route::get('/admin/membership/view/{id}', [App\Http\Controllers\MembershipController::class, 'viewProfile'])->name('admin.membership.view')->middleware('auth:web');
    Route::get('/admin/membership/idcard/{id}', [App\Http\Controllers\MembershipController::class, 'downloadIdCard'])->name('admin.membership.idcard')->middleware('auth:web');
    Route::get('/admin/membership/edit/{id}', [App\Http\Controllers\MembershipController::class, 'editProfile'])->name('admin.membership.edit')->middleware('auth:web');
    Route::post('/admin/membership/update/{id}', [App\Http\Controllers\MembershipController::class, 'updateProfile'])->name('admin.membership.update')->middleware('auth:web');
    Route::delete('/admin/membership/delete/{id}', [App\Http\Controllers\MembershipController::class, 'deleteProfile'])->name('admin.membership.delete')->middleware('auth:web');

    // 7. Pending Membership List
    Route::get('/admin/membership-pending', [App\Http\Controllers\MembershipController::class, 'pendingIndex'])->name('admin.membership.pending')->middleware('auth:web');

    // 8. Volunteer Desk Management
    Route::get('/admin/volunteers/live', [VolunteerController::class, 'liveSync'])->name('admin.volunteers.live')->middleware('auth:web');
    Route::get('/admin/volunteers', [VolunteerController::class, 'adminIndex'])->name('admin.volunteers.index')->middleware('auth:web');
    Route::get('/admin/volunteers/view/{id}', [VolunteerController::class, 'viewProfile'])->name('admin.volunteers.view')->middleware('auth:web');
    Route::get('/admin/volunteers/edit/{id}', [VolunteerController::class, 'editFull'])->name('admin.volunteers.edit')->middleware('auth:web');
    Route::post('/admin/volunteers/update/{id}', [VolunteerController::class, 'updateFull'])->name('admin.volunteers.update')->middleware('auth:web');
    Route::post('/admin/volunteers/update-full/{id}', [VolunteerController::class, 'updateFull'])->name('admin.volunteers.updateFull')->middleware('auth:web');
    Route::get('/admin/volunteers/cadre/{id}', [VolunteerController::class, 'cadreEditForm'])->name('admin.volunteers.cadreEdit')->middleware('auth:web');
    Route::post('/admin/volunteers/cadre/{id}', [VolunteerController::class, 'cadreUpdate'])->name('admin.volunteers.cadreUpdate')->middleware('auth:web');
    Route::post('/admin/volunteers/resend-credentials/{id}', [VolunteerController::class, 'resendCredentials'])->name('admin.volunteers.resendCredentials')->middleware('auth:web');
    Route::delete('/admin/volunteers/delete/{id}', [VolunteerController::class, 'deleteVolunteer'])->name('admin.volunteers.delete')->middleware('auth:web');

    // 8b. Volunteer Events Management (Central Admin)
    Route::get('/admin/volunteer-events', [\App\Http\Controllers\AdminVolunteerEventController::class, 'index'])->name('admin.volunteer_events.index')->middleware('auth:web');
    Route::get('/admin/volunteer-events/{id}', [\App\Http\Controllers\AdminVolunteerEventController::class, 'show'])->name('admin.volunteer_events.show')->middleware('auth:web');
    Route::post('/admin/volunteer-events/{id}/members/{memberLinkId}/replace-proof', [\App\Http\Controllers\AdminVolunteerEventController::class, 'replaceProofImage'])->name('admin.volunteer_events.replace_proof')->middleware('auth:web');
    Route::delete('/admin/volunteer-events/{id}', [\App\Http\Controllers\AdminVolunteerEventController::class, 'destroy'])->name('admin.volunteer_events.delete')->middleware('auth:web');

    // 9. Rudrasena
    Route::get('/admin/rudrasena', [App\Http\Controllers\RudrasenaController::class, 'adminIndex'])->name('admin.rudrasena.index')->middleware('auth:web');
    Route::get('/admin/rudrasena/view/{id}', [App\Http\Controllers\RudrasenaController::class, 'viewMember'])->name('admin.rudrasena.view')->middleware('auth:web');
    Route::get('/admin/rudrasena/view-card/{id}', [App\Http\Controllers\RudrasenaController::class, 'viewCard'])->name('admin.rudrasena.view_card')->middleware('auth:web');
    Route::get('/admin/rudrasena/edit/{id}', [App\Http\Controllers\RudrasenaController::class, 'editMemberForm'])->name('admin.rudrasena.edit')->middleware('auth:web');
    Route::post('/admin/rudrasena/update/{id}', [App\Http\Controllers\RudrasenaController::class, 'updateMember'])->name('admin.rudrasena.update')->middleware('auth:web');
    Route::post('/admin/rudrasena/approve/{id}', [App\Http\Controllers\RudrasenaController::class, 'approveMember'])->name('admin.rudrasena.approve')->middleware('auth:web');
    Route::delete('/admin/rudrasena/delete/{id}', [App\Http\Controllers\RudrasenaController::class, 'deleteMember'])->name('admin.rudrasena.delete')->middleware('auth:web');

    // 10. Kala Brundam, Grama Seva Dal, Organic Farmers Local GP Gateway
    Route::get('/admin/local-gateways', [App\Http\Controllers\LocalGatewayController::class, 'index'])->name('admin.local_gateways.index')->middleware('auth:web');
    Route::post('/admin/local-gateways/approve/{wing}/{id}', [App\Http\Controllers\LocalGatewayController::class, 'approveGroup'])->name('admin.local_gateways.approve')->middleware('auth:web');
    Route::get('/admin/local-gateways/view/{wing}/{id}', [App\Http\Controllers\LocalGatewayController::class, 'viewGroup'])->name('admin.local_gateways.view')->middleware('auth:web');
    Route::delete('/admin/local-gateways/delete/{wing}/{id}', [App\Http\Controllers\LocalGatewayController::class, 'destroyGroup'])->name('admin.local_gateways.delete')->middleware('auth:web');

    // 11. Exams Information Notice Board Loop
    Route::get('/admin/exams', [App\Http\Controllers\ExamController::class, 'adminIndex'])->name('admin.exams.index')->middleware('auth:web');
    Route::get('/admin/exams/create', [App\Http\Controllers\ExamController::class, 'adminCreate'])->name('admin.exams.create')->middleware('auth:web');
    Route::post('/admin/exams/store', [App\Http\Controllers\ExamController::class, 'adminStore'])->name('admin.exams.store')->middleware('auth:web');
    Route::get('/admin/exams/edit/{id}', [App\Http\Controllers\ExamController::class, 'adminEdit'])->name('admin.exams.edit')->middleware('auth:web');
    Route::post('/admin/exams/update/{id}', [App\Http\Controllers\ExamController::class, 'adminUpdate'])->name('admin.exams.update')->middleware('auth:web');
    Route::delete('/admin/exams/delete/{id}', [App\Http\Controllers\ExamController::class, 'adminDelete'])->name('admin.exams.delete')->middleware('auth:web');
    Route::get('/exams-notice-board', [App\Http\Controllers\ExamController::class, 'publicNoticeBoard'])->name('public.exams_board');

    // 11b. Exam Result Management Routes (Result Entry Desk)
    Route::get('/admin/exams/{id}/results',          [App\Http\Controllers\ExamController::class, 'adminResultsIndex'])->name('admin.exams.results')->middleware('auth:web');
    Route::post('/admin/exams/results/{appId}/save', [App\Http\Controllers\ExamController::class, 'adminResultSave'])->name('admin.exams.results.save')->middleware('auth:web');
    Route::post('/admin/exams/{id}/publish-results', [App\Http\Controllers\ExamController::class, 'adminPublishResults'])->name('admin.exams.publish_results')->middleware('auth:web');
    Route::post('/admin/exams/{id}/unpublish-results',[App\Http\Controllers\ExamController::class, 'adminUnpublishResults'])->name('admin.exams.unpublish_results')->middleware('auth:web');

    // 12. Fundraise Multi-Campaign Media Block
    Route::get('/admin/fundraising', [App\Http\Controllers\FundraisingController::class, 'adminIndex'])->name('admin.fundraising.index')->middleware('auth:web');
    Route::get('/admin/fundraising/create', [App\Http\Controllers\FundraisingController::class, 'showCreateForm'])->name('admin.fundraising.create')->middleware('auth:web');
    Route::post('/admin/fundraising/store', [App\Http\Controllers\FundraisingController::class, 'storeCampaignPacket'])->name('admin.fundraising.store')->middleware('auth:web');
    Route::get('/admin/fundraising/edit/{id}', [App\Http\Controllers\FundraisingController::class, 'showEditForm'])->name('admin.fundraising.edit')->middleware('auth:web');
    Route::post('/admin/fundraising/update/{id}', [App\Http\Controllers\FundraisingController::class, 'updateCampaign'])->name('admin.fundraising.update')->middleware('auth:web');
    Route::post('/admin/fundraising/toggle/{id}', [App\Http\Controllers\FundraisingController::class, 'toggleStatus'])->name('admin.fundraising.toggle')->middleware('auth:web');
    Route::delete('/admin/fundraising/delete/{id}', [App\Http\Controllers\FundraisingController::class, 'destroyCampaign'])->name('admin.fundraising.delete')->middleware('auth:web');

    // 13. Contact Forms Audit Tracker
    Route::get('/admin/contacts', [App\Http\Controllers\ContactController::class, 'adminIndex'])->name('admin.contacts.index')->middleware('auth:web');
    Route::get('/admin/contacts/view/{id}', [App\Http\Controllers\ContactController::class, 'adminView'])->name('admin.contacts.view')->middleware('auth:web');
    Route::post('/admin/contacts/{id}/status', [App\Http\Controllers\ContactController::class, 'adminUpdateStatus'])->name('admin.contacts.status')->middleware('auth:web');
    Route::post('/admin/contacts/{id}/notes', [App\Http\Controllers\ContactController::class, 'adminSaveNotes'])->name('admin.contacts.notes')->middleware('auth:web');
    Route::delete('/admin/contacts/delete/{id}', [App\Http\Controllers\ContactController::class, 'adminDelete'])->name('admin.contacts.delete')->middleware('auth:web');
    Route::get('/contact', [App\Http\Controllers\ContactController::class, 'showContactPage'])->name('public.contact');
    Route::post('/contact/submit', [App\Http\Controllers\ContactController::class, 'submitContact'])->name('public.contact.submit');

    // 14. ABVHPS Donation & Tax Certificates Core Gateway
    Route::get('/admin/certificates', [App\Http\Controllers\CertificateController::class, 'adminIndex'])->name('admin.certificates.index')->middleware('auth:web');
    Route::post('/admin/certificates/store', [App\Http\Controllers\CertificateController::class, 'adminStore'])->name('admin.certificates.store')->middleware('auth:web');
    Route::post('/admin/certificates/toggle/{id}', [App\Http\Controllers\CertificateController::class, 'adminToggle'])->name('admin.certificates.toggle')->middleware('auth:web');
    Route::delete('/admin/certificates/delete/{id}', [App\Http\Controllers\CertificateController::class, 'adminDelete'])->name('admin.certificates.delete')->middleware('auth:web');
    Route::get('/compliance-certificates', [App\Http\Controllers\CertificateController::class, 'publicIndex'])->name('public.certificates');

    // 15. Site Settings Central Desk Engine
    Route::get('/admin/settings', [App\Http\Controllers\SettingController::class, 'adminIndex'])->name('admin.settings.index')->middleware('auth:web');
    Route::post('/admin/settings/update', [App\Http\Controllers\SettingController::class, 'adminUpdate'])->name('admin.settings.update')->middleware('auth:web');

    // 16. Page-Wise Banner Management Module
    Route::get('/admin/banner', [App\Http\Controllers\BannerController::class, 'index'])->name('admin.banner.index')->middleware('auth:web');
    Route::get('/admin/banners', [App\Http\Controllers\BannerController::class, 'index'])->name('admin.banners.index')->middleware('auth:web');
    Route::get('/admin/banner/create', [App\Http\Controllers\BannerController::class, 'create'])->name('admin.banner.create')->middleware('auth:web');
    Route::post('/admin/banner/store', [App\Http\Controllers\BannerController::class, 'store'])->name('admin.banner.store')->middleware('auth:web');
    Route::get('/admin/banner/edit/{id}', [App\Http\Controllers\BannerController::class, 'edit'])->name('admin.banner.edit')->middleware('auth:web');
    Route::get('/admin/banner/{id}/edit', [App\Http\Controllers\BannerController::class, 'edit'])->middleware('auth:web');
    Route::post('/admin/banner/update/{id}', [App\Http\Controllers\BannerController::class, 'update'])->name('admin.banner.update')->middleware('auth:web');
    Route::post('/admin/banner/{id}/update', [App\Http\Controllers\BannerController::class, 'update'])->middleware('auth:web');
    Route::post('/admin/banner/toggle/{id}', [App\Http\Controllers\BannerController::class, 'toggleStatus'])->name('admin.banner.toggle')->middleware('auth:web');
    Route::delete('/admin/banner/delete/{id}', [App\Http\Controllers\BannerController::class, 'destroy'])->name('admin.banner.delete')->middleware('auth:web');
    Route::post('/admin/banner/delete/{id}', [App\Http\Controllers\BannerController::class, 'destroy'])->name('admin.banner.destroy')->middleware('auth:web');
    Route::delete('/admin/banner/{id}', [App\Http\Controllers\BannerController::class, 'destroy'])->middleware('auth:web');

    // 🔱 ABVHPS PUBLIC WEBSITE MAIN NAVIGATION ROUTES
// ----------------------------------------------------------------------
// Public Web Home Route
Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('public.home');

// Public Web About Route Link
Route::get('/about', [App\Http\Controllers\HomeController::class, 'about'])->name('about');

// Public Web Gallery Route Link
Route::get('/gallery', [App\Http\Controllers\HomeController::class, 'gallery'])->name('public.gallery');

// Public Web Blogs List Route Link
Route::get('/blogs', [App\Http\Controllers\HomeController::class, 'blogs'])->name('public.blogs');

// Public Web Our Team Leaders Route Link
Route::get('/team', [App\Http\Controllers\HomeController::class, 'team'])->name('public.team');

// Public Web Single Project Full Details Route Link
Route::get('/project/{id}', [App\Http\Controllers\HomeController::class, 'showProject'])->name('public.project.show');


