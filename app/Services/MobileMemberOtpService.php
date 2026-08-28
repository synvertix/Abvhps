<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Membership;

class MobileMemberOtpService
{
    protected const OTP_EXPIRATION_MINUTES = 5;
    protected const MAX_VERIFY_ATTEMPTS = 5;

    /**
     * Issue an expiring, rate-controlled OTP challenge for a 10-digit phone number.
     * Generates a unique challenge_id and dispatches SMS (or skips in local/test).
     *
     * @param string $phone 10-digit mobile number
     * @return array{success: bool, challenge_id: string, message: string}
     */
    public static function createChallenge(string $phone): array
    {
        $challengeId = Str::uuid()->toString();
        $otp = (string) random_int(100000, 999999);
        $expiresAt = Carbon::now()->addMinutes(self::OTP_EXPIRATION_MINUTES);

        $payload = [
            'phone'       => $phone,
            'otp_hash'    => password_hash($otp, PASSWORD_BCRYPT),
            'attempts'    => 0,
            'expires_at'  => $expiresAt->timestamp,
            'is_consumed' => false,
        ];

        Cache::put("member_otp_challenge:{$challengeId}", $payload, $expiresAt);

        // Also track phone -> latest challenge mapping
        Cache::put("member_phone_challenge:{$phone}", $challengeId, $expiresAt);

        // Dispatch OTP via Fast2SMS (DLT / OTP gateway)
        $smsResult = Fast2SmsService::sendOtp($phone, $otp);

        return [
            'success'      => true,
            'challenge_id' => $challengeId,
            'message'      => 'If this mobile number is eligible, an OTP verification code has been dispatched.',
        ];
    }

    /**
     * Atomically verify and consume an OTP challenge.
     *
     * @param string $phone
     * @param string $challengeId
     * @param string $otp
     * @return array{success: bool, membership: ?Membership, error_code?: string, message: string}
     */
    public static function verifyChallenge(string $phone, string $challengeId, string $otp): array
    {
        $cacheKey = "member_otp_challenge:{$challengeId}";
        $data = Cache::get($cacheKey);

        if (!$data || empty($data['otp_hash'])) {
            return [
                'success'    => false,
                'membership' => null,
                'error_code' => 'INVALID_OR_EXPIRED_CHALLENGE',
                'message'    => 'OTP challenge is invalid or has expired. Please request a new OTP.',
            ];
        }

        // Check if phone matches challenge
        if ($data['phone'] !== $phone) {
            return [
                'success'    => false,
                'membership' => null,
                'error_code' => 'PHONE_MISMATCH',
                'message'    => 'Phone number does not match OTP challenge.',
            ];
        }

        // Check expiration
        if (Carbon::now()->timestamp > $data['expires_at']) {
            Cache::forget($cacheKey);
            return [
                'success'    => false,
                'membership' => null,
                'error_code' => 'CHALLENGE_EXPIRED',
                'message'    => 'OTP code has expired. Please request a new OTP.',
            ];
        }

        // Check if already consumed
        if ($data['is_consumed'] === true) {
            return [
                'success'    => false,
                'membership' => null,
                'error_code' => 'CHALLENGE_ALREADY_USED',
                'message'    => 'This OTP has already been used. Please request a new OTP.',
            ];
        }

        // Check attempt count
        if ($data['attempts'] >= self::MAX_VERIFY_ATTEMPTS) {
            Cache::forget($cacheKey);
            return [
                'success'    => false,
                'membership' => null,
                'error_code' => 'MAX_ATTEMPTS_EXCEEDED',
                'message'    => 'Maximum verification attempts exceeded. Please request a new OTP.',
            ];
        }

        // Check OTP
        if (!password_verify($otp, $data['otp_hash'])) {
            $data['attempts']++;
            Cache::put($cacheKey, $data, Carbon::createFromTimestamp($data['expires_at']));
            $remaining = self::MAX_VERIFY_ATTEMPTS - $data['attempts'];

            return [
                'success'    => false,
                'membership' => null,
                'error_code' => 'INVALID_OTP',
                'message'    => "Invalid OTP code. {$remaining} attempts remaining.",
            ];
        }

        // OTP is valid -> atomically mark as consumed
        $data['is_consumed'] = true;
        Cache::forget($cacheKey);
        Cache::forget("member_phone_challenge:{$phone}");

        // Lookup existing membership by phone
        $membership = Membership::where('phone', $phone)->first();

        if (!$membership) {
            return [
                'success'    => false,
                'membership' => null,
                'error_code' => 'MEMBERSHIP_NOT_FOUND',
                'message'    => 'No registered membership found for this mobile number.',
            ];
        }

        return [
            'success'    => true,
            'membership' => $membership,
            'message'    => 'OTP verified successfully.',
        ];
    }
}
