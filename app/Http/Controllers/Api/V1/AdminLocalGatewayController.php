<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminLocalGatewayController extends Controller
{
    /**
     * GET /api/v1/admin/local-gateways
     * Unified Local GP Gateways Roster
     */
    public function index(Request $request)
    {
        $category = $request->query('category', 'all'); // 'all', 'kala_brundam', 'grama_seva_dal', 'organic_farmers'
        $status = $request->query('status', 'all');     // 'all', 'pending', 'approved'

        $allGroups = collect();

        // 1. Kala Brundam Groups
        if ($category === 'all' || $category === 'kala_brundam') {
            $q = DB::table('kala_brundams')
                ->leftJoin('kala_brundam_members', 'kala_brundams.id', '=', 'kala_brundam_members.kala_brundam_id')
                ->select(
                    'kala_brundams.id',
                    'kala_brundams.team_registration_id as reg_id',
                    'kala_brundams.team_name as name',
                    'kala_brundams.team_type as sub_type',
                    'kala_brundams.location as gp_location',
                    'kala_brundams.status',
                    'kala_brundams.created_at',
                    DB::raw('COUNT(kala_brundam_members.id) as members_count')
                )
                ->groupBy('kala_brundams.id', 'kala_brundams.team_registration_id', 'kala_brundams.team_name', 'kala_brundams.team_type', 'kala_brundams.location', 'kala_brundams.status', 'kala_brundams.created_at');

            if ($status !== 'all') {
                $q->where('kala_brundams.status', $status);
            }

            $kb = $q->get()->map(function ($item) {
                return [
                    'id'            => $item->id,
                    'reg_id'        => $item->reg_id,
                    'name'          => $item->name,
                    'sub_type'      => $item->sub_type,
                    'gp_location'   => $item->gp_location,
                    'status'        => $item->status ?? 'pending',
                    'wing'          => 'Kala Brundam',
                    'wing_key'      => 'kala_brundam',
                    'members_count' => (int) $item->members_count,
                    'created_at'    => $item->created_at,
                ];
            });
            $allGroups = $allGroups->concat($kb);
        }

        // 2. Grama Seva Dal Groups
        if ($category === 'all' || $category === 'grama_seva_dal') {
            $q = DB::table('grama_seva_dals')
                ->leftJoin('grama_seva_dal_members', 'grama_seva_dals.id', '=', 'grama_seva_dal_members.grama_seva_dal_id')
                ->select(
                    'grama_seva_dals.id',
                    'grama_seva_dals.gong_registration_id as reg_id',
                    'grama_seva_dals.leader_name as name',
                    'grama_seva_dals.leader_mobile as sub_type',
                    'grama_seva_dals.village_or_gp',
                    'grama_seva_dals.mandal',
                    'grama_seva_dals.status',
                    'grama_seva_dals.created_at',
                    DB::raw('COUNT(grama_seva_dal_members.id) as members_count')
                )
                ->groupBy('grama_seva_dals.id', 'grama_seva_dals.gong_registration_id', 'grama_seva_dals.leader_name', 'grama_seva_dals.leader_mobile', 'grama_seva_dals.village_or_gp', 'grama_seva_dals.mandal', 'grama_seva_dals.status', 'grama_seva_dals.created_at');

            if ($status !== 'all') {
                $q->where('grama_seva_dals.status', $status);
            }

            $gsd = $q->get()->map(function ($item) {
                $loc = trim(($item->village_or_gp ?? '') . ', ' . ($item->mandal ?? ''), ', ');
                return [
                    'id'            => $item->id,
                    'reg_id'        => $item->reg_id,
                    'name'          => $item->name,
                    'sub_type'      => $item->sub_type,
                    'gp_location'   => $loc,
                    'status'        => $item->status ?? 'pending',
                    'wing'          => 'Grama Seva Dal',
                    'wing_key'      => 'grama_seva_dal',
                    'members_count' => (int) $item->members_count,
                    'created_at'    => $item->created_at,
                ];
            });
            $allGroups = $allGroups->concat($gsd);
        }

        // 3. Organic Farmers Groups
        if ($category === 'all' || $category === 'organic_farmers') {
            $q = DB::table('organic_farmers')
                ->select(
                    'organic_farmers.id',
                    'organic_farmers.farmer_registration_id as reg_id',
                    'organic_farmers.farmer_name as name',
                    'organic_farmers.farmer_mobile as sub_type',
                    'organic_farmers.land_size_acres',
                    'organic_farmers.water_source',
                    'organic_farmers.status',
                    'organic_farmers.created_at',
                    DB::raw('1 as members_count')
                );

            if ($status !== 'all') {
                $q->where('organic_farmers.status', $status);
            }

            $of = $q->get()->map(function ($item) {
                $loc = $item->land_size_acres ? $item->land_size_acres . ' Acres (' . ($item->water_source ?? 'Rainfed') . ')' : 'Farmland';
                return [
                    'id'            => $item->id,
                    'reg_id'        => $item->reg_id,
                    'name'          => $item->name,
                    'sub_type'      => $item->sub_type,
                    'gp_location'   => $loc,
                    'status'        => $item->status ?? 'pending',
                    'wing'          => 'Organic Farmers',
                    'wing_key'      => 'organic_farmers',
                    'members_count' => (int) $item->members_count,
                    'created_at'    => $item->created_at,
                ];
            });
            $allGroups = $allGroups->concat($of);
        }

        $stats = [
            'total_groups'     => $allGroups->count(),
            'pending_approval' => $allGroups->where('status', 'pending')->count(),
            'approved_active'  => $allGroups->where('status', 'approved')->count(),
            'kala_brundam'     => $allGroups->where('wing_key', 'kala_brundam')->count(),
            'grama_seva_dal'   => $allGroups->where('wing_key', 'grama_seva_dal')->count(),
            'organic_farmers'  => $allGroups->where('wing_key', 'organic_farmers')->count(),
        ];

        return response()->json([
            'success' => true,
            'stats'   => $stats,
            'data'    => $allGroups->values(),
        ]);
    }

    /**
     * POST /api/v1/admin/local-gateways/approve/{wing}/{id}
     * Approve Local GP Group
     */
    public function approve($wing, $id)
    {
        $tableMap = [
            'kala_brundam'    => 'kala_brundams',
            'grama_seva_dal'  => 'grama_seva_dals',
            'organic_farmers' => 'organic_farmers',
        ];

        if (!isset($tableMap[$wing])) {
            return response()->json(['success' => false, 'message' => 'Invalid wing category'], 422);
        }

        $table = $tableMap[$wing];
        $group = DB::table($table)->where('id', $id)->first();

        if (!$group) {
            return response()->json(['success' => false, 'message' => 'Group record not found'], 404);
        }

        DB::table($table)->where('id', $id)->update([
            'status'     => 'approved',
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Group approved successfully.',
        ]);
    }

    /**
     * DELETE /api/v1/admin/local-gateways/delete/{wing}/{id}
     * Delete Local GP Group
     */
    public function destroy($wing, $id)
    {
        $tableMap = [
            'kala_brundam'    => 'kala_brundams',
            'grama_seva_dal'  => 'grama_seva_dals',
            'organic_farmers' => 'organic_farmers',
        ];

        if (!isset($tableMap[$wing])) {
            return response()->json(['success' => false, 'message' => 'Invalid wing category'], 422);
        }

        $table = $tableMap[$wing];
        DB::table($table)->where('id', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Group record deleted successfully.',
        ]);
    }
}
