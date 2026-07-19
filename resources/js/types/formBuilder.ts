import type { FormElementOptions } from '../utils/formOptions';

export interface FlashMessages {
    success?: string;
    error?: string;
    message?: string;
    warning?: string;
}

export interface LocalizedText extends Record<string, string> {
    en: string;
    bn: string;
}

export interface AvailableElement {
    id: string | number;
    type: string;
    label: string;
    icon: string;
}

export interface TypeData {
    option: string;
    value: string;
}

export interface StatusData {
    option: string;
    value: string;
}

export interface FormElement {
    id: string;
    input_id: string | number;
    type: string;
    label: LocalizedText;
    placeholder: LocalizedText;
    hints?: LocalizedText;
    icon?: string;
    icon_path?: string;
    children?: FormElement[];
    required: boolean;
    options: FormElementOptions;
    correct_answer?: string[];
    marks?: number;
    condition_input_id?: string | number | null;
    condition_value?: string|null;
    is_repeatable?: boolean;
    repeat_min?: number;
    repeat_max?: number | null;
    parent_id?: string | number;
}

export interface EditData {
    form_id?: string | number;
    slug?: string;
    type: string;
    name: string;
    status: string;
    elements: FormElement[];
    description?: string;
}

export interface ApprovalHistoryEntry {
    type: string;
    at: string;
    by?: string | null;
    moduleable_id?: string | number;
    moduleable_user_type?: string;
}

export interface FormBuilderProps {
    slug?: string;
    apiPrefix?: string;
}

export interface FlatFormElementPayload {
    id: string;
    parent_key: string | null;
    input_id: string | number;
    label: LocalizedText;
    placeholder: LocalizedText;
    hints: LocalizedText | null;
    icon: string | null;
    options: FormElementOptions;
    correct_answer: string[];
    marks: number;
    required: boolean;
    sort: number;
    type: string;
    condition_input_id: string | number | null;
    condition_value: string | null;
    is_repeatable: boolean;
    repeat_min: number;
    repeat_max: number | null;
}
