<script setup>
import { Head } from '@inertiajs/vue3'
import { computed, ref, onMounted, watch } from 'vue'
import { useMainStore } from '@/Stores/main'
import {
  mdiAccountMultiple,
  mdiCartOutline,
  mdiChartTimelineVariant,
  mdiReload,
  mdiChartPie,
  mdiAccountDetails,
  mdiTrendingUp,
  mdiStore
} from '@mdi/js'
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
import BaseIcon from '@/Components/BaseIcon.vue'
import UserAvatar from '@/Components/UserAvatar.vue'

const chartData = ref(null)

const newUsers = ref([])
const userCount = ref(0)
const salesAmount = ref(0)
const orderCount = ref(0)

const period = ref('week')
const salesData = ref([])

const loading = ref(false)

const fetchDashboardData = async () => {
  loading.value = true
  try {
    const response = await axios.get(`/api/dashboard?period=${period.value}`)
    newUsers.value = response.data.newUsers
    userCount.value = response.data.userCount
    salesAmount.value = response.data.salesAmount
    orderCount.value = response.data.orderCount
    salesData.value = response.data.salesData
    
    if (response.data.salesData && response.data.salesData.length > 0) {
      updateChartData()
    }
    
    console.log('Sales Data:', response.data.salesData)
    console.log('Chart Data:', chartData.value)
    
  } catch (error) {
    console.error('Error fetching dashboard data:', error)
  } finally {
    loading.value = false
  }
}

watch(period, fetchDashboardData)

onMounted(() => {
  fetchDashboardData()
  fetchStatsData()
})

const mainStore = useMainStore()

const clientBarItems = computed(() => mainStore.clients?.slice(0, 4) || [])

const transactionBarItems = computed(() => mainStore.history)

const chartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  scales: {
    y: {
      beginAtZero: true,
      grid: {
        display: true,
        color: mainStore.isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
      },
      ticks: {
        color: mainStore.isDark ? '#e5e5e5' : '#666',
        callback: function (value) {
          return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND',
            notation: 'compact',
            compactDisplay: 'short'
          }).format(value)
        }
      }
    },
    x: {
      grid: {
        display: false,
        color: mainStore.isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
      },
      ticks: {
        color: mainStore.isDark ? '#e5e5e5' : '#666'
      }
    }
  },
  plugins: {
    legend: {
      display: true,
      position: 'top',
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
}))

const updateChartData = () => {
  if (!salesData.value || !Array.isArray(salesData.value) || salesData.value.length === 0) {
    console.log('No sales data available')
    chartData.value = null
    return
  }

  const labels = salesData.value.map(item => item.date)
  const data = salesData.value.map(item => parseFloat(item.total_sales))

  console.log('Labels:', labels)
  console.log('Data:', data)

  if (!labels.length || !data.length) {
    console.log('No valid data after transformation')
    chartData.value = null
    return
  }

  chartData.value = {
    labels: labels,
    datasets: [
      {
        label: 'Doanh thu',
        data: data,
        fill: true,
        borderColor: mainStore.isDark ? '#10B981' : '#10B981',
        backgroundColor: mainStore.isDark ? 'rgba(16, 185, 129, 0.2)' : 'rgba(16, 185, 129, 0.1)',
        tension: 0.4,
        borderWidth: 2,
        pointRadius: 4,
        pointBackgroundColor: '#10B981',
        pointBorderColor: mainStore.isDark ? '#1E293B' : '#fff',
        pointBorderWidth: 2,
        pointHoverRadius: 6,
      }
    ]
  }
}

watch(salesData, (newValue) => {
  console.log('Sales Data changed:', newValue)
  updateChartData()
}, { deep: true })

watch(() => mainStore.isDark, () => {
  updateChartData();
}, { immediate: true });

const hasChartData = computed(() => {
  return chartData.value !== null && 
         chartData.value.datasets && 
         chartData.value.datasets[0].data && 
         chartData.value.datasets[0].data.length > 0
})

const viewAllCustomers = () => {
  router.visit(route('users.index'));
}

const viewCustomerDetails = (userId) => {
  router.visit(route('users.show', { id: userId }));
}

const activeStatsTab = ref('overview')
const statsData = ref({
  peakHours: [],
  popularServices: [],
  topCustomers: [],
  bestSellingProducts: [],
  lowStockProducts: [],
  cancelledServices: []
})

const fetchStatsData = async () => {
  try {
    const response = await axios.get(`/api/dashboard/stats?period=${period.value}`)
    statsData.value = response.data
  } catch (error) {
    console.error('Error fetching stats data:', error)
  }
}

watch(period, () => {
  fetchDashboardData()
  fetchStatsData()
})

