<?php

namespace App\Support;

/**
 * Tells the layout whether the Vite-compiled Tailwind stylesheet can be used.
 * If the build is missing or stale (no entry / file), the site falls back to the Tailwind CDN build,
 * so a failed or forgotten `npm run build` can never leave the public site unstyled or throw a 500.
 */
class CompiledAssets
{
    private const ENTRY = 'resources/css/app.css';

    public static function cssAvailable(): bool
    {
        if (is_file(public_path('hot'))) {
            return true; // `npm run dev` is running
        }

        $manifestPath = public_path('build/manifest.json');
        if (!is_file($manifestPath)) {
            return false;
        }

        $manifest = json_decode((string) file_get_contents($manifestPath), true);
        $file = is_array($manifest) ? ($manifest[self::ENTRY]['file'] ?? null) : null;

        return is_string($file) && is_file(public_path('build/' . $file));
    }
}
