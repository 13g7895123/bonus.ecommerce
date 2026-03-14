<template>
  <div class="wsp-page">
    <PageHeader title="提款申請" back-to="/settings" />

    <div class="wsp-content">
      <p class="wsp-desc">為保護您的帳戶安全，請先設定提款密碼</p>

      <AppInput v-model="password" type="password" label="設定提款密碼" placeholder="請設定6-12位數字密碼" />
      <AppInput v-model="confirmPassword" type="password" label="確認提款密碼" placeholder="再次輸入密碼" />

      <div style="margin-top: 20px;">
        <AppButton block :disabled="loading" @click="handleSubmit">
          {{ loading ? '處理中...' : '確認' }}
        </AppButton>
      </div>
      <DebugFillButton @fill="fillRandomData" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useToast } from '../composables/useToast'
import PageHeader from '../components/PageHeader.vue'
import AppInput from '../components/AppInput.vue'
import AppButton from '../components/AppButton.vue'
import DebugFillButton from '../components/DebugFillButton.vue'
import { WalletService } from '../services/WalletService'

const router  = useRouter()
const route   = useRoute()
const toast   = useToast()
const walletService = new WalletService()

const password        = ref('')
const confirmPassword = ref('')
const loading         = ref(false)

onMounted(async () => {
  // 只有來自「忘記提款密碼」的 reset=true 才允許已設定過密碼的使用者進入
  if (route.query.reset === 'true') {
    return
  }

  try {
    const wallet = await walletService.getWalletInfo()
    if (wallet?.has_withdrawal_pw) {
      // 已設定提款密碼 → 跳過此頁，一律到下一步（綁定/確認銀行資料）
      router.replace('/withdrawal/setup')
    }
  } catch (e) {
    // 載入失敗不阻斷，依然顯示設定頁面
  }
})

const handleSubmit = async () => {
  if (!password.value || !confirmPassword.value) {
    toast.warning('請填寫所有欄位')
    return
  }
  if (password.value !== confirmPassword.value) {
    toast.error('密碼與確認密碼不一致')
    return
  }
  if (password.value.length < 6 || password.value.length > 12) {
    toast.error('密碼長度須為 6-12 位')
    return
  }

  loading.value = true
  try {
    const userStr = localStorage.getItem('user')
    const userId  = userStr ? JSON.parse(userStr).id : null
    await walletService.setWithdrawPassword(userId, password.value)
    toast.success('提款密碼設定成功')
    router.push('/withdrawal/setup')
  } catch (e) {
    toast.error(e?.response?.data?.message || '設定失敗')
  } finally {
    loading.value = false
  }
}

const fillRandomData = () => {
  password.value        = '123456'
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
