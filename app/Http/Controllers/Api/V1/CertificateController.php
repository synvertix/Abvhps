<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TaxCertificate;
use App\Support\ApiMediaHelper;
use Illuminate\Http\JsonResponse;

class CertificateController extends Controller
{
    /**
     * List all active public 80G & 12A statutory tax compliance certificates.
     */
    public function __invoke(): JsonResponse
    {
        $certificates = TaxCertificate::activeComplianceCertificates()
            ->map(function ($cert) {
                $validFrom = $cert->valid_from ? $cert->valid_from->format('M Y') : null;
                $validTo   = $cert->valid_to ? $cert->valid_to->format('M Y') : null;

                $validitySummary = 'Permanent';
                if ($validFrom && $validTo) {
                    $validitySummary = "{$validFrom} - {$validTo}";
                } elseif ($validFrom) {
                    $validitySummary = "Valid from {$validFrom}";
                } elseif ($validTo) {
                    $validitySummary = "Valid until {$validTo}";
                }

                return [
                    'id'               => $cert->id,
                    'title'            => $cert->title,
                    'certificate_type' => $cert->certificate_type,
                    'document_number'  => $cert->document_number,
                    'valid_from'       => $cert->valid_from ? $cert->valid_from->toDateString() : null,
                    'valid_to'         => $cert->valid_to ? $cert->valid_to->toDateString() : null,
                    'validity_summary' => $validitySummary,
                    'description'      => $cert->description,
                    'download_url'     => ApiMediaHelper::resolveUrl($cert->file_path),
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $certificates,
            'message' => null,
        ]);
    }
}
