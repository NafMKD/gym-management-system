<?php

namespace App\Support;

/**
 * Ethiopian local mobile: 10 characters, leading 0, then 07 or 09 and 8 more digits.
 */
class PhoneNumber
{
    public const PATTERN = '^(07|09)\d{8}$';

    /** Laravel `regex:` validation (delimited). */
    public const REGEX_VALIDATION = '/^(07|09)\d{8}$/';

    public static function isValid(?string $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        return (bool) preg_match('/'.self::PATTERN.'/', $value);
    }

    /**
     * Normalize user input: trim, strip non-digits, return valid local format or null.
     */
    public static function normalize(?string $input): ?string
    {
        if ($input === null || $input === '') {
            return null;
        }

        $digits = preg_replace('/\D/', '', $input);
        if ($digits === '') {
            return null;
        }

        if (strlen($digits) === 10 && preg_match('/'.self::PATTERN.'/', $digits)) {
            return $digits;
        }

        if (strlen($digits) === 9 && ($digits[0] === '7' || $digits[0] === '9')) {
            return '0'.$digits;
        }

        // Legacy: 10 digits starting with 9 but not 09 (e.g. national digits without leading 0)
        if (strlen($digits) === 10 && $digits[0] === '9' && $digits[1] !== '0') {
            return '0'.substr($digits, 0, 9);
        }

        return null;
    }

    /**
     * Convert legacy DB value (historically unsigned bigint) to VARCHAR(10) local format.
     */
    public static function fromLegacyDatabaseValue(int|string|null $raw): string
    {
        if ($raw === null || $raw === '') {
            throw new \InvalidArgumentException('Cannot normalize empty phone.');
        }

        $digits = preg_replace('/\D/', '', (string) $raw);

        $normalized = self::normalize($digits);
        if ($normalized !== null && self::isValid($normalized)) {
            return $normalized;
        }

        throw new \InvalidArgumentException('Cannot normalize phone value: '.$raw);
    }

    /**
     * Deterministic placeholder for migration when legacy value cannot be parsed (should be rare).
     */
    public static function placeholderForUserId(int $id): string
    {
        $n = $id % 100_000_000;

        return '07'.str_pad((string) $n, 8, '0', STR_PAD_LEFT);
    }

    /**
     * Validation rules for Laravel: local mobile, unique on users.phone.
     *
     * @return array<int, mixed>
     */
    public static function rules(bool $required = true, ?int $ignoreUserId = null): array
    {
        $unique = \Illuminate\Validation\Rule::unique('users', 'phone');
        if ($ignoreUserId !== null) {
            $unique = $unique->ignore($ignoreUserId);
        }

        $base = ['string', 'max:10', 'regex:'.self::REGEX_VALIDATION, $unique];

        return $required ? array_merge(['required'], $base) : array_merge(['nullable'], $base);
    }

    /**
     * Optional email rules when nullable unique.
     *
     * @return array<int, mixed>
     */
    public static function optionalEmailRules(?int $ignoreUserId = null): array
    {
        $unique = \Illuminate\Validation\Rule::unique('users', 'email');
        if ($ignoreUserId !== null) {
            $unique = $unique->ignore($ignoreUserId);
        }

        return ['nullable', 'string', 'lowercase', 'email', 'max:255', $unique];
    }
}
