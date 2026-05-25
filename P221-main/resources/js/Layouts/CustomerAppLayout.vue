<script setup>
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { customerNavGroups } from '@/config/navigation';

defineProps({
    pageTitle: { type: String, default: '' },
});
</script>

<template>
    <PortalLayout
        :nav-groups="customerNavGroups"
        brand-name="PharmaCare Shop"
        profile-href="/shop/profile"
        logout-url="/shop/logout"
        :show-inventory-alerts="false"
        search-placeholder="Search medicines..."
    >
        <template v-if="pageTitle || $slots.title" #title>
            <slot name="title">{{ pageTitle }}</slot>
        </template>
        <template v-if="$slots['header-title']" #header-title><slot name="header-title" /></template>
        <slot />
    </PortalLayout>
</template>
