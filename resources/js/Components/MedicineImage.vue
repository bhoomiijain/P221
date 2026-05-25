<script setup>
import { computed } from 'vue';
import { medicineImageUrl } from '@/utils/medicineImages';

const props = defineProps({
    name: { type: String, default: '' },
    brand: { type: String, default: '' },
    src: { type: String, default: '' },
    alt: { type: String, default: '' },
    class: { type: String, default: 'max-h-full max-w-full object-contain' },
});

const url = computed(() => medicineImageUrl(props.name, props.brand, props.src));
</script>

<template>
    <img :src="url" :alt="alt || name" :class="class" loading="lazy" />
</template>
