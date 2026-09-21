<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TaxCertificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminTaxCertificateController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 15);
        $search = $request->input('search');

        $query = TaxCertificate::query();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', '%' . $search . '%')
                  ->orWhere('certificate_type', 'LIKE', '%' . $search . '%')
                  ->orWhere('document_number', 'LIKE', '%' . $search . '%');
            });
        }

        $paginator = $query->orderBy('id', 'desc')->paginate($perPage);

        $items = collect($paginator->items())->map(function ($c) {
            $pdfUrl = null;
            if ($c->file_path) {
                if (str_starts_with($c->file_path, 'certifications/')) {
                    $pdfUrl = url($c->file_path);
                } else {
                    $pdfUrl = Storage::disk('public')->url($c->file_path);
                }
            }

            return [
                'id'               => $c->id,
                'title'            => $c->title,
                'certificate_type' => $c->certificate_type,
                'document_number'  => $c->document_number,
                'valid_from'       => $c->valid_from,
                'valid_to'         => $c->valid_to,
                'description'      => $c->description,
                'is_active'        => (bool) $c->is_active,
                'pdf_url'          => $pdfUrl,
                'created_at'       => $c->created_at,
            ];
        });

        $stats = [
            'total'  => TaxCertificate::count(),
            'active' => TaxCertificate::where('is_active', true)->count(),
            'hidden' => TaxCertificate::where('is_active', false)->count(),
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
            'title'            => 'required|string|max:255',
            'certificate_type' => 'required|string|max:255',
            'document_number'  => 'nullable|string|max:255',
            'valid_from'       => 'nullable|date',
            'valid_to'         => 'nullable|date',
            'description'      => 'nullable|string',
            'pdf_file'         => 'required|file|mimes:pdf|max:10240',
        ]);

        $filePath = $request->file('pdf_file')->store('tax_certificates', 'public');

        $c = TaxCertificate::create([
            'title'            => $request->title,
            'certificate_type' => $request->certificate_type,
            'document_number'  => $request->document_number,
            'valid_from'       => $request->valid_from,
            'valid_to'         => $request->valid_to,
            'description'      => $request->description,
            'file_path'        => $filePath,
            'is_active'        => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tax certificate uploaded successfully.',
            'data'    => $c,
        ], 201);
    }

    public function toggleVisibility($id)
    {
        $c = TaxCertificate::find($id);
        if (!$c) {
            return response()->json(['success' => false, 'message' => 'Certificate not found'], 404);
        }

        $c->is_active = !$c->is_active;
        $c->save();

        return response()->json([
            'success' => true,
            'message' => "Certificate visibility toggled to " . ($c->is_active ? 'public' : 'hidden') . ".",
            'data'    => $c,
        ]);
    }

    public function destroy($id)
    {
        $c = TaxCertificate::find($id);
        if (!$c) {
            return response()->json(['success' => false, 'message' => 'Certificate not found'], 404);
        }

        if ($c->file_path && !str_starts_with($c->file_path, 'certifications/')) {
            if (Storage::disk('public')->exists($c->file_path)) {
                Storage::disk('public')->delete($c->file_path);
            }
        }

        $c->delete();

        return response()->json([
            'success' => true,
            'message' => 'Certificate deleted successfully.',
        ]);
    }
}
