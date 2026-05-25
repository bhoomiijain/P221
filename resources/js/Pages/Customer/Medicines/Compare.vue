<script setup>
import { Head } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

defineProps({ products: Array });
</script>

<template>
    <Head title="Compare Medicines" />
    <CustomerLayout>
        <div class="max-w-7xl mx-auto px-4 py-8 overflow-x-auto">
            <h1 class="text-2xl font-bold mb-6">Medicine Comparison</h1>
            <table class="ph-table min-w-[600px]">
                <thead>
                    <tr><th>Feature</th><th v-for="p in products" :key="p.id">{{ p.name }}</th></tr>
                </thead>
                <tbody>
                    <tr><td>Price</td><td v-for="p in products" :key="p.id">₹{{ p.price }}</td></tr>
                    <tr><td>Brand</td><td v-for="p in products" :key="p.id">{{ p.brand }}</td></tr>
                    <tr><td>Rating</td><td v-for="p in products" :key="p.id">{{ p.rating_avg }}</td></tr>
                    <tr><td>Stock</td><td v-for="p in products" :key="p.id">{{ p.in_stock ? 'Yes' : 'No' }}</td></tr>
                    <tr><td>Rx</td><td v-for="p in products" :key="p.id">{{ p.prescription_required ? 'Yes' : 'No' }}</td></tr>
                </tbody>
            </table>
        </div>
    </CustomerLayout>
</template>
