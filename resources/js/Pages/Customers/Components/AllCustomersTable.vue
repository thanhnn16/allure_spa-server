<script setup>
import { ref, computed, onMounted } from 'vue'
import { useMainStore } from '@/Stores/main.js'
import { mdiEye, mdiTrashCan } from '@mdi/js'
import CardBoxModal from '@/Components/CardBoxModal.vue'
import TableCheckboxCell from '@/Components/TableCheckboxCell.vue'
import BaseLevel from '@/Components/BaseLevel.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import BaseButton from '@/Components/BaseButton.vue'
import UserAvatar from '@/Components/UserAvatar.vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  users: {
    type: Array,
    default: () => []
  },
  checkable: {
    type: Boolean,
    default: false
  }
})

console.log('AllCustomersTable - Users:', props.users)

onMounted(() => {
  console.log('AllCustomersTable - Users (mounted):', props.users)
})

const hasItems = computed(() => {
  console.log('AllCustomersTable - hasItems:', props.users.length > 0)
  return props.users.length > 0
})

const usersData = computed(() => props.users || [])

const isModalDangerActive = ref(false)

const checked = (isChecked, user) => {
  // Xử lý logic khi checkbox được chọn
}

const formatStatus = (user) => {
  return user.deleted_at ? 'Đã xóa' : 'Hoạt động'
}
</script>

<template>
  <CardBoxModal v-model="isModalDangerActive" title="Xác nhận" button="danger" has-cancel>
    <p>Bạn có chắc chắn muốn xóa khách hàng này?</p>
  </CardBoxModal>

  <div v-if="!hasItems" class="text-center py-5">
    <p>Không có dữ liệu</p>
  </div>

  <table v-else>
    <thead>
      <tr>
        <th v-if="checkable" />
        <th />
        <th>Họ và tên</th>
        <th>Số điện thoại</th>
        <th>Ngày sinh</th>
        <th>Ghi chú</th>
        <th>Trạng thái</th>
        <th>Hành động</th>
        <th />
      </tr>
    </thead>
    <tbody>
      <tr v-for="user in users" :key="user.id">
        <TableCheckboxCell v-if="checkable" @checked="checked($event, user)" />
        <td class="border-b-0 lg:w-6 before:hidden">
          <UserAvatar :fullName="user.full_name || ''" class="w-24 h-24 mx-auto lg:w-6 lg:h-6" />
        </td>
        <td data-label="Họ và tên">
          {{ user.full_name }}
        </td>
        <td data-label="Số điện thoại">
          {{ user.phone_number }}
        </td>
        <td data-label="Ngày sinh">
          {{ user.date_of_birth || 'Chưa cập nhật' }}
        </td>
        <td data-label="Ghi chú" class="lg:w-32">
          {{ user.note || 'Không có ghi chú' }}
        </td>
        <td data-label="Trạng thái">
          {{ formatStatus(user) }}
        </td>
        <td data-label="Hành động" class="lg:w-1 whitespace-nowrap">
          <div class="flex items-center justify-start lg:justify-end">
            <Link :href="route('users.show', user.id)">
              <BaseButton color="info" :icon="mdiEye" small />
            </Link>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</template>
