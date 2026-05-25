<script setup>
import { Head } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import MedicineCard from '@/Components/Customer/MedicineCard.vue';

defineProps({ items: Array });
</script>

<template>
    <Head title="Wishlist" />
    <CustomerLayout>
        <div class="max-w-7xl mx-auto px-4 py-8">
            <h1 class="text-2xl font-bold mb-6">My Wishlist</h1>
            <div v-if="!items?.length" class="text-slate-400">Wishlist is empty.</div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <MedicineCard v-for="p in items" :key="p.id" :product="p" :wishlisted="true" />
            </div>
        </div>
    </CustomerLayout>
</template>
