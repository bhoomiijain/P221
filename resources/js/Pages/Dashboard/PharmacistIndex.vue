<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import SparklineChart from '@/Components/SparklineChart.vue';
import { Link } from '@inertiajs/vue3';
import {
    Link2, Banknote, Search, AlertTriangle, ArrowUpRight, CheckCircle2, ChevronRight, X, HeartPulse
} from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps({
    metrics: Object,
    last7Days: Array,
    recentSales: Array,
    lowestStockMedicine: Object,
    expiringChart: Array,
    topCategories: Array,
});

function money(value) {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 0 }).format(value || 0);
}

const revenue7DayValues = computed(() => props.last7Days?.map(d => d.total) || []);
const revenue7DayLabels = computed(() => props.last7Days?.map(d => d.date)  || []);

const revenueLineData = computed(() => ({
    labels: revenue7DayLabels.value,
    datasets: [{
        label: 'Revenue',
        data: revenue7DayValues.value,
        backgroundColor: 'rgba(56, 178, 172, 0.1)',
        borderColor: '#38b2ac',
        borderWidth: 3,
        fill: true,
        tension: 0.4,
        pointBackgroundColor: '#38b2ac',
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
        pointRadius: 6,
    }],
}));

const expiringBarData = computed(() => ({
    labels: ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5', 'Day 6', 'Day 7'],
    datasets: [{
        label: 'Items Expiring',
        data: props.expiringChart || [0,0,0,0,0,0,0],
        backgroundColor: (context) => {
            const ctx = context.chart.ctx;
            const index = context.dataIndex;
            return '#e2e8f0'; 
        },
        hoverBackgroundColor: '#0f4a47',
        borderRadius: 4,
        barThickness: 12,
    }],
}));

const commonLineOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false }, tooltip: { mode: 'index', backgroundColor: '#0f172a' } },
    scales: {
        x: { ticks: { color: '#94a3b8', font: { size: 11 } }, grid: { display: false } },
        y: { ticks: { color: '#94a3b8', font: { size: 11 } }, grid: { color: '#f1f5f9', borderDash: [5, 5] }, border: { display: false } },
    },
};

const commonBarOptions = {
    ...commonLineOptions,
    scales: {
        x: { ticks: { color: '#94a3b8', font: { size: 11 } }, grid: { display: false } },
        y: { display: false },
    }
};
</script>

