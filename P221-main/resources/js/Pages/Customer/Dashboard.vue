<script setup>
import { Head, Link } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import MedicineCard from '@/Components/Customer/MedicineCard.vue';
import { Package, Heart, MapPin, FileText, Bell } from 'lucide-vue-next';

defineProps({
    recentOrders: Array, wishlist: Array, addresses: Array,
    prescriptions: Array, recommended: Array, notifications: Array, unreadCount: Number,
});
</script>

<template>
    <Head title="My Dashboard" />
    <CustomerLayout>
        <template #title>Customer Dashboard</template>
        <div>
            <p class="text-slate-500 mb-6">Hello, {{ $page.props.auth?.user?.name }} — order medicines, track deliveries, and manage prescriptions.</p>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
                <Link href="/shop/orders" class="stat-card flex items-center gap-4">
                    <Package class="w-8 h-8 text-teal-500" />
                    <div><p class="stat-label">Orders</p><p class="stat-value text-2xl">{{ recentOrders?.length || 0 }}</p></div>
                </Link>
                <Link href="/shop/wishlist" class="stat-card flex items-center gap-4">
                    <Heart class="w-8 h-8 text-rose-400" />
                    <div><p class="stat-label">Wishlist</p><p class="stat-value text-2xl">{{ wishlist?.length || 0 }}</p></div>
                </Link>
                <div class="stat-card flex items-center gap-4">
                    <MapPin class="w-8 h-8 text-cyan-500" />
                    <div><p class="stat-label">Addresses</p><p class="stat-value text-2xl">{{ addresses?.length || 0 }}</p></div>
                </div>
                <Link href="/shop/notifications" class="stat-card flex items-center gap-4">
                    <Bell class="w-8 h-8 text-amber-500" />
                    <div><p class="stat-label">Alerts</p><p class="stat-value text-2xl">{{ unreadCount }}</p></div>
                </Link>
            </div>
            <div class="grid lg:grid-cols-2 gap-8">
                <div class="ph-card p-6">
                    <h2 class="font-semibold mb-4 flex items-center gap-2"><Package class="w-5 h-5" /> Recent Orders</h2>
                    <div v-if="!recentOrders?.length" class="text-slate-400 text-sm">No orders yet.</div>
                    <div v-for="o in recentOrders" :key="o.id" class="flex justify-between py-3 border-b border-slate-100 last:border-0">
                        <div>
                            <p class="font-medium">{{ o.order_number }}</p>
                            <p class="text-xs text-slate-400">{{ o.status }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold">₹{{ o.total }}</p>
                            <Link :href="`/shop/orders/${o.id}/track`" class="text-xs text-teal-600">Track</Link>
                        </div>
                    </div>
                </div>
                <div class="ph-card p-6">
                    <h2 class="font-semibold mb-4 flex items-center gap-2"><FileText class="w-5 h-5" /> Prescriptions</h2>
                    <Link href="/shop/prescriptions" class="text-teal-600 text-sm">Manage →</Link>
                    <div v-for="rx in prescriptions" :key="rx.id" class="mt-3 flex justify-between text-sm">
                        <span>{{ rx.file_name }}</span>
                        <span :class="rx.status === 'approved' ? 'badge-green' : 'badge-amber'" class="badge">{{ rx.status }}</span>
                    </div>
                </div>
            </div>
            <section class="mt-12">
                <h2 class="text-xl font-bold mb-4">Recommended for You</h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <MedicineCard v-for="p in recommended" :key="p.id" :product="p" />
                </div>
            </section>
        </div>
    </CustomerLayout>
</template>
