<script setup>
import { Head } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { useCustomerApi } from '@/composables/useCustomerApi';
import { useToast } from '@/composables/useToast';
import { ref } from 'vue';
import { Upload, FileText } from 'lucide-vue-next';

defineProps({ prescriptions: Array });
const api = useCustomerApi();
const toast = useToast();
const uploading = ref(false);

async function onFile(e) {
    const file = e.target.files?.[0];
    if (!file) return;
    const fd = new FormData();
    fd.append('file', file);
    uploading.value = true;
    try {
        await api.uploadPrescription(fd);
        toast.success('Prescription uploaded');
        window.location.reload();
    } catch {
        toast.error('Upload failed');
    } finally {
        uploading.value = false;
    }
}
</script>

<template>
    <Head title="Prescriptions" />
    <CustomerLayout>
        <div class="max-w-3xl mx-auto px-4 py-8">
            <h1 class="text-2xl font-bold mb-6">My Prescriptions</h1>
            <div class="ph-card p-8 border-2 border-dashed border-teal-200 text-center mb-8">
                <Upload class="w-10 h-10 mx-auto text-teal-500 mb-3" />
                <p class="font-medium">Upload prescription (JPG, PNG, PDF)</p>
                <p class="text-xs text-slate-400 mt-1">OCR-ready structure for future AI parsing</p>
                <input type="file" accept="image/*,.pdf" class="mt-4" :disabled="uploading" @change="onFile" />
            </div>
            <div v-for="rx in prescriptions" :key="rx.id || rx._id" class="ph-card p-4 flex items-center gap-4 mb-3">
                <FileText class="w-8 h-8 text-teal-500" />
                <div class="flex-1">
                    <p class="font-medium">{{ rx.file_name }}</p>
                    <p class="text-xs text-slate-400">Uploaded {{ rx.created_at }}</p>
                </div>
                <span :class="rx.status === 'approved' ? 'badge-green' : rx.status === 'rejected' ? 'badge-red' : 'badge-amber'" class="badge">
                    {{ rx.status || 'pending' }}
                </span>
            </div>
        </div>
    </CustomerLayout>
</template>
