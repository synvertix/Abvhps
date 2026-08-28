<?php

namespace App\Support;

class ApiMediaHelper
{
    /**
     * Resolves a public-accessible URL for an image, document, or media asset.
     *
     * @param string|null $path
     * @param string|null $fallback
     * @return string|null
     */
    public static function resolveUrl(?string $path, ?string $fallback = null): ?string
    {
        if (empty($path)) {
            return $fallback;
        }

        $trimmed = trim($path);

        // Reject local filesystem absolute paths
        if (preg_match('/^[a-zA-Z]:[\\\\\/]/', $trimmed) || str_starts_with($trimmed, '/var/www') || str_contains($trimmed, 'storage/app/private')) {
            return $fallback;
        }

        // Already absolute HTTP/HTTPS URLs
        if (preg_match('/^https?:\/\//i', $trimmed)) {
            return $trimmed;
        }

        // Public static directory files
        if (str_starts_with($trimmed, 'certifications/') || str_starts_with($trimmed, 'images/') || str_starts_with($trimmed, 'assets/')) {
            return asset($trimmed);
        }

        // Storage public symlinked files
        $cleanPath = ltrim(str_replace('\\', '/', $trimmed), '/');
        if (str_starts_with($cleanPath, 'storage/')) {
            $cleanPath = substr($cleanPath, 8);
        }

        return url('storage/' . $cleanPath);
    }
}
