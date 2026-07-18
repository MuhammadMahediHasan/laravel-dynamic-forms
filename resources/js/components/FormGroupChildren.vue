<script setup lang="ts">
import FormElement from './FormElement.vue'
import FormGroupChildren from './FormGroupChildren.vue'
import SvgIcon from './SvgIcon.vue'
import Draggable from 'vuedraggable'
import type { FormElementOptions } from '../utils/formOptions'

interface LocalizedText {
    en: string
    bn: string
}

interface FormElementType {
    id: string
    input_id?: string | number
    type: string
    label: LocalizedText
    placeholder: LocalizedText
    hints?: LocalizedText
    icon?: string
    icon_path?: string
    children?: FormElementType[]
    required: boolean
    has_action: boolean
    options: FormElementOptions
    correct_answer?: string[]
    marks?: number
    condition_input_id?: string | number | null
    condition_value?: string
    is_repeatable?: boolean
    repeat_min?: number
    repeat_max?: number | null
}

interface Props {
    group: FormElementType
    depth?: number
    attached?: boolean
    collapseAll?: boolean
    formErrors: Record<string, string>
    allFormElements: FormElementType[]
    isGradableType: boolean
    hoveringGroupId: string | null
    getFlatElementIndex: (id: string) => number
    canMoveIntoGroup: (event: {
        draggedContext?: { element?: { type?: string } }
        dragged?: HTMLElement & { _underlying_vm_?: { type?: string } }
    }) => boolean
    canPutInGroup: (
        to: unknown,
        from: unknown,
        dragEl: HTMLElement & { _underlying_vm_?: { type?: string } },
    ) => boolean
}

const props = withDefaults(defineProps<Props>(), {
    depth: 1,
    attached: false,
})

const emit = defineEmits<{
    (e: 'update', id: string, field: string, value: any): void
    (e: 'remove', id: string): void
    (e: 'moveUp', id: string): void
    (e: 'moveDown', id: string): void
    (e: 'groupDragEnter', groupId: string, event: DragEvent): void
    (e: 'groupDragLeave', groupId: string, event: DragEvent): void
    (e: 'builderDragStart', type: string): void
    (e: 'builderDragEnd'): void
    (e: 'groupChildrenChange', groupId: string): void
}>()

const getDraggedType = (event: {
    item?: HTMLElement & { _underlying_vm_?: { type?: string } }
}): string | null => {
    return event.item?._underlying_vm_?.type ?? null
}

const onDragStart = (event: {
    item?: HTMLElement & { _underlying_vm_?: { type?: string } }
}): void => {
    const type = getDraggedType(event)
    if (type) {
        emit('builderDragStart', type)
    }
}

const onDragEnd = (): void => {
    emit('builderDragEnd')
}

const onGroupChildrenChange = (event: {
    added?: { element: FormElementType }
}): void => {
    if (event.added?.element?.type === 'group') {
        emit('groupChildrenChange', props.group.id)
    }
}

const childIndex = (childId: string): number => {
    return props.group.children?.findIndex((child) => child.id === childId) ?? -1
}

const isNested = props.depth > 1
const FORM_BUILDER_GROUP = 'form-builder'
const childCount = () => props.group.children?.length ?? 0
</script>

