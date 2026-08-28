<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ExamApplication;
use App\Models\ExamSetting;
use App\Support\ApiMediaHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
{
    /**
     * List all exam cycles for the public continuous notice board.
     */
    public function index(): JsonResponse
    {
        $exams = ExamSetting::orderBy('exam_date_time', 'desc')
            ->get()
            ->map(fn($e) => $this->transformExam($e));

        return response()->json([
            'success' => true,
            'data'    => $exams,
            'message' => null,
        ]);
    }

    /**
     * Show single exam cycle details and syllabus link.
     */
    public function show(int $id): JsonResponse
    {
        $exam = ExamSetting::find($id);

        if (!$exam) {
            return response()->json([
                'success' => false,
                'message' => 'Exam cycle not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $this->transformExam($exam, true),
            'message' => null,
        ]);
    }

    /**
     * Public Top 6 Winners Showcase Wall.
     * Strictly requires BOTH show_on_winners_wall = true AND result_publication_status = published.
     */
    public function winners(): JsonResponse
    {
        $winners = ExamApplication::with('examSetting')
            ->where('show_on_winners_wall', true)
            ->whereNotNull('winner_rank')
            ->where('result_publication_status', 'published')
            ->where('payment_status', 'success')
            ->orderBy('winner_rank', 'asc')
            ->take(6)
            ->get()
            ->map(function ($w) {
                return [
                    'id'               => $w->id,
                    'winner_rank'      => (int) $w->winner_rank,
                    'full_name'        => $w->full_name,
                    'school_name'      => $w->school_college_name,
                    'exam_title'       => $w->examSetting?->exam_title ?? 'Sanathana Dharma Exam',
                    'prize_title_won'  => $w->prize_title_won,
                    'grade'            => $w->grade,
                    'photo_url'        => ApiMediaHelper::resolveUrl($w->photo_path),
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $winners,
            'message' => null,
        ]);
    }

    /**
     * Search candidate result via 11-digit unique hall ticket number.
     * Enforces payment_status = success and result_publication_status = published.
     */
    public function searchResult(Request $request): JsonResponse
    {
        $request->validate([
            'hall_ticket_number' => 'required|string|size:11|regex:/^[0-9]{11}$/',
        ], [
            'hall_ticket_number.required' => 'Please provide an 11-digit Hall Ticket Number.',
            'hall_ticket_number.size'     => 'Hall Ticket Number must be exactly 11 numeric digits.',
            'hall_ticket_number.regex'    => 'Hall Ticket Number must contain digits only.',
        ]);

        $hallTicket = trim((string) $request->input('hall_ticket_number'));

        $application = DB::table('exam_applications')
            ->where('hall_ticket_number', $hallTicket)
            ->where('payment_status', 'success')
            ->first();

        if (!$application) {
            return response()->json([
                'success' => false,
                'message' => 'No registration found for this Hall Ticket Number.',
            ], 404);
        }

        // Suppress draft/unpublished results
        if (($application->result_publication_status ?? 'draft') !== 'published') {
            return response()->json([
                'success'   => false,
                'is_draft'  => true,
                'message'   => 'Results for this Hall Ticket Number have not been announced yet. Please check back later.',
            ]);
        }

        $exam = DB::table('exam_settings')
            ->where('id', $application->exam_setting_id)
            ->first();

        $percentage = null;
        if ($application->total_marks > 0 && $application->marks_obtained !== null) {
            $percentage = round(($application->marks_obtained / $application->total_marks) * 100, 2);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'full_name'        => $application->full_name,
                'hall_ticket'      => $application->hall_ticket_number,
                'school_name'      => $application->school_college_name,
                'exam_title'       => $exam->exam_title ?? 'Sanathana Dharma Examination',
                'exam_type'        => $exam->exam_type ?? null,
                'exam_date'        => $exam->exam_date_time ? \Carbon\Carbon::parse($exam->exam_date_time)->format('d M Y, h:i A') : null,
                'marks_obtained'   => $application->marks_obtained,
                'total_marks'      => $application->total_marks,
                'percentage'       => $percentage,
                'grade'            => $application->grade,
                'status'           => $application->result_status,
                'prize_title_won'  => $application->prize_title_won ?? null,
            ],
            'message' => null,
        ]);
    }

    /**
     * Transform an ExamSetting model to safe public API payload.
     */
    private function transformExam(ExamSetting $e, bool $includeGuidelines = false): array
    {
        $syllabusUrl = null;
        if (!empty($e->syllabus_pdf_path)) {
            $syllabusUrl = ApiMediaHelper::resolveUrl($e->syllabus_pdf_path);
        }

        return [
            'id'                   => $e->id,
            'exam_title'           => $e->exam_title,
            'exam_type'            => $e->exam_type,
            'exam_type_label'      => $e->exam_type_label,
            'exam_date_time'       => $e->exam_date_time ? $e->exam_date_time->format('d M Y, h:i A') : null,
            'exam_date_raw'        => $e->exam_date_time ? $e->exam_date_time->toIso8601String() : null,
            'exam_center_location' => $e->exam_center_location,
            'application_fee'      => (float) ($e->application_fee ?? 0),
            'status'               => $e->status,
            'banner_image_url'     => ApiMediaHelper::resolveUrl($e->banner_image_path),
            'syllabus_url'         => $syllabusUrl,
            'prizes'               => $e->prizes_list,
            'guidelines'           => $includeGuidelines ? $e->guidelines : null,
        ];
    }
}
