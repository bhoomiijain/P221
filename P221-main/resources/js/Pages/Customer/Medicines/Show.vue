<script setup>
import { Head, Link } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import MedicineCard from '@/Components/Customer/MedicineCard.vue';
import MedicineImage from '@/Components/MedicineImage.vue';
import { useCustomerApi } from '@/composables/useCustomerApi';
import { useToast } from '@/composables/useToast';
import { ref } from 'vue';
import { ShoppingCart, Heart, Upload, Star } from 'lucide-vue-next';

const props = defineProps({
    product: Object, related: Array, boughtTogether: Array,
    reviews: Array, wishlisted: Boolean,
});
const api = useCustomerApi();
const toast = useToast();
const qty = ref(1);
const wl = ref(props.wishlisted);

async function addCart() {
    try {
        await api.addToCart(props.product.id, qty.value);
        toast.success('Added to cart');
    } catch { window.location.href = '/shop/login'; }
}
async function buyNow() {
    await addCart();
    window.location.href = '/shop/checkout';
}
async function toggleWl() {
    try {
        if (wl.value) await api.removeWishlist(props.product.id);
        else await api.addWishlist(props.product.id);
        wl.value = !wl.value;
    } catch { window.location.href = '/shop/login'; }
}
</script>

<template>
    <Head :title="product.name" />
    <CustomerLayout>
        <template #title>{{ product.name }}</template>
        <div>
            <div class="grid lg:grid-cols-2 gap-10">
                <div class="ph-card p-8 flex items-center justify-center" style="background: var(--surface-muted);">
                    <MedicineImage :name="product.name" :brand="product.brand" :src="product.image" class="max-h-72 w-full object-contain" />
                </div>
                <div>
                    <p class="text-teal-600 font-medium">{{ product.brand }}</p>
                    <h1 class="text-3xl font-bold mt-1">{{ product.name }}</h1>
                    <p class="text-slate-500">{{ product.manufacturer }} · {{ product.form }} · {{ product.pack_size }}</p>
                    <div class="flex items-center gap-2 mt-3">
                        <Star class="w-5 h-5 fill-amber-400 text-amber-400" />
                        <span class="font-semibold">{{ product.rating_avg }}</span>
                        <span class="text-slate-400">({{ product.review_count }} reviews)</span>
                    </div>
                    <div class="flex items-baseline gap-3 mt-4">
                        <span class="text-3xl font-bold text-teal-800">₹{{ product.price }}</span>
                        <span v-if="product.discount_percent" class="line-through text-slate-400">₹{{ product.mrp }}</span>
                    </div>
                    <p class="mt-4 text-sm">{{ product.description }}</p>
                    <div class="flex items-center gap-3 mt-6">
                        <input v-model.number="qty" type="number" min="1" max="10" class="w-20 ph-input !rounded-xl text-center" />
                        <button type="button" class="btn btn-primary" @click="addCart"><ShoppingCart class="w-4 h-4" /> Add to Cart</button>
                        <button type="button" class="btn btn-secondary" @click="buyNow">Buy Now</button>
                        <button type="button" class="btn btn-icon btn-secondary" @click="toggleWl"><Heart :class="{ 'fill-rose-500 text-rose-500': wl }" class="w-5 h-5" /></button>
                    </div>
                    <Link v-if="product.prescription_required" href="/shop/prescriptions" class="mt-4 inline-flex items-center gap-2 text-sm text-amber-700 bg-amber-50 px-4 py-2 rounded-full">
                        <Upload class="w-4 h-4" /> Upload prescription required
                    </Link>
                    <div class="mt-8 space-y-4 text-sm">
                        <div><strong>Ingredients:</strong> {{ product.ingredients }}</div>
                        <div><strong>Dosage:</strong> {{ product.dosage }}</div>
                        <div><strong>Side effects:</strong> {{ product.side_effects }}</div>
                        <div><strong>Warnings:</strong> {{ product.warnings }}</div>
                        <div v-if="product.nearest_expiry"><strong>Expiry:</strong> {{ product.nearest_expiry }}</div>
                    </div>
                </div>
            </div>
            <section class="mt-16">
                <h2 class="text-xl font-bold mb-4">Customer Reviews</h2>
                <div v-for="r in reviews" :key="r.id" class="ph-card p-4 mb-3">
                    <div class="flex items-center gap-2">
                        <span class="font-medium">{{ r.user?.name || 'Customer' }}</span>
                        <span v-if="r.verified_purchase" class="badge badge-green">Verified Purchase</span>
                    </div>
                    <p class="text-amber-500 text-sm">{{ '★'.repeat(r.rating) }}</p>
                    <p class="text-slate-600 mt-1">{{ r.comment }}</p>
                </div>
            </section>
            <section class="mt-12">
                <h2 class="text-xl font-bold mb-4">Related Medicines</h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <MedicineCard v-for="p in related" :key="p.id" :product="p" />
                </div>
            </section>
        </div>
    </CustomerLayout>
</template>
