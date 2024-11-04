<script setup>
import { reactive, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import { useMainStore } from '@/Stores/main'
import {
  mdiAccount,
  mdiMail,
  mdiAsterisk,
  mdiFormTextboxPassword,
  mdiPhone,
  mdiGenderMaleFemale,
  mdiCalendar,
  mdiPencil,
  mdiAccountTie,
  mdiClockOutline,
  mdiUpload
} from '@mdi/js'
import SectionMain from '@/Components/SectionMain.vue'
import CardBox from '@/Components/CardBox.vue'
import BaseDivider from '@/Components/BaseDivider.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import FormFilePicker from '@/Components/FormFilePicker.vue'
import BaseButton from '@/Components/BaseButton.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import UserCard from '@/Components/UserCard.vue'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const mainStore = useMainStore()
const toast = useToast()

const profileForm = reactive({
  full_name: '',
  email: '',
  phone_number: '',
  gender: '',
  date_of_birth: '',
  skin_condition: '',
  avatar: null
})

const passwordForm = reactive({
  password_current: '',
  password: '',
  password_confirmation: ''
})

const updateFormFromStore = () => {
  const user = mainStore.user
  profileForm.full_name = user.full_name
  profileForm.email = user.email
  profileForm.phone_number = user.phone_number
  profileForm.gender = user.gender
  profileForm.date_of_birth = user.date_of_birth
  profileForm.skin_condition = user.skin_condition
  profileForm.avatar = user.avatar_url
}

const submitProfile = () => {
  const formData = new FormData()
  Object.keys(profileForm).forEach(key => {
    if (profileForm[key] !== null) {
      formData.append(key, profileForm[key])
    }
  })

  axios.post(`/api/users/${mainStore.user.id}`, formData, {
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  })
    .then(response => {
      mainStore.setUser(response.data.data)
      toast.success('Cập nhật thông tin thành công')
    })
    .catch(error => {
      toast.error('Có lỗi xảy ra khi cập nhật thông tin')
    })
}

const handleAvatarUpload = async (event) => {
  const file = event.target.files[0]
  if (!file) return

  // Kiểm tra kích thước file (giới hạn 2MB)
  if (file.size > 2 * 1024 * 1024) {
    toast.error('Kích thước file không được vượt quá 2MB')
    return
  }

  // Kiểm tra định dạng file
  const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg']
  if (!allowedTypes.includes(file.type)) {
    toast.error('Chỉ chấp nhận file ảnh định dạng JPG, PNG hoặc GIF')
    return
  }

  const formData = new FormData()
  formData.append('avatar', file)

  try {
    toast.info('Đang tải ảnh lên...')

    const response = await axios.post('/api/users/upload-avatar', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
        'Accept': 'application/json'
      }
    })

    if (response.data.success) {
      mainStore.setUser(response.data.data.user)
      profileForm.avatar = response.data.data.avatar_url
      event.target.value = ''
      toast.success('Tải ảnh đại diện thành công')
    }
  } catch (error) {
    console.error('Upload error:', error)
    const errorMessage = error.response?.data?.message || 'Không thể tải lên ảnh đại diện'
    toast.error(errorMessage)
  }
}

const submitPass = () => {
  axios.post('/api/change-password', passwordForm)
    .then(response => {
      passwordForm.password_current = ''
      passwordForm.password = ''
      passwordForm.password_confirmation = ''
    })
    .catch(error => {
      // Xử lý lỗi
    })
}

// Load user info when component mounted
onMounted(async () => {
  await mainStore.fetchUserInfo()
  updateFormFromStore()
})
</script>

<template>
  <LayoutAuthenticated>

    <Head title="Thông tin cá nhân" />
    <SectionMain>
      <SectionTitleLineWithButton :icon="mdiAccount" title="Thông tin cá nhân" main>
        <BaseButton :icon="mdiUpload" label="Tải lên ảnh đại diện" color="info" rounded-full small
          @click="$refs.avatarInput.click()" />
        <input ref="avatarInput" type="file" accept="image/*" class="hidden" @change="handleAvatarUpload" />
      </SectionTitleLineWithButton>

      <UserCard class="mb-6" :userData="mainStore.user" />

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <CardBox is-form @submit.prevent="submitProfile">
          <FormField label="Họ và tên" help="Bắt buộc">
            <FormControl v-model="profileForm.full_name" :icon="mdiAccount" name="full_name" required />
          </FormField>

          <FormField label="Email">
            <FormControl v-model="profileForm.email" :icon="mdiMail" type="email" name="email" autocomplete="email" />
          </FormField>

          <FormField label="Số điện thoại">
            <FormControl v-model="profileForm.phone_number" :icon="mdiPhone" name="phone_number" />
          </FormField>

          <FormField label="Giới tính">
            <FormControl v-model="profileForm.gender" :icon="mdiGenderMaleFemale" type="select" :options="[
              { value: 'male', label: 'Nam' },
              { value: 'female', label: 'Nữ' },
              { value: 'other', label: 'Khác' }
            ]" name="gender" />
          </FormField>

          <FormField label="Ngày sinh">
            <FormControl v-model="profileForm.date_of_birth" :icon="mdiCalendar" type="date" name="date_of_birth" />
          </FormField>

          <FormField label="Tình trạng da">
            <FormControl v-model="profileForm.skin_condition" name="skin_condition" />
          </FormField>

          <template #footer>
            <BaseButtons>
              <BaseButton color="info" type="submit" label="Cập nhật" />
            </BaseButtons>
          </template>
        </CardBox>

        <CardBox is-form @submit.prevent="submitPass">
          <FormField label="Mật khẩu hiện tại" help="Bắt buộc nhập mật khẩu hiện tại">
            <FormControl v-model="passwordForm.password_current" :icon="mdiAsterisk" name="password_current"
              type="password" required autocomplete="current-password" />
          </FormField>

          <BaseDivider />

          <FormField label="Mật khẩu mới" help="Bắt buộc nhập mật khẩu mới">
            <FormControl v-model="passwordForm.password" :icon="mdiFormTextboxPassword" name="password" type="password"
              required autocomplete="new-password" />
          </FormField>

          <FormField label="Xác nhận mật khẩu" help="Nhập lại mật khẩu mới">
            <FormControl v-model="passwordForm.password_confirmation" :icon="mdiFormTextboxPassword"
              name="password_confirmation" type="password" required autocomplete="new-password" />
          </FormField>

          <template #footer>
            <BaseButtons>
              <BaseButton type="submit" color="info" label="Đổi mật khẩu" />
            </BaseButtons>
          </template>
        </CardBox>
      </div>
    </SectionMain>
  </LayoutAuthenticated>
</template>
