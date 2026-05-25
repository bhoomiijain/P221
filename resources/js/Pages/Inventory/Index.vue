<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import Badge from '@/Components/Badge.vue';
import { useToast } from '@/composables/useToast.js';
import axios from 'axios';
import { router } from '@inertiajs/vue3';
import { Boxes, Plus, Search, AlertTriangle, Clock, Percent, TrendingUp, DollarSign, Save } from 'lucide-vue-next';
import { reactive, ref, watch, computed } from 'vue';
import dayjs from 'dayjs';

const props = defineProps({
    batches: Array,
    lowStock: Array,
    expiring: Array,
    filters: Object,
    defaultProfitPct: { type: Number, default: 0 },
});
const toast = useToast();

// ── Global Profit % ──────────────────────────────────────────────────────────
const globalProfit     = ref(props.defaultProfitPct);
const savingGlobal     = ref(false);

async function saveGlobalProfit() {
    savingGlobal.value = true;
    try {
        await axios.put('/api/user/profit-settings', { default_profit_pct: globalProfit.value });
        toast.success(`Global profit set to ${globalProfit.value}%`);
    } catch (err) {
        toast.error(err.response?.data?.message || 'Failed to update profit setting');
    } finally {
        savingGlobal.value = false;
    }
}

// ── Search (server-side, debounced) ──────────────────────────────────────────
const searchQuery    = ref(props.filters?.search ?? '');
let   searchDebounce = null;

watch(searchQuery, (val) => {
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(() => {
        router.get('/inventory', { search: val }, {
            preserveState: true,
            replace: true,
        });
    }, 350);
});

// ── Batch table with inline profit editing ───────────────────────────────────
// Local reactive copy of batches so we can edit inline without page reload
const localBatches = ref((props.batches || []).map(b => ({
    ...b,
    profit_pct:    parseFloat(b.profit_pct    ?? 0),
    purchase_price: parseFloat(b.purchase_price ?? 0),
    selling_price:  parseFloat(b.selling_price  ?? 0),
    _saving: false,
})));

watch(() => props.batches, (newVal) => {
    localBatches.value = (newVal || []).map(b => ({
        ...b,
        profit_pct:     parseFloat(b.profit_pct    ?? 0),
        purchase_price: parseFloat(b.purchase_price ?? 0),
        selling_price:  parseFloat(b.selling_price  ?? 0),
        _saving: false,
    }));
});

// Compute preview selling price as user types in profit field
function computedSelling(batch) {
    const pct   = parseFloat(batch.profit_pct) || 0;
    const cost  = parseFloat(batch.purchase_price) || 0;
    return (cost * (1 + pct / 100)).toFixed(2);
}

async function saveBatchProfit(batch) {
    batch._saving = true;
    try {
        const res = await axios.put(`/api/batches/${batch._id || batch.id}`, {
            profit_pct: parseFloat(batch.profit_pct) || 0,
        });
        batch.selling_price  = parseFloat(res.data.selling_price ?? 0);
        batch.profit_pct     = parseFloat(res.data.profit_pct    ?? 0);
        toast.success('Profit updated!');
    } catch (err) {
        toast.error(err.response?.data?.message || 'Failed to update profit');
    } finally {
        batch._saving = false;
    }
}

// ── Add Batch Modal ───────────────────────────────────────────────────────────
const showAddBatchModal = ref(false);
const saving = ref(false);
const batchForm = reactive({
    medicine_id:    '',
    batch_number:   '',
    expiry_date:    '',
    quantity:       1,
    purchase_price: '',
    profit_pct:     globalProfit.value,
});
const medicines = ref([]);

// Computed preview selling price in the modal
const modalSellingPreview = computed(() => {
    const cost = parseFloat(batchForm.purchase_price) || 0;
    const pct  = parseFloat(batchForm.profit_pct) || 0;
    return (cost * (1 + pct / 100)).toFixed(2);
});

async function openAddBatch() {
    try {
        const medRes = await axios.get('/api/medicines');
        medicines.value = medRes.data?.data || medRes.data || [];
    } catch {}
    Object.assign(batchForm, {
        medicine_id:    '',
        batch_number:   '',
        expiry_date:    '',
        quantity:       1,
        purchase_price: '',
        profit_pct:     globalProfit.value,
    });
    showAddBatchModal.value = true;
}

