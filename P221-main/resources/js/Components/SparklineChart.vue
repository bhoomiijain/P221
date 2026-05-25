<script setup>
import { Line, Bar, Doughnut } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale, LinearScale,
    PointElement, LineElement,
    BarElement, ArcElement,
    Tooltip, Filler, Legend,
} from 'chart.js';

ChartJS.register(
    CategoryScale, LinearScale,
    PointElement, LineElement,
    BarElement, ArcElement,
    Tooltip, Filler, Legend,
);

const props = defineProps({
    type:    { type: String, default: 'line' },   // line | bar | doughnut
    data:    Object,          // pre-built ChartJS dataset object (takes priority)
    labels:  Array,           // simple labels array
    values:  Array,           // simple values array
    color:   { type: String, default: '#38b2ac' },
    height:  { type: Number, default: 60 },
    options: Object,          // full ChartJS options override
});

/* ── Default options ── */
const defaultLineOpts = {
    responsive: true,
    maintainAspectRatio: false,
    animation: { duration: 1000, easing: 'easeOutQuart' },
    plugins: { tooltip: { enabled: false }, legend: { display: false } },
    scales: { x: { display: false }, y: { display: false } },
    elements: {
        line: { tension: 0.4, borderWidth: 2 },
        point: { radius: 0, hoverRadius: 4 },
    },
};

const defaultBarOpts = {
    responsive: true,
    maintainAspectRatio: false,
    animation: { duration: 1000, easing: 'easeOutQuart' },
    plugins: { legend: { display: false }, tooltip: { mode: 'index' } },
    scales: {
        x: { ticks: { color: '#5C6C75', font: { size: 11 } }, grid: { display: false } },
        y: { ticks: { color: '#5C6C75', font: { size: 11 } }, grid: { color: 'rgba(128,128,128,0.1)' } },
    },
    borderRadius: 4,
};

const defaultDoughnutOpts = {
    responsive: true,
    maintainAspectRatio: false,
    animation: { duration: 1000, easing: 'easeOutQuart', animateScale: true },
    plugins: {
        legend: {
            position: 'right',
            labels: { font: { size: 12 }, color: '#889397', padding: 14, boxWidth: 12 },
        },
    },
    cutout: '68%',
};



/* ── Build simple dataset if no pre-built data given ── */
function buildChartData() {
    if (props.data) return props.data;
    return {
        labels: props.labels || [],
        datasets: [{
            data: props.values || [],
            backgroundColor: props.type === 'doughnut'
                ? ['#38b2ac', '#fda4af', '#f6ad55', '#b794f4']
                : props.color + '33',
            borderColor: props.color,
            fill: props.type === 'line',
            borderWidth: 2,
        }],
    };
}
</script>

<template>
    <div :style="{ height: height + 'px' }">
        <Line
            v-if="type === 'line'"
            :data="buildChartData()"
            :options="options || defaultLineOpts"
        />
        <Bar
            v-else-if="type === 'bar'"
            :data="buildChartData()"
            :options="options || defaultBarOpts"
        />
        <Doughnut
            v-else-if="type === 'doughnut'"
            :data="buildChartData()"
            :options="options || defaultDoughnutOpts"
        />
    </div>
</template>
