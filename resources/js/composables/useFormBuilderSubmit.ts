import type { AvailableElement, TypeData } from '../types/formBuilder';

export async function fetchAvailableElements(): Promise<AvailableElement[]> {
    const response = await (window as any).fetchWithCsrf('/api/v1/df/inputs');

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

export async function fetchBuilderConfig(): Promise<{ types: TypeData[]; locales: string[] }> {
    const response = await (window as any).fetchWithCsrf('/api/v1/df/config');

    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }

    return await response.json();
}

export async function fetchFormDetails(slug: string, apiPrefix: string = '/api/v1/df'): Promise<any> {
    const response = await (window as any).fetchWithCsrf(`${apiPrefix}/${slug}`);

    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }

    const res = await response.json();
    return res.data;
}
