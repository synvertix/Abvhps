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
    });
});
