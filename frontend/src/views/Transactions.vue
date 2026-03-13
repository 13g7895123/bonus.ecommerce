<template>
  <div class="tx-page">
    <PageHeader title="交易紀錄" back-to="/settings" />

    <div class="tx-list">
      <router-link to="/transactions/topup" class="tx-item">
        <div class="tx-icon">
          <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
            <line x1="1" y1="10" x2="23" y2="10"></line>
          </svg>
        </div>
        <span class="tx-label">儲值紀錄</span>
        <span class="arrow-right"></span>
      </router-link>
      <router-link to="/transactions/withdrawal" class="tx-item">
        <div class="tx-icon">
          <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="1" x2="12" y2="23"></line>
            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
          </svg>
        </div>
        <span class="tx-label">提款紀錄</span>
        <span class="arrow-right"></span>
      </router-link>
    </div>
  </div>
</template>

<script setup>
import PageHeader from '../components/PageHeader.vue'
</script>

<style scoped>
.tx-page {
  background-color: #f5f5f5;
  min-height: 100vh;
}

.tx-list {
  margin-top: 1rem;
  background-color: #ffffff;
}

.tx-item {
  display: flex;
  align-items: center;
  padding: 1.25rem 1.5rem;
  text-decoration: none;
  color: #333;
  border-bottom: 1px solid #f0f0f0;
  gap: 0.875rem;
}

.tx-icon {
  font-size: 1.5rem;
  width: 36px;
  text-align: center;
}

.tx-label {
  flex: 1;
  font-size: 1rem;
  font-weight: 500;
}

.arrow-right {
  display: block;
  width: 8px;
  height: 8px;
  border-top: 2px solid #ccc;
  border-right: 2px solid #ccc;
  transform: rotate(45deg);
}
</style>
