<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * The origin / Vision / Mission / Goal wording lives in config/abvhps.php and is reused by the web home page,
 * the About page and the mobile API. The Flutter app keeps its own copy; these tests fail if any of them drift.
 */
class OrganizationCopyTest extends TestCase
{
    use RefreshDatabase;

    private function copy(string $key): string
    {
        return config('abvhps.copy.' . $key);
    }

    public function test_home_page_uses_the_shared_copy(): void
    {
        $html = html_entity_decode($this->get('/')->assertOk()->getContent(), ENT_QUOTES);

        foreach (['vision', 'mission', 'goal', 'origin_2', 'blessing'] as $key) {
            $this->assertStringContainsString($this->copy($key), $html, "Home page is missing copy: {$key}");
        }
        $this->assertStringContainsString('Registration No. 20/2023', $html);
        $this->assertStringNotContainsString('medical aid maps', $html);
        $this->assertStringNotContainsString('behest', $html);
    }

    public function test_about_page_shows_origin_and_the_same_pillars(): void
    {
        $html = html_entity_decode($this->get('/about')->assertOk()->getContent(), ENT_QUOTES);

        foreach (['vision', 'mission', 'goal', 'origin_2'] as $key) {
            $this->assertStringContainsString($this->copy($key), $html, "About page is missing copy: {$key}");
        }
        $this->assertStringContainsString('Our Divine Origin', $html);
        $this->assertStringContainsString('Our Mission in Action', $html);
    }

    public function test_mobile_api_about_returns_the_same_pillars_and_origin(): void
    {
        $data = $this->getJson('/api/v1/about')->assertOk()->json('data');

        $this->assertSame($this->copy('vision'), $data['pillars'][0]['description']);
        $this->assertSame($this->copy('mission'), $data['pillars'][1]['description']);
        $this->assertSame($this->copy('goal'), $data['pillars'][2]['description']);
        $this->assertSame($this->copy('origin_2'), $data['organization']['origin'][1]);
        $this->assertStringContainsString($this->copy('guru'), $data['organization']['origin'][0]);
    }

    public function test_flutter_app_copy_matches_the_shared_copy(): void
    {
        $vision = File::get(base_path('mobile_app/lib/features/home/widgets/vision_mission_section.dart'));
        foreach (['vision', 'mission', 'goal'] as $key) {
            $this->assertStringContainsString($this->copy($key), $vision, "Flutter vision_mission_section drifted: {$key}");
        }

        $origin = File::get(base_path('mobile_app/lib/features/home/widgets/divine_origin_section.dart'));
        [$originStart, $originEnd] = explode(':guru', $this->copy('origin_1'));
        $this->assertStringContainsString($originStart, $origin);
        $this->assertStringContainsString($originEnd, $origin);
        $this->assertStringContainsString($this->copy('origin_2'), $origin);
        $this->assertStringContainsString($this->copy('blessing'), $origin);
        $this->assertStringContainsString($this->copy('guru'), $origin);

        $footer = File::get(base_path('mobile_app/lib/features/home/widgets/public_footer.dart'));
        $this->assertStringContainsString($this->copy('footer_about'), $footer);
    }

    public function test_retired_helpline_number_does_not_appear_anywhere_in_source(): void
    {
        $roots = ['app', 'resources', 'routes', 'config', 'database', 'lang', 'mobile_app/lib', 'mobile_app/test', 'tests'];

        foreach ($roots as $root) {
            $dir = base_path($root);
            if (!is_dir($dir)) {
                continue;
            }
            foreach (File::allFiles($dir) as $file) {
                // This test itself and the migration that replaces the stored number necessarily mention it.
                if (in_array($file->getFilename(), ['OrganizationCopyTest.php', '2026_09_21_000002_replace_old_helpline_number.php'], true)) {
                    continue;
                }
                $this->assertStringNotContainsString('8884933379', File::get($file->getPathname()), $file->getPathname() . ' still contains the retired number');
            }
        }
    }
}
