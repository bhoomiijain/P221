<script setup>
import { useForm } from '@inertiajs/vue3';
import { Eye, EyeOff, Lock, Mail, User, Pill, Truck, ShoppingBag } from 'lucide-vue-next';
import { ref } from 'vue';

const form = useForm({
    name:                 '',
    email:                '',
    password:             '',
    password_confirmation:'',
    role:                 'pharmacist',
});

const showPassword = ref(false);

const roleLabels = {
    pharmacist: 'Pharmacist',
    supplier: 'Supplier',
    customer: 'Customer',
};

function submit() {
    form.post('/register', {
        onFinish: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
            }
        },
    });
}
</script>

<template>
    <div class="min-h-screen flex" style="background: #001E2B; font-family: 'Inter', sans-serif;">

        <!-- ── Left panel — branding ──────────────────────────────────────── -->
        <div class="hidden lg:flex lg:w-1/2 flex-col relative overflow-hidden"
             style="background: #001E2B; border-right: 1px solid #3D4F58;">

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
                    Join the<br/>
                    <span style="color: #00FF00;">pharmacy</span><br/>network.
                </h1>
                <p class="text-base leading-relaxed mb-12" style="color:#C1C7C5;">
                    Sign up as a <strong style="color:#fff;">Pharmacist</strong>, <strong style="color:#fff;">Supplier</strong>,<br/>
                    or <strong style="color:#fff;">Customer</strong> to order medicines online.
                </p>

                <!-- Role descriptions -->
                <div class="space-y-4">
                    <div class="flex items-start gap-3 p-4 rounded-2xl" style="background: rgba(0,255,0,0.07); border: 1px solid rgba(0,255,0,0.15);">
                        <Pill class="h-5 w-5 mt-0.5 shrink-0" style="color: #00FF00;" />
                        <div>
                            <div class="text-sm font-bold" style="color: #fff;">Pharmacist</div>
                            <div class="text-xs" style="color: #C1C7C5;">Manage inventory, billing, sales, and prescriptions</div>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-4 rounded-2xl" style="background: rgba(0,255,0,0.07); border: 1px solid rgba(0,255,0,0.15);">
                        <Truck class="h-5 w-5 mt-0.5 shrink-0" style="color: #00FF00;" />
                        <div>
                            <div class="text-sm font-bold" style="color: #fff;">Supplier</div>
                            <div class="text-xs" style="color: #C1C7C5;">Manage your medicine catalog and fulfill pharmacy orders</div>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-4 rounded-2xl" style="background: rgba(0,255,0,0.07); border: 1px solid rgba(0,255,0,0.15);">
                        <ShoppingBag class="h-5 w-5 mt-0.5 shrink-0" style="color: #00FF00;" />
                        <div>
                            <div class="text-sm font-bold" style="color: #fff;">Customer</div>
                            <div class="text-xs" style="color: #C1C7C5;">Browse medicines, order online, and track delivery at /shop</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Right panel — register form ───────────────────────────────────── -->
        <div class="flex-1 flex items-center justify-center px-6 py-12 relative overflow-y-auto" style="background: #F9FBFA;">

            <div class="relative w-full max-w-lg">

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
                        Create an account
                    </h2>
                    <p class="text-sm" style="color:#3D4F58;">Choose your role and create your account.</p>
                </div>

                <form @submit.prevent="submit" class="space-y-4">

                    <!-- Role Selection -->
                    <div>
                        <label class="block text-xs font-bold mb-2 uppercase tracking-wider"
                               style="color:#3D4F58;">I am a</label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <label
                                class="flex flex-col items-center gap-2 p-3 rounded-xl text-sm font-bold cursor-pointer transition-all"
                                :style="form.role === 'pharmacist'
                                    ? { background: '#00FF00', color: '#001E2B', border: '2px solid #00FF00' }
                                    : { background: '#FFFFFF', color: '#5C6C75', border: '1.5px solid #E8EDEB' }"
                                @click="form.role = 'pharmacist'"
                            >
                                <Pill class="h-5 w-5" />
                                Pharmacist
                                <span class="text-[10px] font-normal text-center leading-tight opacity-70">Inventory &amp; billing</span>
                            </label>
                            <label
                                class="flex flex-col items-center gap-2 p-3 rounded-xl text-sm font-bold cursor-pointer transition-all"
                                :style="form.role === 'supplier'
                                    ? { background: '#00FF00', color: '#001E2B', border: '2px solid #00FF00' }
                                    : { background: '#FFFFFF', color: '#5C6C75', border: '1.5px solid #E8EDEB' }"
                                @click="form.role = 'supplier'"
                            >
                                <Truck class="h-5 w-5" />
                                Supplier
                                <span class="text-[10px] font-normal text-center leading-tight opacity-70">Fulfill orders</span>
                            </label>
                            <label
                                class="flex flex-col items-center gap-2 p-3 rounded-xl text-sm font-bold cursor-pointer transition-all"
                                :style="form.role === 'customer'
                                    ? { background: '#00FF00', color: '#001E2B', border: '2px solid #00FF00' }
                                    : { background: '#FFFFFF', color: '#5C6C75', border: '1.5px solid #E8EDEB' }"
                                @click="form.role = 'customer'"
                            >
                                <ShoppingBag class="h-5 w-5" />
                                Customer
                                <span class="text-[10px] font-normal text-center leading-tight opacity-70">Shop at /shop</span>
                            </label>
                        </div>
                    </div>

                    <!-- Name -->
                    <div>
                        <label class="block text-xs font-bold mb-2 uppercase tracking-wider"
                               style="color:#3D4F58;">Full Name</label>
                        <div class="relative">
                            <User class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4" style="color:#5C6C75;" />
                            <input
                                v-model="form.name"
                                type="text"
                                autocomplete="name"
                                placeholder="John Doe"
                                class="w-full h-12 pl-10 pr-4 rounded-xl text-sm outline-none transition-all"
                                style="background:#FFFFFF; border: 1.5px solid #E8EDEB; color:#001E2B;"
                                :style="form.errors.name ? { borderColor: '#DB3030', boxShadow: '0 0 0 3px rgba(219,48,48,0.15)' } : {}"
                            />
                        </div>
                        <p v-if="form.errors.name" class="mt-1.5 text-xs" style="color:#DB3030;">{{ form.errors.name }}</p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-xs font-bold mb-2 uppercase tracking-wider"
                               style="color:#3D4F58;">Email Address</label>
                        <div class="relative">
                            <Mail class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4" style="color:#5C6C75;" />
                            <input
                                v-model="form.email"
                                type="email"
                                autocomplete="email"
                                placeholder="you@example.com"
                                class="w-full h-12 pl-10 pr-4 rounded-xl text-sm outline-none transition-all"
                                style="background:#FFFFFF; border: 1.5px solid #E8EDEB; color:#001E2B;"
                                :style="form.errors.email ? { borderColor: '#DB3030', boxShadow: '0 0 0 3px rgba(219,48,48,0.15)' } : {}"
                            />
                        </div>
                        <p v-if="form.errors.email" class="mt-1.5 text-xs" style="color:#DB3030;">{{ form.errors.email }}</p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-xs font-bold mb-2 uppercase tracking-wider" style="color:#3D4F58;">Password</label>
                        <div class="relative">
                            <Lock class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4" style="color:#5C6C75;" />
                            <input
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                placeholder="Min. 8 characters"
                                class="w-full h-12 pl-10 pr-12 rounded-xl text-sm outline-none transition-all"
                                style="background:#FFFFFF; border: 1.5px solid #E8EDEB; color:#001E2B;"
                                :style="form.errors.password ? { borderColor: '#DB3030', boxShadow: '0 0 0 3px rgba(219,48,48,0.15)' } : {}"
                            />
                        </div>
                        <p v-if="form.errors.password" class="mt-1.5 text-xs" style="color:#DB3030;">{{ form.errors.password }}</p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label class="block text-xs font-bold mb-2 uppercase tracking-wider" style="color:#3D4F58;">Confirm Password</label>
                        <div class="relative">
                            <Lock class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4" style="color:#5C6C75;" />
                            <input
                                v-model="form.password_confirmation"
                                :type="showPassword ? 'text' : 'password'"
                                placeholder="Repeat password"
                                class="w-full h-12 pl-10 pr-12 rounded-xl text-sm outline-none transition-all"
                                style="background:#FFFFFF; border: 1.5px solid #E8EDEB; color:#001E2B;"
                            />
                            <button type="button" tabindex="-1" class="absolute right-3.5 top-1/2 -translate-y-1/2"
                                    @click="showPassword = !showPassword">
                                <EyeOff v-if="showPassword" class="h-4 w-4" style="color:#5C6C75;" />
                                <Eye v-else class="h-4 w-4" style="color:#5C6C75;" />
                            </button>
                        </div>
                    </div>

                    <!-- Submit -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full h-12 mt-4 rounded-xl font-bold text-sm tracking-wide transition-all"
                        style="background: #00FF00; color:#001E2B;"
                        onmouseover="this.style.background='#00A35C'; this.style.color='#FFFFFF';"
                        onmouseout="this.style.background='#00FF00'; this.style.color='#001E2B';"
                    >
                        <span v-if="!form.processing">
                            Create {{ roleLabels[form.role] }} Account →
                        </span>
                        <span v-else class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity="0.3"/>
                                <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                            </svg>
                            Registering...
                        </span>
                    </button>

                    <div class="text-center mt-4">
                        <a href="/login" class="text-sm font-semibold hover:underline" style="color:#3D4F58;">
                            Already have an account? Sign in
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</template>
