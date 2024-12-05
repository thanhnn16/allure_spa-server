<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { mdiPlus, mdiDelete, mdiPencil, mdiSync, mdiChartBox, mdiClose } from '@mdi/js'
import BaseButton from '@/Components/BaseButton.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import CardBox from '@/Components/CardBox.vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()
const groups = ref([])
const showCreateForm = ref(false)
const editingGroup = ref(null)
const loading = ref(false)
const showStats = ref(false)
const selectedGroupStats = ref(null)
const syncingGroups = ref(false)

const form = ref({
    name: '',
    description: '',
    conditions: [
        { field: '', operator: '', value: '' }
    ]
})

const availableFields = [
    { value: 'loyalty_points', label: 'Điểm tích lũy' },
    { value: 'purchase_count', label: 'Số lần mua hàng' },
    { value: 'last_visit', label: 'Lần ghé thăm cuối' },
    { value: 'gender', label: 'Giới tính' },
    { value: 'age', label: 'Độ tuổi' },
    { value: 'skin_condition', label: 'Tình trạng da' },
    { value: 'total_spent', label: 'Tổng chi tiêu' },
    { value: 'membership_duration', label: 'Thời gian là thành viên' },
    { value: 'favorite_services', label: 'Dịch vụ yêu thích' }
]

const operators = {
    loyalty_points: [
        { value: '>=', label: 'Lớn hơn hoặc bằng' },
        { value: '<=', label: 'Nhỏ hơn hoặc bằng' },
        { value: 'between', label: 'Trong khoảng' }
    ],
    purchase_count: [
        { value: '>=', label: 'Lớn hơn hoặc bằng' },
        { value: '<=', label: 'Nhỏ hơn hoặc bằng' },
        { value: 'between', label: 'Trong khoảng' }
    ],
    last_visit: [
        { value: 'within', label: 'Trong vòng' },
        { value: 'not_within', label: 'Không trong vòng' },
        { value: 'never', label: 'Chưa từng ghé thăm' }
    ],
    gender: [
        { value: '=', label: 'Là' },
        { value: '!=', label: 'Không phải là' }
    ],
    age: [
        { value: '>=', label: 'Lớn hơn hoặc bằng' },
        { value: '<=', label: 'Nhỏ hơn hoặc bằng' },
        { value: 'between', label: 'Trong khoảng' }
    ],
    skin_condition: [
        { value: '=', label: 'Là' },
        { value: 'contains', label: 'Chứa' },
        { value: 'not_contains', label: 'Không chứa' }
    ],
    total_spent: [
        { value: '>=', label: 'Lớn hơn hoặc bằng' },
        { value: '<=', label: 'Nhỏ hơn hoặc bằng' },
        { value: 'between', label: 'Trong khoảng' }
    ],
    membership_duration: [
        { value: '>=', label: 'Lớn hơn hoặc bằng (tháng)' },
        { value: '<=', label: 'Nhỏ hơn hoặc bằng (tháng)' },
        { value: 'between', label: 'Trong khoảng (tháng)' }
    ],
    favorite_services: [
        { value: 'includes', label: 'Bao gồm' },
        { value: 'excludes', label: 'Không bao gồm' }
    ]
}

const isFormValid = computed(() => {
    return form.value.name &&
        form.value.conditions.every(c => 
            c.field && 
            c.operator && 
            validateConditionValue(c)
        )
})

const watchFieldChanges = (condition) => {
    watch(() => condition.field, (newField) => {
        condition.operator = ''
        condition.value = ''
        
        switch (newField) {
            case 'gender':
                condition.operator = '='
                condition.value = 'male'
                break
                
            case 'last_visit':
                condition.operator = 'within'
                condition.value = '30'
                break
                
            case 'loyalty_points':
            case 'purchase_count':
                condition.operator = '>='
                condition.value = '0'
                break
                
            case 'age':
                condition.operator = '>='
                condition.value = '18'
                break
                
            case 'skin_condition':
                condition.operator = 'contains'
                condition.value = ''
                break
                
            case 'total_spent':
                condition.operator = '>='
                condition.value = '1000000'
                break
                
            case 'membership_duration':
                condition.operator = '>='
                condition.value = '1'
                break
                
            case 'favorite_services':
                condition.operator = 'includes'
                condition.value = ''
                break
        }
    })

    watch(() => condition.operator, (newOperator) => {
        if (condition.field === 'last_visit' && newOperator === 'never') {
            condition.value = 'true'
        } else if (newOperator === 'between') {
            condition.value = '0,100'
        }
    })
}

const addCondition = () => {
    const newCondition = { field: '', operator: '', value: '' }
    form.value.conditions.push(newCondition)
    watchFieldChanges(newCondition)
}

const removeCondition = (index) => {
    if (form.value.conditions.length > 1) {
        form.value.conditions.splice(index, 1)
    }
}

