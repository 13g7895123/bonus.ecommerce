<template>
  <PageLayout title="里程紀錄" back-to="/skywards" theme="white">
    <ContentList>
      <ContentListItem v-for="record in records" :key="record.id" class="record-item">
        <div class="record-left">
          <p class="record-type">{{ record.type }}</p>
          <p class="record-time">{{ record.time }}</p>
        </div>
        <div class="record-amount" :class="{ 'negative': record.amount.startsWith('-') }">
          {{ record.amount }}
        </div>
      </ContentListItem>
    </ContentList>
  </PageLayout>
</template>

<script setup>
import PageLayout from '../components/PageLayout.vue'
import ContentList from '../components/ContentList.vue'
import ContentListItem from '../components/ContentListItem.vue'

const records = [
  { id: 1, type: '搭乘回饋 - 台北至東京', time: '2026-03-12 14:30:00', amount: '+2,500' },
  { id: 2, type: '里程兌換 - 機場貴賓室', time: '2026-03-10 09:15:00', amount: '-1,000' },
  { id: 3, type: '搭乘回饋 - 高雄至首爾', time: '2026-03-08 18:47:00', amount: '+1,800' },
  { id: 4, type: '生日禮遇贈送', time: '2026-03-05 11:20:00', amount: '+500' },
  { id: 5, type: '里程兌換 - 行李加購', time: '2026-03-01 16:03:00', amount: '-800' },
]
</script>

<style scoped>
.record-item {
  justify-content: space-between;
}

.record-left {
  display: flex;
  flex-direction: column;
  text-align: left;
}

.record-type {
  font-size: 0.95rem;
  font-weight: 500;
  color: #333;
  margin: 0 0 0.25rem 0;
}

.record-time {
  font-size: 0.8rem;
  color: #999;
  font-family: 'Avram Sans', sans-serif; /* Consistent font usage */
  margin: 0;
}

.record-amount {
  font-weight: 500;
  color: #27ae60; /* Positive green */
}

.record-amount.negative {
  color: #e74c3c; /* Negative red */
}
</style>