<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import { useToast } from '@/composables/useToast.js';
import { router } from '@inertiajs/vue3';
import { Boxes, PackagePlus, DollarSign, Plus, Pencil } from 'lucide-vue-next';
import { ref, reactive, computed } from 'vue';
import axios from 'axios';

const props = defineProps({ medicines: Array, supplierMedicines: Array });
const toast = useToast();

const showModal  = ref(false);
const saving     = ref(false);
const editMode   = ref(false);  // false = add from catalog, true = update existing
const isCreatingNew = ref(false);
const selectedMedicine = ref(null);
const categories = ref([]);

const form = reactive({ medicine_id: '', quantity: 0, price: 0 });
const newMedForm = reactive({ name: '', category_id: '', description: '' });

// ── medicines NOT yet in supplier's stock ──
const stockedIds = computed(() => (props.supplierMedicines || []).map(sm => sm.medicine_id));
const availableToAdd = computed(() =>
    (props.medicines || []).filter(m => !stockedIds.value.includes(m._id || m.id))
);

function getSupplierStock(medicineId) {
    const sm = (props.supplierMedicines || []).find(m => m.medicine_id === medicineId);
    return sm ? { quantity: sm.quantity, price: sm.price } : null;
}

// Open modal to ADD a medicine from the catalog
function openAdd() {
    editMode.value = false;
    isCreatingNew.value = false;
    selectedMedicine.value = null;
    Object.assign(form, { medicine_id: '', quantity: 0, price: 0 });
    Object.assign(newMedForm, { name: '', category_id: '', description: '' });
    
    // Fetch categories if creating new
    if (categories.value.length === 0) {
        axios.get('/api/categories').then(res => {
            categories.value = res.data.data || res.data;
        });
    }
    
    showModal.value = true;
}

// Open modal to UPDATE an existing stocked medicine
function openEdit(med) {
    editMode.value = true;
    selectedMedicine.value = med;
    const stock = getSupplierStock(med._id || med.id);
    Object.assign(form, {
        medicine_id: med._id || med.id,
        quantity:    stock?.quantity ?? 0,
        price:       stock?.price    ?? 0,
    });
    showModal.value = true;
}

async function saveStock() {
    if (!editMode.value && isCreatingNew.value) {
        if (!newMedForm.name || !newMedForm.category_id) {
            toast.error('Name and Category are required');
            return;
        }
    } else {
        if (!form.medicine_id) { toast.error('Please select a medicine'); return; }
    }
    
    if (form.quantity < 0 || form.price < 0) {
        toast.error('Quantity and price must be positive numbers');
        return;
    }
    
    saving.value = true;

    try {
        let medId = form.medicine_id;
        
        // If creating new, hit POST /api/medicines first
        if (!editMode.value && isCreatingNew.value) {
            const medRes = await axios.post('/api/medicines', {
                name: newMedForm.name,
                category_id: newMedForm.category_id,
                description: newMedForm.description,
            });
            medId = medRes.data._id || medRes.data.id;
            form.medicine_id = medId;
        }

        router.post('/supplier/inventory', form, {
            onSuccess: () => {
                toast.success(editMode.value ? 'Stock updated!' : 'Medicine added to your inventory!');
                showModal.value = false;
                saving.value = false;
            },
            onError: (err) => {
                toast.error(Object.values(err)[0] || 'Failed to save stock');
                saving.value = false;
            }
        });
    } catch (err) {
        toast.error(err.response?.data?.message || 'Failed to create medicine');
        saving.value = false;
    }
}

// Stocked medicines with their medicine details joined
const stockedMedicines = computed(() =>
    (props.supplierMedicines || []).map(sm => {
        const med = (props.medicines || []).find(m => (m._id || m.id) === sm.medicine_id);
        return { ...sm, medicine: med };
    }).filter(sm => sm.medicine)
);
</script>

