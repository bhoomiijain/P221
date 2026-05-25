<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

const form = useForm({ name: '', email: '', phone: '', password: '', password_confirmation: '' });
function submit() { form.post('/shop/register'); }
</script>

<template>
    <Head title="Register" />
    <CustomerLayout>
        <div class="max-w-md mx-auto px-4 py-16">
            <div class="ph-card p-8">
                <h1 class="text-2xl font-bold text-center mb-8">Create Account</h1>
                <form @submit.prevent="submit" class="space-y-4">
                    <input v-model="form.name" placeholder="Full name" class="ph-input !rounded-xl" required />
                    <input v-model="form.email" type="email" placeholder="Email" class="ph-input !rounded-xl" required />
                    <input v-model="form.phone" placeholder="Phone" class="ph-input !rounded-xl" />
                    <input v-model="form.password" type="password" placeholder="Password" class="ph-input !rounded-xl" required />
                    <input v-model="form.password_confirmation" type="password" placeholder="Confirm password" class="ph-input !rounded-xl" required />
                    <button type="submit" class="btn btn-primary w-full" :disabled="form.processing">Register</button>
                </form>
                <p class="text-center text-sm mt-6">Already have an account? <Link href="/shop/login" class="text-teal-600">Login</Link></p>
            </div>
        </div>
    </CustomerLayout>
</template>
