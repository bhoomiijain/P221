<script setup>
import { CheckCircle, Info, AlertTriangle, XCircle, X } from 'lucide-vue-next';
import { useToast } from '@/composables/useToast.js';

const { toasts, remove } = useToast();

const icons = {
    success: CheckCircle,
    error:   XCircle,
    warning: AlertTriangle,
    info:    Info,
};

const colors = {
    success: { bg: '#f0fdf4', border: '#bbf7d0', icon: '#16a34a', text: '#14532d' },
    error:   { bg: '#fef2f2', border: '#fecaca', icon: '#dc2626', text: '#7f1d1d' },
    warning: { bg: '#fffbeb', border: '#fde68a', icon: '#d97706', text: '#78350f' },
    info:    { bg: '#eff6ff', border: '#bfdbfe', icon: '#2563eb', text: '#1e3a8a' },
};

const darkColors = {
    success: { bg: '#052e16', border: '#166534', icon: '#4ade80', text: '#bbf7d0' },
    error:   { bg: '#450a0a', border: '#7f1d1d', icon: '#f87171', text: '#fecaca' },
    warning: { bg: '#451a03', border: '#92400e', icon: '#fbbf24', text: '#fde68a' },
    info:    { bg: '#1e3a5f', border: '#1e40af', icon: '#60a5fa', text: '#bfdbfe' },
};
</script>

<template>
    <Teleport to="body">
        <div class="fixed bottom-6 right-6 z-[200] flex flex-col gap-3 pointer-events-none" style="max-width: 380px; width: 100%;">
            <TransitionGroup name="toast">
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    class="pointer-events-auto flex items-start gap-3 rounded-xl border p-4 shadow-lg backdrop-blur"
                    :style="{
                        background: colors[toast.type]?.bg,
                        borderColor: colors[toast.type]?.border,
                    }"
                    style="animation: slideInRight 0.3s ease;"
                >
                    <component
                        :is="icons[toast.type]"
                        class="h-5 w-5 shrink-0 mt-0.5"
                        :style="{ color: colors[toast.type]?.icon }"
                    />
                    <p class="text-sm font-medium flex-1" :style="{ color: colors[toast.type]?.text }">
                        {{ toast.message }}
                    </p>
                    <button
                        class="shrink-0 opacity-60 hover:opacity-100 transition-opacity"
                        :style="{ color: colors[toast.type]?.text }"
                        @click="remove(toast.id)"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<style scoped>
.toast-enter-active { animation: slideInRight 0.3s ease; }
.toast-leave-active { animation: slideInRight 0.25s ease reverse; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateX(24px); }
</style>
