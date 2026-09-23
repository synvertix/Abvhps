<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\BootstrapController;
use App\Http\Controllers\Api\V1\HomeController;
use App\Http\Controllers\Api\V1\VolunteerAuthController;
use App\Http\Controllers\Api\V1\MemberAuthController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\VolunteerProfileController;
use App\Http\Controllers\Api\V1\MemberProfileController;

/*
|--------------------------------------------------------------------------
| Mobile & External API Routes (V1)
|--------------------------------------------------------------------------
|
| Versioned RESTful API endpoints for ABVHPS mobile applications.
| Base prefix: /api/v1/
|
*/

Route::prefix('v1')->group(function () {

    // ---------------------------------------------------------------------
    // 1. Public Content & Service Endpoints
    // ---------------------------------------------------------------------
    Route::get('/health', HealthController::class)->name('api.v1.health');
    Route::get('/bootstrap', BootstrapController::class)->name('api.v1.bootstrap');
    Route::get('/home', HomeController::class)->name('api.v1.home');
    Route::get('/about', \App\Http\Controllers\Api\V1\AboutController::class)->name('api.v1.about');
    Route::get('/team', \App\Http\Controllers\Api\V1\TeamDirectoryController::class)->name('api.v1.team');
    Route::get('/certificates', \App\Http\Controllers\Api\V1\CertificateController::class)->name('api.v1.certificates');
    Route::get('/gallery', \App\Http\Controllers\Api\V1\GalleryController::class)->name('api.v1.gallery');

    // Projects
    Route::get('/projects', [\App\Http\Controllers\Api\V1\ProjectController::class, 'index'])->name('api.v1.projects.index');
    Route::get('/projects/{id}', [\App\Http\Controllers\Api\V1\ProjectController::class, 'show'])
        ->whereNumber('id')
        ->name('api.v1.projects.show');

    // Campaigns
    Route::get('/campaigns', [\App\Http\Controllers\Api\V1\CampaignController::class, 'index'])->name('api.v1.campaigns.index');
    Route::get('/campaigns/{id}', [\App\Http\Controllers\Api\V1\CampaignController::class, 'show'])
        ->whereNumber('id')
        ->name('api.v1.campaigns.show');

    // Health & System Bootstrap
    Route::get('/health', [\App\Http\Controllers\Api\V1\HealthController::class, '__invoke'])->name('api.v1.health');
    Route::get('/bootstrap', [\App\Http\Controllers\Api\V1\BootstrapController::class, '__invoke'])->name('api.v1.bootstrap');
    Route::get('/sync-state', [\App\Http\Controllers\Api\V1\SyncStateController::class, 'index'])->name('api.v1.sync_state');

    // Online Donations & Application APIs
    Route::post('/donations/initiate', [\App\Http\Controllers\Api\V1\DonationApiController::class, 'initiate'])->name('api.v1.donations.initiate');
    Route::get('/donations/{id}/status', [\App\Http\Controllers\Api\V1\DonationApiController::class, 'checkStatus'])->whereNumber('id')->name('api.v1.donations.status');
    Route::post('/membership/apply', [\App\Http\Controllers\Api\V1\MemberApplicationApiController::class, 'apply'])->name('api.v1.membership.apply');
    Route::post('/volunteer/apply', [\App\Http\Controllers\Api\V1\VolunteerApplicationApiController::class, 'apply'])->name('api.v1.volunteer.apply');

    // Blogs
    Route::get('/blogs', [\App\Http\Controllers\Api\V1\BlogController::class, 'index'])->name('api.v1.blogs.index');
    Route::get('/blogs/{id}', [\App\Http\Controllers\Api\V1\BlogController::class, 'show'])
        ->whereNumber('id')
        ->name('api.v1.blogs.show');

    // Contact
    Route::get('/contact', [\App\Http\Controllers\Api\V1\ContactController::class, 'show'])->name('api.v1.contact.show');
    Route::post('/contact', [\App\Http\Controllers\Api\V1\ContactController::class, 'submit'])
        ->middleware('throttle:5,1')
        ->name('api.v1.contact.submit');

    // Exams (Static routes before parameter routes)
    Route::get('/exams/results/winners', [\App\Http\Controllers\Api\V1\ExamController::class, 'winners'])->name('api.v1.exams.winners');
    Route::post('/exams/results/search', [\App\Http\Controllers\Api\V1\ExamController::class, 'searchResult'])
        ->middleware('throttle:10,1')
        ->name('api.v1.exams.search');
    Route::get('/exams', [\App\Http\Controllers\Api\V1\ExamController::class, 'index'])->name('api.v1.exams.index');
    Route::get('/exams/{id}', [\App\Http\Controllers\Api\V1\ExamController::class, 'show'])
        ->whereNumber('id')
        ->name('api.v1.exams.show');

    // Wings (Eligibility check before slug route)
    Route::post('/wings/rudrasena/verify-eligibility', [\App\Http\Controllers\Api\V1\WingController::class, 'verifyRudrasenaEligibility'])
        ->middleware('throttle:15,1')
        ->name('api.v1.wings.rudrasena.verify');
    Route::get('/wings', [\App\Http\Controllers\Api\V1\WingController::class, 'index'])->name('api.v1.wings.index');
    Route::get('/wings/{slug}', [\App\Http\Controllers\Api\V1\WingController::class, 'show'])
        ->whereIn('slug', ['rudrasena', 'kala-brundam', 'grama-seva-dal', 'organic-farmers'])
        ->name('api.v1.wings.show');

    // Public QR / Master ID Verification
    Route::get('/verify/{type}/{id}', [\App\Http\Controllers\Api\V1\PublicVerificationController::class, 'verify'])
        ->whereIn('type', ['membership', 'volunteer', 'rudrasena', 'exam', 'organic-farmers', 'kala-brundham', 'kala-brundam', 'grama-seva-dal'])
        ->middleware('throttle:30,1')
        ->name('api.v1.verify');

    // ---------------------------------------------------------------------
    // 2. Authentication Flow (Public Entrypoints)
    // ---------------------------------------------------------------------
    Route::prefix('auth')->group(function () {
        // Admin Login
        Route::post('/admin/login', [\App\Http\Controllers\Api\V1\AdminAuthController::class, 'login'])
            ->name('api.v1.auth.admin.login');

        // Volunteer Login
        Route::post('/volunteer/login', [VolunteerAuthController::class, 'login'])
            ->name('api.v1.auth.volunteer.login');

        // Member OTP Authentication
        Route::post('/member/send-otp', [MemberAuthController::class, 'sendOtp'])
            ->name('api.v1.auth.member.send_otp');
        Route::post('/member/verify-otp', [MemberAuthController::class, 'verifyOtp'])
            ->name('api.v1.auth.member.verify_otp');
    });

    // ---------------------------------------------------------------------
    // 3. Protected Shared Endpoints (Sanctum Authenticated)
    // ---------------------------------------------------------------------
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/me', [MeController::class, 'me'])->name('api.v1.me');
        Route::post('/auth/logout', [MeController::class, 'logout'])->name('api.v1.auth.logout');
        Route::post('/auth/logout-all', [MeController::class, 'logoutAll'])->name('api.v1.auth.logout_all');

        // -----------------------------------------------------------------
        // 4. Protected Volunteer Routes
        // -----------------------------------------------------------------
        Route::prefix('volunteer')->middleware([
            'api.account_type:volunteer',
            'api.volunteer.eligible',
        ])->group(function () {
            // Password change endpoint (accessible even when must_change_password=true)
            Route::post('/change-password', [VolunteerAuthController::class, 'changePassword'])
                ->middleware(['ability:volunteer:change-password,volunteer:dashboard'])
                ->name('api.v1.volunteer.change_password');

            // Protected volunteer resources (blocked if must_change_password=true)
            Route::middleware([
                'api.volunteer.password',
            ])->group(function () {
                Route::get('/profile', [VolunteerProfileController::class, 'profile'])
                    ->middleware(['ability:volunteer:profile'])
                    ->name('api.v1.volunteer.profile');
                Route::get('/dashboard', [VolunteerProfileController::class, 'dashboard'])
                    ->middleware(['ability:volunteer:dashboard'])
                    ->name('api.v1.volunteer.dashboard');
            });
        });

        // -----------------------------------------------------------------
        // 5. Protected Member Routes
        // -----------------------------------------------------------------
        Route::prefix('member')->middleware([
            'api.account_type:member',
        ])->group(function () {
            Route::get('/profile', [MemberProfileController::class, 'profile'])
                ->middleware(['ability:member:profile'])
                ->name('api.v1.member.profile');
            Route::get('/card', [MemberProfileController::class, 'card'])
                ->middleware(['ability:member:card'])
                ->name('api.v1.member.card');
        });

        // -----------------------------------------------------------------
        // 6. Protected Admin Routes
        // -----------------------------------------------------------------
        Route::prefix('admin')->middleware([
            'api.account_type:admin',
        ])->group(function () {
            Route::get('/dashboard', [\App\Http\Controllers\Api\V1\AdminDashboardController::class, 'show'])
                ->middleware(['ability:admin:dashboard'])
                ->name('api.v1.admin.dashboard');

            // Approved & Pending Memberships
            Route::get('/memberships/pending', [\App\Http\Controllers\Api\V1\AdminMembershipController::class, 'pending'])
                ->middleware(['ability:admin:dashboard'])
                ->name('api.v1.admin.memberships.pending');
            Route::get('/memberships', [\App\Http\Controllers\Api\V1\AdminMembershipController::class, 'index'])
                ->middleware(['ability:admin:dashboard'])
                ->name('api.v1.admin.memberships.index');
            Route::get('/memberships/{id}', [\App\Http\Controllers\Api\V1\AdminMembershipController::class, 'show'])
                ->whereNumber('id')
                ->middleware(['ability:admin:dashboard'])
                ->name('api.v1.admin.memberships.show');
            Route::put('/memberships/{id}', [\App\Http\Controllers\Api\V1\AdminMembershipController::class, 'update'])
                ->whereNumber('id')
                ->middleware(['ability:admin:dashboard'])
                ->name('api.v1.admin.memberships.update');
            Route::delete('/memberships/{id}', [\App\Http\Controllers\Api\V1\AdminMembershipController::class, 'destroy'])
                ->whereNumber('id')
                ->middleware(['ability:admin:dashboard'])
                ->name('api.v1.admin.memberships.destroy');

            // Volunteer Desk
            Route::get('/volunteers', [\App\Http\Controllers\Api\V1\AdminVolunteerController::class, 'index'])
                ->middleware(['ability:admin:dashboard'])
                ->name('api.v1.admin.volunteers.index');
            Route::get('/volunteers/{id}', [\App\Http\Controllers\Api\V1\AdminVolunteerController::class, 'show'])
                ->whereNumber('id')
                ->middleware(['ability:admin:dashboard'])
                ->name('api.v1.admin.volunteers.show');
            Route::post('/volunteers/{id}/cadre', [\App\Http\Controllers\Api\V1\AdminVolunteerController::class, 'cadreUpdate'])
                ->whereNumber('id')
                ->middleware(['ability:admin:dashboard'])
                ->name('api.v1.admin.volunteers.cadre');
            Route::delete('/volunteers/{id}', [\App\Http\Controllers\Api\V1\AdminVolunteerController::class, 'destroy'])
                ->whereNumber('id')
                ->middleware(['ability:admin:dashboard'])
                ->name('api.v1.admin.volunteers.destroy');

            // Volunteer Events
            Route::get('/volunteer-events', [\App\Http\Controllers\Api\V1\AdminVolunteerEventApiController::class, 'index'])
                ->middleware(['ability:admin:dashboard'])
                ->name('api.v1.admin.volunteer_events.index');
            Route::get('/volunteer-events/{id}', [\App\Http\Controllers\Api\V1\AdminVolunteerEventApiController::class, 'show'])
                ->whereNumber('id')
                ->middleware(['ability:admin:dashboard'])
                ->name('api.v1.admin.volunteer_events.show');
            Route::delete('/volunteer-events/{id}', [\App\Http\Controllers\Api\V1\AdminVolunteerEventApiController::class, 'destroy'])
                ->whereNumber('id')
                ->middleware(['ability:admin:dashboard'])
                ->name('api.v1.admin.volunteer_events.destroy');

            // Rudrasena
            Route::get('/rudrasena', [\App\Http\Controllers\Api\V1\AdminRudrasenaController::class, 'index'])
                ->middleware(['ability:admin:dashboard'])
                ->name('api.v1.admin.rudrasena.index');
            Route::get('/rudrasena/{id}', [\App\Http\Controllers\Api\V1\AdminRudrasenaController::class, 'show'])
                ->whereNumber('id')
                ->middleware(['ability:admin:dashboard'])
                ->name('api.v1.admin.rudrasena.show');
            Route::post('/rudrasena/{id}/status', [\App\Http\Controllers\Api\V1\AdminRudrasenaController::class, 'updateStatus'])
                ->whereNumber('id')
                ->middleware(['ability:admin:dashboard'])
                ->name('api.v1.admin.rudrasena.status');
            Route::delete('/rudrasena/{id}', [\App\Http\Controllers\Api\V1\AdminRudrasenaController::class, 'destroy'])
                ->whereNumber('id')
                ->middleware(['ability:admin:dashboard'])
                ->name('api.v1.admin.rudrasena.destroy');

            // Local GP Gateways
            Route::get('/local-gateways', [\App\Http\Controllers\Api\V1\AdminLocalGatewayController::class, 'index'])
                ->middleware(['ability:admin:dashboard'])
                ->name('api.v1.admin.local_gateways.index');
            Route::post('/local-gateways/approve/{wing}/{id}', [\App\Http\Controllers\Api\V1\AdminLocalGatewayController::class, 'approve'])
                ->whereNumber('id')
                ->middleware(['ability:admin:dashboard'])
                ->name('api.v1.admin.local_gateways.approve');
            Route::delete('/local-gateways/delete/{wing}/{id}', [\App\Http\Controllers\Api\V1\AdminLocalGatewayController::class, 'destroy'])
                ->whereNumber('id')
                ->middleware(['ability:admin:dashboard'])
                ->name('api.v1.admin.local_gateways.destroy');

            // Our Team
            Route::get('/team', [\App\Http\Controllers\Api\V1\AdminTeamController::class, 'index'])->middleware(['ability:admin:dashboard']);
            Route::get('/team/{id}', [\App\Http\Controllers\Api\V1\AdminTeamController::class, 'show'])->whereNumber('id')->middleware(['ability:admin:dashboard']);
            Route::post('/team', [\App\Http\Controllers\Api\V1\AdminTeamController::class, 'store'])->middleware(['ability:admin:dashboard']);
            Route::post('/team/{id}', [\App\Http\Controllers\Api\V1\AdminTeamController::class, 'update'])->whereNumber('id')->middleware(['ability:admin:dashboard']);
            Route::put('/team/{id}', [\App\Http\Controllers\Api\V1\AdminTeamController::class, 'update'])->whereNumber('id')->middleware(['ability:admin:dashboard']);
            Route::delete('/team/{id}', [\App\Http\Controllers\Api\V1\AdminTeamController::class, 'destroy'])->whereNumber('id')->middleware(['ability:admin:dashboard']);

            // Donations
            Route::get('/donations', [\App\Http\Controllers\Api\V1\AdminDonationController::class, 'index'])->middleware(['ability:admin:dashboard']);
            Route::get('/donations/{id}', [\App\Http\Controllers\Api\V1\AdminDonationController::class, 'show'])->whereNumber('id')->middleware(['ability:admin:dashboard']);

            // Blogs
            Route::get('/blogs', [\App\Http\Controllers\Api\V1\AdminBlogController::class, 'index'])->middleware(['ability:admin:dashboard']);
            Route::get('/blogs/{id}', [\App\Http\Controllers\Api\V1\AdminBlogController::class, 'show'])->whereNumber('id')->middleware(['ability:admin:dashboard']);
            Route::post('/blogs', [\App\Http\Controllers\Api\V1\AdminBlogController::class, 'store'])->middleware(['ability:admin:dashboard']);
            Route::post('/blogs/{id}', [\App\Http\Controllers\Api\V1\AdminBlogController::class, 'update'])->whereNumber('id')->middleware(['ability:admin:dashboard']);
            Route::put('/blogs/{id}', [\App\Http\Controllers\Api\V1\AdminBlogController::class, 'update'])->whereNumber('id')->middleware(['ability:admin:dashboard']);
            Route::delete('/blogs/{id}', [\App\Http\Controllers\Api\V1\AdminBlogController::class, 'destroy'])->whereNumber('id')->middleware(['ability:admin:dashboard']);

            // Media Gallery
            Route::get('/gallery', [\App\Http\Controllers\Api\V1\AdminGalleryController::class, 'index'])->middleware(['ability:admin:dashboard']);
            Route::post('/gallery', [\App\Http\Controllers\Api\V1\AdminGalleryController::class, 'store'])->middleware(['ability:admin:dashboard']);
            Route::delete('/gallery/{id}', [\App\Http\Controllers\Api\V1\AdminGalleryController::class, 'destroy'])->whereNumber('id')->middleware(['ability:admin:dashboard']);

            // Support Cores
            Route::get('/support-cores', [\App\Http\Controllers\Api\V1\AdminSupportCoreController::class, 'index'])->middleware(['ability:admin:dashboard']);
            Route::get('/support-cores/{id}', [\App\Http\Controllers\Api\V1\AdminSupportCoreController::class, 'show'])->whereNumber('id')->middleware(['ability:admin:dashboard']);
            Route::post('/support-cores', [\App\Http\Controllers\Api\V1\AdminSupportCoreController::class, 'store'])->middleware(['ability:admin:dashboard']);
            Route::post('/support-cores/{id}', [\App\Http\Controllers\Api\V1\AdminSupportCoreController::class, 'update'])->whereNumber('id')->middleware(['ability:admin:dashboard']);
            Route::put('/support-cores/{id}', [\App\Http\Controllers\Api\V1\AdminSupportCoreController::class, 'update'])->whereNumber('id')->middleware(['ability:admin:dashboard']);
            Route::delete('/support-cores/{id}', [\App\Http\Controllers\Api\V1\AdminSupportCoreController::class, 'destroy'])->whereNumber('id')->middleware(['ability:admin:dashboard']);

            // Exams Board & Results
            Route::get('/exams', [\App\Http\Controllers\Api\V1\AdminExamController::class, 'index'])->middleware(['ability:admin:dashboard']);
            Route::post('/exams', [\App\Http\Controllers\Api\V1\AdminExamController::class, 'store'])->middleware(['ability:admin:dashboard']);
            Route::put('/exams/{id}', [\App\Http\Controllers\Api\V1\AdminExamController::class, 'update'])->whereNumber('id')->middleware(['ability:admin:dashboard']);
            Route::delete('/exams/{id}', [\App\Http\Controllers\Api\V1\AdminExamController::class, 'destroy'])->whereNumber('id')->middleware(['ability:admin:dashboard']);
            Route::get('/exams/{id}/applicants', [\App\Http\Controllers\Api\V1\AdminExamController::class, 'applicants'])->whereNumber('id')->middleware(['ability:admin:dashboard']);
            Route::post('/exams/results/{appId}', [\App\Http\Controllers\Api\V1\AdminExamController::class, 'saveResult'])->whereNumber('appId')->middleware(['ability:admin:dashboard']);
            Route::post('/exams/{id}/publish-results', [\App\Http\Controllers\Api\V1\AdminExamController::class, 'publishResults'])->whereNumber('id')->middleware(['ability:admin:dashboard']);
            Route::post('/exams/{id}/unpublish-results', [\App\Http\Controllers\Api\V1\AdminExamController::class, 'unpublishResults'])->whereNumber('id')->middleware(['ability:admin:dashboard']);

            // Fundraising
            Route::get('/fundraising', [\App\Http\Controllers\Api\V1\AdminCampaignController::class, 'index'])->middleware(['ability:admin:dashboard']);
            Route::get('/fundraising/{id}', [\App\Http\Controllers\Api\V1\AdminCampaignController::class, 'show'])->whereNumber('id')->middleware(['ability:admin:dashboard']);
            Route::post('/fundraising', [\App\Http\Controllers\Api\V1\AdminCampaignController::class, 'store'])->middleware(['ability:admin:dashboard']);
            Route::post('/fundraising/{id}', [\App\Http\Controllers\Api\V1\AdminCampaignController::class, 'update'])->whereNumber('id')->middleware(['ability:admin:dashboard']);
            Route::put('/fundraising/{id}', [\App\Http\Controllers\Api\V1\AdminCampaignController::class, 'update'])->whereNumber('id')->middleware(['ability:admin:dashboard']);
            Route::post('/fundraising/{id}/toggle', [\App\Http\Controllers\Api\V1\AdminCampaignController::class, 'toggleStatus'])->whereNumber('id')->middleware(['ability:admin:dashboard']);
            Route::delete('/fundraising/{id}', [\App\Http\Controllers\Api\V1\AdminCampaignController::class, 'destroy'])->whereNumber('id')->middleware(['ability:admin:dashboard']);

            // Contact Forms Audit
            Route::get('/contacts', [\App\Http\Controllers\Api\V1\AdminContactController::class, 'index'])->middleware(['ability:admin:dashboard']);
            Route::get('/contacts/{id}', [\App\Http\Controllers\Api\V1\AdminContactController::class, 'show'])->whereNumber('id')->middleware(['ability:admin:dashboard']);
            Route::post('/contacts/{id}/status', [\App\Http\Controllers\Api\V1\AdminContactController::class, 'updateStatus'])->whereNumber('id')->middleware(['ability:admin:dashboard']);
            Route::delete('/contacts/{id}', [\App\Http\Controllers\Api\V1\AdminContactController::class, 'destroy'])->whereNumber('id')->middleware(['ability:admin:dashboard']);

            // Tax Certificates
            Route::get('/tax-certificates', [\App\Http\Controllers\Api\V1\AdminTaxCertificateController::class, 'index'])->middleware(['ability:admin:dashboard']);
            Route::post('/tax-certificates', [\App\Http\Controllers\Api\V1\AdminTaxCertificateController::class, 'store'])->middleware(['ability:admin:dashboard']);
            Route::post('/tax-certificates/{id}/toggle', [\App\Http\Controllers\Api\V1\AdminTaxCertificateController::class, 'toggleVisibility'])->whereNumber('id')->middleware(['ability:admin:dashboard']);
            Route::delete('/tax-certificates/{id}', [\App\Http\Controllers\Api\V1\AdminTaxCertificateController::class, 'destroy'])->whereNumber('id')->middleware(['ability:admin:dashboard']);

            // Site Global Settings
            Route::get('/settings', [\App\Http\Controllers\Api\V1\AdminSiteSettingsController::class, 'index'])->middleware(['ability:admin:dashboard']);
            Route::post('/settings', [\App\Http\Controllers\Api\V1\AdminSiteSettingsController::class, 'update'])->middleware(['ability:admin:dashboard']);

            // Banner Management
            Route::get('/banners', [\App\Http\Controllers\Api\V1\AdminBannerController::class, 'index'])->middleware(['ability:admin:dashboard']);
            Route::get('/banners/{id}', [\App\Http\Controllers\Api\V1\AdminBannerController::class, 'show'])->whereNumber('id')->middleware(['ability:admin:dashboard']);
            Route::post('/banners', [\App\Http\Controllers\Api\V1\AdminBannerController::class, 'store'])->middleware(['ability:admin:dashboard']);
            Route::post('/banners/{id}', [\App\Http\Controllers\Api\V1\AdminBannerController::class, 'update'])->whereNumber('id')->middleware(['ability:admin:dashboard']);
            Route::put('/banners/{id}', [\App\Http\Controllers\Api\V1\AdminBannerController::class, 'update'])->whereNumber('id')->middleware(['ability:admin:dashboard']);
            Route::post('/banners/{id}/toggle', [\App\Http\Controllers\Api\V1\AdminBannerController::class, 'toggleStatus'])->whereNumber('id')->middleware(['ability:admin:dashboard']);
            Route::delete('/banners/{id}', [\App\Http\Controllers\Api\V1\AdminBannerController::class, 'destroy'])->whereNumber('id')->middleware(['ability:admin:dashboard']);
        });
    });
});
