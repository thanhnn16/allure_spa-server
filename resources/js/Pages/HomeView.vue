<script setup>
import { Head, usePage, Link } from '@inertiajs/vue3'
import { computed, ref, onMounted, watch } from 'vue'
import { useMainStore } from '@/Stores/main'
import {
  mdiAccountMultiple,
  mdiCartOutline,
  mdiChartTimelineVariant,
  mdiMonitorCellphone,
  mdiReload,
  mdiGithub,
  mdiChartPie,
  mdiDetails,
  mdiAccountDetails
} from '@mdi/js'
import * as chartConfig from '@/Components/Charts/chart.config.js'
import LineChart from '@/Components/Charts/LineChart.vue'
import SectionMain from '@/Components/SectionMain.vue'
import CardBoxWidget from '@/Components/CardBoxWidget.vue'
import CardBox from '@/Components/CardBox.vue'
import CustomerTable from '@/Pages/Customers/Components/CustomerTable.vue'
import BaseButton from '@/Components/BaseButton.vue'
import CardBoxTransaction from '@/Components/CardBoxTransaction.vue'
import CardBoxClient from '@/Components/CardBoxClient.vue'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import axios from 'axios'
import { router } from '@inertiajs/vue3'

const chartData = ref(null)

const newUsers = ref([])
const userCount = ref(0)
const salesAmount = ref(0)
const orderCount = ref(0)

const period = ref('week')
const salesData = ref([])

const fetchDashboardData = async () => {
  try {
    const response = await axios.get(`/api/dashboard?period=${period.value}`)
    newUsers.value = response.data.newUsers
    userCount.value = response.data.userCount
    salesAmount.value = response.data.salesAmount
    orderCount.value = response.data.orderCount
    salesData.value = response.data.salesData
    updateChartData()
  } catch (error) {
    console.error('Error fetching dashboard data:', error)
    if (error.response) {
      // The request was made and the server responded with a status code
      // that falls out of the range of 2xx
      console.error(error.response.data)
      console.error(error.response.status)
      console.error(error.response.headers)
    } else if (error.request) {
      // The request was made but no response was received
      console.error(error.request)
    } else {
      // Something happened in setting up the request that triggered an Error
      console.error('Error', error.message)
    }
  }
}

watch(period, fetchDashboardData)

onMounted(() => {
  fetchDashboardData()
})

const mainStore = useMainStore()

// Modify this line to handle potential undefined clients
const clientBarItems = computed(() => mainStore.clients?.slice(0, 4) || [])

const transactionBarItems = computed(() => mainStore.history)

const chartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  scales: {
    y: {
      beginAtZero: true,
      grid: {
        display: true
      },
      ticks: {
        callback: function (value) {
          return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND',
            notation: 'compact',
            compactDisplay: 'short'
          }).format(value);
        }
      }
    },
    x: {
      grid: {
        display: false
      }
    }
  },
  plugins: {
    legend: {
      display: true,
      position: 'top'
    },
    tooltip: {
      callbacks: {
        label: function (context) {
          let label = context.dataset.label || '';
          if (label) {
            label += ': ';
          }
          if (context.parsed.y !== null) {
            label += new Intl.NumberFormat('vi-VN', {
              style: 'currency',
              currency: 'VND'
            }).format(context.parsed.y);
          }
          return label;
        }
      }
    }
  }
}));

const updateChartData = () => {
  if (!salesData.value || salesData.value.length === 0) {
    chartData.value = null;
    return;
  }

  const labels = salesData.value.map(item => item.date);
  const data = salesData.value.map(item => item.total_sales);

  chartData.value = {
    labels: labels,
    datasets: [
      {
        label: 'Doanh thu',
        data: data,
        fill: true,
        borderColor: '#10B981', // Màu xanh lá
        backgroundColor: 'rgba(16, 185, 129, 0.1)', // Màu xanh lá nhạt
        tension: 0.4,
        borderWidth: 2,
        pointRadius: 4,
        pointBackgroundColor: '#10B981',
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
        pointHoverRadius: 6,
      }
    ]
  };
};

watch(salesData, () => {
  updateChartData();
}, { deep: true });

const hasChartData = computed(() => {
  return chartData.value !== null && salesData.value && salesData.value.length > 0;
});

const viewAllCustomers = () => {
  router.visit(route('users.index'));
}

