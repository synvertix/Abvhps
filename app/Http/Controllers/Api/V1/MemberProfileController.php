<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Http\Resources\Api\V1\MemberProfileResource;
use App\Http\Resources\Api\V1\MemberCardResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MemberProfileController extends Controller
{
    /**
     * Get authenticated Member profile.
     */
    public function profile(Request $request): JsonResponse
    {
        /** @var Membership $member */
        $member = $request->user();

        return response()->json([
            'success' => true,
            'data'    => new MemberProfileResource($member),
            'message' => null,
        ]);
    }

    /**
     * Get authenticated Member digital ID card payload (JSON).
     */
    public function card(Request $request): JsonResponse
    {
        /** @var Membership $member */
        $member = $request->user();

        return response()->json([
            'success' => true,
            'data'    => new MemberCardResource($member),
            'message' => null,
        ]);
    }
}
