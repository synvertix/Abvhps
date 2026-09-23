<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HomeHeroSlidesTest extends TestCase
{
    use RefreshDatabase;

    public function test_hero_falls_back_to_built_in_rotating_slides_when_none_configured(): void
    {
        $content = $this->get('/')->assertOk()->getContent();

        $this->assertSame(3, substr_count($content, 'aria-roledescription="slide"'), 'Three default slides expected');
        $this->assertStringContainsString('id="hero-prev"', $content);
        $this->assertStringContainsString('id="hero-next"', $content);
        $this->assertSame(1, substr_count($content, '<h1'), 'Exactly one h1 (first slide) for SEO');
        $this->assertStringContainsString('images/hero.mp4', $content);
        $this->assertStringNotContainsString('videos/hero.mp4', $content);
    }

    public function test_hero_uses_configured_slides_and_skips_inactive_ones(): void
    {
        DB::table('home_sliders')->insert([
            ['image_path' => 'sliders/temple.jpg', 'title' => 'Temple Restoration Drive', 'subtitle' => 'Rebuilding sacred spaces', 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['image_path' => 'sliders/hidden.jpg', 'title' => 'Hidden Slide', 'subtitle' => 'Should not show', 'sort_order' => 2, 'is_active' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $content = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('Temple Restoration Drive', $content);
        $this->assertStringNotContainsString('Hidden Slide', $content);
        $this->assertStringNotContainsString('Serve with Devotion', $content, 'Defaults must not mix with configured slides');
        // A single slide has no arrows/dots
        $this->assertStringNotContainsString('id="hero-next"', $content);
    }

    public function test_mobile_api_home_returns_default_slides_when_none_configured(): void
    {
        $response = $this->getJson('/api/v1/home')->assertOk();

        $sliders = $response->json('data.sliders');
        $this->assertCount(3, $sliders);
        $this->assertSame('Serve with Devotion', $sliders[1]['title']);
        $this->assertArrayHasKey('subtitle', $sliders[0]);
        $this->assertArrayHasKey('image_url', $sliders[0]);
    }

    public function test_divine_origin_section_renders_verified_facts(): void
    {
        $content = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('id="divine-origin"', $content);
        $this->assertStringContainsString('Registration No. 20/2023', $content);
        $this->assertStringContainsString('Sri Sri Sri Subrahmanneswara Swamy Garu', $content);
        $this->assertStringContainsString('Divine Blessings', $content);
    }
}