async function saveBatch() {
    if (!batchForm.medicine_id || !batchForm.batch_number || !batchForm.expiry_date) {
        toast.error('Medicine, batch number, and expiry date are required');
        return;
    }
    saving.value = true;
    try {
        await axios.post('/api/batches', {
            medicine_id:    batchForm.medicine_id,
            batch_number:   batchForm.batch_number,
            expiry_date:    batchForm.expiry_date,
            quantity:       batchForm.quantity,
            purchase_price: batchForm.purchase_price,
            profit_pct:     batchForm.profit_pct,
        });
        toast.success('Batch added successfully!');
        showAddBatchModal.value = false;
        setTimeout(() => window.location.reload(), 700);
    } catch (err) {
        toast.error(err.response?.data?.message || 'Failed to add batch');
    } finally {
        saving.value = false;
    }
}

// ── Expiry helpers ────────────────────────────────────────────────────────────
function expiryVariant(expiryStr) {
    if (!expiryStr) return 'gray';
    const days = dayjs(expiryStr).diff(dayjs(), 'day');
    if (days < 0)   return 'red';
    if (days <= 7)  return 'red';
    if (days <= 30) return 'amber';
    return 'green';
}

function expiryLabel(expiryStr) {
    if (!expiryStr) return '—';
    const days = dayjs(expiryStr).diff(dayjs(), 'day');
    if (days < 0)   return 'Expired';
    if (days === 0) return 'Today!';
    if (days <= 30) return `${days}d left`;
    return dayjs(expiryStr).format('DD MMM YYYY');
}
</script>

