<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ExamApplication;
use App\Models\ExamSetting;
use Illuminate\Http\Request;

class AdminExamController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 15);

        $query = ExamSetting::query();

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($search)) {
            $query->where('exam_title', 'LIKE', '%' . $search . '%');
        }

        $paginator = $query->orderBy('id', 'desc')->paginate($perPage);

        $items = collect($paginator->items())->map(function ($e) {
            $totalApps = ExamApplication::where('exam_setting_id', $e->id)->count();
            $paidApps = ExamApplication::where('exam_setting_id', $e->id)->where('payment_status', 'success')->count();

            return [
                'id'                 => $e->id,
                'exam_title'         => $e->exam_title,
                'exam_type'          => $e->exam_type,
                'exam_date_time'     => $e->exam_date_time,
                'application_fee'    => (float) $e->application_fee,
                'status'             => $e->status ?? 'active',
                'total_applicants'   => $totalApps,
                'paid_applicants'    => $paidApps,
                'created_at'         => $e->created_at,
            ];
        });

        $stats = [
            'total_cycles'      => ExamSetting::count(),
            'total_applicants'  => ExamApplication::count(),
            'paid_applicants'   => ExamApplication::where('payment_status', 'success')->count(),
            'active_cycles'     => ExamSetting::where('status', 'active')->count(),
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

    public function store(Request $request)
    {
        $request->validate([
            'exam_title'      => 'required|string|max:255',
            'exam_type'       => 'nullable|string|max:255',
            'exam_date_time'  => 'required|date',
            'application_fee' => 'required|numeric|min:0',
            'status'          => 'required|in:active,upcoming,completed,draft',
        ]);

        $e = ExamSetting::create([
            'exam_title'           => $request->exam_title,
            'exam_type'            => $request->exam_type,
            'exam_date_time'       => $request->exam_date_time,
            'application_fee'      => $request->application_fee,
            'status'               => $request->status,
            'syllabus_pdf_path'    => $request->syllabus_pdf_path ?? 'syllabuses/default.pdf',
            'exam_center_location' => $request->exam_center_location ?? 'HYDERABAD MAIN CENTER',
            'prize_details_json'   => json_encode([]),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Exam cycle created successfully.',
            'data'    => $e,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $e = ExamSetting::find($id);
        if (!$e) {
            return response()->json(['success' => false, 'message' => 'Exam cycle not found'], 404);
        }

        $request->validate([
            'exam_title'      => 'required|string|max:255',
            'exam_type'       => 'nullable|string|max:255',
            'exam_date_time'  => 'required|date',
            'application_fee' => 'required|numeric|min:0',
            'status'          => 'required|in:active,upcoming,completed,draft',
        ]);

        $e->update($request->only([
            'exam_title', 'exam_type', 'exam_date_time', 'application_fee', 'status'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Exam cycle updated successfully.',
            'data'    => $e,
        ]);
    }

    public function destroy($id)
    {
        $e = ExamSetting::find($id);
        if (!$e) {
            return response()->json(['success' => false, 'message' => 'Exam cycle not found'], 404);
        }

        $e->delete();

        return response()->json([
            'success' => true,
            'message' => 'Exam cycle deleted successfully.',
        ]);
    }

    public function applicants(Request $request, $id)
    {
        $e = ExamSetting::find($id);
        if (!$e) {
            return response()->json(['success' => false, 'message' => 'Exam cycle not found'], 404);
        }

        $search = $request->input('search');
        $status = $request->input('result_status');
        $pub = $request->input('publication_status');
        $perPage = (int) $request->input('per_page', 15);

        $query = ExamApplication::where('exam_setting_id', $id);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'LIKE', '%' . $search . '%')
                  ->orWhere('hall_ticket_number', 'LIKE', '%' . $search . '%')
                  ->orWhere('email', 'LIKE', '%' . $search . '%');
            });
        }

        if (!empty($status)) {
            $query->where('result_status', $status);
        }

        if (!empty($pub)) {
            $query->where('result_publication_status', $pub);
        }

        $paginator = $query->orderBy('id', 'desc')->paginate($perPage);

        $items = collect($paginator->items())->map(function ($app) {
            return [
                'id'                        => $app->id,
                'full_name'                 => $app->full_name,
                'hall_ticket_number'        => $app->hall_ticket_number,
                'email'                     => $app->email,
                'guardian_mobile_or_id'     => $app->guardian_mobile_or_id,
                'school_college_name'       => $app->school_college_name,
                'payment_status'            => $app->payment_status,
                'marks_obtained'            => $app->marks_obtained,
                'total_marks'               => $app->total_marks,
                'grade'                     => $app->grade,
                'result_status'             => $app->result_status ?? 'pending',
                'result_publication_status' => $app->result_publication_status ?? 'draft',
                'winner_rank'               => $app->winner_rank,
                'prize_title_won'           => $app->prize_title_won,
            ];
        });

        return response()->json([
            'success' => true,
            'exam'    => [
                'id'         => $e->id,
                'exam_title' => $e->exam_title,
            ],
            'data'    => $items,
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    public function saveResult(Request $request, $appId)
    {
        $app = ExamApplication::find($appId);
        if (!$app) {
            return response()->json(['success' => false, 'message' => 'Applicant not found'], 404);
        }

        $validated = $request->validate([
            'marks_obtained'       => 'nullable|integer|min:0',
            'total_marks'          => 'nullable|integer|min:1',
            'grade'                => 'nullable|string|max:10',
            'result_status'        => 'required|in:pending,passed,failed',
            'winner_rank'          => 'nullable|integer|min:1|max:6',
            'prize_title_won'      => 'nullable|string|max:255',
            'show_on_winners_wall' => 'nullable|boolean',
        ]);

        if (
            isset($validated['marks_obtained']) &&
            isset($validated['total_marks']) &&
            $validated['marks_obtained'] > $validated['total_marks']
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Marks obtained cannot exceed total marks.',
            ], 422);
        }

        $app->marks_obtained      = $validated['marks_obtained'] ?? $app->marks_obtained;
        $app->total_marks         = $validated['total_marks'] ?? $app->total_marks;
        $app->grade               = $validated['grade'] ?? $app->grade;
        $app->result_status       = $validated['result_status'];
        $app->winner_rank         = $validated['winner_rank'] ?? null;
        $app->prize_title_won     = $validated['prize_title_won'] ?? null;
        $app->show_on_winners_wall = $validated['show_on_winners_wall'] ?? false;
        $app->result_publication_status = $app->result_publication_status ?? 'draft';
        $app->save();

        return response()->json([
            'success' => true,
            'message' => 'Applicant result saved as draft.',
            'data'    => $app,
        ]);
    }

    public function publishResults($id)
    {
        $count = ExamApplication::where('exam_setting_id', $id)
            ->whereIn('result_status', ['passed', 'failed'])
            ->update(['result_publication_status' => 'published']);

        return response()->json([
            'success' => true,
            'message' => "Published {$count} applicant results successfully.",
        ]);
    }

    public function unpublishResults($id)
    {
        $count = ExamApplication::where('exam_setting_id', $id)
            ->update(['result_publication_status' => 'draft']);

        return response()->json([
            'success' => true,
            'message' => "Unpublished {$count} applicant results to draft successfully.",
        ]);
    }
}
