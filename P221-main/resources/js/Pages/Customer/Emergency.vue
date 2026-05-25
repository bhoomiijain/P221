<script setup>
import { Head, Link } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { Phone, Ambulance, MapPin, Stethoscope } from 'lucide-vue-next';

defineProps({ nearbyPharmacies: Array, emergencyNumbers: Array });
</script>

<template>
    <Head title="Emergency" />
    <CustomerLayout>
        <div class="max-w-4xl mx-auto px-4 py-8">
            <h1 class="text-2xl font-bold text-rose-700 mb-2">Emergency & Quick Help</h1>
            <div class="grid sm:grid-cols-3 gap-4 mb-10">
                <a v-for="e in emergencyNumbers" :key="e.number" :href="`tel:${e.number}`"
                    class="ph-card p-6 text-center bg-rose-50 border-rose-200 hover:scale-105 transition-transform">
                    <Ambulance v-if="e.label === 'Ambulance'" class="w-10 h-10 mx-auto text-rose-600 mb-2" />
                    <Phone v-else class="w-10 h-10 mx-auto text-rose-600 mb-2" />
                    <p class="font-bold">{{ e.label }}</p>
                    <p class="text-2xl font-bold text-rose-700 mt-1">{{ e.number }}</p>
                </a>
            </div>
            <Link href="/shop/consultant" class="ph-card p-6 flex items-center gap-4 mb-10 hover:bg-teal-50">
                <Stethoscope class="w-10 h-10 text-teal-600" />
                <div><p class="font-bold">Consult Doctor / AI</p><p class="text-sm text-slate-500">Chat with our AI assistant now</p></div>
            </Link>
            <h2 class="text-xl font-bold mb-4 flex items-center gap-2"><MapPin class="w-5 h-5" /> Nearby Pharmacies</h2>
            <div v-for="p in nearbyPharmacies" :key="p.name" class="ph-card p-4 mb-3 flex justify-between">
                <div>
                    <p class="font-semibold">{{ p.name }}</p>
                    <p class="text-sm text-slate-500">{{ p.distance }} · {{ p.open ? 'Open' : 'Closed' }}</p>
                </div>
                <a :href="`tel:${p.phone}`" class="btn btn-sm btn-primary"><Phone class="w-4 h-4" /> Call</a>
            </div>
        </div>
    </CustomerLayout>
</template>
