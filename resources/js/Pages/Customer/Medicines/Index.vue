<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import MedicineCard from '@/Components/Customer/MedicineCard.vue';
import { ref, watch } from 'vue';
import { Filter } from 'lucide-vue-next';

const props = defineProps({
    products: Array, pagination: Object, categories: Array,
    filters: Object, brands: Array,
});

const local = ref({ ...props.filters });

function apply() {
    router.get('/shop/medicines', { ...local.value, page: 1 }, { preserveState: true });
}
</script>

<template>
    <Head title="Medicines" />
    <CustomerLayout>
        <template #title>Browse Medicines</template>
        <div>
            <div class="flex flex-col lg:flex-row gap-8">
                <aside class="lg:w-64 ph-card p-6 h-fit">
                    <h3 class="font-semibold flex items-center gap-2 mb-4"><Filter class="w-4 h-4" /> Filters</h3>
                    <div class="space-y-4 text-sm">
                        <div>
                            <label class="font-medium">Category</label>
                            <select v-model="local.category_id" class="ph-input mt-1 !rounded-xl !h-10" @change="apply">
                                <option value="">All</option>
                                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="font-medium">Brand</label>
                            <select v-model="local.brand" class="ph-input mt-1 !rounded-xl !h-10" @change="apply">
                                <option value="">All</option>
                                <option v-for="b in brands" :key="b" :value="b">{{ b }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="font-medium">Prescription</label>
                            <select v-model="local.prescription_required" class="ph-input mt-1 !rounded-xl !h-10" @change="apply">
                                <option value="">Any</option>
                                <option value="1">Required</option>
                                <option value="0">OTC</option>
                            </select>
                        </div>
                        <div>
                            <label class="font-medium">Stock</label>
                            <select v-model="local.in_stock" class="ph-input mt-1 !rounded-xl !h-10" @change="apply">
                                <option value="">Any</option>
                                <option value="1">In stock</option>
                            </select>
                        </div>
                        <div>
                            <label class="font-medium">Sort</label>
                            <select v-model="local.sort" class="ph-input mt-1 !rounded-xl !h-10" @change="apply">
                                <option value="popularity">Popularity</option>
                                <option value="price_asc">Price: Low to High</option>
                                <option value="price_desc">Price: High to Low</option>
                                <option value="rating">Rating</option>
                            </select>
                        </div>
                        <button type="button" class="btn btn-primary w-full btn-sm" @click="apply">Apply</button>
                    </div>
                </aside>
                <div class="flex-1">
                    <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-6">
                        <MedicineCard v-for="p in products" :key="p.id" :product="p" />
                    </div>
                    <p v-if="!products?.length" class="text-center text-slate-400 py-12">No medicines found.</p>
                    <div v-if="pagination?.last_page > 1" class="flex justify-center gap-2 mt-8">
                        <Link v-for="p in pagination.last_page" :key="p"
                            :href="`/shop/medicines?page=${p}`"
                            class="w-10 h-10 rounded-full flex items-center justify-center"
                            :class="p === pagination.current_page ? 'bg-teal-600 text-white' : 'bg-white border'">{{ p }}</Link>
                    </div>
                </div>
            </div>
        </div>
    </CustomerLayout>
</template>
