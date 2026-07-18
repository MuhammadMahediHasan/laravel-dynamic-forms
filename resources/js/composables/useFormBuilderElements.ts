import { computed, type Ref } from 'vue';
import type { AvailableElement, FormElement } from '../types/formBuilder';
import { createFormElement, walkElements } from '../utils/formElementTree';

export function useFormBuilderElements(formElements: Ref<FormElement[]>) {
    const createElement = (source: AvailableElement): FormElement =>
        createFormElement(source);

    const getElementIndex = (elementId: string): number =>
        formElements.value.findIndex((element) => element.id === elementId);

    const updateElement = (id: string, field: string, value: unknown): void => {
        walkElements(formElements.value, (element) => {
            if (element.id === id) {
                (element as unknown as Record<string, unknown>)[field] = value;
                return true;
            }
        });
    };

    const removeElement = (id: string): void => {
        const removeFromList = (list: FormElement[]): FormElement[] =>
            list
                .filter((element) => element.id !== id)
                .map((element) => ({
                    ...element,
                    children: element.children
                        ? removeFromList(element.children)
                        : element.children,
                }));

        formElements.value = removeFromList(formElements.value);
    };

    const reorderElement = (id: string, direction: 'up' | 'down'): void => {
        const reorderInTree = (
            list: FormElement[],
            parent: FormElement | null,
        ): boolean => {
            const index = list.findIndex((element) => element.id === id);

            if (index >= 0) {
                const targetIndex = direction === 'up' ? index - 1 : index + 1;

                if (targetIndex < 0 || targetIndex >= list.length) {
                    return false;
                }

                const reordered = [...list];
                [reordered[index], reordered[targetIndex]] = [
                    reordered[targetIndex],
                    reordered[index],
                ];

                if (parent) {
                    parent.children = reordered;
                } else {
                    formElements.value = reordered;
                }

                return true;
            }

            return walkElements(list, (element) => {
                if (element.children?.length) {
                    return reorderInTree(element.children, element);
                }
            });
        };

        reorderInTree(formElements.value, null);
    };

    const moveElementUp = (id: string): void => reorderElement(id, 'up');
    const moveElementDown = (id: string): void => reorderElement(id, 'down');

    const removeGroupFromChildren = (groupId: string): void => {
        walkElements(formElements.value, (element) => {
            if (element.id === groupId && element.children) {
                element.children = element.children.filter(
                    (child) => child.type !== 'group',
                );
                return true;
            }
        });
    };

    const allFormElementsFlattened = computed(() => {
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

        walk(formElements.value);
        return flattened;
    });

    const totalElementsCount = computed(
        () => allFormElementsFlattened.value.length,
    );

    const getFlatElementIndex = (elementId: string): number =>
        allFormElementsFlattened.value.findIndex(
            (element) => element.id === elementId,
        );

    return {
        createElement,
        getElementIndex,
        updateElement,
        removeElement,
        moveElementUp,
        moveElementDown,
        removeGroupFromChildren,
        allFormElementsFlattened,
        totalElementsCount,
        getFlatElementIndex,
    };
}
