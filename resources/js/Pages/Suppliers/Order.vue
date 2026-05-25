<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useToast } from '@/composables/useToast.js';
import { router } from '@inertiajs/vue3';
import {
    Truck, Search, Plus, Minus, Trash2, ShoppingCart,
    Package, ArrowLeft, CheckCircle, Building2, Phone, Mail, MapPin
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps({
    supplier: Object,
    supplierMedicines: Array,
});
const toast = useToast();

// ── Search ────────────────────────────────────────────────────────────────────
const searchQuery = ref('');
const filteredMedicines = computed(() => {
    if (!searchQuery.value.trim()) return props.supplierMedicines || [];
    const re = new RegExp(searchQuery.value.trim(), 'i');
    return (props.supplierMedicines || []).filter(m => re.test(m.medicine_name));
});

// ── Cart ──────────────────────────────────────────────────────────────────────
const cart = ref([]);

function addToCart(med) {
    const existing = cart.value.find(i => i.medicine_id === med.medicine_id);
    if (existing) {
        if (existing.quantity < med.available_qty) {
            existing.quantity++;
        } else {
            toast.warning(`Max available: ${med.available_qty} units`);
        }
    } else {
        cart.value.push({
            medicine_id:   med.medicine_id,
            medicine_name: med.medicine_name,
            unit_price:    med.unit_price,
            available_qty: med.available_qty,
            quantity:      1,
        });
    }
}

function removeFromCart(medId) {
    cart.value = cart.value.filter(i => i.medicine_id !== medId);
}

function increment(item) {
    if (item.quantity < item.available_qty) item.quantity++;
    else toast.warning(`Max available: ${item.available_qty}`);
}

function decrement(item) {
    if (item.quantity > 1) item.quantity--;
    else removeFromCart(item.medicine_id);
}

const cartTotal = computed(() =>
    cart.value.reduce((sum, i) => sum + i.quantity * i.unit_price, 0)
);

// ── Submit ────────────────────────────────────────────────────────────────────
const submitting = ref(false);
const submitted  = ref(false);

function submitOrder() {
    if (!cart.value.length) return;
    submitting.value = true;
    router.post('/supplier-orders', {
        supplier_id: props.supplier.id,
        items: cart.value.map(i => ({
            medicine_id: i.medicine_id,
            quantity:    i.quantity,
        })),
    }, {
        onSuccess: () => {
            submitted.value = true;
            submitting.value = false;
        },
        onError: (err) => {
            toast.error(Object.values(err)[0] || 'Failed to place order');
            submitting.value = false;
        },
    });
}

function money(v) {
    return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(v || 0);
}

function inCart(medId) {
    return cart.value.find(i => i.medicine_id === medId);
}
</script>

<template>
    <AppLayout>
        <template #title>
            <div class="flex items-center gap-3">
                <a href="/suppliers" class="btn btn-icon btn-sm btn-secondary" title="Back">
                    <ArrowLeft class="h-4 w-4" />
                </a>
                <div class="flex h-9 w-9 items-center justify-center rounded-lg" style="background: linear-gradient(135deg,#3b82f6,#2563eb)">
                    <ShoppingCart class="h-4 w-4 text-white" />
                </div>
                <div>
                    <h1 class="text-xl font-bold" style="color: var(--text-primary);">Order from Supplier</h1>
                    <p class="text-xs" style="color: var(--text-muted);">Select medicines and quantities · Payment: Cash on Delivery</p>
                </div>
            </div>
        </template>

        <!-- Success state -->
        <div v-if="submitted" class="max-w-lg mx-auto py-12 text-center">
            <div class="ph-card p-10">
                <div class="h-20 w-20 rounded-full mx-auto mb-6 flex items-center justify-center"
                     style="background: linear-gradient(135deg,#10b981,#059669);">
                    <CheckCircle class="h-10 w-10 text-white" />
                </div>
                <h2 class="text-2xl font-black mb-2" style="color: var(--text-primary);">Order Sent!</h2>
                <p class="text-sm mb-6" style="color: var(--text-muted);">
                    Your order request has been sent to <strong>{{ supplier.business }}</strong>.<br>
                    The supplier will review and fulfill it shortly.
                </p>
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 text-sm text-amber-800">
                    💰 Payment mode: <strong>Cash on Delivery</strong>
                </div>
                <a href="/suppliers" class="btn btn-primary w-full">Back to Suppliers</a>
            </div>
        </div>

        <!-- Main cart UI -->
        <div v-else class="grid gap-5 xl:grid-cols-[1fr_360px]">

            <!-- Left: Supplier info + Medicine list -->
            <div class="flex flex-col gap-4">

                <!-- Supplier info card -->
                <div class="ph-card p-5">
                    <div class="flex items-center gap-4">
                        <div class="h-14 w-14 rounded-2xl overflow-hidden flex items-center justify-center shrink-0"
                             style="background: linear-gradient(135deg,#dbeafe,#bfdbfe);">
                            <img v-if="supplier.avatar_url" :src="supplier.avatar_url"
                                 class="h-14 w-14 object-cover rounded-2xl" />
                            <Building2 v-else class="h-7 w-7 text-blue-600" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <h2 class="font-bold text-base" style="color: var(--text-primary);">{{ supplier.business }}</h2>
                            <div class="flex flex-wrap gap-3 mt-1 text-xs" style="color: var(--text-muted);">
                                <span v-if="supplier.phone" class="flex items-center gap-1"><Phone class="h-3 w-3" />{{ supplier.phone }}</span>
                                <span v-if="supplier.email" class="flex items-center gap-1"><Mail class="h-3 w-3" />{{ supplier.email }}</span>
                                <span v-if="supplier.city" class="flex items-center gap-1"><MapPin class="h-3 w-3" />{{ supplier.city }}</span>
                            </div>
                        </div>
                        <div class="shrink-0 text-right">
                            <div class="text-xs font-semibold" style="color: var(--text-muted);">Available</div>
                            <div class="text-xl font-black" style="color: var(--brand-primary);">{{ supplierMedicines?.length }}</div>
                            <div class="text-xs" style="color: var(--text-muted);">medicines</div>
                        </div>
                    </div>
                </div>

                <!-- Search + medicine list -->
                <div class="ph-card flex-1">
                    <div class="ph-card-header">
                        <h2>Available Medicines</h2>
                    </div>
                    <div class="p-4">
                        <div class="relative mb-4">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4" style="color: var(--text-muted);" />
                            <input v-model="searchQuery" class="ph-input pl-10" placeholder="Search medicines..." />
                        </div>

                        <div v-if="!filteredMedicines.length" class="py-12 text-center">
                            <Package class="h-10 w-10 mx-auto mb-3 opacity-20" />
                            <p class="text-sm" style="color: var(--text-muted);">
                                {{ searchQuery ? 'No medicines match your search' : 'Supplier has no medicines in stock' }}
                            </p>
                        </div>

                        <div v-else class="grid gap-3 sm:grid-cols-2">
                            <div
                                v-for="med in filteredMedicines"
                                :key="med.medicine_id"
                                class="rounded-xl border-2 p-4 transition-all cursor-pointer"
                                :style="inCart(med.medicine_id)
                                    ? 'border-color: var(--brand-primary); background: rgba(0,163,92,0.04);'
                                    : 'border-color: var(--surface-border); background: var(--surface-card);'"
                                @click="addToCart(med)"
                            >
                                <div class="flex items-start gap-3">
                                    <div class="h-12 w-12 rounded-xl overflow-hidden shrink-0 flex items-center justify-center"
                                         style="background: #f0f4f8;">
                                        <img v-if="med.image" :src="med.image" class="h-12 w-12 object-cover rounded-xl" />
                                        <Package v-else class="h-6 w-6 opacity-30" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-semibold text-sm" style="color: var(--text-primary);">{{ med.medicine_name }}</div>
                                        <div class="text-xs mt-0.5" style="color: var(--text-muted);">{{ med.description }}</div>
                                        <div class="flex items-center gap-2 mt-2">
                                            <span class="badge badge-green text-xs">₹{{ med.unit_price }}/unit</span>
                                            <span class="badge badge-blue text-xs">{{ med.available_qty }} avail.</span>
                                        </div>
                                    </div>
                                    <div v-if="inCart(med.medicine_id)" class="shrink-0">
                                        <div class="h-6 w-6 rounded-full flex items-center justify-center"
                                             style="background: var(--brand-primary);">
                                            <CheckCircle class="h-4 w-4 text-white" />
                                        </div>
                                    </div>
                                </div>
                                <div v-if="inCart(med.medicine_id)" class="mt-2 text-xs font-semibold text-center" style="color: var(--brand-primary);">
                                    In cart: {{ inCart(med.medicine_id).quantity }} × ₹{{ med.unit_price }} = ₹{{ (inCart(med.medicine_id).quantity * med.unit_price).toFixed(2) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Order cart -->
            <aside class="flex flex-col gap-4">
                <div class="ph-card flex-1 flex flex-col">
                    <div class="ph-card-header">
                        <h2>Order Cart</h2>
                        <span v-if="cart.length" class="badge badge-blue">{{ cart.length }} item{{ cart.length !== 1 ? 's' : '' }}</span>
                    </div>

                    <div v-if="!cart.length" class="flex-1 flex flex-col items-center justify-center py-12 text-center px-4">
                        <ShoppingCart class="h-10 w-10 mb-3 opacity-20" />
                        <p class="text-sm" style="color: var(--text-muted);">
                            Click any medicine to add it to your order
                        </p>
                    </div>

                    <div v-else class="flex-1 flex flex-col">
                        <div class="flex-1 overflow-y-auto divide-y" style="border-color: var(--surface-border); max-height: 380px;">
                            <div
                                v-for="item in cart"
                                :key="item.medicine_id"
                                class="p-4"
                            >
                                <div class="flex items-start justify-between gap-2 mb-3">
                                    <div class="font-semibold text-sm" style="color: var(--text-primary);">{{ item.medicine_name }}</div>
                                    <button class="text-red-400 hover:text-red-600 transition-colors" @click="removeFromCart(item.medicine_id)">
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <button class="btn btn-icon btn-sm btn-secondary" @click="decrement(item)">
                                            <Minus class="h-3 w-3" />
                                        </button>
                                        <span class="w-8 text-center font-bold text-sm" style="color: var(--text-primary);">{{ item.quantity }}</span>
                                        <button class="btn btn-icon btn-sm btn-secondary" @click="increment(item)">
                                            <Plus class="h-3 w-3" />
                                        </button>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs" style="color: var(--text-muted);">₹{{ item.unit_price }}/unit</div>
                                        <div class="font-bold text-sm" style="color: var(--brand-primary);">₹{{ (item.quantity * item.unit_price).toFixed(2) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order summary -->
                        <div class="p-4 border-t" style="border-color: var(--surface-border);">
                            <div class="flex justify-between items-center mb-1 text-sm" style="color: var(--text-muted);">
                                <span>Subtotal ({{ cart.length }} items)</span>
                                <span class="font-medium" style="color: var(--text-primary);">{{ money(cartTotal) }}</span>
                            </div>
                            <div class="flex justify-between items-center mb-4 text-sm">
                                <span style="color: var(--text-muted);">Payment</span>
                                <span class="badge badge-amber">Cash on Delivery</span>
                            </div>
                            <div class="rounded-xl p-3 mb-4 text-sm font-black flex justify-between items-center"
                                 style="background: var(--brand-primary); color: var(--surface-bg);">
                                <span>Order Total</span>
                                <span>{{ money(cartTotal) }}</span>
                            </div>
                            <button
                                class="btn btn-primary w-full h-10"
                                :disabled="submitting || !cart.length"
                                @click="submitOrder"
                            >
                                <span v-if="submitting" class="flex items-center justify-center gap-2">
                                    <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
                                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity="0.3"/>
                                        <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                                    </svg>
                                    Sending Order...
                                </span>
                                <span v-else class="flex items-center justify-center gap-2">
                                    <Truck class="h-4 w-4" /> Send Order Request
                                </span>
                            </button>
                            <p class="text-[10px] text-center mt-2" style="color: var(--text-muted);">
                                Order will be reviewed by the supplier before dispatch
                            </p>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </AppLayout>
</template>