const viewCustomerDetails = (userId) => {
  router.visit(route('users.show', { id: userId }));
}
const darkChartOptions = computed(() => ({
  ...chartOptions.value,
  scales: {
    ...chartOptions.value.scales,
    y: {
      ...chartOptions.value.scales.y,
      grid: {
        color: mainStore.isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
      },
      ticks: {
        ...chartOptions.value.scales.y.ticks,
        color: mainStore.isDark ? '#e5e5e5' : '#666',
        callback: function (value) {
          return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value)
        }
      }
    },
    x: {
      grid: {
        color: mainStore.isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
      },
      ticks: {
        color: mainStore.isDark ? '#e5e5e5' : '#666'
      }
    }
  },
  plugins: {
    ...chartOptions.value.plugins,
    legend: {
      labels: {
        color: mainStore.isDark ? '#e5e5e5' : '#666'
      }
    },
    tooltip: {
      callbacks: {
        label: function (context) {
          let label = context.dataset.label || '';
          if (label) {
            label += ': ';
          }
          if (context.parsed.y !== null) {
            label += new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(context.parsed.y);
          }
          return label;
        }
      }
    }
  }
}))
</script>

<template>
  <LayoutAuthenticated>

    <Head title="Dashboard" />
    <SectionMain>
      <SectionTitleLineWithButton :icon="mdiChartTimelineVariant" title="Tổng quan" main>
      </SectionTitleLineWithButton>

      <div class="mb-6">
        <select v-model="period" class="form-select dark:bg-dark-surface dark:text-dark-text dark:border-dark-border">
          <option value="week">Tuần này</option>
          <option value="month">Tháng này</option>
          <option value="quarter">Quý này</option>
          <option value="year">Năm nay</option>
        </select>
      </div>

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 mb-6">
        <CardBoxWidget trend="12%" trend-type="up" color="text-emerald-500" :icon="mdiAccountMultiple"
          :number="userCount" label="Khách hàng" />
        <CardBoxWidget trend="12%" trend-type="down" color="text-blue-500" :icon="mdiCartOutline"
          :number="Number(salesAmount)" prefix="₫" label="Doanh số" />
        <CardBoxWidget trend="New" trend-type="alert" color="text-red-500" :icon="mdiChartTimelineVariant"
          :number="orderCount" label="Đơn hàng" />
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="flex flex-col justify-between">
          <CardBoxTransaction v-for="(transaction, index) in transactionBarItems" :key="index"
            :amount="transaction.amount" :date="transaction.date" :business="transaction.business"
            :type="transaction.type" :name="transaction.name" :account="transaction.account" />
        </div>
        <div class="flex flex-col justify-between">
          <CardBoxClient v-for="client in clientBarItems" :key="client.id" :name="client.name" :login="client.login"
            :date="client.created" :progress="client.progress" />
        </div>
      </div>

      <SectionTitleLineWithButton :icon="mdiChartPie" title="Doanh thu">
      </SectionTitleLineWithButton>

      <CardBox class="mb-6 dark:bg-dark-surface">
        <div class="flex items-center justify-between mb-3">
          <select v-model="period" class="form-select w-40 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300">
            <option value="week">Tuần này</option>
            <option value="month">Tháng này</option>
            <option value="quarter">Quý này</option>
            <option value="year">Năm nay</option>
          </select>
          <BaseButton :icon="mdiReload" color="info" @click="fetchDashboardData" :loading="loading" />
        </div>

        <div v-if="hasChartData" class="h-96">
          <LineChart :data="chartData" :options="chartOptions" />
        </div>
        <div v-else class="flex items-center justify-center h-96 bg-gray-50 dark:bg-slate-800 rounded-lg">
          <p class="text-gray-500 dark:text-slate-400">
            Chưa có dữ liệu doanh thu trong khoảng thời gian này
          </p>
        </div>
      </CardBox>

      <SectionTitleLineWithButton :icon="mdiAccountMultiple" title="Khách hàng mới">
        <BaseButton :icon="mdiAccountDetails" color="whiteDark" @click="viewAllCustomers" label="Xem tất cả"
          rounded-full />
      </SectionTitleLineWithButton>

      <CardBox has-table px-4 py-2 mx-2>
        <CustomerTable :items="newUsers" @view-details="viewCustomerDetails" />
      </CardBox>
    </SectionMain>
  </LayoutAuthenticated>
</template>
