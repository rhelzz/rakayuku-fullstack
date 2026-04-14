<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import { useAuthSession } from '@/composables/useAuthSession';
import { authService } from '@/services/authService';

const { setSession } = useAuthSession();

const email = ref('');
const password = ref('');
const rememberMe = ref(false);
const isLoading = ref(false);
const submitError = ref('');

const emailError = computed(() => {
    if (!email.value) {
        return '';
    }

    return email.value.includes('@') ? '' : 'Email tidak valid';
});

const isFormReady = computed(() => {
    return (
        Boolean(email.value.trim()) &&
        password.value.length >= 6 &&
        !emailError.value
    );
});

const handleSubmit = async (event: Event) => {
    event.preventDefault();
    submitError.value = '';

    if (!email.value.trim()) {
        submitError.value = 'Email tidak boleh kosong';

        return;
    }

    if (emailError.value) {
        submitError.value = emailError.value;

        return;
    }

    if (!password.value) {
        submitError.value = 'Password tidak boleh kosong';

        return;
    }

    if (password.value.length < 6) {
        submitError.value = 'Password minimal 6 karakter';

        return;
    }

    isLoading.value = true;

    try {
        const result = await authService.login(
            email.value.trim(),
            password.value,
            'rakayuku-admin-web',
        );

        setSession(
            {
                token: result.token,
                tokenType: result.tokenType,
                user: result.user,
            },
            rememberMe.value,
        );

        email.value = '';
        password.value = '';

        router.visit('/admin', {
            replace: true,
        });
    } catch (error) {
        submitError.value =
            error instanceof Error ? error.message : 'Login gagal';
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <Head title="Login" />

    <div class="relative min-h-screen overflow-hidden bg-slate-200">
        <div
            class="absolute inset-y-0 left-0 hidden w-1/2 bg-emerald-700 lg:block"
        ></div>

        <div
            class="relative z-10 mx-auto flex min-h-screen max-w-6xl items-center justify-center p-4 lg:p-8"
        >
            <div
                class="grid w-full max-w-5xl overflow-hidden rounded-3xl bg-slate-100 shadow-[0_24px_70px_rgba(15,23,42,0.28)] lg:grid-cols-2"
            >
                <section
                    class="relative hidden min-h-160 overflow-hidden bg-linear-to-br from-emerald-500 via-emerald-600 to-emerald-700 p-12 text-white lg:block"
                >
                    <div class="pointer-events-none absolute inset-0">
                        <div
                            class="absolute -top-10 -left-14 h-32 w-32 rounded-full bg-white/20"
                        ></div>
                        <div
                            class="absolute top-14 right-12 h-24 w-24 rounded-full bg-emerald-200/30"
                        ></div>
                        <div
                            class="absolute bottom-10 left-12 h-20 w-20 rounded-full bg-emerald-300/35"
                        ></div>
                        <div
                            class="absolute right-4 bottom-2 h-56 w-56 rounded-full border-8 border-emerald-200/55"
                        ></div>
                    </div>

                    <div
                        class="relative z-10 flex h-full flex-col justify-between"
                    >
                        <div class="flex items-center gap-3 text-emerald-100">
                            <span
                                class="inline-block h-6 w-6 rounded-full bg-emerald-100"
                            ></span>
                            <span
                                class="inline-block h-12 w-3 rounded-full bg-emerald-200/70"
                            ></span>
                            <span
                                class="inline-block h-10 w-3 rounded-full bg-emerald-200/50"
                            ></span>
                            <span
                                class="inline-block h-8 w-8 rounded-full border-4 border-emerald-100/70"
                            ></span>
                        </div>

                        <div class="max-w-xs space-y-4">
                            <h2
                                class="text-5xl leading-[0.95] font-semibold tracking-tight"
                            >
                                Rakayuku
                                <br />
                                Workspace
                            </h2>
                            <p
                                class="text-lg leading-relaxed text-emerald-50/90"
                            >
                                ERP untuk furniture custom kayu. Kelola
                                produksi, inventaris, dan penjualan dengan
                                mudah.
                            </p>
                        </div>

                        <div class="flex items-end justify-between">
                            <div class="grid grid-cols-4 gap-2 opacity-80">
                                <span
                                    v-for="n in 12"
                                    :key="n"
                                    class="h-1.5 w-1.5 rounded-full bg-emerald-100/70"
                                ></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span
                                    class="h-10 w-10 rounded-full bg-emerald-200/80"
                                ></span>
                                <span
                                    class="h-3 w-3 rounded-full bg-white/90"
                                ></span>
                            </div>
                        </div>
                    </div>
                </section>

                <section
                    class="relative flex min-h-160 items-center justify-center bg-slate-100 px-6 py-10 sm:px-10"
                >
                    <div
                        class="pointer-events-none absolute inset-0 hidden lg:block"
                    >
                        <div
                            class="absolute -top-24 -right-24 h-64 w-64 rounded-full border-26 border-emerald-200/60"
                        ></div>
                        <div
                            class="absolute right-14 -bottom-20 h-44 w-44 rounded-full border-18 border-emerald-300/50"
                        ></div>
                    </div>

                    <div
                        class="relative z-10 w-full max-w-lg rounded-2xl bg-white p-8 shadow-xl ring-1 ring-slate-200 sm:p-10"
                    >
                        <div
                            class="mb-8 flex flex-col items-center gap-4 text-center"
                        >
                            <div
                                class="flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-50 text-3xl font-bold text-emerald-600 ring-1 ring-emerald-100"
                            >
                                R
                            </div>
                            <p
                                class="text-2xl font-semibold tracking-tight text-slate-800"
                            >
                                Selamat datang di Rakayuku
                            </p>
                        </div>

                        <div
                            v-if="submitError"
                            class="mb-5 rounded-lg border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-600"
                        >
                            {{ submitError }}
                        </div>

                        <form class="space-y-5" @submit="handleSubmit">
                            <div>
                                <label
                                    for="email"
                                    class="mb-2 block text-sm font-medium text-slate-600"
                                    >Email</label
                                >
                                <input
                                    id="email"
                                    v-model="email"
                                    type="email"
                                    placeholder="Enter your email address"
                                    required
                                    :disabled="isLoading"
                                    class="h-12 w-full rounded-lg border border-slate-200 bg-slate-50 px-4 text-base text-slate-700 transition outline-none focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100 disabled:cursor-not-allowed disabled:opacity-70"
                                />
                                <p
                                    v-if="emailError"
                                    class="mt-1.5 text-xs text-red-500"
                                >
                                    {{ emailError }}
                                </p>
                            </div>

                            <div>
                                <label
                                    for="password"
                                    class="mb-2 block text-sm font-medium text-slate-600"
                                    >Password</label
                                >
                                <input
                                    id="password"
                                    v-model="password"
                                    type="password"
                                    placeholder="************"
                                    required
                                    :disabled="isLoading"
                                    class="h-12 w-full rounded-lg border border-slate-200 bg-slate-50 px-4 text-base text-slate-700 transition outline-none focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100 disabled:cursor-not-allowed disabled:opacity-70"
                                />
                            </div>

                            <div
                                class="flex items-center justify-between pt-1 text-sm"
                            >
                                <label
                                    class="inline-flex items-center gap-2 text-slate-500"
                                >
                                    <input
                                        v-model="rememberMe"
                                        type="checkbox"
                                        class="h-4 w-4 rounded border-slate-300 accent-emerald-600"
                                    />
                                    Remember me
                                </label>
                                <button
                                    type="button"
                                    class="font-medium text-emerald-600 transition hover:text-emerald-700"
                                >
                                    Reset Password!
                                </button>
                            </div>

                            <button
                                type="submit"
                                :disabled="!isFormReady || isLoading"
                                class="mt-1 flex h-12 w-full items-center justify-center rounded-lg bg-emerald-600 px-4 text-base font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-emerald-300"
                            >
                                <span v-if="!isLoading">Login</span>
                                <span v-else class="flex items-center gap-2">
                                    <svg
                                        class="h-4 w-4 animate-spin"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <circle
                                            class="opacity-30"
                                            cx="12"
                                            cy="12"
                                            r="10"
                                            stroke="currentColor"
                                            stroke-width="4"
                                        ></circle>
                                        <path
                                            class="opacity-90"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0A12 12 0 000 12h4z"
                                        ></path>
                                    </svg>
                                    Logging in...
                                </span>
                            </button>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>
