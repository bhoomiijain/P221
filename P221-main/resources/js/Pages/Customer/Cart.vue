<script setup>
import { Head, Link } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { useCustomerApi } from '@/composables/useCustomerApi';
import { useToast } from '@/composables/useToast';
import { onMounted, ref } from 'vue';
import { Trash2, Tag } from 'lucide-vue-next';

const props = defineProps({ cart: Object, coupons: Array });
const api = useCustomerApi();
const toast = useToast();
const cartData = ref(props.cart);
const couponCode = ref('');

onMounted(async () => {
    try {
        const { data } = await api.getCart();
        cartData.value = data;
    } catch { /* use server props */ }
});

async function updateQty(id, quantity) {
    const { data } = await api.updateCart(id, { quantity });
    cartData.value = data;
}
async function remove(id) {
    const { data } = await api.removeFromCart(id);
    cartData.value = data;
    toast.success('Item removed');
}
async function saveLater(id) {
    const { data } = await api.updateCart(id, { saved_for_later: true });
    cartData.value = data;
}
async function applyCoupon() {
    try {
        const { data } = await api.applyCoupon(couponCode.value);
        cartData.value = data;
        toast.success('Coupon applied');
    } catch (e) {
        toast.error(e.response?.data?.message || 'Invalid coupon');
    }
}
</script>

<template>
    <Head title="Cart" />
    <CustomerLayout>
        <template #title>Shopping Cart</template>
        <div class="max-w-5xl mx-auto">
            <div v-if="!cartData?.items?.length" class="ph-card p-12 text-center">
                <p class="text-slate-500">Your cart is empty.</p>
                <Link href="/shop/medicines" class="btn btn-primary mt-4">Browse Medicines</Link>
            </div>
            <div v-else class="grid lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-4">
                    <div v-for="item in cartData.items" :key="item.id" class="ph-card p-4 flex gap-4">
                        <img :src="item.image" class="w-20 h-20 object-contain rounded-xl bg-teal-50" />
                        <div class="flex-1">
                            <h3 class="font-semibold">{{ item.name }}</h3>
                            <p v-if="item.prescription_required" class="text-xs text-amber-600">Prescription required</p>
                            <p class="text-teal-700 font-bold mt-1">₹{{ item.line_total }}</p>
                            <div class="flex items-center gap-2 mt-2">
                                <button type="button" class="btn btn-sm btn-secondary" @click="updateQty(item.id, Math.max(1, item.quantity - 1))">−</button>
                                <span>{{ item.quantity }}</span>
                                <button type="button" class="btn btn-sm btn-secondary" @click="updateQty(item.id, item.quantity + 1)">+</button>
                                <button type="button" class="text-xs text-slate-500 ml-2" @click="saveLater(item.id)">Save for later</button>
                            </div>
                        </div>
                        <button type="button" @click="remove(item.id)" class="text-rose-500"><Trash2 class="w-5 h-5" /></button>
                    </div>
                </div>
                <div class="ph-card p-6 h-fit">
                    <h3 class="font-semibold mb-4">Order Summary</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span>Subtotal</span><span>₹{{ cartData.subtotal }}</span></div>
                        <div class="flex justify-between"><span>Tax (5%)</span><span>₹{{ cartData.tax }}</span></div>
                        <div class="flex justify-between"><span>Delivery</span><span>₹{{ cartData.delivery_charge }}</span></div>
                        <div v-if="cartData.coupon_discount" class="flex justify-between text-teal-600"><span>Coupon</span><span>-₹{{ cartData.coupon_discount }}</span></div>
                    </div>
                    <div class="flex gap-2 mt-4">
                        <input v-model="couponCode" placeholder="Coupon code" class="ph-input flex-1 !rounded-xl !h-10 text-sm" />
                        <button type="button" class="btn btn-sm btn-secondary" @click="applyCoupon"><Tag class="w-4 h-4" /></button>
                    </div>
                    <p class="text-xs text-slate-400 mt-2">Try: {{ coupons?.join(', ') }}</p>
                    <p class="text-sm text-slate-500 mt-4">Est. delivery: {{ cartData.estimated_delivery }}</p>
                    <p v-if="cartData.needs_prescription" class="text-xs text-amber-600 mt-2">⚠ Prescription validation required at checkout</p>
                    <div class="flex justify-between font-bold text-lg mt-4 pt-4 border-t">
                        <span>Total</span><span>₹{{ cartData.total }}</span>
                    </div>
                    <Link href="/shop/checkout" class="btn btn-primary w-full mt-4">Proceed to Checkout</Link>
                </div>
            </div>
        </div>
    </CustomerLayout>
</template>
