<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class LanguageSwitchTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_language_is_english_with_switcher_listing_all_languages(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('<html lang="en">', $html);
        $this->assertStringContainsString('id="lang-switcher"', $html);
        foreach (config('abvhps.locales') as $code => $meta) {
            $this->assertStringContainsString('/lang/' . $code, $html, "Switcher is missing {$code}");
            $this->assertStringContainsString($meta['native'], $html);
        }
        $this->assertStringNotContainsString('fonts.googleapis.com/css2', $html, 'No extra font for English');
    }

    public function test_switching_language_remembers_choice_and_translates_the_page(): void
    {
        $this->get('/lang/te')->assertRedirect('/')->assertCookie('abvhps_lang');

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('<html lang="te">', $html);
        $this->assertStringContainsString('Noto+Sans+Telugu', $html);
        $this->assertStringContainsString('మా గురించి', $html);              // nav "About"
        $this->assertStringContainsString('మా దృక్పథం', $html);               // "Our Vision"
        $this->assertStringContainsString('హెల్ప్‌లైన్', $html);               // "Helpline"
        // proper names stay in Latin script
        $this->assertStringContainsString('Sri Sri Sri Subrahmanneswara Swamy Garu', $html);
    }

    public function test_each_language_renders_home_without_errors(): void
    {
        foreach (array_keys(config('abvhps.locales')) as $code) {
            $this->withSession(['locale' => $code])->get('/')->assertOk()->assertSee('<html lang="' . $code . '">', false);
            $this->withSession(['locale' => $code])->get('/about')->assertOk();
        }
    }

    public function test_unknown_language_is_rejected(): void
    {
        $this->get('/lang/xx')->assertNotFound();
        $this->get('/lang/%3Cscript%3E')->assertNotFound();
    }

    public function test_language_switch_never_redirects_off_site_or_into_admin(): void
    {
        $this->get('/lang/hi', ['Referer' => 'https://evil.example/phish'])->assertRedirect('/');
        $this->get('/lang/hi', ['Referer' => url('/admin/sliders')])->assertRedirect('/');
        $this->get('/lang/hi', ['Referer' => url('/about')])->assertRedirect(url('/about'));
    }

    public function test_admin_screens_stay_in_english_whatever_language_the_visitor_chose(): void
    {
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@abvhps.org', 'password' => bcrypt('password123')]);

        $this->withSession(['locale' => 'te'])->actingAs($admin)->get(route('admin.sliders.index'))
            ->assertOk()
            ->assertSee('<html lang="en">', false)
            ->assertSee('Home Page Hero Slides')
            ->assertDontSee('Noto+Sans+Telugu', false);
    }

    public function test_translation_files_are_complete_and_keep_placeholders(): void
    {
        $english = collect(File::files(base_path('lang')))->map->getFilename()->all();
        $codes = array_diff(array_keys(config('abvhps.locales')), ['en']);

        $reference = json_decode(File::get(base_path('lang/hi.json')), true);
        $this->assertNotEmpty($reference);

        foreach ($codes as $code) {
            $this->assertContains($code . '.json', $english, "lang/{$code}.json is missing");
            $map = json_decode(File::get(base_path("lang/{$code}.json")), true);
            $this->assertIsArray($map, "lang/{$code}.json is not valid JSON");
            $this->assertSame(array_keys($reference), array_keys($map), "lang/{$code}.json keys differ from lang/hi.json");

            foreach ($map as $key => $value) {
                $this->assertNotSame('', trim($value), "{$code}: empty translation for {$key}");
                foreach ([':name', ':guru'] as $placeholder) {
                    if (str_contains($key, $placeholder)) {
                        $this->assertStringContainsString($placeholder, $value, "{$code}: '{$placeholder}' lost in translation of: {$key}");
                    }
                }
            }
        }

        // every shared organisation sentence is translatable
        foreach (['vision', 'mission', 'goal', 'origin_1', 'origin_2', 'blessing', 'footer_about'] as $copyKey) {
            $this->assertArrayHasKey(config('abvhps.copy.' . $copyKey), $reference, "No translation entry for copy: {$copyKey}");
        }
    }

    public function test_mobile_app_translations_are_identical_to_the_website_ones(): void
    {
        foreach (array_diff(array_keys(config('abvhps.locales')), ['en']) as $code) {
            $this->assertSame(
                File::get(base_path("lang/{$code}.json")),
                File::get(base_path("mobile_app/assets/i18n/{$code}.json")),
                "mobile_app/assets/i18n/{$code}.json is out of sync with lang/{$code}.json (copy it over)"
            );
        }
    }
}
