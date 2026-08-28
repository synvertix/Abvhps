<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    /**
     * Public Health check endpoint.
     * Safe JSON response with no internal system details.
     */
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'status'  => 'ok',
        ]);
    }
}
