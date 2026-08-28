<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Support\ApiMediaHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    /**
     * List all public media gallery items (images & videos) with pagination.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $mediaType = $request->query('type');
        if ($mediaType === 'photo') {
            $mediaType = 'image';
        }

        $perPage = (int) $request->query('per_page', 24);
        if ($perPage < 1 || $perPage > 50) {
            $perPage = 24;
        }

        $query = Gallery::orderBy('id', 'desc');

        if (!empty($mediaType) && in_array($mediaType, ['image', 'video'], true)) {
            $query->where('media_type', $mediaType);
        }

        $paginated = $query->paginate($perPage);

        $items = collect($paginated->items())->map(function ($g) {
            return [
                'id'         => $g->id,
                'media_type' => $g->media_type ?? 'image',
                'image_url'  => ApiMediaHelper::resolveUrl($g->image_path),
                'video_url'  => $g->video_url,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $items,
            'meta'    => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ],
            'message' => null,
        ]);
    }
}
