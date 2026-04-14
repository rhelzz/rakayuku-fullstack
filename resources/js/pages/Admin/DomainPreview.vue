<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

import { getAdminModule } from '@/data/adminFeatureMap';
import type { AdminModuleKey } from '@/data/adminFeatureMap';
import AdminLayout from '@/layouts/AdminLayout.vue';

defineOptions({
    layout: AdminLayout,
});

const props = defineProps<{
    moduleKey: AdminModuleKey;
}>();

const moduleDefinition = computed(() => getAdminModule(props.moduleKey));

const readinessClass = computed(() =>
    moduleDefinition.value.readiness === 'ready'
        ? 'bg-emerald-100 text-emerald-700'
        : 'bg-amber-100 text-amber-700',
);
</script>

<template>
    <Head :title="moduleDefinition.title" />

    <div class="space-y-6">
        <section
            class="rounded-3xl bg-white p-6 shadow-xl ring-1 ring-slate-200 lg:p-8"
        >
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p
                        class="text-xs font-semibold tracking-wider text-emerald-600 uppercase"
                    >
                        Domain Module
                    </p>
                    <h1
                        class="mt-2 text-2xl font-semibold tracking-tight text-slate-800 lg:text-3xl"
                    >
                        {{ moduleDefinition.title }}
                    </h1>
                    <p
                        class="mt-3 max-w-3xl text-sm leading-relaxed text-slate-600"
                    >
                        {{ moduleDefinition.summary }}
                    </p>
                </div>

                <span
                    class="inline-flex rounded-full px-3 py-1.5 text-xs font-semibold uppercase"
                    :class="readinessClass"
                >
                    {{
                        moduleDefinition.readiness === 'ready'
                            ? 'Ready'
                            : 'UI Preview'
                    }}
                </span>
            </div>

            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <article
                    class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                >
                    <p class="text-xs font-semibold text-slate-600 uppercase">
                        API Coverage
                    </p>
                    <p class="mt-2 text-sm font-medium text-slate-800">
                        {{ moduleDefinition.apiCoverage }}
                    </p>
                </article>

                <article
                    class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                >
                    <p class="text-xs font-semibold text-slate-600 uppercase">
                        Sumber Controller
                    </p>
                    <p
                        v-if="moduleDefinition.controllers.length === 0"
                        class="mt-2 text-sm text-slate-700"
                    >
                        Belum ada controller endpoint spesifik untuk domain ini.
                    </p>
                    <div v-else class="mt-2 space-y-1 text-sm text-slate-700">
                        <p
                            v-for="controller in moduleDefinition.controllers"
                            :key="controller"
                        >
                            {{ controller }}
                        </p>
                    </div>
                </article>
            </div>
        </section>

        <section
            class="rounded-3xl bg-white p-6 shadow-lg ring-1 ring-slate-200 lg:p-8"
        >
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-slate-800">
                    Blueprint Kolom Data
                </h2>
                <p class="text-xs text-slate-500">
                    Dirancang berdasarkan model dan migration backend.
                </p>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200">
                <table class="min-w-full bg-white text-sm">
                    <thead
                        class="bg-slate-100 text-left text-xs font-semibold tracking-wider text-slate-600 uppercase"
                    >
                        <tr>
                            <th
                                v-for="column in moduleDefinition.tableColumns"
                                :key="column"
                                class="px-4 py-3"
                            >
                                {{ column }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td
                                :colspan="moduleDefinition.tableColumns.length"
                                class="px-4 py-8 text-center text-sm text-slate-500"
                            >
                                Belum ada endpoint list untuk menampilkan data
                                aktual modul ini. UI tabel sudah dipersiapkan
                                agar langsung terhubung saat endpoint
                                ditambahkan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="grid gap-4 xl:grid-cols-2">
            <article
                class="rounded-3xl bg-white p-6 shadow-lg ring-1 ring-slate-200"
            >
                <h2 class="text-lg font-semibold text-slate-800">
                    Flow Domain
                </h2>
                <div class="mt-4 space-y-2">
                    <div
                        v-for="(step, index) in moduleDefinition.flowSteps"
                        :key="`${moduleDefinition.key}-${index}`"
                        class="flex items-start gap-3 rounded-xl border border-slate-100 bg-slate-50 p-3"
                    >
                        <span
                            class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-xs font-semibold text-emerald-700"
                        >
                            {{ index + 1 }}
                        </span>
                        <p class="text-sm text-slate-700">{{ step }}</p>
                    </div>
                </div>
            </article>

            <article
                class="rounded-3xl bg-white p-6 shadow-lg ring-1 ring-slate-200"
            >
                <h2 class="text-lg font-semibold text-slate-800">
                    Arah Integrasi Berikutnya
                </h2>
                <div class="mt-4 space-y-3 text-sm text-slate-700">
                    <p>
                        1. Tambahkan endpoint list dengan pagination untuk
                        domain ini.
                    </p>
                    <p>
                        2. Hubungkan form create/update ke endpoint domain
                        ketika backend siap.
                    </p>
                    <p>
                        3. Aktifkan role-based visibility sesuai kemampuan token
                        user.
                    </p>
                </div>

                <div class="mt-6">
                    <Link
                        href="/admin"
                        class="inline-flex h-10 items-center justify-center rounded-xl bg-emerald-600 px-4 text-sm font-semibold text-white transition hover:bg-emerald-700"
                    >
                        Kembali ke Overview
                    </Link>
                </div>
            </article>
        </section>
    </div>
</template>
