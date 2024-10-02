<script setup>
import { containerMaxW } from '@/config.js'
import BaseLevel from '@/Components/BaseLevel.vue'
import AllureLogo from '@/Components/AllureLogo.vue';

const year = new Date().getFullYear()
</script>

<template>
  <footer class="py-2 px-6" :class="containerMaxW">
    <BaseLevel>
      <div class="text-center md:text-left">
        <b>Copyright &copy; {{ year }} by Allure Spa.</b>
        <slot />
      </div>
      <div class="md:py-2">
        <a href="https://allurespa.com.vn" target="_blank">
          <AllureLogo class="w-auto h-8 md:h-6" />
        </a>
      </div>
    </BaseLevel>
  </footer>
</template>
