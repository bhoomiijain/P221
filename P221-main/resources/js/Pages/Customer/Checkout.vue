<script setup>
import { Head, Link } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import AiConsultantModal from '@/Components/Customer/AiConsultantModal.vue';
import { useCustomerApi } from '@/composables/useCustomerApi';
import { useToast } from '@/composables/useToast';
import { ref } from 'vue';

const props = defineProps({
    cart: Object, addresses: Array, prescriptions: Array,
    aiQuestions: Array, paymentMethods: Array,
});
const api = useCustomerApi();
const toast = useToast();
const showAi = ref(false);
const paymentMethod = ref('cod');
const selectedAddress = ref(props.addresses?.find(a => a.is_default)?.id || props.addresses?.[0]?.id);
const selectedRx = ref([]);
const deliveryInstructions = ref('');
const placing = ref(false);
let aiPayload = null;

function placeOrderClick() {
    if (!selectedAddress.value) {
        toast.error('Select a delivery address');
        return;
    }
    showAi.value = true;
}

async function onAiComplete({ responses, analysis }) {
    showAi.value = false;
    aiPayload = responses;
    const addr = props.addresses.find(a => a.id === selectedAddress.value || a._id === selectedAddress.value);
    if (!addr) return;
    placing.value = true;
    try {
        const { data } = await api.placeOrder({
            address: {
                name: addr.name, phone: addr.phone, address_line: addr.address_line,
                city: addr.city, state: addr.state, pincode: addr.pincode,
            },
            payment_method: paymentMethod.value,
            ai_responses: responses,
            prescription_ids: selectedRx.value,
            delivery_instructions: deliveryInstructions.value,
        });
        toast.success('Order placed!');
        window.location.href = `/shop/orders/${data.order.id || data.order._id}/track`;
    } catch (e) {
        toast.error(e.response?.data?.message || 'Order failed');
    } finally {
        placing.value = false;
    }
}
</script>

<template>
    <Head title="Checkout" />
    <CustomerLayout>
        <div class="max-w-5xl mx-auto px-4 py-8">
            <h1 class="text-2xl font-bold mb-6">Checkout</h1>
            <div class="grid lg:grid-cols-2 gap-8">
                <div class="space-y-6">
                    <div class="ph-card p-6">
                        <h3 class="font-semibold mb-4">Delivery Address</h3>
                        <div v-for="a in addresses" :key="a.id || a._id" class="mb-3">
                            <label class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer"
                                :class="selectedAddress === (a.id || a._id) ? 'border-teal-500 bg-teal-50' : 'border-slate-200'">
                                <input v-model="selectedAddress" type="radio" :value="a.id || a._id" />
                                <div class="text-sm">
                                    <p class="font-medium">{{ a.name }} · {{ a.phone }}</p>
                                    <p class="text-slate-500">{{ a.address_line }}, {{ a.city }} {{ a.pincode }}</p>
                                </div>
                            </label>
                        </div>
                        <p v-if="!addresses?.length" class="text-sm text-slate-500">Add an address from your profile first.</p>
                    </div>
                    <div class="ph-card p-6">
                        <h3 class="font-semibold mb-4">Payment Method</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <label v-for="pm in paymentMethods" :key="pm.id"
                                class="p-4 rounded-xl border cursor-pointer text-center text-sm"
                                :class="paymentMethod === pm.id ? 'border-teal-500 bg-teal-50' : ''">
                                <input v-model="paymentMethod" type="radio" :value="pm.id" class="sr-only" />
                                {{ pm.label }}
                            </label>
                        </div>
                    </div>
                    <div v-if="cart.needs_prescription" class="ph-card p-6">
                        <h3 class="font-semibold mb-4">Prescription Verification</h3>
                        <label v-for="rx in prescriptions" :key="rx.id || rx._id" class="flex items-center gap-2 text-sm mb-2">
                            <input v-model="selectedRx" type="checkbox" :value="rx.id || rx._id" />
                            {{ rx.file_name }} ({{ rx.status }})
                        </label>
                        <Link href="/shop/prescriptions" class="text-teal-600 text-sm">Upload new →</Link>
                    </div>
                    <textarea v-model="deliveryInstructions" placeholder="Delivery instructions (optional)"
                        class="ph-input !rounded-xl !h-24 py-3 w-full" rows="3" />
                </div>
                <div class="ph-card p-6 h-fit">
                    <h3 class="font-semibold mb-4">Order Summary</h3>
                    <div v-for="item in cart.items" :key="item.medicine_id" class="flex justify-between text-sm py-2 border-b">
                        <span>{{ item.name }} × {{ item.quantity }}</span>
                        <span>₹{{ item.line_total }}</span>
                    </div>
                    <div class="mt-4 space-y-1 text-sm">
                        <div class="flex justify-between"><span>Total</span><span class="font-bold text-lg">₹{{ cart.total }}</span></div>
                    </div>
                    <button type="button" class="btn btn-primary w-full mt-6" :disabled="placing" @click="placeOrderClick">
                        Place Order — AI Safety Check
                    </button>
                </div>
            </div>
        </div>
        <AiConsultantModal :show="showAi" :questions="aiQuestions" @close="showAi = false" @complete="onAiComplete" />
    </CustomerLayout>
</template>
