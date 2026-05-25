<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import { useToast } from '@/composables/useToast.js';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { PackagePlus, Plus, Search, Pencil, Trash2, Layers, Lock, Percent, Save } from 'lucide-vue-next';
import { computed, reactive, ref } from 'vue';

const props = defineProps({ medicines: Array, categories: Array, defaultProfitPct: { type: Number, default: 0 } });
const toast = useToast();
const page  = usePage();
const userId = computed(() => page.props.auth?.user?.id);

// ── Global Default Profit % ──────────────────────────────────────────────────
const globalProfit   = ref(props.defaultProfitPct);
const savingGlobal   = ref(false);

async function saveGlobalProfit() {
    savingGlobal.value = true;
    try {
        await axios.put('/api/user/profit-settings', { default_profit_pct: globalProfit.value });
        toast.success(`Global default profit set to ${globalProfit.value}%`);
    } catch (err) {
        toast.error(err.response?.data?.message || 'Failed to update profit setting');
    } finally {
        savingGlobal.value = false;
    }
}

const searchQuery  = ref('');
const showModal    = ref(false);
const saving       = ref(false);
const editing      = ref(null);

const form    = reactive({ name: '', category_id: '', description: '' });
const catForm = reactive({ name: '' });

// ── Separate master vs. user categories ──────────────────────────────────────
const masterCategories = computed(() =>
    (props.categories || []).filter(c => c.type === 'master')
);
const userCategories = computed(() =>
    (props.categories || []).filter(c => c.type !== 'master')
);

const filteredMedicines = computed(() =>
    (props.medicines || []).filter(m =>
        m.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        (m.category?.name || '').toLowerCase().includes(searchQuery.value.toLowerCase())
    )
);

function openAdd() {
    editing.value = null;
    form.name = '';
    form.category_id = '';
    form.description = '';
    showModal.value = true;
}

function openEdit(med) {
    editing.value = med;
    form.name        = med.name;
    form.category_id = med.category_id || med.category?.id || '';
    form.description = med.description || '';
    showModal.value  = true;
}

async function saveMedicine() {
    if (!form.name.trim() || !form.category_id) {
        toast.error('Name and category are required');
        return;
    }
    saving.value = true;
    try {
        if (editing.value) {
            await axios.put(`/api/medicines/${editing.value._id || editing.value.id}`, form);
            toast.success('Medicine updated!');
        } else {
            await axios.post('/api/medicines', form);
            toast.success('Medicine added!');
        }
        showModal.value = false;
        setTimeout(() => window.location.reload(), 800);
    } catch (err) {
        toast.error(err.response?.data?.message || 'Failed to save medicine');
    } finally {
        saving.value = false;
    }
}

async function deleteMedicine(med) {
    if (!confirm(`Delete "${med.name}"? This cannot be undone.`)) return;
    try {
        await axios.delete(`/api/medicines/${med._id || med.id}`);
        toast.success('Medicine deleted');
        setTimeout(() => window.location.reload(), 600);
    } catch (err) {
        toast.error(err.response?.data?.message || 'Cannot delete this medicine');
    }
}

async function addCategory() {
    if (!catForm.name.trim()) return;
    try {
        await axios.post('/api/categories', { name: catForm.name });
        toast.success('Category added!');
        catForm.name = '';
        setTimeout(() => window.location.reload(), 600);
    } catch (err) {
        toast.error(err.response?.data?.message || 'Failed to add category');
    }
}

async function deleteCategory(cat) {
    if (cat.type === 'master') {
        toast.error('Master categories cannot be deleted.');
        return;
    }
    if (!confirm(`Delete category "${cat.name}"?`)) return;
    try {
        await axios.delete(`/api/categories/${cat._id || cat.id}`);
        toast.success('Category deleted');
        setTimeout(() => window.location.reload(), 600);
    } catch (err) {
        const msg = err.response?.data?.message || 'Cannot delete category';
        toast.error(msg);
    }
}

function stockVariant(total) {
    if (total === undefined || total === null || total === 0) return 'red';
    if (total <= 10) return 'amber';
    return 'green';
}
</script>

