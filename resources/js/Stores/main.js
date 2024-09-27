import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export const useMainStore = defineStore('main', () => {
  const userName = ref('')
  const userEmail = ref('')

  const userAvatar = computed(
    () =>
      `https://api.dicebear.com/7.x/avataaars/svg?seed=${userEmail.value.replace(
        /[^a-z0-9]+/gi,
        '-'
      )}`
  )

  const isFieldFocusRegistered = ref(false)
  const clients = ref([])
  const history = ref([])
  const users = ref([])

  function setUser(payload) {
    if (payload.full_name) {
      userName.value = payload.full_name
    }
    if (payload.email) {
      userEmail.value = payload.email
    }
  }

  function fetchSampleHistory() {
    axios
      .get(`data-sources/history.json`)
      .then((result) => {
        history.value = result?.data?.data
      })
      .catch((error) => {
        alert(error.message)
      })
  }

  function fetchUsers() {
    return axios.get(route('users.index'))
      .then((response) => {
        users.value = response.data.users || []
      })
      .catch((error) => {
        console.error('Error fetching users:', error)
        users.value = []
      })
  }

  return {
    userName,
    userEmail,
    userAvatar,
    isFieldFocusRegistered,
    history,
    users,
    setUser,
    fetchSampleHistory,
    fetchUsers
  }
})
