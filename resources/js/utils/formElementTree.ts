import {
    createDefaultOptionLists,
    getConfigLocales,
    hasOptionsFieldType,
    normalizeElementOptions,
    serializeOptionsForStorage,
} from './formOptions';
import type {
    AvailableElement,
    FlatFormElementPayload,
    FormElement,
    LocalizedText,
} from '../types/formBuilder';

export function generateElementId(): string {
    return Math.random().toString(36).substring(2, 11);
}

export function reconstructElementTree(
    flatElements: FormElement[],
): FormElement[] {
    const elementMap = new Map<string | number, FormElement>();
    const rootElements: FormElement[] = [];

    flatElements.forEach((element) => {
        elementMap.set(element.id, { ...element, children: [] });
    });

    flatElements.forEach((element) => {
        const parentId = element.parent_id;

        if (parentId) {
            const parent = elementMap.get(parentId);
            if (parent?.children) {
                parent.children.push(elementMap.get(element.id)!);
            }
        } else {
            rootElements.push(elementMap.get(element.id)!);
        }
    });

    return rootElements;
}

function normalizeTranslatable(val: any): LocalizedText {
    const locales = getConfigLocales();
    const result: LocalizedText = {
        en: '',
        bn: '',
    };

    locales.forEach((loc) => {
        result[loc] = '';
    });

    if (val && typeof val === 'object') {
        locales.forEach((loc) => {
            if (val[loc] !== undefined && val[loc] !== null) {
                result[loc] = String(val[loc]);
            }
        });
    } else if (typeof val === 'string' && val.trim() !== '') {
        const primaryLoc = locales[0] || 'en';
        result[primaryLoc] = val;
    }

    return result;
}

export function normalizeFormElement(element: FormElement): FormElement {
    const options = normalizeElementOptions(element.options, element.type);

    return {
        ...element,
        label: normalizeTranslatable(element.label),
        placeholder: normalizeTranslatable(element.placeholder),
        hints: normalizeTranslatable(element.hints),
        options,
        correct_answer: element.correct_answer ?? [],
        children: element.children?.map(normalizeFormElement),
    };
}

export function initializeFormElements(
    elements: FormElement[] | undefined,
): FormElement[] {
    if (!elements?.length) {
        return [];
    }

    return reconstructElementTree(elements).map(normalizeFormElement);
}

export function flattenAllElements(elements: FormElement[]): FormElement[] {
    const flattened: FormElement[] = [];

    const walk = (items: FormElement[]) => {
        items.forEach((item) => {
            if (item.type !== 'header') {
                flattened.push(item);
            }

            if (item.children?.length) {
                walk(item.children);
            }
        });
    };

    walk(elements);
    return flattened;
}

export function flattenElementsForSubmit(
    list: FormElement[],
    parentKey: string | null = null,
): FlatFormElementPayload[] {
    const output: FlatFormElementPayload[] = [];

    list.forEach((element, index) => {
        output.push({
            id: element.id,
            parent_key: parentKey,
            input_id: element.input_id,
            label: element.label,
            placeholder: element.placeholder,
            hints: element.hints ?? null,
            icon: element.icon ?? null,
            options: serializeOptionsForStorage(element.options, element.type),
            correct_answer: element.correct_answer ?? [],
            marks: element.marks ?? 1,
            required: element.required,
            sort: index,
            type: element.type,
            condition_input_id: element.condition_input_id ?? null,
            condition_value: element.condition_value ?? null,
            is_repeatable: !!element.is_repeatable,
            repeat_min: element.repeat_min ?? 1,
            repeat_max: element.repeat_max ?? null,
        });

        if (element.children?.length) {
            output.push(...flattenElementsForSubmit(element.children, element.id));
        }
    });

    return output;
}

export function createFormElement(source: AvailableElement): FormElement {
    const type = source.type || 'text';
    const capitalizedType = type.charAt(0).toUpperCase() + type.slice(1);

    return {
        id: generateElementId(),
        input_id: source.id,
        type,
        label: {
            en: `${capitalizedType} Field`,
            bn: `${capitalizedType} Field`,
        },
        placeholder: {
            en: `Enter ${type}`,
            bn: `Enter ${type}`,
        },
        hints: {
            en: '',
            bn: '',
        },
        icon: type === 'group' ? 'mdi:image' : undefined,
        children: type === 'group' ? [] : undefined,
        required: false,
        options: hasOptionsFieldType(type)
            ? createDefaultOptionLists()
            : null,
        correct_answer: [],
        marks: 1,
        condition_input_id: null,
        condition_value: null,
        is_repeatable: false,
        repeat_min: 1,
        repeat_max: null,
    };
}

export function walkElements(
    elements: FormElement[],
    visitor: (element: FormElement, list: FormElement[]) => boolean | void,
): boolean {
    for (const element of elements) {
        if (visitor(element, elements) === true) {
            return true;
        }

        if (element.children?.length && walkElements(element.children, visitor)) {
            return true;
        }
    }

    return false;
}
