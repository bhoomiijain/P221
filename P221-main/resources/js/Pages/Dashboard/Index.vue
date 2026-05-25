<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import SparklineChart from '@/Components/SparklineChart.vue';
import { router } from '@inertiajs/vue3';
import {
    DollarSign, Boxes, Clock, ArrowRight,
    ShoppingCart, TrendingUp,
    Banknote, MoreHorizontal, Edit, Trash, Search, ChevronDown, ChevronLeft, ChevronRight
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    metrics:      Object,
    topMedicines: Array,
    lowStock:     Array,
    last7Days:    Array,
    recentSales:  Array,
    stockHealth:  Object,
    pagination:   Object,
    filters:      Object,
});

function money(value) {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value || 0);
}

// ── Revenue period filter ──────────────────────────────────────────
const period  = ref(props.filters?.period || 'weekly');
const searchQ = ref(props.filters?.search || '');
let searchTimer = null;

watch(period, (val) => {
    router.get('/', { period: val, search: searchQ.value, page: 1 }, { preserveState: true, replace: true });
});

function doSearch() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        router.get('/', { period: period.value, search: searchQ.value, page: 1 }, { preserveState: true, replace: true });
    }, 350);
}

function goPage(p) {
    router.get('/', { period: period.value, search: searchQ.value, page: p }, { preserveState: true, replace: true });
}

// ── Chart data ─────────────────────────────────────────────────────
const revenue7DayLabels = computed(() => props.last7Days?.map(d => d.date) || []);
const revenue7DayValues = computed(() => props.last7Days?.map(d => d.total) || []);

const revenueLineData = computed(() => ({
    labels: revenue7DayLabels.value,
    datasets: [{
        label: 'Revenue',
        data: revenue7DayValues.value,
        backgroundColor: 'rgba(56, 178, 172, 0.1)',
        borderColor: '#38b2ac',
        borderWidth: 2,
        fill: true,
        tension: 0.4,
        pointBackgroundColor: '#fff',
        pointBorderColor: '#38b2ac',
        pointBorderWidth: 2,
        pointRadius: 4,
    }],
}));

const topMedLabels = computed(() => props.topMedicines?.map(m => m.name) || []);
const topMedValues = computed(() => props.topMedicines?.map(m => m.total_sold || 0) || []);

const topMedLineData = computed(() => ({
    labels: topMedLabels.value,
    datasets: [{
        label: 'Units Sold',
        data: topMedValues.value,
        backgroundColor: 'rgba(253, 164, 175, 0.1)',
        borderColor: '#fda4af',
        borderWidth: 2,
        fill: true,
        tension: 0.4,
        pointBackgroundColor: '#fff',
        pointBorderColor: '#fda4af',
        pointBorderWidth: 2,
        pointRadius: 4,
    }],
}));

const commonLineOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false }, tooltip: { mode: 'index', backgroundColor: '#0f172a' } },
    scales: {
        x: { ticks: { color: '#94a3b8', font: { size: 11 } }, grid: { display: false } },
        y: { ticks: { color: '#94a3b8', font: { size: 11 } }, grid: { color: '#f1f5f9', borderDash: [5, 5] } },
    },
};

// ── Pagination helpers ─────────────────────────────────────────────
const pag = computed(() => props.pagination || { current_page: 1, total_pages: 1, per_page: 10, total: 0 });
const pageRange = computed(() => {
    const pages = [];
    for (let i = 1; i <= pag.value.total_pages; i++) pages.push(i);
    return pages.slice(Math.max(0, pag.value.current_page - 3), pag.value.current_page + 2);
});

function statusColor(status) {
    return status === 'payment_received'
        ? 'bg-emerald-100 text-emerald-700'
        : 'bg-orange-100 text-orange-700';
}
</script>

