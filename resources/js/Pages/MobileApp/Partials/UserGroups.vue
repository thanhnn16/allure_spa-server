<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { mdiPlus, mdiDelete, mdiPencil, mdiSync, mdiChartBox, mdiClose } from '@mdi/js'
import BaseButton from '@/Components/BaseButton.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import CardBox from '@/Components/CardBox.vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'
import PieChart from '@/Components/Charts/PieChart.vue'
import BarChart from '@/Components/Charts/BarChart.vue'
import StatCard from '@/Components/Charts/StatCard.vue'
import CardBoxModal from '@/Components/CardBoxModal.vue'

const toast = useToast()
const groups = ref([])
const showCreateForm = ref(false)
const editingGroup = ref(null)
const loading = ref(false)
const showStats = ref(false)
const selectedGroupStats = ref(null)
const syncingGroups = ref(false)
const search = ref('')
const filter = ref('all')
const syncingGroup = ref(null)

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
        { value: '=', label: 'Là' }
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

const getDefaultValueForField = (field, operator) => {
    switch (field) {
        case 'loyalty_points':
        case 'purchase_count':
            return operator === 'between' ? '0,100' : '0'

        case 'last_visit':
            return operator === 'never' ? 'true' : '30'

        case 'gender':
            return 'male'

        case 'age':
            return operator === 'between' ? '18,60' : '18'

        case 'skin_condition':
            return ''

        case 'total_spent':
            return operator === 'between' ? '0,10000000' : '1000000'

        case 'membership_duration':
            return operator === 'between' ? '1,12' : '1'

        case 'favorite_services':
            return ''

        default:
            return ''
    }
}

