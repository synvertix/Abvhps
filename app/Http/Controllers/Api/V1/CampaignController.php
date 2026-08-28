<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FundraisingCampaign;
use App\Support\ApiMediaHelper;
use Illuminate\Http\JsonResponse;

class CampaignController extends Controller
{
    /**
     * List all public active fundraising campaigns.
     */
    public function index(): JsonResponse
    {
        $campaigns = FundraisingCampaign::active()
            ->orderBy('id', 'desc')
            ->get()
            ->map(fn($c) => $this->transformCampaign($c));

        return response()->json([
            'success' => true,
            'data'    => $campaigns,
            'message' => null,
        ]);
    }

    /**
     * Show single public active fundraising campaign detail.
     */
    public function show(int $id): JsonResponse
    {
        $campaign = FundraisingCampaign::active()
            ->where('id', $id)
            ->first();

        if (!$campaign) {
            return response()->json([
                'success' => false,
                'message' => 'Campaign not found or has concluded.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $this->transformCampaign($campaign, true),
            'message' => null,
        ]);
    }

    /**
     * Transform a campaign model to safe allowlisted API payload.
     */
    private function transformCampaign(FundraisingCampaign $c, bool $includeDetails = false): array
    {
        $target = (float) ($c->target_amount ?? 1);
        $raised = (float) ($c->raised_amount ?? 0);
        $percent = $target > 0 ? min(round(($raised / $target) * 100, 2), 100) : 0;

        $gallery = [];
        if ($includeDetails) {
            foreach (['image_1', 'image_2', 'image_3', 'image_4'] as $imgKey) {
                if (!empty($c->$imgKey)) {
                    $resolved = ApiMediaHelper::resolveUrl($c->$imgKey);
                    if ($resolved) {
                        $gallery[] = $resolved;
                    }
                }
            }
        }

        return [
            'id'                 => $c->id,
            'title'              => $c->title,
            'description'        => strip_tags($c->description ?? ''),
            'image_url'          => ApiMediaHelper::resolveUrl($c->cover_image, ApiMediaHelper::resolveUrl('images/ABVHPS_LOGO.jpg')),
            'gallery_images'     => $gallery,
            'video_url'          => !empty($c->video_path) ? ApiMediaHelper::resolveUrl($c->video_path) : null,
            'target_amount'      => $target,
            'raised_amount'      => $raised,
            'target_formatted'   => FundraisingCampaign::formatIndianCurrency($target),
            'raised_formatted'   => FundraisingCampaign::formatIndianCurrency($raised),
            'percent'            => $percent,
            'end_date'           => $c->end_date ? \Carbon\Carbon::parse($c->end_date)->format('d-M-Y') : null,
            'whatsapp_share_url' => $c->whatsapp_share_url,
            'public_url'         => $c->public_url,
        ];
    }
}
