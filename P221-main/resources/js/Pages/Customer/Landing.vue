<script setup>
import { Head, Link } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import MedicineCard from '@/Components/Customer/MedicineCard.vue';
import { Shield, Truck, Stethoscope, Sparkles } from 'lucide-vue-next';

defineProps({
    featured: Array, trending: Array, categories: Array, offers: Array,
    testimonials: Array, healthTips: Array,
});
const heroImg = '/images/hero-pharmacy.svg';
</script>

<template>
    <Head title="PharmaCare — Online Pharmacy" />
    <CustomerLayout>
        <section class="relative overflow-hidden bg-gradient-to-br from-teal-50 via-cyan-50 to-rose-50 dark:from-slate-800 dark:via-slate-900 dark:to-slate-800 py-20 px-4">
            <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <span class="inline-flex items-center gap-2 px-4 py-1 rounded-full bg-white/80 text-teal-700 text-sm font-medium mb-4">
                        <Sparkles class="w-4 h-4" /> Trusted Healthcare Delivery
                    </span>
                    <h1 class="text-4xl md:text-5xl font-bold text-slate-900 dark:text-white leading-tight">
                        Your Health, <span class="text-teal-600">Delivered</span> with Care
                    </h1>
                    <p class="mt-4 text-lg text-slate-600 dark:text-slate-300 max-w-lg">
                        Order medicines online with prescription upload, AI safety checks, and real-time tracking.
                    </p>
                    <form class="mt-8 flex gap-2 max-w-md" action="/shop/medicines" method="get">
                        <input name="q" type="search" placeholder="Search medicines..." class="ph-input flex-1 !rounded-2xl" />
                        <button type="submit" class="btn btn-primary">Search</button>
                    </form>
                    <div class="flex flex-wrap gap-6 mt-10 text-sm text-slate-600 dark:text-slate-400">
                        <span class="flex items-center gap-2"><Shield class="w-5 h-5 text-teal-500" /> 100% Genuine</span>
                        <span class="flex items-center gap-2"><Truck class="w-5 h-5 text-teal-500" /> Fast Delivery</span>
                        <span class="flex items-center gap-2"><Stethoscope class="w-5 h-5 text-teal-500" /> AI Consultant</span>
                    </div>
                </div>
                <div class="hidden lg:block relative">
                    <div class="w-full aspect-square rounded-3xl overflow-hidden shadow-2xl bg-white p-6 flex items-center justify-center">
                        <img :src="heroImg" alt="Online pharmacy" class="w-full h-full object-contain" />
                    </div>
                </div>
            </div>
        </section>

        <section class="max-w-7xl mx-auto px-4 py-12">
            <h2 class="text-2xl font-bold mb-6">Shop by Category</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <Link v-for="c in categories" :key="c.id" :href="`/shop/medicines?category_id=${c.id}`"
                    class="ph-card p-6 text-center hover:scale-105 transition-transform">
                    <div class="w-12 h-12 mx-auto rounded-full bg-teal-100 flex items-center justify-center text-teal-700 font-bold">{{ c.name[0] }}</div>
                    <p class="mt-3 font-medium text-sm">{{ c.name }}</p>
                    <p class="text-xs text-slate-400">{{ c.count }} items</p>
                </Link>
            </div>
        </section>

        <section class="max-w-7xl mx-auto px-4 py-8">
            <div class="grid md:grid-cols-3 gap-4">
                <div v-for="o in offers" :key="o.code" class="rounded-2xl p-6 text-white"
                    :class="o.color === 'teal' ? 'bg-teal-600' : o.color === 'rose' ? 'bg-rose-400' : 'bg-amber-500'">
                    <h3 class="font-bold text-lg">{{ o.title }}</h3>
                    <p class="text-sm opacity-90 mt-1">Use code <strong>{{ o.code }}</strong></p>
                </div>
            </div>
        </section>

        <section class="max-w-7xl mx-auto px-4 py-12">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Featured Medicines</h2>
                <Link href="/shop/medicines" class="text-teal-600 font-medium text-sm">View all →</Link>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <MedicineCard v-for="p in featured" :key="p.id" :product="p" />
            </div>
        </section>

        <section class="bg-white dark:bg-slate-800 py-12">
            <div class="max-w-7xl mx-auto px-4">
                <h2 class="text-2xl font-bold mb-6">Trending Now</h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <MedicineCard v-for="p in trending" :key="p.id" :product="p" />
                </div>
            </div>
        </section>

        <section class="max-w-7xl mx-auto px-4 py-16 grid md:grid-cols-2 gap-12">
            <div>
                <h2 class="text-2xl font-bold mb-6">What Customers Say</h2>
                <div v-for="t in testimonials" :key="t.name" class="ph-card p-6 mb-4">
                    <p class="text-slate-600 dark:text-slate-300 italic">"{{ t.text }}"</p>
                    <p class="mt-3 font-semibold">— {{ t.name }}</p>
                </div>
            </div>
            <div>
                <h2 class="text-2xl font-bold mb-6">Health Tips</h2>
                <div v-for="tip in healthTips" :key="tip.title" class="ph-card p-6 mb-4 border-l-4 border-teal-400">
                    <h3 class="font-semibold text-teal-800 dark:text-teal-300">{{ tip.title }}</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-2">{{ tip.body }}</p>
                </div>
            </div>
        </section>
    </CustomerLayout>
</template>
