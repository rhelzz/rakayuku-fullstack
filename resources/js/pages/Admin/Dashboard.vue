<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import {
    adminModules,
    dashboardStats,
    featureFlowCards,
} from '@/data/adminFeatureMap';
import AdminLayout from '@/layouts/AdminLayout.vue';

defineOptions({
    layout: AdminLayout,
});

const readinessClass: Record<string, string> = {
    ready: 'bg-emerald-100 text-emerald-700',
    'ui-preview': 'bg-amber-100 text-amber-700',
};
</script>

<template>
    <Head title="Admin Dashboard" />

    <div class="space-y-6">
        <section
            class="rounded-3xl bg-white/95 p-6 shadow-xl ring-1 ring-slate-200 lg:p-8"
        >
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p
                        class="text-xs font-semibold tracking-wider text-emerald-600 uppercase"
                    >
                        Feature Inventory
                    </p>
                    <h1
                        class="mt-2 text-2xl font-semibold tracking-tight text-slate-800 lg:text-3xl"
                    >
                        Rakayuku Admin Flow Dashboard
                    </h1>
                    <p
                        class="mt-3 max-w-3xl text-sm leading-relaxed text-slate-600"
                    >
                        Peta dashboard ini dibangun berdasarkan backend yang
                        terverifikasi: endpoint aktif saat ini adalah Auth API
                        dan Material Flow API, sedangkan modul domain lainnya
                        sudah tersedia di model dan migration namun masih dalam
                        mode UI preview.
                    </p>
                </div>

                <Link
                    href="/admin/material-flow"
                    class="inline-flex h-11 items-center justify-center rounded-xl bg-emerald-600 px-5 text-sm font-semibold text-white transition hover:bg-emerald-700"
                >
                    Buka Material Flow
                </Link>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article
                    class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4"
                >
                    <p class="text-xs font-semibold text-emerald-700 uppercase">
                        API Aktif
                    </p>
                    <p class="mt-2 text-3xl font-semibold text-emerald-800">
                        {{ dashboardStats.activeApiEndpoints }}
                    </p>
                    <p class="mt-1 text-xs text-emerald-700/80">
                        Auth + Material Flow
                    </p>
                </article>

                <article
                    class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                >
                    <p class="text-xs font-semibold text-slate-600 uppercase">
                        Modul Ready
                    </p>
                    <p class="mt-2 text-3xl font-semibold text-slate-800">
                        {{ dashboardStats.fullyReadyModules }}
                    </p>
                    <p class="mt-1 text-xs text-slate-500">
                        Bisa diuji end-to-end saat ini
                    </p>
                </article>

                <article
                    class="rounded-2xl border border-amber-200 bg-amber-50 p-4"
                >
                    <p class="text-xs font-semibold text-amber-700 uppercase">
                        UI Preview
                    </p>
                    <p class="mt-2 text-3xl font-semibold text-amber-800">
                        {{ dashboardStats.uiPreviewModules }}
                    </p>
                    <p class="mt-1 text-xs text-amber-700/80">
                        Menunggu endpoint query/CRUD
                    </p>
                </article>

                <article
                    class="rounded-2xl border border-slate-200 bg-white p-4"
                >
                    <p class="text-xs font-semibold text-slate-600 uppercase">
                        Total Domain
                    </p>
                    <p class="mt-2 text-3xl font-semibold text-slate-800">
                        {{ dashboardStats.totalDomainModules }}
                    </p>
                    <p class="mt-1 text-xs text-slate-500">
                        Dari auth sampai debt/receivable
                    </p>
                </article>
            </div>
        </section>

        <section class="grid gap-4 xl:grid-cols-2">
            <article
                v-for="flowCard in featureFlowCards"
                :key="flowCard.title"
                class="rounded-3xl bg-white p-6 shadow-lg ring-1 ring-slate-200"
            >
                <h2 class="text-lg font-semibold text-slate-800">
                    {{ flowCard.title }}
                </h2>
                <div class="mt-4 space-y-2">
                    <div
                        v-for="(step, index) in flowCard.steps"
                        :key="`${flowCard.title}-${index}`"
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
        </section>

        <section
            class="rounded-3xl bg-white p-6 shadow-lg ring-1 ring-slate-200 lg:p-8"
        >
            <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="text-xl font-semibold text-slate-800">
                        Modul Admin & Data-Flow Readiness
                    </h2>
                    <p class="mt-1 text-sm text-slate-600">
                        Semua modul divisualisasikan dengan tema yang konsisten
                        dan status backend aktual.
                    </p>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <article
                    v-for="module in adminModules"
                    :key="module.key"
                    class="rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-emerald-200 hover:bg-white"
                >
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="text-sm font-semibold text-slate-800">
                            {{ module.title }}
                        </h3>
                        <span
                            class="rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase"
                            :class="readinessClass[module.readiness]"
                        >
                            {{
                                module.readiness === 'ready'
                                    ? 'Ready'
                                    : 'UI Preview'
                            }}
                        </span>
                    </div>

                    <p class="mt-2 text-xs leading-relaxed text-slate-600">
                        {{ module.summary }}
                    </p>
                    <p class="mt-3 text-xs font-medium text-emerald-700">
                        {{ module.apiCoverage }}
                    </p>

                    <div class="mt-4 flex items-center justify-between">
                        <p class="text-[11px] text-slate-500">
                            {{ module.tableColumns.length }} kolom data utama
                        </p>
                        <Link
                            :href="module.routePath"
                            class="inline-flex h-8 items-center justify-center rounded-lg bg-emerald-600 px-3 text-xs font-semibold text-white transition hover:bg-emerald-700"
                        >
                            Buka
                        </Link>
                    </div>
                </article>
            </div>
        </section>
    </div>
</template>
