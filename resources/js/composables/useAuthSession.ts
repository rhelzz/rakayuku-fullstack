import { computed, ref } from 'vue';

import type { AuthSession } from '@/types';

const LOCAL_STORAGE_KEY = 'rakayuku.auth.local';
const SESSION_STORAGE_KEY = 'rakayuku.auth.session';

const canUseBrowserStorage = () => typeof window !== 'undefined';

const parseSession = (value: string | null): AuthSession | null => {
    if (!value) {
        return null;
    }

    try {
        return JSON.parse(value) as AuthSession;
    } catch {
        return null;
    }
};

const readInitialSession = (): AuthSession | null => {
    if (!canUseBrowserStorage()) {
        return null;
    }

    return parseSession(sessionStorage.getItem(SESSION_STORAGE_KEY))
        ?? parseSession(localStorage.getItem(LOCAL_STORAGE_KEY));
};

const session = ref<AuthSession | null>(readInitialSession());

const writeSession = (value: AuthSession | null, remember: boolean): void => {
    if (!canUseBrowserStorage()) {
        return;
    }

    localStorage.removeItem(LOCAL_STORAGE_KEY);
    sessionStorage.removeItem(SESSION_STORAGE_KEY);

    if (!value) {
        return;
    }

    if (remember) {
        localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(value));

        return;
    }

    sessionStorage.setItem(SESSION_STORAGE_KEY, JSON.stringify(value));
};

export const useAuthSession = () => {
    const isAuthenticated = computed(() => Boolean(session.value?.token));

    const setSession = (value: AuthSession, remember = true): void => {
        session.value = value;
        writeSession(value, remember);
    };

    const clearSession = (): void => {
        session.value = null;
        writeSession(null, false);
    };

    return {
        session,
        isAuthenticated,
        setSession,
        clearSession,
    };
};
