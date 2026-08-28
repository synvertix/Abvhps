<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\OurSupport;
use App\Support\ApiMediaHelper;
use Illuminate\Http\JsonResponse;

class ProjectController extends Controller
{
    /**
     * List all public active core projects (Our Supports).
     */
    public function index(): JsonResponse
    {
        $projects = OurSupport::where('status', 'show')
            ->orderBy('sort_order', 'asc')
            ->get()
            ->map(function ($p) {
                return [
                    'id'         => $p->id,
                    'name'       => $p->name,
                    'short_info' => strip_tags($p->short_info ?? ''),
                    'image_url'  => ApiMediaHelper::resolveUrl($p->image_path),
                    'sort_order' => (int) $p->sort_order,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $projects,
            'message' => null,
        ]);
    }

    /**
     * Show single public core project detail.
     */
    public function show(int $id): JsonResponse
    {
        $project = OurSupport::where('id', $id)
            ->where('status', 'show')
            ->first();

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found or is currently inactive.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'         => $project->id,
                'name'       => $project->name,
                'short_info' => strip_tags($project->short_info ?? ''),
                'image_url'  => ApiMediaHelper::resolveUrl($project->image_path),
                'sort_order' => (int) $project->sort_order,
            ],
            'message' => null,
        ]);
    }
}
