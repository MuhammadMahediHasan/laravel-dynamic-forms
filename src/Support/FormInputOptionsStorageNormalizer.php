<?php

namespace MuhammadMahediHasan\Df\Support;

class FormInputOptionsStorageNormalizer
{
    /**
     * Normalize multi-lingual option lists for DB persistence.
     */
    public function normalize(mixed $options, string $type): ?array
    {
        $optionFieldTypes = config('dynamic-forms.option_field_types', ['select', 'radio', 'checkbox', 'multiSelect']);

        if (! in_array($type, $optionFieldTypes, true)) {
            return null;
        }

        $locales = config('dynamic-forms.locales', ['en']);
        $decoded = JsonArrayDecoder::decode($options);

        $result = [];
        foreach ($locales as $locale) {
            $result[$locale] = (is_array($decoded) && is_array($decoded[$locale] ?? null))
                ? array_values($decoded[$locale])
                : [];
        }

        return $result;
    }
}
