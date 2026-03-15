<template>
  <PageLayout title="儲值紀錄" back-to="/transactions" theme="white">
    <div v-if="loading" class="tr-hint">載入中...</div>
    <div v-else-if="errorMsg" class="tr-hint tr-error">{{ errorMsg }}</div>
    <EmptyTransactions v-else-if="records.length === 0" />
    <ContentList v-else>
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
import { ref, onMounted } from 'vue'
import PageLayout from '../components/PageLayout.vue'
import ContentList from '../components/ContentList.vue'
import ContentListItem from '../components/ContentListItem.vue'
import { WalletService } from '../services/WalletService'
import EmptyTransactions from '../components/EmptyTransactions.vue'

const walletService = new WalletService()
const records  = ref([])
const loading  = ref(true)
const errorMsg = ref('')

const formatAmount = (amount) => {
  const n = Number(amount)
  return n >= 0 ? `+$${n.toLocaleString()}` : `-$${Math.abs(n).toLocaleString()}`
}

const formatTime = (val) => {
  if (!val) return ''
  return new Date(val).toLocaleString('zh-TW', { hour12: false }).replace('T', ' ').slice(0, 19)
}

onMounted(async () => {
  try {
    // 撈全部交易，只顯示 deposit / adjustment（管理員儲值）類型
    const result = await walletService.getTransactions()
    records.value = (result?.items || [])
      .filter(t => t.type === 'deposit' || t.type === 'adjustment')
      .map(t => ({
        id:     t.id,
        time:   formatTime(t.created_at || t.createdAt),
        amount: formatAmount(t.amount),
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
  margin: 0;
}

.record-amount {
  font-size: 1rem;
  font-weight: 700;
  color: #000000;
}

.tr-hint {
  text-align: center;
  padding: 2rem 1rem;
  color: #999;
  font-size: 0.9rem;
}

.tr-error {
  color: #e74c3c;
}
</style>
