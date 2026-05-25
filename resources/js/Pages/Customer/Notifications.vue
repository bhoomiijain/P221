<script setup>
import { Head } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { useCustomerApi } from '@/composables/useCustomerApi';
import { onMounted, ref } from 'vue';

const props = defineProps({ notifications: Object });
const items = ref(props.notifications?.data || []);
const api = useCustomerApi();

onMounted(async () => {
    const { data } = await api.notifications();
    items.value = data.notifications;
});

async function markRead(id) {
    await api.markRead(id);
    const { data } = await api.notifications();
    items.value = data.notifications;
}
</script>

<template>
    <Head title="Notifications" />
    <CustomerLayout>
        <div class="max-w-2xl mx-auto px-4 py-8">
            <h1 class="text-2xl font-bold mb-6">Notifications</h1>
            <div v-for="n in items" :key="n.id || n._id"
                class="ph-card p-4 mb-3 cursor-pointer" :class="{ 'opacity-60': n.read_at }" @click="markRead(n.id || n._id)">
                <p class="font-medium">{{ n.title }}</p>
                <p class="text-sm text-slate-500 mt-1">{{ n.message }}</p>
                <span class="badge badge-blue mt-2">{{ n.type }}</span>
            </div>
        </div>
    </CustomerLayout>
</template>