<template>
    <AppLayout>
        <!-- ── Metrics ──────────────────────────────────────────────────────── -->
        <div class="grid gap-4 sm:grid-cols-3 mb-8">
            <div class="ph-card p-5 flex items-center gap-4">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50">
                    <DollarSign class="h-5 w-5 text-emerald-600" />
                </div>
                <div>
                    <div class="text-xs text-gray-500 font-semibold">Today's Revenue</div>
                    <div class="text-2xl font-black" style="color: var(--text-primary);">{{ money(metrics.todaySales) }}</div>
                </div>
            </div>
            <div class="ph-card p-5 flex items-center gap-4">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50">
                    <Boxes class="h-5 w-5 text-blue-600" />
                </div>
                <div>
                    <div class="text-xs text-gray-500 font-semibold">Total Orders</div>
                    <div class="text-2xl font-black" style="color: var(--text-primary);">{{ metrics.totalOrders || 0 }}</div>
                </div>
            </div>
            <div class="ph-card p-5 flex items-center gap-4">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-orange-50">
                    <Clock class="h-5 w-5 text-orange-500" />
                </div>
                <div>
                    <div class="text-xs text-gray-500 font-semibold">Pending Orders</div>
                    <div class="text-2xl font-black" style="color: var(--text-primary);">{{ metrics.pendingCount || 0 }}</div>
                </div>
            </div>
        </div>

        <!-- ── Top Charts Row ────────────────────────────────────────────────── -->
        <section class="grid gap-6 lg:grid-cols-3 mb-8">

            <!-- Revenue Chart -->
            <div class="ph-card lg:col-span-2 flex flex-col">
                <div class="p-6 pb-2 flex justify-between items-start">
                    <div>
                        <h2 class="text-lg font-bold" style="color: var(--text-primary);">Total Revenue</h2>
                        <div class="text-3xl font-black mt-2" style="color: var(--text-primary);">
                            {{ money(metrics.todaySales) }}
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-[#38b2ac]"></div>
                            <span class="text-xs font-semibold text-gray-500">Revenue</span>
                        </div>
                        <select
                            v-model="period"
                            class="text-xs border rounded-full px-3 py-1 bg-white shadow-sm outline-none cursor-pointer"
                        >
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                </div>
                <div class="p-4 flex-1">
                    <SparklineChart
                        v-if="revenue7DayLabels.length"
                        type="line"
                        :data="revenueLineData"
                        :options="commonLineOptions"
                        :height="220"
                    />
                    <div v-else class="h-full flex items-center justify-center text-sm" style="color: var(--text-muted);">
                        No sales data yet
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="flex flex-col gap-6">
                <!-- Top Medicines -->
                <div class="ph-card flex-1 p-6 flex flex-col">
                    <div class="flex justify-between items-start mb-3">
                        <h2 class="font-bold" style="color: var(--text-primary);">Top Medicines</h2>
                    </div>
                    <div v-if="topMedicines?.length" class="space-y-3 flex-1">
                        <div v-for="med in topMedicines" :key="med.name" class="flex items-center justify-between">
                            <span class="text-xs font-semibold truncate max-w-[140px]" style="color: var(--text-secondary);">{{ med.name }}</span>
                            <span class="text-xs font-black px-2 py-0.5 rounded-full bg-pink-50 text-pink-600">{{ med.total_sold }} units</span>
                        </div>
                    </div>
                    <div v-else class="flex-1 flex items-center justify-center text-xs text-gray-400">
                        No fulfilled orders yet
                    </div>
                </div>

                <!-- Pending Count -->
                <div class="ph-card flex-1 p-6 flex flex-col">
                    <div class="flex justify-between items-start mb-4">
                        <h2 class="font-bold" style="color: var(--text-primary);">Awaiting Payment</h2>
                    </div>
                    <div class="text-3xl font-black mb-1 text-orange-400">{{ metrics.pendingCount }}</div>
                    <span class="text-xs text-orange-400 bg-orange-50 px-2 py-0.5 rounded-md font-semibold inline-block self-start">Pending approval</span>
                </div>
            </div>

        </section>

        <!-- ── Recent Orders Table ────────────────────────────────────────────────── -->
        <section class="ph-card p-6">
            <div class="flex justify-between items-center mb-6 flex-wrap gap-4">
                <h2 class="text-xl font-bold" style="color: var(--text-primary);">Recent Orders</h2>
                <div class="flex gap-3">
                    <div class="relative">
                        <input
                            v-model="searchQ"
                            @input="doSearch"
                            type="text"
                            placeholder="Search by pharmacist..."
                            class="border rounded-full pl-8 pr-4 py-1.5 text-sm w-52 outline-none focus:border-[#38b2ac] transition-colors"
                        />
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-gray-400" />
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-xs text-gray-400 border-b">
                            <th class="py-3 font-semibold">Pharmacist</th>
                            <th class="py-3 font-semibold">Medicine</th>
                            <th class="py-3 font-semibold">Payment</th>
                            <th class="py-3 font-semibold">Total</th>
                            <th class="py-3 font-semibold">Status</th>
                            <th class="py-3 font-semibold">Date</th>
                        </tr>
                    </thead>
                    <tbody v-if="recentSales?.length">
                        <tr v-for="sale in recentSales" :key="sale.id" class="border-b last:border-0 hover:bg-gray-50/50 transition-colors">
                            <td class="py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-xs">
                                        {{ (sale.customer_name || 'U')[0].toUpperCase() }}
                                    </div>
                                    <span class="font-semibold text-sm">{{ sale.customer_name }}</span>
                                </div>
                            </td>
                            <td class="py-4">
                                <span class="text-sm text-gray-600">{{ sale.medicine_name }}</span>
                            </td>
                            <td class="py-4">
                                <span class="text-sm text-gray-600 capitalize">{{ sale.payment_method }}</span>
                            </td>
                            <td class="py-4">
                                <span class="text-sm font-semibold">{{ money(sale.total_amount) }}</span>
                            </td>
                            <td class="py-4">
                                <span :class="['px-2 py-0.5 rounded-full text-xs font-semibold', statusColor(sale.status)]">
                                    {{ sale.status === 'payment_received' ? 'Fulfilled' : 'Pending' }}
                                </span>
                            </td>
                            <td class="py-4">
                                <span class="text-sm text-gray-500">{{ sale.created_at }}</span>
                            </td>
                        </tr>
                    </tbody>
                    <tbody v-else>
                        <tr>
                            <td colspan="6" class="py-12 text-center text-gray-400 text-sm">
                                {{ searchQ ? 'No orders matched your search.' : 'No orders found.' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex justify-between items-center mt-6 flex-wrap gap-3">
                <span class="text-xs text-gray-400">
                    Showing {{ Math.min((pag.current_page - 1) * pag.per_page + 1, pag.total) }}–{{ Math.min(pag.current_page * pag.per_page, pag.total) }}
                    of {{ pag.total }} entries
                </span>
                <div class="flex gap-1.5 items-center">
                    <button
                        @click="goPage(pag.current_page - 1)"
                        :disabled="pag.current_page <= 1"
                        class="w-8 h-8 rounded-full border text-sm text-gray-500 hover:bg-gray-50 disabled:opacity-30 flex items-center justify-center"
                    >
                        <ChevronLeft class="h-3.5 w-3.5" />
                    </button>

                    <template v-for="p in pageRange" :key="p">
                        <button
                            @click="goPage(p)"
                            :class="[
                                'w-8 h-8 rounded-full text-sm font-semibold border',
                                p === pag.current_page
                                    ? 'bg-[#38b2ac] text-white border-[#38b2ac] shadow-sm'
                                    : 'text-gray-500 hover:bg-gray-50'
                            ]"
                        >{{ p }}</button>
                    </template>

                    <button
                        @click="goPage(pag.current_page + 1)"
                        :disabled="pag.current_page >= pag.total_pages"
                        class="w-8 h-8 rounded-full border text-sm text-gray-500 hover:bg-gray-50 disabled:opacity-30 flex items-center justify-center"
                    >
                        <ChevronRight class="h-3.5 w-3.5" />
                    </button>
                </div>
            </div>
        </section>

    </AppLayout>
</template>
