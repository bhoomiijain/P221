<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { Search, Menu, X, PlusCircle } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import CustomerAppLayout from '@/Layouts/CustomerAppLayout.vue';
import Toast from '@/Components/Toast.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const mobileOpen = ref(false);
const searchQ = ref('');

function search() {
    if (searchQ.value.trim()) {
        window.location.href = `/shop/medicines?q=${encodeURIComponent(searchQ.value)}`;
    }
}
</script>

<template>
    <!-- Logged-in: same portal layout as pharmacist/supplier -->
    <CustomerAppLayout v-if="user">
        <template v-if="$slots.title" #title><slot name="title" /></template>
        <template v-if="$slots['header-title']" #header-title><slot name="header-title" /></template>
        <slot />
    </CustomerAppLayout>

    <!-- Guest: marketing shell for landing & public browse -->
    <div v-else class="min-h-screen" style="background: var(--surface-bg);">
        <Toast />
        <header class="sticky top-0 z-40 bg-white shadow-sm border-b" style="border-color: var(--surface-border);">
            <div class="max-w-7xl mx-auto px-4 h-16 flex items-center gap-4">
                <button class="lg:hidden p-2" @click="mobileOpen = !mobileOpen">
                    <Menu v-if="!mobileOpen" class="w-5 h-5" /><X v-else class="w-5 h-5" />
                </button>
                <Link href="/shop" class="flex items-center gap-2 font-bold" style="color: var(--brand-primary);">
                    <div class="h-9 w-9 rounded-full bg-white shadow-sm flex items-center justify-center">
                        <PlusCircle class="w-5 h-5" style="color: var(--brand-cyan);" />
                    </div>
                    PharmaCare
                </Link>
                <form class="hidden md:flex flex-1 max-w-xl mx-4" @submit.prevent="search">
                    <div class="relative w-full">
                        <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                        <input v-model="searchQ" type="search" placeholder="Search medicines..." class="ph-input w-full !rounded-full" />
                    </div>
                </form>
                <nav class="hidden lg:flex gap-2 text-sm font-medium ml-auto">
                    <Link href="/shop/medicines" class="px-3 py-2 rounded-full hover:bg-teal-50">Medicines</Link>
                    <Link href="/shop/emergency" class="px-3 py-2 rounded-full text-rose-600">Emergency</Link>
                    <Link href="/login" class="btn btn-sm btn-secondary">Staff Login</Link>
                    <Link href="/login" class="btn btn-sm btn-primary">Sign In</Link>
                </nav>
            </div>
        </header>
        <main class="animate-fade-in-up"><slot /></main>
        <footer class="mt-12 py-10 text-white" style="background: var(--brand-primary);">
            <div class="max-w-7xl mx-auto px-4 text-center text-sm opacity-90">
                © {{ new Date().getFullYear() }} PharmaCare — Retail Pharmacy Management System (Final Year Project)
            </div>
        </footer>
    </div>
</template>
