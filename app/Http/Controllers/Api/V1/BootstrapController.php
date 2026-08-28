<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;

class BootstrapController extends Controller
{
    /**
     * Public Bootstrap configuration.
     * Returns ONLY safe, public allowlisted configuration parameters.
     */
    public function __invoke(): JsonResponse
    {
        $organizationName = SiteSetting::get('site_name', 'Akhanda Bharata Viswa Hindu Parirakshana Samiti');
        $tagline = SiteSetting::get('site_tagline');
        $supportEmail = SiteSetting::get('contact_email', 'info@abvhps.org');
        $supportPhone = SiteSetting::get('contact_phone', '+91 9989980055');
        $whatsappNumber = SiteSetting::getWhatsAppNumber();
        $socialLinks = SiteSetting::getActiveSocialLinks();

        return response()->json([
            'success' => true,
            'data'    => [
                'organization' => [
                    'name'         => $organizationName,
                    'short_name'   => 'ABVHPS',
                    'tagline'      => $tagline,
                    'contact_email'=> $supportEmail,
                    'contact_phone'=> $supportPhone,
                    'whatsapp'     => $whatsappNumber,
                ],
                'social_links' => array_values($socialLinks),
                'app_config'   => [
                    'min_supported_version' => '1.0.0',
                    'latest_version'        => '1.0.0',
                    'features'              => [
                        'member_login'    => true,
                        'volunteer_login' => true,
                    ],
                ],
            ],
            'message' => null,
        ]);
    }
}
