<script setup>
import { computed } from 'vue'
import { mdiEye, mdiTrashCan } from '@mdi/js'
import BaseButtons from '@/Components/BaseButtons.vue'
import BaseButton from '@/Components/BaseButton.vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    items: {
        type: Array,
        default: () => []
    },
    checkable: Boolean
})

const deleteStaff = (id) => {
    if (confirm('Bạn có chắc chắn muốn xóa nhân viên này?')) {
        router.delete(route('staff.destroy', id), {
            preserveScroll: true,
            onSuccess: () => {
                // Reload the page after successful deletion
                router.reload()
            }
        })
    }
}
</script>

<template>
    <table>
        <thead>
            <tr>
                <th>Họ và tên</th>
                <th>Số điện thoại</th>
                <th>Email</th>
                <th>Chức vụ</th>
                <th>Phòng ban</th>
                <th>Ngày vào làm</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="staff in items" :key="staff.id">
                <td data-label="Họ và tên">{{ staff.full_name }}</td>
                <td data-label="Số điện thoại">{{ staff.phone_number }}</td>
                <td data-label="Email">{{ staff.email }}</td>
                <td data-label="Chức vụ">{{ staff.staff_detail?.position }}</td>
                <td data-label="Phòng ban">{{ staff.staff_detail?.department }}</td>
                <td data-label="Ngày vào làm">{{ staff.staff_detail?.hire_date }}</td>
                <td class="before:hidden lg:w-1 whitespace-nowrap">
                    <BaseButtons type="justify-start lg:justify-end" no-wrap>
                        <BaseButton color="info" :icon="mdiEye" small @click="router.get(route('staff.show', staff.id))" />
                        <BaseButton color="danger" :icon="mdiTrashCan" small @click="deleteStaff(staff.id)" />
                    </BaseButtons>
                </td>
            </tr>
        </tbody>
    </table>
</template> 