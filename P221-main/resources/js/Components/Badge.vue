<script setup>
defineProps({
    variant: { type: String, default: 'gray' }, // green | amber | red | blue | gray | purple
    dot: { type: Boolean, default: false },
    size: { type: String, default: 'sm' },
});
</script>

<template>
    <span :class="['badge', `badge-${variant}`]">
        <span v-if="dot" class="w-1.5 h-1.5 rounded-full inline-block" :class="{
            'bg-emerald-500': variant === 'green',
            'bg-amber-500': variant === 'amber',
            'bg-red-500': variant === 'red',
            'bg-blue-500': variant === 'blue',
            'bg-slate-400': variant === 'gray',
            'bg-purple-500': variant === 'purple',
        }"></span>
        <slot />
    </span>
</template>
