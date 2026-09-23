<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\SiteSetting;
use App\Models\Banner;
use App\Models\Blog;
use App\Models\Gallery;
use App\Models\OurSupport;
use App\Models\FundraisingCampaign;
use App\Models\Volunteer;
use App\Models\Membership;
use App\Models\ExamSetting;
use App\Models\TaxCertificate;

class SyncStateController extends Controller
{
    /**
     * Return lightweight signatures and timestamps for foreground reconciliation.
     */
    public function index(): JsonResponse
    {
        $settingsUpdated   = SiteSetting::max('updated_at') ?? now()->toDateTimeString();
        $bannersUpdated    = Banner::max('updated_at') ?? now()->toDateTimeString();
        $blogsUpdated      = Blog::max('updated_at') ?? now()->toDateTimeString();
        $galleryUpdated    = Gallery::max('updated_at') ?? now()->toDateTimeString();
        $supportUpdated    = OurSupport::max('updated_at') ?? now()->toDateTimeString();
        $campaignsUpdated  = FundraisingCampaign::max('updated_at') ?? now()->toDateTimeString();
        $volunteersUpdated = Volunteer::max('updated_at') ?? now()->toDateTimeString();
        $membershipsUpdated= Membership::max('updated_at') ?? now()->toDateTimeString();
        $examsUpdated      = ExamSetting::max('updated_at') ?? now()->toDateTimeString();
        $certsUpdated      = TaxCertificate::max('updated_at') ?? now()->toDateTimeString();

        $signatures = [
            'site_settings' => md5($settingsUpdated),
            'banners'       => md5($bannersUpdated . '_' . Banner::count()),
            'blogs'         => md5($blogsUpdated . '_' . Blog::count()),
            'gallery'       => md5($galleryUpdated . '_' . Gallery::count()),
            'support_cores' => md5($supportUpdated . '_' . OurSupport::count()),
            'campaigns'     => md5($campaignsUpdated . '_' . FundraisingCampaign::count()),
            'volunteers'    => md5($volunteersUpdated . '_' . Volunteer::count()),
            'memberships'   => md5($membershipsUpdated . '_' . Membership::count()),
            'exams'         => md5($examsUpdated . '_' . ExamSetting::count()),
            'certificates'  => md5($certsUpdated . '_' . TaxCertificate::count()),
        ];

        return response()->json([
            'success'    => true,
            'timestamp'  => now()->toIso8601String(),
            'signatures' => $signatures,
            'metadata'   => [
                'active_campaigns_count' => FundraisingCampaign::where('status', 'active')->count(),
                'volunteers_count'       => Volunteer::count(),
                'approved_members_count' => Membership::where('status', 'approved')->count(),
            ],
        ]);
    }
}
