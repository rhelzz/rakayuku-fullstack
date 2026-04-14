import type { ApiEnvelope, ApiErrorMap } from '@/types';

type ApiRouteLike = {
    url: string;
    method?: string;
    methods?: string[];
};

type ApiRequestOptions = {
    body?: unknown;
    token?: string;
    headers?: Record<string, string>;
};

export class ApiClientError extends Error {
    public readonly statusCode: number;
    public readonly errors: ApiErrorMap;

    public constructor(message: string, statusCode = 400, errors: ApiErrorMap = {}) {
        super(message);
        this.name = 'ApiClientError';
        this.statusCode = statusCode;
        this.errors = errors;
    }
}

const resolveMethod = (route: ApiRouteLike): string => {
    if (route.method) {
        return route.method.toUpperCase();
    }

    if (Array.isArray(route.methods) && route.methods.length > 0) {
        return route.methods[0].toUpperCase();
    }

    return 'GET';
};

const readJson = async <TData>(response: Response): Promise<ApiEnvelope<TData> | null> => {
    const contentType = response.headers.get('content-type') ?? '';

    if (!contentType.includes('application/json')) {
        return null;
    }

    return (await response.json()) as ApiEnvelope<TData>;
};

export const apiRequest = async <TData>(
    route: ApiRouteLike,
    options: ApiRequestOptions = {},
): Promise<ApiEnvelope<TData>> => {
    const method = resolveMethod(route);
    const isBodyAllowed = !['GET', 'HEAD'].includes(method);
    const headers: Record<string, string> = {
        Accept: 'application/json',
        ...options.headers,
    };

    if (options.token) {
        headers.Authorization = `Bearer ${options.token}`;
    }

    if (isBodyAllowed && options.body !== undefined) {
        headers['Content-Type'] = 'application/json';
    }

    const response = await fetch(route.url, {
        method,
        headers,
        body: isBodyAllowed && options.body !== undefined
            ? JSON.stringify(options.body)
            : undefined,
    });

    const payload = await readJson<TData>(response);

    if (!response.ok) {
        throw new ApiClientError(
            payload?.message ?? 'Permintaan gagal diproses.',
            response.status,
            payload?.errors ?? {},
        );
    }

    if (!payload) {
        throw new ApiClientError('Respons API tidak valid.', response.status);
    }

    if (!payload.success) {
        throw new ApiClientError(payload.message, response.status, payload.errors ?? {});
    }

    return payload;
};
