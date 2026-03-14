<template>
  <div class="cp-page">
    <PageHeader title="重設密碼" back-to="/profile" />
    
    <div class="cp-content">
      <AppInput 
        v-model="newPassword" 
        type="password" 
        label="重設密碼" 
        placeholder="請設定6-12位數字密碼" 
      />
      <AppInput 
        v-model="confirmPassword" 
        type="password" 
        label="確認密碼" 
        placeholder="再次輸入密碼" 
      />
      
      <AppButton block @click="handleSubmit" class="cp-submit-btn">送出</AppButton>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import PageHeader from '../components/PageHeader.vue'
import AppInput from '../components/AppInput.vue'
import AppButton from '../components/AppButton.vue'
import { usePasswordForm } from '../composables/usePasswordForm.js'

const router = useRouter()
const { password: newPassword, confirmPassword, validate, saveToLocalStorage } = usePasswordForm()

const handleSubmit = () => {
  if (validate()) {
    saveToLocalStorage('password')
    alert('密碼重設成功')
    router.push('/profile')
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
