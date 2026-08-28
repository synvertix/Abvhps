<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\FundraisingCampaign;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Public Home page dynamic content API.
     * Replicates exactly the data feed used by the website's home page.
     */
    public function __invoke(): JsonResponse
    {
        // 1. Home banner
        $homeBanner = Banner::getBannerForPage('home');
        $bannerData = null;
        if ($homeBanner && !empty($homeBanner->desktop_banner)) {
            $bannerData = [
                'title'          => $homeBanner->title,
                'subtitle'       => $homeBanner->subtitle,
                'desktop_banner' => $homeBanner->desktop_url,
                'mobile_banner'  => $homeBanner->mobile_url,
            ];
        }

        // 2. Sliders
        $sliders = DB::table('home_sliders')
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get()
            ->map(function ($s) {
                return [
                    'id'          => $s->id,
                    'title'       => $s->title,
                    'subtitle'    => $s->subtitle,
                    'image_url'   => !empty($s->image_path) ? asset('storage/' . $s->image_path) : null,
                ];
            });

        // 3. Published Announcements (Exams)
        $publishedExams = DB::table('exam_settings')
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('exam_applications')
                    ->whereColumn('exam_applications.exam_setting_id', 'exam_settings.id')
                    ->where('exam_applications.result_publication_status', 'published');
            })
            ->latest('id')
            ->take(3)
            ->get(['id', 'exam_title']);

        // 4. Live Statistics
        $liveCounts = Cache::remember('homepage_public_stats', 600, function () {
            $membersCount = \App\Models\Membership::completed()->count();
            $volunteersCount = \App\Models\Volunteer::approved()->count();
            $donorsCount = (int) (\App\Models\Donation::paid()
                ->selectRaw('COUNT(DISTINCT COALESCE(NULLIF(phone, ""), NULLIF(email, ""), name)) as count')
                ->value('count') ?? 0);

            $configuredYears = SiteSetting::get('years_of_service');
            if ($configuredYears !== null && is_numeric($configuredYears)) {
                $yearsOfService = (int)$configuredYears;
            } else {
                $foundedYear = (int)SiteSetting::get('organization_founded_year', 2023);
                $yearsOfService = max(1, (int)date('Y') - $foundedYear);
            }

            return [
                'donors'     => $donorsCount,
                'members'    => $membersCount,
                'volunteers' => $volunteersCount,
                'years'      => $yearsOfService,
            ];
        });

        // 5. Active Fundraising Campaigns
        $fundraisingCampaigns = FundraisingCampaign::active()
            ->orderBy('id', 'desc')
            ->take(6)
            ->get()
            ->map(function ($c) {
                $target = $c->target_amount ?? 1;
                $raised = $c->raised_amount ?? 0;
                $percent = $target > 0 ? min(round(($raised / $target) * 100, 2), 100) : 0;
                return [
                    'id'            => $c->id,
                    'title'         => $c->title,
                    'description'   => strip_tags($c->description ?? ''),
                    'image_url'     => $c->cover_image_url ?? $c->image_url,
                    'target_amount' => (float)$target,
                    'raised_amount' => (float)$raised,
                    'target_formatted' => FundraisingCampaign::formatIndianCurrency($target),
                    'raised_formatted' => FundraisingCampaign::formatIndianCurrency($raised),
                    'percent'       => $percent,
                    'end_date'      => $c->end_date ? \Carbon\Carbon::parse($c->end_date)->format('d-M-Y') : null,
                ];
            });

        // 6. Core Projects (our_supports)
        $projects = DB::table('our_supports')
            ->where('status', 'show')
            ->orderBy('sort_order', 'asc')
            ->get()
            ->map(function ($p) {
                return [
                    'id'          => $p->id,
                    'name'        => $p->name,
                    'short_info'  => strip_tags($p->short_info ?? ''),
                    'image_url'   => !empty($p->image_path) ? asset('storage/' . $p->image_path) : null,
                ];
            });

        // 7. Join Strip
        $rawJoinEnabled = SiteSetting::get('homepage_join_enabled', '1');
        $memberHeading = SiteSetting::get('homepage_join_member_heading')
            ?: SiteSetting::get('homepage_join_vol_heading', 'BECOME AN ABVHPS MEMBER');
        if ($memberHeading === 'JOIN AS A VOLUNTEER') {
            $memberHeading = 'BECOME AN ABVHPS MEMBER';
        }
        $memberText = SiteSetting::get('homepage_join_member_text')
            ?: SiteSetting::get('homepage_join_vol_text', 'Join our growing community and participate in Dharma, Seva, cultural and social initiatives through ABVHPS.');
        if (str_contains($memberText, 'volunteer') || str_contains($memberText, 'Volunteer')) {
            $memberText = 'Join our growing community and participate in Dharma, Seva, cultural and social initiatives through ABVHPS.';
        }
        $ctaText = SiteSetting::get('homepage_join_cta_text', 'BECOME A MEMBER');
        if ($ctaText === 'JOIN AS VOLUNTEER') {
            $ctaText = 'BECOME A MEMBER';
        }

        $joinStrip = [
            'enabled'        => in_array($rawJoinEnabled, ['1', 'yes', true, 1], true),
            'why_heading'    => SiteSetting::get('homepage_join_why_heading', 'WHY JOIN ABVHPS?'),
            'why_text'       => SiteSetting::get('homepage_join_why_text', 'Become part of a service-oriented community committed to Dharma, social service, cultural awareness and organized voluntary service.'),
            'member_heading' => $memberHeading,
            'member_text'    => $memberText,
            'cta_text'       => $ctaText,
        ];

        // 8. Sponsors / Partners
        $rawSponsorsEnabled = SiteSetting::get('homepage_sponsors_enabled', '1');
        $sponsorsHeading = SiteSetting::get('homepage_sponsors_heading', 'OUR SUPPORTING PARTNERS');
        $supportingPartners = SiteSetting::getSupportingPartners();
        $sponsorsStrip = [
            'enabled'  => in_array($rawSponsorsEnabled, ['1', 'yes', true, 1], true),
            'heading'  => $sponsorsHeading,
            'partners' => $supportingPartners,
        ];

        // 9. Social Media
        $rawSocialEnabled = SiteSetting::get('homepage_social_enabled', '1');
        $socialHeading = SiteSetting::get('homepage_social_heading', 'CONNECT WITH ABVHPS');
        $socialSubtext = SiteSetting::get('homepage_social_subtext', 'Follow ABVHPS for updates on Seva activities, membership programs, volunteer initiatives, events, and organizational announcements.');
        $activeSocialLinks = SiteSetting::getActiveSocialLinks();
        $socialStrip = [
            'enabled'   => in_array($rawSocialEnabled, ['1', 'yes', true, 1], true) && count($activeSocialLinks) > 0,
            'heading'   => $socialHeading,
            'subtext'   => $socialSubtext,
            'platforms' => array_values($activeSocialLinks),
        ];

        // 10. Contact & WhatsApp
        $contact = [
            'phone'           => SiteSetting::get('contact_phone', '+91 8884933379'),
            'email'           => SiteSetting::get('contact_email', 'info@abvhps.org'),
            'address'         => SiteSetting::get('contact_address', 'Survey No:1826, Shanmukhapuram, Akkalareddy Palli Village and Post, Porumamilla Mandalam, Kadapa, A.P - 516193'),
            'whatsapp_number' => SiteSetting::getWhatsAppNumber(),
            'whatsapp_url'    => SiteSetting::getWhatsAppUrl(),
        ];

        return response()->json([
            'success' => true,
            'data'    => [
                'banner'         => $bannerData,
                'sliders'        => $sliders,
                'announcements'  => $publishedExams,
                'stats'          => $liveCounts,
                'join_strip'     => $joinStrip,
                'campaigns'      => $fundraisingCampaigns,
                'projects'       => $projects,
                'sponsors_strip' => $sponsorsStrip,
                'social_strip'   => $socialStrip,
                'contact'        => $contact,
            ],
            'message' => null,
        ]);
    }
}
