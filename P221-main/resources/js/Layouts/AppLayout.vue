<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { pharmacistNavGroups, supplierNavGroups } from '@/config/navigation';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const navGroups = computed(() =>
    user.value?.role === 'supplier' ? supplierNavGroups : pharmacistNavGroups
);
</script>

<template>
    <PortalLayout
        :nav-groups="navGroups"
        brand-name="Pharmacy POS"
        profile-href="/profile"
        logout-url="/logout"
        :show-inventory-alerts="user?.role !== 'supplier'"
        search-placeholder="Search inventory, medicines..."
    >
        <template v-if="$slots.title" #title><slot name="title" /></template>
        <template v-if="$slots['header-title']" #header-title><slot name="header-title" /></template>
        <slot />
    </PortalLayout>
</template>
