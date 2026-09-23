<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\OurTeam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminTeamController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $cadre = $request->input('cadre_level');
        $perPage = (int) $request->input('per_page', 15);

        $query = OurTeam::query();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                  ->orWhere('designation', 'LIKE', '%' . $search . '%')
                  ->orWhere('locality', 'LIKE', '%' . $search . '%')
                  ->orWhere('membership_id', 'LIKE', '%' . $search . '%');
            });
        }

        if (!empty($cadre)) {
            $query->where('cadre_level', $cadre);
        }

        $paginator = $query->orderBy('cadre_level', 'asc')->orderBy('id', 'asc')->paginate($perPage);

        $items = collect($paginator->items())->map(function ($item) {
            return [
                'id'            => $item->id,
                'membership_id' => $item->membership_id,
                'name'          => $item->name,
                'cadre_level'   => $item->cadre_level,
                'designation'   => $item->designation,
                'locality'      => $item->locality,
                'image_url'     => $item->image_path ? Storage::disk('public')->url($item->image_path) : null,
                'created_at'    => $item->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $items,
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    public function show($id)
    {
        $item = OurTeam::find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Team leader not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'            => $item->id,
                'membership_id' => $item->membership_id,
                'name'          => $item->name,
                'cadre_level'   => $item->cadre_level,
                'designation'   => $item->designation,
                'locality'      => $item->locality,
                'image_url'     => $item->image_path ? Storage::disk('public')->url($item->image_path) : null,
                'created_at'    => $item->created_at,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'membership_id' => 'nullable|string|size:12',
            'name'          => 'required|string|max:255',
            'cadre_level'   => 'required|string|in:grama_panchayat,mandal_level,assembly_segment,district_level,state_level,national_level,international_level',
            'designation'   => 'required|string|max:255',
            'locality'      => 'required|string|max:255',
            'image'         => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        $uploadedPath = null;
        if ($request->hasFile('image')) {
            $uploadedPath = $request->file('image')->store('teams', 'public');
        }

        $item = OurTeam::create([
            'membership_id' => $request->membership_id,
            'name'          => strtoupper($request->name),
            'cadre_level'   => $request->cadre_level,
            'designation'   => $request->designation,
            'locality'      => strtoupper($request->locality),
            'image_path'    => $uploadedPath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Team leader added successfully.',
            'data'    => $item,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $item = OurTeam::find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Team leader not found'], 404);
        }

        $request->validate([
            'membership_id' => 'nullable|string|size:12',
            'name'          => 'required|string|max:255',
            'cadre_level'   => 'required|string|in:grama_panchayat,mandal_level,assembly_segment,district_level,state_level,national_level,international_level',
            'designation'   => 'required|string|max:255',
            'locality'      => 'required|string|max:255',
            'image'         => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($item->image_path && Storage::disk('public')->exists($item->image_path)) {
                Storage::disk('public')->delete($item->image_path);
            }
            $item->image_path = $request->file('image')->store('teams', 'public');
        }

        $item->membership_id = $request->membership_id;
        $item->name          = strtoupper($request->name);
        $item->cadre_level   = $request->cadre_level;
        $item->designation   = $request->designation;
        $item->locality      = strtoupper($request->locality);
        $item->save();

        return response()->json([
            'success' => true,
            'message' => 'Team leader updated successfully.',
            'data'    => $item,
        ]);
    }

    public function destroy($id)
    {
        $item = OurTeam::find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Team leader not found'], 404);
        }

        if ($item->image_path && Storage::disk('public')->exists($item->image_path)) {
            Storage::disk('public')->delete($item->image_path);
        }

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Team leader deleted successfully.',
        ]);
    }
}
