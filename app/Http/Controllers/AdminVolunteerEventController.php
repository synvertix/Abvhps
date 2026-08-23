<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\VolunteerEvent;
use App\Models\VolunteerEventMember;
use App\Models\Volunteer;
use App\Services\TinyProofImageService;
use App\Services\AuditLogger;
use InvalidArgumentException;
use RuntimeException;

class AdminVolunteerEventController extends Controller
{
    protected TinyProofImageService $imageService;

    public function __construct(TinyProofImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Display centralized roster of all volunteer events.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $volunteerFilter = $request->query('volunteer_id');

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

        if (!empty($volunteerFilter)) {
            $query->where('volunteer_id', $volunteerFilter);
        }

        $events = $query->orderBy('event_date', 'desc')->paginate(15)->withQueryString();

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

        return view('admin.volunteer_events_index', compact('events', 'stats', 'search', 'status', 'volunteerFilter'));
    }

    /**
     * Display detailed admin dossier for a specific volunteer event.
     */
    public function show($id)
    {
        $event = VolunteerEvent::with(['volunteer.membership', 'eventMembers.membership', 'eventMembers.addedByVolunteer'])
            ->findOrFail($id);

        return view('admin.volunteer_event_detail', compact('event'));
    }

    /**
     * Admin replacement of a participant's proof image (strictly enforces <= 2048 bytes).
     */
    public function replaceProofImage(Request $request, $id, $memberLinkId)
    {
        $event = VolunteerEvent::findOrFail($id);
        $eventMember = VolunteerEventMember::where('volunteer_event_id', $event->id)->findOrFail($memberLinkId);

        $request->validate([
            'proof_image' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        $oldPath = $eventMember->proof_image_path;
        $proofData = null;

        try {
            $proofData = $this->imageService->compressUploadedImage($request->file('proof_image'));
        } catch (InvalidArgumentException | RuntimeException $e) {
            return redirect()->route('admin.volunteer_events.show', $event->id)
                ->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error("Admin proof image replacement compression failed: " . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('admin.volunteer_events.show', $event->id)
                ->with('error', 'Proof image upload failed. Please try again.');
        }

        DB::beginTransaction();
        try {
            $eventMember->update([
                'proof_image_path'       => $proofData['path'],
                'proof_image_size_bytes' => $proofData['bytes'],
                'proof_image_mime'       => $proofData['mime'],
                'proof_image_width'      => $proofData['width'],
                'proof_image_height'     => $proofData['height'],
            ]);

            DB::commit();

            // Clean up previous image ONLY after successful database persistence
            if (!empty($oldPath) && $oldPath !== $proofData['path']) {
                $this->imageService->deleteImageIfExists($oldPath);
            }
        } catch (\Throwable $dbEx) {
            DB::rollBack();
            // Delete newly created file on DB failure so no orphan exists
            if (!empty($proofData['path'])) {
                $this->imageService->deleteImageIfExists($proofData['path']);
            }
            Log::error("Admin proof image DB update failed: " . $dbEx->getMessage(), ['exception' => $dbEx]);
            return redirect()->route('admin.volunteer_events.show', $event->id)
                ->with('error', 'Failed to update database record for proof image.');
        }

        AuditLogger::log(
            'ADMIN_EVENT_PROOF_IMAGE_UPDATED',
            'VolunteerEventMember',
            (string)$eventMember->id,
            [
                'event_id'      => $event->id,
                'membership_id' => $eventMember->membership_id,
                'bytes'         => $proofData['bytes'],
            ]
        );

        return redirect()->route('admin.volunteer_events.show', $event->id)
            ->with('success', 'Participant proof image replaced with optimized ' . round($proofData['bytes'] / 1024, 1) . ' KB image.');
    }

    /**
     * Admin delete of an event.
     */
    public function destroy($id)
    {
        $event = VolunteerEvent::with('eventMembers')->findOrFail($id);

        $proofPaths = [];
        foreach ($event->eventMembers as $em) {
            if ($em->proof_image_path) {
                $proofPaths[] = $em->proof_image_path;
            }
        }

        $eventTitle = $event->title;

        DB::beginTransaction();
        try {
            $event->delete();
            DB::commit();

            foreach ($proofPaths as $path) {
                $this->imageService->deleteImageIfExists($path);
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Admin event deletion failed: " . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('admin.volunteer_events.index')
                ->with('error', 'Failed to delete event. Please try again.');
        }

        AuditLogger::log(
            'ADMIN_EVENT_DELETED',
            'VolunteerEvent',
            (string)$id,
            ['title' => $eventTitle]
        );

        return redirect()->route('admin.volunteer_events.index')
            ->with('success', "Volunteer event '{$eventTitle}' removed successfully.");
    }
}
