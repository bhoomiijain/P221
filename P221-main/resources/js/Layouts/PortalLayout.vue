<script setup>
import { Link, usePage, useForm } from '@inertiajs/vue3';
import {
    Bell, X, LogOut, ChevronLeft, AlertTriangle, Menu, Search, Globe, ChevronDown, PlusCircle,
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import axios from 'axios';
import Toast from '@/Components/Toast.vue';

const props = defineProps({
    navGroups: { type: Array, required: true },
    brandName: { type: String, default: 'Pharmacy' },
    profileHref: { type: String, default: '/profile' },
    logoutUrl: { type: String, default: '/logout' },
    showInventoryAlerts: { type: Boolean, default: false },
    searchPlaceholder: { type: String, default: 'Search' },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const showNotifications = ref(false);
const alerts = ref([]);
const alertCount = ref(0);
const sidebarOpen = ref(false);
const desktopSidebarOpen = ref(true);
const logoutForm = useForm({});

const completion = computed(() => user.value?.profileCompletion ?? 0);
const RADIUS = 18;
const CIRC = 2 * Math.PI * RADIUS;
const ringOffset = computed(() => CIRC - (completion.value / 100) * CIRC);
const ringColor = computed(() => {
    if (completion.value >= 80) return '#10b981';
    if (completion.value >= 50) return '#f59e0b';
    return '#ef4444';
});

function isActive(href) {
    const url = page.url.split('?')[0];
    if (href === '/' || href === '/shop/dashboard') {
        return url === href || (href === '/' && url === '/');
    }
    return url.startsWith(href);
}

async function loadAlerts() {
    if (!props.showInventoryAlerts) return;
    try {
        const { data } = await axios.get('/api/inventory/alerts');
        alerts.value = data;
        alertCount.value = data.length;
    } catch {}
}

async function dismissAlert(id) {
    try {
        await axios.put(`/api/inventory/alerts/${id}/resolve`);
        alerts.value = alerts.value.filter(a => (a._id || a.id) !== id);
        alertCount.value = alerts.value.length;
    } catch {}
}

onMounted(() => {
    document.documentElement.classList.remove('dark');
    loadAlerts();
    document.addEventListener('click', e => {
        if (!e.target.closest('[data-notif]')) showNotifications.value = false;
    });
});
</script>

<template>
    <div class="min-h-screen flex" style="background: var(--surface-bg); color: var(--text-primary);">
        <Toast />
        <div v-if="sidebarOpen" class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false" />
        <aside
            :class="['fixed inset-y-0 left-0 z-50 flex flex-col transition-transform duration-300',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
                desktopSidebarOpen ? 'lg:translate-x-0' : 'lg:-translate-x-full']"
            style="width: var(--sidebar-width); background: var(--surface-bg);"
        >
            <div class="flex items-center justify-between px-6 py-8">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full font-black bg-white shadow-sm" style="color: var(--brand-primary);">
                        <PlusCircle class="h-6 w-6" />
                    </div>
                    <div class="text-xl font-bold tracking-tight" style="color: var(--brand-primary);">{{ brandName }}</div>
                </div>
                <button class="lg:hidden btn btn-icon btn-sm" style="background: transparent;" @click="sidebarOpen = false"><X class="h-5 w-5" /></button>
                <button class="hidden lg:flex btn btn-icon btn-sm" style="background: transparent;" @click="desktopSidebarOpen = false"><ChevronLeft class="h-5 w-5" /></button>
            </div>
            <nav class="flex-1 overflow-y-auto px-6 space-y-8 pb-8">
                <div v-for="group in navGroups" :key="group.title">
                    <p class="mb-4 text-[11px] font-bold uppercase tracking-widest pl-2" style="color: var(--text-muted);">{{ group.title }}</p>
                    <div class="space-y-1">
                        <Link v-for="item in group.items" :key="item.href" :href="item.href" class="nav-link" :class="{ active: isActive(item.href) }" @click="sidebarOpen = false">
                            <component :is="item.icon" class="h-5 w-5 opacity-80" />
                            <span class="font-semibold">{{ item.label }}</span>
                        </Link>
                    </div>
                </div>
            </nav>
            <div class="px-6 pb-6 mt-auto">
                <Link :href="profileHref" class="rounded-2xl p-4 flex flex-col items-center text-center block hover:opacity-90" style="background: rgba(56,178,172,0.15); text-decoration: none;">
                    <div class="h-12 w-12 rounded-full flex items-center justify-center text-white font-bold mb-3" style="background: var(--brand-primary);">
                        {{ (user?.name || 'U')[0].toUpperCase() }}
                    </div>
                    <h4 class="text-sm font-bold mb-1" style="color: var(--brand-primary);">{{ user?.name || 'User' }}</h4>
                    <p class="text-[11px] capitalize mb-3" style="color: var(--text-secondary);">{{ user?.role }}</p>
                    <span class="btn btn-primary w-full text-xs py-2 rounded-xl">View Profile</span>
                </Link>
                <button class="mt-4 flex items-center justify-center gap-2 w-full rounded-full py-2.5 text-xs font-semibold" style="color: var(--brand-rose);" @click="logoutForm.post(logoutUrl)">
                    <LogOut class="h-4 w-4" /> Sign Out
                </button>
            </div>
        </aside>
        <div class="flex flex-col flex-1 min-h-screen transition-all duration-300" :class="desktopSidebarOpen ? 'lg:pl-[280px]' : 'lg:pl-0'">
            <header class="h-20 flex items-center justify-between px-6 lg:px-8">
                <div class="flex items-center gap-4 flex-1">
                    <button class="btn btn-icon bg-white shadow-sm" :class="desktopSidebarOpen ? 'lg:hidden' : 'flex'" @click="desktopSidebarOpen = true; sidebarOpen = true"><Menu class="h-5 w-5" /></button>
                    <div class="hidden sm:flex items-center bg-white rounded-full px-4 py-2 w-80 shadow-sm border border-transparent focus-within:border-[var(--brand-cyan)]">
                        <Search class="h-4 w-4 mr-2" style="color: var(--text-muted);" />
                        <input type="text" :placeholder="searchPlaceholder" class="bg-transparent border-none outline-none text-sm w-full" />
                    </div>
                </div>
                <div class="flex items-center gap-4 shrink-0">
                    <div v-if="showInventoryAlerts" class="relative" data-notif>
                        <button class="h-10 w-10 rounded-full bg-white shadow-sm flex items-center justify-center relative" @click.stop="showNotifications = !showNotifications">
                            <Bell class="h-4 w-4" />
                            <span v-if="alertCount > 0" class="absolute top-0 right-0 h-3 w-3 rounded-full border-2 border-white" style="background: var(--brand-rose);" />
                        </button>
                        <div v-if="showNotifications" class="dropdown-menu right-0 top-12 w-80" data-notif>
                            <div class="px-4 py-3 border-b font-bold text-sm">Alerts</div>
                            <div v-if="!alerts.length" class="px-4 py-8 text-center text-sm text-slate-400">No alerts</div>
                            <div v-else class="max-h-72 overflow-y-auto">
                                <div v-for="alert in alerts" :key="alert._id || alert.id" class="alert-item">
                                    <AlertTriangle class="h-4 w-4 shrink-0" />
                                    <p class="text-xs flex-1">{{ alert.message }}</p>
                                    <button class="text-xs underline" @click="dismissAlert(alert._id || alert.id)">Dismiss</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <Link :href="profileHref" class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full flex items-center justify-center text-white font-bold text-sm" style="background: var(--brand-primary);">
                            {{ (user?.name || 'U')[0].toUpperCase() }}
                        </div>
                        <div class="hidden sm:block">
                            <div class="text-sm font-bold">{{ user?.name }}</div>
                            <div class="text-xs capitalize text-slate-500">{{ user?.role }}</div>
                        </div>
                        <ChevronDown class="hidden sm:block h-4 w-4 text-slate-400" />
                    </Link>
                </div>
            </header>
            <main class="flex-1 px-4 lg:px-8 pb-8 flex flex-col">
                <div class="flex-1 bg-white rounded-[2rem] shadow-[0_4px_24px_rgba(0,0,0,0.02)] p-6 md:p-8 flex flex-col min-h-[60vh]">
                    <div v-if="$slots.title" class="mb-6 flex items-center justify-between flex-wrap gap-4">
                        <h1 class="text-2xl font-bold" style="color: var(--brand-primary);"><slot name="title" /></h1>
                        <slot name="header-title" />
                    </div>
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
