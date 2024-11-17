import { defineStore } from 'pinia'
import { ref, watch } from 'vue'

export const useLayoutStore = defineStore('layout', () => {
  const isDark = ref(localStorage.getItem('darkMode') === 'true')
  const isAsideMobileExpanded = ref(false)

  watch(isDark, (newValue) => {
    localStorage.setItem('darkMode', newValue)
    if (newValue) {
      document.documentElement.classList.add('dark')
    } else {
      document.documentElement.classList.remove('dark')
    }
  })

  function toggleDarkMode() {
    isDark.value = !isDark.value
  }

  function toggleAside() {
    if (window.innerWidth < 1024) {
      isAsideMobileExpanded.value = !isAsideMobileExpanded.value
    }
  }

  // Khởi tạo darkMode khi load app
  function initDarkMode() {
    if (isDark.value) {
      document.documentElement.classList.add('dark')
    }
  }

  // Xử lý resize window
  window.addEventListener('resize', () => {
    if (window.innerWidth >= 1024) {
      isAsideMobileExpanded.value = false
    }
  })

  return {
    isDark,
    isAsideMobileExpanded,
    toggleDarkMode,
    toggleAside,
    initDarkMode
  }
}) 