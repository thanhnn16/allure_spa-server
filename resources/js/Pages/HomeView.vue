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
  mdiStore,
  mdiClockOutline,
  mdiChartLineVariant
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
import { Chart } from 'chart.js/auto'
import {
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
} from 'chart.js'

Chart.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
)

const chartData = ref({
  labels: [],
  datasets: [{
    label: 'Doanh thu',
    data: [],
    fill: true,
    borderColor: '#10B981',
    backgroundColor: 'rgba(16, 185, 129, 0.2)',
    tension: 0.4,
    borderWidth: 2,
    pointRadius: 4,
    pointBackgroundColor: '#10B981',
    pointBorderColor: '#fff',
    pointBorderWidth: 2,
    pointHoverRadius: 6,
  }]
})

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
    return
  }

  chartData.value = {
    labels: salesData.value.map(item => item.date),
    datasets: [{
      ...chartData.value.datasets[0],
      data: salesData.value.map(item => parseFloat(item.total_sales)),
      borderColor: mainStore.isDark ? '#10B981' : '#10B981',
      backgroundColor: mainStore.isDark
        ? 'rgba(16, 185, 129, 0.2)'
        : 'rgba(16, 185, 129, 0.1)',
      pointBorderColor: mainStore.isDark ? '#1E293B' : '#fff'
    }]
  }
}

watch(salesData, (newValue) => {
  updateChartData()
}, { deep: true })

watch(() => mainStore.isDark, () => {
  updateChartData();
}, { immediate: true });

const hasChartData = computed(() => {
  return chartData.value.datasets[0].data.length > 0
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

const isAsideLgActive = ref(true)

const hasStatsData = computed(() => {
  const currentTabData = {
    'overview': statsData.value.peakHours?.length || statsData.value.popularServices?.length,
    'services': statsData.value.cancelledServices?.length,
    'products': statsData.value.bestSellingProducts?.length || statsData.value.lowStockProducts?.length,
    'customers': statsData.value.topCustomers?.length
  }
  return currentTabData[activeStatsTab.value] > 0
})
</script>

<template>
  <LayoutAuthenticated>

    <Head title="Dashboard" />
    <SectionMain :is-aside-lg-active="isAsideLgActive">
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
        <div class="border-b dark:border-slate-700 mb-4 h-12">
          <div class="flex space-x-4">
            <button 
              v-for="tab in statsTabs" 
              :key="tab.id" 
              @click="activeStatsTab = tab.id"
              class="pb-2 px-6 transition-all duration-200 relative"
              :class="[
                'hover:text-blue-600 dark:hover:text-blue-400',
                activeStatsTab === tab.id 
                  ? 'text-blue-600 dark:text-blue-400' 
                  : 'text-gray-500 dark:text-gray-400'
              ]"
            >
              <div class="flex items-center space-x-2">
                <BaseIcon :path="tab.icon" class="w-5 h-5" />
                <span>{{ tab.label }}</span>
              </div>
              <div 
                class="absolute bottom-0 left-0 w-full h-0.5 transition-all duration-200"
                :class="[
                  activeStatsTab === tab.id 
                    ? 'bg-blue-500 scale-100' 
                    : 'bg-transparent scale-0'
                ]"
              ></div>
            </button>
          </div>
        </div>

        <div v-if="loading" class="flex items-center justify-center h-64">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
        </div>

        <div 
          v-else-if="!hasStatsData" 
          class="flex flex-col items-center justify-center h-64 text-gray-500 dark:text-gray-400"
        >
          <BaseIcon :path="mdiChartLineVariant" class="w-16 h-16 mb-4 opacity-50" />
          <p>Không có dữ liệu thống kê cho khoảng thời gian này</p>
          <BaseButton 
            :icon="mdiReload" 
            label="Tải lại" 
            color="info" 
            class="mt-4" 
            @click="fetchStatsData" 
          />
        </div>

        <div class="min-h-[400px]">
          <TransitionGroup name="fade">
            <div v-if="activeStatsTab === 'overview' && hasStatsData" key="overview" class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="p-6 bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-100 dark:border-slate-700">
                <h4 class="font-semibold mb-4 dark:text-white flex items-center">
                  <BaseIcon :path="mdiClockOutline" class="w-5 h-5 mr-2" />
                  Thời điểm đông khách
                </h4>
                <div class="space-y-3">
                  <div v-for="(peak, index) in statsData.peakHours" :key="index" class="flex flex-col">
                    <div class="flex justify-between items-center mb-1">
                      <span class="text-gray-600 dark:text-gray-400">{{ peak.time }}</span>
                      <span class="text-sm text-gray-500">{{ peak.count }} khách</span>
                    </div>
                    <div class="w-full h-2 bg-gray-100 dark:bg-slate-700 rounded-full overflow-hidden">
                      <div 
                        class="h-full bg-blue-500 rounded-full transition-all duration-500"
                        :style="{ width: `${peak.percentage}%` }"
                      ></div>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="p-6 bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-100 dark:border-slate-700">
                <h4 class="font-semibold mb-4 dark:text-white flex items-center">
                  <BaseIcon :path="mdiTrendingUp" class="w-5 h-5 mr-2" />
                  Dịch vụ phổ biến
                </h4>
                <div class="space-y-3">
                  <div v-for="service in statsData.popularServices" :key="service.id" class="flex flex-col">
                    <div class="flex justify-between items-center mb-1">
                      <span class="text-gray-600 dark:text-gray-400">{{ service.name }}</span>
                      <span class="text-sm text-gray-500">{{ service.bookings }} lượt đặt</span>
                    </div>
                    <div class="w-full h-2 bg-gray-100 dark:bg-slate-700 rounded-full overflow-hidden">
                      <div 
                        class="h-full bg-green-500 rounded-full transition-all duration-500"
                        :style="{ width: `${service.percentage}%` }"
                      ></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="activeStatsTab === 'services' && hasStatsData" key="services" class="grid grid-cols-1 gap-4">
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

            <div v-if="activeStatsTab === 'products' && hasStatsData" key="products" class="grid grid-cols-1 md:grid-cols-2 gap-4">
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

            <div v-if="activeStatsTab === 'customers' && hasStatsData" key="customers" class="grid grid-cols-1 gap-4">
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
          </TransitionGroup>
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

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
