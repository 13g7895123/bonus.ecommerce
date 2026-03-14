<template>
  <div class="cp-page">
    <PageHeader title="重設密碼" back-to="/profile" />
    
    <div class="cp-content">
      <AppInput 
        v-model="newPassword" 
        type="password" 
        label="新密碼" 
        placeholder="請設定 6-12 位數字密碼" 
      />
      <AppInput 
        v-model="confirmPassword" 
        type="password" 
        label="確認新密碼" 
        placeholder="再次輸入新密碼" 
      />
      
      <AppButton block :disabled="loading" @click="handleSubmit" class="cp-submit-btn">
        {{ loading ? '處理中...' : '送出' }}
      </AppButton>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from '../composables/useToast'
import PageHeader from '../components/PageHeader.vue'
import AppInput from '../components/AppInput.vue'
import AppButton from '../components/AppButton.vue'
import { UserService } from '../services/UserService'

const router = useRouter()
const toast = useToast()
const userService = new UserService()

const newPassword     = ref('')
const confirmPassword = ref('')
const loading         = ref(false)

const handleSubmit = async () => {
  if (!newPassword.value || !confirmPassword.value) {
    toast.warning('請填寫所有欄位')
    return
  }
  if (newPassword.value !== confirmPassword.value) {
    toast.error('新密碼與確認密碼不一致')
    return
  }
  if (newPassword.value.length < 6 || newPassword.value.length > 12) {
    toast.error('密碼長度須為 6-12 位')
    return
  }

  loading.value = true
  try {
    await userService.changePassword(newPassword.value, confirmPassword.value)
    toast.success('密碼更新成功')
    router.push('/profile')
  } catch (e) {
    toast.error(e?.response?.data?.message || '密碼更新失敗')
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.cp-page {
  background-color: #ffffff;
  min-height: 100vh;
}

.cp-content {
  padding: 3rem 5rem;
}

.cp-submit-btn {
  margin-top: 1.5rem;
}

:deep(.app-input-label) {
  text-align: left;
}
</style>
