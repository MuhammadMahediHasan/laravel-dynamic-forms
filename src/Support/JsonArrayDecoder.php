<?php

namespace MuhammadMahediHasan\Df\Support;

final class JsonArrayDecoder
{
    /**
     * Decode a JSON string to an array, or return arrays as-is.
     */
    public static function decode(mixed $value): ?array
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || $value === '') {
            return null;
        }

        $decoded = json_decode($value, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            return null;
        }

        return $decoded;
    }

    /**
     * Whether a string appears to be JSON.
     */
    public static function looksLikeJsonString(string $value): bool
    {
        $trimmed = trim($value);

        return $trimmed !== '' && (
            str_starts_with($trimmed, '{') || str_starts_with($trimmed, '[')
        );
    }

    private static ?array $cachedLocales = null;

    public static function hasBilingualKeys(array $value): bool
    {
        if (self::$cachedLocales === null) {
            self::$cachedLocales = config('dynamic-forms.locales', ['en']);
        }
        foreach (self::$cachedLocales as $locale) {
            if (array_key_exists($locale, $value)) {
                return true;
            }
        }
        return false;
    }
}
