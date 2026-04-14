import {
    destroy,
    destroyAll,
    store,
} from '@/actions/App/Http/Controllers/Api/Auth/AuthenticatedSessionController';
import CurrentUserController from '@/actions/App/Http/Controllers/Api/Auth/CurrentUserController';
import { apiRequest } from '@/services/apiClient';
import type { ApiUser, User } from '@/types';

type AuthLoginData = {
    user: ApiUser;
    token: string;
    token_type: string;
};

const mapUser = (user: ApiUser): User => ({
    id: user.id,
    name: user.name,
    email: user.email,
    role: user.role,
    created_at: user.created_at,
    updated_at: user.updated_at,
});

export const authService = {
    async login(email: string, password: string, deviceName = 'rakayuku-admin-web') {
        const response = await apiRequest<AuthLoginData>(store(), {
            body: {
                email,
                password,
                device_name: deviceName,
            },
        });

        return {
            user: mapUser(response.data.user),
            token: response.data.token,
            tokenType: response.data.token_type,
            message: response.message,
        };
    },

    async me(token: string) {
        const response = await apiRequest<{ user: ApiUser }>(CurrentUserController(), {
            token,
        });

        return mapUser(response.data.user);
    },

    async logout(token: string) {
        await apiRequest<{ revoked: boolean }>(destroy(), {
            token,
        });
    },

    async logoutAll(token: string) {
        await apiRequest<{ revoked_all: boolean }>(destroyAll(), {
            token,
        });
    },
};
