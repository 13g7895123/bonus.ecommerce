<template>
  <PageLayout title="里程紀錄" back-to="/skywards" theme="white">
    <div v-if="loading" class="mr-hint">載入中...</div>
    <div v-else-if="errorMsg" class="mr-hint mr-error">{{ errorMsg }}</div>
    <EmptyTransactions v-else-if="records.length === 0" />
    <ContentList v-else>
      <ContentListItem v-for="record in records" :key="record.id" class="record-item">
        <div class="record-left">
          <p class="record-type">
            {{ record.type }}
            <span v-if="record.subtype" :class="['record-status', record.isRejected ? 'status-rejected' : record.isPending ? 'status-pending' : 'status-approved']">{{ record.subtype }}</span>
          </p>
          <p class="record-time">{{ record.time }}</p>
        </div>
        <div class="record-amount" :class="{ 'negative': record.isRejected }">
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

const SHOW_REWARD_ORDERS = true

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

const REWARD_STATUS_LABEL = {
  pending_review: '審核中',
  approved:       '已核准',
  rejected:       '已拒絕',
}

const formatRewardAmount = (amount) => {
  const n = Number(amount)
  return n >= 0 ? `+${n.toLocaleString()}` : n.toLocaleString()
}

onMounted(async () => {
  try {
    const requests = [mileageService.getHistory()]
    if (SHOW_REWARD_ORDERS) requests.push(mileageService.getMyRewardOrders())
    const [historyResult, rewardResult] = await Promise.all(requests)

    // 里程紀錄
    const filterFn = SHOW_REWARD_ORDERS
      ? () => true
      : t => !(t.type === 'earn' && t.source === 'reward_purchase')
    const mileageItems = (historyResult?.items || [])
      .filter(filterFn)
      .map(t => ({
        id:         `m-${t.id}`,
        type:       getTypeLabel(t),
        subtype:    null,
        time:       formatTime(t.created_at),
        amount:     formatAmount(t.type, t.amount),
        sortTime:   t.created_at || '',
      }))

    // 里程回饋訂單（僅在 showRewardOrders 為 true 時顯示）
    const rewardItems = SHOW_REWARD_ORDERS
      ? (rewardResult?.items || []).map(o => ({
          id:         `r-${o.id}`,
          type:       '里程回饋',
          subtype:    REWARD_STATUS_LABEL[o.status] || o.status,
          time:       formatTime(o.created_at),
          amount:     formatRewardAmount(o.mileage_reward_amount),
          sortTime:   o.created_at || '',
          isPending:  o.status === 'pending_review',
          isRejected: o.status === 'rejected',
        }))
      : []

    // 依時間降序
    records.value = [...mileageItems, ...rewardItems]
      .sort((a, b) => (a.sortTime < b.sortTime ? 1 : a.sortTime > b.sortTime ? -1 : 0))
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
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.record-status {
  font-size: 0.7rem;
  font-weight: 600;
  padding: 1px 6px;
  border-radius: 999px;
}

.status-pending {
  background-color: #fff3cd;
  color: #856404;
}

.status-approved {
  background-color: #d1f7e0;
  color: #1a7a42;
}

.status-rejected {
  background-color: #fde8e8;
  color: #c0392b;
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