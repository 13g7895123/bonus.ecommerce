<template>
  <div class="topup-page">
    <PageHeader title="儲值紀錄" back-to="/transactions" />

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
import PageHeader from '../components/PageHeader.vue'

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
