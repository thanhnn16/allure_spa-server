<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { getButtonColor } from '@/colors.js'
import AsideMenuList from '@/Components/AsideMenuList.vue'
import { useDarkModeStore } from '@/Stores/darkMode'
import BaseIcon from '@/Components/BaseIcon.vue'
import { mdiPlus, mdiMinus } from '@mdi/js'

const props = defineProps({
    item: {
        type: Object,
        required: true
    },
    isDropdownList: Boolean
})

const emit = defineEmits(['menu-click'])

const hasColor = computed(() => props.item && props.item.color)

const isDropdownActive = ref(false)

const componentClass = computed(() => [
    props.isDropdownList ? 'py-3 px-6 text-sm' : 'py-3 px-6',
    hasColor.value
        ? getButtonColor(props.item.color, false, true)
        : 'hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors duration-200',
    activeInactiveStyle.value
])

const itemHref = computed(() => (props.item.route ? route(props.item.route) : props.item.href))

const activeInactiveStyle = computed(() =>
    props.item.route && route().current(props.item.route)
        ? 'text-blue-600 dark:text-blue-400 font-medium bg-blue-50 dark:bg-slate-800/75'
        : 'text-gray-600 dark:text-slate-300'
)

const hasDropdown = computed(() => !!props.item.menu)

const menuClick = (event) => {
    emit('menu-click', event, props.item)

    if (hasDropdown.value) {
        isDropdownActive.value = !isDropdownActive.value
    }
}

const isActive = computed(() => 
    props.item.route && route().current(props.item.route)
)

</script>

<template>
    <li>
        <component :is="item.route ? Link : 'a'" :href="itemHref" :target="item.target ?? null"
            class="flex items-center cursor-pointer rounded-lg mx-3 transition-all duration-200" :class="[
                componentClass,
                'hover:translate-x-1',
                isDropdownList ? 'py-2 px-4 text-sm' : 'py-2.5 px-4'
            ]" @click="menuClick">
            <BaseIcon v-if="item.icon" :path="item.icon" class="flex-none transition-colors"
                :class="[activeInactiveStyle, isActive ? 'text-primary-500' : '']" w="w-5" :size="16" />
            <span class="grow text-ellipsis line-clamp-1 ml-3" :class="[{ 'pr-12': !hasDropdown }]">
                {{ item.label }}
            </span>
            <BaseIcon v-if="hasDropdown" :path="isDropdownActive ? mdiMinus : mdiPlus"
                class="flex-none w-4 transition-transform" :class="{ 'rotate-180': isDropdownActive }" />
        </component>
        <div v-if="hasDropdown" class="overflow-hidden transition-all duration-300"
            :class="[isDropdownActive ? 'max-h-screen' : 'max-h-0']">
            <AsideMenuList :menu="item.menu" class="pl-4 mt-2 border-l-2 border-gray-100 dark:border-dark-border"
                is-dropdown-list />
        </div>
    </li>
</template>
