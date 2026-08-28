<?php

namespace App\Services;

use Carbon\Carbon;
use InvalidArgumentException;

class RudrasenaEligibilityService
{
    public const MIN_AGE = 24;
    public const MAX_AGE = 44;

    /**
     * Calculate exact calendar-aware age from Date of Birth against an asOfDate.
     *
     * @param string|\DateTimeInterface $dob
     * @param Carbon|null $asOfDate
     * @return int
     */
    public static function calculateAge($dob, ?Carbon $asOfDate = null): int
    {
        if (empty($dob)) {
            throw new InvalidArgumentException('Date of birth is required to calculate age.');
        }

        $now = $asOfDate ?? Carbon::now();
        $dobCarbon = $dob instanceof Carbon ? $dob->copy() : Carbon::parse($dob);

        if ($dobCarbon->isAfter($now)) {
            throw new InvalidArgumentException('Date of birth cannot be in the future.');
        }

        if ($asOfDate !== null) {
            return (int) $dobCarbon->diffInYears($asOfDate, false);
        }

        return $dobCarbon->age;
    }

    /**
     * Check if the age meets the strict Rudrasena eligibility criteria (24 to 44 years inclusive).
     *
     * @param string|\DateTimeInterface $dob
     * @param Carbon|null $asOfDate
     * @return bool
     */
    public static function isAgeEligible($dob, ?Carbon $asOfDate = null): bool
    {
        try {
            $age = self::calculateAge($dob, $asOfDate);
            return $age >= self::MIN_AGE && $age <= self::MAX_AGE;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Get the standardized validation failure message.
     *
     * @param int|null $age
     * @return string
     */
    public static function validationMessage(?int $age = null): string
    {
        if ($age !== null) {
            return "Rudrasena eligibility is limited to persons aged " . self::MIN_AGE . " to " . self::MAX_AGE . " years. Your current calculated age is {$age}.";
        }

        return "Rudrasena eligibility is limited to persons aged " . self::MIN_AGE . " to " . self::MAX_AGE . " years.";
    }
}
