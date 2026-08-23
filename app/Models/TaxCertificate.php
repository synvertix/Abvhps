<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxCertificate extends Model
{
    protected $table = 'tax_certificates';

    protected $fillable = [
        'title',
        'certificate_type',
        'document_number',
        'valid_from',
        'valid_to',
        'file_path',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'valid_from' => 'date',
        'valid_to'   => 'date',
    ];

    protected $appends = ['file_url'];

    public function getFileUrlAttribute()
    {
        if (!$this->file_path) {
            return '#';
        }
        if (str_starts_with($this->file_path, 'certifications/')) {
            return asset($this->file_path);
        }
        return asset('storage/' . $this->file_path);
    }

    /**
     * Reusable query for currently published & valid compliance certificates.
     *
     * Rules:
     * - is_active = true
     * - valid_from <= today OR valid_from IS NULL
     * - valid_to >= today OR valid_to IS NULL
     */
    public static function activeComplianceCertificates()
    {
        $today = now()->toDateString();
        return static::where('is_active', true)
            ->where(function ($q) use ($today) {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('valid_to')->orWhere('valid_to', '>=', $today);
            })
            ->orderBy('id', 'asc')
            ->get();
    }
}
