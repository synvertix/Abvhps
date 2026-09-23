<?php

namespace Tests\Feature;

use App\Models\HomeSlider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminHomeSliderTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name'     => 'Super Admin',
            'email'    => 'admin@abvhps.org',
            'password' => bcrypt('password123'),
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title'      => 'Temple Restoration Drive',
            'subtitle'   => 'Rebuilding sacred spaces',
            'sort_order' => 1,
            'is_active'  => '1',
        ], $overrides);
    }

    public function test_guests_cannot_reach_slider_admin(): void
    {
        $this->get(route('admin.sliders.index'))->assertRedirect();
        $this->post(route('admin.sliders.store'), $this->payload())->assertRedirect();
        $this->assertSame(0, HomeSlider::count());
    }

    public function test_admin_sees_the_list_and_sidebar_link(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.sliders.index'))
            ->assertOk()
            ->assertSee('Home Page Hero Slides')
            ->assertSee('HERO SLIDES')
            ->assertSee('3 built-in slides');

        // Existing sidebar entries must be untouched
        $this->actingAs($admin)->get(route('admin.sliders.index'))->assertSee('BANNER MANAGEMENT')->assertSee('SITE GLOBAL SETTINGS');
        $this->actingAs($admin)->get(route('admin.sliders.create'))->assertOk();
    }

    public function test_admin_can_create_slide_with_image_and_button(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->post(route('admin.sliders.store'), $this->payload([
            'cta_label' => 'Make a Donation',
            'cta_url'   => '/donations',
            'image'     => UploadedFile::fake()->image('hero.jpg', 1600, 600),
        ]))->assertRedirect(route('admin.sliders.index'));

        $slide = HomeSlider::firstOrFail();
        $this->assertTrue($slide->is_active);
        $this->assertSame('/donations', $slide->cta_url);
        Storage::disk('public')->assertExists($slide->image_path);

        // Public home + mobile API now use the configured slide (not the built-in ones)
        $this->get('/')->assertOk()->assertSee('Temple Restoration Drive')->assertSee('Make a Donation')->assertDontSee('Serve with Devotion');
        $this->getJson('/api/v1/home')->assertOk()->assertJsonPath('data.sliders.0.title', 'Temple Restoration Drive');
    }

    public function test_slide_without_image_is_allowed(): void
    {
        $this->actingAs($this->admin())->post(route('admin.sliders.store'), $this->payload())
            ->assertRedirect(route('admin.sliders.index'));

        $this->assertSame('', HomeSlider::firstOrFail()->image_path);
        $this->get('/')->assertOk()->assertSee('Temple Restoration Drive');
    }

    public function test_validation_rejects_unsafe_or_incomplete_button_links(): void
    {
        $admin = $this->admin();

        foreach (['javascript:alert(1)', 'http://insecure.example', '//evil.example', 'donations', 'https://exa mple.com'] as $badUrl) {
            $this->actingAs($admin)->post(route('admin.sliders.store'), $this->payload(['cta_label' => 'Go', 'cta_url' => $badUrl]))
                ->assertSessionHasErrors('cta_url');
        }

        // label without url, and url without label
        $this->actingAs($admin)->post(route('admin.sliders.store'), $this->payload(['cta_label' => 'Go']))->assertSessionHasErrors('cta_url');
        $this->actingAs($admin)->post(route('admin.sliders.store'), $this->payload(['cta_url' => '/about']))->assertSessionHasErrors('cta_label');

        // missing title and non-image upload
        $this->actingAs($admin)->post(route('admin.sliders.store'), $this->payload(['title' => '']))->assertSessionHasErrors('title');
        $this->actingAs($admin)->post(route('admin.sliders.store'), $this->payload(['image' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')]))->assertSessionHasErrors('image');

        $this->assertSame(0, HomeSlider::count());

        // valid forms still pass
        $this->actingAs($admin)->post(route('admin.sliders.store'), $this->payload(['cta_label' => 'Join', 'cta_url' => 'https://janavedika.in/@abvhps']))
            ->assertSessionHasNoErrors();
        $this->assertSame(1, HomeSlider::count());
    }

    public function test_update_replaces_and_removes_images_and_toggle_and_delete_clean_up(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.sliders.store'), $this->payload(['image' => UploadedFile::fake()->image('a.png')]));
        $slide = HomeSlider::firstOrFail();
        $oldPath = $slide->image_path;

        // replace image
        $this->actingAs($admin)->post(route('admin.sliders.update', $slide->id), $this->payload(['title' => 'Renamed', 'image' => UploadedFile::fake()->image('b.png')]))
            ->assertRedirect(route('admin.sliders.index'));
        $slide->refresh();
        $this->assertSame('Renamed', $slide->title);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($slide->image_path);

        // not ticking "visible" hides it
        $this->actingAs($admin)->post(route('admin.sliders.update', $slide->id), ['title' => 'Renamed', 'sort_order' => 1, 'remove_image' => '1']);
        $slide->refresh();
        $this->assertFalse($slide->is_active);
        $this->assertSame('', $slide->image_path);

        // toggle back on, then delete
        $this->actingAs($admin)->post(route('admin.sliders.toggle', $slide->id))->assertRedirect(route('admin.sliders.index'));
        $this->assertTrue($slide->fresh()->is_active);

        $this->actingAs($admin)->delete(route('admin.sliders.destroy', $slide->id))->assertRedirect(route('admin.sliders.index'));
        $this->assertSame(0, HomeSlider::count());

        // with nothing configured the built-in slides return
        $this->get('/')->assertSee('Serve with Devotion');
    }
}
