<script setup lang="ts">
import { Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import { useAuthSession } from '@/composables/useAuthSession';
import { adminModules } from '@/data/adminFeatureMap';
import { authService } from '@/services/authService';

const desktopSidebarCollapsed = ref(false);
const mobileSidebarOpen = ref(false);
const isLoggingOut = ref(false);

const page = usePage();
const { session, clearSession } = useAuthSession();

const navItems = computed(() => [
    {
        label: 'Overview',
        href: '/admin',
    },
    ...adminModules
        .filter((module) => !['auth'].includes(module.key))
        .map((module) => ({
            label: module.title,
            href: module.routePath,
        })),
]);

const currentPath = computed(() => page.url.split('?')[0]);

const isActiveLink = (href: string): boolean => {
    if (href === '/admin') {
        return currentPath.value === '/admin';
    }

    return currentPath.value.startsWith(href);
};

const userName = computed(() => session.value?.user.name ?? 'Guest User');
const userRole = computed(() => {
    const role = session.value?.user.role ?? 'viewer';

    return role.replaceAll('_', ' ').toUpperCase();
});

const closeMobileSidebar = (): void => {
    mobileSidebarOpen.value = false;
};

const toggleDesktopSidebar = (): void => {
    desktopSidebarCollapsed.value = !desktopSidebarCollapsed.value;
};

const logout = async (): Promise<void> => {
    if (isLoggingOut.value) {
        return;
    }

    isLoggingOut.value = true;

    try {
        if (session.value?.token) {
            await authService.logout(session.value.token);
        }
    } catch {
        // Keep UX smooth even if API logout fails on expired token.
    } finally {
        clearSession();
        isLoggingOut.value = false;
        router.visit('/login', {
            replace: true,
        });
    }
};
</script>

<template>
    <div class="relative h-screen overflow-hidden bg-slate-200 text-slate-800">
        <div class="pointer-events-none absolute inset-0 opacity-80">
            <div
                class="absolute -top-24 -left-12 h-80 w-80 rounded-full bg-emerald-200/45 blur-3xl"
            ></div>
            <div
                class="absolute -right-20 bottom-0 h-96 w-96 rounded-full bg-emerald-300/35 blur-3xl"
            ></div>
        </div>

        <div class="relative flex h-full">
            <aside
                :class="[
                    desktopSidebarCollapsed ? 'w-24' : 'w-72',
                    'hidden h-full shrink-0 border-r border-emerald-100 bg-emerald-700/95 text-white shadow-2xl backdrop-blur md:flex md:flex-col',
                ]"
            >
                <div
                    class="flex items-center justify-between border-b border-emerald-500/40 px-4 py-4"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-lg font-bold text-emerald-700"
                        >
                            R
                        </div>
                        <div v-if="!desktopSidebarCollapsed">
                            <p class="text-sm font-semibold tracking-wide">
                                Rakayuku Admin
                            </p>
                            <p class="text-xs text-emerald-100/85">
                                ERP Workspace
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-emerald-300/50 bg-emerald-600/50 text-emerald-50 transition hover:bg-emerald-600"
                        @click="toggleDesktopSidebar"
                    >
                        <span class="text-sm">{{
                            desktopSidebarCollapsed ? '>' : '<'
                        }}</span>
                    </button>
                </div>

                <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                    <Link
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition"
                        :class="[
                            isActiveLink(item.href)
                                ? 'bg-emerald-50 text-emerald-700'
                                : 'text-emerald-50/95 hover:bg-emerald-600/70',
                        ]"
                    >
                        <span
                            class="inline-flex h-2.5 w-2.5 rounded-full"
                            :class="
                                isActiveLink(item.href)
                                    ? 'bg-emerald-500'
                                    : 'bg-emerald-200/80'
                            "
                        ></span>
                        <span
                            v-if="!desktopSidebarCollapsed"
                            class="truncate"
                            >{{ item.label }}</span
                        >
                    </Link>
                </nav>

                <div class="border-t border-emerald-500/40 px-4 py-4">
                    <p
                        v-if="!desktopSidebarCollapsed"
                        class="text-xs text-emerald-100/90"
                    >
                        Data flow UI saat ini mengikuti endpoint aktif: Auth API
                        dan Material Flow API.
                    </p>
                    <p
                        v-else
                        class="text-center text-[11px] text-emerald-100/90"
                    >
                        v1
                    </p>
                </div>
            </aside>

            <div class="flex min-w-0 flex-1 flex-col">
                <header
                    class="shrink-0 border-b border-slate-200 bg-white/85 px-4 py-3 shadow-sm backdrop-blur lg:px-8"
                >
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:border-emerald-200 hover:text-emerald-600 md:hidden"
                                @click="mobileSidebarOpen = true"
                            >
                                <span class="text-lg">☰</span>
                            </button>

                            <div>
                                <p class="text-sm font-semibold text-slate-800">
                                    Admin Dashboard
                                </p>
                                <p class="text-xs text-slate-500">
                                    UI-first flow dengan sinkronisasi Inertia
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div
                                class="hidden rounded-xl bg-emerald-50 px-3 py-2 text-right sm:block"
                            >
                                <p class="text-xs text-slate-500">Signed as</p>
                                <p
                                    class="text-sm font-semibold text-emerald-700"
                                >
                                    {{ userName }}
                                </p>
                                <p class="text-[11px] text-emerald-600">
                                    {{ userRole }}
                                </p>
                            </div>

                            <button
                                type="button"
                                class="inline-flex h-10 items-center justify-center rounded-xl bg-emerald-600 px-4 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-emerald-300"
                                :disabled="isLoggingOut"
                                @click="logout"
                            >
                                {{ isLoggingOut ? 'Logging out...' : 'Logout' }}
                            </button>
                        </div>
                    </div>
                </header>

                <main class="min-h-0 flex-1 overflow-y-auto px-4 py-6 lg:px-8">
                    <slot />
                </main>
            </div>
        </div>

        <transition
            enter-active-class="transition duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="mobileSidebarOpen"
                class="fixed inset-0 z-30 bg-slate-950/45 md:hidden"
                @click="closeMobileSidebar"
            ></div>
        </transition>

        <aside
            class="fixed top-0 left-0 z-40 h-full w-72 border-r border-emerald-300 bg-emerald-700 px-3 py-4 text-white shadow-xl transition md:hidden"
            :class="mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="mb-4 flex items-center justify-between px-1">
                <p class="text-sm font-semibold tracking-wide">
                    Rakayuku Admin
                </p>
                <button
                    type="button"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-emerald-300/60 bg-emerald-600/70"
                    @click="closeMobileSidebar"
                >
                    ✕
                </button>
            </div>

            <nav class="space-y-1">
                <Link
                    v-for="item in navItems"
                    :key="`mobile-${item.href}`"
                    :href="item.href"
                    class="block rounded-xl px-3 py-2.5 text-sm"
                    :class="
                        isActiveLink(item.href)
                            ? 'bg-emerald-50 text-emerald-700'
                            : 'text-emerald-50/95 hover:bg-emerald-600/80'
                    "
                    @click="closeMobileSidebar"
                >
                    {{ item.label }}
                </Link>
            </nav>
        </aside>
    </div>
</template>
