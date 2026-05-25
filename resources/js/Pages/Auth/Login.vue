<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { Eye, EyeOff, Lock, Mail, ShoppingBag } from 'lucide-vue-next';
import { ref } from 'vue';

const page = usePage();
const form = useForm({
    email:    '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

function submit() {
    form.post('/login', {
        onFinish: () => { if (form.errors.password) form.reset('password'); },
    });
}

function fillDemo(account) {
    form.email = account.email;
    form.password = account.password;
}
</script>

<template>
    <div class="min-h-screen flex" style="background: #001E2B; font-family: 'Inter', sans-serif;">

        <!-- ── Left panel — branding ──────────────────────────────────────── -->
        <div class="hidden lg:flex lg:w-1/2 flex-col relative overflow-hidden"
             style="background: #001E2B; border-right: 1px solid #3D4F58;">

            <!-- Subtle pattern or clean bg (no neon orbs) -->
            <div class="absolute inset-0 opacity-5" style="background-image: radial-gradient(#00FF00 1px, transparent 1px); background-size: 32px 32px;"></div>

            <div class="relative flex flex-col justify-center h-full px-16">
                <!-- Logo -->
                <div class="flex items-center gap-4 mb-16">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl font-black text-xl"
                         style="background: #00FF00; color: #001E2B;">
                        Rx
                    </div>
                    <div>
                        <div class="text-xl font-black" style="color:#FFFFFF;">Retail Pharmacy</div>
                        <div class="text-sm" style="color:#C1C7C5;">POS Management System</div>
                    </div>
                </div>

                <h1 class="text-5xl font-black leading-tight mb-6"
                    style="color:#FFFFFF; letter-spacing: -0.03em;">
                    Manage your<br/>
                    <span style="color: #00FF00;">
                        pharmacy
                    </span><br/>smarter.
                </h1>
                <p class="text-base leading-relaxed mb-12" style="color:#C1C7C5;">
                    Billing, inventory, suppliers, and analytics —<br/>
                    everything in one place.
                </p>

                <!-- Feature pills -->
                <div class="flex flex-col gap-3">
                    <div v-for="feat in ['⚡  Instant medicine search with FIFO billing', '📦  Batch-level expiry tracking', '📊  Real-time revenue analytics', '🧾  PDF invoices & thermal receipts']"
                         :key="feat"
                         class="flex items-center gap-3 text-sm"
                         style="color:#888;">
                        <span>{{ feat }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Right panel — login form ───────────────────────────────────── -->
        <div class="flex-1 flex items-center justify-center px-6 py-12 relative" style="background: #F9FBFA;">

            <div class="relative w-full max-w-md">

                <!-- Mobile logo -->
                <div class="flex items-center gap-3 mb-10 lg:hidden">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl font-black"
                         style="background: #00FF00; color:#001E2B;">
                        Rx
                    </div>
                    <span class="text-lg font-black" style="color:#001E2B;">Retail Pharmacy POS</span>
                </div>

                <div class="mb-8">
                    <h2 class="text-3xl font-black mb-2" style="color:#001E2B; letter-spacing:-0.02em;">
                        Welcome back
                    </h2>
                    <p class="text-sm" style="color:#3D4F58;">Sign in to access your pharmacy dashboard</p>
                </div>

                <form @submit.prevent="submit" class="space-y-5">

                    <!-- Email -->
                    <div>
                        <label class="block text-xs font-bold mb-2 uppercase tracking-wider"
                               style="color:#3D4F58;">Email address</label>
                        <div class="relative">
                            <Mail class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4"
                                  style="color:#5C6C75;" />
                            <input
                                v-model="form.email"
                                type="email"
                                autocomplete="email"
                                placeholder="you@pharmacy.com"
                                class="w-full h-12 pl-10 pr-4 rounded-xl text-sm outline-none transition-all"
                                style="background:#FFFFFF; border: 1.5px solid #E8EDEB; color:#001E2B;"
                                :style="form.errors.email
                                    ? { borderColor: '#DB3030', boxShadow: '0 0 0 3px rgba(219,48,48,0.15)' }
                                    : {}"
                                @focus="e => !form.errors.email && (e.target.style.borderColor='#00FF00', e.target.style.boxShadow='0 0 0 3px rgba(0,255,0,0.12)')"
                                @blur="e => !form.errors.email && (e.target.style.borderColor='#E8EDEB', e.target.style.boxShadow='')"
                            />
                        </div>
                        <p v-if="form.errors.email" class="mt-1.5 text-xs" style="color:#DB3030;">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-xs font-bold mb-2 uppercase tracking-wider"
                               style="color:#3D4F58;">Password</label>
                        <div class="relative">
                            <Lock class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4"
                                  style="color:#5C6C75;" />
                            <input
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="w-full h-12 pl-10 pr-12 rounded-xl text-sm outline-none transition-all"
                                style="background:#FFFFFF; border: 1.5px solid #E8EDEB; color:#001E2B;"
                                :style="form.errors.password
                                    ? { borderColor: '#DB3030', boxShadow: '0 0 0 3px rgba(219,48,48,0.15)' }
                                    : {}"
                                @focus="e => !form.errors.password && (e.target.style.borderColor='#00FF00', e.target.style.boxShadow='0 0 0 3px rgba(0,255,0,0.12)')"
                                @blur="e => !form.errors.password && (e.target.style.borderColor='#E8EDEB', e.target.style.boxShadow='')"
                            />
                            <button type="button" tabindex="-1"
                                    class="absolute right-3.5 top-1/2 -translate-y-1/2"
                                    @click="showPassword = !showPassword">
                                <EyeOff v-if="showPassword" class="h-4 w-4" style="color:#5C6C75;" />
                                <Eye v-else class="h-4 w-4" style="color:#5C6C75;" />
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="mt-1.5 text-xs" style="color:#DB3030;">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Remember me -->
                    <div class="flex items-center gap-2.5">
                        <button
                            type="button"
                            role="checkbox"
                            :aria-checked="form.remember"
                            class="h-5 w-5 rounded flex items-center justify-center transition-all shrink-0"
                            :style="form.remember
                                ? { background:'#00FF00', border:'1.5px solid #00FF00' }
                                : { background:'#FFFFFF', border:'1.5px solid #3D4F58' }"
                            @click="form.remember = !form.remember"
                        >
                            <svg v-if="form.remember" class="h-3 w-3" fill="none" viewBox="0 0 12 12">
                                <path d="M2 6l3 3 5-5" stroke="#001E2B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                        <span class="text-sm" style="color:#3D4F58;">Keep me signed in</span>
                    </div>

                    <!-- Submit -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full h-12 rounded-xl font-bold text-sm tracking-wide transition-all relative overflow-hidden"
                        style="background: #00FF00; color:#001E2B; border: 1px solid transparent;"
                        onmouseover="this.style.background='#00A35C'; this.style.color='#FFFFFF';"
                        onmouseout="this.style.background='#00FF00'; this.style.color='#001E2B';"
                    >
                        <span v-if="!form.processing">Sign In →</span>
                        <span v-else class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity="0.3"/>
                                <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                            </svg>
                            Signing in...
                        </span>
                    </button>
                </form>

                <!-- Register link -->
                <p class="mt-6 text-center text-sm" style="color:#5C6C75;">
                    Don't have an account?
                    <a href="/register" class="font-bold hover:underline ml-1" style="color:#001E2B;">Register here →</a>
                </p>

                <a href="/shop"
                   class="mt-4 flex items-center justify-center gap-2 w-full h-11 rounded-xl text-sm font-bold transition-all"
                   style="background:#FFFFFF; color:#064e3b; border: 1.5px solid #E8EDEB;">
                    <ShoppingBag class="h-4 w-4" />
                    Browse Online Shop (guest)
                </a>

                <!-- Footer -->
                <p class="mt-4 text-center text-xs" style="color:#5C6C75;">
                    © {{ new Date().getFullYear() }} Retail Pharmacy POS · Secure session login
                </p>
            </div>
        </div>
    </div>
</template>
