import { usePage } from '@inertiajs/vue3';

export interface FormOptionItem {
    label: string;
    value: string;
    isSelected?: boolean;
}

export type OptionLists = Record<string, FormOptionItem[]>;
export type TextOptions = Record<string, string>;

// Keep the old names for backwards compatibility
export type BilingualOptionLists = OptionLists;
export type BilingualTextOptions = TextOptions;
export type FormElementOptions = OptionLists | null;

export function getConfigLocales(): string[] {
    try {
        const page = usePage();
        const locales = page?.props?.dynamic_forms_locales as string[];
        if (Array.isArray(locales) && locales.length > 0) {
            if (!locales.includes('en')) {
                return ['en', ...locales];
            }
            return locales;
        }
    } catch (e) {
        // usePage might throw outside Inertia context (e.g. unit tests, preview mode, etc.)
    }
    return ['en'];
}

export function emptyOptionLists(locales = getConfigLocales()): OptionLists {
    const lists: OptionLists = {};
    locales.forEach((loc) => {
        lists[loc] = [];
    });
    return lists;
}

export const OPTION_FIELD_TYPES = [
    'select',
    'multiSelect',
    'radio',
    'checkbox',
] as const;

export function hasOptionsFieldType(type: string): boolean {
    return OPTION_FIELD_TYPES.includes(type as (typeof OPTION_FIELD_TYPES)[number]);
}

export function slugifyOptionValue(label: string): string {
    return label.trim().replace(/\s+/g, '_');
}

export function createDefaultOptionLists(count = 3, locales = getConfigLocales()): OptionLists {
    const lists: OptionLists = {};
    locales.forEach((loc) => {
        lists[loc] = [];
    });

    for (let i = 1; i <= count; i++) {
        const label = `Option ${i}`;
        const value = slugifyOptionValue(label);
        locales.forEach((loc) => {
            lists[loc].push({ label, value });
        });
    }

    return lists;
}

export function parseOptionItems(value: unknown): FormOptionItem[] {
    if (!Array.isArray(value)) {
        return [];
    }

    return value
        .map((item) => {
            if (!item || typeof item !== 'object') {
                return null;
            }

            const record = item as Record<string, unknown>;
            const label = String(record.label ?? record.value ?? '').trim();
            const optionValue = String(record.value ?? record.label ?? '').trim();

            if (!label && !optionValue) {
                return null;
            }

            return {
                label: label || optionValue,
                value: optionValue || label,
                ...(typeof record.isSelected === 'boolean'
                    ? { isSelected: record.isSelected }
                    : {}),
            };
        })
        .filter((item): item is FormOptionItem => item !== null);
}

function syncEnglishOptionValues(items: FormOptionItem[]): FormOptionItem[] {
    return items.map((item) => ({
        ...item,
        value: item.label.trim() ? slugifyOptionValue(item.label) : '',
    }));
}

function extractOptionLabels(value: unknown): string[] {
    if (!Array.isArray(value)) {
        return [];
    }

    return value.map((item) => {
        if (item && typeof item === 'object') {
            return String((item as Record<string, unknown>).label ?? '').trim();
        }

        return '';
    });
}

export function syncOptionLists(
    lists: OptionLists,
    locales = getConfigLocales()
): OptionLists {
    const primaryLoc = locales[0] || 'en';
    const primaryItems = lists[primaryLoc] || [];
    
    const syncedPrimary = syncEnglishOptionValues(primaryItems);

    const result: OptionLists = {};
    result[primaryLoc] = syncedPrimary;

    locales.forEach((loc) => {
        if (loc === primaryLoc) return;
        const currentItems = lists[loc] || [];
        result[loc] = syncedPrimary.map((primItem, index) => ({
            label: currentItems[index]?.label ?? '',
            value: primItem.value,
        }));
    });

    return result;
}

// Keep old signature alias for compatibility
export function syncBilingualOptionLists(
    en: FormOptionItem[],
    bn: FormOptionItem[],
): BilingualOptionLists {
    return syncOptionLists({ en, bn }, ['en']);
}

