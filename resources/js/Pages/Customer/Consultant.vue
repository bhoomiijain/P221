<script setup>
import { Head } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { ref, onMounted } from 'vue';
import { useCustomerApi } from '@/composables/useCustomerApi';
import { Bot, Send, Mic } from 'lucide-vue-next';

const api = useCustomerApi();
const messages = ref([{ role: 'bot', text: 'Hello! Ask me about medicine interactions, dosage, or general health tips.' }]);
const input = ref('');
const questions = ref([]);

onMounted(async () => {
    const { data } = await api.aiQuestions();
    questions.value = data.questions;
});

function send() {
    if (!input.value.trim()) return;
    messages.value.push({ role: 'user', text: input.value });
    const q = input.value.toLowerCase();
    let reply = 'For personalized advice, please consult a licensed physician.';
    if (q.includes('paracetamol')) reply = 'Paracetamol is commonly used for fever and pain. Do not exceed recommended dose.';
    if (q.includes('allergy')) reply = 'If you have allergies, always check medicine ingredients and inform your pharmacist.';
    if (q.includes('pregnant')) reply = 'Many medicines are not safe during pregnancy. Consult your doctor before ordering.';
    setTimeout(() => messages.value.push({ role: 'bot', text: reply }), 500);
    input.value = '';
}
</script>

<template>
    <Head title="AI Consultant" />
    <CustomerLayout>
        <div class="max-w-2xl mx-auto px-4 py-8">
            <h1 class="text-2xl font-bold flex items-center gap-2 mb-6"><Bot class="w-8 h-8 text-teal-500" /> AI Health Consultant</h1>
            <div class="ph-card flex flex-col h-[500px]">
                <div class="flex-1 overflow-y-auto p-4 space-y-3">
                    <div v-for="(m, i) in messages" :key="i" :class="m.role === 'user' ? 'text-right' : ''">
                        <span :class="['inline-block px-4 py-2 rounded-2xl text-sm', m.role === 'user' ? 'bg-teal-600 text-white' : 'bg-slate-100']">{{ m.text }}</span>
                    </div>
                </div>
                <div class="p-4 border-t flex gap-2">
                    <button type="button" class="btn btn-icon btn-secondary" title="Voice search demo"><Mic class="w-4 h-4" /></button>
                    <input v-model="input" class="ph-input flex-1 !rounded-xl" placeholder="Ask a health question..." @keyup.enter="send" />
                    <button type="button" class="btn btn-primary btn-icon" @click="send"><Send class="w-4 h-4" /></button>
                </div>
            </div>
            <p class="text-xs text-slate-400 mt-4 text-center">Rule-based demo — ready for OpenAI / Gemini API integration</p>
        </div>
    </CustomerLayout>
</template>
