import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export const useMainStore = defineStore('main', () => {
  const user = ref({
    full_name: '',
    email: '',
    avatar_url: '',
    role: '',
    phone_number: '',
    gender: '',
    date_of_birth: '',
    skin_condition: ''
  })

  const fullName = computed(() => user.value.full_name)
  const avatarUrl = computed(() => user.value.avatar_url ||
    `https://api.dicebear.com/7.x/avataaars/svg?seed=${user.value.email.replace(/[^a-z0-9]+/gi, '-')}`)

  function setUser(payload) {
    user.value = {
      ...user.value,
      ...payload
    }
  }

  // Lấy thông tin user từ API
  async function fetchUserInfo() {
    try {
      const response = await axios.get('/api/user/info')
      if (response.data.success) {
        console.log(response.data.data)
        setUser(response.data.data)
      }
    } catch (error) {
      console.error('Error fetching user info:', error)
    }
  }

  return {
    user,
    fullName,
    avatarUrl,
    setUser,
    fetchUserInfo
  }
})
