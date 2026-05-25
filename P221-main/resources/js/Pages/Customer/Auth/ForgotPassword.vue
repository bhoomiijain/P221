<script setup>
import { Head, Link } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { ref } from 'vue';
const email = ref('');
function submit() { alert('Password reset link sent (demo). Check email.'); }
</script>

<template>
    <Head title="Forgot Password" />
    <CustomerLayout>
        <div class="max-w-md mx-auto px-4 py-16">
            <div class="ph-card p-8">
                <h1 class="text-2xl font-bold mb-4">Reset Password</h1>
                <p class="text-sm text-slate-500 mb-6">Enter your email to receive a reset link (demo UI).</p>
                <form @submit.prevent="submit" class="space-y-4">
                    <input v-model="email" type="email" class="ph-input !rounded-xl" placeholder="Email" required />
                    <button type="submit" class="btn btn-primary w-full">Send Reset Link</button>
                </form>
                <Link href="/shop/login" class="block text-center text-sm text-teal-600 mt-6">Back to login</Link>
            </div>
        </div>
    </CustomerLayout>
</template>
