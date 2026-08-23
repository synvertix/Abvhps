<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Services\TinyProofImageService;
use RuntimeException;
use InvalidArgumentException;

class TinyProofImageServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TinyProofImageService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->service = new TinyProofImageService();
    }

    /**
     * Helper to generate a test GD image.
     */
    protected function createTestImage(int $width = 800, int $height = 600, string $format = 'jpeg'): UploadedFile
    {
        $img = imagecreatetruecolor($width, $height);
        $bg = imagecolorallocate($img, 220, 100, 50);
        imagefilledrectangle($img, 0, 0, $width, $height, $bg);
        $textColor = imagecolorallocate($img, 255, 255, 255);
        imagestring($img, 5, 20, 20, "ABVHPS PROOF TEST", $textColor);

        $tempPath = tempnam(sys_get_temp_dir(), 'test_img_');
        if ($format === 'png') {
            imagepng($img, $tempPath);
            $mime = 'image/png';
            $ext = 'png';
        } elseif ($format === 'webp') {
            imagewebp($img, $tempPath);
            $mime = 'image/webp';
            $ext = 'webp';
        } else {
            imagejpeg($img, $tempPath, 90);
            $mime = 'image/jpeg';
            $ext = 'jpg';
        }
        imagedestroy($img);

        return new UploadedFile($tempPath, "sample_proof.{$ext}", $mime, null, true);
    }

    /**
     * Helper to generate a high-entropy noisy test image (photo-like complex pattern).
     */
    protected function createHighEntropyImage(int $width = 600, int $height = 600): UploadedFile
    {
        $img = imagecreatetruecolor($width, $height);
        // Fill canvas with high-entropy noise patterns
        for ($x = 0; $x < $width; $x += 4) {
            for ($y = 0; $y < $height; $y += 4) {
                $color = imagecolorallocate($img, ($x * 13 + $y * 17) % 256, ($x * 37 + $y * 7) % 256, ($x * 19 + $y * 23) % 256);
                imagefilledrectangle($img, $x, $y, $x + 4, $y + 4, $color);
            }
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'noise_img_');
        imagejpeg($img, $tempPath, 95);
        imagedestroy($img);

        return new UploadedFile($tempPath, 'noisy_proof.jpg', 'image/jpeg', null, true);
    }

    public function test_jpeg_upload_is_compressed_strictly_below_2048_bytes()
    {
        $file = $this->createTestImage(1200, 900, 'jpeg');
        $this->assertGreaterThan(2048, $file->getSize());

        $result = $this->service->compressUploadedImage($file, 'public');

        $this->assertNotNull($result['path']);
        $this->assertLessThanOrEqual(2048, $result['bytes']);
        $this->assertTrue(Storage::disk('public')->exists($result['path']));

        $storedBytes = Storage::disk('public')->size($result['path']);
        $this->assertGreaterThan(0, $storedBytes);
        $this->assertLessThanOrEqual(2048, $storedBytes);
        $this->assertEquals($result['bytes'], $storedBytes);
    }

    public function test_png_upload_is_compressed_strictly_below_2048_bytes()
    {
        $file = $this->createTestImage(600, 600, 'png');

        $result = $this->service->compressUploadedImage($file, 'public');

        $this->assertLessThanOrEqual(2048, $result['bytes']);
        $this->assertTrue(Storage::disk('public')->exists($result['path']));

        $storedBytes = Storage::disk('public')->size($result['path']);
        $this->assertLessThanOrEqual(2048, $storedBytes);
    }

    public function test_webp_upload_is_compressed_strictly_below_2048_bytes()
    {
        $file = $this->createTestImage(500, 500, 'webp');

        $result = $this->service->compressUploadedImage($file, 'public');

        $this->assertLessThanOrEqual(2048, $result['bytes']);
        $this->assertTrue(Storage::disk('public')->exists($result['path']));

        $storedBytes = Storage::disk('public')->size($result['path']);
        $this->assertLessThanOrEqual(2048, $storedBytes);
    }

    public function test_high_entropy_noisy_image_is_compressed_and_stored_strictly_below_2048_bytes()
    {
        $file = $this->createHighEntropyImage(800, 800);

        $result = $this->service->compressUploadedImage($file, 'public');

        $this->assertLessThanOrEqual(2048, $result['bytes']);
        $this->assertTrue(Storage::disk('public')->exists($result['path']));

        $actualDiskBytes = Storage::disk('public')->size($result['path']);
        $this->assertGreaterThan(0, $actualDiskBytes);
        $this->assertLessThanOrEqual(2048, $actualDiskBytes);
        $this->assertEquals($result['bytes'], $actualDiskBytes);
    }

    public function test_uncompressible_image_triggers_controlled_rejection_message()
    {
        // Create an anonymous subclass that simulates a failure where no stage meets <= 2048 bytes
        $mockService = new class extends TinyProofImageService {
            public function processTinyCompression($sourceImage, int $origWidth, int $origHeight): ?array
            {
                return null; // Simulates that even lowest resolution exceeded 2048 bytes
            }
        };

        $file = $this->createTestImage(400, 400);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Image could not be compressed below 2 KB. Please upload a simpler image.');

        $mockService->compressUploadedImage($file);
    }

    public function test_filename_is_random_uuid_and_never_contains_member_data()
    {
        $file = $this->createTestImage(400, 400);

        $result = $this->service->compressUploadedImage($file);

        $this->assertStringStartsWith('proof_images/', $result['path']);
        $basename = basename($result['path']);
        $this->assertMatchesRegularExpression('/^[a-f0-9\-]{36}\.(webp|jpg)$/i', $basename);
    }

    public function test_service_does_not_delete_old_path_on_its_own()
    {
        $file1 = $this->createTestImage(400, 400);
        $result1 = $this->service->compressUploadedImage($file1);
        $this->assertTrue(Storage::disk('public')->exists($result1['path']));

        $file2 = $this->createTestImage(400, 400);
        $result2 = $this->service->compressUploadedImage($file2);

        // Both files exist on storage; caller manages atomic post-commit deletion
        $this->assertTrue(Storage::disk('public')->exists($result1['path']));
        $this->assertTrue(Storage::disk('public')->exists($result2['path']));
    }

    public function test_invalid_mime_type_is_rejected()
    {
        $this->expectException(InvalidArgumentException::class);
        $tempPath = tempnam(sys_get_temp_dir(), 'test_txt_');
        file_put_contents($tempPath, 'fake pdf content');

        $file = new UploadedFile($tempPath, 'document.pdf', 'application/pdf', null, true);
        $this->service->compressUploadedImage($file);
    }

    public function test_oversized_incoming_file_above_5mb_is_rejected()
    {
        $this->expectException(InvalidArgumentException::class);
        $tempPath = tempnam(sys_get_temp_dir(), 'test_big_');
        $fp = fopen($tempPath, 'w');
        fseek($fp, 5242881);
        fwrite($fp, 'a');
        fclose($fp);

        $file = new UploadedFile($tempPath, 'huge.jpg', 'image/jpeg', null, true);
        $this->service->compressUploadedImage($file);
    }
}
