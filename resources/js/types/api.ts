export type ApiErrorMap = Record<string, string[]>;

export type ApiMeta = {
    timestamp: string;
    path: string;
};

export type ApiEnvelope<TData> = {
    success: boolean;
    message: string;
    data: TData;
    errors?: ApiErrorMap;
    meta?: ApiMeta;
};

export type ApiUser = {
    id: number;
    name: string;
    email: string;
    role: string;
    created_at: string;
    updated_at: string;
};
