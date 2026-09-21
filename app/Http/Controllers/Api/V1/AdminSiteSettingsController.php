<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class AdminSiteSettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'site_title'                         => SiteSetting::get('site_title', 'ABVHPS - Akhanda Bharatha Viswa Hindu Parirakshana Samiti'),
            'contact_phone'                      => SiteSetting::get('contact_phone', '+91 9989980055'),
            'whatsapp_number'                   => SiteSetting::getWhatsAppNumber(),
            'contact_email'                      => SiteSetting::get('contact_email', 'info@abvhps.org'),
            'contact_address'                    => SiteSetting::get('contact_address', 'Survey No:1826, Shanmukhapuram, Akkalareddy Palli, Porumamilla, Kadapa, A.P - 516193'),
            'facebook_url'                       => SiteSetting::get('facebook_url', 'https://facebook.com/abvhps'),
            'twitter_url'                        => SiteSetting::get('twitter_url', 'https://twitter.com/abvhps'),
            'youtube_url'                        => SiteSetting::get('youtube_url', 'https://youtube.com/@abvhps'),
            'footer_about'                       => SiteSetting::get('footer_about', 'Dedicated to preserving and promoting Hindu culture and values worldwide.'),
            'membership_fee'                     => SiteSetting::get('membership_fee', '100.00'),
            'volunteer_fee'                      => SiteSetting::get('volunteer_fee', '150.00'),
            'homepage_join_enabled'              => SiteSetting::get('homepage_join_enabled', '1'),
            'homepage_join_why_heading'          => SiteSetting::get('homepage_join_why_heading', 'WHY JOIN ABVHPS?'),
            'homepage_join_why_text'             => SiteSetting::get('homepage_join_why_text', 'Become part of a service-oriented community committed to Dharma.'),
            'homepage_join_member_heading'       => SiteSetting::get('homepage_join_member_heading', 'BECOME AN ABVHPS MEMBER'),
            'homepage_join_member_text'          => SiteSetting::get('homepage_join_member_text', 'Join our growing community and participate in Dharma initiatives.'),
            'homepage_join_cta_text'             => SiteSetting::get('homepage_join_cta_text', 'BECOME A MEMBER'),
            'homepage_sponsors_enabled'          => SiteSetting::get('homepage_sponsors_enabled', '1'),
            'homepage_sponsors_heading'          => SiteSetting::get('homepage_sponsors_heading', 'OUR SUPPORTING PARTNERS'),
            'homepage_social_enabled'            => SiteSetting::get('homepage_social_enabled', '1'),
            'homepage_social_heading'            => SiteSetting::get('homepage_social_heading', 'CONNECT WITH ABVHPS'),
            'homepage_social_subtext'            => SiteSetting::get('homepage_social_subtext', 'Follow ABVHPS for updates on Seva activities.'),
            'homepage_stats_donors'              => SiteSetting::get('homepage_stats_donors', ''),
            'homepage_stats_members'             => SiteSetting::get('homepage_stats_members', ''),
            'homepage_stats_volunteers'          => SiteSetting::get('homepage_stats_volunteers', ''),
            'social_janavedika_url'              => SiteSetting::get('social_janavedika_url', ''),
            'social_facebook_url'                => SiteSetting::get('social_facebook_url', ''),
            'social_instagram_url'               => SiteSetting::get('social_instagram_url', ''),
            'social_youtube_url'                 => SiteSetting::get('social_youtube_url', ''),
            'social_x_url'                       => SiteSetting::get('social_x_url', ''),
            'social_linkedin_url'                => SiteSetting::get('social_linkedin_url', ''),
            'social_whatsapp_url'                => SiteSetting::get('social_whatsapp_url', ''),
            'social_telegram_url'                => SiteSetting::get('social_telegram_url', ''),
        ];

        return response()->json([
            'success' => true,
            'data'    => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $keys = [
            'site_title', 'contact_phone', 'whatsapp_number', 'contact_email', 'contact_address',
            'facebook_url', 'twitter_url', 'youtube_url', 'footer_about',
            'homepage_join_enabled', 'homepage_join_why_heading', 'homepage_join_why_text',
            'homepage_join_member_heading', 'homepage_join_member_text', 'homepage_join_cta_text',
            'homepage_sponsors_enabled', 'homepage_sponsors_heading',
            'homepage_social_enabled', 'homepage_social_heading', 'homepage_social_subtext',
            'social_janavedika_url', 'social_facebook_url', 'social_instagram_url', 'social_youtube_url',
            'social_x_url', 'social_linkedin_url', 'social_whatsapp_url', 'social_telegram_url',
            'homepage_stats_donors', 'homepage_stats_members', 'homepage_stats_volunteers',
        ];

        foreach ($keys as $key) {
            if ($request->has($key)) {
                $val = $request->input($key);
                $cleanVal = is_string($val) ? trim($val) : $val;
                SiteSetting::set($key, $cleanVal !== '' ? $cleanVal : null);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Site settings updated successfully.',
        ]);
    }
}
