<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
        /**
     * Display the Central Master Administrative Dashboard Grid with Secure Analytics Fallbacks
     */
    public function showMasterDashboard(\App\Services\AdminDashboardService $dashboardService)
    {
        $dashboardData = $dashboardService->getDashboardMetrics();
        $stats = $dashboardData['stats'];

        // ── RECENT AUDIT ACTIVITY (last 8 entries) ────────────────────────
        try {
            $recentActivity = DB::table('audit_logs')
                ->orderBy('created_at', 'desc')
                ->limit(8)
                ->get(['action', 'actor_type', 'actor_identifier', 'target_type', 'target_id', 'created_at']);
        } catch (\Exception $e) {
            $recentActivity = collect([]);
        }

        // ── RECENT MEMBERS ────────────────────────────────────────────────
        try {
            $recentMembers = DB::table('memberships')->orderBy('id', 'desc')->limit(5)->get(['id', 'full_name', 'membership_id', 'status', 'created_at']);
        } catch (\Exception $e) {
            $recentMembers = collect([]);
        }

        // ── ACTIVE CAMPAIGNS ──────────────────────────────────────────────
        try {
            $activeCampaigns = DB::table('fundraisings')->where('is_active', true)->orderBy('id', 'desc')->get(['id', 'title', 'raised_amount', 'goal_amount']);
        } catch (\Exception $e) {
            try {
                $activeCampaigns = DB::table('fundraising_campaigns')->where('status', 'active')->orderBy('id', 'desc')->get(['id', 'title', 'raised_amount', 'goal_amount']);
            } catch (\Exception $ex) {
                $activeCampaigns = collect([]);
            }
        }

        return view('admin.dashboard', compact('stats', 'recentMembers', 'activeCampaigns', 'recentActivity'));
    }

    /**
     * Process Administrative Ingestion Gateways to Approve or Defer Application Packets
     */
    public function processWingApproval(Request $request)
    {
        $request->validate([
            'membership_id' => 'required|string|size:12',
            'wing_type' => 'required|string|in:rudrasena,kala_brundam,grama_seva_dal,organic_farmers',
            'action_status' => 'required|string|in:approve,reject'
        ]);

        // Validate if the targeted member is legitimately present inside central database registries
        $member = DB::table('memberships')->where('membership_id', $request->membership_id)->first();
        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Action Blocked: Targeted identity does not match central database footprints.'
            ]);
        }

        // Execute dynamic state routing parameters within transaction blocks
        DB::beginTransaction();
        try {
            if ($request->action_status === 'approve') {
                switch ($request->wing_type) {
                    case 'rudrasena':
                        // Inject into Rudrasena active military roster vault if absent
                        $exists = DB::table('rudrasenas')->where('membership_id', $request->membership_id)->exists();
                        if (!$exists) {
                            DB::table('rudrasenas')->insert([
                                'membership_id' => $request->membership_id,
                                'full_name' => $member->full_name,
                                'phone' => $member->phone,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now()
                            ]);
                        }
                        break;

                    case 'kala_brundam':
                        $exists = DB::table('kala_brundam_members')->where('membership_id', $request->membership_id)->exists();
                        if (!$exists) {
                            DB::table('kala_brundam_members')->insert([
                                'membership_id' => $request->membership_id,
                                'full_name' => $member->full_name,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now()
                            ]);
                        }
                        break;
                }
            } else {
                // Execute rollback delete removals if action state is set to explicit reject
                switch ($request->wing_type) {
                    case 'rudrasena':
                        DB::table('rudrasenas')->where('membership_id', $request->membership_id)->delete();
                        break;
                    case 'kala_brundam':
                        DB::table('kala_brundam_members')->where('membership_id', $request->membership_id)->delete();
                        break;
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => '🎉 Administrative status updated and broadcasted inside core system registries.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Critical infrastructure deadlock failure during transmission validation.'
            ]);
        }
    }
}
