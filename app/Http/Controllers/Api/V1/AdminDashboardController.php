<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\AdminDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    /**
     * Return structured, authoritative admin dashboard analytics and operational metrics.
     */
    public function show(Request $request, AdminDashboardService $dashboardService): JsonResponse
    {
        $user = $request->user();
        $dashboardData = $dashboardService->getDashboardMetrics();
        $stats = $dashboardData['stats'];

        return response()->json([
            'success' => true,
            'data' => [
                'administrator' => [
                    'name' => (string)($user->name ?? 'Administrator'),
                    'email' => (string)($user->email ?? ''),
                ],
                'summary' => [
                    'total_profiles' => (int)($stats['total_members'] ?? 0),
                    'volunteers' => (int)($stats['total_volunteers'] ?? 0),
                    'pending_actions' => (int)(($stats['pending_memberships'] ?? 0) + ($stats['pending_volunteers'] ?? 0)),
                    'funds_raised' => (float)($stats['total_funds_raised'] ?? 0.0),
                ],
                'wings' => [
                    'central_base' => (int)($stats['total_members'] ?? 0),
                    'rudra_sena' => (int)($stats['rudrasena_count'] ?? 0),
                    'kala_brundham' => (int)($stats['kala_brundam_count'] ?? 0),
                    'grama_seva_dal' => (int)($stats['grama_seva_dal_count'] ?? 0),
                    'organic_farmers' => (int)($stats['organic_farmers_count'] ?? 0),
                    'dharma_seva' => (int)($stats['active_campaigns'] ?? 0),
                ],
                'pending' => [
                    'volunteers' => (int)($stats['pending_volunteers'] ?? 0),
                    'memberships' => (int)($stats['pending_memberships'] ?? 0),
                    'exam_applications' => (int)($stats['total_exam_applications'] ?? 0),
                    'results_published' => (int)($stats['published_results'] ?? 0),
                    'active_campaigns' => (int)($stats['active_campaigns'] ?? 0),
                ],
                'system' => $dashboardData['system'],
                'exams' => [
                    'total' => (int)($stats['total_exams'] ?? 0),
                    'active' => (int)($stats['active_exams'] ?? 0),
                    'applications' => (int)($stats['total_exam_applications'] ?? 0),
                    'results_published' => (int)($stats['published_results'] ?? 0),
                ],
                'fundraising' => [
                    'total_campaigns' => (int)($stats['total_campaigns'] ?? 0),
                    'active_campaigns' => (int)($stats['active_campaigns'] ?? 0),
                    'total_donors' => (int)($stats['total_donors'] ?? 0),
                    'amount_raised' => (float)($stats['total_funds_raised'] ?? 0.0),
                ],
                'content' => [
                    'blogs' => (int)($stats['total_blogs'] ?? 0),
                    'published_blogs' => (int)($stats['published_blogs'] ?? 0),
                    'gallery_media' => (int)($stats['gallery_media'] ?? 0),
                    'support_cores' => (int)($stats['support_cores'] ?? 0),
                ],
                'recent_activity' => $dashboardData['recent_activity'],
            ],
        ]);
    }
}
