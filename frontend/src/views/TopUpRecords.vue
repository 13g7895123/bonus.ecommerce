<template>
  <div class="topup-page">
    <div class="topup-header">
      <router-link to="/transactions" class="back-btn">
        <span class="arrow-left"></span>
      </router-link>
      <h2 class="header-title">儲值紀錄</h2>
      <div class="header-placeholder"></div>
    </div>

    <div class="record-list">
      <div class="record-item" v-for="record in records" :key="record.id">
        <div class="record-left">
          <p class="record-type">儲值</p>
          <p class="record-time">{{ record.time }}</p>
        </div>
        <div class="record-amount">{{ record.amount }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
const records = [
  { id: 1, time: '2026/03/11 14:32', amount: '+$7,000' },
  { id: 2, time: '2026/03/10 09:15', amount: '+$3,500' },
  { id: 3, time: '2026/03/08 18:47', amount: '+$10,000' },
  { id: 4, time: '2026/03/05 11:20', amount: '+$5,000' },
  { id: 5, time: '2026/03/01 16:03', amount: '+$2,000' },
]
</script>

<style scoped>
.topup-page {
  background-color: #f5f5f5;
  min-height: 100vh;
}

.topup-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.5rem;
  background-color: #ffffff;
  border-bottom: 1px solid #eee;
  position: sticky;
  top: 0;
  z-index: 10;
}

.back-btn {
  text-decoration: none;
  color: #333;
  padding: 5px;
  display: flex;
  align-items: center;
}

.arrow-left {
  display: block;
  width: 12px;
  height: 12px;
  border-left: 2px solid #333;
  border-bottom: 2px solid #333;
  transform: rotate(45deg);
}

.header-title {
  font-size: 1.1rem;
  font-weight: 700;
  margin: 0;
}

.header-placeholder {
  width: 22px;
}

.record-list {
  margin-top: 1rem;
  background-color: #ffffff;
}

.record-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.1rem 1.5rem;
  border-bottom: 1px solid #f0f0f0;
}

.record-left {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.record-type {
  font-size: 0.95rem;
  font-weight: 600;
  color: #333;
  margin: 0;
}

.record-time {
  font-size: 0.8rem;
  color: #999;
  margin: 0;
}

.record-amount {
  font-size: 1rem;
  font-weight: 700;
  color: #2ca44e;
}
</style>
