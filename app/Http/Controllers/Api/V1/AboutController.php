<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\SiteSetting;
use App\Support\ApiMediaHelper;
use Illuminate\Http\JsonResponse;

class AboutController extends Controller
{
    /**
     * Public About Us page dynamic content API.
     * Replicates the exact mission, values, and organizational info from the web about page.
     */
    public function __invoke(): JsonResponse
    {
        $aboutBanner = Banner::getBannerForPage('about');
        $bannerData = null;
        if ($aboutBanner && !empty($aboutBanner->desktop_banner)) {
            $bannerData = [
                'title'          => $aboutBanner->title,
                'subtitle'       => $aboutBanner->subtitle,
                'desktop_banner' => ApiMediaHelper::resolveUrl($aboutBanner->desktop_banner),
                'mobile_banner'  => ApiMediaHelper::resolveUrl($aboutBanner->mobile_banner),
            ];
        }

        $organization = [
            'name'           => 'Akhanda Bharatha Viswa Hindu Parirakshana Samiti',
            'short_name'     => 'ABVHPS',
            'tagline'        => 'Preserving Sanathana Dharma and Empowering Communities',
            'founded_year'   => (int) SiteSetting::get('organization_founded_year', 2023),
            'registration_no'=> '20/2023',
            'founder_guru'   => 'Rajaguru Sri Sri Sri Subrahmanneswara Swamy Garu',
            'logo_url'       => ApiMediaHelper::resolveUrl('images/ABVHPS_LOGO.jpg'),
            'origin'         => [
                str_replace(':guru', config('abvhps.copy.guru'), config('abvhps.copy.origin_1')),
                config('abvhps.copy.origin_2'),
            ],
        ];

        $mission = [
            'title' => 'Our Mission in Action',
            'paragraphs' => [
                'Akhanda Bharatha Viswa Hindu Parirakshana Samiti (ABVHPS) is dedicated to safeguarding, nurturing, and propagating the timeless principles of Sanathana Dharma across every village, mandal, and district. Through proactive grassroots initiatives, we unite communities to preserve cultural heritage, temple welfare, and traditional values.',
                'Our mission encompasses selfless service (Seva), educational support for underprivileged youth, comprehensive healthcare camps, Gau Samrakshana (cow protection), and rural empowerment through specialized wings like Rudrasena, Kala Brundam, Grama Seva Dal, and Organic Farmers support desks.',
            ],
        ];

        $coreValues = [
            [
                'id'          => 'dharma_rakshana',
                'title'       => 'Dharma Rakshana',
                'description' => 'Upholding the timeless heritage, sacred temples, and spiritual practices of Sanathana Dharma with utmost reverence.',
                'icon'        => 'temple',
            ],
            [
                'id'          => 'nishkama_seva',
                'title'       => 'Nishkama Seva',
                'description' => 'Serving the needy, promoting social welfare, and providing humanitarian assistance without any expectation of personal gain.',
                'icon'        => 'handshake',
            ],
            [
                'id'          => 'grama_vikas',
                'title'       => 'Grama Vikas',
                'description' => 'Empowering rural communities through sustainable agriculture, environmental conservation, and local youth development.',
                'icon'        => 'sprout',
            ],
            [
                'id'          => 'unity_integrity',
                'title'       => 'Unity & Integrity',
                'description' => 'Fostering national integrity, social harmony, and collective brotherhood among all sections of society.',
                'icon'        => 'shield',
            ],
        ];

        $pillars = [
            ['title' => 'Our Vision',  'description' => config('abvhps.copy.vision')],
            ['title' => 'Our Mission', 'description' => config('abvhps.copy.mission')],
            ['title' => 'The Goal',    'description' => config('abvhps.copy.goal')],
        ];

        return response()->json([
            'success' => true,
            'data'    => [
                'banner'       => $bannerData,
                'organization' => $organization,
                'mission'      => $mission,
                'core_values'  => $coreValues,
                'pillars'      => $pillars,
            ],
            'message' => null,
        ]);
    }
}
