<script setup>
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js'
import { computed } from 'vue'

ChartJS.register(
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend
)

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
    label: props.title,
    data: Object.values(props.data),
    backgroundColor: '#36A2EB'
  }]
}))

const options = {
  responsive: true,
  plugins: {
    legend: {
      display: false
    },
    title: {
      display: true,
      text: props.title
    }
  }
}
</script>

<template>
  <Bar :data="chartData" :options="options" />
</template>