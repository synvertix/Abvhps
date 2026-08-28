<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\RudrasenaEligibilityService;
use App\Support\ApiMediaHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WingController extends Controller
{
    /**
     * Official metadata dictionary for all 4 ABVHPS wings.
     */
    private static function getWingsData(): array
    {
        return [
            'rudrasena' => [
                'slug'                 => 'rudrasena',
                'name'                 => 'Rudrasena Dal',
                'slogan'               => 'Dharma Rakshana & Disaster Relief Brigade',
                'tagline'              => 'Dedicated youth volunteer corps for emergency relief, temple protection, and humanitarian response.',
                'description'          => 'Rudrasena Dal is the disciplined volunteer wing of ABVHPS trained for rapid response during cultural gatherings, disaster mitigation, medical emergencies, and the preservation of sacred Hindu heritage and traditions.',
                'eligibility_criteria' => 'Registered ABVHPS life member aged strictly between 24 and 44 years.',
                'min_age'              => RudrasenaEligibilityService::MIN_AGE,
                'max_age'              => RudrasenaEligibilityService::MAX_AGE,
                'requires_age_check'   => true,
                'key_initiatives'      => [
                    'Disaster relief and humanitarian assistance during natural calamities.',
                    'Temple security, crowd management, and sacred festival coordination.',
                    'First-aid medical assistance camps and blood donation drives.',
                    'Preservation and defense of Sanathana Dharma values.',
                ],
                'badge_icon'           => 'shield',
                'image_url'            => ApiMediaHelper::resolveUrl('images/ABVHPS_LOGO.jpg'),
            ],
            'kala-brundam' => [
                'slug'                 => 'kala-brundam',
                'name'                 => 'Kala Brundam',
                'slogan'               => 'Sanathana Cultural & Performing Arts Network',
                'tagline'              => 'Reviving and celebrating sacred art forms, bhajans, Harikatha, and cultural drama.',
                'description'          => 'Kala Brundam brings together artists, bhajan troupes, Harikatha scholars, Burrakatha performers, and devotional musicians to celebrate Sanathana Dharma through traditional performing arts across villages and towns.',
                'eligibility_criteria' => 'ABVHPS registered member artists, bhajan groups, and cultural performers.',
                'min_age'              => null,
                'max_age'              => null,
                'requires_age_check'   => false,
                'key_initiatives'      => [
                    'Village-level bhajan sandhyas and Harikatha performances.',
                    'Preservation of traditional folk drama and devotional arts.',
                    'Cultural competitions and youth talent encouragement.',
                    'Celebration of sacred festivals with traditional fanfare.',
                ],
                'badge_icon'           => 'music',
                'image_url'            => ApiMediaHelper::resolveUrl('images/ABVHPS_LOGO.jpg'),
            ],
            'grama-seva-dal' => [
                'slug'                 => 'grama-seva-dal',
                'name'                 => 'Grama Seva Dal',
                'slogan'               => 'Rural Seva & Village Empowerment Network',
                'tagline'              => 'Grassroots youth brigade committed to village cleanliness, tree planting, and temple renovation.',
                'description'          => 'Grama Seva Dal organizes localized youth units in every panchayat to undertake community development, temple sanitation, village water resource rejuvenation, and social service.',
                'eligibility_criteria' => 'Village youth with an active ABVHPS membership commitment.',
                'min_age'              => null,
                'max_age'              => null,
                'requires_age_check'   => false,
                'key_initiatives'      => [
                    'Temple cleaning (Swachh Devalayam) and heritage conservation.',
                    'Village tree planting drives (Vriksha Bandhana).',
                    'Assisting elder and needy residents with essential supplies.',
                    'Community health awareness and village hygiene drives.',
                ],
                'badge_icon'           => 'home',
                'image_url'            => ApiMediaHelper::resolveUrl('images/ABVHPS_LOGO.jpg'),
            ],
            'organic-farmers' => [
                'slug'                 => 'organic-farmers',
                'name'                 => 'Organic Farmers Network',
                'slogan'               => 'Desi Cow-Based Natural Agriculture Wing',
                'tagline'              => 'Empowering farmers with indigenous cow-based natural farming techniques and crop clusters.',
                'description'          => 'The Organic Farmers Network promotes Gau Adharita Vyavasayam (cow-based natural farming), indigenous seeds conservation, organic crop certification guidance, and sustainable rural livelihoods.',
                'eligibility_criteria' => 'Cultivators and farmers practicing or transitioning to natural/cow-based farming.',
                'min_age'              => null,
                'max_age'              => null,
                'requires_age_check'   => false,
                'key_initiatives'      => [
                    'Cow-based natural agriculture training and Jeevamrutham preparation.',
                    'Protection and promotion of indigenous (Desi) cow breeds.',
                    'Direct village-to-devotee organic produce markets.',
                    'Soil rejuvenation and chemical-free sustainable farming.',
                ],
                'badge_icon'           => 'leaf',
                'image_url'            => ApiMediaHelper::resolveUrl('images/ABVHPS_LOGO.jpg'),
            ],
        ];
    }

    /**
     * List all 4 public ABVHPS wings.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => array_values(self::getWingsData()),
            'message' => null,
        ]);
    }

    /**
     * Show single wing details.
     */
    public function show(string $slug): JsonResponse
    {
        $wings = self::getWingsData();

        if (!isset($wings[$slug])) {
            return response()->json([
                'success' => false,
                'message' => 'Wing not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $wings[$slug],
            'message' => null,
        ]);
    }

    /**
     * Verify Rudrasena eligibility against 12-digit membership ID.
     * Calculates age from authoritative database record and enforces 24-44 years inclusive.
     */
    public function verifyRudrasenaEligibility(Request $request): JsonResponse
    {
        $request->validate([
            'membership_id' => 'required|string|size:12|regex:/^[0-9]{12}$/',
        ], [
            'membership_id.required' => 'Please provide your 12-digit Membership ID.',
            'membership_id.size'     => 'Membership ID must be exactly 12 numeric digits.',
            'membership_id.regex'    => 'Membership ID must contain digits only.',
        ]);

        $membershipId = trim((string) $request->input('membership_id'));

        $member = DB::table('memberships')
            ->where('membership_id', $membershipId)
            ->first();

        if (!$member) {
            return response()->json([
                'success'  => false,
                'eligible' => false,
                'message'  => 'Given 12-Digit Membership ID is not registered in our central portal.',
            ], 404);
        }

        $dobField = $member->dob ?? ($member->date_of_birth ?? null);
        if (empty($dobField)) {
            return response()->json([
                'success'  => false,
                'eligible' => false,
                'message'  => 'Your core membership profile does not have a Date of Birth recorded. Please update your profile.',
            ], 422);
        }

        try {
            $age = RudrasenaEligibilityService::calculateAge($dobField);
        } catch (\Throwable $e) {
            return response()->json([
                'success'  => false,
                'eligible' => false,
                'message'  => 'Invalid Date of Birth recorded in core membership profile.',
            ], 422);
        }

        $isEligible = RudrasenaEligibilityService::isAgeEligible($dobField);

        if (!$isEligible) {
            return response()->json([
                'success'  => true,
                'eligible' => false,
                'age'      => $age,
                'message'  => RudrasenaEligibilityService::validationMessage($age),
            ]);
        }

        return response()->json([
            'success'  => true,
            'eligible' => true,
            'age'      => $age,
            'data'     => [
                'membership_id' => $member->membership_id,
                'full_name'     => $member->full_name,
                'district'      => $member->district,
                'state'         => $member->state,
            ],
            'message'  => "Membership status verified & Age clearance granted ({$age} years). You are eligible for Rudrasena Dal!",
        ]);
    }
}
