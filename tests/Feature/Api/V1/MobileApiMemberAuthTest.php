<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use App\Models\Membership;
use App\Services\MobileMemberOtpService;

class MobileApiMemberAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function createMember(array $attributes = []): Membership
    {
        return Membership::create(array_merge([
            'membership_id' => '123456789012',
            'phone'         => '9876543210',
            'full_name'     => 'Anjaneya',
            'is_completed'  => true,
            'state'         => 'Andhra Pradesh',
            'district'      => 'Guntur',
        ], $attributes));
    }

    public function test_send_otp_returns_generic_response_and_challenge_id(): void
    {
        $response = $this->postJson('/api/v1/auth/member/send-otp', [
            'phone' => '9876543210',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'challenge_id',
                'message',
            ]);

        $this->assertTrue($response->json('success'));
        $this->assertNotEmpty($response->json('challenge_id'));

        // Assert OTP is NOT returned in API response
        $this->assertArrayNotHasKey('otp', $response->json());
    }

    public function test_verify_otp_authenticates_member_and_issues_sanctum_token(): void
    {
        $member = $this->createMember();

        // Create challenge directly
        $challenge = MobileMemberOtpService::createChallenge('9876543210');
        $challengeId = $challenge['challenge_id'];

        // Retrieve the cached OTP for test verification
        $cachedData = Cache::get("member_otp_challenge:{$challengeId}");
        $this->assertNotNull($cachedData);

        // Simulate OTP verification using a known test challenge setup
        // Let's create an explicit test challenge with fixed hash
        $testChallengeId = \Illuminate\Support\Str::uuid()->toString();
        Cache::put("member_otp_challenge:{$testChallengeId}", [
            'phone'       => '9876543210',
            'otp_hash'    => password_hash('123456', PASSWORD_BCRYPT),
            'attempts'    => 0,
            'expires_at'  => now()->addMinutes(5)->timestamp,
            'is_consumed' => false,
        ], now()->addMinutes(5));

        $verifyResponse = $this->postJson('/api/v1/auth/member/verify-otp', [
            'phone'        => '9876543210',
            'challenge_id' => $testChallengeId,
            'otp'          => '123456',
            'device_name'  => 'Galaxy S24',
        ]);

        $verifyResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'account_type' => 'member',
                    'profile' => [
                        'membership_id' => '123456789012',
                        'full_name'     => 'Anjaneya',
                    ],
                ],
            ]);

        $token = $verifyResponse->json('data.token');
        $this->assertNotEmpty($token);

        // Assert OTP was consumed and cannot be replayed
        $replayResponse = $this->postJson('/api/v1/auth/member/verify-otp', [
            'phone'        => '9876543210',
            'challenge_id' => $testChallengeId,
            'otp'          => '123456',
            'device_name'  => 'Galaxy S24',
        ]);

        $replayResponse->assertStatus(410);
    }

    public function test_wrong_otp_increments_attempts_and_locks_after_max(): void
    {
        $testChallengeId = \Illuminate\Support\Str::uuid()->toString();
        Cache::put("member_otp_challenge:{$testChallengeId}", [
            'phone'       => '9876543210',
            'otp_hash'    => password_hash('654321', PASSWORD_BCRYPT),
            'attempts'    => 0,
            'expires_at'  => now()->addMinutes(5)->timestamp,
            'is_consumed' => false,
        ], now()->addMinutes(5));

        // Wrong attempt 1
        $res = $this->postJson('/api/v1/auth/member/verify-otp', [
            'phone'        => '9876543210',
            'challenge_id' => $testChallengeId,
            'otp'          => '000000',
            'device_name'  => 'Galaxy S24',
        ]);
        $res->assertStatus(422);

        // Attempt until max exceeded (5 times total)
        for ($i = 0; $i < 4; $i++) {
            $this->postJson('/api/v1/auth/member/verify-otp', [
                'phone'        => '9876543210',
                'challenge_id' => $testChallengeId,
                'otp'          => '000000',
                'device_name'  => 'Galaxy S24',
            ]);
        }

        // 6th attempt should return 429 or 410 (exceeded/expired)
        $lockedResponse = $this->postJson('/api/v1/auth/member/verify-otp', [
            'phone'        => '9876543210',
            'challenge_id' => $testChallengeId,
            'otp'          => '654321', // Even correct OTP fails once max exceeded
            'device_name'  => 'Galaxy S24',
        ]);

        $this->assertTrue(in_array($lockedResponse->status(), [410, 429]));
    }

    public function test_device_name_validation_on_member_verify_otp(): void
    {
        $testChallengeId = \Illuminate\Support\Str::uuid()->toString();

        // Missing device_name -> 422
        $res1 = $this->postJson('/api/v1/auth/member/verify-otp', [
            'phone'        => '9876543210',
            'challenge_id' => $testChallengeId,
            'otp'          => '123456',
        ]);
        $res1->assertStatus(422)
            ->assertJsonValidationErrors(['device_name']);

        // device_name > 100 chars -> 422
        $res2 = $this->postJson('/api/v1/auth/member/verify-otp', [
            'phone'        => '9876543210',
            'challenge_id' => $testChallengeId,
            'otp'          => '123456',
            'device_name'  => str_repeat('b', 101),
        ]);
        $res2->assertStatus(422)
            ->assertJsonValidationErrors(['device_name']);
    }

    public function test_member_multi_device_login_and_token_names(): void
    {
        $member = $this->createMember();

        // Challenge 1 for Device A
        $challengeA = \Illuminate\Support\Str::uuid()->toString();
        Cache::put("member_otp_challenge:{$challengeA}", [
            'phone'       => '9876543210',
            'otp_hash'    => password_hash('111111', PASSWORD_BCRYPT),
            'attempts'    => 0,
            'expires_at'  => now()->addMinutes(5)->timestamp,
            'is_consumed' => false,
        ], now()->addMinutes(5));

        $verifyA = $this->postJson('/api/v1/auth/member/verify-otp', [
            'phone'        => '9876543210',
            'challenge_id' => $challengeA,
            'otp'          => '111111',
            'device_name'  => 'Samsung Galaxy',
        ]);
        $verifyA->assertStatus(200);
        $tokenA = $verifyA->json('data.token');

        // Challenge 2 for Device B
        $challengeB = \Illuminate\Support\Str::uuid()->toString();
        Cache::put("member_otp_challenge:{$challengeB}", [
            'phone'       => '9876543210',
            'otp_hash'    => password_hash('222222', PASSWORD_BCRYPT),
            'attempts'    => 0,
            'expires_at'  => now()->addMinutes(5)->timestamp,
            'is_consumed' => false,
        ], now()->addMinutes(5));

        $verifyB = $this->postJson('/api/v1/auth/member/verify-otp', [
            'phone'        => '9876543210',
            'challenge_id' => $challengeB,
            'otp'          => '222222',
            'device_name'  => 'Android Tablet',
        ]);
        $verifyB->assertStatus(200);
        $tokenB = $verifyB->json('data.token');

        // Inspect issued tokens
        $tokens = $member->tokens()->get();
        $this->assertCount(2, $tokens);
        $this->assertTrue($tokens->pluck('name')->contains('Samsung Galaxy'));
        $this->assertTrue($tokens->pluck('name')->contains('Android Tablet'));

        // Assert member exact abilities
        $tokenRecordA = $tokens->firstWhere('name', 'Samsung Galaxy');
        $this->assertEquals([
            'mobile',
            'account:member',
            'member:profile',
            'member:card',
        ], $tokenRecordA->abilities);

        // Independent logout: Logout Device A
        \Illuminate\Support\Facades\Auth::forgetGuards();
        $logoutA = $this->withHeader('Authorization', 'Bearer ' . $tokenA)
            ->postJson('/api/v1/auth/logout');
        $logoutA->assertStatus(200);

        // Token A is revoked
        \Illuminate\Support\Facades\Auth::forgetGuards();
        $this->withHeader('Authorization', 'Bearer ' . $tokenA)
            ->getJson('/api/v1/me')
            ->assertStatus(401);

        // Token B remains valid
        \Illuminate\Support\Facades\Auth::forgetGuards();
        $this->withHeader('Authorization', 'Bearer ' . $tokenB)
            ->getJson('/api/v1/me')
            ->assertStatus(200);

        // Logout-all on Device B revokes remaining tokens
        \Illuminate\Support\Facades\Auth::forgetGuards();
        $logoutAll = $this->withHeader('Authorization', 'Bearer ' . $tokenB)
            ->postJson('/api/v1/auth/logout-all');
        $logoutAll->assertStatus(200);

        // Token B is now revoked
        \Illuminate\Support\Facades\Auth::forgetGuards();
        $this->withHeader('Authorization', 'Bearer ' . $tokenB)
            ->getJson('/api/v1/me')
            ->assertStatus(401);
    }
}
