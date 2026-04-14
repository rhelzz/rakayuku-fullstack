export type User = {
    id: number;
    name: string;
    email: string;
    role: string;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User | null;
};

export type AuthSession = {
    token: string;
    tokenType: string;
    user: User;
};
