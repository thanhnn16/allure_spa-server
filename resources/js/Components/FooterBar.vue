<script setup>
import { containerMaxW } from '@/config.js'
import BaseLevel from '@/Components/BaseLevel.vue'
import AllureLogo from '@/Components/AllureLogo.vue';

const year = new Date().getFullYear()
</script>

<template>
  <footer class="py-2 px-6 fixed bottom-0 right-0 left-0 w-full">
    <div class="flex items-center justify-end gap-4">
      <div class="md:py-2">
        <a href="https://allurespa.com.vn" target="_blank">
          <AllureLogo class="w-auto h-8 md:h-6" />
        </a>
      </div>
    </div>
  </footer>
</template>
