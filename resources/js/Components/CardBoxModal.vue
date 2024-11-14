<script setup>
import { computed } from 'vue'
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
</script>

<template>
  <OverlayLayer v-show="value" @overlay-click="cancel">
    <CardBox v-show="value" class="shadow-lg w-11/12 md:w-3/5 lg:w-2/5 xl:w-4/12 z-50" is-modal>
      <div class="flex flex-col h-full">
        <!-- Header -->
        <CardBoxComponentTitle :title="title" class="sticky top-0 z-10">
          <BaseButton v-if="hasCancel" :icon="mdiClose" color="whiteDark" small rounded-full @click.prevent="cancel" />
        </CardBoxComponentTitle>

        <!-- Content -->
        <div class="flex-grow overflow-y-auto">
          <div class="p-5">
            <slot />
          </div>
        </div>

        <!-- Footer -->
        <footer class="p-5 border-t dark:border-dark-border">
          <BaseButtons class="justify-end gap-3">
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
  /* Firefox */
  -ms-overflow-style: none;
  /* IE and Edge */
}

.custom-scrollbar::-webkit-scrollbar {
  display: none;
  /* Chrome, Safari and Opera */
}

/* Sticky header and footer */
.sticky {
  position: sticky;
}

/* Card styling */
:deep(.card) {
  border-radius: 0.5rem;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

/* Button spacing */
.gap-3 {
  gap: 0.75rem;
}
</style>