const statsTabs = computed(() => [
  { id: 'overview', label: 'Tổng quan', icon: mdiChartTimelineVariant },
  { id: 'services', label: 'Dịch vụ', icon: mdiStore },
  { id: 'products', label: 'Sản phẩm', icon: mdiCartOutline },
  { id: 'customers', label: 'Khách hàng', icon: mdiAccountMultiple }
])
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
            {{ loading ? 'Đang tải dữ liệu...' : 'Chưa có dữ liệu doanh thu trong khoảng thời gian này' }}
          </p>
        </div>
      </CardBox>

      <SectionTitleLineWithButton :icon="mdiTrendingUp" title="Phân tích chi tiết">
      </SectionTitleLineWithButton>

      <CardBox class="mb-6">
        <div class="border-b dark:border-slate-700 mb-4">
          <div class="flex space-x-4">
            <button v-for="tab in statsTabs" :key="tab.id" @click="activeStatsTab = tab.id"
              class="pb-2 px-4 transition-all duration-200" :class="[
                activeStatsTab === tab.id
                  ? 'border-b-2 border-blue-500 text-blue-600 dark:text-blue-400'
                  : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
              ]">
              <div class="flex items-center space-x-2">
                <BaseIcon :path="tab.icon" class="w-5 h-5" />
                <span>{{ tab.label }}</span>
              </div>
            </button>
          </div>
        </div>

        <div v-if="activeStatsTab === 'overview'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="p-4 border rounded-lg dark:border-slate-700">
            <h4 class="font-semibold mb-3 dark:text-white">Thời điểm đông khách</h4>
            <div class="space-y-2">
              <div v-for="(peak, index) in statsData.peakHours" :key="index" class="flex justify-between items-center">
                <span class="text-gray-600 dark:text-gray-400">{{ peak.time }}</span>
                <div class="flex items-center space-x-2">
                  <div class="h-2 bg-blue-500 rounded" :style="{ width: `${peak.percentage}%` }"></div>
                  <span class="text-sm text-gray-500">{{ peak.count }} khách</span>
                </div>
              </div>
            </div>
          </div>

          <div class="p-4 border rounded-lg dark:border-slate-700">
            <h4 class="font-semibold mb-3 dark:text-white">Dịch vụ phổ biến</h4>
            <div class="space-y-2">
              <div v-for="service in statsData.popularServices" :key="service.id"
                class="flex justify-between items-center">
                <span class="text-gray-600 dark:text-gray-400">{{ service.name }}</span>
                <span class="text-sm text-gray-500">{{ service.bookings }} lượt đặt</span>
              </div>
            </div>
          </div>
        </div>

        <div v-if="activeStatsTab === 'services'" class="grid grid-cols-1 gap-4">
          <div class="p-4 border rounded-lg dark:border-slate-700">
            <h4 class="font-semibold mb-3 dark:text-white">Dịch vụ bị hủy nhiều</h4>
            <div class="space-y-2">
              <div v-for="service in statsData.cancelledServices" :key="service.id"
                class="flex justify-between items-center">
                <span class="text-gray-600 dark:text-gray-400">{{ service.name }}</span>
                <div class="flex items-center space-x-4">
                  <span class="text-sm text-gray-500">{{ service.cancelled_count }} lần hủy</span>
                  <span class="text-sm text-red-500">{{ service.cancel_rate }}%</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-if="activeStatsTab === 'products'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="p-4 border rounded-lg dark:border-slate-700">
            <h4 class="font-semibold mb-3 dark:text-white">Sản phẩm bán chạy</h4>
            <div class="space-y-2">
              <div v-for="product in statsData.bestSellingProducts" :key="product.id"
                class="flex justify-between items-center">
                <span class="text-gray-600 dark:text-gray-400">{{ product.name }}</span>
                <span class="text-sm text-gray-500">{{ product.sold }} đã bán</span>
              </div>
            </div>
          </div>

          <div class="p-4 border rounded-lg dark:border-slate-700">
            <h4 class="font-semibold mb-3 dark:text-white">Sản phẩm sắp hết hàng</h4>
            <div class="space-y-2">
              <div v-for="product in statsData.lowStockProducts" :key="product.id"
                class="flex justify-between items-center">
                <span class="text-gray-600 dark:text-gray-400">{{ product.name }}</span>
                <span class="text-sm text-red-500">Còn {{ product.stock }} sản phẩm</span>
              </div>
            </div>
          </div>
        </div>

        <div v-if="activeStatsTab === 'customers'" class="grid grid-cols-1 gap-4">
          <div class="p-4 border rounded-lg dark:border-slate-700">
            <h4 class="font-semibold mb-3 dark:text-white">Khách hàng thân thiết</h4>
            <div class="space-y-2">
              <div v-for="customer in statsData.topCustomers" :key="customer.id"
                class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                  <UserAvatar :fullName="customer.name" size="sm" />
                  <span class="text-gray-600 dark:text-gray-400">{{ customer.name }}</span>
                </div>
                <div class="flex items-center space-x-4">
                  <span class="text-sm text-gray-500">{{ customer.total_spent }}đ</span>
                  <span class="text-sm text-gray-500">{{ customer.visit_count }} lần ghé</span>
                </div>
              </div>
            </div>
          </div>
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
