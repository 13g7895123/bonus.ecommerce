<template>
  <div class="ivc-page">
    <div class="ivc-body">
      <!-- 成功圖示 -->
      <div class="ivc-icon-wrap">
        <div class="ivc-icon-circle">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
        </div>
      </div>

      <!-- 標題 -->
      <h2 class="ivc-title">實名認證已送出</h2>

      <!-- 說明文字 -->
      <p class="ivc-desc">您的身份驗證資料已成功送出，<br />我們將在 1-3 個工作天內完成審核。</p>
      <p class="ivc-desc-sub">審核結果將透過通知告知您，<br />請耐心等候。</p>

      <!-- 狀態卡片 -->
      <div class="ivc-status-card">
        <div class="ivc-status-row">
          <span class="ivc-status-label">目前狀態</span>
          <span class="ivc-status-value pending">審核中</span>
        </div>
      </div>

      <!-- 返回按鈕 -->
      <AppButton block class="ivc-btn" @click="goToSettings">返回設定</AppButton>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import AppButton from '../components/AppButton.vue'

const router = useRouter()

const goToSettings = () => {
  router.push('/settings')
}
</script>

<style scoped>
.ivc-page {
  background-color: #ffffff;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem 1.5rem;
}

.ivc-body {
  width: 100%;
  max-width: 360px;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

/* 成功圓形圖示 */
.ivc-icon-wrap {
  margin-bottom: 1.75rem;
}

.ivc-icon-circle {
  width: 96px;
  height: 96px;
  border-radius: 50%;
  background-color: #d71921;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* 標題 */
.ivc-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1a1a1a;
  margin: 0 0 1rem 0;
}

/* 說明文字 */
.ivc-desc {
  font-size: 0.95rem;
  color: #555;
  line-height: 1.7;
  margin: 0 0 0.5rem 0;
}

.ivc-desc-sub {
  font-size: 0.875rem;
  color: #999;
  line-height: 1.7;
  margin: 0 0 2rem 0;
}

/* 狀態卡片 */
.ivc-status-card {
  width: 100%;
  background-color: #f9f9f9;
  border: 1px solid #efefef;
  border-radius: 12px;
  padding: 1rem 1.25rem;
  margin-bottom: 2rem;
}

.ivc-status-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.ivc-status-label {
  font-size: 0.9rem;
  color: #555;
}

.ivc-status-value {
  font-size: 0.9rem;
  font-weight: 700;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
}

.ivc-status-value.pending {
  background-color: #fff8e1;
  color: #b8860b;
  border: 1px solid #f5c842;
}

/* 按鈕 */
.ivc-btn {
  width: 100%;
}
</style>
