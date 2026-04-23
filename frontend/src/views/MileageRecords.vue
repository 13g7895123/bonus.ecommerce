<template>
  <PageLayout title="里程紀錄" back-to="/skywards" theme="white">
    <div v-if="loading" class="mr-hint">載入中...</div>
    <div v-else-if="errorMsg" class="mr-hint mr-error">{{ errorMsg }}</div>
    <EmptyTransactions v-else-if="records.length === 0" />
    <ContentList v-else>
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
import { ref, onMounted } from 'vue'
import PageLayout from '../components/PageLayout.vue'
import ContentList from '../components/ContentList.vue'
import ContentListItem from '../components/ContentListItem.vue'
import { MileageService } from '../services/MileageService'
import EmptyTransactions from '../components/EmptyTransactions.vue'

const mileageService = new MileageService()
const records  = ref([])
const loading  = ref(true)
const errorMsg = ref('')

const TYPE_LABEL = {
  earn:  '里程代碼兌換',
  spend: '里程使用',
}

const getTypeLabel = (t) => {
  if (t.type === 'earn' && t.source === 'reward_purchase') return '里程回饋'
  return TYPE_LABEL[t.type] || t.type || '里程交易'
}

const formatAmount = (type, amount) => {
  const n = Number(amount)
  if (type === 'spend') {
    return n <= 0 ? n.toLocaleString() : `-${n.toLocaleString()}`
  }
  return n >= 0 ? `+${n.toLocaleString()}` : n.toLocaleString()
}

const formatTime = (val) => {
  if (!val) return ''
  return new Date(val).toLocaleString('zh-TW', { hour12: false }).replace('T', ' ').slice(0, 19)
}

onMounted(async () => {
  try {
    const result = await mileageService.getHistory()
    records.value = (result?.items || []).map(t => ({
      id:     t.id,
      type:   getTypeLabel(t),
      time:   formatTime(t.created_at),
      amount: formatAmount(t.type, t.amount),
    }))
  } catch (e) {
    errorMsg.value = '載入失敗，請稍後再試'
  } finally {
    loading.value = false
  }
})
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

.mr-hint {
  text-align: center;
  padding: 2rem 1rem;
  color: #999;
  font-size: 0.9rem;
}

.mr-error {
  color: #e74c3c;
}
</style>