<script setup>
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js'
import { computed } from 'vue'

ChartJS.register(ArcElement, Tooltip, Legend)

const props = defineProps({
  data: {
    type: Object,
    required: true
  },
  title: String
})

const chartData = computed(() => ({
  labels: Object.keys(props.data),
  datasets: [{
    data: Object.values(props.data),
    backgroundColor: [
      '#FF6384',
      '#36A2EB',
      '#FFCE56',
      '#4BC0C0',
      '#9966FF'
    ]
  }]
}))

const options = {
  responsive: true,
  plugins: {
    legend: {
      position: 'bottom'
    },
    title: {
      display: true,
      text: props.title
    }
  }
}
</script>

<template>
  <Pie :data="chartData" :options="options" />
</template>