<template>
    <AppLayout>
        <template #title>
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg" style="background: linear-gradient(135deg,#6366f1,#8b5cf6)">
                    <PackagePlus class="h-4 w-4 text-white" />
                </div>
                <div>
                    <h1 class="text-xl font-bold" style="color: var(--text-primary);">Medicines</h1>
                    <p class="text-xs" style="color: var(--text-muted);">Catalog management · Stock lives in batches</p>
                </div>
            </div>
        </template>

        <!-- ── Global Default Profit % ──────────────────────────────────────── -->
        <div class="ph-card mb-5 border-l-4" style="border-left-color: #6366f1;">
            <div class="flex flex-wrap items-center gap-4 p-4">
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg" style="background: linear-gradient(135deg,#6366f1,#8b5cf6);">
                        <Percent class="h-4 w-4 text-white" />
                    </div>
                    <div>
                        <p class="text-xs font-bold" style="color: var(--text-muted);">GLOBAL DEFAULT PROFIT</p>
                        <p class="text-xs" style="color: var(--text-muted);">Default profit % applied when adding new inventory batches</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-1">
                    <div class="relative">
                        <input
                            v-model.number="globalProfit"
                            type="number"
                            min="0"
                            max="10000"
                            step="0.5"
                            class="ph-input text-center font-bold"
                            style="width: 110px; padding-right: 2rem;"
                            @keyup.enter="saveGlobalProfit"
                        />
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm font-bold" style="color: var(--text-muted);">%</span>
                    </div>
                    <button
                        class="btn btn-primary btn-sm flex items-center gap-1.5"
                        :disabled="savingGlobal"
                        @click="saveGlobalProfit"
                    >
                        <Save class="h-3.5 w-3.5" />
                        {{ savingGlobal ? 'Saving...' : 'Set as Default' }}
                    </button>
                    <span class="text-xs px-3 py-1.5 rounded-full font-medium" style="background: rgba(99,102,241,0.1); color: #6366f1;">
                        All new batches → <strong>{{ globalProfit }}% profit</strong> by default
                    </span>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between gap-4 mb-5 flex-wrap">
            <div class="relative flex-1 max-w-sm">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4" style="color: var(--text-muted);" />
                <input v-model="searchQuery" class="ph-input pl-10" placeholder="Search by name or category..." />
            </div>
            <div class="flex gap-2">
                <button class="btn btn-primary btn-sm" @click="openAdd">
                    <Plus class="h-3.5 w-3.5" /> Add Medicine
                </button>
            </div>
        </div>

        <div class="grid gap-5 lg:grid-cols-[1fr_300px]">

            <!-- Medicines Table -->
            <div class="ph-card">
                <div class="ph-card-header">
                    <h2>Catalog <span class="ml-2 text-xs font-normal" style="color: var(--text-muted);">({{ filteredMedicines.length }} medicines)</span></h2>
                </div>
                <div v-if="!filteredMedicines.length" class="py-16 text-center">
                    <PackagePlus class="h-10 w-10 mx-auto mb-3 opacity-20" />
                    <p class="text-sm" style="color: var(--text-muted);">No medicines found</p>
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="ph-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th class="text-right">Total Stock</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="med in filteredMedicines" :key="med._id || med.id">
                                <td class="font-semibold" style="color: var(--text-primary);">{{ med.name }}</td>
                                <td>
                                    <span class="badge badge-purple">{{ med.category?.name || '—' }}</span>
                                </td>
                                <td class="text-xs max-w-xs truncate" style="color: var(--text-muted);">
                                    {{ med.description || '—' }}
                                </td>
                                <td class="text-right">
                                    <span class="badge" :class="`badge-${stockVariant(med.user_stock)}`">
                                        {{ med.user_stock ?? 0 }} units
                                    </span>
                                </td>
                                <td>
                                    <div class="flex items-center gap-1 justify-end">
                                        <button class="btn btn-icon btn-sm btn-secondary" title="Edit" @click="openEdit(med)">
                                            <Pencil class="h-3.5 w-3.5" />
                                        </button>
                                        <button class="btn btn-icon btn-sm btn-danger" title="Delete" @click="deleteMedicine(med)">
                                            <Trash2 class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Category Panel -->
            <div class="ph-card h-fit">
                <div class="ph-card-header">
                    <h2>Categories</h2>
                    <span class="badge badge-blue">{{ (categories || []).length }}</span>
                </div>
                <div class="p-3 space-y-4">

                    <!-- Master Categories (read-only) -->
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest mb-2 pl-1" style="color: var(--text-muted);">
                            🔒 Master Categories
                        </p>
                        <div class="divide-y rounded-xl overflow-hidden" style="border: 1px solid var(--surface-border);">
                            <div
                                v-for="cat in masterCategories"
                                :key="cat._id || cat.id"
                                class="flex items-center justify-between px-3 py-2.5 text-sm"
                                style="background: #f8fafc;"
                            >
                                <div class="flex items-center gap-2">
                                    <Layers class="h-3.5 w-3.5 shrink-0" style="color: var(--text-muted);" />
                                    <span style="color: var(--text-primary);">{{ cat.name }}</span>
                                </div>
                                <span class="flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full"
                                      style="background: #e0f2fe; color: #0369a1;">
                                    <Lock class="h-2.5 w-2.5" /> Global
                                </span>
                            </div>
                            <div v-if="!masterCategories.length" class="px-3 py-4 text-center text-xs"
                                 style="color: var(--text-muted);">No master categories</div>
                        </div>
                    </div>

                    <!-- User Categories -->
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest mb-2 pl-1" style="color: var(--text-muted);">
                            Your Custom Categories
                        </p>

                        <!-- Add new -->
                        <div class="flex gap-2 mb-2">
                            <input v-model="catForm.name" class="ph-input flex-1 text-sm h-9"
                                   placeholder="New category name..." @keyup.enter="addCategory" />
                            <button class="btn btn-primary btn-icon" style="height: 36px; width: 36px;" @click="addCategory">
                                <Plus class="h-4 w-4" />
                            </button>
                        </div>

                        <div v-if="!userCategories.length" class="px-3 py-6 text-center text-xs rounded-xl"
                             style="color: var(--text-muted); background: #f8fafc; border: 1px dashed var(--surface-border);">
                            No custom categories yet.<br/>Add one above!
                        </div>
                        <div v-else class="divide-y rounded-xl overflow-hidden" style="border: 1px solid var(--surface-border);">
                            <div
                                v-for="cat in userCategories"
                                :key="cat._id || cat.id"
                                class="flex items-center justify-between px-3 py-2.5 text-sm"
                            >
                                <div class="flex items-center gap-2">
                                    <Layers class="h-3.5 w-3.5 shrink-0" style="color: var(--text-muted);" />
                                    <span style="color: var(--text-primary);">{{ cat.name }}</span>
                                </div>
                                <button class="btn btn-icon btn-sm btn-danger" title="Delete" @click="deleteCategory(cat)">
                                    <Trash2 class="h-3 w-3" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Medicine Modal -->
        <Modal :show="showModal" :title="editing ? 'Edit Medicine' : 'Add Medicine'" @close="showModal = false">
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold mb-1.5" style="color: var(--text-secondary);">Medicine Name *</label>
                    <input v-model="form.name" class="ph-input" placeholder="e.g. Paracetamol 500mg" />
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5" style="color: var(--text-secondary);">Category *</label>
                    <select v-model="form.category_id" class="ph-input">
                        <option value="">Select category...</option>
                        <optgroup label="── Global Categories ──">
                            <option v-for="cat in masterCategories" :key="cat._id || cat.id" :value="cat._id || cat.id">
                                {{ cat.name }}
                            </option>
                        </optgroup>
                        <optgroup v-if="userCategories.length" label="── Your Categories ──">
                            <option v-for="cat in userCategories" :key="cat._id || cat.id" :value="cat._id || cat.id">
                                {{ cat.name }}
                            </option>
                        </optgroup>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5" style="color: var(--text-secondary);">Description</label>
                    <textarea v-model="form.description" class="ph-input h-20 resize-none py-2" rows="3" placeholder="Optional description..."></textarea>
                </div>
            </div>
            <template #footer>
                <button class="btn btn-secondary" @click="showModal = false">Cancel</button>
                <button class="btn btn-primary" :disabled="saving" @click="saveMedicine">
                    {{ saving ? 'Saving...' : editing ? 'Update' : 'Add Medicine' }}
                </button>
            </template>
        </Modal>
    </AppLayout>
</template>
