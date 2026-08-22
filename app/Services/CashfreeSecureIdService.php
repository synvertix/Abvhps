<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Cashfree Secure ID Service
 *
 * Implements Cashfree Verification Suite (Secure ID) integration for Aadhaar
 * verification and strict server-side name matching for ABVHPS membership.
 *
 * SECURITY:
 *  - Secure ID Client ID and Secret Key stay server-side at all times.
 *  - Never log full Aadhaar numbers, OTPs, API keys, or raw personal data.
 *  - Safe failure when unconfigured (never creates fake runtime identity data).
 */
class CashfreeSecureIdService
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $baseUrl;
    protected bool $isConfigured;

    public function __construct()
    {
        $this->clientId     = (string) config('services.cashfree.verify_client_id', '');
        $this->clientSecret = (string) config('services.cashfree.verify_client_secret', '');

        $customBaseUrl = (string) config('services.cashfree.verification_base_url', '');
        if (!empty($customBaseUrl)) {
            $this->baseUrl = rtrim($customBaseUrl, '/');
        } else {
            $env = strtolower((string) config('services.cashfree.environment', 'sandbox'));
            $this->baseUrl = $env === 'production'
                ? 'https://api.cashfree.com/verification'
                : 'https://sandbox.cashfree.com/verification';
        }

        $this->isConfigured = !empty($this->clientId) && !empty($this->clientSecret);
    }

    /**
     * Check if Cashfree Secure ID credentials are configured.
     */
    public function isConfigured(): bool
    {
        return $this->isConfigured;
    }

    /**
     * Normalize a name string for identity-safe strict comparison.
     *
     * Rules:
     *  - Trim leading/trailing whitespace
     *  - Convert to uppercase (multibyte safe)
     *  - Replace punctuation (periods, commas, hyphens, underscores) with space
     *  - Collapse multiple consecutive spaces into a single space
     */
    public static function normalizeName(string $name): string
    {
        $normalized = trim($name);
        if (function_exists('mb_strtoupper')) {
            $normalized = mb_strtoupper($normalized, 'UTF-8');
        } else {
            $normalized = strtoupper($normalized);
        }

        // Replace common punctuation with a single space
        $normalized = preg_replace('/[.,\-_()\/]/', ' ', $normalized);

        // Collapse multiple whitespace characters to a single space
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        return trim($normalized);
    }

    /**
     * Strict and identity-safe server-side name comparison.
     *
     * Compares user-entered name against authoritative Cashfree verified name.
     * Rejects disparate names (e.g. "KONDA REDDY" vs "RAVI KUMAR").
     * Accepts normalized exact equality and token-order permutations.
     */
    public static function compareNames(string $enteredName, string $verifiedName): bool
    {
        $normEntered  = self::normalizeName($enteredName);
        $normVerified = self::normalizeName($verifiedName);

        if (empty($normEntered) || empty($normVerified)) {
            return false;
        }

        // 1. Direct exact normalized match (e.g. "Konda Reddy" vs "KONDA REDDY")
        if ($normEntered === $normVerified) {
            return true;
        }

        // 2. Token order comparison (e.g. "Reddy Konda" vs "Konda Reddy")
        $tokensEntered  = array_filter(explode(' ', $normEntered));
        $tokensVerified = array_filter(explode(' ', $normVerified));

        if (count($tokensEntered) === count($tokensVerified)) {
            $sortedEntered  = $tokensEntered;
            $sortedVerified = $tokensVerified;
            sort($sortedEntered);
            sort($sortedVerified);

            if ($sortedEntered === $sortedVerified) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verify if user has an existing DigiLocker account using Cashfree Secure ID.
     *
     * @param string $verificationId Server-controlled unique reference ID
     * @param string|null $aadhaarNumber Optional 12-digit Aadhaar number
     * @param string|null $mobileNumber Optional mobile number
     * @return array Standardized result array
     */
    public function verifyDigiLockerAccount(
        string $verificationId,
        ?string $aadhaarNumber = null,
        ?string $mobileNumber = null
    ): array {
        if (!$this->isConfigured) {
            Log::warning("CashfreeSecureId: Secure ID credentials not configured in environment. DigiLocker account check skipped for Ref: {$verificationId}.");
            return [
                'success' => false,
                'status'  => 'UNCONFIGURED',
                'data'    => [],
                'message' => 'Cashfree Secure ID credentials are not configured. Please contact the administrator.',
            ];
        }

        try {
            $endpoint = "{$this->baseUrl}/digilocker/verify-account";

            $payload = [
                'verification_id' => $verificationId,
            ];

            if (!empty($aadhaarNumber)) {
                $payload['aadhaar_number'] = $aadhaarNumber;
            } elseif (!empty($mobileNumber)) {
                $payload['mobile_number'] = $mobileNumber;
            }

            Log::info("CashfreeSecureId: Initiating DigiLocker account verification request (Ref: {$verificationId}).");

            $response = Http::withHeaders([
                'x-client-id'     => $this->clientId,
                'x-client-secret' => $this->clientSecret,
                'Content-Type'    => 'application/json',
                'Accept'          => 'application/json',
            ])->timeout(15)->post($endpoint, $payload);

            $statusCode = $response->status();
            $body = $response->json();

            if ($response->successful() && is_array($body)) {
                $status = strtoupper((string) ($body['status'] ?? $body['verification_status'] ?? 'UNKNOWN'));
                $refId  = $body['reference_id'] ?? $body['ref_id'] ?? $body['verification_id'] ?? $verificationId;

                if (in_array($status, ['ACCOUNT_EXISTS', 'ACCOUNT_NOT_FOUND'], true)) {
                    return [
                        'success'      => true,
                        'status'       => $status,
                        'reference_id' => $refId,
                        'data'         => ['status' => $status, 'reference_id' => $refId],
                        'message'      => $body['message'] ?? ($status === 'ACCOUNT_EXISTS' ? 'DigiLocker account exists.' : 'DigiLocker account not found.'),
                    ];
                }

                return [
                    'success'      => false,
                    'status'       => $status,
                    'reference_id' => $refId,
                    'data'         => [],
                    'message'      => $body['message'] ?? $body['error_msg'] ?? 'DigiLocker account verification returned unrecognized status.',
                ];
            }

            $providerMsg = is_array($body) ? ($body['message'] ?? $body['error_msg'] ?? 'Provider API error') : 'Non-JSON response';
            Log::error("CashfreeSecureId: DigiLocker account verification failed with HTTP {$statusCode} (Ref: {$verificationId}).", [
                'response_message' => $providerMsg,
            ]);

            return [
                'success'      => false,
                'status'       => 'GATEWAY_ERROR',
                'reference_id' => null,
                'data'         => [],
                'message'      => $providerMsg,
            ];
        } catch (\Throwable $e) {
            Log::error("CashfreeSecureId: Exception during DigiLocker account verification: " . $e->getMessage());
            return [
                'success'      => false,
                'status'       => 'SERVICE_EXCEPTION',
                'reference_id' => null,
                'data'         => [],
                'message'      => 'Unable to communicate with the DigiLocker verification service. Please try again later.',
            ];
        }
    }

    /**
     * Create DigiLocker verification redirection URL using Cashfree Secure ID.
     *
     * @param string $verificationId Server-controlled unique reference ID
     * @param string $redirectUrl URL to return user to after DigiLocker flow
     * @param string $userFlow User flow ('signin' or 'signup', default 'signup')
     * @return array Standardized result array
     */
    public function createDigiLockerUrl(
        string $verificationId,
        string $redirectUrl,
        string $userFlow = 'signup'
    ): array {
        if (!$this->isConfigured) {
            Log::warning("CashfreeSecureId: Secure ID credentials not configured in environment. DigiLocker URL creation skipped for Ref: {$verificationId}.");
            return [
                'success'         => false,
                'status'          => 'UNCONFIGURED',
                'verification_id' => $verificationId,
                'reference_id'    => null,
                'url'             => null,
                'message'         => 'Cashfree Secure ID credentials are not configured. Please contact the administrator.',
            ];
        }

        try {
            $endpoint = "{$this->baseUrl}/digilocker";
            $flow     = in_array(strtolower($userFlow), ['signin', 'signup'], true) ? strtolower($userFlow) : 'signup';

            $payload = [
                'verification_id'    => $verificationId,
                'document_requested' => ['AADHAAR'],
                'redirect_url'       => $redirectUrl,
                'user_flow'          => $flow,
            ];

            Log::info("CashfreeSecureId: Requesting DigiLocker verification URL (Ref: {$verificationId}, Flow: {$flow}).");

            $response = Http::withHeaders([
                'x-client-id'     => $this->clientId,
                'x-client-secret' => $this->clientSecret,
                'Content-Type'    => 'application/json',
                'Accept'          => 'application/json',
            ])->timeout(15)->post($endpoint, $payload);

            $statusCode = $response->status();
            $body       = $response->json();

            if ($response->successful() && is_array($body)) {
                $status  = strtoupper((string) ($body['status'] ?? $body['verification_status'] ?? 'UNKNOWN'));
                $refId   = $body['reference_id'] ?? $body['ref_id'] ?? null;
                $verifId = $body['verification_id'] ?? $verificationId;
                $url     = (string) ($body['url'] ?? $body['redirect_url'] ?? $body['link'] ?? '');

                if ($status === 'PENDING' && !empty($url)) {
                    Log::info("CashfreeSecureId: Successfully generated DigiLocker URL (Ref: {$verifId}).");
                    return [
                        'success'         => true,
                        'status'          => 'PENDING',
                        'verification_id' => $verifId,
                        'reference_id'    => $refId,
                        'url'             => $url,
                        'message'         => 'DigiLocker verification URL created successfully.',
                    ];
                }
            }

            $providerMsg = is_array($body) ? ($body['message'] ?? $body['error_msg'] ?? 'DigiLocker URL creation failed or missing URL.') : 'Non-JSON response';
            Log::error("CashfreeSecureId: DigiLocker URL creation failed with HTTP {$statusCode} (Ref: {$verificationId}).", [
                'response_message' => $providerMsg,
            ]);

            return [
                'success'         => false,
                'status'          => 'GATEWAY_ERROR',
                'verification_id' => $verificationId,
                'reference_id'    => null,
                'url'             => null,
                'message'         => $providerMsg,
            ];
        } catch (\Throwable $e) {
            Log::error("CashfreeSecureId: Exception during DigiLocker URL creation: " . $e->getMessage());
            return [
                'success'         => false,
                'status'          => 'SERVICE_EXCEPTION',
                'verification_id' => $verificationId,
                'reference_id'    => null,
                'url'             => null,
                'message'         => 'Unable to generate DigiLocker verification link. Please try again later.',
            ];
        }
    }

    /**
     * Get DigiLocker verification session status using Cashfree Secure ID.
     *
     * @param string $verificationId Server-controlled unique reference ID
     * @param int|string|null $referenceId Optional gateway reference ID
     * @return array Standardized result array
     */
    public function getDigiLockerStatus(
        string $verificationId,
        int|string|null $referenceId = null
    ): array {
        if (!$this->isConfigured) {
            Log::warning("CashfreeSecureId: Secure ID credentials not configured. DigiLocker status check skipped for Ref: {$verificationId}.");
            return [
                'success'          => false,
                'status'           => 'UNCONFIGURED',
                'verification_id'  => $verificationId,
                'reference_id'     => $referenceId,
                'user_details'     => [],
                'document_consent' => [],
                'message'          => 'Cashfree Secure ID credentials are not configured. Please contact the administrator.',
            ];
        }

        try {
            $endpoint = "{$this->baseUrl}/digilocker";

            $queryParams = [
                'verification_id' => $verificationId,
            ];
            if (!empty($referenceId)) {
                $queryParams['reference_id'] = (string) $referenceId;
            }

            Log::info("CashfreeSecureId: Checking DigiLocker session status (Ref: {$verificationId}).");

            $response = Http::withHeaders([
                'x-client-id'     => $this->clientId,
                'x-client-secret' => $this->clientSecret,
                'Content-Type'    => 'application/json',
                'Accept'          => 'application/json',
            ])->timeout(15)->get($endpoint, $queryParams);

            $statusCode = $response->status();
            $body       = $response->json();

            if ($response->successful() && is_array($body)) {
                $status          = strtoupper((string) ($body['status'] ?? $body['verification_status'] ?? 'UNKNOWN'));
                $refId           = $body['reference_id'] ?? $body['ref_id'] ?? $referenceId;
                $verifId         = $body['verification_id'] ?? $verificationId;
                $rawUserDetails  = is_array($body['user_details'] ?? null) ? $body['user_details'] : [];
                $documentConsent = is_array($body['document_consent'] ?? null) ? $body['document_consent'] : [];

                // Sanitize user_details to ONLY safe fields: name, dob, gender
                $sanitizedUserDetails = array_filter([
                    'name'   => $rawUserDetails['name'] ?? $rawUserDetails['user_name'] ?? null,
                    'dob'    => $rawUserDetails['dob'] ?? $rawUserDetails['date_of_birth'] ?? null,
                    'gender' => $rawUserDetails['gender'] ?? null,
                ]);

                // Documented HTTP-200 statuses: PENDING, AUTHENTICATED, EXPIRED, CONSENT_DENIED
                if (in_array($status, ['PENDING', 'AUTHENTICATED', 'EXPIRED', 'CONSENT_DENIED'], true)) {
                    return [
                        'success'          => true,
                        'status'           => $status,
                        'verification_id'  => $verifId,
                        'reference_id'     => $refId,
                        'user_details'     => $sanitizedUserDetails,
                        'document_consent' => $documentConsent,
                        'message'          => $body['message'] ?? "DigiLocker status is {$status}.",
                    ];
                }

                return [
                    'success'          => false,
                    'status'           => $status,
                    'verification_id'  => $verifId,
                    'reference_id'     => $refId,
                    'user_details'     => [],
                    'document_consent' => [],
                    'message'          => $body['message'] ?? $body['error_msg'] ?? 'DigiLocker status returned unrecognized status.',
                ];
            }

            $providerMsg = is_array($body) ? ($body['message'] ?? $body['error_msg'] ?? 'Provider API error') : 'Non-JSON response';
            Log::error("CashfreeSecureId: DigiLocker status request failed with HTTP {$statusCode} (Ref: {$verificationId}).", [
                'response_message' => $providerMsg,
            ]);

            return [
                'success'          => false,
                'status'           => 'GATEWAY_ERROR',
                'verification_id'  => $verificationId,
                'reference_id'     => $referenceId,
                'user_details'     => [],
                'document_consent' => [],
                'message'          => $providerMsg,
            ];
        } catch (\Throwable $e) {
            Log::error("CashfreeSecureId: Exception during DigiLocker status check: " . $e->getMessage());
            return [
                'success'          => false,
                'status'           => 'SERVICE_EXCEPTION',
                'verification_id'  => $verificationId,
                'reference_id'     => $referenceId,
                'user_details'     => [],
                'document_consent' => [],
                'message'          => 'Unable to communicate with the DigiLocker verification service. Please try again later.',
            ];
        }
    }

    /**
     * Fetch verified Aadhaar document data from DigiLocker session using Cashfree Secure ID.
     *
     * @param string $verificationId Server-controlled unique reference ID
     * @param int|string|null $referenceId Optional gateway reference ID
     * @return array Standardized result array with sanitized identity fields ONLY
     */
    public function getDigiLockerAadhaarDocument(
        string $verificationId,
        int|string|null $referenceId = null
    ): array {
        if (!$this->isConfigured) {
            Log::warning("CashfreeSecureId: Secure ID credentials not configured. DigiLocker Aadhaar document fetch skipped for Ref: {$verificationId}.");
            return [
                'success'         => false,
                'status'          => 'UNCONFIGURED',
                'verification_id' => $verificationId,
                'reference_id'    => $referenceId,
                'verified_name'   => null,
                'data'            => [],
                'message'         => 'Cashfree Secure ID credentials are not configured. Please contact the administrator.',
            ];
        }

        try {
            $endpoint = "{$this->baseUrl}/digilocker/document/AADHAAR";

            $queryParams = [
                'verification_id' => $verificationId,
            ];
            if (!empty($referenceId)) {
                $queryParams['reference_id'] = (string) $referenceId;
            }

            Log::info("CashfreeSecureId: Requesting DigiLocker Aadhaar document (Ref: {$verificationId}).");

            $response = Http::withHeaders([
                'x-client-id'     => $this->clientId,
                'x-client-secret' => $this->clientSecret,
                'Content-Type'    => 'application/json',
                'Accept'          => 'application/json',
            ])->timeout(15)->get($endpoint, $queryParams);

            $statusCode = $response->status();
            $body       = $response->json();

            if ($response->successful() && is_array($body)) {
                $status  = strtoupper((string) ($body['status'] ?? $body['verification_status'] ?? 'UNKNOWN'));
                $refId   = $body['reference_id'] ?? $body['ref_id'] ?? $referenceId;
                $verifId = $body['verification_id'] ?? $verificationId;

                if ($status === 'AADHAAR_NOT_LINKED') {
                    Log::warning("CashfreeSecureId: Aadhaar is not linked to DigiLocker account (Ref: {$verifId}).");
                    return [
                        'success'         => false,
                        'status'          => 'AADHAAR_NOT_LINKED',
                        'verification_id' => $verifId,
                        'reference_id'    => $refId,
                        'verified_name'   => null,
                        'data'            => [],
                        'message'         => 'Aadhaar document is not linked to this DigiLocker account.',
                    ];
                }

                if ($status === 'SUCCESS') {
                    $extracted = $this->extractIdentityData($body);

                    // Filter sanitized identity fields ONLY — exclude photo_link, xml_file, UIDs, or raw tokens
                    $sanitizedData = array_filter([
                        'name'                   => $extracted['name'] ?? null,
                        'dob'                    => $extracted['dob'] ?? null,
                        'gender'                 => $extracted['gender'] ?? null,
                        'care_of'                => $extracted['care_of'] ?? null,
                        'father_or_husband_name' => $extracted['father_or_husband_name'] ?? null,
                        'address'                => $extracted['address'] ?? null,
                        'permanent_address'      => $extracted['permanent_address'] ?? null,
                        'pincode'                => $extracted['pincode'] ?? null,
                        'district'               => $extracted['district'] ?? null,
                        'state'                  => $extracted['state'] ?? null,
                    ], fn($v) => !is_null($v));

                    Log::info("CashfreeSecureId: Successfully retrieved verified DigiLocker Aadhaar document (Ref: {$verifId}).");

                    return [
                        'success'         => true,
                        'status'          => 'SUCCESS',
                        'verification_id' => $verifId,
                        'reference_id'    => $refId,
                        'verified_name'   => $sanitizedData['name'] ?? null,
                        'data'            => $sanitizedData,
                        'message'         => 'Aadhaar document retrieved and verified via DigiLocker successfully.',
                    ];
                }

                return [
                    'success'         => false,
                    'status'          => $status,
                    'verification_id' => $verifId,
                    'reference_id'    => $refId,
                    'verified_name'   => null,
                    'data'            => [],
                    'message'         => $body['message'] ?? $body['error_msg'] ?? 'DigiLocker Aadhaar document fetch was not successful.',
                ];
            }

            $providerMsg = is_array($body) ? ($body['message'] ?? $body['error_msg'] ?? 'Provider API error') : 'Non-JSON response';
            Log::error("CashfreeSecureId: DigiLocker Aadhaar document fetch failed with HTTP {$statusCode} (Ref: {$verificationId}).", [
                'response_message' => $providerMsg,
            ]);

            return [
                'success'         => false,
                'status'          => 'GATEWAY_ERROR',
                'verification_id' => $verificationId,
                'reference_id'    => $referenceId,
                'verified_name'   => null,
                'data'            => [],
                'message'         => $providerMsg,
            ];
        } catch (\Throwable $e) {
            Log::error("CashfreeSecureId: Exception during DigiLocker Aadhaar document fetch: " . $e->getMessage());
            return [
                'success'         => false,
                'status'          => 'SERVICE_EXCEPTION',
                'verification_id' => $verificationId,
                'reference_id'    => $referenceId,
                'verified_name'   => null,
                'data'            => [],
                'message'         => 'Unable to retrieve Aadhaar document from DigiLocker service. Please try again later.',
            ];
        }
    }

    /**
     * Verify Aadhaar via Cashfree Secure ID Verification Suite.
     *
     * In live/sandbox environments, communicates with Cashfree Verification API.
     * If unconfigured, fails safely without producing mock identity data.
     *
     * @param string $aadhaarNumber 12-digit Aadhaar number
     * @param string $verificationId Server-controlled unique reference ID
     * @param string|null $enteredName User-entered name for optional gateway-assisted match
     * @return array Standardized result array
     */
    public function verifyAadhaar(string $aadhaarNumber, string $verificationId, ?string $enteredName = null): array
    {
        $maskedAadhaar = 'XXXX-XXXX-' . substr($aadhaarNumber, -4);

        if (!$this->isConfigured) {
            Log::warning("CashfreeSecureId: Secure ID credentials not configured in environment. Verification skipped for {$maskedAadhaar}.");
            return [
                'success' => false,
                'status'  => 'UNCONFIGURED',
                'message' => 'Cashfree Secure ID credentials are not configured. Please contact the administrator.',
            ];
        }

        try {
            // Cashfree Verification Suite: Offline Aadhaar / DigiLocker Verification Endpoint
            $endpoint = "{$this->baseUrl}/offline-aadhaar/verify";

            $payload = [
                'aadhaar_number'  => $aadhaarNumber,
                'verification_id' => $verificationId,
            ];

            if (!empty($enteredName)) {
                $payload['name'] = $enteredName;
            }

            Log::info("CashfreeSecureId: Initiating Aadhaar verification request for {$maskedAadhaar} (Ref: {$verificationId}).");

            $response = Http::withHeaders([
                'x-client-id'     => $this->clientId,
                'x-client-secret' => $this->clientSecret,
                'Content-Type'    => 'application/json',
                'Accept'          => 'application/json',
            ])->timeout(15)->post($endpoint, $payload);

            $statusCode = $response->status();
            $body = $response->json();

            if ($response->successful() && is_array($body)) {
                $status = strtoupper((string) ($body['status'] ?? $body['verification_status'] ?? 'SUCCESS'));

                if (in_array($status, ['SUCCESS', 'VALID', 'AUTHENTICATED', 'COMPLETED'], true)) {
                    $extracted = $this->extractIdentityData($body);
                    Log::info("CashfreeSecureId: Aadhaar verification successfully completed by gateway for {$maskedAadhaar}.");

                    return [
                        'success'         => true,
                        'status'          => $status,
                        'ref_id'          => $body['ref_id'] ?? $body['verification_id'] ?? $verificationId,
                        'verified_name'   => $extracted['name'],
                        'data'            => $extracted,
                        'message'         => 'Aadhaar identity verified successfully by Cashfree Secure ID.',
                    ];
                }

                Log::warning("CashfreeSecureId: Gateway returned non-successful verification status [{$status}] for {$maskedAadhaar}.");
                return [
                    'success' => false,
                    'status'  => $status,
                    'message' => $body['message'] ?? 'Aadhaar verification was not confirmed by the verification provider.',
                ];
            }

            Log::error("CashfreeSecureId: Gateway returned HTTP {$statusCode} for {$maskedAadhaar}.", [
                'response_message' => is_array($body) ? ($body['message'] ?? 'Unknown error') : 'Non-JSON response',
            ]);

            return [
                'success' => false,
                'status'  => 'GATEWAY_ERROR',
                'message' => is_array($body) && !empty($body['message'])
                    ? $body['message']
                    : 'Aadhaar verification failed. Please check the Aadhaar number and try again.',
            ];
        } catch (\Throwable $e) {
            Log::error("CashfreeSecureId: Network / service exception during Aadhaar verification for {$maskedAadhaar}: " . $e->getMessage());
            return [
                'success' => false,
                'status'  => 'SERVICE_EXCEPTION',
                'message' => 'Unable to communicate with the Aadhaar verification service. Please try again later.',
            ];
        }
    }

    /**
     * Standardize and extract identity fields from Cashfree verification response.
     */
    protected function extractIdentityData(array $response): array
    {
        // Check root or nested data/document blocks
        $source = $response['data'] ?? $response['document'] ?? $response;

        $name = (string) ($source['name'] ?? $source['full_name'] ?? $source['user_name'] ?? '');
        $dob  = (string) ($source['dob'] ?? $source['date_of_birth'] ?? '');

        $gender = (string) ($source['gender'] ?? '');
        if (!empty($gender)) {
            $upperGender = strtoupper(trim($gender));
            if (in_array($upperGender, ['M', 'MALE'], true)) {
                $gender = 'Male';
            } elseif (in_array($upperGender, ['F', 'FEMALE'], true)) {
                $gender = 'Female';
            } elseif (in_array($upperGender, ['O', 'OTHER'], true)) {
                $gender = 'Other';
            }
        }

        $careOf  = (string) ($source['care_of'] ?? $source['father_name'] ?? $source['father_or_husband_name'] ?? '');
        $address = (string) ($source['address'] ?? $source['permanent_address'] ?? '');

        $split = $source['split_address'] ?? [];
        $pincode  = (string) ($split['pincode'] ?? $source['pincode'] ?? $source['zip'] ?? '');
        $district = (string) ($split['district'] ?? $split['dist'] ?? $source['district'] ?? '');
        $state    = (string) ($split['state'] ?? $source['state'] ?? '');

        return [
            'name'                   => !empty($name) ? self::normalizeName($name) : null,
            'dob'                    => !empty($dob) ? $dob : null,
            'gender'                 => !empty($gender) ? $gender : null,
            'care_of'                => !empty($careOf) ? $careOf : null,
            'father_or_husband_name' => !empty($careOf) ? $careOf : null,
            'address'                => !empty($address) ? $address : null,
            'permanent_address'      => !empty($address) ? $address : null,
            'pincode'                => !empty($pincode) ? $pincode : null,
            'district'               => !empty($district) ? $district : null,
            'state'                  => !empty($state) ? $state : null,
            'split_address'          => [
                'pincode'  => !empty($pincode) ? $pincode : null,
                'district' => !empty($district) ? $district : null,
                'state'    => !empty($state) ? $state : null,
            ],
        ];
    }
}
