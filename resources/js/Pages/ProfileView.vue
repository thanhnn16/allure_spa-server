<script setup>
import { reactive, onMounted, watch, computed } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
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

// Khai báo genderOptions trước khi sử dụng trong watch
const genderOptions = [
  { value: 'male', label: 'Nam' },
  { value: 'female', label: 'Nữ' },
  { value: 'other', label: 'Khác' }
]

// Khởi tạo form với giá trị mặc định
const profileForm = useForm({
  full_name: '',
  email: '',
  phone_number: '',
  gender: '',
  date_of_birth: '',
  skin_condition: '',
})

const passwordForm = reactive({
  password_current: '',
  password: '',
  password_confirmation: ''
})

const avatarForm = useForm({
  avatar: null
})

// Tách riêng hàm cập nhật form để tránh việc reset không mong muốn
const updateFormFromStore = () => {
  const user = mainStore.user
  if (!user) return

  // Cập nhật các trường cơ bản
  profileForm.full_name = user.full_name || ''
  profileForm.email = user.email || ''
  profileForm.phone_number = user.phone_number || ''
  profileForm.skin_condition = user.skin_condition || ''

  // Xử lý giới tính một lần duy nhất
  if (user.gender) {
    const genderOption = genderOptions.find(opt => opt.value === user.gender)
    if (genderOption && (!profileForm.gender || profileForm.gender.value !== user.gender)) {
      profileForm.gender = genderOption
    }
  }

  // Xử lý ngày sinh một lần duy nhất
  if (user.date_of_birth && (!profileForm.date_of_birth || profileForm.date_of_birth !== user.date_of_birth)) {
    const date = new Date(user.date_of_birth)
    profileForm.date_of_birth = date.toISOString().split('T')[0]
  }
}

const submitProfile = () => {
  const formData = {
    full_name: profileForm.full_name,
    email: profileForm.email,
    phone_number: profileForm.phone_number,
    gender: profileForm.gender?.value || profileForm.gender,
    date_of_birth: profileForm.date_of_birth,
    skin_condition: profileForm.skin_condition
  }

  console.log('Form data being submitted:', formData)

  axios.patch('/api/user/profile', formData)
    .then(response => {
      console.log('API response success:', response.data)
      toast.success('Cập nhật thông tin thành công')
      mainStore.fetchUserInfo()
      router.reload()
    })
    .catch(error => {
      console.error('API error:', {
        status: error.response?.status,
        data: error.response?.data,
        fullError: error
      })

      if (error.response?.data?.message) {
        if (typeof error.response.data.message === 'object') {
          Object.values(error.response.data.message).forEach(messages => {
            if (Array.isArray(messages)) {
              messages.forEach(message => {
                console.log('Validation error:', message)
                toast.error(message)
              })
            } else {
              console.log('Validation error:', messages)
              toast.error(messages)
            }
          })
        } else {
          console.log('API error message:', error.response.data.message)
          toast.error(error.response.data.message)
        }
      } else {
        console.log('Generic error occurred')
        toast.error('Có lỗi xảy ra khi cập nhật thông tin')
      }
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

  avatarForm.avatar = file

  toast.info('Đang tải ảnh lên...')

  avatarForm.post('/profile/avatar', {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Tải ảnh đại diện thành công')
      // Cập nhật lại thông tin user từ mainStore
      mainStore.fetchUserInfo()
      router.reload()
    },
    onError: (errors) => {
      toast.error(errors.avatar || 'Không thể tải lên ảnh đại diện')
    }
  })
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

// Sử dụng onMounted để load dữ liệu ban đầu
onMounted(async () => {
  await mainStore.fetchUserInfo()
  updateFormFromStore()
})

// Chỉ watch những thay đổi từ mainStore
watch(() => mainStore.user, (newUser) => {
  if (newUser) {
    updateFormFromStore()
  }
}, { deep: true })

// Thêm computed property để hiển thị label phù hợp
const selectedGenderLabel = computed(() => {
  const option = genderOptions.find(opt => opt.value === profileForm.gender)
  return option ? option.label : ''
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
            <FormControl v-model="profileForm.gender" :icon="mdiGenderMaleFemale" type="select" :options="genderOptions"
              name="gender" value-field="value" label-field="label" />
          </FormField>

          <FormField label="Ngày sinh">
            <FormControl v-model="profileForm.date_of_birth" :icon="mdiCalendar" type="date" name="date_of_birth" />
          </FormField>

          <FormField label="Tình trạng da">
            <FormControl v-model="profileForm.skin_condition" name="skin_condition" />
          </FormField>

          <template #footer>
            <BaseButtons>
              <BaseButton color="info" type="submit" label="Cập nhật" :loading="profileForm.processing" />
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
