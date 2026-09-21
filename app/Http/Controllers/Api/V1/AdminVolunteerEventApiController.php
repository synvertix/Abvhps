<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VolunteerEvent;
use App\Models\VolunteerEventMember;
use Illuminate\Support\Facades\Storage;

class AdminVolunteerEventApiController extends Controller
{
    /**
     * GET /api/v1/admin/volunteer-events
     * Centralized Roster of Volunteer Events & Beneficiary Ledger
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $perPage = (int) $request->query('per_page', 15);

        $query = VolunteerEvent::with(['volunteer.membership', 'eventMembers']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('venue', 'LIKE', "%{$search}%")
                  ->orWhere('mandal', 'LIKE', "%{$search}%")
                  ->orWhere('district', 'LIKE', "%{$search}%")
                  ->orWhereHas('volunteer', function ($vq) use ($search) {
                      $vq->where('volunteer_id', 'LIKE', "%{$search}%")
                         ->orWhereHas('membership', function ($mq) use ($search) {
                             $mq->where('full_name', 'LIKE', "%{$search}%");
                         });
                  });
            });
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $paginator = $query->orderBy('event_date', 'desc')->paginate($perPage);

        $items = collect($paginator->items())->map(function ($e) {
            $organizerName = $e->volunteer->membership->identity_verified_name ?? ($e->volunteer->membership->full_name ?? 'Volunteer');
            return [
                'id'                 => $e->id,
                'title'              => $e->title,
                'event_type'         => $e->event_type ?? 'Community Service',
                'event_date'         => $e->event_date ? $e->event_date->format('Y-m-d') : null,
                'formatted_date'     => $e->event_date ? $e->event_date->format('d M Y') : null,
                'venue'              => $e->venue,
                'district'           => $e->district,
                'mandal'             => $e->mandal,
                'status'             => $e->status ?? 'completed',
                'organizer_name'     => $organizerName,
                'organizer_id'       => $e->volunteer->volunteer_id ?? null,
                'participants_count' => $e->eventMembers ? $e->eventMembers->count() : 0,
                'beneficiaries_count'=> $e->eventMembers ? $e->eventMembers->where('participation_type', 'beneficiary')->count() : 0,
                'created_at'         => $e->created_at ? $e->created_at->format('Y-m-d H:i:s') : null,
            ];
        });

        $stats = [
            'total_events'        => VolunteerEvent::count(),
            'conducted_events'    => VolunteerEvent::where('status', 'completed')->count(),
            'upcoming_events'     => VolunteerEvent::where('status', 'upcoming')->count(),
            'total_participants'  => VolunteerEventMember::whereIn('participation_status', ['registered', 'participated', 'benefited'])->count(),
            'total_beneficiaries' => VolunteerEventMember::where(function ($q) {
                $q->where('participation_type', 'beneficiary')
                  ->orWhere('participation_status', 'benefited');
            })->count(),
        ];

        return response()->json([
            'success' => true,
            'stats'   => $stats,
            'data'    => $items,
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    /**
     * GET /api/v1/admin/volunteer-events/{id}
     * Detailed Event Dossier & Participant Roster
     */
    public function show($id)
    {
        $e = VolunteerEvent::with(['volunteer.membership', 'eventMembers.membership'])->findOrFail($id);

        $organizerName = $e->volunteer->membership->identity_verified_name ?? ($e->volunteer->membership->full_name ?? 'Volunteer');

        $members = collect($e->eventMembers)->map(function ($em) {
            $mName = $em->membership->identity_verified_name ?? ($em->membership->full_name ?? 'Member');
            return [
                'id'                   => $em->id,
                'member_id'            => $em->membership_id,
                'name'                 => $mName,
                'phone'                => $em->membership->phone ?? null,
                'participation_type'   => $em->participation_type ?? 'participant',
                'participation_status' => $em->participation_status ?? 'participated',
                'proof_image_url'      => $em->proof_image_path ? Storage::disk('public')->url($em->proof_image_path) : null,
                'remarks'              => $em->remarks,
            ];
        });

        $data = [
            'id'                 => $e->id,
            'title'              => $e->title,
            'description'        => $e->description,
            'event_type'         => $e->event_type ?? 'Community Service',
            'event_date'         => $e->event_date ? $e->event_date->format('Y-m-d') : null,
            'formatted_date'     => $e->event_date ? $e->event_date->format('d M Y') : null,
            'venue'              => $e->venue,
            'district'           => $e->district,
            'mandal'             => $e->mandal,
            'grama_panchayat'    => $e->grama_panchayat,
            'status'             => $e->status ?? 'completed',
            'organizer_name'     => $organizerName,
            'organizer_id'       => $e->volunteer->volunteer_id ?? null,
            'participants_count' => $e->eventMembers ? $e->eventMembers->count() : 0,
            'members'            => $members,
            'created_at'         => $e->created_at ? $e->created_at->format('Y-m-d H:i:s') : null,
        ];

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * DELETE /api/v1/admin/volunteer-events/{id}
     * Delete Event Record
     */
    public function destroy($id)
    {
        $e = VolunteerEvent::findOrFail($id);
        $e->delete();

        return response()->json([
            'success' => true,
            'message' => 'Volunteer event record deleted successfully.',
        ]);
    }
}
