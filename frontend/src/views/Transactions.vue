<template>
  <div class="tx-page">
    <PageHeader title="交易紀錄" back-to="/settings" :bordered="false" />

    <div class="tx-content">
      <router-link to="/transactions/topup" class="tx-btn">
        儲值紀錄
      </router-link>
      <router-link to="/transactions/withdrawal" class="tx-btn">
        提款紀錄
      </router-link>
    </div>
  </div>
</template>

<script setup>
import PageHeader from '../components/PageHeader.vue'
</script>

<style scoped>
.tx-page {
  background-color: #ffffff;
  min-height: 100vh;
}

.tx-content {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.tx-btn {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
  padding: 1.25rem;
  background-color: #ffffff;
  color: #333;
  text-decoration: none;
  font-size: 1rem;
  font-weight: 500;
  border: 1px solid #c7c7c7;
  border-radius: 8px; /* Slight rounded corners */
  box-sizing: border-box;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  transition: all 0.2s;
}

.tx-btn:hover {
  background-color: #f9f9f9;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
</style>