<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto py-6">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Pharmacii Dashboard</h1>
            </div>

            <!-- Top Row Cards -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                
                <!-- Prescriptions Card -->
                <div class="bg-white rounded-[2rem] p-8 shadow-sm flex flex-col">
                    <div class="flex justify-between items-start mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full border flex items-center justify-center text-gray-600 bg-gray-50">
                                <Link2 class="h-5 w-5" />
                            </div>
                            <span class="font-bold text-gray-800 text-lg">Orders</span>
                        </div>
                    </div>
                    
                    <div class="mb-8">
                        <div class="flex items-baseline gap-2">
                            <span class="text-5xl font-black text-gray-900">{{ metrics.totalOrders || 0 }}</span>
                            <span class="text-gray-500 font-semibold">Total</span>
                        </div>
                    </div>

                    <div class="flex-1 flex flex-col justify-end space-y-4">
                        <div v-for="cat in topCategories" :key="cat.name">
                            <div class="flex justify-between text-sm font-bold text-gray-600 mb-1">
                                <span>{{ cat.name }}</span>
                                <span>{{ cat.percentage }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-1.5">
                                <div class="bg-[#38b2ac] h-1.5 rounded-full transition-all" :style="{ width: cat.percentage + '%' }"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Revenue Card -->
                <div class="bg-white rounded-[2rem] p-8 shadow-sm flex flex-col">
                    <div class="flex justify-between items-start mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full border flex items-center justify-center text-gray-600 bg-gray-50">
                                <Banknote class="h-5 w-5" />
                            </div>
                            <span class="font-bold text-gray-800 text-lg">Revenue</span>
                        </div>
                        <div class="text-xl font-bold text-gray-900 border-b-4 border-[#ff6b2b] pb-1">
                            {{ money(metrics.totalSales) }}
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <div class="flex items-baseline gap-2">
                            <span class="text-5xl font-black text-gray-900">{{ metrics.todaySales || 0 }}</span>
                            <span class="text-gray-500 font-semibold uppercase">Today</span>
                        </div>
                    </div>

                    <div class="flex-1 mt-auto">
                        <SparklineChart
                            type="line"
                            :data="revenueLineData"
                            :options="commonLineOptions"
                            :height="120"
                        />
                    </div>
                </div>

                <!-- Low Stocks Image Card -->
                <div class="rounded-[2rem] p-8 shadow-sm relative overflow-hidden bg-[#0d342f] text-white flex flex-col justify-between" style="min-height: 380px;">
                    <!-- Background Image -->
                    <div class="absolute inset-0 z-0">
                        <img :src="lowestStockMedicine?.image || '/images/low_stock_pills.png'" class="w-full h-full object-cover opacity-80 mix-blend-luminosity" alt="Pills" />
                        <div class="absolute inset-0 bg-gradient-to-t from-[#0d342f] via-transparent to-[#0d342f]/50"></div>
                    </div>
                    
                    <div class="relative z-10 flex justify-between items-start">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center border border-white/20">
                                <AlertTriangle class="h-5 w-5 text-white" v-if="metrics.lowStockCount > 0" />
                                <HeartPulse class="h-5 w-5 text-white" v-else />
                            </div>
                            <span class="font-bold text-xl" v-if="metrics.lowStockCount > 0">{{ metrics.lowStockCount }} Low stocks</span>
                            <span class="font-bold text-xl" v-else>Healthy Stock</span>
                        </div>
                    </div>

                    <div class="relative z-10 bg-white/20 backdrop-blur-xl border border-white/30 rounded-2xl p-5 mt-auto" v-if="lowestStockMedicine">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="font-bold text-xl tracking-tight">{{ lowestStockMedicine.name }}</h3>
                            <span class="bg-[#ff6b2b] text-white text-xs px-3 py-1 rounded-full font-bold" v-if="lowestStockMedicine.stock < 10">Critical</span>
                            <span class="bg-[#38b2ac] text-white text-xs px-3 py-1 rounded-full font-bold" v-else>Low</span>
                        </div>
                        <div class="flex items-center gap-4 text-sm text-white/80 font-medium mb-3">
                            <span>Stock: {{ lowestStockMedicine.stock }}</span>
                            <span>Category: {{ lowestStockMedicine.category }}</span>
                        </div>
                        <div class="w-full bg-white/20 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-[#ff6b2b] h-1.5 rounded-full" :style="{ width: Math.min((lowestStockMedicine.stock / 100) * 100, 100) + '%' }"></div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Bottom Row Cards -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Expiring Soon -->
                <div class="bg-white rounded-[2rem] p-8 shadow-sm flex flex-col">
                    <div class="flex justify-between items-start mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full border flex items-center justify-center text-gray-600 bg-gray-50">
                                <span class="font-bold text-lg">*</span>
                            </div>
                            <span class="font-bold text-gray-800 text-lg">Expiring 7 Days</span>
                        </div>
                    </div>

                    <div class="flex-1 relative">
                        <SparklineChart
                            type="bar"
                            :data="expiringBarData"
                            :options="commonBarOptions"
                            :height="140"
                        />
                    </div>

                    <div class="flex justify-between items-baseline mt-6 pt-4 border-t">
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-black text-gray-900">{{ metrics.expiringCount || 0 }}</span> <span class="text-sm font-semibold text-gray-500">Total Expiring</span>
                        </div>
                        <div class="flex items-baseline gap-1 text-[#0f4a47]">
                            <span class="text-xl font-black">{{ money(metrics.lostAmount) }}</span> <span class="text-xs font-semibold">Lost</span>
                        </div>
                    </div>
                </div>

                <!-- Recent Prescriptions Table -->
                <div class="bg-white rounded-[2rem] p-8 shadow-sm lg:col-span-2 flex flex-col">
                    <div class="flex justify-between items-start mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full border flex items-center justify-center text-gray-600 bg-gray-50">
                                <span class="font-bold text-lg">rx</span>
                            </div>
                            <span class="font-bold text-gray-800 text-lg">Recent orders</span>
                        </div>
                    </div>

                    <div class="flex-1 overflow-auto">
                        <table class="w-full text-left border-separate border-spacing-y-4">
                            <tbody>
                                <tr v-if="!recentSales?.length">
                                    <td class="text-center text-gray-500 py-4">No recent sales recorded.</td>
                                </tr>
                                <tr v-else v-for="(sale, index) in recentSales" :key="sale.id">
                                    <td class="w-12 py-2">
                                        <img :src="`https://i.pravatar.cc/100?img=${index + 10}`" class="w-10 h-10 rounded-full object-cover" />
                                    </td>
                                    <td class="py-2">
                                        <div class="font-bold text-gray-900">{{ sale.customer_name || 'Walk-in Customer' }}</div>
                                        <div class="text-xs font-semibold text-gray-400 mt-0.5">Walk-in Customer</div>
                                    </td>
                                    <td class="py-2">
                                        <div class="font-bold text-gray-900 uppercase">{{ sale.payment_method }} {{ money(sale.total_amount) }}</div>
                                        <div class="text-xs font-semibold text-gray-400 mt-0.5">Cashier: {{ sale.cashier }}</div>
                                    </td>
                                    <td class="py-2">
                                        <div class="font-bold text-gray-700">RX-{{ sale.id.substring(0, 6).toUpperCase() }}</div>
                                        <div class="text-xs font-semibold text-gray-400 mt-0.5">{{ sale.created_at }}</div>
                                    </td>
                                    <td class="py-2 text-right">
                                        <span class="text-xs font-bold text-blue-500 bg-blue-500/10 px-3 py-1 rounded-md">Filled</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
