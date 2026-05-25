<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import Badge from '@/Components/Badge.vue';
import { useToast } from '@/composables/useToast.js';
import axios from 'axios';
import {
    Search, Plus, Minus, Trash2, ShoppingCart, CreditCard,
    Smartphone, Banknote, Shield, Receipt, Download, Printer,
    CheckCircle, User, Phone, RefreshCw, FileText, Clock,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
dayjs.extend(relativeTime);

const toast = useToast();

// ── Search & Cart ──────────────────────────────────────────────────────────
const query = ref('');
const results = ref([]);
const searching = ref(false);
const cart = ref([]);
const discount = ref(0);
const discountType = ref('flat');   // flat | percent
const tax = ref(0);
const taxType = ref('flat');        // flat | percent

// ── Customer ───────────────────────────────────────────────────────────────
const customerName = ref('');
const customerPhone = ref('');

// ── Payment ────────────────────────────────────────────────────────────────
const paymentMethod = ref('cash');
const paymentMethods = [
    { value: 'cash',      label: 'Cash',      icon: Banknote,    color: 'green' },
    { value: 'upi',       label: 'UPI',       icon: Smartphone,  color: 'purple' },
    { value: 'card',      label: 'Card',      icon: CreditCard,  color: 'blue' },
    { value: 'insurance', label: 'Insurance', icon: Shield,      color: 'amber' },
];

// ── State ──────────────────────────────────────────────────────────────────
const loading = ref(false);
const completedSale = ref(null);
const showHistory = ref(false);
const historyList = ref([]);
const historyLoading = ref(false);
const activeTab = ref('sale'); // sale | history

// ── Search debounce ────────────────────────────────────────────────────────
let searchTimer;
const noResults = ref(false);

// Normalize: trim edges, collapse inner whitespace
function normalizeQuery(raw) {
    return raw.trim().replace(/\s+/g, ' ');
}

watch(query, (raw) => {
    clearTimeout(searchTimer);
    const q = normalizeQuery(raw);
    // Clear if nothing meaningful left
    if (q.length < 1) {
        results.value = [];
        noResults.value = false;
        searching.value = false;
        return;
    }
    searching.value = true;
    noResults.value = false;
    searchTimer = setTimeout(async () => {
        try {
            const { data } = await axios.get('/api/search/medicines', { params: { q } });
            results.value = data;
            noResults.value = data.length === 0;
        } catch {
            results.value = [];
            noResults.value = true;
        } finally {
            searching.value = false;
        }
    }, 250); // slightly faster debounce
});

// Highlight matched tokens inside a medicine name
function highlightMatch(name, raw) {
    const q = normalizeQuery(raw);
    if (!q) return name;
    const tokens = q.split(' ').filter(Boolean);
    let result = name;
    tokens.forEach(token => {
        const regex = new RegExp(`(${token.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
        result = result.replace(regex, '<mark>$1</mark>');
    });
    return result;
}

// ── Computed totals ────────────────────────────────────────────────────────
const subtotal = computed(() =>
    cart.value.reduce((sum, item) => sum + item.quantity * (item.estimated_price || 0), 0)
);
const discountAmount = computed(() => {
    const d = Number(discount.value) || 0;
    return discountType.value === 'percent' ? subtotal.value * (d / 100) : d;
});
const taxAmount = computed(() => {
    const t = Number(tax.value) || 0;
    return taxType.value === 'percent' ? (subtotal.value - discountAmount.value) * (t / 100) : t;
});
const total = computed(() => Math.max(0, subtotal.value - discountAmount.value + taxAmount.value));

// ── Cart operations ────────────────────────────────────────────────────────
function addMedicine(medicine) {
    const existing = cart.value.find(i => i.medicine_id === medicine.id);
    if (existing) {
        if (existing.quantity < medicine.stock) existing.quantity++;
        else toast.warning(`Only ${medicine.stock} units available for ${medicine.name}`);
    } else {
        cart.value.push({
            medicine_id: medicine.id,
            name: medicine.name,
            quantity: 1,
            stock: medicine.stock,
            estimated_price: medicine.selling_price || 0,
        });
    }
    query.value = '';
    results.value = [];
}

function removeItem(id) {
    cart.value = cart.value.filter(i => i.medicine_id !== id);
}

function incrementQty(item) {
    if (item.quantity < item.stock) item.quantity++;
    else toast.warning(`Max stock: ${item.stock}`);
}

// ── Checkout ───────────────────────────────────────────────────────────────
async function checkout() {
    if (!cart.value.length) return;
    loading.value = true;
    try {
        const { data } = await axios.post('/api/billing/checkout', {
            items: cart.value.map(i => ({ medicine_id: i.medicine_id, quantity: i.quantity })),
            discount: discountAmount.value,
            tax: taxAmount.value,
            customer_name: customerName.value || null,
            customer_phone: customerPhone.value || null,
            payment_method: paymentMethod.value,
        });
        completedSale.value = data;
        toast.success('Bill generated successfully! 🎉');
        // Auto-open PDF in new tab
        const saleId = data._id || data.id;
        setTimeout(() => {
            window.open(`/api/billing/${saleId}/invoice`, '_blank');
        }, 600);
    } catch (err) {
        const msg = err.response?.data?.message || err.response?.data?.errors?.items || 'Billing failed';
        toast.error(typeof msg === 'string' ? msg : Object.values(msg).flat().join(', '));
    } finally {
        loading.value = false;
    }
}

function newSale() {
    cart.value = [];
    discount.value = 0;
    tax.value = 0;
    customerName.value = '';
    customerPhone.value = '';
    paymentMethod.value = 'cash';
    completedSale.value = null;
}

async function downloadInvoice(saleId) {
    try {
        const res = await axios.get(`/api/billing/${saleId}/invoice`, { responseType: 'blob' });
        const url = URL.createObjectURL(new Blob([res.data], { type: 'application/pdf' }));
        const a = document.createElement('a');
        a.href = url; a.download = `invoice-${saleId}.pdf`; a.click();
        URL.revokeObjectURL(url);
        toast.success('Invoice downloaded!');
    } catch { toast.error('Failed to download invoice'); }
}

async function downloadReceipt(saleId) {
    try {
        const res = await axios.get(`/api/billing/${saleId}/receipt`, { responseType: 'blob' });
        const url = URL.createObjectURL(new Blob([res.data], { type: 'application/pdf' }));
        const a = document.createElement('a');
        a.href = url; a.download = `receipt-${saleId}.pdf`; a.click();
        URL.revokeObjectURL(url);
        toast.success('Receipt downloaded!');
    } catch { toast.error('Failed to download receipt'); }
}

async function loadHistory() {
    historyLoading.value = true;
    try {
        const { data } = await axios.get('/api/billing/history');
        historyList.value = data.data || data;
    } catch { toast.error('Could not load history'); }
    finally { historyLoading.value = false; }
}

function switchTab(tab) {
    activeTab.value = tab;
    if (tab === 'history' && !historyList.value.length) loadHistory();
}

function money(v) {
    return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(v || 0);
}

function paymentIcon(method) {
    return paymentMethods.find(p => p.value === method)?.icon || Banknote;
}

function paymentLabel(method) {
    return paymentMethods.find(p => p.value === method)?.label || method;
}
</script>

<template>
    <AppLayout>
        <template #title>
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg" style="background: linear-gradient(135deg,#10b981,#059669)">
                    <Receipt class="h-4 w-4 text-white" />
                </div>
                <div>
                    <h1 class="text-xl font-bold" style="color: var(--text-primary);">Patient Billing</h1>
                    <p class="text-xs" style="color: var(--text-muted);">Generate bills for patients · FIFO expiry-safe stock</p>
                </div>
            </div>
        </template>

        <!-- Tab Switcher -->
        <div class="flex gap-2 mb-5">
            <button
                class="btn btn-sm"
                :class="activeTab === 'sale' ? 'btn-primary' : 'btn-secondary'"
                @click="switchTab('sale')"
            >
                <ShoppingCart class="h-3.5 w-3.5" /> New Bill
            </button>
            <button
                class="btn btn-sm"
                :class="activeTab === 'history' ? 'btn-primary' : 'btn-secondary'"
                @click="switchTab('history')"
            >
                <Clock class="h-3.5 w-3.5" /> Bill History
            </button>
        </div>

        <!-- ── NEW SALE TAB ──────────────────────────────────────────── -->
        <div v-if="activeTab === 'sale'">

            <!-- SUCCESS STATE -->
            <div v-if="completedSale" class="animate-fade-in-up">
                <div class="ph-card p-8 text-center max-w-lg mx-auto">
                    <!-- Flat success icon -->
                    <div class="flex h-20 w-20 items-center justify-center rounded-full mx-auto mb-5"
                         style="background: var(--brand-primary); color: #001E2B;">
                        <CheckCircle class="h-10 w-10" />
                    </div>
                    <h2 class="text-3xl font-black mb-1" style="color: var(--text-primary);">Bill Generated!</h2>
                    <p class="text-sm mb-1" style="color: var(--text-muted);">Bill #{{ (completedSale._id || completedSale.id || '').slice(-8).toUpperCase() }}</p>
                    <p class="text-4xl font-black mt-4 mb-2" style="color: var(--text-primary);">
                        {{ money(completedSale.total_amount) }}
                    </p>
                    <p class="text-sm mb-4" style="color: var(--text-muted);">The bill PDF has been opened in a new tab automatically.</p>
                    <div v-if="completedSale.customer_name" class="mb-5 px-4 py-3 rounded-xl text-sm" style="background: var(--surface-muted); border: 1px solid var(--surface-border);">
                        <span class="font-semibold">{{ completedSale.customer_name }}</span>
                        <span v-if="completedSale.customer_phone" class="ml-2" style="color: var(--text-muted);">{{ completedSale.customer_phone }}</span>
                    </div>
                    <div class="flex gap-3 justify-center flex-wrap">
                        <button class="btn btn-primary" @click="downloadInvoice(completedSale._id || completedSale.id)">
                            <Download class="h-4 w-4" /> Download Bill
                        </button>
                        <button class="btn btn-secondary" @click="downloadReceipt(completedSale._id || completedSale.id)">
                            <Printer class="h-4 w-4" /> Print Receipt
                        </button>
                        <button class="btn btn-secondary" @click="newSale">
                            <RefreshCw class="h-4 w-4" /> New Sale
                        </button>
                    </div>
                </div>
            </div>

            <!-- SALE FORM -->
            <div v-else class="grid gap-5 xl:grid-cols-[1fr_380px]">

                <!-- Left: Cart Panel -->
                <div class="flex flex-col gap-4">

                    <!-- Search -->
                    <div class="ph-card">
                        <div class="ph-card-header">
                            <h2>Add Medicines</h2>
                        </div>
                        <div class="p-4">
                            <div class="relative">
                                <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4" style="color: var(--text-muted);" />
                                <input
                                    v-model="query"
                                    class="ph-input pl-10"
                                    placeholder="Type medicine name to search..."
                                    autocomplete="off"
                                />
                            </div>

                            <!-- Search results -->
                            <div v-if="results.length || searching || noResults"
                                 class="mt-2 rounded-xl border overflow-hidden"
                                 style="border-color: var(--surface-border); background: var(--surface-card);">

                                <!-- Searching spinner -->
                                <div v-if="searching" class="px-4 py-3 text-xs flex items-center gap-2"
                                     style="color: var(--text-muted);">
                                    <svg class="animate-spin h-3.5 w-3.5" viewBox="0 0 24 24" fill="none">
                                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity="0.25"/>
                                        <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                                    </svg>
                                    Searching...
                                </div>

                                <!-- No results -->
                                <div v-else-if="noResults" class="px-4 py-3 text-xs"
                                     style="color: var(--text-muted);">
                                    No medicines found for
                                    <strong style="color: var(--text-primary);">"{{ normalizeQuery(query) }}"</strong>
                                </div>

                                <!-- Result rows -->
                                <button
                                    v-for="med in results"
                                    :key="med.id"
                                    class="flex w-full items-center justify-between px-4 py-2.5 text-sm transition-colors"
                                    style="border-bottom: 1px solid var(--surface-border);"
                                    :class="med.stock === 0
                                        ? 'opacity-40 cursor-not-allowed'
                                        : 'cursor-pointer'"
                                    :style="med.stock > 0 ? { '--hover': 'var(--surface-muted)' } : {}"
                                    type="button"
                                    :disabled="med.stock === 0"
                                    @click="addMedicine(med)"
                                    @mouseenter="e => { if (med.stock > 0) e.currentTarget.style.background = 'var(--surface-muted)'; }"
                                    @mouseleave="e => { e.currentTarget.style.background = ''; }"
                                >
                                    <span
                                        class="font-medium text-left"
                                        style="color: var(--text-primary);"
                                        v-html="highlightMatch(med.name, query)"
                                    ></span>
                                    <span class="badge shrink-0 ml-3"
                                          :class="med.stock === 0 ? 'badge-red' : med.stock <= 10 ? 'badge-amber' : 'badge-green'">
                                        {{ med.stock === 0 ? 'Out of stock' : med.stock + ' units' }}
                                    </span>
                                </button>
                            </div>

                        </div>
                    </div>

                    <!-- Cart Table -->
                    <div class="ph-card">
                        <div class="ph-card-header">
                            <h2>Cart</h2>
                            <span v-if="cart.length" class="badge badge-blue">{{ cart.length }} item{{ cart.length !== 1 ? 's' : '' }}</span>
                        </div>
                        <div v-if="!cart.length" class="py-14 text-center">
                            <ShoppingCart class="h-10 w-10 mx-auto mb-3 opacity-20" />
                            <p class="text-sm" style="color: var(--text-muted);">Cart is empty. Search for medicines above.</p>
                        </div>
                        <div v-else class="overflow-x-auto">
                            <table class="ph-table">
                                <thead>
                                    <tr>
                                        <th>Medicine</th>
                                        <th>Quantity</th>
                                        <th class="text-right">Stock</th>
                                        <th class="text-right">Est. Price</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in cart" :key="item.medicine_id" class="animate-fade-in-up">
                                        <td>
                                            <div class="font-semibold text-sm" style="color: var(--text-primary);">{{ item.name }}</div>
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-1.5">
                                                <button class="btn btn-icon btn-secondary btn-sm" @click="item.quantity = Math.max(1, item.quantity - 1)">
                                                    <Minus class="h-3.5 w-3.5" />
                                                </button>
                                                <input
                                                    v-model.number="item.quantity"
                                                    class="h-8 w-14 rounded-lg border text-center text-sm font-medium"
                                                    style="border-color: var(--surface-border); background: var(--surface-card); color: var(--text-primary);"
                                                    type="number" min="1" :max="item.stock"
                                                />
                                                <button class="btn btn-icon btn-secondary btn-sm" @click="incrementQty(item)">
                                                    <Plus class="h-3.5 w-3.5" />
                                                </button>
                                            </div>
                                        </td>
                                        <td class="text-right">
                                            <span class="badge" :class="item.stock <= 5 ? 'badge-red' : item.stock <= 15 ? 'badge-amber' : 'badge-green'">
                                                {{ item.stock }}
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            <input
                                                v-model.number="item.estimated_price"
                                                class="h-8 w-24 rounded-lg border text-right text-sm font-medium ml-auto"
                                                style="border-color: var(--surface-border); background: var(--surface-card); color: var(--text-primary);"
                                                type="number" min="0" step="0.01"
                                                placeholder="₹ rate"
                                            />
                                        </td>
                                        <td>
                                            <button class="btn btn-icon btn-danger btn-sm" @click="removeItem(item.medicine_id)">
                                                <Trash2 class="h-3.5 w-3.5" />
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Customer Info -->
                    <div class="ph-card">
                        <div class="ph-card-header">
                            <h2>Customer Info <span class="text-xs font-normal ml-1" style="color: var(--text-muted);">(optional)</span></h2>
                        </div>
                        <div class="p-4 grid grid-cols-2 gap-3">
                            <label class="block">
                                <span class="text-xs font-medium mb-1 block" style="color: var(--text-secondary);">
                                    <User class="inline h-3 w-3 mr-1" />Customer Name
                                </span>
                                <input v-model="customerName" class="ph-input" placeholder="Full name" />
                            </label>
                            <label class="block">
                                <span class="text-xs font-medium mb-1 block" style="color: var(--text-secondary);">
                                    <Phone class="inline h-3 w-3 mr-1" />Phone Number
                                </span>
                                <input v-model="customerPhone" class="ph-input" placeholder="+91 00000 00000" type="tel" />
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Right: Invoice Summary -->
                <aside class="flex flex-col gap-4">

                    <!-- Payment Method -->
                    <div class="ph-card p-4">
                        <h2 class="text-sm font-semibold mb-3" style="color: var(--text-primary);">Payment Method</h2>
                        <div class="grid grid-cols-2 gap-2">
                            <button
                                v-for="pm in paymentMethods"
                                :key="pm.value"
                                class="flex flex-col items-center gap-1.5 rounded-xl p-3 border-2 transition-all text-sm font-medium"
                                :class="paymentMethod === pm.value
                                    ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300'
                                    : 'border-transparent hover:border-neutral-200 dark:hover:border-neutral-700'"
                                :style="paymentMethod !== pm.value ? { background: 'var(--surface-muted)' } : {}"
                                @click="paymentMethod = pm.value"
                            >
                                <component :is="pm.icon" class="h-5 w-5" />
                                {{ pm.label }}
                            </button>
                        </div>
                    </div>

                    <!-- Discounts & Tax -->
                    <div class="ph-card p-4">
                        <h2 class="text-sm font-semibold mb-3" style="color: var(--text-primary);">Adjustments</h2>
                        <div class="space-y-3">
                            <div>
                                <label class="text-xs font-medium block mb-1" style="color: var(--text-secondary);">Discount</label>
                                <div class="flex gap-2">
                                    <input v-model.number="discount" class="ph-input flex-1" type="number" min="0" placeholder="0" />
                                    <select v-model="discountType" class="ph-input w-24 px-2">
                                        <option value="flat">₹ Flat</option>
                                        <option value="percent">% Off</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="text-xs font-medium block mb-1" style="color: var(--text-secondary);">Tax / GST</label>
                                <div class="flex gap-2">
                                    <input v-model.number="tax" class="ph-input flex-1" type="number" min="0" placeholder="0" />
                                    <select v-model="taxType" class="ph-input w-24 px-2">
                                        <option value="flat">₹ Flat</option>
                                        <option value="percent">%</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="ph-card overflow-hidden border-t-4" style="border-top-color: var(--brand-primary);">
                        <div class="p-5">
                            <h2 class="text-lg font-bold mb-4" style="color: var(--text-primary);">Bill Summary</h2>
                            <div class="space-y-3 pb-4 border-b" style="border-color: var(--surface-border);">
                                <div class="flex justify-between text-sm">
                                    <span style="color: var(--text-secondary);">Items ({{ cart.length }}):</span>
                                    <span class="font-medium" style="color: var(--text-primary);">{{ money(subtotal) }}</span>
                                </div>
                                <div v-if="discountAmount > 0" class="flex justify-between text-sm">
                                    <span style="color: var(--text-secondary);">Discount:</span>
                                    <span class="font-semibold" style="color: var(--brand-rose);">- {{ money(discountAmount) }}</span>
                                </div>
                                <div v-if="taxAmount > 0" class="flex justify-between text-sm">
                                    <span style="color: var(--text-secondary);">Estimated Tax/GST:</span>
                                    <span class="font-medium" style="color: var(--text-primary);">{{ money(taxAmount) }}</span>
                                </div>
                            </div>
                            <div class="flex justify-between items-center py-4 text-lg font-black" style="color: var(--brand-rose);">
                                <span>Bill Total:</span>
                                <span>{{ money(total) }}</span>
                            </div>
                            <div class="text-right mb-4">
                                <span class="badge text-xs" style="background:var(--surface-muted); color:var(--text-secondary); border:1px solid var(--surface-border);">
                                    Paying via {{ paymentLabel(paymentMethod) }}
                                </span>
                            </div>
                            <button
                                class="btn btn-primary w-full h-10 text-sm tracking-wide"
                                :disabled="!cart.length || loading"
                                style="border-radius: 8px;"
                                @click="checkout"
                            >
                                <span v-if="loading">Processing...</span>
                                <span v-else class="flex items-center justify-center gap-2">
                                    <CheckCircle class="h-4 w-4" /> Generate Bill
                                </span>
                            </button>
                        </div>
                    </div>
                </aside>
            </div>
        </div>

        <!-- ── HISTORY TAB ─────────────────────────────────────────────── -->
        <div v-else-if="activeTab === 'history'">
            <div class="ph-card">
                <div class="ph-card-header">
                    <h2>Recent Sales</h2>
                    <button class="btn btn-sm btn-secondary" @click="loadHistory">
                        <RefreshCw class="h-3.5 w-3.5" />
                    </button>
                </div>
                <div v-if="historyLoading" class="py-16 text-center text-sm" style="color: var(--text-muted);">Loading...</div>
                <div v-else-if="!historyList.length" class="py-16 text-center">
                    <FileText class="h-10 w-10 mx-auto mb-3 opacity-20" />
                    <p class="text-sm" style="color: var(--text-muted);">No sales found.</p>
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="ph-table">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Customer</th>
                                <th>Payment</th>
                                <th>Date</th>
                                <th class="text-right">Amount</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="sale in historyList" :key="sale._id || sale.id">
                                <td class="font-mono text-xs font-semibold">
                                    #{{ (sale._id || sale.id || '').slice(-8).toUpperCase() }}
                                </td>
                                <td>{{ sale.customer_name || 'Walk-in' }}</td>
                                <td>
                                    <span class="badge" :class="{
                                        'badge-green': sale.payment_method === 'cash',
                                        'badge-purple': sale.payment_method === 'upi',
                                        'badge-blue': sale.payment_method === 'card',
                                        'badge-amber': sale.payment_method === 'insurance',
                                        'badge-gray': !sale.payment_method,
                                    }">{{ (sale.payment_method || 'cash').toUpperCase() }}</span>
                                </td>
                                <td class="text-xs" style="color: var(--text-muted);">
                                    {{ sale.created_at ? dayjs(sale.created_at).format('DD MMM YYYY HH:mm') : '—' }}
                                </td>
                                <td class="text-right font-semibold">{{ money(sale.total_amount) }}</td>
                                <td>
                                    <div class="flex gap-1 justify-end">
                                        <button class="btn btn-icon btn-sm btn-secondary" title="Download Invoice"
                                            @click="downloadInvoice(sale._id || sale.id)">
                                            <Download class="h-3.5 w-3.5" />
                                        </button>
                                        <button class="btn btn-icon btn-sm btn-secondary" title="Print Receipt"
                                            @click="downloadReceipt(sale._id || sale.id)">
                                            <Printer class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
