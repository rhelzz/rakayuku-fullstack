<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, reactive, ref, watch } from 'vue';

import { useAuthSession } from '@/composables/useAuthSession';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { ApiClientError } from '@/services/apiClient';
import { materialFlowService } from '@/services/materialFlowService';
import type {
    MaterialFlowPayload,
    MaterialFlowResult,
} from '@/services/materialFlowService';

defineOptions({
    layout: AdminLayout,
});

type ProjectOption = {
    id: number;
    code: string;
    name: string;
    customer_name: string;
    status: string;
};

type ItemOption = {
    id: number;
    code: string;
    name: string;
    unit: string;
    current_stock: number;
    base_price: number;
};

const props = defineProps<{
    projects: ProjectOption[];
    items: ItemOption[];
}>();

const { session } = useAuthSession();

const isLoading = ref(false);
const submitError = ref('');
const successMessage = ref('');
const result = ref<MaterialFlowResult | null>(null);

const form = reactive({
    project_id: '',
    required_qty: 1,
    itemMode: 'existing' as 'existing' | 'new',
    item_id: '',
    item_code: '',
    item_name: '',
    item_unit: '',
    base_price: 0,
    initial_stock: 0,
    minimum_stock: 0,
    auto_purchase: true,
    supplier_name: '',
    purchase_unit_price: 0,
    purchase_qty: 0,
    purchase_notes: '',
    document_ref: '',
    reference_note: '',
});

const projectOptions = computed(() => props.projects ?? []);
const itemOptions = computed(() => props.items ?? []);

const selectedItem = computed(
    () =>
        itemOptions.value.find((item) => item.id === Number(form.item_id)) ??
        null,
);

watch(selectedItem, (item) => {
    if (!item || form.itemMode !== 'existing') {
        return;
    }

    form.item_code = item.code;
    form.item_name = item.name;
    form.item_unit = item.unit;
    form.base_price = Number(item.base_price);
});

watch(
    () => form.itemMode,
    (mode) => {
        if (mode === 'new') {
            form.item_id = '';
            form.item_code = '';
            form.item_name = '';
            form.item_unit = '';
            form.base_price = 0;
            form.initial_stock = 0;
            form.minimum_stock = 0;
        }
    },
);

const canSubmit = computed(() => {
    const hasProject = Number(form.project_id) > 0;
    const hasQty = Number(form.required_qty) > 0;

    if (form.itemMode === 'existing') {
        return hasProject && hasQty && Number(form.item_id) > 0;
    }

    return (
        hasProject &&
        hasQty &&
        form.item_name.trim().length > 0 &&
        form.item_unit.trim().length > 0 &&
        Number(form.base_price) >= 0
    );
});

const resolvePayload = (): MaterialFlowPayload => {
    const payload: MaterialFlowPayload = {
        project_id: Number(form.project_id),
        required_qty: Number(form.required_qty),
        auto_purchase: Boolean(form.auto_purchase),
    };

    if (form.document_ref.trim()) {
        payload.document_ref = form.document_ref.trim();
    }

    if (form.reference_note.trim()) {
        payload.reference_note = form.reference_note.trim();
    }

    if (form.itemMode === 'existing') {
        payload.item_id = Number(form.item_id);
    } else {
        if (form.item_code.trim()) {
            payload.item_code = form.item_code.trim();
        }

        payload.item_name = form.item_name.trim();
        payload.item_unit = form.item_unit.trim();
        payload.base_price = Number(form.base_price);
        payload.initial_stock = Number(form.initial_stock);
        payload.minimum_stock = Number(form.minimum_stock);
    }

    if (form.auto_purchase) {
        payload.purchase = {
            supplier_name: form.supplier_name.trim() || undefined,
            unit_price:
                Number(form.purchase_unit_price) > 0
                    ? Number(form.purchase_unit_price)
                    : undefined,
            qty:
                Number(form.purchase_qty) > 0
                    ? Number(form.purchase_qty)
                    : undefined,
            notes: form.purchase_notes.trim() || undefined,
        };
    }

    return payload;
};