export function normalizeElementOptions(
    options: unknown,
    type: string,
    locales = getConfigLocales()
): FormElementOptions {
    if (!hasOptionsFieldType(type)) {
        return null;
    }

    const record =
        options && typeof options === 'object'
            ? (options as Record<string, unknown>)
            : {};

    const primaryLoc = locales[0] || 'en';
    let primaryItems = parseOptionItems(record[primaryLoc]);

    if (primaryItems.length === 0) {
        primaryItems = [{ label: '', value: '' }];
    }

    const lists: OptionLists = {};
    lists[primaryLoc] = primaryItems;

    locales.forEach((loc) => {
        if (loc === primaryLoc) return;
        const labels = extractOptionLabels(record[loc]);
        lists[loc] = primaryItems.map((_, index) => ({
            label: labels[index] ?? '',
            value: '',
        }));
    });

    return syncOptionLists(lists, locales);
}

export function serializeOptionsForStorage(
    options: unknown,
    type: string,
    locales = getConfigLocales()
): FormElementOptions {
    if (!hasOptionsFieldType(type)) {
        return null;
    }

    const lists = getOptionLists(options, locales);
    const primaryLoc = locales[0] || 'en';
    const primaryItems = lists[primaryLoc] || [];

    const filteredPrimary = primaryItems.filter(
        (item) => item.label.trim() !== '' || item.value.trim() !== '',
    );

    if (filteredPrimary.length === 0) {
        return emptyOptionLists(locales);
    }

    const result: OptionLists = {};
    result[primaryLoc] = filteredPrimary;

    locales.forEach((loc) => {
        if (loc === primaryLoc) return;
        const currentItems = lists[loc] || [];
        result[loc] = filteredPrimary.map((item, index) => {
            const currentItem = currentItems[index];

            return {
                label: currentItem?.label ?? '',
                value: item.value,
                ...(typeof item.isSelected === 'boolean'
                    ? { isSelected: item.isSelected }
                    : {}),
            };
        });
    });

    return syncOptionLists(result, locales);
}

export function getPrimaryOptionValues(options: unknown, locales = getConfigLocales()): string[] {
    const primaryLoc = locales[0] || 'en';
    return getOptionLists(options, locales)[primaryLoc]
        .map((item) => item.value.trim())
        .filter(Boolean);
}

// Keep old name for compatibility
export const getEnglishOptionValues = getPrimaryOptionValues;

export function getOptionLists(options: unknown, locales = getConfigLocales()): OptionLists {
    const record =
        options && typeof options === 'object'
            ? (options as Record<string, unknown>)
            : {};

    const primaryLoc = locales[0] || 'en';
    let primaryItems = parseOptionItems(record[primaryLoc]);

    if (primaryItems.length === 0) {
        primaryItems = [{ label: '', value: '' }];
    }

    const lists: OptionLists = {};
    lists[primaryLoc] = primaryItems;

    locales.forEach((loc) => {
        if (loc === primaryLoc) return;
        const labels = extractOptionLabels(record[loc]);
        lists[loc] = primaryItems.map((_, index) => ({
            label: labels[index] ?? '',
            value: '',
        }));
    });

    return syncOptionLists(lists, locales);
}

export function getLocaleOptionItems(
    options: unknown,
    lang: string,
): FormOptionItem[] {
    if (Array.isArray(options)) {
        return parseOptionItems(options);
    }

    if (options && typeof options === 'object') {
        return parseOptionItems(
            (options as Record<string, unknown>)[lang],
        );
    }

    return [];
}

export function resolveOptionLabelByValue(
    options: unknown,
    selectedValue: string | null | undefined,
    lang: string,
): string | null {
    const value = selectedValue?.trim();

    if (!value) {
        return null;
    }

    const match = getLocaleOptionItems(options, lang).find(
        (option) => option.value === value || option.label === value,
    );

    return match?.label ?? null;
}

export function resolveOptionLabelsByValue(
    options: unknown,
    selectedValue: string | null | undefined,
    lang: string,
): string[] {
    const value = selectedValue?.trim();

    if (!value) {
        return [];
    }

    return value
        .split(',')
        .map((part) => part.trim())
        .filter(Boolean)
        .map(
            (part) =>
                resolveOptionLabelByValue(options, part, lang) ?? part,
        );
}