const watchFieldChanges = (condition) => {
    watch(() => condition.field, (newField) => {
        const actualField = typeof newField === 'object' ? newField.value : newField
        
        console.log('Field changed:', {
            actualField,
            currentOperator: condition.operator,
            currentValue: condition.value
        })
        
        if (actualField) {
            const availableOperators = operators[actualField] || []
            condition.operator = availableOperators.length > 0 ? availableOperators[0].value : ''
            condition.value = getDefaultValueForField(actualField, condition.operator)
            
            console.log('After field change:', {
                field: actualField,
                newOperator: condition.operator,
                newValue: condition.value
            })
        } else {
            condition.operator = ''
            condition.value = ''
        }
    })

    watch(() => condition.operator, (newOperator, oldOperator) => {
        const actualField = typeof condition.field === 'object' ? condition.field.value : condition.field
        const actualNewOperator = typeof newOperator === 'object' ? newOperator.value : newOperator
        const actualOldOperator = typeof oldOperator === 'object' ? oldOperator.value : oldOperator
        
        console.log('Operator changed:', {
            field: actualField,
            oldOperator: actualOldOperator,
            newOperator: actualNewOperator,
            currentValue: condition.value
        })

        const isValid = validateConditionValue({
            field: actualField,
            operator: actualNewOperator,
            value: condition.value
        })

        console.log('Validation result:', {
            isValid,
            currentValue: condition.value
        })

        if (actualNewOperator !== actualOldOperator || !isValid) {
            const defaultValue = getDefaultValueForField(actualField, actualNewOperator)
            console.log('Setting default value:', {
                from: condition.value,
                to: defaultValue,
                reason: actualNewOperator !== actualOldOperator ? 'operator changed' : 'invalid value'
            })
            condition.value = defaultValue
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
    const field = typeof condition.field === 'object' ? condition.field.value : condition.field
    const operator = typeof condition.operator === 'object' ? condition.operator.value : condition.operator
    const value = condition.value
    
    console.log('Validating condition:', { field, operator, value })
    
    if (!value && value !== false) {
        console.log('Validation failed: empty value')
        return false
    }

    let isValid = false
    
    switch (field) {
        case 'loyalty_points':
        case 'purchase_count':
        case 'total_spent':
            if (operator === 'between') {
                const [min, max] = value.split(',').map(Number)
                isValid = !isNaN(min) && !isNaN(max) && min <= max
            } else {
                isValid = !isNaN(value) && Number(value) >= 0
            }
            break;
            
        case 'last_visit':
            if (operator === 'never') {
                isValid = true
            } else {
                isValid = !isNaN(value) && Number(value) > 0
            }
            break;

        case 'gender':
            isValid = ['male', 'female', 'other'].includes(value)
            break;

        case 'skin_condition':
            isValid = value.length > 0
            break;

        case 'membership_duration':
            if (operator === 'between') {
                const [min, max] = value.split(',').map(Number)
                isValid = !isNaN(min) && !isNaN(max) && min <= max && min > 0
            }
            isValid = !isNaN(value) && Number(value) > 0
            break;

        case 'favorite_services':
            isValid = value.length > 0
            break;

        default:
            isValid = true
    }
    
    console.log('Validation result:', { isValid, field, operator, value })
    return isValid
}

const isFormValid = computed(() => {
    return form.value.name &&
        form.value.conditions.every(c =>
            c.field &&
            c.operator &&
            validateConditionValue(c)
        )
})

const getOperatorsForField = computed(() => (field) => {
    const fieldValue = typeof field === 'object' ? field.value : field
    return operators[fieldValue] || []
})

const filteredGroups = computed(() => {
    let result = groups.value

    if (search.value) {
        result = result.filter(group =>
            group.name.toLowerCase().includes(search.value.toLowerCase()) ||
            group.description?.toLowerCase().includes(search.value.toLowerCase())
        )
    }

    if (filter.value !== 'all') {
        result = result.filter(group =>
            filter.value === 'active' ? group.is_active : !group.is_active
        )
    }

    return result
})

const syncGroup = async (groupId) => {
    try {
        syncingGroup.value = groupId
        await axios.post(`/api/user-groups/${groupId}/sync`)
        await fetchGroups()
        toast.success('Đồng bộ nhóm thành công')
    } catch (error) {
        toast.error('Lỗi khi đồng bộ nhóm: ' + error.message)
    } finally {
        syncingGroup.value = null
    }
}

const formatCondition = (condition) => {
    const field = availableFields.find(f => f.value === condition.field)?.label
    const operator = operators[condition.field]?.find(o => o.value === condition.operator)?.label
    return `${field} ${operator} ${condition.value}`
}

const statsChartData = computed(() => {
    if (!selectedGroupStats.value) return {
        gender: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: []
            }]
        },
        age: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: []
            }]
        }
    }

    return {
        gender: {
            labels: ['Nam', 'Nữ', 'Khác'],
            datasets: [{
                data: [
                    selectedGroupStats.value.gender_stats?.male || 0,
                    selectedGroupStats.value.gender_stats?.female || 0,
                    selectedGroupStats.value.gender_stats?.other || 0
                ],
                backgroundColor: ['#4CAF50', '#2196F3', '#9C27B0']
            }]
        },
        age: {
            labels: ['18-25', '26-35', '36-45', '46-55', '55+'],
            datasets: [{
                data: [
                    selectedGroupStats.value.age_stats?.['18-25'] || 0,
                    selectedGroupStats.value.age_stats?.['26-35'] || 0,
                    selectedGroupStats.value.age_stats?.['36-45'] || 0,
                    selectedGroupStats.value.age_stats?.['46-55'] || 0,
                    selectedGroupStats.value.age_stats?.['55+'] || 0
                ],
                backgroundColor: '#4CAF50'
            }]
        }
    }
})

const getInputType = (field, operator) => {
    if (!field || !operator) return 'text'
    
    if (operator === 'between') {
        return 'text'
    }

    switch (field) {
        case 'gender':
            return 'select'
        case 'loyalty_points':
        case 'purchase_count':
        case 'age':
        case 'total_spent':
        case 'membership_duration':
            return 'number'
        case 'last_visit':
            return operator === 'never' ? 'checkbox' : 'number'
        default:
            return 'text'
    }
}

