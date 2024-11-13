import { defineStore } from 'pinia'
import { ref, watch } from 'vue'

export const useLayoutStore = defineStore('layout', () => {
  // Khởi tạo state với giá trị từ localStorage nếu có
  const isDark = ref(localStorage.getItem('darkMode') === 'true')
  const isAsideLgActive = ref(localStorage.getItem('asideExpanded') === 'true')

  // Watch changes để lưu vào localStorage
  watch(isDark, (newValue) => {
    localStorage.setItem('darkMode', newValue)
    // Thêm/xóa class dark cho document
    if (newValue) {
      document.documentElement.classList.add('dark')
    } else {
      document.documentElement.classList.remove('dark')
    }
  })

  watch(isAsideLgActive, (newValue) => {
    localStorage.setItem('asideExpanded', newValue)
  })

  // Actions
  function toggleDarkMode() {
    isDark.value = !isDark.value
  }

  function toggleAside() {
    isAsideLgActive.value = !isAsideLgActive.value
  }

  // Khởi tạo darkMode khi load app
  function initDarkMode() {
    if (isDark.value) {
      document.documentElement.classList.add('dark')
    }
  }

  return {
    isDark,
    isAsideLgActive,
    toggleDarkMode,
    toggleAside,
    initDarkMode
  }
}) 