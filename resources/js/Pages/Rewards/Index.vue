<script setup>
import { ref, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionMain from '@/Components/SectionMain.vue'
import CardBox from '@/Components/CardBox.vue'
import BaseButton from '@/Components/BaseButton.vue'
import FormControl from '@/Components/FormControl.vue'
import { mdiGift, mdiHistory, mdiPlus, mdiSync } from '@mdi/js'
import axios from 'axios'
import { useToast } from 'vue-toastification'
import RewardList from './Partials/RewardList.vue'
import RedemptionHistory from './Partials/RedemptionHistory.vue'
import CreateRewardForm from './Partials/CreateRewardForm.vue'
import { mdiMagnify } from '@mdi/js'

const toast = useToast()
const props = defineProps({
  products: Array,
  services: Array
})
const activeTab = ref('rewards')
const loading = ref(false)
const search = ref('')
const showCreateForm = ref(false)

const switchTab = (tab) => {
    activeTab.value = tab
    showCreateForm.value = false
}

const handleRewardCreated = () => {
    showCreateForm.value = false
    // Refresh danh sách rewards
    if (activeTab.value === 'rewards') {
        // Gọi method refresh trong RewardList component
        // (cần thêm ref cho component RewardList)
    }
}
</script>

<template>
    <LayoutAuthenticated>
        <Head title="Quản lý Rewards" />
        <SectionMain>
            <CardBox class="mb-6 dark:bg-dark-surface">
                <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 space-y-4 md:space-y-0">
                    <h1 class="text-2xl font-bold dark:text-dark-text">Quản lý Rewards</h1>
                    <BaseButton 
                        v-if="!showCreateForm" 
                        color="success" 
                        :icon="mdiPlus" 
                        @click="showCreateForm = true"
                        class="w-full md:w-auto"
                    >
                        Tạo Reward mới
                    </BaseButton>
                </div>

                <div v-if="showCreateForm" class="mb-6">
                    <CardBox class="dark:bg-dark-surface-secondary">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 space-y-4 md:space-y-0">
                            <h2 class="text-xl font-semibold dark:text-dark-text">Tạo Reward mới</h2>
                            <BaseButton 
                                color="danger" 
                                @click="showCreateForm = false"
                                class="w-full md:w-auto"
                            >
                                Đóng
                            </BaseButton>
                        </div>
                        <CreateRewardForm 
                            :products="products"
                            :services="services"
                            @created="handleRewardCreated" 
                        />
                    </CardBox>
                </div>

                <div v-if="!showCreateForm" class="space-y-6">
                    <!-- Tab buttons -->
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4">
                        <BaseButton 
                            :color="activeTab === 'rewards' ? 'info' : 'white'" 
                            :icon="mdiGift"
                            @click="switchTab('rewards')"
                            class="w-full sm:w-auto"
                        >
                            Danh sách Rewards
                        </BaseButton>
                        <BaseButton 
                            :color="activeTab === 'history' ? 'info' : 'white'" 
                            :icon="mdiHistory"
                            @click="switchTab('history')"
                            class="w-full sm:w-auto"
                        >
                            Lịch sử đổi thưởng
                        </BaseButton>
                    </div>

                    <!-- Search bar -->
                    <div class="relative">
                        <FormControl 
                            v-model="search" 
                            :icon="mdiMagnify" 
                            placeholder="Tìm kiếm..."
                            class="dark:bg-dark-surface"
                        />
                    </div>

                    <!-- Content based on active tab -->
                    <div class="bg-white dark:bg-dark-surface rounded-lg shadow-sm">
                        <RewardList 
                            v-if="activeTab === 'rewards'" 
                            :search="search" 
                        />
                        <RedemptionHistory 
                            v-else 
                            :search="search" 
                        />
                    </div>
                </div>
            </CardBox>
        </SectionMain>
    </LayoutAuthenticated>
</template>