<script setup>
import { Head } from '@inertiajs/vue3'
import SectionMain from '@/Components/SectionMain.vue'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import CardBox from '@/Components/CardBox.vue'
import UserGroups from '../Partials/UserGroups.vue'

</script>

<template>
    <LayoutAuthenticated>
        <Head title="Quản lý nhóm người dùng" />
        <SectionMain>
            <CardBox class="mb-6">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold">Quản lý nhóm người dùng</h1>
                </div>
                
                <UserGroups />
            </CardBox>
        </SectionMain>
    </LayoutAuthenticated>
</template> 