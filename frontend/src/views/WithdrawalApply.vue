<template>
  <div class="wa-page">
    <PageHeader title="提款申請" back-to="/settings" :bordered="false" />

    <div class="wa-content">
      <!-- 可提款現金 -->
      <div class="info-section">
        <p class="info-label">可提款現金</p>
        <p class="info-value cash-value">${{ balance.toLocaleString() }}</p>
      </div>

      <div class="divider"></div>

      <!-- 銀行存摺 -->
      <div class="info-section">
        <p class="info-label">銀行帳號</p>
        <p class="info-value account-value">{{ bankAccount || '尚未綁定' }}</p>
      </div>

      <div class="divider"></div>

      <!-- 提款現金 -->
      <div class="info-section">
        <p class="info-label highlight-label">提款現金</p>
        <p class="info-hint">單次提款金額為1000元，最大可提款100000000元</p>
      </div>

      <!-- 輸入框 -->
      <AppInput v-model="amount" type="number" placeholder="請輸入提款金額" />
      <AppInput v-model="password" type="password" placeholder="請輸入提款密碼" />

      <div class="forgot-pwd-link">
        <router-link :to="{ path: '/transactions/withdrawal', query: { reset: 'true' }}" class="link-text">忘記提款密碼?</router-link>
      </div>

      <!-- 提款申請按鈕 -->
      <div class="bottom-button-container">
          <AppButton block :disabled="loading" @click="handleWithdraw">提款申請</AppButton>
          <DebugFillButton @fill="fillRandomData" />
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useApi } from '../composables/useApi'
import { useToast } from '../composables/useToast'
import PageHeader from '../components/PageHeader.vue'
import AppInput from '../components/AppInput.vue'
import AppButton from '../components/AppButton.vue'
import DebugFillButton from '../components/DebugFillButton.vue'

const router = useRouter()
const api = useApi()

const amount = ref('')
const password = ref('')
const bankAccount = ref('')
const balance = ref(0)
const loading = ref(false)
const toast = useToast()

const fetchProfile = async () => {
    try {
        const userStr = localStorage.getItem('user');
        if (!userStr) {
             router.push('/login');
             return;
        }
        const localUser = JSON.parse(userStr);
        // 更新用戶資料以取得最新餘額
        const user = await api.user.getProfile(localUser.id);
        
        if (user && user.wallet) {
            balance.value = user.wallet.balance || 0;
            if (user.wallet.bank) {
                bankAccount.value = user.wallet.bank.account || '';
            }
        }
    } catch (error) {
        toast.error('無法載入帳戶資訊')
    }
}

const handleWithdraw = async () => {
    if (!amount.value || !password.value) {
        return; // or show toast
    }
    loading.value = true;
    try {
        const userStr = localStorage.getItem('user');
        if (!userStr) return;
        const localUser = JSON.parse(userStr);
        
        await api.wallet.applyWithdrawal(localUser.id, Number(amount.value), password.value);
        toast.success('提款申請已送出');
        router.push('/settings');
    } catch(err) {
        toast.error(err.message || '申請失敗');
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    fetchProfile();
})

const fillRandomData = () => {
  amount.value = '1000'
  password.value = '123456'
}
</script>

<style scoped>
.wa-page {
  background-color: #ffffff;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.wa-content {
  padding: 1.5rem 1.5rem; /* Reduced padding from 3rem 5rem */
  display: flex;
  flex-direction: column;
  flex: 1;
  text-align: left; /* Ensure left alignment */
}

/* 資訊區塊 */
.info-section {
  padding: 0.75rem 0;
  text-align: left; /* Ensure info sections are left aligned */
}

.info-label {
  font-size: 0.8rem;
  color: #999;
  margin: 0 0 0.35rem 0;
}

.highlight-label {
  font-size: 1.2rem;
  font-weight: 700;
  color: #444; /* Dark grey */
}

.info-value {
  font-size: 1.25rem;
  font-weight: 700;
  margin: 0;
  color: #333;
}

.cash-value {
  color: #d71921;
  font-size: 1.5rem;
}

.account-value {
  font-size: 1rem;
  letter-spacing: 0.05em;
}

.info-hint {
  font-size: 0.8rem;
  color: #999;
  margin: 0;
  line-height: 1.5;
}

.divider {
  height: 1px;
  background-color: #f0f0f0;
  margin: 0.25rem 0;
}

/* 提款申請按鈕 */
/* .apply-btn removed */

.forgot-pwd-link {
  margin-top: 0.5rem;
  text-align: left;
  margin-bottom: 2rem;
}

.link-text {
  color: #666;
  font-size: 0.9rem;
  text-decoration: none;
  cursor: pointer;
}

.bottom-button-container {
  margin-top: auto;
  padding-bottom: 2rem;
}

</style>
