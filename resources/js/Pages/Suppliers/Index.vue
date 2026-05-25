<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useToast } from '@/composables/useToast.js';
import { Truck, Search, Phone, Mail, MapPin, Building2, ShoppingCart, Globe } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps({ suppliers: Array, supplierMedicines: Array, medicines: Array });
const toast = useToast();

const searchQuery = ref('');

const filteredSuppliers = computed(() =>
    (props.suppliers || []).filter(s =>
        (s.name || '').toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        (s.phone || '').includes(searchQuery.value) ||
        (s.email || '').toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        (s.city || '').toLowerCase().includes(searchQuery.value.toLowerCase())
    )
);

// Count medicines stocked by each supplier
function getStockCount(sup) {
    return (props.supplierMedicines || []).filter(sm => sm.supplier_id === sup.user_id).length;
}
</script>

<template>
    <AppLayout>
        <template #title>
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg" style="background: linear-gradient(135deg,#3b82f6,#2563eb)">
                    <Truck class="h-4 w-4 text-white" />
                </div>
                <div>
                    <h1 class="text-xl font-bold" style="color: var(--text-primary);">Suppliers</h1>
                    <p class="text-xs" style="color: var(--text-muted);">Browse registered suppliers and place medicine orders.</p>
                </div>
            </div>
        </template>

        <!-- Search only (no Add Supplier) -->
        <div class="mb-5">
            <div class="relative max-w-sm">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4" style="color: var(--text-muted);" />
                <input v-model="searchQuery" class="ph-input pl-10" placeholder="Search suppliers by name, city, phone..." />
            </div>
        </div>

        <!-- Empty state -->
        <div v-if="!filteredSuppliers.length" class="ph-card py-20 text-center">
            <Truck class="h-12 w-12 mx-auto mb-4 opacity-20" />
            <p class="text-sm" style="color: var(--text-muted);">
                {{ searchQuery ? `No suppliers matching "${searchQuery}"` : 'No suppliers registered yet.' }}
            </p>
        </div>

        <!-- Supplier cards grid -->
        <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="sup in filteredSuppliers"
                :key="sup._id || sup.id"
                class="ph-card p-5 hover:shadow-md transition-all flex flex-col"
            >
                <!-- Avatar + Name -->
                <div class="flex items-start gap-4 mb-4">
                    <!-- Profile photo or fallback -->
                    <div class="h-14 w-14 rounded-2xl overflow-hidden flex items-center justify-center shrink-0 border-2"
                         style="border-color: var(--surface-border); background: linear-gradient(135deg,#dbeafe,#bfdbfe);">
                        <img
                            v-if="sup.user?.avatar_url || sup.avatar_url"
                            :src="sup.user?.avatar_url || sup.avatar_url"
                            class="h-14 w-14 object-cover"
                            :alt="sup.name"
                        />
                        <Building2 v-else class="h-7 w-7 text-blue-600" />
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="font-bold text-base" style="color: var(--text-primary);">{{ sup.name }}</div>
                        <!-- Stock badge -->
                        <span class="badge badge-blue mt-1">
                            {{ getStockCount(sup) }} medicine{{ getStockCount(sup) !== 1 ? 's' : '' }}
                        </span>
                    </div>
                </div>

                <!-- Contact info -->
                <div class="space-y-2 text-xs flex-1" style="color: var(--text-secondary);">
                    <div v-if="sup.phone" class="flex items-center gap-2">
                        <Phone class="h-3.5 w-3.5 shrink-0" style="color: var(--text-muted);" />
                        {{ sup.phone }}
                    </div>
                    <div v-if="sup.email" class="flex items-center gap-2">
                        <Mail class="h-3.5 w-3.5 shrink-0" style="color: var(--text-muted);" />
                        <span class="truncate">{{ sup.email }}</span>
                    </div>
                    <div v-if="sup.address || sup.city" class="flex items-start gap-2">
                        <MapPin class="h-3.5 w-3.5 shrink-0 mt-0.5" style="color: var(--text-muted);" />
                        <span>{{ [sup.address, sup.city, sup.state].filter(Boolean).join(', ') }}</span>
                    </div>
                    <div v-if="sup.website" class="flex items-center gap-2">
                        <Globe class="h-3.5 w-3.5 shrink-0" style="color: var(--text-muted);" />
                        <a :href="sup.website" target="_blank" class="truncate hover:underline" style="color: var(--brand-primary);">{{ sup.website }}</a>
                    </div>
                </div>

                <!-- Order button -->
                <div class="mt-4 pt-4 border-t" style="border-color: var(--surface-border);">
                    <a
                        v-if="sup.user_id"
                        :href="`/suppliers/${sup.user_id}/order`"
                        class="btn btn-primary btn-sm w-full flex items-center justify-center gap-2"
                    >
                        <ShoppingCart class="h-3.5 w-3.5" />
                        Order Medicines
                    </a>
                    <div v-else class="text-xs text-center w-full font-medium" style="color: var(--text-muted);">
                        Account not linked yet
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
