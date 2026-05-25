<script setup>
import { X } from 'lucide-vue-next';
import { onMounted, onUnmounted } from 'vue';

const props = defineProps({
    show: Boolean,
    title: String,
    size: { type: String, default: 'md' }, // sm | md | lg | xl
});
const emit = defineEmits(['close']);

const sizeMap = {
    sm: '380px',
    md: '520px',
    lg: '680px',
    xl: '860px',
};

function handleKey(e) {
    if (e.key === 'Escape') emit('close');
}
onMounted(() => window.addEventListener('keydown', handleKey));
onUnmounted(() => window.removeEventListener('keydown', handleKey));
</script>

<template>
    <Teleport to="body">
        <Transition name="modal">
            <div v-if="show" class="modal-overlay" @click.self="emit('close')">
                <div class="modal-box" :style="{ maxWidth: sizeMap[size] }">
                    <div class="modal-header">
                        <h3 class="text-base font-semibold">{{ title }}</h3>
                        <button class="btn btn-icon btn-secondary" @click="emit('close')">
                            <X class="h-4 w-4" />
                        </button>
                    </div>
                    <div class="modal-body">
                        <slot />
                    </div>
                    <div v-if="$slots.footer" class="modal-footer">
                        <slot name="footer" />
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.modal-enter-active, .modal-leave-active {
    transition: opacity 0.2s ease;
}
.modal-enter-from, .modal-leave-to {
    opacity: 0;
}
.modal-enter-active .modal-box,
.modal-leave-active .modal-box {
    transition: transform 0.25s ease, opacity 0.25s ease;
}
.modal-enter-from .modal-box {
    transform: translateY(20px);
    opacity: 0;
}
.modal-leave-to .modal-box {
    transform: translateY(10px);
    opacity: 0;
}
</style>
