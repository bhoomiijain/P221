<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useToast } from '@/composables/useToast.js';
import { router } from '@inertiajs/vue3';
import { Truck, CheckCircle, Clock, XCircle, Package, ChevronDown, ChevronUp } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({ orders: Array });
const toast = useToast();
const processing = ref(null);
const expandedOrders = ref(new Set());

function money(val) {
    return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(val || 0);
}

function toggleExpand(id) {
    if (expandedOrders.value.has(id)) {
        expandedOrders.value.delete(id);
    } else {
        expandedOrders.value.add(id);
    }
}

function approveOrder(order) {
    processing.value = order._id || order.id;
    router.post(`/supplier/orders/${order._id || order.id}/approve`, {}, {
        onSuccess: () => {
            toast.success('Order approved! Stock dispatched to pharmacist.');
            processing.value = null;
        },
        onError: (err) => {
            toast.error(Object.values(err)[0] || 'Failed to approve order.');
            processing.value = null;
        }
    });
}

function getItems(order) {
    // Multi-item
    if (order.items && order.items.length) return order.items;
    // Legacy single item
    if (order.medicine_id) {
        return [{ medicine_name: order.medicine?.name ?? 'Unknown', quantity: order.quantity, unit_price: order.total_price / (order.quantity || 1), line_total: order.total_price }];
    }
    return [];
}
</script>

<template>
    <AppLayout>
        <template #title>
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg" style="background: linear-gradient(135deg,#10b981,#059669)">
                    <Truck class="h-4 w-4 text-white" />
                </div>
                <div>
                    <h1 class="text-xl font-bold" style="color: var(--text-primary);">Incoming Orders</h1>
                    <p class="text-xs" style="color: var(--text-muted);">Review and fulfill requests from pharmacists.</p>
                </div>
            </div>
        </template>

        <div v-if="!orders?.length" class="ph-card py-20 text-center">
            <Truck class="h-12 w-12 mx-auto mb-4 opacity-20" />
            <p class="text-sm" style="color: var(--text-muted);">No orders received yet.</p>
        </div>

        <div v-else class="space-y-4">
            <div
                v-for="order in orders"
                :key="order._id || order.id"
                class="ph-card overflow-hidden"
            >
                <!-- Order header row -->
                <div class="flex items-center gap-4 p-5 flex-wrap">
                    <!-- Pharmacist avatar -->
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <div class="h-10 w-10 rounded-full flex items-center justify-center text-white font-bold text-sm shrink-0"
                             :style="{ background: 'linear-gradient(135deg,#6366f1,#8b5cf6)' }">
                            <img v-if="order.pharmacist?.avatar_url" :src="order.pharmacist.avatar_url"
                                 class="h-10 w-10 rounded-full object-cover" />
                            <span v-else>{{ (order.pharmacist?.name || 'P')[0].toUpperCase() }}</span>
                        </div>
                        <div class="min-w-0">
                            <div class="font-semibold text-sm" style="color: var(--text-primary);">
                                {{ order.pharmacist?.name || 'Unknown Pharmacist' }}
                            </div>
                            <div class="text-xs" style="color: var(--text-muted);">
                                {{ new Date(order.created_at).toLocaleDateString('en-IN', { day:'numeric', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' }) }}
                            </div>
                        </div>
                    </div>

                    <!-- Items summary -->
                    <div class="shrink-0 text-center">
                        <div class="text-xs font-semibold" style="color: var(--text-muted);">Items</div>
                        <div class="text-lg font-black" style="color: var(--text-primary);">{{ getItems(order).length }}</div>
                    </div>

                    <!-- Total -->
                    <div class="shrink-0 text-center">
                        <div class="text-xs font-semibold" style="color: var(--text-muted);">Total (Cash)</div>
                        <div class="text-lg font-black" style="color: var(--brand-primary);">{{ money(order.total_price) }}</div>
                    </div>

                    <!-- Status -->
                    <div class="shrink-0">
                        <span v-if="order.status === 'pending'"
                              class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold"
                              style="background: #fff7ed; color: #c2410c;">
                            <Clock class="h-3 w-3" /> Pending
                        </span>
                        <span v-else-if="order.status === 'payment_received'"
                              class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold"
                              style="background: #f0fdf4; color: #166534;">
                            <CheckCircle class="h-3 w-3" /> Fulfilled
                        </span>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2 shrink-0">
                        <button
                            class="btn btn-icon btn-sm btn-secondary"
                            :title="expandedOrders.has(order._id || order.id) ? 'Collapse' : 'View items'"
                            @click="toggleExpand(order._id || order.id)"
                        >
                            <ChevronUp v-if="expandedOrders.has(order._id || order.id)" class="h-3.5 w-3.5" />
                            <ChevronDown v-else class="h-3.5 w-3.5" />
                        </button>
                        <button
                            v-if="order.status === 'pending'"
                            class="btn btn-primary btn-sm flex items-center gap-2"
                            :disabled="processing === (order._id || order.id)"
                            @click="approveOrder(order)"
                        >
                            <CheckCircle class="h-3.5 w-3.5" />
                            {{ processing === (order._id || order.id) ? 'Processing...' : 'Mark as Fulfilled' }}
                        </button>
                    </div>
                </div>

                <!-- Expanded items table -->
                <div v-if="expandedOrders.has(order._id || order.id)"
                     class="border-t overflow-x-auto" style="border-color: var(--surface-border);">
                    <table class="ph-table">
                        <thead>
                            <tr>
                                <th>Medicine</th>
                                <th class="text-right">Qty</th>
                                <th class="text-right">Unit Price</th>
                                <th class="text-right">Line Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, i) in getItems(order)" :key="i">
                                <td class="font-semibold" style="color: var(--text-primary);">
                                    <div class="flex items-center gap-2">
                                        <Package class="h-3.5 w-3.5 opacity-40" />
                                        {{ item.medicine_name || item.medicine?.name || '—' }}
                                    </div>
                                </td>
                                <td class="text-right">{{ item.quantity }}</td>
                                <td class="text-right">{{ money(item.unit_price) }}</td>
                                <td class="text-right font-bold" style="color: var(--brand-primary);">{{ money(item.line_total) }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr style="background: var(--surface-muted);">
                                <td colspan="3" class="text-right font-bold text-sm" style="color: var(--text-primary);">Grand Total (Cash on Delivery)</td>
                                <td class="text-right font-black" style="color: var(--brand-primary);">{{ money(order.total_price) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
