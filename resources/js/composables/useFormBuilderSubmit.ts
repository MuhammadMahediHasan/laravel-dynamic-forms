import type { AvailableElement } from '../types/formBuilder';

export async function fetchAvailableElements(): Promise<AvailableElement[]> {
    const response = await window.fetchWithCsrf('/api/v1/form-inputs');

    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();

    if (data.data) {
        return data.data;
    }

    if (Array.isArray(data)) {
        return data;
    }

    return [];
}
