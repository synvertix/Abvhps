<?php

namespace Tests\Feature;

use App\Support\CompiledAssets;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * The public layout uses the Vite-compiled Tailwind CSS when a valid build exists and silently falls back to the
 * Tailwind CDN build otherwise — a missing / stale build must never break a page.
 */
class CompiledCssFallbackTest extends TestCase
{
    use RefreshDatabase;

    private string $tmpPublic;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tmpPublic = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'abvhps_public_' . uniqid();
        File::makeDirectory($this->tmpPublic . '/build/assets', 0777, true);
        $this->app->usePublicPath($this->tmpPublic);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->tmpPublic);
        parent::tearDown();
    }

    public function test_falls_back_to_the_cdn_when_there_is_no_build(): void
    {
        $this->assertFalse(CompiledAssets::cssAvailable());

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('cdn.jsdelivr.net/npm/@tailwindcss/browser@4', $html);
        $this->assertStringContainsString('--color-brandOrange', $html);
    }

    public function test_falls_back_when_the_manifest_is_stale_or_broken(): void
    {
        // manifest without our entry
        File::put($this->tmpPublic . '/build/manifest.json', json_encode(['resources/js/app.js' => ['file' => 'assets/app.js']]));
        $this->assertFalse(CompiledAssets::cssAvailable());
        $this->get('/')->assertOk()->assertSee('tailwindcss/browser@4', false);

        // entry present but the file is missing on disk
        File::put($this->tmpPublic . '/build/manifest.json', json_encode(['resources/css/app.css' => ['file' => 'assets/gone.css', 'isEntry' => true]]));
        $this->assertFalse(CompiledAssets::cssAvailable());
        $this->get('/')->assertOk()->assertSee('tailwindcss/browser@4', false);

        // corrupt JSON
        File::put($this->tmpPublic . '/build/manifest.json', '{not json');
        $this->assertFalse(CompiledAssets::cssAvailable());
        $this->get('/')->assertOk();
    }

    public function test_uses_the_compiled_stylesheet_when_the_build_is_valid(): void
    {
        File::put($this->tmpPublic . '/build/assets/app-test.css', '.x{color:red}');
        File::put($this->tmpPublic . '/build/manifest.json', json_encode([
            'resources/css/app.css' => ['file' => 'assets/app-test.css', 'src' => 'resources/css/app.css', 'isEntry' => true],
        ]));

        $this->assertTrue(CompiledAssets::cssAvailable());

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('build/assets/app-test.css', $html);
        $this->assertStringNotContainsString('tailwindcss/browser@4', $html);
    }

    public function test_the_real_theme_tokens_are_kept_in_the_compiled_source(): void
    {
        $css = File::get(base_path('resources/css/app.css'));

        foreach (['--color-brandOrange: #FF6600', '--color-brandGray: #4A4A4A', '--color-brandDarkGray: #1A1A1A', '--color-brandLightOrange: #FFF5EE'] as $token) {
            $this->assertStringContainsString($token, $css);
        }
        $this->assertStringContainsString('../views/**/*.blade.php', $css);
    }
}
