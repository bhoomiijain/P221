<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({ email: '', password: '', remember: false });
function submit() { form.post('/login'); }
</script>

<template>
    <Head title="Sign In" />
    <div class="min-h-screen flex items-center justify-center px-4" style="background: var(--surface-bg);">
        <div class="ph-card p-8 max-w-md w-full text-center">
            <h1 class="text-2xl font-bold mb-2" style="color: var(--brand-primary);">Customer Sign In</h1>
            <p class="text-sm text-slate-500 mb-6">Use the unified login for all roles.</p>
            <Link href="/login" class="btn btn-primary w-full">Go to Sign In Page</Link>
            <p class="text-sm mt-4">New customer? <Link href="/register" class="text-teal-700 font-semibold">Register</Link></p>
        </div>
    </div>
</template>
