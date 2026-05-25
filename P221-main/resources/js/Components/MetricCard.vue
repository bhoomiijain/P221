<script setup>
import { TrendingUp, TrendingDown, Minus } from 'lucide-vue-next';

const props = defineProps({
    label:      String,
    value:      [String, Number],
    detail:     String,
    trend:      Number,
    variant:    { type: String, default: 'mint' },   // mint | violet | amber | rose
    icon:       [Object, Function],
    showSparkline: Boolean,
    sparklineData: Array,
    sparklineColor: String,
});

const cardClass = {
    mint:   'stat-card-mint',
    violet: 'stat-card-violet',
    amber:  'stat-card-amber',
    rose:   'stat-card-rose',
};
</script>

<template>
    <div class="stat-card" :class="cardClass[variant] || cardClass.mint">
        <!-- Large background icon -->
        <div class="stat-bg-icon">
            <component v-if="icon" :is="icon" />
        </div>

        <!-- Label -->
        <div class="stat-label">{{ label }}</div>

        <!-- Main value -->
        <div class="stat-value">{{ value }}</div>

        <!-- Trend + detail -->
        <div class="stat-detail flex items-center gap-2 flex-wrap">
            <span v-if="trend != null" class="inline-flex items-center gap-1 font-bold">
                <TrendingUp  v-if="trend > 0" class="h-3.5 w-3.5" />
                <TrendingDown v-else-if="trend < 0" class="h-3.5 w-3.5" />
                <Minus v-else class="h-3.5 w-3.5" />
                {{ Math.abs(trend) }}%
            </span>
            <span>{{ detail }}</span>
        </div>
    </div>
</template>
