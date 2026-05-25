<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useToast } from '@/composables/useToast.js';
import axios from 'axios';
import {
    ShoppingBag, Download, Printer, RefreshCw, TrendingUp,
    Calendar, CreditCard, Search, Filter, ChevronLeft, ChevronRight,
    Banknote, Smartphone, Shield,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import dayjs from 'dayjs';

const props = defineProps({
    sales: Object,
    totalRevenue: Number,
});

const toast = useToast();
const searchQuery = ref('');

function money(value) {
    return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(value || 0);
}

async function downloadInvoice(saleId) {
    try {
        const res = await axios.get(`/api/billing/${saleId}/invoice`, { responseType: 'blob' });
        const url = URL.createObjectURL(new Blob([res.data], { type: 'application/pdf' }));
        const a = document.createElement('a');
        a.href = url; a.download = `invoice-${saleId}.pdf`; a.click();
        URL.revokeObjectURL(url);
        toast.success('Invoice downloaded!');
    } catch { toast.error('Failed to download'); }
}

async function downloadReceipt(saleId) {
    try {
        const res = await axios.get(`/api/billing/${saleId}/receipt`, { responseType: 'blob' });
        const url = URL.createObjectURL(new Blob([res.data], { type: 'application/pdf' }));
        const a = document.createElement('a');
        a.href = url; a.download = `receipt-${saleId}.pdf`; a.click();
        URL.revokeObjectURL(url);
        toast.success('Receipt downloaded!');
    } catch { toast.error('Failed to download'); }
}

const sales = computed(() => props.sales?.data || []);

const paymentBadge = {
    cash:      'badge-green',
    upi:       'badge-purple',
    card:      'badge-blue',
    insurance: 'badge-amber',
};
</script>

<template>
    <AppLayout>
        <template #title>
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg" style="background: linear-gradient(135deg,#8b5cf6,#6d28d9)">
                    <ShoppingBag class="h-4 w-4 text-white" />
                </div>
                <div>
                    <h1 class="text-xl font-bold" style="color: var(--text-primary);">Sales History</h1>
                    <p class="text-xs" style="color: var(--text-muted);">All transactions · Download invoices · Re-print receipts</p>
                </div>
            </div>
        </template>

        <!-- Summary bar -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <div class="ph-card p-4">
                <div class="text-xs mb-1" style="color: var(--text-muted);">Total Revenue</div>
                <div class="text-xl font-bold" style="color: var(--brand-primary);">{{ money(totalRevenue) }}</div>
            </div>
            <div class="ph-card p-4">
                <div class="text-xs mb-1" style="color: var(--text-muted);">Total Sales</div>
                <div class="text-xl font-bold" style="color: var(--text-primary);">{{ props.sales?.total || 0 }}</div>
            </div>
        </div>

        <!-- Sales Table -->
        <div class="ph-card">
            <div class="ph-card-header">
                <h2>All Transactions</h2>
                <div class="relative">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4" style="color: var(--text-muted);" />
                    <input v-model="searchQuery" class="ph-input pl-9 h-8 text-sm w-48" placeholder="Search..." />
                </div>
            </div>
            <div v-if="!sales.length" class="py-20 text-center">
                <ShoppingBag class="h-12 w-12 mx-auto mb-4 opacity-20" />
                <p class="text-sm" style="color: var(--text-muted);">No sales recorded yet</p>
            </div>
            <div v-else class="overflow-x-auto">
                <table class="ph-table">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Payment</th>
                            <th>Cashier</th>
                            <th>Date & Time</th>
                            <th class="text-right">Amount</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="sale in sales" :key="sale._id || sale.id">
                            <td class="font-mono text-xs font-bold" style="color: var(--brand-primary);">
                                #{{ (sale._id || sale.id || '').slice(-8).toUpperCase() }}
                            </td>
                            <td>
                                <div class="font-medium text-sm" style="color: var(--text-primary);">
                                    {{ sale.customer_name || 'Walk-in' }}
                                </div>
                                <div v-if="sale.customer_phone" class="text-xs" style="color: var(--text-muted);">
                                    {{ sale.customer_phone }}
                                </div>
                            </td>
                            <td class="text-sm" style="color: var(--text-secondary);">
                                {{ (sale.items || []).length }} item(s)
                            </td>
                            <td>
                                <span class="badge" :class="paymentBadge[sale.payment_method] || 'badge-gray'">
                                    {{ (sale.payment_method || 'cash').toUpperCase() }}
                                </span>
                            </td>
                            <td class="text-sm" style="color: var(--text-secondary);">
                                {{ sale.user?.name || '—' }}
                            </td>
                            <td class="text-xs" style="color: var(--text-muted);">
                                {{ sale.created_at ? dayjs(sale.created_at).format('DD MMM YYYY HH:mm') : '—' }}
                            </td>
                            <td class="text-right font-bold" style="color: var(--text-primary);">
                                {{ money(sale.total_amount) }}
                            </td>
                            <td>
                                <div class="flex gap-1 justify-end">
                                    <button
                                        class="btn btn-icon btn-sm btn-secondary"
                                        title="Download Invoice PDF"
                                        @click="downloadInvoice(sale._id || sale.id)"
                                    >
                                        <Download class="h-3.5 w-3.5" />
                                    </button>
                                    <button
                                        class="btn btn-icon btn-sm btn-secondary"
                                        title="Print Receipt"
                                        @click="downloadReceipt(sale._id || sale.id)"
                                    >
                                        <Printer class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="props.sales?.last_page > 1" class="flex items-center justify-between px-5 py-3 border-t" style="border-color: var(--surface-border);">
                <span class="text-xs" style="color: var(--text-muted);">
                    Page {{ props.sales.current_page }} of {{ props.sales.last_page }}
                </span>
                <div class="flex gap-2">
                    <a :href="props.sales.prev_page_url" class="btn btn-sm btn-secondary" :class="!props.sales.prev_page_url ? 'opacity-40 pointer-events-none' : ''">
                        <ChevronLeft class="h-3.5 w-3.5" /> Prev
                    </a>
                    <a :href="props.sales.next_page_url" class="btn btn-sm btn-secondary" :class="!props.sales.next_page_url ? 'opacity-40 pointer-events-none' : ''">
                        Next <ChevronRight class="h-3.5 w-3.5" />
                    </a>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
