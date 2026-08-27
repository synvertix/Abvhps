<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\FundraisingCampaign;

class HomeController extends Controller
{
    // 1. Public Website Home Page Layout
    public function index()
    {
        // Fetch all active sliders from database
        $sliders = DB::table('home_sliders')->where('is_active', true)->orderBy('sort_order', 'asc')->get();

        // Fetch all active core projects from database
        $projects = DB::table('our_supports')->where('status', 'show')->orderBy('sort_order', 'asc')->get();

        // Fetch active fundraising campaigns for home page showcase
        $fundraisingCampaigns = FundraisingCampaign::active()->orderBy('id', 'desc')->take(6)->get();
        $fundraising = $fundraisingCampaigns->first();

        // Fetch latest exam cycles with published results
        $publishedExams = DB::table('exam_settings')
            ->whereExists(function($q) {
                $q->select(DB::raw(1))
                  ->from('exam_applications')
                  ->whereColumn('exam_applications.exam_setting_id', 'exam_settings.id')
                  ->where('exam_applications.result_publication_status', 'published');
            })
            ->latest('id')
            ->take(3)
            ->get();

        // Real database statistics with 10-minute cache for fast public homepage rendering
        $liveCounts = \Illuminate\Support\Facades\Cache::remember('homepage_public_stats', 600, function () {
            // 1. Qualifying completed registered members
            $membersCount = \App\Models\Membership::completed()->count();

            // 2. Qualifying approved and active volunteers
            $volunteersCount = \App\Models\Volunteer::approved()->count();

            // 3. Qualifying verified distinct donors (excluding failed/pending)
            $donorsCount = (int) (\App\Models\Donation::paid()
                ->selectRaw('COUNT(DISTINCT COALESCE(NULLIF(phone, ""), NULLIF(email, ""), name)) as count')
                ->value('count') ?? 0);

            // 4. Years of Service: Check configurable setting or calculate from organization registration year
            $configuredYears = \App\Models\SiteSetting::get('years_of_service');
            if ($configuredYears !== null && is_numeric($configuredYears)) {
                $yearsOfService = (int)$configuredYears;
            } else {
                $foundedYear = (int)\App\Models\SiteSetting::get('organization_founded_year', 2023);
                $yearsOfService = max(1, (int)date('Y') - $foundedYear);
            }

            return [
                'donors'     => $donorsCount,
                'members'    => $membersCount,
                'volunteers' => $volunteersCount,
                'years'      => $yearsOfService,
            ];
        });

        // Homepage Floating Join / Membership Strip Settings
        $rawJoinEnabled = \App\Models\SiteSetting::get('homepage_join_enabled', '1');
        $memberHeading = \App\Models\SiteSetting::get('homepage_join_member_heading')
            ?: \App\Models\SiteSetting::get('homepage_join_vol_heading', 'BECOME AN ABVHPS MEMBER');
        if ($memberHeading === 'JOIN AS A VOLUNTEER') {
            $memberHeading = 'BECOME AN ABVHPS MEMBER';
        }

        $memberText = \App\Models\SiteSetting::get('homepage_join_member_text')
            ?: \App\Models\SiteSetting::get('homepage_join_vol_text', 'Join our growing community and participate in Dharma, Seva, cultural and social initiatives through ABVHPS.');
        if (str_contains($memberText, 'volunteer') || str_contains($memberText, 'Volunteer')) {
            $memberText = 'Join our growing community and participate in Dharma, Seva, cultural and social initiatives through ABVHPS.';
        }

        $ctaText = \App\Models\SiteSetting::get('homepage_join_cta_text', 'BECOME A MEMBER');
        if ($ctaText === 'JOIN AS VOLUNTEER') {
            $ctaText = 'BECOME A MEMBER';
        }

        $joinStrip = [
            'enabled'            => in_array($rawJoinEnabled, ['1', 'yes', true, 1], true),
            'why_heading'        => \App\Models\SiteSetting::get('homepage_join_why_heading', 'WHY JOIN ABVHPS?'),
            'why_text'           => \App\Models\SiteSetting::get('homepage_join_why_text', 'Become part of a service-oriented community committed to Dharma, social service, cultural awareness and organized community service.'),
            'member_heading'     => $memberHeading,
            'member_text'        => $memberText,
            'cta_text'           => $ctaText,
            'cta_url'            => route('membership.form'),
            'secondary_cta_text' => \App\Models\SiteSetting::get('homepage_join_secondary_cta_text'),
            'secondary_cta_url'  => \App\Models\SiteSetting::get('homepage_join_secondary_cta_url'),
        ];

        // Supporting Partners / Sponsors Scrolling Marquee Settings
        $rawSponsorsEnabled = \App\Models\SiteSetting::get('homepage_sponsors_enabled', '1');
        $sponsorsHeading = \App\Models\SiteSetting::get('homepage_sponsors_heading', 'OUR SUPPORTING PARTNERS');
        $supportingPartners = \App\Models\SiteSetting::getSupportingPartners();

        $sponsorsStrip = [
            'enabled'  => in_array($rawSponsorsEnabled, ['1', 'yes', true, 1], true),
            'heading'  => $sponsorsHeading,
            'partners' => $supportingPartners,
            'sponsors' => array_column($supportingPartners, 'name'),
        ];

        // Pass all database items to the view folder
        return view('home', compact('sliders', 'projects', 'fundraising', 'fundraisingCampaigns', 'liveCounts', 'publishedExams', 'joinStrip', 'sponsorsStrip'));
    }