<template>
    <div
        :class="
            attached
                ? 'px-4 pb-4'
                : isNested
                  ? 'mt-3 border-t border-border/60 pt-3'
                  : 'mt-4 border-t border-border/60 pt-4'
        "
    >
        <div class="mb-3 flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <SvgIcon
                    name="mdi:format-list-bulleted-type"
                    class="h-4 w-4 text-primary"
                />
                <h4 class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                    {{ isNested ? 'Subgroup fields' : 'Group fields' }}
                </h4>
            </div>
            <span
                v-if="childCount() > 0"
                class="rounded-full bg-primary/10 px-2 py-0.5 text-[11px] font-medium text-primary"
            >
                {{ childCount() }}
            </span>
        </div>

        <div
            :class="[
                'min-h-[7rem] rounded-lg border border-dashed p-3 transition-colors',
                hoveringGroupId === group.id
                    ? 'border-primary bg-primary/5 dark:bg-primary/10'
                    : 'border-border/80 bg-muted/20 dark:bg-muted/10',
            ]"
        >
            <Draggable
                v-model="group.children"
                item-key="id"
                class="space-y-2.5"
                :animation="200"
                ghost-class="ghost-child"
                :group="{
                    name: FORM_BUILDER_GROUP,
                    pull: true,
                    put: canPutInGroup,
                }"
                :move="canMoveIntoGroup"
                @start="onDragStart"
                @end="onDragEnd"
                @change="onGroupChildrenChange"
                @dragover.native.prevent
                @dragenter.native="(e: DragEvent) => emit('groupDragEnter', group.id, e)"
                @dragleave.native="(e: DragEvent) => emit('groupDragLeave', group.id, e)"
            >
                <template #header>
                    <div
                        v-if="!group.children || group.children.length === 0"
                        class="flex flex-col items-center justify-center py-6 text-center"
                    >
                        <div
                            class="mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-primary/10"
                        >
                            <SvgIcon
                                name="mdi:cursor-move"
                                class="h-5 w-5 text-primary"
                            />
                        </div>
                        <p class="text-sm font-medium text-foreground">
                            Drop fields here
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Drag fields from the sidebar into this group
                        </p>
                    </div>
                </template>

                <template #item="{ element: child }">
                    <div :data-group-child-id="child.id">
                        <div
                            v-if="child.type === 'group'"
                            class="overflow-hidden rounded-lg border border-border/70 bg-background"
                        >
                            <FormElement
                                :element="child"
                                :index="getFlatElementIndex(child.id)"
                                :errors="formErrors"
                                :all-form-elements="allFormElements"
                                :is-gradable-type="isGradableType"
                                :collapse-all="collapseAll"
                                :can-move-up="childIndex(child.id) > 0"
                                :can-move-down="group.children ? childIndex(child.id) < group.children.length - 1 : false"
                                embedded
                                class="!rounded-none !border-0 !shadow-none"
                                @update="(id, field, value) => emit('update', id, field, value)"
                                @remove="(id) => emit('remove', id)"
                                @move-up="(id) => emit('moveUp', id)"
                                @move-down="(id) => emit('moveDown', id)"
                            />
                            <FormGroupChildren
                                :group="child"
                                :depth="depth + 1"
                                attached
                                :collapse-all="collapseAll"
                                :form-errors="formErrors"
                                :all-form-elements="allFormElements"
                                :is-gradable-type="isGradableType"
                                :hovering-group-id="hoveringGroupId"
                                :get-flat-element-index="getFlatElementIndex"
                                :can-move-into-group="canMoveIntoGroup"
                                :can-put-in-group="canPutInGroup"
                                @update="(id, field, value) => emit('update', id, field, value)"
                                @remove="(id) => emit('remove', id)"
                                @move-up="(id) => emit('moveUp', id)"
                                @move-down="(id) => emit('moveDown', id)"
                                @group-drag-enter="(groupId, e) => emit('groupDragEnter', groupId, e)"
                                @group-drag-leave="(groupId, e) => emit('groupDragLeave', groupId, e)"
                                @builder-drag-start="(type) => emit('builderDragStart', type)"
                                @builder-drag-end="() => emit('builderDragEnd')"
                                @group-children-change="(groupId) => emit('groupChildrenChange', groupId)"
                            />
                        </div>

                        <FormElement
                            v-else
                            :element="child"
                            :index="getFlatElementIndex(child.id)"
                            :errors="formErrors"
                            :all-form-elements="allFormElements"
                            :is-gradable-type="isGradableType"
                            :collapse-all="collapseAll"
                            :can-move-up="childIndex(child.id) > 0"
                            :can-move-down="group.children ? childIndex(child.id) < group.children.length - 1 : false"
                            embedded
                            @update="(id, field, value) => emit('update', id, field, value)"
                            @remove="(id) => emit('remove', id)"
                            @move-up="(id) => emit('moveUp', id)"
                            @move-down="(id) => emit('moveDown', id)"
                        />
                    </div>
                </template>
            </Draggable>
        </div>
    </div>
</template>

<style scoped>
.ghost-child {
    opacity: 0.35;
    background-color: color-mix(in srgb, var(--primary) 18%, transparent);
}
</style>