const getPlaceholder = (field, operator) => {
    if (operator === 'between') {
        switch (field) {
            case 'age':
                return 'Ví dụ: 18,60'
            case 'loyalty_points':
                return 'Ví dụ: 0,1000'
            case 'total_spent':
                return 'Ví dụ: 0,10000000'
            default:
                return 'Ví dụ: min,max'
        }
    }

    switch (field) {
        case 'loyalty_points':
            return 'Nhập số điểm'
        case 'purchase_count':
            return 'Nhập số lần mua'
        case 'last_visit':
            return operator === 'never' ? '' : 'Nhập số ngày'
        case 'age':
            return 'Nhập tuổi'
        case 'total_spent':
            return 'Nhập số tiền'
        case 'membership_duration':
            return 'Nhập số tháng'
        case 'skin_condition':
        case 'favorite_services':
            return 'Nhập giá trị'
        default:
            return ''
    }
}

const getOptionsForField = (field, operator) => {
    switch (field) {
        case 'gender':
            return [
                { value: 'male', label: 'Nam' },
                { value: 'female', label: 'Nữ' },
                { value: 'other', label: 'Khác' }
            ]
        case 'skin_condition':
            return [
                { value: 'dry', label: 'Da khô' },
                { value: 'oily', label: 'Da dầu' },
                { value: 'combination', label: 'Da hỗn hợp' },
                { value: 'sensitive', label: 'Da nhạy cảm' }
            ]
        default:
            return []
    }
}

onMounted(() => {
    fetchGroups()
    form.value.conditions.forEach(condition => {
        watchFieldChanges(condition)
    })
})
</script>