<template>
    <AppLayout>
        <template #title>
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg" style="background: linear-gradient(135deg,#3b82f6,#2563eb)">
                    <Boxes class="h-4 w-4 text-white" />
                </div>
                <div>
                    <h1 class="text-xl font-bold" style="color: var(--text-primary);">My Inventory</h1>
                    <p class="text-xs" style="color: var(--text-muted);">Manage your available stock and pricing for pharmacies.</p>
                </div>
            </div>
        </template>

        <!-- Add button -->
        <div class="flex justify-between items-center mb-5">
            <p class="text-sm" style="color: var(--text-secondary);">
                Stocking <strong>{{ stockedMedicines.length }}</strong> of {{ medicines?.length }} available medicines.
            </p>
            <button
                class="btn btn-primary btn-sm flex items-center gap-2"
                :disabled="!availableToAdd.length"
                @click="openAdd"
            >
                <Plus class="h-3.5 w-3.5" /> Add Medicine
            </button>
        </div>

        <!-- Stocked medicines grid -->
        <div v-if="stockedMedicines.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="sm in stockedMedicines"
                :key="sm.medicine_id"
                class="ph-card p-5 hover:shadow-md transition-shadow flex flex-col"
            >
                <div class="flex items-start gap-4 mb-4">
                    <img
                        v-if="sm.medicine?.image"
                        :src="sm.medicine.image"
                        class="h-16 w-16 object-cover rounded-xl border border-gray-100"
                    />
                    <div v-else class="h-16 w-16 bg-gray-100 rounded-xl flex items-center justify-center border border-gray-200">
                        <PackagePlus class="h-6 w-6 text-gray-400" />
                    </div>
                    <div>
                        <div class="font-bold text-sm" style="color: var(--text-primary);">{{ sm.medicine.name }}</div>
                        <div class="text-xs" style="color: var(--text-secondary);">{{ sm.medicine.description }}</div>
                    </div>
                </div>

                <div class="flex-1"></div>

                <div class="bg-gray-50 rounded-lg p-3 mb-4 border border-gray-100">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-xs font-semibold text-gray-500">Your Stock</span>
                        <span class="text-sm font-bold text-gray-900">{{ sm.quantity }} units</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-semibold text-gray-500">Your Price</span>
                        <span class="text-sm font-bold text-[var(--brand-primary)]">${{ sm.price }}</span>
                    </div>
                </div>

                <button class="btn btn-secondary btn-sm w-full flex justify-center items-center gap-2" @click="openEdit(sm.medicine)">
                    <Pencil class="h-3.5 w-3.5" /> Update Stock
                </button>
            </div>
        </div>

        <div v-else class="ph-card py-20 text-center">
            <PackagePlus class="h-12 w-12 mx-auto mb-3 opacity-20" />
            <p class="text-sm" style="color: var(--text-muted);">
                You haven't added any medicines yet.<br/>
                Click <strong>Add Medicine</strong> to stock from the catalog.
            </p>
        </div>

        <!-- Add / Edit Modal -->
        <Modal :show="showModal" :title="editMode ? 'Update Stock & Pricing' : 'Add Medicine to Inventory'" @close="showModal = false">
            <div class="space-y-4">

                <!-- Select medicine (only in add mode) -->
                <div v-if="!editMode">
                    <div class="flex gap-4 mb-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" :value="false" v-model="isCreatingNew" class="text-blue-600 focus:ring-blue-500">
                            <span class="text-sm font-semibold" style="color: var(--text-primary);">Select Existing</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" :value="true" v-model="isCreatingNew" class="text-blue-600 focus:ring-blue-500">
                            <span class="text-sm font-semibold" style="color: var(--text-primary);">Create New</span>
                        </label>
                    </div>

                    <div v-if="!isCreatingNew">
                        <label class="block text-xs font-semibold mb-1.5" style="color: var(--text-secondary);">Select Medicine</label>
                        <select v-model="form.medicine_id" class="ph-input">
                            <option value="" disabled>-- Choose a medicine --</option>
                            <option
                                v-for="med in availableToAdd"
                                :key="med._id || med.id"
                                :value="med._id || med.id"
                            >
                                {{ med.name }}
                            </option>
                        </select>
                    </div>
                    
                    <div v-else class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold mb-1.5" style="color: var(--text-secondary);">Medicine Name</label>
                            <input v-model="newMedForm.name" class="ph-input" placeholder="e.g. Paracetamol 500mg" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1.5" style="color: var(--text-secondary);">Category</label>
                            <select v-model="newMedForm.category_id" class="ph-input">
                                <option value="" disabled>-- Select Category --</option>
                                <option v-for="cat in categories" :key="cat._id || cat.id" :value="cat._id || cat.id">{{ cat.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1.5" style="color: var(--text-secondary);">Description</label>
                            <textarea v-model="newMedForm.description" class="ph-input" rows="2" placeholder="Brief description..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- In edit mode show medicine name -->
                <div v-else class="flex items-center gap-3 p-3 bg-blue-50 text-blue-900 rounded-lg">
                    <PackagePlus class="h-5 w-5 shrink-0" />
                    <span class="font-semibold text-sm">{{ selectedMedicine?.name }}</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold mb-1.5" style="color: var(--text-secondary);">Available Quantity</label>
                    <input v-model.number="form.quantity" type="number" min="0" class="ph-input" />
                </div>

                <div>
                    <label class="block text-xs font-semibold mb-1.5" style="color: var(--text-secondary);">Price per Unit ($)</label>
                    <div class="relative">
                        <DollarSign class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                        <input v-model.number="form.price" type="number" min="0" step="0.01" class="ph-input pl-9" />
                    </div>
                </div>
            </div>
            <template #footer>
                <button class="btn btn-secondary" @click="showModal = false">Cancel</button>
                <button class="btn btn-primary" :disabled="saving" @click="saveStock">
                    {{ saving ? 'Saving...' : editMode ? 'Update Stock' : 'Add to Inventory' }}
                </button>
            </template>
        </Modal>

    </AppLayout>
</template>
