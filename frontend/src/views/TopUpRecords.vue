<template>
  <PageLayout title="儲值紀錄" back-to="/transactions" theme="white">
    <ContentList>
      <ContentListItem v-for="record in records" :key="record.id" class="record-item">
        <div class="record-left">
          <p class="record-type">儲值</p>
          <p class="record-time">{{ record.time }}</p>
        </div>
        <div class="record-amount">{{ record.amount }}</div>
      </ContentListItem>
    </ContentList>
  </PageLayout>
</template>

<script setup>
import PageLayout from '../components/PageLayout.vue'
import ContentList from '../components/ContentList.vue'
import ContentListItem from '../components/ContentListItem.vue'

const records = [
  { id: 1, time: '2026-03-11 14:32:00', amount: '+$7,000' },
  { id: 2, time: '2026-03-10 09:15:00', amount: '+$3,500' },
  { id: 3, time: '2026-03-08 18:47:00', amount: '+$10,000' },
  { id: 4, time: '2026-03-05 11:20:00', amount: '+$5,000' },
  { id: 5, time: '2026-03-01 16:03:00', amount: '+$2,000' },
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
  margin: 0;
}

.record-amount {
  font-size: 1rem;
  font-weight: 700;
  color: #000000;
}
</style>
