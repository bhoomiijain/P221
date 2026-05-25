<script setup>
import { ref, computed, watch } from 'vue';
import { AlertTriangle, Mic, Send, Bot } from 'lucide-vue-next';
import { useCustomerApi } from '@/composables/useCustomerApi';

const props = defineProps({
    show: Boolean,
    questions: { type: Array, default: () => [] },
});
const emit = defineEmits(['close', 'complete']);
const api = useCustomerApi();

const step = ref(0);
const responses = ref({});
const analyzing = ref(false);
const typing = ref(false);
const result = ref(null);
const messages = ref([{ role: 'assistant', text: 'Hi! I am your AI health consultant. I will ask a few safety questions before confirming your order.' }]);

const currentQ = computed(() => props.questions[step.value]);
const progress = computed(() => props.questions.length ? ((step.value) / props.questions.length) * 100 : 0);

const suggestedReplies = computed(() => {
    const q = currentQ.value;
    if (!q?.options) return [];
    return q.options.map(o => ({ label: o, value: o }));
});

watch(() => props.show, (v) => {
    if (v) { step.value = 0; responses.value = {}; result.value = null; }
});

function pickReply(val) {
    if (!currentQ.value) return;
    responses.value[currentQ.value.key] = val;
    messages.value.push({ role: 'user', text: String(val) });
    typing.value = true;
    setTimeout(() => {
        typing.value = false;
        if (step.value < props.questions.length - 1) {
            step.value++;
            messages.value.push({ role: 'assistant', text: props.questions[step.value].question });
        } else {
            runAnalysis();
        }
    }, 600);
}

async function runAnalysis() {
    analyzing.value = true;
    try {
        const { data } = await api.analyzeAi(responses.value);
        result.value = data;
        messages.value.push({ role: 'assistant', text: data.message });
    } finally {
        analyzing.value = false;
    }
}

function confirm() {
    emit('complete', { responses: responses.value, analysis: result.value });
}
</script>

<template>
    <div v-if="show" class="modal-overlay" @click.self="emit('close')">
        <div class="modal-box max-w-lg flex flex-col max-h-[85vh]">
            <div class="modal-header bg-gradient-to-r from-teal-50 to-cyan-50 dark:from-slate-700 dark:to-slate-600">
                <div class="flex items-center gap-2">
                    <Bot class="w-6 h-6 text-teal-600" />
                    <h3 class="font-semibold">AI Health Consultant</h3>
                </div>
                <button type="button" class="text-slate-400 hover:text-slate-600" @click="emit('close')">✕</button>
            </div>
            <div class="h-2 bg-slate-100"><div class="h-full bg-teal-500 transition-all" :style="{ width: progress + '%' }" /></div>
            <div class="modal-body flex-1 overflow-y-auto space-y-3 min-h-[200px]">
                <div v-for="(m, i) in messages" :key="i" :class="m.role === 'user' ? 'text-right' : ''">
                    <span :class="['inline-block px-4 py-2 rounded-2xl text-sm max-w-[90%]',
                        m.role === 'user' ? 'bg-teal-600 text-white' : 'bg-slate-100 dark:bg-slate-700']">{{ m.text }}</span>
                </div>
                <div v-if="typing" class="text-sm text-slate-400 animate-pulse">Consultant is typing...</div>
                <div v-if="result?.risk_level === 'high'" class="p-4 rounded-xl bg-rose-50 border border-rose-200 flex gap-2">
                    <AlertTriangle class="w-5 h-5 text-rose-600 shrink-0" />
                    <p class="text-sm text-rose-800">Emergency: Consult a doctor immediately. Order flagged for pharmacist review.</p>
                </div>
            </div>
            <div v-if="!result && currentQ && !analyzing" class="px-6 pb-4 space-y-2">
                <p class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ currentQ.question }}</p>
                <div v-if="currentQ.type === 'text'" class="flex gap-2">
                    <input v-model="responses[currentQ.key]" class="ph-input flex-1 !rounded-xl" placeholder="Type answer or none" />
                    <button type="button" class="btn btn-primary btn-sm" @click="pickReply(responses[currentQ.key] || 'none')"><Send class="w-4 h-4" /></button>
                </div>
                <div v-else class="flex flex-wrap gap-2">
                    <button v-for="s in suggestedReplies" :key="s.value" type="button"
                        class="px-4 py-2 rounded-full text-sm bg-teal-50 hover:bg-teal-100 border border-teal-200"
                        @click="pickReply(s.value)">{{ s.label }}</button>
                </div>
                <button type="button" class="flex items-center gap-2 text-xs text-slate-400 mt-2" title="Voice input UI (demo)">
                    <Mic class="w-4 h-4" /> Voice input (demo)
                </button>
            </div>
            <div class="modal-footer">
                <button v-if="!result" type="button" class="btn btn-secondary" @click="emit('close')">Cancel</button>
                <template v-else>
                    <button type="button" class="btn btn-secondary" @click="emit('close')">Back</button>
                    <button type="button" class="btn btn-primary" :class="{ 'opacity-70': result.suggest_doctor }"
                        @click="confirm">
                        {{ result.approved ? 'Confirm Order' : 'Proceed with Review Flag' }}
                    </button>
                </template>
            </div>
        </div>
    </div>
</template>