<template>
    <AppLayout>
        <template #title>
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg" style="background: linear-gradient(135deg,#f59e0b,#d97706)">
                    <Boxes class="h-4 w-4 text-white" />
                </div>
                <div>
                    <h1 class="text-xl font-bold" style="color: var(--text-primary);">Inventory</h1>
                    <p class="text-xs" style="color: var(--text-muted);">Batch-level stock · Expiry tracking · Profit management</p>
                </div>
            </div>
        </template>

        <!-- ── Global Profit % Banner ─────────────────────────────────────────── -->
        <div class="ph-card mb-5 border-l-4" style="border-left-color: #6366f1;">
            <div class="flex flex-wrap items-center gap-4 p-4">
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg" style="background: linear-gradient(135deg,#6366f1,#8b5cf6);">
                        <Percent class="h-4 w-4 text-white" />
                    </div>
                    <div>
                        <p class="text-xs font-bold" style="color: var(--text-muted);">GLOBAL DEFAULT PROFIT</p>
                        <p class="text-xs" style="color: var(--text-muted);">Applied to all new batches you add</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-1">
                    <div class="relative">
                        <input
                            v-model.number="globalProfit"
                            type="number"
                            min="0"
                            max="10000"
                            step="0.5"
                            class="ph-input text-center font-bold"
                            style="width: 110px; padding-right: 2rem;"
                            @keyup.enter="saveGlobalProfit"
                        />
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm font-bold" style="color: var(--text-muted);">%</span>
                    </div>
                    <button
                        class="btn btn-primary btn-sm flex items-center gap-1.5"
                        :disabled="savingGlobal"
                        @click="saveGlobalProfit"
                    >
                        <Save class="h-3.5 w-3.5" />
                        {{ savingGlobal ? 'Saving...' : 'Save Default' }}
                    </button>
                    <span class="text-xs px-3 py-1.5 rounded-full font-medium" style="background: rgba(99,102,241,0.1); color: #6366f1;">
                        New batches → <strong>{{ globalProfit }}% profit</strong>
                    </span>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between gap-4 mb-5 flex-wrap">
            <div class="relative flex-1 max-w-sm">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4" style="color: var(--text-muted);" />
                <input
                    v-model="searchQuery"
                    class="ph-input pl-10"
                    placeholder="Search medicine or batch..."
                />
            </div>
            <div class="flex items-center gap-2">
                <span v-if="searchQuery" class="text-xs font-medium px-2 py-1 rounded-full"
                      style="background: rgba(59,130,246,0.1); color: #2563eb;">
                    {{ localBatches?.length ?? 0 }} result{{ (localBatches?.length ?? 0) !== 1 ? 's' : '' }} for "{{ searchQuery }}"
                </span>
                <button class="btn btn-primary btn-sm" @click="openAddBatch">
                    <Plus class="h-3.5 w-3.5" /> Add Batch
                </button>
            </div>
        </div>

        <section class="grid gap-5 lg:grid-cols-3">

            <!-- Batches Table -->
            <div class="ph-card lg:col-span-2">
                <div class="ph-card-header">
                    <h2>All Batches</h2>
                    <span class="badge badge-blue">{{ localBatches?.length ?? 0 }}</span>
                </div>
                <div v-if="!localBatches?.length" class="py-12 text-center">
                    <Boxes class="h-10 w-10 mx-auto mb-3 opacity-20" />
                    <p class="text-sm" style="color: var(--text-muted);">
                        {{ searchQuery ? `No batches matching "${searchQuery}"` : 'No batches found' }}
                    </p>
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="ph-table">
                        <thead>
                            <tr>
                                <th>Medicine</th>
                                <th>Batch #</th>
                                <th>Expiry</th>
                                <th class="text-right">Qty</th>
                                <th class="text-right">Cost Price</th>
                                <th class="text-center">Profit %</th>
                                <th class="text-right">Sell Price</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="batch in localBatches" :key="batch._id || batch.id">
                                <td class="font-semibold" style="color: var(--text-primary);">{{ batch.medicine?.name || '—' }}</td>
                                <td class="font-mono text-xs">{{ batch.batch_number }}</td>
                                <td>
                                    <div class="text-sm">{{ batch.expiry_date ? dayjs(batch.expiry_date).format('DD MMM YYYY') : '—' }}</div>
                                </td>
                                <td class="text-right">
                                    <span class="badge" :class="batch.quantity === 0 ? 'badge-red' : batch.quantity <= 10 ? 'badge-amber' : 'badge-green'">
                                        {{ batch.quantity }}
                                    </span>
                                </td>
                                <!-- Cost Price -->
                                <td class="text-right text-sm" style="color: var(--text-muted);">
                                    ₹{{ Number(batch.purchase_price || 0).toFixed(2) }}
                                </td>
                                <!-- Inline editable Profit % -->
                                <td class="text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <div class="relative" style="width: 80px;">
                                            <input
                                                v-model.number="batch.profit_pct"
                                                type="number"
                                                min="0"
                                                step="0.5"
                                                class="ph-input text-center text-xs font-bold"
                                                style="height: 28px; padding: 0 1.5rem 0 0.5rem; font-size: 0.75rem;"
                                                @keyup.enter="saveBatchProfit(batch)"
                                                @blur="saveBatchProfit(batch)"
                                                :title="`Preview: ₹${computedSelling(batch)}`"
                                            />
                                            <span class="absolute right-1.5 top-1/2 -translate-y-1/2 text-xs" style="color: var(--text-muted);">%</span>
                                        </div>
                                        <span v-if="batch._saving" class="text-xs" style="color: var(--text-muted);">...</span>
                                    </div>
                                </td>
                                <!-- Sell Price (computed from profit) -->
                                <td class="text-right font-semibold" style="color: #10b981;">
                                    ₹{{ computedSelling(batch) }}
                                </td>
                                <td>
                                    <span class="badge" :class="`badge-${expiryVariant(batch.expiry_date)}`">
                                        {{ expiryLabel(batch.expiry_date) }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="px-5 py-2 text-xs" style="color: var(--text-muted);">
                        💡 Edit the <strong>Profit %</strong> field directly — Selling Price updates automatically on save.
                    </p>
                </div>
            </div>

            <!-- Side Panels -->
            <div class="space-y-4">

                <!-- Low Stock -->
                <div class="ph-card">
                    <div class="ph-card-header">
                        <h2 class="flex items-center gap-2">
                            <AlertTriangle class="h-4 w-4 text-amber-500" />
                            Low Stock
                        </h2>
                        <span v-if="lowStock?.length" class="badge badge-amber">{{ lowStock.length }}</span>
                    </div>
                    <div v-if="!lowStock?.length" class="px-5 py-8 text-center text-sm" style="color: var(--text-muted);">
                        All stocks healthy ✓
                    </div>
                    <div v-else class="divide-y" style="border-color: var(--surface-border);">
                        <div v-for="item in lowStock" :key="item.id" class="px-5 py-3">
                            <div class="flex justify-between mb-1.5">
                                <span class="text-sm font-medium" style="color: var(--text-primary);">{{ item.name }}</span>
                                <span class="badge" :class="item.stock === 0 ? 'badge-red' : 'badge-amber'">{{ item.stock }}</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill"
                                    :style="{
                                        width: Math.min(100, (item.stock / 10) * 100) + '%',
                                        background: item.stock === 0 ? '#ff2d55' : '#ff9500',
                                        boxShadow: item.stock === 0 ? '0 0 8px #ff2d55' : '0 0 8px #ff9500',
                                    }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Expiring Soon -->
                <div class="ph-card">
                    <div class="ph-card-header">
                        <h2 class="flex items-center gap-2">
                            <Clock class="h-4 w-4 text-red-500" />
                            Expiring Soon
                        </h2>
                        <span v-if="expiring?.length" class="badge badge-red">{{ expiring.length }}</span>
                    </div>
                    <div v-if="!expiring?.length" class="px-5 py-8 text-center text-sm" style="color: var(--text-muted);">
                        No expiring batches ✓
                    </div>
                    <div v-else class="divide-y" style="border-color: var(--surface-border);">
                        <div v-for="batch in expiring" :key="batch._id || batch.id" class="px-5 py-3 text-sm">
                            <div class="font-medium" style="color: var(--text-primary);">{{ batch.medicine?.name }}</div>
                            <div class="flex items-center justify-between mt-1">
                                <span class="text-xs font-mono" style="color: var(--text-muted);">{{ batch.batch_number }}</span>
                                <span class="badge badge-amber text-xs">{{ expiryLabel(batch.expiry_date) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profit Summary Card -->
                <div class="ph-card" style="border-top: 3px solid #10b981;">
                    <div class="ph-card-header">
                        <h2 class="flex items-center gap-2">
                            <TrendingUp class="h-4 w-4 text-emerald-500" />
                            Profit Overview
                        </h2>
                    </div>
                    <div class="p-4 space-y-3">
                        <div v-if="!localBatches?.length" class="text-center text-sm py-4" style="color: var(--text-muted);">
                            No batches to summarize
                        </div>
                        <template v-else>
                            <div class="flex justify-between text-sm">
                                <span style="color: var(--text-muted);">Avg Profit %</span>
                                <span class="font-bold" style="color: #6366f1;">
                                    {{ (localBatches.reduce((s, b) => s + parseFloat(b.profit_pct || 0), 0) / localBatches.length).toFixed(1) }}%
                                </span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span style="color: var(--text-muted);">Total Stock Value (Cost)</span>
                                <span class="font-bold" style="color: var(--text-primary);">
                                    ₹{{ localBatches.reduce((s, b) => s + (parseFloat(b.purchase_price || 0) * parseInt(b.quantity || 0)), 0).toFixed(2) }}
                                </span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span style="color: var(--text-muted);">Total Stock Value (MRP)</span>
                                <span class="font-bold text-emerald-500">
                                    ₹{{ localBatches.reduce((s, b) => s + (parseFloat(computedSelling(b)) * parseInt(b.quantity || 0)), 0).toFixed(2) }}
                                </span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </section>

        <!-- Add Batch Modal -->
        <Modal :show="showAddBatchModal" title="Add New Batch" size="lg" @close="showAddBatchModal = false">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs font-semibold mb-1.5" style="color: var(--text-secondary);">Medicine *</label>
                    <select v-model="batchForm.medicine_id" class="ph-input">
                        <option value="">Select medicine...</option>
                        <option v-for="m in medicines" :key="m._id || m.id" :value="m._id || m.id">{{ m.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5" style="color: var(--text-secondary);">Batch Number *</label>
                    <input v-model="batchForm.batch_number" class="ph-input" placeholder="e.g. BT-2024-001" />
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5" style="color: var(--text-secondary);">Expiry Date *</label>
                    <input v-model="batchForm.expiry_date" class="ph-input" type="date" />
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5" style="color: var(--text-secondary);">Quantity</label>
                    <input v-model.number="batchForm.quantity" class="ph-input" type="number" min="1" />
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5" style="color: var(--text-secondary);">Cost Price / Unit (₹)</label>
                    <input v-model="batchForm.purchase_price" class="ph-input" type="number" min="0" step="0.01" placeholder="0.00" />
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5" style="color: var(--text-secondary);">
                        Profit % <span class="font-normal text-xs" style="color: var(--text-muted);">(global default: {{ globalProfit }}%)</span>
                    </label>
                    <div class="relative">
                        <input v-model.number="batchForm.profit_pct" class="ph-input pr-8" type="number" min="0" step="0.5" placeholder="0" />
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm font-bold" style="color: var(--text-muted);">%</span>
                    </div>
                </div>
                <!-- Selling Price Preview -->
                <div class="col-span-2 rounded-xl p-3" style="background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.2);">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <DollarSign class="h-4 w-4 text-emerald-500" />
                            <span class="text-sm font-semibold" style="color: var(--text-primary);">Calculated Selling Price</span>
                        </div>
                        <span class="text-xl font-bold text-emerald-500">₹{{ modalSellingPreview }}</span>
                    </div>
                    <p class="text-xs mt-1" style="color: var(--text-muted);">
                        ₹{{ batchForm.purchase_price || '0.00' }} × (1 + {{ batchForm.profit_pct || 0 }}%) = ₹{{ modalSellingPreview }} per unit
                    </p>
                </div>
            </div>
            <template #footer>
                <button class="btn btn-secondary" @click="showAddBatchModal = false">Cancel</button>
                <button class="btn btn-primary" :disabled="saving" @click="saveBatch">
                    {{ saving ? 'Adding...' : 'Add Batch' }}
                </button>
            </template>
        </Modal>
    </AppLayout>
</template>
