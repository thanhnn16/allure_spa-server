<script setup>
import { mdiMonitorCellphone, mdiTableBorder, mdiTableOff, mdiGithub, mdiAccountPlus, mdiCalendarAccount, mdiTableAccount } from '@mdi/js'
import SectionMain from '@/Components/SectionMain.vue'
import NotificationBar from '@/Components/NotificationBar.vue'
import AllCustomersTable from '@/Pages/Customers/Components/AllCustomersTable.vue'
import VipCustomersTable from '@/Pages/Customers/Components/VipCustomersTable.vue'
import CardBox from '@/Components/CardBox.vue'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import BaseButton from '@/Components/BaseButton.vue'
import CardBoxComponentEmpty from '@/Components/CardBoxComponentEmpty.vue'
import { Head } from "@inertiajs/vue3";
import { computed, ref } from 'vue'

const props = defineProps({
    users: Array
})

const hasUsers = computed(() => props.users && props.users.length > 0)

</script>

<template>
    <LayoutAuthenticated>

        <Head title="Quản lý khách hàng" />
        <SectionMain>
            <SectionTitleLineWithButton :icon="mdiTableAccount" title="Danh sách khách hàng" main>
                <BaseButton route-name="users.create" :icon="mdiAccountPlus" label="Thêm khách hàng mới" rounded-full
                    small />
            </SectionTitleLineWithButton>
            <NotificationBar color="info" :icon="mdiCalendarAccount">
                Có<b> 0 </b>khách hàng có sinh nhật <b>trong 15 ngày tới</b>.
                Xem ngay!
            </NotificationBar>

            <CardBox v-if="hasUsers" class="mb-6" has-table>
                <AllCustomersTable checkable :users="users" />
            </CardBox>

            <CardBox v-else class="mb-6">
                <div class="text-center py-5">
                    <p>Không có khách hàng nào</p>
                </div>
            </CardBox>

            <SectionTitleLineWithButton :icon="mdiTableAccount" title="Khách hàng VIP" />

            <CardBox v-if="hasUsers" class="mb-6" has-table>
                <VipCustomersTable checkable :users="users" />
            </CardBox>

            <CardBox v-else class="mb-6">
                <div class="text-center py-5">
                    <p>Không có khách hàng nào</p>
                </div>
            </CardBox>

        </SectionMain>
    </LayoutAuthenticated>
</template>
