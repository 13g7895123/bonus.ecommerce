<template>
  <div class="tx-page">
    <PageHeader title="交易紀錄" back-to="/settings" />

    <div class="tx-list">
      <router-link to="/transactions/topup" class="tx-item">
        <div class="tx-icon">💳</div>
        <span class="tx-label">儲值紀錄</span>
        <span class="arrow-right"></span>
      </router-link>
      <router-link to="/transactions/withdrawal" class="tx-item">
        <div class="tx-icon">💸</div>
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
