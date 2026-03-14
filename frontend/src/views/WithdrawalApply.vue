<template>
  <div class="wa-page">
    <PageHeader title="提款申請" back-to="/withdrawal/setup" />

    <div class="wa-content">
      <!-- 可提款現金 -->
      <div class="info-section">
        <p class="info-label">可提款現金</p>
        <p class="info-value cash-value">$236,802</p>
      </div>

      <div class="divider"></div>

      <!-- 銀行存摺 -->
      <div class="info-section">
        <p class="info-label">銀行存摺</p>
        <p class="info-value account-value">XXXXXXXXXXX</p>
      </div>

      <div class="divider"></div>

      <!-- 提款現金 -->
      <div class="info-section">
        <p class="info-label">提款現金</p>
        <p class="info-hint">單次提款金額為1000元，最大可提款100000000元</p>
      </div>

      <!-- 輸入框 -->
      <AppInput v-model="amount" type="number" placeholder="請輸入提款金額" />
      <AppInput v-model="password" type="password" placeholder="請輸入提款密碼" />

      <!-- 提款申請按鈕 -->
      <button class="apply-btn">提款申請</button>
      
      <div class="forgot-pwd-link">
        <router-link :to="{ path: '/transactions/withdrawal', query: { reset: 'true' }}" class="link-text">忘記提款密碼?</router-link>
      </div>

      <DebugFillButton @fill="fillRandomData" />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import PageHeader from '../components/PageHeader.vue'
import AppInput from '../components/AppInput.vue'
import DebugFillButton from '../components/DebugFillButton.vue'

const amount = ref('')
const password = ref('')

const fillRandomData = () => {
  amount.value = '1000'
  password.value = '123456'
}
</script>

<style scoped>
.wa-page {
  background-color: #ffffff;
  min-height: 100vh;
}

.wa-content {
  padding: 3rem 5rem;
}

/* 資訊區塊 */
.info-section {
  padding: 0.75rem 0;
}

.info-label {
  font-size: 0.8rem;
  color: #999;
  margin: 0 0 0.35rem 0;
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
.apply-btn {
  width: 100%;
  padding: 1rem;
  background-color: #d71921;
  color: #ffffff;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  margin-top: 1rem;
  transition: background-color 0.2s;
}

.apply-btn:hover {
  background-color: #b8151b;
}

.forgot-pwd-link {
  margin-top: 1rem;
  text-align: center;
}

.link-text {
  color: #666;
  font-size: 0.9rem;
  text-decoration: underline;
  cursor: pointer;
}
</style>
