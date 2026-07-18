<?php

namespace MuhammadMahediHasan\Df\Support;

class BilingualFieldParser
{
    /**
     * Parse bilingual field to standardized array format.
     */
    public function parse(mixed $field, string $fallback = ''): array
    {
        $decoded = JsonArrayDecoder::decode($field);
        $locales = config('dynamic-forms.locales', ['en']);

        if ($decoded !== null && JsonArrayDecoder::hasBilingualKeys($decoded)) {
            return $decoded;
        }

        if (is_string($field)) {
            $result = [];
            foreach ($locales as $locale) {
                $result[$locale] = $field ?: $fallback;
            }
            return $result;
        }

        if (is_array($field)) {
            return $field;
        }

        $result = [];
        foreach ($locales as $locale) {
            $result[$locale] = $fallback;
        }
        return $result;
    }
}
