<template>
  <div class="wsp-page">
    <PageHeader title="提款申請" back-to="/transactions" />

    <div class="wsp-content">
      <p class="wsp-desc">為保護您的帳戶安全，請先設定提款密碼</p>

      <AppInput type="password" label="設定提款密碼" placeholder="請設关6-12位數字密碼" />
      <AppInput type="password" label="確認提款密碼" placeholder="再次輸入密碼" />

      <router-link to="/withdrawal/setup" class="submit-btn">確認</router-link>
    </div>
  </div>
</template>

<script setup>
import PageHeader from '../components/PageHeader.vue'
import AppInput from '../components/AppInput.vue'
</script>

<style scoped>
.wsp-page {
  background-color: #ffffff;
  min-height: 100vh;
}

.wsp-content {
  padding: 2rem 1.5rem;
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
