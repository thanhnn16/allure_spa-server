<script setup>
import { computed, ref, onMounted, nextTick, watch } from 'vue'
import { mdiClose } from '@mdi/js'
import BaseButton from '@/Components/BaseButton.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import CardBox from '@/Components/CardBox.vue'
import OverlayLayer from '@/Components/OverlayLayer.vue'
import CardBoxComponentTitle from '@/Components/CardBoxComponentTitle.vue'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  button: {
    type: String,
    default: 'info'
  },
  buttonLabel: {
    type: String,
    default: 'Done'
  },
  hasCancel: Boolean,
  modelValue: {
    type: [String, Number, Boolean],
    default: null
  },
  hasButton: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['update:modelValue', 'cancel', 'confirm'])

const value = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const confirmCancel = (mode) => {
  value.value = false
  emit(mode)
}

const confirm = () => confirmCancel('confirm')
const cancel = () => confirmCancel('cancel')

window.addEventListener('keydown', (e) => {
  if (e.key === 'Escape' && value.value) {
    cancel()
  }
})

// Thêm biến để kiểm tra nội dung có vượt quá không
const isContentOverflowing = ref(false)

// Thêm hàm kiểm tra overflow
const checkContentOverflow = async () => {
  await nextTick()
  const modalContent = document.querySelector('.custom-scrollbar')
  if (modalContent) {
    isContentOverflowing.value = modalContent.scrollHeight > window.innerHeight * 0.9
  }
}

// Theo dõi khi modal được mở
watch(() => props.modelValue, async (newVal) => {
  if (newVal) {
    await checkContentOverflow()
  }
})

onMounted(() => {
  if (props.modelValue) {
    checkContentOverflow()
  }
})
</script>

<template>
  <OverlayLayer v-show="value" @overlay-click="cancel">
    <CardBox v-show="value" class="shadow-lg w-11/12 md:w-3/5 lg:w-2/5 xl:w-4/12 z-50 overflow-hidden" is-modal>
      <div class="flex flex-col" :class="{ 'h-[90vh]': isContentOverflowing }">
        <!-- Header -->
        <CardBoxComponentTitle :title="title">
          <BaseButton v-if="hasCancel" :icon="mdiClose" color="whiteDark" small rounded-full @click.prevent="cancel" />
        </CardBoxComponentTitle>

        <!-- Content -->
        <div class="overflow-y-auto custom-scrollbar" :class="{ 'flex-1': isContentOverflowing }">
          <div class="p-5">
            <slot />
          </div>
        </div>

        <!-- Footer -->
        <footer v-if="hasButton || $slots.footer"
          class="p-5 border-t dark:border-dark-border bg-white dark:bg-dark-surface">
          <BaseButtons v-if="hasButton" class="justify-end gap-3">
            <BaseButton v-if="hasCancel" label="Hủy" :color="button" outline @click="cancel" />
            <BaseButton :label="buttonLabel" :color="button" @click="confirm" />
          </BaseButtons>
          <slot name="footer" />
        </footer>
      </div>
    </CardBox>
  </OverlayLayer>
</template>

<style scoped>
.custom-scrollbar {
  scrollbar-width: none;
  -ms-overflow-style: none;
}

.custom-scrollbar::-webkit-scrollbar {
  display: none;
}

/* Đảm bảo modal không vượt quá chiều cao màn hình */
:deep(.card) {
  max-height: 90vh;
  display: flex;
  flex-direction: column;
}
</style>
