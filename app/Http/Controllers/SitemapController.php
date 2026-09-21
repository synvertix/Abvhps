<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Models\FundraisingCampaign;
use Carbon\Carbon;

class SitemapController extends Controller
{
    /**
     * Generate dynamic XML Sitemap adhering to sitemaps.org schema.
     */
    public function index(): Response
    {
        $baseUrl = rtrim(config('app.url', 'https://abvhps.org'), '/');

        // Static core public pages with realistic change frequencies and priorities
        $staticPages = [
            ['loc' => $baseUrl . '/', 'changefreq' => 'daily', 'priority' => '1.0', 'lastmod' => Carbon::now()->startOfDay()->toIso8601String()],
            ['loc' => $baseUrl . '/about', 'changefreq' => 'monthly', 'priority' => '0.8', 'lastmod' => null],
            ['loc' => $baseUrl . '/membership', 'changefreq' => 'monthly', 'priority' => '0.8', 'lastmod' => null],
            ['loc' => $baseUrl . '/volunteer', 'changefreq' => 'monthly', 'priority' => '0.8', 'lastmod' => null],
            ['loc' => $baseUrl . '/rudrasena-apply', 'changefreq' => 'monthly', 'priority' => '0.8', 'lastmod' => null],
            ['loc' => $baseUrl . '/kala-brundam-apply', 'changefreq' => 'monthly', 'priority' => '0.7', 'lastmod' => null],
            ['loc' => $baseUrl . '/grama-seva-dal-apply', 'changefreq' => 'monthly', 'priority' => '0.7', 'lastmod' => null],
            ['loc' => $baseUrl . '/organic-farmers-apply', 'changefreq' => 'monthly', 'priority' => '0.7', 'lastmod' => null],
            ['loc' => $baseUrl . '/team', 'changefreq' => 'weekly', 'priority' => '0.8', 'lastmod' => null],
            ['loc' => $baseUrl . '/gallery', 'changefreq' => 'weekly', 'priority' => '0.7', 'lastmod' => null],
            ['loc' => $baseUrl . '/donations', 'changefreq' => 'daily', 'priority' => '0.9', 'lastmod' => Carbon::now()->startOfDay()->toIso8601String()],
            ['loc' => $baseUrl . '/blogs', 'changefreq' => 'weekly', 'priority' => '0.7', 'lastmod' => null],
            ['loc' => $baseUrl . '/contact', 'changefreq' => 'monthly', 'priority' => '0.7', 'lastmod' => null],
            ['loc' => $baseUrl . '/compliance-certificates', 'changefreq' => 'monthly', 'priority' => '0.7', 'lastmod' => null],
            ['loc' => $baseUrl . '/exam-results', 'changefreq' => 'weekly', 'priority' => '0.8', 'lastmod' => null],
            ['loc' => $baseUrl . '/exams-notice-board', 'changefreq' => 'weekly', 'priority' => '0.8', 'lastmod' => null],
            ['loc' => $baseUrl . '/legal', 'changefreq' => 'yearly', 'priority' => '0.4', 'lastmod' => null],
            ['loc' => $baseUrl . '/privacy-policy', 'changefreq' => 'yearly', 'priority' => '0.4', 'lastmod' => null],
            ['loc' => $baseUrl . '/terms-and-conditions', 'changefreq' => 'yearly', 'priority' => '0.4', 'lastmod' => null],
            ['loc' => $baseUrl . '/refund-cancellation-policy', 'changefreq' => 'yearly', 'priority' => '0.4', 'lastmod' => null],
            ['loc' => $baseUrl . '/donation-payment-policy', 'changefreq' => 'yearly', 'priority' => '0.4', 'lastmod' => null],
            ['loc' => $baseUrl . '/account-deletion', 'changefreq' => 'yearly', 'priority' => '0.4', 'lastmod' => null],
        ];

        // Dynamic active public fundraising campaigns
        $campaigns = FundraisingCampaign::active()->get();
        $campaignEntries = [];
        foreach ($campaigns as $camp) {
            $campaignEntries[] = [
                'loc' => $baseUrl . '/donations/campaign/' . $camp->id,
                'changefreq' => 'daily',
                'priority' => '0.85',
                'lastmod' => $camp->updated_at ? $camp->updated_at->toIso8601String() : null,
            ];
        }

        // Dynamic active core service projects
        $projects = DB::table('our_supports')->where('status', 'show')->get();
        $projectEntries = [];
        foreach ($projects as $proj) {
            $projectEntries[] = [
                'loc' => $baseUrl . '/project/' . $proj->id,
                'changefreq' => 'monthly',
                'priority' => '0.75',
                'lastmod' => isset($proj->updated_at) ? Carbon::parse($proj->updated_at)->toIso8601String() : null,
            ];
        }

        // Dynamic active blogs
        $blogs = DB::table('blogs')->where('status', 'active')->get();
        $blogEntries = [];
        foreach ($blogs as $blog) {
            $blogEntries[] = [
                'loc' => $baseUrl . '/blogs',
                'changefreq' => 'weekly',
                'priority' => '0.7',
                'lastmod' => isset($blog->updated_at) ? Carbon::parse($blog->updated_at)->toIso8601String() : null,
            ];
        }

        $allEntries = array_merge($staticPages, $campaignEntries, $projectEntries);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($allEntries as $entry) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($entry['loc'], ENT_XML1, 'UTF-8') . "</loc>\n";
            if (!empty($entry['lastmod'])) {
                $xml .= "    <lastmod>" . $entry['lastmod'] . "</lastmod>\n";
            }
            $xml .= "    <changefreq>" . $entry['changefreq'] . "</changefreq>\n";
            $xml .= "    <priority>" . $entry['priority'] . "</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
            'X-Robots-Tag' => 'noindex', // Prevents search engines from indexing the sitemap file itself as a web page
        ]);
    }
}