const resetForm = (): void => {
    form.project_id = '';
    form.required_qty = 1;
    form.itemMode = 'existing';
    form.item_id = '';
    form.item_code = '';
    form.item_name = '';
    form.item_unit = '';
    form.base_price = 0;
    form.initial_stock = 0;
    form.minimum_stock = 0;
    form.auto_purchase = true;
    form.supplier_name = '';
    form.purchase_unit_price = 0;
    form.purchase_qty = 0;
    form.purchase_notes = '';
    form.document_ref = '';
    form.reference_note = '';
};

const submit = async (): Promise<void> => {
    submitError.value = '';
    successMessage.value = '';
    result.value = null;

    if (!session.value?.token) {
        submitError.value =
            'Silakan login terlebih dahulu untuk menjalankan material flow.';

        return;
    }

    if (!canSubmit.value) {
        submitError.value = 'Mohon lengkapi data wajib sebelum submit.';

        return;
    }

    isLoading.value = true;

    try {
        result.value = await materialFlowService.process(
            resolvePayload(),
            session.value.token,
        );
        successMessage.value =
            'Material flow berhasil diproses dan stok sudah diperbarui di backend.';
        resetForm();
    } catch (error) {
        if (error instanceof ApiClientError) {
            const firstFieldError = Object.values(error.errors)[0]?.[0];
            submitError.value = firstFieldError ?? error.message;
        } else {
            submitError.value =
                error instanceof Error
                    ? error.message
                    : 'Proses material flow gagal.';
        }
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <Head title="Material Flow" />

    <div class="space-y-6">
        <section
            class="rounded-3xl bg-white p-6 shadow-xl ring-1 ring-slate-200 lg:p-8"
        >
            <p
                class="text-xs font-semibold tracking-wider text-emerald-600 uppercase"
            >
                Ready Module
            </p>
            <h1
                class="mt-2 text-2xl font-semibold tracking-tight text-slate-800 lg:text-3xl"
            >
                Material Flow Processor
            </h1>
            <p class="mt-3 max-w-3xl text-sm leading-relaxed text-slate-600">
                Halaman ini terhubung langsung ke endpoint aktif
                /api/v1/material-flow/process dan mengikuti alur backend aktual:
                resolve item, cek stok, auto purchase, mutasi stok, dan update
                cost summary.
            </p>

            <div class="mt-5 grid gap-4 md:grid-cols-3">
                <article
                    class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                >
                    <p class="text-xs text-slate-500 uppercase">
                        Project Ready
                    </p>
                    <p class="mt-1 text-2xl font-semibold text-slate-800">
                        {{ projectOptions.length }}
                    </p>
                </article>

                <article
                    class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                >
                    <p class="text-xs text-slate-500 uppercase">Item Ready</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-800">
                        {{ itemOptions.length }}
                    </p>
                </article>

                <article
                    class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                >
                    <p class="text-xs text-slate-500 uppercase">Auth Session</p>
                    <p class="mt-1 text-sm font-semibold text-slate-800">
                        {{ session?.user?.name ?? 'Belum login' }}
                    </p>
                </article>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-3">
            <form
                class="space-y-5 rounded-3xl bg-white p-6 shadow-lg ring-1 ring-slate-200 xl:col-span-2"
                @submit.prevent="submit"
            >
                <h2 class="text-lg font-semibold text-slate-800">
                    Form Proses Material
                </h2>

                <div
                    v-if="submitError"
                    class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-600"
                >
                    {{ submitError }}
                </div>

                <div
                    v-if="successMessage"
                    class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700"
                >
                    {{ successMessage }}
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-slate-600"
                            >Project</label
                        >
                        <select
                            v-model="form.project_id"
                            class="h-11 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 transition outline-none focus:border-emerald-400 focus:bg-white"
                            required
                        >
                            <option value="">Pilih project</option>
                            <option
                                v-for="project in projectOptions"
                                :key="project.id"
                                :value="project.id"
                            >
                                {{ project.code }} - {{ project.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-slate-600"
                            >Required Qty</label
                        >
                        <input
                            v-model.number="form.required_qty"
                            type="number"
                            min="1"
                            class="h-11 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 transition outline-none focus:border-emerald-400 focus:bg-white"
                            required
                        />
                    </div>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                >
                    <p class="text-sm font-semibold text-slate-700">
                        Mode Material
                    </p>
                    <div class="mt-3 flex flex-wrap gap-4 text-sm">
                        <label class="inline-flex items-center gap-2">
                            <input
                                v-model="form.itemMode"
                                type="radio"
                                value="existing"
                                class="accent-emerald-600"
                            />
                            Pilih Item Existing
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input
                                v-model="form.itemMode"
                                type="radio"
                                value="new"
                                class="accent-emerald-600"
                            />
                            Daftarkan Item Baru
                        </label>
                    </div>

                    <div v-if="form.itemMode === 'existing'" class="mt-4">
                        <label
                            class="mb-2 block text-sm font-medium text-slate-600"
                            >Item Existing</label
                        >
                        <select
                            v-model="form.item_id"
                            class="h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 transition outline-none focus:border-emerald-400"
                        >
                            <option value="">Pilih item</option>
                            <option
                                v-for="item in itemOptions"
                                :key="item.id"
                                :value="item.id"
                            >
                                {{ item.code }} - {{ item.name }} (stok:
                                {{ item.current_stock }})
                            </option>
                        </select>
                    </div>

                    <div v-else class="mt-4 grid gap-4 md:grid-cols-2">
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-600"
                                >Kode Item</label
                            >
                            <input
                                v-model="form.item_code"
                                type="text"
                                class="h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 transition outline-none focus:border-emerald-400"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-600"
                                >Nama Item</label
                            >
                            <input
                                v-model="form.item_name"
                                type="text"
                                class="h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 transition outline-none focus:border-emerald-400"
                                required
                            />
                        </div>
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-600"
                                >Satuan</label
                            >
                            <input
                                v-model="form.item_unit"
                                type="text"
                                placeholder="pcs, kg, lembar"
                                class="h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 transition outline-none focus:border-emerald-400"
                                required
                            />
                        </div>
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-600"
                                >Base Price</label
                            >
                            <input
                                v-model.number="form.base_price"
                                type="number"
                                min="0"
                                class="h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 transition outline-none focus:border-emerald-400"
                                required
                            />
                        </div>
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-600"
                                >Initial Stock</label
                            >
                            <input
                                v-model.number="form.initial_stock"
                                type="number"
                                min="0"
                                class="h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 transition outline-none focus:border-emerald-400"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-600"
                                >Minimum Stock</label
                            >
                            <input
                                v-model.number="form.minimum_stock"
                                type="number"
                                min="0"
                                class="h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 transition outline-none focus:border-emerald-400"
                            />
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                >
                    <label
                        class="inline-flex items-center gap-2 text-sm font-medium text-slate-700"
                    >
                        <input
                            v-model="form.auto_purchase"
                            type="checkbox"
                            class="h-4 w-4 rounded accent-emerald-600"
                        />
                        Auto Purchase saat stok kurang
                    </label>

                    <div
                        v-if="form.auto_purchase"
                        class="mt-4 grid gap-4 md:grid-cols-2"
                    >
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-600"
                                >Supplier Name</label
                            >
                            <input
                                v-model="form.supplier_name"
                                type="text"
                                class="h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 transition outline-none focus:border-emerald-400"
                            />
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-600"
                                >Purchase Unit Price</label
                            >
                            <input
                                v-model.number="form.purchase_unit_price"
                                type="number"
                                min="0"
                                class="h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 transition outline-none focus:border-emerald-400"
                            />
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-600"
                                >Purchase Qty (opsional)</label
                            >
                            <input
                                v-model.number="form.purchase_qty"
                                type="number"
                                min="0"
                                class="h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 transition outline-none focus:border-emerald-400"
                            />
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-600"
                                >Purchase Note</label
                            >
                            <input
                                v-model="form.purchase_notes"
                                type="text"
                                class="h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 transition outline-none focus:border-emerald-400"
                            />
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-slate-600"
                            >Document Ref (opsional)</label
                        >
                        <input
                            v-model="form.document_ref"
                            type="text"
                            class="h-11 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 transition outline-none focus:border-emerald-400 focus:bg-white"
                        />
                    </div>
                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-slate-600"
                            >Reference Note (opsional)</label
                        >
                        <input
                            v-model="form.reference_note"
                            type="text"
                            class="h-11 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 transition outline-none focus:border-emerald-400 focus:bg-white"
                        />
                    </div>
                </div>

                <button
                    type="submit"
                    class="inline-flex h-12 items-center justify-center rounded-xl bg-emerald-600 px-5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-emerald-300"
                    :disabled="isLoading || !canSubmit"
                >
                    {{ isLoading ? 'Processing...' : 'Process Material Flow' }}
                </button>
            </form>

            <aside
                class="space-y-4 rounded-3xl bg-white p-6 shadow-lg ring-1 ring-slate-200"
            >
                <h2 class="text-lg font-semibold text-slate-800">
                    Flow Snapshot
                </h2>
                <div class="space-y-3 text-sm text-slate-700">
                    <p>1. Item resolve (existing/new)</p>
                    <p>2. Upsert project BOM</p>
                    <p>3. Stock check & shortage</p>
                    <p>4. Auto purchase (opsional)</p>
                    <p>5. Stock out + update project cost summary</p>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-xs leading-relaxed text-slate-600"
                >
                    Agar submit berhasil, project harus sudah ada di database
                    dan user harus memiliki token login aktif.
                </div>
            </aside>
        </section>

        <section
            v-if="result"
            class="rounded-3xl bg-white p-6 shadow-lg ring-1 ring-slate-200 lg:p-8"
        >
            <h2 class="text-xl font-semibold text-slate-800">
                Result Material Flow
            </h2>

            <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <article
                    class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                >
                    <p class="text-xs text-slate-500 uppercase">Project</p>
                    <p class="mt-1 text-sm font-semibold text-slate-800">
                        {{ result.project.code }} - {{ result.project.name }}
                    </p>
                </article>
                <article
                    class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                >
                    <p class="text-xs text-slate-500 uppercase">Item</p>
                    <p class="mt-1 text-sm font-semibold text-slate-800">
                        {{ result.item.code }} - {{ result.item.name }}
                    </p>
                </article>
                <article
                    class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                >
                    <p class="text-xs text-slate-500 uppercase">
                        Stock Before / After
                    </p>
                    <p class="mt-1 text-sm font-semibold text-slate-800">
                        {{ result.stock.stock_before }} →
                        {{ result.stock.stock_after }}
                    </p>
                </article>
                <article
                    class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                >
                    <p class="text-xs text-slate-500 uppercase">Gross Margin</p>
                    <p class="mt-1 text-sm font-semibold text-slate-800">
                        {{ result.project_cost_summary.gross_margin_percent }}%
                    </p>
                </article>
            </div>

            <div class="mt-6 grid gap-4 xl:grid-cols-2">
                <article
                    class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                >
                    <h3 class="text-sm font-semibold text-slate-800">
                        Flow Flags
                    </h3>
                    <div class="mt-3 space-y-1 text-sm text-slate-700">
                        <p>
                            Material registered:
                            {{ result.flow.material_registered ? 'Yes' : 'No' }}
                        </p>
                        <p>
                            Stock sufficient initially:
                            {{
                                result.flow.stock_sufficient_initially
                                    ? 'Yes'
                                    : 'No'
                            }}
                        </p>
                        <p>
                            Auto purchase used:
                            {{ result.flow.auto_purchase_used ? 'Yes' : 'No' }}
                        </p>
                    </div>
                </article>

                <article
                    class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                >
                    <h3 class="text-sm font-semibold text-slate-800">
                        Purchase Summary
                    </h3>
                    <div class="mt-3 space-y-1 text-sm text-slate-700">
                        <p>
                            Performed:
                            {{ result.purchase.performed ? 'Yes' : 'No' }}
                        </p>
                        <p>
                            PO Number:
                            {{
                                result.purchase.purchase_order?.po_number ?? '-'
                            }}
                        </p>
                        <p>Purchased Qty: {{ result.stock.purchased_qty }}</p>
                        <p>
                            Stock In Tx:
                            {{ result.purchase.stock_in_transaction_id ?? '-' }}
                        </p>
                    </div>
                </article>
            </div>
        </section>
    </div>
</template>