    // 2. Public Website About Page Node
    public function about()
    {
        return view('about');
    }

    // 3. Public Website Gallery Page Node
    public function gallery()
    {
        // Fetch all active photos and videos uploaded from admin panel
        $galleryItems = DB::table('galleries')->orderBy('id', 'desc')->get();

        return view('gallery', compact('galleryItems'));
    }

    // 3. Public Website Blogs / Articles Page Node
    public function blogs()
    {
        // Fetch all published blog articles from admin panel (Only active status)
        $blogs = DB::table('blogs')->where('status', 'active')->orderBy('id', 'desc')->paginate(9);

        return view('blogs', compact('blogs'));
    }

    // 4. Public Website Our Team Leadership Page Node (Official Approved Volunteers Directory)
    public function team(Request $request)
    {
        // Base approved pool for calculating dynamic counts and dropdown options
        $approvedBase = \App\Models\Volunteer::with('membership')->approved()->get();

        $selectedCadre    = $request->query('cadre');
        $selectedCountry  = $request->query('country');
        $selectedState    = $request->query('state');
        $selectedDistrict = $request->query('district');
        $selectedAssembly = $request->query('assembly_segment') ?: $request->query('taluk');
        $selectedMandal   = $request->query('mandal');
        $selectedPanchayat= $request->query('panchayat') ?: $request->query('grama_panchayat');
        $searchQuery      = $request->query('search');

        // Dynamic distinct lists with real counts for cascading navigation
        $cadres = $approvedBase->map(fn($v) => $v->cadre_label)->filter()->unique()->values();

        $countries = $approvedBase->map(fn($v) => $v->resolved_country)->filter()->unique()->values();

        $states = $approvedBase->filter(function($v) use ($selectedCountry) {
            return empty($selectedCountry) || strcasecmp($v->resolved_country, $selectedCountry) === 0;
        })->map(fn($v) => $v->resolved_state)->filter()->unique()->values();

        $districts = $approvedBase->filter(function($v) use ($selectedCountry, $selectedState) {
            $matchCountry = empty($selectedCountry) || strcasecmp($v->resolved_country, $selectedCountry) === 0;
            $matchState   = empty($selectedState)   || strcasecmp($v->resolved_state, $selectedState) === 0;
            return $matchCountry && $matchState;
        })->map(fn($v) => $v->resolved_district)->filter()->unique()->values();

        $assemblies = $approvedBase->filter(function($v) use ($selectedCountry, $selectedState, $selectedDistrict) {
            $matchCountry  = empty($selectedCountry)  || strcasecmp($v->resolved_country, $selectedCountry) === 0;
            $matchState    = empty($selectedState)    || strcasecmp($v->resolved_state, $selectedState) === 0;
            $matchDistrict = empty($selectedDistrict) || strcasecmp($v->resolved_district, $selectedDistrict) === 0;
            return $matchCountry && $matchState && $matchDistrict;
        })->map(fn($v) => $v->resolved_assembly_segment)->filter()->unique()->values();

        $mandals = $approvedBase->filter(function($v) use ($selectedCountry, $selectedState, $selectedDistrict, $selectedAssembly) {
            $matchCountry  = empty($selectedCountry)  || strcasecmp($v->resolved_country, $selectedCountry) === 0;
            $matchState    = empty($selectedState)    || strcasecmp($v->resolved_state, $selectedState) === 0;
            $matchDistrict = empty($selectedDistrict) || strcasecmp($v->resolved_district, $selectedDistrict) === 0;
            $matchAssembly = empty($selectedAssembly) || strcasecmp($v->resolved_assembly_segment, $selectedAssembly) === 0;
            return $matchCountry && $matchState && $matchDistrict && $matchAssembly;
        })->map(fn($v) => $v->resolved_mandal)->filter()->unique()->values();

        $panchayats = $approvedBase->filter(function($v) use ($selectedCountry, $selectedState, $selectedDistrict, $selectedAssembly, $selectedMandal) {
            $matchCountry  = empty($selectedCountry)  || strcasecmp($v->resolved_country, $selectedCountry) === 0;
            $matchState    = empty($selectedState)    || strcasecmp($v->resolved_state, $selectedState) === 0;
            $matchDistrict = empty($selectedDistrict) || strcasecmp($v->resolved_district, $selectedDistrict) === 0;
            $matchAssembly = empty($selectedAssembly) || strcasecmp($v->resolved_assembly_segment, $selectedAssembly) === 0;
            $matchMandal   = empty($selectedMandal)   || strcasecmp($v->resolved_mandal, $selectedMandal) === 0;
            return $matchCountry && $matchState && $matchDistrict && $matchAssembly && $matchMandal;
        })->map(fn($v) => $v->resolved_grama_panchayat)->filter()->unique()->values();

        // Main paginated query strictly for APPROVED & ACTIVE volunteers
        $volunteersQuery = \App\Models\Volunteer::with('membership')->approved();

        if (!empty($selectedCadre)) {
            $volunteersQuery->where(function($q) use ($selectedCadre) {
                $q->where('cadre', $selectedCadre)
                  ->orWhere('designation', $selectedCadre);
            });
        }

        if (!empty($selectedCountry)) {
            $volunteersQuery->where(function($q) use ($selectedCountry) {
                $q->where('country', $selectedCountry)
                  ->orWhereHas('membership', fn($mq) => $mq->where('country', $selectedCountry));
            });
        }

        if (!empty($selectedState)) {
            $volunteersQuery->where(function($q) use ($selectedState) {
                $q->where('state', $selectedState)
                  ->orWhereHas('membership', fn($mq) => $mq->where('state', $selectedState));
            });
        }

        if (!empty($selectedDistrict)) {
            $volunteersQuery->where(function($q) use ($selectedDistrict) {
                $q->where('district', $selectedDistrict)
                  ->orWhereHas('membership', fn($mq) => $mq->where('district', $selectedDistrict));
            });
        }

        if (!empty($selectedAssembly)) {
            $volunteersQuery->where(function($q) use ($selectedAssembly) {
                $q->where('assembly_segment', $selectedAssembly)
                  ->orWhereHas('membership', fn($mq) => $mq->where('assembly_segment', $selectedAssembly));
            });
        }

        if (!empty($selectedMandal)) {
            $volunteersQuery->where(function($q) use ($selectedMandal) {
                $q->where('mandal', $selectedMandal)
                  ->orWhereHas('membership', fn($mq) => $mq->where('mandal', $selectedMandal));
            });
        }

        if (!empty($selectedPanchayat)) {
            $volunteersQuery->where(function($q) use ($selectedPanchayat) {
                $q->where('grama_panchayat', $selectedPanchayat)
                  ->orWhereHas('membership', fn($mq) => $mq->where('grama_panchayat', $selectedPanchayat));
            });
        }

        if (!empty($searchQuery)) {
            $volunteersQuery->where(function($q) use ($searchQuery) {
                $q->where('volunteer_id', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('cadre', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('designation', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('locality', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('district', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('mandal', 'LIKE', "%{$searchQuery}%")
                  ->orWhereHas('membership', function($mq) use ($searchQuery) {
                      $mq->where('full_name', 'LIKE', "%{$searchQuery}%")
                         ->orWhere('district', 'LIKE', "%{$searchQuery}%")
                         ->orWhere('mandal', 'LIKE', "%{$searchQuery}%")
                         ->orWhere('grama_panchayat', 'LIKE', "%{$searchQuery}%");
                  });
            });
        }

        $volunteers = $volunteersQuery->orderBy('id', 'asc')->paginate(12)->withQueryString();
        $totalApprovedCount = $approvedBase->count();

        return view('team', compact(
            'volunteers',
            'cadres',
            'countries',
            'states',
            'districts',
            'assemblies',
            'mandals',
            'panchayats',
            'selectedCadre',
            'selectedCountry',
            'selectedState',
            'selectedDistrict',
            'selectedAssembly',
            'selectedMandal',
            'selectedPanchayat',
            'searchQuery',
            'totalApprovedCount'
        ));
    }
        // 5. Display Public Single Project Full Details Page
    public function showProject($id)
    {
        // Fetch the specific core service project from database using ID
        $project = DB::table('our_supports')->where('id', $id)->where('status', 'show')->first();

        // If project not found, redirect back to home page
        if (!$project) {
            return redirect()->route('public.home');
        }

        return view('project_details', compact('project'));
    }

}
