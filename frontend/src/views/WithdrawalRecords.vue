<template>
  <PageLayout title="提款紀錄" back-to="/transactions" theme="white">
    <div v-if="loading" class="wr-hint">載入中...</div>
    <div v-else-if="errorMsg" class="wr-hint wr-error">{{ errorMsg }}</div>
    <EmptyTransactions v-else-if="records.length === 0" />
    <ContentList v-else>
      <ContentListItem v-for="record in records" :key="record.id" class="record-item">
        <div class="record-left">
          <div class="record-title-row">
            <p class="record-type">提款</p>
            <span class="record-status" :class="`status-${record.status}`">{{ record.statusText }}</span>
          </div>
          <p class="record-time">{{ record.time }}</p>
          <p v-if="record.referenceId" class="record-reference">{{ record.referenceId }}</p>
        </div>
        <div class="record-amount">{{ record.amount }}</div>
      </ContentListItem>
    </ContentList>
  </PageLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import PageLayout from '../components/PageLayout.vue'
import ContentList from '../components/ContentList.vue'
import ContentListItem from '../components/ContentListItem.vue'
import EmptyTransactions from '../components/EmptyTransactions.vue'
import { WalletService } from '../services/WalletService'

const walletService = new WalletService()
const records = ref([])
const loading = ref(true)
const errorMsg = ref('')

const statusLabels = {
  pending: '處理中',
  completed: '已完成',
  failed: '失敗',
  cancelled: '已取消',
}

const formatAmount = (amount) => {
  const n = Number(amount || 0)
  return `-$${Math.abs(n).toLocaleString()}`
}

const formatTime = (val) => {
  if (!val) return ''
  return new Date(val).toLocaleString('zh-TW', { hour12: false }).replace('T', ' ').slice(0, 19)
}

onMounted(async () => {
  try {
    const result = await walletService.getTransactions({ type: 'withdrawal', limit: 100 })
    records.value = (result?.items || [])
      .filter(t => t.type === 'withdrawal')
      .map(t => ({
        id: t.id,
        time: formatTime(t.created_at || t.createdAt),
        amount: formatAmount(t.amount),
        status: t.status || 'completed',
        statusText: statusLabels[t.status] || t.status || '已完成',
        referenceId: t.reference_id || t.referenceId || '',
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
  gap: 1rem;
}

.record-left {
  display: flex;
  flex: 1;
  min-width: 0;
  flex-direction: column;
  text-align: left;
}

.record-title-row {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.25rem;
}

.record-type {
  font-size: 0.95rem;
  font-weight: 500;
  color: #333;
  margin: 0;
}

.record-status {
  flex: 0 0 auto;
  padding: 0.12rem 0.45rem;
  border-radius: 999px;
  font-size: 0.72rem;
  line-height: 1.2;
  color: #666;
  background: #f1f3f5;
}

.status-completed {
  color: #0f7a43;
  background: #e9f8ef;
}

.status-pending {
  color: #9a5b00;
  background: #fff3d6;
}

.status-failed,
.status-cancelled {
  color: #c0392b;
  background: #fdecea;
}

.record-time,
.record-reference {
  font-size: 0.8rem;
  color: #999;
  margin: 0;
}

.record-reference {
  margin-top: 0.2rem;
  word-break: break-all;
}

.record-amount {
  flex: 0 0 auto;
  font-size: 1rem;
  font-weight: 700;
  color: #c0392b;
}

.wr-hint {
  text-align: center;
  padding: 2rem 1rem;
  color: #999;
  font-size: 0.9rem;
}

.wr-error {
  color: #e74c3c;
}
</style>