const fetchGroups = async () => {
    try {
        loading.value = true
        const response = await axios.get('/api/user-groups')
        console.log('Fetched groups:', response.data)
        groups.value = response.data.data
    } catch (error) {
        toast.error('Lỗi khi tải nhóm: ' + error.message)
    } finally {
        loading.value = false
    }
}

const syncAllGroups = async () => {
    try {
        syncingGroups.value = true
        await axios.post('/api/user-groups/sync-all')
        toast.success('Đồng bộ nhóm thành công')
        await fetchGroups()
    } catch (error) {
        toast.error('Lỗi khi đồng bộ nhóm: ' + error.message)
    } finally {
        syncingGroups.value = false
    }
}

const showGroupStats = async (groupId) => {
    try {
        loading.value = true
        const response = await axios.get(`/api/user-groups/${groupId}/stats`)
        selectedGroupStats.value = response.data
        showStats.value = true
    } catch (error) {
        toast.error('Lỗi khi lấy thống kê: ' + error.message)
    } finally {
        loading.value = false
    }
}

const saveGroup = async () => {
    try {
        loading.value = true
        if (editingGroup.value) {
            await axios.put(`/api/user-groups/${editingGroup.value.id}`, form.value)
        } else {
            await axios.post('/api/user-groups', form.value)
        }
        await fetchGroups()
        resetForm()
    } catch (error) {
        console.error('Lỗi khi lưu nhóm:', error)
    } finally {
        loading.value = false
    }
}

const editGroup = (group) => {
    editingGroup.value = group
    form.value = {
        name: group.name,
        description: group.description,
        conditions: Array.isArray(group.conditions) ? [...group.conditions] : [{ field: '', operator: '', value: '' }]
    }
    form.value.conditions.forEach(condition => {
        watchFieldChanges(condition)
    })
    showCreateForm.value = true
}

const deleteGroup = async (groupId) => {
    if (!confirm('Bạn có chắc muốn xóa nhóm này?')) return

    try {
        await axios.delete(`/api/user-groups/${groupId}`)
        await fetchGroups()
    } catch (error) {
        console.error('Lỗi khi xóa nhóm:', error)
    }
}

const resetForm = () => {
    form.value = {
        name: '',
        description: '',
        conditions: [{ field: '', operator: '', value: '' }]
    }
    editingGroup.value = null
    showCreateForm.value = false
}

const validateConditionValue = (condition) => {
    const { field, operator, value } = condition
    
    if (!value) return false
    
    switch (field) {
        case 'loyalty_points':
        case 'purchase_count':
        case 'age':
            if (operator === 'between') {
                const [min, max] = value.split(',')
                return !isNaN(min) && !isNaN(max) && Number(min) <= Number(max)
            }
            return !isNaN(value)
            
        case 'last_visit':
            if (operator === 'never') return true
            return !isNaN(value) && value > 0
            
        case 'gender':
            return ['male', 'female', 'other'].includes(value)
            
        case 'skin_condition':
            return value.length > 0
            
        case 'total_spent':
            if (operator === 'between') {
                const [min, max] = value.split(',')
                return !isNaN(min) && !isNaN(max) && Number(min) <= Number(max)
            }
            return !isNaN(value) && Number(value) >= 0
            
        case 'membership_duration':
            return !isNaN(value) && Number(value) > 0
            
        case 'favorite_services':
            return value.length > 0
            
        default:
            return true
    }
}

const getOperatorsForField = computed(() => (field) => {
    return operators[field] || []
})

onMounted(() => {
    fetchGroups()
    form.value.conditions.forEach(condition => {
        watchFieldChanges(condition)
    })
})
</script>

