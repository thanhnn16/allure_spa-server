<script setup>
import { ref, computed, onMounted, defineProps, watch } from 'vue'
import { mdiEye, mdiTrashCan } from '@mdi/js'
import CardBoxModal from '@/Components/CardBoxModal.vue'
import TableCheckboxCell from '@/Components/TableCheckboxCell.vue'
import BaseLevel from '@/Components/BaseLevel.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import BaseButton from '@/Components/BaseButton.vue'
import UserAvatar from '@/Components/UserAvatar.vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  items: {
    type: Array,
    default: () => []
  },
  checkable: {
    type: Boolean,
    default: false
  }
})

const items = ref([])

watch(() => props.items, (newItems) => {
  items.value = newItems
  console.log('CustomerTable - Items updated:', items.value)
}, { immediate: true, deep: true })

console.log('CustomerTable - Items:', items.value)

onMounted(() => {
  console.log('CustomerTable - Items (mounted):', items.value)
})

const hasItems = computed(() => {
  console.log('CustomerTable - hasItems:', items.value.length > 0)
  return items.value.length > 0
})

const isModalDangerActive = ref(false)

const checked = (isChecked, item) => {
  // Xử lý logic khi checkbox được chọn
}

const formatStatus = (item) => {
  return item.deleted_at ? 'Đã xóa' : 'Hoạt động'
}

const emit = defineEmits(['viewDetails'])

const viewDetails = (itemId) => {
  emit('viewDetails', itemId)
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
      <tr v-for="item in items" :key="item.id">
        <td v-if="checkable">
          <TableCheckboxCell @checked="checked($event, item)" />
        </td>
        <td class="border-b-0 lg:w-6 before:hidden">
          <UserAvatar :fullName="item.full_name || ''" class="w-24 h-24 mx-auto lg:w-6 lg:h-6" />
        </td>
        <td data-label="Họ và tên">
          {{ item.full_name }}
        </td>
        <td data-label="Số điện thoại">
          {{ item.phone_number }}
        </td>
        <td data-label="Ngày sinh">
          {{ item.date_of_birth || 'Chưa cập nhật' }}
        </td>
        <td data-label="Ghi chú" class="lg:w-32">
          {{ item.note || 'Không có ghi chú' }}
        </td>
        <td data-label="Trạng thái">
          {{ formatStatus(item) }}
        </td>
        <td data-label="Hành động" class="lg:w-1 whitespace-nowrap">
          <div class="flex items-center justify-start lg:justify-end">
            <BaseButtons type="justify-start lg:justify-end" no-wrap>
              <Link :href="route('users.show', item.id)" class="inline-flex">
                <BaseButton
                  color="info"
                  :icon="mdiEye"
                  small
                />
              </Link>
            </BaseButtons>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</template>
