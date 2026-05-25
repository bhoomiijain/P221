<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { useCustomerApi } from '@/composables/useCustomerApi';
import { onMounted, ref } from 'vue';
import { useToast } from '@/composables/useToast';

const props = defineProps({ user: Object });
const form = useForm({
    name: props.user?.name, phone: props.user?.phone || '',
    address: props.user?.address || '', city: props.user?.city || '',
    state: props.user?.state || '', pincode: props.user?.pincode || '',
});
const api = useCustomerApi();
const toast = useToast();
const addresses = ref([]);
const newAddr = ref({ label: 'Home', name: '', phone: '', address_line: '', city: '', state: '', pincode: '', is_default: true });

onMounted(async () => {
    try {
        const { data } = await api.addresses();
        addresses.value = data.addresses;
    } catch { /* */ }
});

function saveProfile() {
    form.put('/shop/profile', { preserveScroll: true, onSuccess: () => toast.success('Profile updated') });
}
async function addAddress() {
    await api.saveAddress(newAddr.value);
    toast.success('Address saved');
    const { data } = await api.addresses();
    addresses.value = data.addresses;
}
</script>

<template>
    <Head title="Profile" />
    <CustomerLayout>
        <div class="max-w-3xl mx-auto px-4 py-8 space-y-8">
            <h1 class="text-2xl font-bold">Profile Settings</h1>
            <div class="ph-card p-6">
                <h3 class="font-semibold mb-4">Personal Info</h3>
                <form @submit.prevent="saveProfile" class="space-y-4">
                    <input v-model="form.name" class="ph-input !rounded-xl" placeholder="Name" />
                    <input v-model="form.phone" class="ph-input !rounded-xl" placeholder="Phone" />
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
            </div>
            <div class="ph-card p-6">
                <h3 class="font-semibold mb-4">Saved Addresses</h3>
                <div v-for="a in addresses" :key="a.id || a._id" class="text-sm py-3 border-b">{{ a.name }} — {{ a.address_line }}, {{ a.city }}</div>
                <div class="mt-4 grid gap-3">
                    <input v-model="newAddr.name" placeholder="Name" class="ph-input !rounded-xl !h-10" />
                    <input v-model="newAddr.phone" placeholder="Phone" class="ph-input !rounded-xl !h-10" />
                    <input v-model="newAddr.address_line" placeholder="Address" class="ph-input !rounded-xl !h-10" />
                    <div class="grid grid-cols-3 gap-2">
                        <input v-model="newAddr.city" placeholder="City" class="ph-input !rounded-xl !h-10" />
                        <input v-model="newAddr.state" placeholder="State" class="ph-input !rounded-xl !h-10" />
                        <input v-model="newAddr.pincode" placeholder="PIN" class="ph-input !rounded-xl !h-10" />
                    </div>
                    <button type="button" class="btn btn-secondary" @click="addAddress">Add Address</button>
                </div>
            </div>
        </div>
    </CustomerLayout>
</template>
