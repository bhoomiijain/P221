<script setup>
import { Head, Link } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { useCustomerApi } from '@/composables/useCustomerApi';
import { Check } from 'lucide-vue-next';

const props = defineProps({ order: Object, steps: Array });
const api = useCustomerApi();

async function cancel() {
    if (!confirm('Cancel this order?')) return;
    await api.cancelOrder(props.order.id || props.order._id);
    window.location.reload();
}
</script>

<template>
    <Head :title="`Track ${order.order_number}`" />
    <CustomerLayout>
        <div class="max-w-2xl mx-auto px-4 py-8">
            <Link href="/shop/orders" class="text-sm text-teal-600">← Orders</Link>
            <h1 class="text-2xl font-bold mt-4">Track Order</h1>
            <p class="text-slate-500">{{ order.order_number }}</p>
            <div class="ph-card p-8 mt-8">
                <div v-for="(s, i) in steps" :key="s.key" class="flex gap-4 pb-8 last:pb-0 relative">
                    <div v-if="i < steps.length - 1" class="absolute left-5 top-10 w-0.5 h-full bg-slate-200" />
                    <div :class="['w-10 h-10 rounded-full flex items-center justify-center shrink-0 z-10',
                        s.done ? 'bg-teal-600 text-white' : 'bg-slate-100 text-slate-400']">
                        <Check v-if="s.done" class="w-5 h-5" />
                        <span v-else class="text-sm">{{ i + 1 }}</span>
                    </div>
                    <div>
                        <p :class="['font-semibold', s.active ? 'text-teal-700' : '']">{{ s.label }}</p>
                        <p v-if="s.active" class="text-xs text-teal-500 mt-1">In progress</p>
                    </div>
                </div>
            </div>
            <p class="text-sm text-slate-500 mt-4">Estimated delivery: {{ order.estimated_delivery }}</p>
            <button v-if="order.status !== 'delivered' && order.status !== 'cancelled'" type="button"
                class="btn btn-danger mt-4" @click="cancel">Cancel Order</button>
        </div>
    </CustomerLayout>
</template>