<template>
    <div class="space-y-6">
        <!-- Header với nút actions -->
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-4">
                <BaseButton v-if="!showCreateForm" color="info" :icon="mdiPlus" label="Tạo nhóm mới"
                    @click="showCreateForm = true" />
                <BaseButton color="success" :icon="mdiSync" :loading="syncingGroups" label="Đồng bộ tất cả"
                    @click="syncAllGroups" />
            </div>
        </div>

        <!-- Form tạo/chỉnh sửa nhóm -->
        <CardBox v-if="showCreateForm" class="mb-6 relative">
            <div class="absolute top-4 right-4">
                <BaseButton color="light" :icon="mdiClose" small @click="resetForm" />
            </div>

            <h3 class="text-xl font-semibold mb-6">
                {{ editingGroup ? 'Chỉnh sửa nhóm' : 'Tạo nhóm mới' }}
            </h3>

            <form @submit.prevent="saveGroup" class="space-y-6">
                <div class="grid md:grid-cols-2 gap-6">
                    <FormField label="Tên nhóm">
                        <FormControl v-model="form.name" type="text" required placeholder="Nhập tên nhóm" />
                    </FormField>

                    <FormField label="Mô tả">
                        <FormControl v-model="form.description" type="textarea" placeholder="Mô tả về nhóm" />
                    </FormField>
                </div>

                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <h4 class="font-semibold">Điều kiện</h4>
                        <BaseButton color="info" :icon="mdiPlus" small @click="addCondition" label="Thêm điều kiện" />
                    </div>

                    <TransitionGroup name="list" tag="div" class="space-y-4">
                        <div v-for="(condition, index) in form.conditions" :key="index"
                            class="flex gap-4 items-start p-4 rounded-lg border dark:border-dark-border bg-white dark:bg-dark-surface transition-colors duration-150">
                            <div class="flex-1">
                                <FormField label="Trường">
                                    <FormControl v-model="condition.field" :options="availableFields" type="select"
                                        required />
                                </FormField>
                            </div>

                            <div class="flex-1">
                                <FormField label="Điều kiện">
                                    <FormControl 
                                        v-model="condition.operator"
                                        :options="getOperatorsForField(condition.field)" 
                                        type="select" 
                                        required 
                                    />
                                </FormField>
                            </div>

                            <div class="flex-1">
                                <FormField label="Giá trị">
                                    <FormControl v-model="condition.value"
                                        :type="condition.field === 'gender' ? 'select' : 'text'" :options="condition.field === 'gender' ? [
                                            { value: 'male', label: 'Nam' },
                                            { value: 'female', label: 'Nữ' },
                                            { value: 'other', label: 'Khác' }
                                        ] : undefined" required />
                                </FormField>
                            </div>

                            <BaseButton v-if="form.conditions.length > 1" color="danger" :icon="mdiDelete" small
                                @click="removeCondition(index)" />
                        </div>
                    </TransitionGroup>
                </div>

                <div class="flex justify-end gap-4">
                    <BaseButton type="button" color="light" label="Hủy" @click="resetForm" />
                    <BaseButton type="submit" color="info" :loading="loading" :disabled="!isFormValid"
                        :label="editingGroup ? 'Cập nhật' : 'Tạo nhóm'" />
                </div>
            </form>
        </CardBox>

        <!-- Danh sách nhóm -->
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            <CardBox v-for="group in groups" :key="group.id"
                class="relative hover:shadow-lg transition-shadow duration-200" :class="{ 'opacity-75': loading }">
                <div class="absolute top-4 right-4 flex gap-2">
                    <BaseButton color="info" :icon="mdiChartBox" small @click="showGroupStats(group.id)"
                        title="Xem thống kê" />
                    <BaseButton color="success" :icon="mdiPencil" small @click="editGroup(group)" title="Chỉnh sửa" />
                    <BaseButton color="danger" :icon="mdiDelete" small @click="deleteGroup(group.id)" title="Xóa" />
                </div>

                <h3 class="text-lg font-semibold mb-2">{{ group.name }}</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-4">{{ group.description }}</p>

                <div class="space-y-2">
                    <div v-for="(condition, index) in group.conditions" :key="index"
                        class="text-sm text-gray-600 dark:text-gray-400 p-2 rounded-md bg-gray-50 dark:bg-dark-surface-hover">
                        <span class="font-medium">{{ availableFields.find(f => f.value === condition.field)?.label
                            }}</span>
                        <span class="mx-1">{{ operators[condition.field]?.find(o => o.value ===
                            condition.operator)?.label }}</span>
                        <span class="font-medium">{{ condition.value }}</span>
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-2">
                    <span
                        class="px-3 py-1 text-sm font-medium rounded-full bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-100">
                        {{ group.user_count }} thành viên
                    </span>
                </div>
            </CardBox>
        </div>

        <!-- Modal thống kê -->
        <CardBox v-if="showStats && selectedGroupStats"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
            @click.self="showStats = false">
            <div class="bg-white dark:bg-dark-surface p-6 rounded-lg w-full max-w-2xl mx-4">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-semibold">Thống kê nhóm</h3>
                    <BaseButton color="light" :icon="mdiClose" small @click="showStats = false" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 rounded-lg bg-gray-50 dark:bg-dark-surface-hover">
                        <div class="text-sm text-gray-600 dark:text-gray-400">Tổng số thành viên</div>
                        <div class="text-2xl font-bold">{{ selectedGroupStats.total_users }}</div>
                    </div>

                    <div class="p-4 rounded-lg bg-gray-50 dark:bg-dark-surface-hover">
                        <div class="text-sm text-gray-600 dark:text-gray-400">Điểm trung bình</div>
                        <div class="text-2xl font-bold">{{ Math.round(selectedGroupStats.loyalty_points_avg) }}</div>
                    </div>
                </div>
            </div>
        </CardBox>
    </div>
</template>

<style scoped>
.list-enter-active,
.list-leave-active {
    transition: all 0.3s ease;
}

.list-enter-from,
.list-leave-to {
    opacity: 0;
    transform: translateX(30px);
}
</style>