<template>
    <div class="space-y-6">
        <!-- Thêm nút tạo nhóm mới -->
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <FormControl v-model="search" type="search" placeholder="Tìm kiếm nhóm..." class="max-w-xs" />
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Hiển thị:</span>
                    <FormControl v-model="filter" type="select" :options="[
                        { value: 'all', label: 'Tất cả' },
                        { value: 'active', label: 'Đang hoạt động' },
                        { value: 'inactive', label: 'Không hoạt động' }
                    ]" class="w-40" />
                </div>
            </div>
            <BaseButton color="info" :icon="mdiPlus" @click="showCreateForm = true">
                Tạo nhóm mới
            </BaseButton>
        </div>

        <!-- Form tạo/chỉnh sửa nhóm -->
        <CardBoxModal v-model="showCreateForm" :title="editingGroup ? 'Chỉnh sửa nhóm' : 'Tạo nhóm mới'"
            @submit="saveGroup" :button="editingGroup ? 'warning' : 'info'" :has-cancel="true">
            <div class="space-y-4">
                <!-- Tên nhóm -->
                <FormField label="Tên nhóm">
                    <FormControl v-model="form.name" type="text" placeholder="Nhập tên nhóm" required />
                </FormField>

                <!-- Mô tả -->
                <FormField label="Mô tả">
                    <FormControl v-model="form.description" type="textarea" placeholder="Nhập mô tả nhóm" />
                </FormField>

                <!-- Điều kiện -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="font-medium">Điều kiện</h4>
                        <BaseButton color="info" :icon="mdiPlus" small @click="addCondition">
                            Thêm điều kiện
                        </BaseButton>
                    </div>

                    <div v-for="(condition, index) in form.conditions" :key="index" class="flex gap-4">
                        <!-- Trường -->
                        <FormControl v-model="condition.field" type="select" :options="availableFields"
                            class="flex-1" />

                        <!-- Toán tử -->
                        <FormControl v-model="condition.operator" type="select"
                            :options="getOperatorsForField(condition.field)" class="flex-1" />

                        <!-- Giá trị -->
                        <div class="flex gap-2 flex-1">
                            <template v-if="condition.operator === 'between'">
                                <FormControl v-model="condition.value" type="text" placeholder="Ví dụ: 0,100"
                                    class="flex-1" />
                            </template>
                            <template v-else-if="getInputType(condition.field, condition.operator) === 'select'">
                                <FormControl v-model="condition.value" type="select"
                                    :options="getOptionsForField(condition.field)" class="flex-1" />
                            </template>
                            <template v-else-if="condition.field === 'last_visit' && condition.operator === 'never'">
                                <FormControl v-model="condition.value" type="checkbox" class="flex-1" />
                            </template>
                            <template v-else>
                                <FormControl
                                    v-model="condition.value"
                                    :type="getInputType(condition.field, condition.operator)"
                                    :placeholder="getPlaceholder(condition.field, condition.operator)"
                                    class="flex-1"
                                />
                            </template>
                            <BaseButton v-if="form.conditions.length > 1" color="danger" :icon="mdiClose" small
                                @click="removeCondition(index)" />
                        </div>
                    </div>
                </div>
            </div>
        </CardBoxModal>
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            <CardBox v-for="group in filteredGroups" :key="group.id"
                class="relative hover:shadow-lg transition-shadow duration-200"
                :class="{ 'opacity-75': !group.is_active }">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-semibold mb-1">{{ group.name }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ group.description }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span :class="[
                            'px-2 py-1 text-xs font-medium rounded-full',
                            group.is_active
                                ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100'
                                : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-100'
                        ]">
                            {{ group.is_active ? 'Đang hoạt động' : 'Không hoạt động' }}
                        </span>
                    </div>
                </div>

                <!-- Thống kê và điều kiện -->
                <div class="mt-4 grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <div class="text-sm font-medium">Điều kiện:</div>
                        <div v-for="condition in group.conditions" :key="condition.id"
                            class="text-sm p-2 rounded-md bg-gray-50 dark:bg-dark-surface-hover">
                            {{ formatCondition(condition) }}
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="text-sm font-medium">Thông tin:</div>
                        <div class="text-sm">
                            <span class="text-gray-600 dark:text-gray-400">Số thành viên:</span>
                            <span class="font-medium ml-1">{{ group.user_count }}</span>
                        </div>
                        <div class="text-sm">
                            <span class="text-gray-600 dark:text-gray-400">Đồng bộ lần cuối:</span>
                            <span class="font-medium ml-1">{{ group.last_sync_at }}</span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-4 flex justify-end gap-2">
                    <BaseButton color="info" :icon="mdiChartBox" small @click="showGroupStats(group.id)"
                        title="Xem thống kê" />
                    <BaseButton color="success" :icon="mdiSync" small @click="syncGroup(group.id)"
                        :loading="syncingGroup === group.id" title="Đồng bộ nhóm" />
                    <BaseButton color="warning" :icon="mdiPencil" small @click="editGroup(group)" title="Chỉnh sửa" />
                    <BaseButton color="danger" :icon="mdiDelete" small @click="deleteGroup(group.id)" title="Xóa" />
                </div>
            </CardBox>
        </div>

        <!-- Modal thống kê -->
        <CardBoxModal v-model="showStats" title="Thống kê nhóm" button="info" :has-cancel="true">
            <div v-if="selectedGroupStats" class="grid grid-cols-2 gap-6">
                <!-- Biểu đồ phân bố giới tính -->
                <div class="col-span-1">
                    <PieChart :data="statsChartData.gender" title="Phân bố giới tính" />
                </div>

                <!-- Biểu đồ phân bố độ tuổi -->
                <div class="col-span-1">
                    <BarChart :data="statsChartData.age" title="Phân bố độ tuổi" />
                </div>

                <!-- Thông tin chi tiết -->
                <div class="col-span-2 grid grid-cols-3 gap-4">
                    <StatCard title="Tổng thành viên" :value="selectedGroupStats?.total_users || 0"
                        icon="mdiAccountGroup" />
                    <StatCard title="Điểm trung bình" :value="selectedGroupStats?.loyalty_points_avg || 0"
                        icon="mdiStar" />
                    <StatCard title="Lượt mua trung bình" :value="selectedGroupStats?.purchase_count_avg || 0"
                        icon="mdiCart" />
                </div>
            </div>
            <div v-else class="flex justify-center items-center p-4">
                <span class="text-gray-500">Đang tải dữ liệu...</span>
            </div>
        </CardBoxModal>
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