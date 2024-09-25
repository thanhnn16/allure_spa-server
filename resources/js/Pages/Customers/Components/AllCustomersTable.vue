<script setup>
import { computed, ref } from 'vue'
import { useMainStore } from '@/Stores/main.js'
import { mdiEye, mdiTrashCan } from '@mdi/js'
import CardBoxModal from '@/Components/CardBoxModal.vue'
import TableCheckboxCell from '@/Components/TableCheckboxCell.vue'
import BaseLevel from '@/Components/BaseLevel.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import BaseButton from '@/Components/BaseButton.vue'
import UserAvatar from '@/Components/UserAvatar.vue'

const props = defineProps({
  checkable: Boolean,
  users: Array
})

const items = computed(() => props.users || [])

const isModalActive = ref(false)

const isModalDangerActive = ref(false)

const perPage = ref(5)

const currentPage = ref(0)

const checkedRows = ref([])

const itemsPaginated = computed(() =>
  items.value.slice(perPage.value * currentPage.value, perPage.value * (currentPage.value + 1))
)

const numPages = computed(() => Math.ceil(items.value.length / perPage.value))

const currentPageHuman = computed(() => currentPage.value + 1)

const pagesList = computed(() => {
  const pagesList = []

  for (let i = 0; i < numPages.value; i++) {
    pagesList.push(i)
  }

  return pagesList
})

const remove = (arr, cb) => {
  const newArr = []

  arr.forEach((item) => {
    if (!cb(item)) {
      newArr.push(item)
    }
  })

  return newArr
}

const checked = (isChecked, client) => {
  if (isChecked) {
    checkedRows.value.push(client)
  } else {
    checkedRows.value = remove(checkedRows.value, (row) => row.id === client.id)
  }
}

const hasItems = computed(() => items.value.length > 0)
</script>

<template>
  <CardBoxModal v-model="isModalActive" title="Chi tiết khách hàng">
    <!-- <p>Họ và tên: {{ users.full_name }}</p>
    <p>Số điện thoại: {{ users.phone }}</p>
    <p>Ngày sinh: {{ users.birthday }}</p>
    <p>Ghi chú: {{ users.note }}</p>
    <p>Ngày gia nhập: {{ users.created }}</p> -->
  </CardBoxModal>

  <CardBoxModal v-model="isModalDangerActive" title="Xác nhận" button="danger" has-cancel>
    <p>Bạn có chắc chắn muốn xóa khách hàng này?</p>
  </CardBoxModal>

  <div v-if="!hasItems" class="text-center py-5">
    <p>Không có dữ liệu</p>
  </div>

  <template v-else>
    <table>
      <thead>
        <tr>
          <th v-if="checkable" />
          <th />
          <th>Họ và tên</th>
          <th>Số điện thoại</th>
          <th>Ngày sinh</th>
          <th>Ghi chú</th>
          <th>Hành động</th>
          <th />
        </tr>
      </thead>
      <tbody>
        <tr v-for="client in itemsPaginated" :key="client.id">
          <TableCheckboxCell v-if="checkable" @checked="checked($event, client)" />
          <td class="border-b-0 lg:w-6 before:hidden">
            <UserAvatar :username="client.name" class="w-24 h-24 mx-auto lg:w-6 lg:h-6" />
          </td>
          <td data-label="Họ và tên">
            {{ client.name }}
          </td>
          <td data-label="Số điện thoại">
            {{ client.company }}
          </td>
          <td data-label="Ngày sinh">
            {{ client.city }}
          </td>
          <td data-label="Ghi chú" class="lg:w-32">
            <progress class="flex w-2/5 self-center lg:w-full" max="100" :value="client.progress">
              {{ client.progress }}
            </progress>
          </td>
          <td data-label="Hành động" class="lg:w-1 whitespace-nowrap">
            <small class="text-gray-500 dark:text-slate-400" :title="client.created">{{
              client.created
              }}</small>
          </td>
          <td class="before:hidden lg:w-1 whitespace-nowrap">
            <BaseButtons type="justify-start lg:justify-end" no-wrap>
              <BaseButton color="info" :icon="mdiEye" small @click="isModalActive = true" />
              <BaseButton color="danger" :icon="mdiTrashCan" small @click="isModalDangerActive = true" />
            </BaseButtons>
          </td>
        </tr>
      </tbody>
    </table>
    <div v-if="hasItems" class="p-3 lg:px-6 border-t border-gray-100 dark:border-slate-800">
      <BaseLevel>
        <BaseButtons>
          <BaseButton v-for="page in pagesList" :key="page" :active="page === currentPage" :label="page + 1"
            :color="page === currentPage ? 'lightDark' : 'whiteDark'" small @click="currentPage = page" />
        </BaseButtons>
        <small>Trang {{ currentPageHuman }} / {{ numPages }}</small>
      </BaseLevel>
    </div>
  </template>
</template>
