<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Admin Global Settings Desk
     */
    public function adminIndex()
    {
        $settings = [
            'site_title' => SiteSetting::get('site_title', 'ABVHPS - Akhanda Bharatha Viswa Hindu Parirakshana Samiti'),
            'contact_phone' => SiteSetting::get('contact_phone', '+91 8884933379'),
            'whatsapp_number' => SiteSetting::getWhatsAppNumber(),
            'contact_email' => SiteSetting::get('contact_email', 'info@abvhps.org'),
            'contact_address' => SiteSetting::get('contact_address', 'Survey No:1826, Shanmukhapuram, Akkalareddy Palli Village and Post, Porumamilla Mandalam, Kadapa, A.P - 516193'),
            'facebook_url' => SiteSetting::get('facebook_url', 'https://facebook.com/abvhps'),
            'twitter_url' => SiteSetting::get('twitter_url', 'https://twitter.com/abvhps'),
            'youtube_url' => SiteSetting::get('youtube_url', 'https://youtube.com/@abvhps'),
            'footer_about' => SiteSetting::get('footer_about', 'Dedicated to preserving and promoting Hindu culture and values worldwide under the behest of Rajaguru Sri Sri Sri Subrahmanneswara Swamy Garu.'),
            'membership_fee' => SiteSetting::get('membership_fee', '100.00'),
            'volunteer_fee' => SiteSetting::get('volunteer_fee', '150.00'),

            // Homepage Floating Join / Membership Strip Settings
            'homepage_join_enabled' => SiteSetting::get('homepage_join_enabled', '1'),
            'homepage_join_why_heading' => SiteSetting::get('homepage_join_why_heading', 'WHY JOIN ABVHPS?'),
            'homepage_join_why_text' => SiteSetting::get('homepage_join_why_text', 'Become part of a service-oriented community committed to Dharma, social service, cultural awareness and organized community service.'),
            'homepage_join_member_heading' => SiteSetting::get('homepage_join_member_heading', SiteSetting::get('homepage_join_vol_heading', 'BECOME AN ABVHPS MEMBER')),
            'homepage_join_member_text' => SiteSetting::get('homepage_join_member_text', SiteSetting::get('homepage_join_vol_text', 'Join our growing community and participate in Dharma, Seva, cultural and social initiatives through ABVHPS.')),
            'homepage_join_cta_text' => SiteSetting::get('homepage_join_cta_text', 'BECOME A MEMBER'),
            'homepage_join_secondary_cta_text' => SiteSetting::get('homepage_join_secondary_cta_text', ''),
            'homepage_join_secondary_cta_url' => SiteSetting::get('homepage_join_secondary_cta_url', ''),

            // Homepage Supporting Partners / Sponsors Settings
            'homepage_sponsors_enabled' => SiteSetting::get('homepage_sponsors_enabled', '1'),
            'homepage_sponsors_heading' => SiteSetting::get('homepage_sponsors_heading', 'OUR SUPPORTING PARTNERS'),
            'homepage_sponsors_list' => SiteSetting::get('homepage_sponsors_list', "Synvertix Technologies\nMMP\nMMS\nMMA\nTaskly"),
            'supporting_partners' => SiteSetting::getSupportingPartners(),
        ];

        return view('admin.settings_index', compact('settings'));
    }

    /**
     * Admin Update Global Settings
     */
    public function adminUpdate(Request $request)
    {
        $rules = [
            'site_title' => 'string|max:255',
            'contact_phone' => 'string|max:50',
            'whatsapp_number' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[\+]?[0-9\s\-\(\)]+$/',
                function ($attribute, $value, $fail) {
                    $digits = preg_replace('/[^0-9]/', '', (string)$value);
                    if (strlen($digits) < 10 || strlen($digits) > 15) {
                        $fail('The WhatsApp number must contain between 10 and 15 digits.');
                    }
                }
            ],
            'contact_email' => 'email|max:100',
            'contact_address' => 'string|max:500',
            'facebook_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'footer_about' => 'string|max:1000',

            // Homepage Floating Join / Membership Strip Validation
            'homepage_join_enabled' => 'nullable|in:0,1,yes,no',
            'homepage_join_why_heading' => 'nullable|string|max:255',
            'homepage_join_why_text' => 'nullable|string|max:1000',
            'homepage_join_member_heading' => 'nullable|string|max:255',
            'homepage_join_member_text' => 'nullable|string|max:1000',
            'homepage_join_cta_text' => 'nullable|string|max:100',
            'homepage_join_secondary_cta_text' => 'nullable|string|max:100',
            'homepage_join_secondary_cta_url' => 'nullable|string|max:255',

            // Homepage Supporting Partners / Sponsors Validation
            'homepage_sponsors_enabled' => 'nullable|in:0,1,yes,no',
            'homepage_sponsors_heading' => 'nullable|string|max:255',
            'homepage_sponsors_list' => 'nullable|string|max:5000',
            'new_partner_name' => 'nullable|string|max:120',
            'new_partner_order' => 'nullable|integer',
            'new_partner_logo' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'partner_logos.*' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'partners.*.name' => 'nullable|string|max:120',
            'partners.*.order' => 'nullable|integer',
        ];

        $request->validate($rules);

        foreach (['site_title', 'contact_phone', 'whatsapp_number', 'contact_email', 'contact_address', 'facebook_url', 'twitter_url', 'youtube_url', 'footer_about', 'homepage_join_enabled', 'homepage_join_why_heading', 'homepage_join_why_text', 'homepage_join_member_heading', 'homepage_join_member_text', 'homepage_join_cta_text', 'homepage_join_secondary_cta_text', 'homepage_join_secondary_cta_url', 'homepage_sponsors_enabled', 'homepage_sponsors_heading'] as $key) {
            if ($request->has($key)) {
                SiteSetting::set($key, $request->input($key));
            }
        }

        // Handle Supporting Partners Structured Management
        $removePartnerIds = $request->input('remove_partner_ids', []);
        if (!is_array($removePartnerIds)) {
            $removePartnerIds = [$removePartnerIds];
        }

        $removeLogoIds = $request->input('remove_logo_ids', []);
        if (!is_array($removeLogoIds)) {
            $removeLogoIds = [$removeLogoIds];
        }

        $partnersInput = $request->input('partners', []);
        $partnerLogos = $request->file('partner_logos', []);

        $updatedPartners = [];

        if (is_array($partnersInput) && count($partnersInput) > 0) {
            foreach ($partnersInput as $id => $data) {
                // If marked for partner removal, skip and delete managed logo
                if (in_array((string)$id, array_map('strval', $removePartnerIds), true)) {
                    $existingLogo = $data['existing_logo_path'] ?? null;
                    if ($existingLogo && str_starts_with($existingLogo, 'partners/')) {
                        Storage::disk('public')->delete($existingLogo);
                    }
                    continue;
                }

                $name = !empty($data['name']) ? trim((string)$data['name']) : '';
                if ($name === '') {
                    continue;
                }

                $logoPath = !empty($data['existing_logo_path']) ? $data['existing_logo_path'] : null;

                // Check if marked for logo removal
                if (in_array((string)$id, array_map('strval', $removeLogoIds), true)) {
                    if ($logoPath && str_starts_with($logoPath, 'partners/')) {
                        Storage::disk('public')->delete($logoPath);
                    }
                    $logoPath = null;
                }

                // Check if replacement logo uploaded
                if (isset($partnerLogos[$id]) && $partnerLogos[$id]->isValid()) {
                    if ($logoPath && str_starts_with($logoPath, 'partners/')) {
                        Storage::disk('public')->delete($logoPath);
                    }
                    $logoPath = $partnerLogos[$id]->store('partners', 'public');
                }

                $order = isset($data['order']) ? (int)$data['order'] : count($updatedPartners) + 1;

                $updatedPartners[] = [
                    'id'        => (string)$id,
                    'name'      => $name,
                    'logo_path' => $logoPath,
                    'order'     => $order,
                ];
            }
        } elseif ($request->has('homepage_sponsors_list')) {
            // Backward compatibility fallback from raw textarea
            $rawList = (string)$request->input('homepage_sponsors_list');
            $lines = preg_split('/\r\n|\r|\n/', $rawList);
            $order = 1;
            foreach ($lines as $line) {
                $name = trim($line);
                if ($name === '') continue;
                $updatedPartners[] = [
                    'id'        => 'partner_' . substr(md5($name . $order), 0, 10),
                    'name'      => $name,
                    'logo_path' => null,
                    'order'     => $order++,
                ];
            }
        } else {
            // Preserve current partners if not submitting partners array (e.g. adding new partner standalone)
            $existingList = SiteSetting::getSupportingPartners();
            foreach ($existingList as $p) {
                $id = $p['id'];
                if (in_array((string)$id, array_map('strval', $removePartnerIds), true)) {
                    if (!empty($p['logo_path']) && str_starts_with($p['logo_path'], 'partners/')) {
                        Storage::disk('public')->delete($p['logo_path']);
                    }
                    continue;
                }
                $logoPath = $p['logo_path'];
                if (in_array((string)$id, array_map('strval', $removeLogoIds), true)) {
                    if (!empty($logoPath) && str_starts_with($logoPath, 'partners/')) {
                        Storage::disk('public')->delete($logoPath);
                    }
                    $logoPath = null;
                }
                $updatedPartners[] = [
                    'id'        => $p['id'],
                    'name'      => $p['name'],
                    'logo_path' => $logoPath,
                    'order'     => $p['order'] ?? (count($updatedPartners) + 1),
                ];
            }
        }

        // Add new partner if submitted
        if ($request->filled('new_partner_name')) {
            $newName = trim((string)$request->input('new_partner_name'));
            $newLogoPath = null;
            if ($request->hasFile('new_partner_logo') && $request->file('new_partner_logo')->isValid()) {
                $newLogoPath = $request->file('new_partner_logo')->store('partners', 'public');
            }
            $newOrder = $request->filled('new_partner_order') ? (int)$request->input('new_partner_order') : count($updatedPartners) + 1;

            $newPartnerItem = [
                'id'        => 'partner_' . uniqid(),
                'name'      => $newName,
                'logo_path' => $newLogoPath,
                'order'     => $newOrder,
            ];

            if ($newOrder <= 1) {
                array_unshift($updatedPartners, $newPartnerItem);
            } else {
                $updatedPartners[] = $newPartnerItem;
            }
        }

        if (count($updatedPartners) > 0 || $request->has('partners') || $request->has('homepage_sponsors_list')) {
            SiteSetting::setSupportingPartners($updatedPartners);
        }

        // Handle Custom Logo Upload
        if ($request->hasFile('site_logo')) {
            $request->validate(['site_logo' => 'image|max:2048']);
            $logoPath = $request->file('site_logo')->storeAs('images', 'logo.png', 'public');
            copy(storage_path('app/public/images/logo.png'), public_path('images/logo.png'));
            SiteSetting::set('site_logo', 'images/logo.png');
        }

        // Handle Custom Favicon Upload
        if ($request->hasFile('site_favicon')) {
            $request->validate(['site_favicon' => 'image|max:1024']);
            $favPath = $request->file('site_favicon')->storeAs('images', 'favicon.png', 'public');
            copy(storage_path('app/public/images/favicon.png'), public_path('favicon.png'));
            copy(storage_path('app/public/images/favicon.png'), public_path('favicon.ico'));
            SiteSetting::set('site_favicon', 'favicon.png');
        }

        return redirect()->route('admin.settings.index')->with('success', 'Global Site Settings updated and synced across all pages.');
    }
}
