<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\FundraisingCampaign;
use App\Models\Donation;
use Carbon\Carbon;

class FundraisingController extends Controller
{
    /**
     * Public Multi-Campaign Donations Grid (supports ?campaign={id} for crawler metadata)
     */
    public function showDonationsGrid(Request $request)
    {
        $featuredCampaign = null;
        if ($request->has('campaign')) {
            $featuredCampaign = FundraisingCampaign::find($request->get('campaign'));
        }

        $campaigns = FundraisingCampaign::active()
            ->orderBy('id', 'desc')
            ->get();

        foreach ($campaigns as $campaign) {
            $campaign->whatsapp_share = $campaign->whatsapp_share_url;
            $campaign->facebook_share = $campaign->facebook_share_url;
        }

        return view('donations_grid', compact('campaigns', 'featuredCampaign'));
    }

    /**
     * Dedicated Single Campaign Public View & Crawler Endpoint (/donations/campaign/{id})
     */
    public function showCampaign($id)
    {
        $featuredCampaign = FundraisingCampaign::findOrFail($id);

        $campaigns = FundraisingCampaign::active()
            ->orderBy('id', 'desc')
            ->get();

        foreach ($campaigns as $campaign) {
            $campaign->whatsapp_share = $campaign->whatsapp_share_url;
            $campaign->facebook_share = $campaign->facebook_share_url;
        }

        return view('donations_grid', compact('campaigns', 'featuredCampaign'));
    }

    /**
     * Admin Fundraising Matrices Dashboard (KPIs, Targets vs Raised, Status Management)
     */
    public function adminIndex()
    {
        $campaigns = FundraisingCampaign::orderBy('id', 'desc')->get();

        $totalTarget = $campaigns->sum('target_amount');
        $totalRaised = $campaigns->sum('raised_amount');
        $activeCount = $campaigns->where('status', 'active')->count();
        $expiredCount = $campaigns->where('status', 'expired')->count();

        // Also fetch total general devotee donations from donations table (only successfully paid donations)
        $paidDonations = Donation::where('payment_status', 'paid');
        $totalDevoteeDonations = (clone $paidDonations)->sum('amount');
        $donorCount = (clone $paidDonations)->count();

        $stats = [
            'total_target' => $totalTarget,
            'total_raised' => $totalRaised,
            'overall_progress' => $totalTarget > 0 ? min(round(($totalRaised / $totalTarget) * 100, 2), 100) : 0,
            'active_campaigns' => $activeCount,
            'expired_campaigns' => $expiredCount,
            'total_devotee_donations' => $totalDevoteeDonations,
            'donor_count' => $donorCount,
        ];

        $recentDonations = Donation::orderBy('id', 'desc')->take(8)->get();

        return view('admin.fundraising_index', compact('campaigns', 'stats', 'recentDonations'));
    }

    /**
     * Admin Create Campaign Form
     */
    public function showCreateForm()
    {
        return view('admin.fundraising_create');
    }

    /**
     * Admin Store New Campaign
     */
    public function storeCampaignPacket(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'target_amount' => 'required|numeric|min:1',
            'raised_amount' => 'nullable|numeric|min:0',
            'end_date' => 'required|date',
            'cover_image' => 'required|image|max:4096',
            'image_1' => 'nullable|image|max:2048',
            'image_2' => 'nullable|image|max:2048',
            'image_3' => 'nullable|image|max:2048',
            'image_4' => 'nullable|image|max:2048',
            'video_file' => 'nullable|mimes:mp4,mov,avi|max:25600'
        ]);

        $pathCover = $request->file('cover_image')->store('campaigns/covers', 'public');
        $pathImg1 = $request->hasFile('image_1') ? $request->file('image_1')->store('campaigns/gallery', 'public') : null;
        $pathImg2 = $request->hasFile('image_2') ? $request->file('image_2')->store('campaigns/gallery', 'public') : null;
        $pathImg3 = $request->hasFile('image_3') ? $request->file('image_3')->store('campaigns/gallery', 'public') : null;
        $pathImg4 = $request->hasFile('image_4') ? $request->file('image_4')->store('campaigns/gallery', 'public') : null;
        $pathVideo = $request->hasFile('video_file') ? $request->file('video_file')->store('campaigns/videos', 'public') : null;

        FundraisingCampaign::create([
            'title' => strtoupper($request->title),
            'description' => $request->description,
            'target_amount' => $request->target_amount,
            'raised_amount' => $request->raised_amount ?? 0.00,
            'end_date' => $request->end_date,
            'cover_image' => $pathCover,
            'image_1' => $pathImg1,
            'image_2' => $pathImg2,
            'image_3' => $pathImg3,
            'image_4' => $pathImg4,
            'video_path' => $pathVideo,
            'status' => 'active',
        ]);

        return redirect()->route('admin.fundraising.index')->with('success', '🎉 New Multi-Media Fundraising Campaign Deployed Successfully!');
    }

    /**
     * Admin Edit Campaign Form
     */
    public function showEditForm($id)
    {
        $campaign = FundraisingCampaign::findOrFail($id);
        return view('admin.fundraising_edit', compact('campaign'));
    }

    /**
     * Admin Update Campaign
     */
    public function updateCampaign(Request $request, $id)
    {
        $campaign = FundraisingCampaign::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'target_amount' => 'required|numeric|min:1',
            'raised_amount' => 'required|numeric|min:0',
            'end_date' => 'required|date',
            'cover_image' => 'nullable|image|max:4096',
            'status' => 'required|in:active,expired',
        ]);

        if ($request->hasFile('cover_image')) {
            $campaign->cover_image = $request->file('cover_image')->store('campaigns/covers', 'public');
        }

        $campaign->title = strtoupper($request->title);
        $campaign->description = $request->description;
        $campaign->target_amount = $request->target_amount;
        $campaign->raised_amount = $request->raised_amount;
        $campaign->end_date = $request->end_date;
        $campaign->status = $request->status;
        $campaign->save();

        return redirect()->route('admin.fundraising.index')->with('success', 'Campaign updated successfully.');
    }

    /**
     * Toggle Active / Expired Status
     */
    public function toggleStatus($id)
    {
        $campaign = FundraisingCampaign::findOrFail($id);
        $campaign->status = $campaign->status === 'active' ? 'expired' : 'active';
        $campaign->save();

        return redirect()->back()->with('success', 'Campaign status updated to ' . strtoupper($campaign->status));
    }

    /**
     * Admin Delete Campaign
     */
    public function destroyCampaign($id)
    {
        $campaign = FundraisingCampaign::findOrFail($id);
        $campaign->delete();

        return redirect()->route('admin.fundraising.index')->with('success', 'Campaign removed from matrix.');
    }
}
