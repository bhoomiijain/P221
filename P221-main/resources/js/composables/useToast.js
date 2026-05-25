// composables/useToast.js
import { reactive } from 'vue';

const toasts = reactive([]);
let counter = 0;

export function useToast() {
    function add(message, type = 'success', duration = 4000) {
        const id = ++counter;
        toasts.push({ id, message, type });
        setTimeout(() => remove(id), duration);
    }

    function remove(id) {
        const idx = toasts.findIndex(t => t.id === id);
        if (idx !== -1) toasts.splice(idx, 1);
    }

    return {
        toasts,
        success: (msg, dur) => add(msg, 'success', dur),
        error:   (msg, dur) => add(msg, 'error', dur),
        warning: (msg, dur) => add(msg, 'warning', dur),
        info:    (msg, dur) => add(msg, 'info', dur),
        remove,
    };
}
