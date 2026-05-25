<script setup>
import { Link } from '@inertiajs/vue3';
import { Heart, ShoppingCart, Eye, Star } from 'lucide-vue-next';
import MedicineImage from '@/Components/MedicineImage.vue';
import { useCustomerApi } from '@/composables/useCustomerApi';
import { ref } from 'vue';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    product: { type: Object, required: true },
    wishlisted: { type: Boolean, default: false },
});
const emit = defineEmits(['wishlist-toggle']);
const api = useCustomerApi();
const toast = useToast();
const loading = ref(false);

async function addCart(e) {
    e.preventDefault();
    loading.value = true;
    try {
        await api.addToCart(props.product.id);
        toast.success('Added to cart');
    } catch {
        toast.error('Please login to add items');
        window.location.href = '/login';
    } finally {
        loading.value = false;
    }
}

async function toggleWish(e) {
    e.preventDefault();
    try {
        if (props.wishlisted) {
            await api.removeWishlist(props.product.id);
        } else {
            await api.addWishlist(props.product.id);
        }
        emit('wishlist-toggle');
        toast.success(props.wishlisted ? 'Removed from wishlist' : 'Added to wishlist');
    } catch {
        window.location.href = '/login';
    }
}
</script>

<template>
    <article class="ph-card group hover:shadow-lg transition-all duration-300 flex flex-col">
        <Link :href="`/shop/medicines/${product.id}`" class="block p-4 pb-0">
            <div class="aspect-square rounded-2xl flex items-center justify-center overflow-hidden p-4" style="background: var(--surface-muted);">
                <MedicineImage :name="product.name" :brand="product.brand" :src="product.image" :alt="product.name" class="max-h-28 w-full object-contain" />
            </div>
            <p class="text-xs text-teal-600 dark:text-teal-400 mt-3 font-medium">{{ product.brand }}</p>
            <h3 class="font-semibold text-slate-800 dark:text-white line-clamp-2 mt-1">{{ product.name }}</h3>
            <p class="text-xs text-slate-500 mt-1">{{ product.manufacturer }}</p>
            <div class="flex items-center gap-1 mt-2 text-amber-500 text-sm">
                <Star class="w-4 h-4 fill-current" />
                <span>{{ product.rating_avg }}</span>
                <span class="text-slate-400">({{ product.review_count }})</span>
            </div>
            <div class="flex items-baseline gap-2 mt-2">
                <span class="text-lg font-bold text-teal-800 dark:text-teal-300">₹{{ product.price }}</span>
                <span v-if="product.discount_percent" class="text-xs line-through text-slate-400">₹{{ product.mrp }}</span>
                <span v-if="product.discount_percent" class="text-xs bg-rose-100 text-rose-600 px-2 py-0.5 rounded-full">{{ product.discount_percent }}% off</span>
            </div>
            <span :class="product.in_stock ? 'badge-green' : 'badge-red'" class="badge mt-2">
                {{ product.in_stock ? 'In Stock' : 'Out of Stock' }}
            </span>
            <span v-if="product.prescription_required" class="badge badge-amber ml-1 mt-2">Rx Required</span>
        </Link>
        <div class="p-4 pt-3 flex gap-2 mt-auto">
            <button type="button" class="btn btn-sm btn-primary flex-1" :disabled="!product.in_stock || loading" @click="addCart">
                <ShoppingCart class="w-4 h-4" /> Cart
            </button>
            <button type="button" class="btn btn-icon btn-secondary" @click="toggleWish">
                <Heart class="w-4 h-4" :class="{ 'fill-rose-500 text-rose-500': wishlisted }" />
            </button>
            <Link :href="`/shop/medicines/${product.id}`" class="btn btn-icon btn-secondary">
                <Eye class="w-4 h-4" />
            </Link>
        </div>
    </article>
</template>
