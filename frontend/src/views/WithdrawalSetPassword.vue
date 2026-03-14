<template>
  <div class="wsp-page">
    <PageHeader title="提款申請" back-to="/transactions" />

    <div class="wsp-content">
      <p class="wsp-desc">為保護您的帳戶安全，請先設定提款密碼</p>

      <AppInput v-model="password" type="password" label="設定提款密碼" placeholder="請設定6-12位數字密碼" />
      <AppInput v-model="confirmPassword" type="password" label="確認提款密碼" placeholder="再次輸入密碼" />

      <div style="margin-top: 20px;">
        <AppButton block @click="handleSubmit">確認</AppButton>
      </div>
      <DebugFillButton @fill="fillRandomData" />
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import PageHeader from '../components/PageHeader.vue'
import AppInput from '../components/AppInput.vue'
import AppButton from '../components/AppButton.vue'
import DebugFillButton from '../components/DebugFillButton.vue'
import { usePasswordForm } from '../composables/usePasswordForm.js'

const router = useRouter()
const route = useRoute()
const { password, confirmPassword, validate, saveToLocalStorage } = usePasswordForm()

onMounted(() => {
  if (route.query.reset === 'true') {
     return; // Allow password reset
  }

  const userStr = localStorage.getItem('user')
  if (userStr) {
    try {
      const user = JSON.parse(userStr)
      // Check if user has wallet password set
      // Some versions of user object might not have wallet property initialized
      if (user.wallet && user.wallet.password) {
        router.replace('/withdrawal/setup')
      }
    } catch (e) {
      console.error('Failed to parse user from localStorage', e)
    }
  }
})

const handleSubmit = () => {
  if (validate()) {
    saveToLocalStorage('wallet.password')
    router.push('/withdrawal/setup')
  }
}

const fillRandomData = () => {
  password.value = '123456'
  confirmPassword.value = '123456'
}
</script>

<style scoped>
.wsp-page {
  background-color: #ffffff;
  min-height: 100vh;
}

.wsp-content {
  padding: 0 5rem 3rem 5rem;
}

.wsp-desc {
  font-size: 0.875rem;
  color: #999;
  text-align: center;
  margin-bottom: 2rem;
}

.submit-btn {
  display: block;
  width: 100%;
  padding: 1rem;
  background-color: #d71921;
  color: #ffffff;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  margin-top: 2rem;
  text-align: center;
  text-decoration: none;
  box-sizing: border-box;
  transition: background-color 0.2s;
}

.submit-btn:hover {
  background-color: #b8151b;
}
</style>
