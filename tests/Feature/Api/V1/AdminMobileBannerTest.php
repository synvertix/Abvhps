<?php

namespace Tests\Feature\Api\V1;

use App\Models\Banner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminMobileBannerTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminUser(): User
    {
        return User::create([
            'name'     => 'Commander Admin',
            'email'    => 'admin@abvhps.org',
            'password' => \Illuminate\Support\Facades\Hash::make('AdminSecret123!'),
        ]);
    }

    public function test_admin_can_toggle_banner_status()
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin, ['account:admin', 'admin:dashboard']);

        $b = Banner::create([
            'page_key'       => 'home',
            'title'          => 'WELCOME BANNER',
            'status'         => 'show',
            'desktop_banner' => 'banners/desktop/default.jpg',
            'mobile_banner'  => 'banners/mobile/default.jpg',
        ]);

        $response = $this->postJson("/api/v1/admin/banners/{$b->id}/toggle");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('banners', ['id' => $b->id, 'status' => 'hide']);
    }
}
