<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use App\Models\Volunteer;
use App\Models\VolunteerEvent;
use App\Models\VolunteerEventMember;
use App\Models\Membership;
use App\Services\TinyProofImageService;
use App\Services\AuditLogger;
use InvalidArgumentException;
use RuntimeException;

class VolunteerEventController extends Controller
{
    protected TinyProofImageService $imageService;

    public function __construct(TinyProofImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Get the authenticated and approved volunteer.
     */
    protected function getAuthenticatedVolunteer(): Volunteer
    {
        $volunteer = Auth::guard('volunteer')->user();
        if (!$volunteer || $volunteer->status !== 'approved' || (isset($volunteer->is_active) && !$volunteer->is_active)) {
            abort(403, 'Unauthorized. Approved volunteer credentials required.');
        }
        return $volunteer;
    }

    /**
     * Display volunteer's events dashboard with Conducted / Upcoming / All tabs.
     */
    public function index(Request $request)
    {
        $volunteer = $this->getAuthenticatedVolunteer();
        $tab = $request->query('tab', 'conducted');

        $query = VolunteerEvent::where('volunteer_id', $volunteer->id)->with('eventMembers');

        if ($tab === 'upcoming') {
            $query->where('status', 'upcoming')->orderBy('event_date', 'asc');
        } elseif ($tab === 'completed' || $tab === 'conducted') {
            $query->where('status', 'completed')->orderBy('event_date', 'desc');
        } elseif ($tab === 'cancelled') {
            $query->where('status', 'cancelled')->orderBy('event_date', 'desc');
        } else {
            $query->orderBy('event_date', 'desc');
        }

        $events = $query->paginate(12)->withQueryString();

        $stats = [
            'total'         => $volunteer->events()->count(),
            'conducted'     => $volunteer->conductedEventsCount(),
            'upcoming'      => $volunteer->upcomingEventsCount(),
            'participants'  => $volunteer->totalParticipantsCount(),
            'beneficiaries' => $volunteer->totalBeneficiariesCount(),
        ];

        return view('volunteer.events.index', compact('volunteer', 'events', 'tab', 'stats'));
    }

    /**
     * Show form to create a new event.
     */
    public function create()
    {
        $volunteer = $this->getAuthenticatedVolunteer();
        $serviceTypes = VolunteerEvent::SERVICE_TYPES;

        return view('volunteer.events.create', compact('volunteer', 'serviceTypes'));
    }

    /**
     * Store a newly created event.
     */
    public function store(Request $request)
    {
        $volunteer = $this->getAuthenticatedVolunteer();

        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'event_type'   => ['required', 'string', Rule::in(array_keys(VolunteerEvent::SERVICE_TYPES))],
            'description'  => 'nullable|string|max:5000',
            'event_date'   => 'required|date',
            'start_time'   => 'nullable|string|max:20',
            'end_time'     => 'nullable|string|max:20',
            'venue'        => 'nullable|string|max:255',
            'village'      => 'nullable|string|max:100',
            'mandal'       => 'nullable|string|max:100',
            'district'     => 'nullable|string|max:100',
            'state'        => 'nullable|string|max:100',
            'status'       => 'required|string|in:upcoming,completed,cancelled',
            'outcome'      => 'nullable|string|max:5000',
        ]);

        $event = VolunteerEvent::create([
            'volunteer_id' => $volunteer->id,
            'title'        => $validated['title'],
            'event_type'   => $validated['event_type'],
            'description'  => $validated['description'] ?? null,
            'event_date'   => $validated['event_date'],
            'start_time'   => $validated['start_time'] ?? null,
            'end_time'     => $validated['end_time'] ?? null,
            'venue'        => $validated['venue'] ?? null,
            'village'      => $validated['village'] ?? ($volunteer->resolved_grama_panchayat ?? null),
            'mandal'       => $validated['mandal'] ?? ($volunteer->resolved_mandal ?? null),
            'district'     => $validated['district'] ?? ($volunteer->resolved_district ?? null),
            'state'        => $validated['state'] ?? ($volunteer->resolved_state ?? 'Andhra Pradesh'),
            'status'       => $validated['status'],
            'outcome'      => $validated['outcome'] ?? null,
        ]);

        AuditLogger::log(
            'VOLUNTEER_EVENT_CREATED',
            'VolunteerEvent',
            (string)$event->id,
            [
                'title'      => $event->title,
                'status'     => $event->status,
                'event_date' => $event->event_date->format('Y-m-d'),
            ],
            'Volunteer',
            $volunteer->volunteer_id,
            $volunteer->id
        );

