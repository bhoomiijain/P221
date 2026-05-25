<script setup>
import { Head, Link } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { ref } from 'vue';
const otp = ref(['', '', '', '', '', '']);
function verify() {
    const code = otp.value.join('');
    if (code === '123456' || code.length === 6) window.location.href = '/shop/dashboard';
    else alert('Demo OTP: 123456');
}
</script>

<template>
    <Head title="Verify OTP" />
    <CustomerLayout>
        <div class="max-w-md mx-auto px-4 py-16 text-center">
            <div class="ph-card p-8">
                <h1 class="text-2xl font-bold mb-2">Verify OTP</h1>
                <p class="text-sm text-slate-500 mb-8">Enter the 6-digit code sent to your phone (demo: 123456)</p>
                <div class="flex justify-center gap-2 mb-6">
                    <input v-for="(d, i) in otp" :key="i" v-model="otp[i]" maxlength="1"
                        class="w-12 h-14 text-center text-xl border rounded-xl border-teal-200 focus:border-teal-500 outline-none" />
                </div>
                <button type="button" class="btn btn-primary w-full" @click="verify">Verify</button>
                <Link href="/shop/dashboard" class="block text-sm text-teal-600 mt-4">Skip (demo)</Link>
            </div>
        </div>
    </CustomerLayout>
</template>
