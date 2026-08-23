<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class TinyProofImageService
{
    /**
     * Strict maximum byte limit for stored proof images (2 KB = 2048 bytes).
     */
    public const MAX_BYTES = 2048;

    /**
     * Maximum incoming source file size (5 MB = 5242880 bytes).
     */
    public const MAX_INCOMING_BYTES = 5242880;

    /**
     * Supported incoming MIME types.
     */
    public const ALLOWED_MIMES = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/webp',
    ];

    /**
     * Compress an uploaded proof image strictly below 2048 bytes and persist only the tiny result.
     *
     * Note: This service ONLY compresses and writes the new file.
     * Old file cleanup must be performed by the caller ONLY after successful database persistence.
     *
     * @param UploadedFile $file
     * @param string $disk Storage disk (default: 'public')
     * @return array{path: string, bytes: int, mime: string, width: int, height: int}
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function compressUploadedImage(UploadedFile $file, string $disk = 'public'): array
    {
        // 1. Validate file size up to 5 MB
        if ($file->getSize() > self::MAX_INCOMING_BYTES) {
            throw new InvalidArgumentException('Source image exceeds maximum allowed upload size of 5 MB.');
        }

        // 2. Validate MIME type
        $mime = $file->getMimeType();
        if (!in_array(strtolower((string)$mime), self::ALLOWED_MIMES, true)) {
            throw new InvalidArgumentException('Invalid image format. Only JPG, PNG, and WebP images are accepted.');
        }

        $realPath = $file->getRealPath();
        if (!$realPath || !file_exists($realPath)) {
            throw new InvalidArgumentException('Uploaded source file could not be read.');
        }

        // 3. Decode server-side safely
        $sourceImage = $this->createImageResource($realPath, $mime);
        if (!$sourceImage) {
            throw new InvalidArgumentException('Could not decode uploaded image.');
        }

        $origWidth = imagesx($sourceImage);
        $origHeight = imagesy($sourceImage);

        if ($origWidth <= 0 || $origHeight <= 0) {
            imagedestroy($sourceImage);
            throw new InvalidArgumentException('Invalid image dimensions.');
        }

        // 4. Iterative downscaling and compression pipeline
        $result = $this->processTinyCompression($sourceImage, $origWidth, $origHeight);

        // Discard source resource immediately
        imagedestroy($sourceImage);

        if (!$result || $result['bytes'] > self::MAX_BYTES) {
            throw new RuntimeException('Image could not be compressed below 2 KB. Please upload a simpler image.');
        }

        // 5. Generate secure randomized filename (UUID.webp or UUID.jpg)
        $extension = $result['format'] === 'webp' ? 'webp' : 'jpg';
        $filename = Str::uuid()->toString() . '.' . $extension;
        $storagePath = 'proof_images/' . $filename;

        // 6. Persist with write verification
        $writeSuccess = false;
        try {
            $writeSuccess = Storage::disk($disk)->put($storagePath, $result['binary']);
        } catch (\Throwable $e) {
            throw new RuntimeException('Failed to write proof image to storage: ' . $e->getMessage(), 0, $e);
        }

        if (!$writeSuccess) {
            throw new RuntimeException('Failed to write proof image to storage.');
        }

        // 7. Verify actual persisted file on disk
        $actualStoredBytes = Storage::disk($disk)->size($storagePath);
        if ($actualStoredBytes <= 0 || $actualStoredBytes > self::MAX_BYTES) {
            Storage::disk($disk)->delete($storagePath);
            throw new RuntimeException("Image could not be compressed below 2 KB. Please upload a simpler image.");
        }

        return [
            'path'   => $storagePath,
            'bytes'  => $actualStoredBytes,
            'mime'   => $result['mime'],
            'width'  => $result['width'],
            'height' => $result['height'],
        ];
    }

    /**
     * Compress raw image binary content directly.
     *
     * @param string $binaryContent
     * @param string $disk
     * @return array{path: string, bytes: int, mime: string, width: int, height: int}
     */
    public function compressBinary(string $binaryContent, string $disk = 'public'): array
    {
        $sourceImage = @imagecreatefromstring($binaryContent);
        if (!$sourceImage) {
            throw new InvalidArgumentException('Could not decode binary image.');
        }

        $origWidth = imagesx($sourceImage);
        $origHeight = imagesy($sourceImage);

        $result = $this->processTinyCompression($sourceImage, $origWidth, $origHeight);
        imagedestroy($sourceImage);

        if (!$result || $result['bytes'] > self::MAX_BYTES) {
            throw new RuntimeException('Image could not be compressed below 2 KB. Please upload a simpler image.');
        }

        $extension = $result['format'] === 'webp' ? 'webp' : 'jpg';
        $filename = Str::uuid()->toString() . '.' . $extension;
        $storagePath = 'proof_images/' . $filename;

        $writeSuccess = Storage::disk($disk)->put($storagePath, $result['binary']);
        if (!$writeSuccess) {
            throw new RuntimeException('Failed to write proof image to storage.');
        }

        $actualStoredBytes = Storage::disk($disk)->size($storagePath);
        if ($actualStoredBytes <= 0 || $actualStoredBytes > self::MAX_BYTES) {
            Storage::disk($disk)->delete($storagePath);
            throw new RuntimeException("Image could not be compressed below 2 KB. Please upload a simpler image.");
        }

        return [
            'path'   => $storagePath,
            'bytes'  => $actualStoredBytes,
            'mime'   => $result['mime'],
            'width'  => $result['width'],
            'height' => $result['height'],
        ];
    }

    /**
     * Delete stored proof image if it exists.
     */
    public function deleteImageIfExists(?string $path, string $disk = 'public'): bool
    {
        if (empty($path)) {
            return false;
        }

        try {
            if (Storage::disk($disk)->exists($path)) {
                return Storage::disk($disk)->delete($path);
            }
        } catch (\Throwable) {
            // Ignore deletion failures during compensating cleanup
        }

        return false;
    }

    /**
     * Safe image decoder supporting WebP, JPEG, PNG.
     */
    protected function createImageResource(string $path, ?string $mime = null)
    {
        if (function_exists('imagecreatefromstring')) {
            $content = @file_get_contents($path);
            if ($content !== false) {
                $img = @imagecreatefromstring($content);
                if ($img !== false) {
                    return $img;
                }
            }
        }

        return match ($mime) {
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null,
            'image/png'  => function_exists('imagecreatefrompng') ? @imagecreatefrompng($path) : null,
            default      => function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($path) : null,
        };
    }

    /**
     * Iteratively scale and compress down to <= 2048 bytes.
     *
     * Dimensions sequence: 128 -> 112 -> 96 -> 80 -> 64 -> 48 -> 36 -> 32 -> 24
     * Qualities sequence: 75 -> 60 -> 45 -> 30 -> 20 -> 12 -> 8 -> 5
     */
    public function processTinyCompression($sourceImage, int $origWidth, int $origHeight): ?array
    {
        $hasWebp = function_exists('imagewebp');
        $dimensionSteps = [128, 112, 96, 80, 64, 48, 36, 32, 24];
        $qualitySteps = [75, 60, 45, 30, 20, 12, 8, 5];

        foreach ($dimensionSteps as $maxDim) {
            // Compute dimensions maintaining aspect ratio without upscaling
            if ($origWidth <= $maxDim && $origHeight <= $maxDim) {
                $targetWidth = $origWidth;
                $targetHeight = $origHeight;
            } else {
                $ratio = min($maxDim / $origWidth, $maxDim / $origHeight);
                $targetWidth = max(1, (int) round($origWidth * $ratio));
                $targetHeight = max(1, (int) round($origHeight * $ratio));
            }

            $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
            if (!$canvas) {
                continue;
            }

            // Fill white background for transparency handling
            $white = imagecolorallocate($canvas, 255, 255, 255);
            imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $white);

            // Resample (strips EXIF automatically)
            imagecopyresampled(
                $canvas,
                $sourceImage,
                0, 0, 0, 0,
                $targetWidth,
                $targetHeight,
                $origWidth,
                $origHeight
            );

            // Try WebP first if available
            if ($hasWebp) {
                foreach ($qualitySteps as $q) {
                    ob_start();
                    imagewebp($canvas, null, $q);
                    $binary = ob_get_clean();

                    if ($binary !== false) {
                        $bytes = strlen($binary);
                        if ($bytes > 0 && $bytes <= self::MAX_BYTES) {
                            imagedestroy($canvas);
                            return [
                                'binary' => $binary,
                                'bytes'  => $bytes,
                                'mime'   => 'image/webp',
                                'format' => 'webp',
                                'width'  => $targetWidth,
                                'height' => $targetHeight,
                            ];
                        }
                    }
                }
            }

            // Try JPEG fallback
            foreach ($qualitySteps as $q) {
                ob_start();
                imagejpeg($canvas, null, $q);
                $binary = ob_get_clean();

                if ($binary !== false) {
                    $bytes = strlen($binary);
                    if ($bytes > 0 && $bytes <= self::MAX_BYTES) {
                        imagedestroy($canvas);
                        return [
                            'binary' => $binary,
                            'bytes'  => $bytes,
                            'mime'   => 'image/jpeg',
                            'format' => 'jpeg',
                            'width'  => $targetWidth,
                            'height' => $targetHeight,
                        ];
                    }
                }
            }

            imagedestroy($canvas);
        }

        return null;
    }
}