        return redirect()->route('volunteer.events.show', $event->id)
            ->with('success', 'Event "' . $event->title . '" created successfully!');
    }

    /**
     * Show event details, participant roster, and member addition tools.
     */
    public function show($id)
    {
        $volunteer = $this->getAuthenticatedVolunteer();

        $event = VolunteerEvent::with(['eventMembers.membership'])->findOrFail($id);

        if (!$event->isEditableBy($volunteer)) {
            abort(403, 'Unauthorized. You can only view and manage your own events.');
        }

        $participationTypes = VolunteerEventMember::PARTICIPATION_TYPES;
        $participationStatuses = VolunteerEventMember::PARTICIPATION_STATUSES;

        return view('volunteer.events.show', compact('volunteer', 'event', 'participationTypes', 'participationStatuses'));
    }

    /**
     * Show form to edit an event.
     */
    public function edit($id)
    {
        $volunteer = $this->getAuthenticatedVolunteer();

        $event = VolunteerEvent::findOrFail($id);

        if (!$event->isEditableBy($volunteer)) {
            abort(403, 'Unauthorized. You can only edit your own events.');
        }

        $serviceTypes = VolunteerEvent::SERVICE_TYPES;
        $statuses = VolunteerEvent::STATUSES;

        return view('volunteer.events.edit', compact('volunteer', 'event', 'serviceTypes', 'statuses'));
    }

    /**
     * Update an event.
     */
    public function update(Request $request, $id)
    {
        $volunteer = $this->getAuthenticatedVolunteer();

        $event = VolunteerEvent::findOrFail($id);

        if (!$event->isEditableBy($volunteer)) {
            abort(403, 'Unauthorized. You cannot edit another volunteer\'s event.');
        }

        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'event_type'   => ['required', 'string', Rule::in(array_keys(VolunteerEvent::SERVICE_TYPES))],
            'description'  => 'nullable|string|max:5000',
            'event_date'   => 'required|date',
            'start_time'   => 'nullable|string|max:20',
            'end_time'     => 'nullable|string|max:20',
            'venue'        => 'nullable|string|max:255',
            'village'      => 'nullable|string|max:100',
            'mandal'       => 'nullable|string|max:100',
            'district'     => 'nullable|string|max:100',
            'state'        => 'nullable|string|max:100',
            'status'       => 'required|string|in:upcoming,completed,cancelled',
            'outcome'      => 'nullable|string|max:5000',
        ]);

        $event->update($validated);

        AuditLogger::log(
            'VOLUNTEER_EVENT_UPDATED',
            'VolunteerEvent',
            (string)$event->id,
            [
                'title'      => $event->title,
                'status'     => $event->status,
                'event_date' => $event->event_date->format('Y-m-d'),
            ],
            'Volunteer',
            $volunteer->volunteer_id,
            $volunteer->id
        );

        return redirect()->route('volunteer.events.show', $event->id)
            ->with('success', 'Event updated successfully.');
    }

    /**
     * Privacy-safe exact 12-digit Membership ID search endpoint for volunteers.
     * Throttled to 20 searches/min per volunteer.
     */
    public function searchMember(Request $request)
    {
        $volunteer = $this->getAuthenticatedVolunteer();

        $throttleKey = 'vol_exact_member_search:' . $volunteer->id . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 20)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json([
                'success' => false,
                'error'   => "Too many search requests. Please wait {$seconds} seconds before searching again.",
            ], 429);
        }
        RateLimiter::hit($throttleKey, 60);

        $request->validate([
            'membership_id' => ['required', 'string', 'digits:12', 'regex:/^\d{12}$/'],
        ]);

        $cleanId = trim($request->input('membership_id'));

        // Strict exact match lookup: Must have payment_status=success AND is_completed=true
        $member = Membership::where('membership_id', $cleanId)
            ->where('payment_status', 'success')
            ->where(function ($q) {
                $q->where('is_completed', true)->orWhere('is_completed', 1);
            })
            ->first();

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found.',
            ], 404);
        }

        // Return ONLY privacy-safe non-sensitive fields
        $photoUrl = null;
        if (!empty($member->photo_path)) {
            $photoUrl = asset('storage/' . $member->photo_path);
        }

        return response()->json([
            'success' => true,
            'member'  => [
                'membership_id' => $member->membership_id,
                'full_name'     => $member->full_name,
                'status'        => 'Active',
                'district'      => $member->district ?? '-',
                'state'         => $member->state ?? '-',
                'photo_url'     => $photoUrl,
            ],
        ]);
    }

    /**
     * Dedicated view for standalone Membership Search desk.
     */
    public function memberSearchDesk(Request $request)
    {
        $volunteer = $this->getAuthenticatedVolunteer();
        $myEvents = $volunteer->events()->orderBy('event_date', 'desc')->get(['id', 'title', 'event_date', 'status']);

        return view('volunteer.events.member_search', compact('volunteer', 'myEvents'));
    }

    /**
     * Add a member as a participant / beneficiary to an event.
     */
    public function addMember(Request $request, $eventId)
    {
        $volunteer = $this->getAuthenticatedVolunteer();

        $event = VolunteerEvent::findOrFail($eventId);

        if (!$event->isEditableBy($volunteer)) {
            abort(403, 'Unauthorized. You cannot add participants to another volunteer\'s event.');
        }

        $request->validate([
            'membership_id'        => ['required', 'string', 'digits:12', 'regex:/^\d{12}$/'],
            'participation_type'   => 'required|string|in:participant,beneficiary,volunteer_support,other',
            'participation_status' => 'required|string|in:registered,participated,benefited,absent',
            'benefit_details'      => 'nullable|string|max:2000',
            'notes'                => 'nullable|string|max:2000',
            'proof_image'          => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        $cleanId = trim($request->input('membership_id'));

        // Load Membership record server-side with strict completed & paid condition
        $member = Membership::where('membership_id', $cleanId)
            ->where('payment_status', 'success')
            ->where(function ($q) {
                $q->where('is_completed', true)->orWhere('is_completed', 1);
            })
            ->first();

        if (!$member) {
            return back()->withInput()->with('error', 'Active registered member not found for Membership ID: ' . $cleanId);
        }

        // Prevent duplicate membership in same event via relational FK
        $exists = VolunteerEventMember::where('volunteer_event_id', $event->id)
            ->where('membership_record_id', $member->id)
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Member (' . $member->membership_id . ') is already linked to this event.');
        }

        // Strict <= 2048 bytes image processing
        $proofData = null;
        if ($request->hasFile('proof_image')) {
            try {
                $proofData = $this->imageService->compressUploadedImage($request->file('proof_image'));
            } catch (InvalidArgumentException | RuntimeException $e) {
                return back()->withInput()->with('error', $e->getMessage());
            } catch (\Throwable $e) {
                Log::error("Proof image upload failed: " . $e->getMessage(), ['exception' => $e]);
                return back()->withInput()->with('error', 'Proof image upload failed. Please try again.');
            }
        }

        // Atomic DB insertion with compensating storage cleanup
        DB::beginTransaction();
        try {
            $eventMember = VolunteerEventMember::create([
                'volunteer_event_id'     => $event->id,
                'membership_record_id'   => $member->id,
                'membership_id'          => $member->membership_id,
                'participation_type'     => $request->input('participation_type'),
                'participation_status'   => $request->input('participation_status'),
                'benefit_details'        => $request->input('benefit_details'),
                'notes'                  => $request->input('notes'),
                'proof_image_path'       => $proofData['path'] ?? null,
                'proof_image_size_bytes' => $proofData['bytes'] ?? null,
                'proof_image_mime'       => $proofData['mime'] ?? null,
                'proof_image_width'      => $proofData['width'] ?? null,
                'proof_image_height'     => $proofData['height'] ?? null,
                'added_by_volunteer_id'  => $volunteer->id,
            ]);
            DB::commit();
        } catch (\Throwable $dbEx) {
            DB::rollBack();
            if (!empty($proofData['path'])) {
                $this->imageService->deleteImageIfExists($proofData['path']);
            }
            Log::error("Failed to persist event member: " . $dbEx->getMessage(), ['exception' => $dbEx]);
            return back()->withInput()->with('error', 'Failed to link member to event. Please try again.');
        }

        AuditLogger::log(
            'EVENT_MEMBER_ADDED',
            'VolunteerEventMember',
            (string)$eventMember->id,
            [
                'event_id'         => $event->id,
                'membership_id'    => $member->membership_id,
                'type'             => $eventMember->participation_type,
                'status'           => $eventMember->participation_status,
                'proof_size_bytes' => $eventMember->proof_image_size_bytes,
            ],
            'Volunteer',
            $volunteer->volunteer_id,
            $volunteer->id
        );

        return redirect()->route('volunteer.events.show', $event->id)
            ->with('success', 'Member (' . $member->full_name . ') added to event successfully.');
    }

    /**
     * Update participant/beneficiary details and proof image.
     */
    public function updateMember(Request $request, $eventId, $memberLinkId)
    {
        $volunteer = $this->getAuthenticatedVolunteer();

        $event = VolunteerEvent::findOrFail($eventId);
        if (!$event->isEditableBy($volunteer)) {
            abort(403, 'Unauthorized. You cannot modify participants on another volunteer\'s event.');
        }

        $eventMember = VolunteerEventMember::where('volunteer_event_id', $event->id)
            ->findOrFail($memberLinkId);

        $request->validate([
            'participation_type'   => 'required|string|in:participant,beneficiary,volunteer_support,other',
            'participation_status' => 'required|string|in:registered,participated,benefited,absent',
            'benefit_details'      => 'nullable|string|max:2000',
            'notes'                => 'nullable|string|max:2000',
            'proof_image'          => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        $updateData = [
            'participation_type'   => $request->input('participation_type'),
            'participation_status' => $request->input('participation_status'),
            'benefit_details'      => $request->input('benefit_details'),
            'notes'                => $request->input('notes'),
        ];

        $oldPath = $eventMember->proof_image_path;
        $newProofData = null;

        // Process proof image replacement if provided
        if ($request->hasFile('proof_image')) {
            try {
                $newProofData = $this->imageService->compressUploadedImage($request->file('proof_image'));
                $updateData['proof_image_path']       = $newProofData['path'];
                $updateData['proof_image_size_bytes'] = $newProofData['bytes'];
                $updateData['proof_image_mime']       = $newProofData['mime'];
                $updateData['proof_image_width']      = $newProofData['width'];
                $updateData['proof_image_height']     = $newProofData['height'];
            } catch (InvalidArgumentException | RuntimeException $e) {
                return back()->with('error', $e->getMessage());
            } catch (\Throwable $e) {
                Log::error("Proof image update failed: " . $e->getMessage(), ['exception' => $e]);
                return back()->with('error', 'Proof image upload failed. Please try again.');
            }
        }

        // Atomic DB update with post-commit old image cleanup and roll-back new image deletion
        DB::beginTransaction();
        try {
            $eventMember->update($updateData);
            DB::commit();

            // Delete old image ONLY after successful database update
            if ($newProofData && !empty($oldPath) && $oldPath !== $newProofData['path']) {
                $this->imageService->deleteImageIfExists($oldPath);
            }
        } catch (\Throwable $dbEx) {
            DB::rollBack();
            // If DB update failed, delete the newly stored image and keep old image intact!
            if ($newProofData && !empty($newProofData['path'])) {
                $this->imageService->deleteImageIfExists($newProofData['path']);
            }
            Log::error("Failed to update participant: " . $dbEx->getMessage(), ['exception' => $dbEx]);
            return back()->with('error', 'Failed to update participant. Please try again.');
        }

        AuditLogger::log(
            'EVENT_MEMBER_UPDATED',
            'VolunteerEventMember',
            (string)$eventMember->id,
            [
                'event_id'      => $event->id,
                'membership_id' => $eventMember->membership_id,
                'type'          => $eventMember->participation_type,
                'status'        => $eventMember->participation_status,
            ],
            'Volunteer',
            $volunteer->volunteer_id,
            $volunteer->id
        );

        return redirect()->route('volunteer.events.show', $event->id)
            ->with('success', 'Participant details updated successfully.');
    }

    /**
     * Remove a member from the event.
     */
    public function removeMember($eventId, $memberLinkId)
    {
        $volunteer = $this->getAuthenticatedVolunteer();

        $event = VolunteerEvent::findOrFail($eventId);
        if (!$event->isEditableBy($volunteer)) {
            abort(403, 'Unauthorized. You cannot remove participants from another volunteer\'s event.');
        }

        $eventMember = VolunteerEventMember::where('volunteer_event_id', $event->id)
            ->findOrFail($memberLinkId);

        $proofPath = $eventMember->proof_image_path;
        $memberId = $eventMember->membership_id;

        DB::beginTransaction();
        try {
            $eventMember->delete();
            DB::commit();
            if ($proofPath) {
                $this->imageService->deleteImageIfExists($proofPath);
            }
        } catch (\Throwable $dbEx) {
            DB::rollBack();
            Log::error("Failed to remove member link: " . $dbEx->getMessage(), ['exception' => $dbEx]);
            return back()->with('error', 'Failed to remove participant from event.');
        }

        AuditLogger::log(
            'EVENT_MEMBER_REMOVED',
            'VolunteerEventMember',
            (string)$memberLinkId,
            [
                'event_id'      => $event->id,
                'membership_id' => $memberId,
            ],
            'Volunteer',
            $volunteer->volunteer_id,
            $volunteer->id
        );

        return redirect()->route('volunteer.events.show', $event->id)
            ->with('success', 'Participant removed from event.');
    }
}
