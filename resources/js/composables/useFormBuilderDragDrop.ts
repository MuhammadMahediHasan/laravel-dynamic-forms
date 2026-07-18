import { ref } from 'vue';

function getDraggedElementType(
    dragEl?: HTMLElement & { _underlying_vm_?: { type?: string } },
    draggedContext?: { element?: { type?: string } },
): string | undefined {
    return draggedContext?.element?.type ?? dragEl?._underlying_vm_?.type;
}

interface DragDropDependencies {
    removeGroupFromChildren: (groupId: string) => void;
}

export function useFormBuilderDragDrop({
    removeGroupFromChildren,
}: DragDropDependencies) {
    const draggedItemType = ref<string | null>(null);
    const hoveringGroupId = ref<string | null>(null);

    const isDraggingGroup = (): boolean => draggedItemType.value === 'group';

    const canMoveIntoGroup = (event: {
        draggedContext?: { element?: { type?: string } };
        dragged?: HTMLElement & { _underlying_vm_?: { type?: string } };
    }): boolean =>
        getDraggedElementType(event.dragged, event.draggedContext) !== 'group';

    const canPutInGroup = (
        _to: unknown,
        _from: unknown,
        dragEl: HTMLElement & { _underlying_vm_?: { type?: string } },
    ): boolean => dragEl._underlying_vm_?.type !== 'group';

    const handleBuilderDragStart = (event: {
        item?: HTMLElement & { _underlying_vm_?: { type?: string } };
    }): void => {
        draggedItemType.value = event.item?._underlying_vm_?.type ?? null;
    };

    const handleBuilderDragStartByType = (type: string): void => {
        draggedItemType.value = type;
    };

    const handleBuilderDragEnd = (): void => {
        draggedItemType.value = null;
        hoveringGroupId.value = null;
    };

    const handleGroupDragEnter = (groupId: string, event: DragEvent): void => {
        event.preventDefault();

        if (isDraggingGroup()) {
            return;
        }

        hoveringGroupId.value = groupId;
    };

    const handleGroupDragLeave = (groupId: string, event: DragEvent): void => {
        event.preventDefault();

        if (hoveringGroupId.value === groupId) {
            hoveringGroupId.value = null;
        }
    };

    return {
        hoveringGroupId,
        canMoveIntoGroup,
        canPutInGroup,
        handleBuilderDragStart,
        handleBuilderDragStartByType,
        handleBuilderDragEnd,
        handleGroupDragEnter,
        handleGroupDragLeave,
        removeGroupFromChildren,
    };
}
