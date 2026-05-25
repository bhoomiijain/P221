<script setup>
import { usePage, useForm, Link, router } from '@inertiajs/vue3';
import {
    User, Mail, Phone, MapPin, Building2, ShieldCheck, Globe,
    Pencil, Check, X, ArrowLeft, Hash, Briefcase, Award, Calendar, Camera
} from 'lucide-vue-next';
import axios from 'axios';
import { computed, ref } from 'vue';

const page   = usePage();
const user   = computed(() => page.props.auth?.user);
const editing = ref(false);

// ── Avatar upload ─────────────────────────────────────────────────────────────
const avatarInput     = ref(null);
const uploadingAvatar = ref(false);
const avatarPreview   = ref(null);
const avatarSuccess   = ref(false);

function triggerAvatarUpload() {
    avatarInput.value?.click();
}

async function onAvatarSelected(event) {
    const file = event.target.files[0];
    if (!file) return;

    // Show local preview immediately
    avatarPreview.value = URL.createObjectURL(file);
    uploadingAvatar.value = true;
    avatarSuccess.value   = false;

    const fd = new FormData();
    fd.append('avatar', file);

    try {
        await axios.post('/profile/avatar', fd, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        avatarSuccess.value = true;
        // Reload auth props so avatar_url refreshes everywhere (sidebar, header)
        router.reload({ only: ['auth'] });
    } catch (err) {
        console.error('Avatar upload failed:', err);
        avatarPreview.value = null;
    } finally {
        uploadingAvatar.value = false;
        // Reset file input so same file can be re-selected
        if (avatarInput.value) avatarInput.value.value = '';
    }
}

const form = useForm({
    name:           user.value?.name          ?? '',
    phone:          user.value?.phone          ?? '',
    address:        user.value?.address        ?? '',
    city:           user.value?.city           ?? '',
    state:          user.value?.state          ?? '',
    pincode:        user.value?.pincode        ?? '',
    license_number: user.value?.license_number ?? '',
    pharmacy_name:  user.value?.pharmacy_name  ?? '',
    business_name:  user.value?.business_name  ?? '',
    gst_number:     user.value?.gst_number     ?? '',
    website:        user.value?.website        ?? '',
});

const isPharmacist = computed(() => user.value?.role === 'pharmacist');
const isSupplier   = computed(() => user.value?.role === 'supplier');
const completion   = computed(() => user.value?.profileCompletion ?? 0);

// SVG ring helpers
const RADIUS = 40;
const CIRC   = 2 * Math.PI * RADIUS;
const ringOffset = computed(() => CIRC - (completion.value / 100) * CIRC);
const ringColor  = computed(() => {
    if (completion.value >= 80) return '#10b981'; // green
    if (completion.value >= 50) return '#f59e0b'; // amber
    return '#ef4444';                              // red
});

function startEdit() {
    form.name           = user.value?.name           ?? '';
    form.phone          = user.value?.phone           ?? '';
    form.address        = user.value?.address         ?? '';
    form.city           = user.value?.city            ?? '';
    form.state          = user.value?.state           ?? '';
    form.pincode        = user.value?.pincode         ?? '';
    form.license_number = user.value?.license_number  ?? '';
    form.pharmacy_name  = user.value?.pharmacy_name   ?? '';
    form.business_name  = user.value?.business_name   ?? '';
    form.gst_number     = user.value?.gst_number      ?? '';
    form.website        = user.value?.website         ?? '';
    editing.value = true;
}

function cancelEdit() {
    form.reset();
    editing.value = false;
}

function save() {
    form.put('/profile', {
        onSuccess: () => { editing.value = false; },
    });
}

function initials(name) {
    return (name || 'U').split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
}

function joinedDate(dt) {
    if (!dt) return '—';
    return new Date(dt).toLocaleDateString('en-IN', { year: 'numeric', month: 'long', day: 'numeric' });
}
</script>

<template>
    <div class="min-h-screen" style="background: #f0f4f8; font-family: 'Inter', sans-serif;">

        <!-- Top Bar -->
        <div class="sticky top-0 z-10 flex items-center gap-4 px-6 py-4 bg-white border-b shadow-sm"
             style="border-color: #e2e8f0;">
            <Link href="/" class="flex items-center gap-2 text-sm font-semibold hover:opacity-70 transition-opacity"
                  style="color: #1e3a5f;">
                <ArrowLeft class="h-4 w-4" />
                Back to Dashboard
            </Link>
            <div class="flex-1" />
            <template v-if="!editing">
                <button @click="startEdit"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition-all hover:opacity-90"
                    style="background: #1e3a5f; color: white;">
                    <Pencil class="h-4 w-4" /> Edit Profile
                </button>
            </template>
            <template v-else>
                <button @click="cancelEdit"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold border transition-all hover:bg-gray-50"
                    style="border-color: #cbd5e1; color: #64748b;">
                    <X class="h-4 w-4" /> Cancel
                </button>
                <button @click="save" :disabled="form.processing"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition-all hover:opacity-90"
                    style="background: #10b981; color: white;">
                    <Check class="h-4 w-4" /> {{ form.processing ? 'Saving...' : 'Save Changes' }}
                </button>
            </template>
        </div>

        <div class="max-w-4xl mx-auto px-4 py-8 space-y-6">

            <!-- Hero Card -->
            <div class="bg-white rounded-3xl shadow-sm overflow-hidden">
                <!-- Gradient header strip -->
                <div class="h-24" style="background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 60%, #38bdf8 100%);"></div>

                <div class="px-8 pb-8 -mt-12">
                    <div class="flex items-end gap-5 flex-wrap">
                        <!-- Avatar with ring + camera upload -->
                        <div class="relative flex-shrink-0">
                            <svg width="96" height="96" viewBox="0 0 96 96">
                                <circle cx="48" cy="48" r="40" fill="none"
                                    stroke="#e2e8f0" stroke-width="6"/>
                                <circle cx="48" cy="48" :r="RADIUS" fill="none"
                                    :stroke="ringColor" stroke-width="6"
                                    stroke-linecap="round"
                                    :stroke-dasharray="CIRC"
                                    :stroke-dashoffset="ringOffset"
                                    transform="rotate(-90 48 48)"
                                    style="transition: stroke-dashoffset 0.6s ease;"/>
                                <!-- Avatar image inside ring -->
                                <clipPath id="circle-clip">
                                    <circle cx="48" cy="48" r="34" />
                                </clipPath>
                                <image v-if="avatarPreview || user?.avatar_url"
                                    :href="avatarPreview || user?.avatar_url"
                                    x="14" y="14" width="68" height="68"
                                    clip-path="url(#circle-clip)"
                                    preserveAspectRatio="xMidYMid slice"
                                />
                                <text v-else
                                    x="48" y="53" text-anchor="middle"
                                    font-size="22" font-weight="700" fill="#1e3a5f">
                                    {{ initials(user?.name) }}
                                </text>
                            </svg>
                            <!-- % badge -->
                            <div class="absolute -bottom-1 -right-1 h-7 w-7 rounded-full flex items-center justify-center text-white text-[10px] font-black"
                                 :style="{ background: ringColor }">
                                {{ completion }}%
                            </div>
                            <!-- Camera upload button -->
                            <button
                                class="absolute top-0 right-0 h-7 w-7 rounded-full flex items-center justify-center shadow-md transition-opacity"
                                style="background: #1e3a5f; color: white;"
                                :class="uploadingAvatar ? 'opacity-60' : 'hover:opacity-90'"
                                :disabled="uploadingAvatar"
                                title="Change profile photo"
                                @click="triggerAvatarUpload"
                            >
                                <Camera class="h-3.5 w-3.5" />
                            </button>
                            <input
                                ref="avatarInput"
                                type="file"
                                accept="image/jpg,image/jpeg,image/png,image/webp"
                                class="hidden"
                                @change="onAvatarSelected"
                            />
                        </div>

                        <div class="flex-1 min-w-0 pt-14">
                            <div class="flex items-center gap-3 flex-wrap">
                                <h1 class="text-2xl font-black" style="color: #1e3a5f;">
                                    {{ user?.name || '—' }}
                                </h1>
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider"
                                      :style="isSupplier
                                          ? 'background: #ede9fe; color: #7c3aed;'
                                          : 'background: #dcfce7; color: #16a34a;'">
                                    {{ isSupplier ? '🏭 Supplier' : '💊 Pharmacist' }}
                                </span>
                            </div>
                            <p class="text-sm mt-1" style="color: #64748b;">{{ user?.email }}</p>
                        </div>

                        <!-- Completion summary -->
                        <div class="text-right">
                            <div class="text-xs font-semibold uppercase tracking-wider mb-1" style="color: #94a3b8;">Profile Completion</div>
                            <div class="text-3xl font-black" :style="{ color: ringColor }">{{ completion }}%</div>
                            <div class="text-xs mt-1" style="color: #94a3b8;">
                                {{ completion < 100 ? 'Fill in missing details' : 'Profile complete!' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Personal Information -->
            <div class="bg-white rounded-3xl shadow-sm p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="h-9 w-9 rounded-xl flex items-center justify-center"
                         style="background: #eff6ff;">
                        <User class="h-5 w-5" style="color: #2563eb;" />
                    </div>
                    <h2 class="text-lg font-bold" style="color: #1e3a5f;">Personal Information</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <!-- Name -->
                    <div>
                        <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wider" style="color: #64748b;">Full Name *</label>
                        <div v-if="!editing" class="flex items-center gap-2 text-sm font-medium" style="color: #1e3a5f;">
                            <User class="h-4 w-4 opacity-40" /> {{ user?.name || '—' }}
                        </div>
                        <input v-else v-model="form.name" class="profile-input" placeholder="Your full name" />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                    </div>

                    <!-- Email (read-only) -->
                    <div>
                        <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wider" style="color: #64748b;">Email Address</label>
                        <div class="flex items-center gap-2 text-sm font-medium" style="color: #1e3a5f;">
                            <Mail class="h-4 w-4 opacity-40" /> {{ user?.email || '—' }}
                        </div>
                        <p class="text-[10px] mt-1" style="color: #94a3b8;">Email cannot be changed</p>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wider" style="color: #64748b;">Phone Number</label>
                        <div v-if="!editing" class="flex items-center gap-2 text-sm font-medium" style="color: #1e3a5f;">
                            <Phone class="h-4 w-4 opacity-40" />
                            <span :class="!user?.phone ? 'opacity-40 italic' : ''">{{ user?.phone || 'Not set' }}</span>
                        </div>
                        <input v-else v-model="form.phone" class="profile-input" placeholder="+91 98765 43210" />
                    </div>

                    <!-- Pincode -->
                    <div>
                        <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wider" style="color: #64748b;">Pincode</label>
                        <div v-if="!editing" class="flex items-center gap-2 text-sm font-medium" style="color: #1e3a5f;">
                            <Hash class="h-4 w-4 opacity-40" />
                            <span :class="!user?.pincode ? 'opacity-40 italic' : ''">{{ user?.pincode || 'Not set' }}</span>
                        </div>
                        <input v-else v-model="form.pincode" class="profile-input" placeholder="400001" />
                    </div>

                    <!-- Address -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wider" style="color: #64748b;">Street Address</label>
                        <div v-if="!editing" class="flex items-start gap-2 text-sm font-medium" style="color: #1e3a5f;">
                            <MapPin class="h-4 w-4 opacity-40 mt-0.5" />
                            <span :class="!user?.address ? 'opacity-40 italic' : ''">{{ user?.address || 'Not set' }}</span>
                        </div>
                        <input v-else v-model="form.address" class="profile-input" placeholder="Street, Area, Landmark" />
                    </div>

                    <!-- City -->
                    <div>
                        <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wider" style="color: #64748b;">City</label>
                        <div v-if="!editing" class="flex items-center gap-2 text-sm font-medium" style="color: #1e3a5f;">
                            <Building2 class="h-4 w-4 opacity-40" />
                            <span :class="!user?.city ? 'opacity-40 italic' : ''">{{ user?.city || 'Not set' }}</span>
                        </div>
                        <input v-else v-model="form.city" class="profile-input" placeholder="Mumbai" />
                    </div>

                    <!-- State -->
                    <div>
                        <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wider" style="color: #64748b;">State</label>
                        <div v-if="!editing" class="flex items-center gap-2 text-sm font-medium" style="color: #1e3a5f;">
                            <MapPin class="h-4 w-4 opacity-40" />
                            <span :class="!user?.state ? 'opacity-40 italic' : ''">{{ user?.state || 'Not set' }}</span>
                        </div>
                        <input v-else v-model="form.state" class="profile-input" placeholder="Maharashtra" />
                    </div>
                </div>
            </div>

            <!-- Pharmacist-specific -->
            <div v-if="isPharmacist" class="bg-white rounded-3xl shadow-sm p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="h-9 w-9 rounded-xl flex items-center justify-center" style="background: #f0fdf4;">
                        <Award class="h-5 w-5" style="color: #16a34a;" />
                    </div>
                    <div>
                        <h2 class="text-lg font-bold" style="color: #1e3a5f;">Pharmacist Details</h2>
                        <p class="text-xs" style="color: #94a3b8;">Your professional and pharmacy information</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wider" style="color: #64748b;">License Number</label>
                        <div v-if="!editing" class="flex items-center gap-2 text-sm font-medium" style="color: #1e3a5f;">
                            <ShieldCheck class="h-4 w-4 opacity-40" />
                            <span :class="!user?.license_number ? 'opacity-40 italic' : ''">{{ user?.license_number || 'Not set' }}</span>
                        </div>
                        <input v-else v-model="form.license_number" class="profile-input" placeholder="MH-PH-2024-00123" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wider" style="color: #64748b;">Pharmacy Name</label>
                        <div v-if="!editing" class="flex items-center gap-2 text-sm font-medium" style="color: #1e3a5f;">
                            <Building2 class="h-4 w-4 opacity-40" />
                            <span :class="!user?.pharmacy_name ? 'opacity-40 italic' : ''">{{ user?.pharmacy_name || 'Not set' }}</span>
                        </div>
                        <input v-else v-model="form.pharmacy_name" class="profile-input" placeholder="City Care Pharmacy" />
                    </div>
                </div>
            </div>

            <!-- Supplier-specific -->
            <div v-if="isSupplier" class="bg-white rounded-3xl shadow-sm p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="h-9 w-9 rounded-xl flex items-center justify-center" style="background: #faf5ff;">
                        <Briefcase class="h-5 w-5" style="color: #7c3aed;" />
                    </div>
                    <div>
                        <h2 class="text-lg font-bold" style="color: #1e3a5f;">Supplier Details</h2>
                        <p class="text-xs" style="color: #94a3b8;">Your business and company information</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wider" style="color: #64748b;">Business Name</label>
                        <div v-if="!editing" class="flex items-center gap-2 text-sm font-medium" style="color: #1e3a5f;">
                            <Building2 class="h-4 w-4 opacity-40" />
                            <span :class="!user?.business_name ? 'opacity-40 italic' : ''">{{ user?.business_name || 'Not set' }}</span>
                        </div>
                        <input v-else v-model="form.business_name" class="profile-input" placeholder="Metro Medical Supply Pvt. Ltd." />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wider" style="color: #64748b;">GST Number</label>
                        <div v-if="!editing" class="flex items-center gap-2 text-sm font-medium" style="color: #1e3a5f;">
                            <Hash class="h-4 w-4 opacity-40" />
                            <span :class="!user?.gst_number ? 'opacity-40 italic' : ''">{{ user?.gst_number || 'Not set' }}</span>
                        </div>
                        <input v-else v-model="form.gst_number" class="profile-input" placeholder="27AAPFU0939F1ZV" />
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wider" style="color: #64748b;">Website</label>
                        <div v-if="!editing" class="flex items-center gap-2 text-sm font-medium" style="color: #1e3a5f;">
                            <Globe class="h-4 w-4 opacity-40" />
                            <a v-if="user?.website" :href="user.website" target="_blank"
                               class="underline hover:opacity-70" style="color: #2563eb;">{{ user.website }}</a>
                            <span v-else class="opacity-40 italic">Not set</span>
                        </div>
                        <input v-else v-model="form.website" class="profile-input" placeholder="https://metromed.com" />
                        <p v-if="form.errors.website" class="mt-1 text-xs text-red-500">{{ form.errors.website }}</p>
                    </div>
                </div>
            </div>

            <!-- Account Information -->
            <div class="bg-white rounded-3xl shadow-sm p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="h-9 w-9 rounded-xl flex items-center justify-center" style="background: #fff7ed;">
                        <Calendar class="h-5 w-5" style="color: #ea580c;" />
                    </div>
                    <h2 class="text-lg font-bold" style="color: #1e3a5f;">Account Information</h2>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <div class="rounded-2xl p-4" style="background: #f8fafc;">
                        <div class="text-xs font-semibold uppercase tracking-wider mb-2" style="color: #94a3b8;">Role</div>
                        <div class="text-sm font-bold capitalize" style="color: #1e3a5f;">{{ user?.role }}</div>
                    </div>
                    <div class="rounded-2xl p-4" style="background: #f8fafc;">
                        <div class="text-xs font-semibold uppercase tracking-wider mb-2" style="color: #94a3b8;">Member Since</div>
                        <div class="text-sm font-bold" style="color: #1e3a5f;">{{ joinedDate(user?.created_at) }}</div>
                    </div>
                    <div class="rounded-2xl p-4" style="background: #f8fafc;">
                        <div class="text-xs font-semibold uppercase tracking-wider mb-2" style="color: #94a3b8;">Profile Status</div>
                        <div class="text-sm font-bold" :style="{ color: ringColor }">
                            {{ completion >= 80 ? 'Complete ✓' : completion >= 50 ? 'In Progress' : 'Incomplete' }}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<style scoped>
.profile-input {
    width: 100%;
    height: 42px;
    padding: 0 14px;
    border-radius: 12px;
    border: 1.5px solid #e2e8f0;
    background: #f8fafc;
    font-size: 14px;
    color: #1e3a5f;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.profile-input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    background: #fff;
}
</style>
