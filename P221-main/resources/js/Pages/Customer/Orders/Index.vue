<script setup>
import { Head, Link } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

defineProps({ orders: Object });
</script>

<template>
    <Head title="My Orders" />
    <CustomerLayout>
        <div class="max-w-4xl mx-auto px-4 py-8">
            <h1 class="text-2xl font-bold mb-6">Order History</h1>
            <div v-for="o in orders.data" :key="o.id || o._id" class="ph-card p-6 mb-4 flex flex-wrap justify-between gap-4">
                <div>
                    <p class="font-bold">{{ o.order_number }}</p>
                    <p class="text-sm text-slate-500">{{ o.status }} · {{ o.payment_method }}</p>
                </div>
                <div class="text-right">
                    <p class="font-bold text-teal-700">₹{{ o.total }}</p>
                    <div class="flex gap-3 mt-2 text-sm">
                        <Link :href="`/shop/orders/${o.id || o._id}/track`" class="text-teal-600">Track</Link>
                        <a :href="`/api/customer/orders/${o.id || o._id}/invoice`" class="text-slate-500">Invoice</a>
                    </div>
                </div>
            </div>
        </div>
    </CustomerLayout>
</template>
