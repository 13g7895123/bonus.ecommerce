<template>
  <div class="panel">
    <div class="panel-header">
      <span class="panel-title"></span>
      <div style="display:flex;gap:0.5rem;align-items:center">
        <select v-model="rewardOrdersTab" class="f-input" style="width:auto;padding:0.3rem 0.65rem;font-size:0.85rem" @change="loadRewardOrders">
          <option value="">全部</option>
          <option value="pending_review">待審核</option>
          <option value="approved">已批准</option>
          <option value="rejected">已拒絕</option>
        </select>
        <button class="btn btn-outline" :disabled="loadingRewardOrders" @click="loadRewardOrders"><RefreshCw :size="14" /></button>
      </div>
    </div>
    <div class="table-wrap">
      <div v-if="loadingRewardOrders" class="state-msg">載入中...</div>
      <div v-else-if="rewardOrdersList.length === 0" class="state-msg">尚無訂單</div>
      <table v-else class="data-table">
        <thead>
          <tr>
            <th>#</th><th>用戶 ID</th><th>商品</th><th>數量</th>
            <th>總金額</th><th>消耗里程</th><th>現金回饋</th>
            <th>狀態</th><th>時間</th><th>操作</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in rewardOrdersList" :key="order.id">
            <td class="td-muted">{{ order.id }}</td>
            <td>{{ order.user_id }}</td>
            <td class="td-name">{{ order.product_name }}</td>
            <td class="td-num">{{ order.quantity }}</td>
            <td class="td-num">${{ Number(order.total_price).toLocaleString() }}</td>
            <td class="td-num">{{ Number(order.total_miles_points).toLocaleString() }}</td>
            <td class="td-num">${{ Number(order.cash_reward_amount ?? order.mileage_reward_amount ?? 0).toLocaleString() }}</td>
            <td>
              <span :class="['badge', order.status === 'approved' ? 'badge-green' : order.status === 'rejected' ? 'badge-red' : 'badge-yellow']">
                {{ order.status === 'approved' ? '已批准' : order.status === 'rejected' ? '已拒絕' : '待審核' }}
              </span>
            </td>
            <td class="td-muted td-sub">{{ order.created_at ? order.created_at.substring(0,10) : '—' }}</td>
            <td class="td-actions">
              <template v-if="order.status === 'pending_review'">
                <button class="btn btn-sm btn-green" @click="reviewRewardOrder(order.id, 'approve')">批准</button>
                <button class="btn btn-sm btn-danger" @click="reviewRewardOrder(order.id, 'reject')">拒絕</button>
              </template>
              <span v-else class="td-muted">—</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RefreshCw } from 'lucide-vue-next'

const rewardOrdersList    = ref([])
const loadingRewardOrders = ref(false)
const rewardOrdersTab     = ref('')

const loadRewardOrders = async () => {
  loadingRewardOrders.value = true
  try {
    const qs  = rewardOrdersTab.value ? `?status=${rewardOrdersTab.value}` : ''
    const res  = await fetch(`/api/v1/admin-panel/reward-orders${qs}`)
    const data = await res.json()
    rewardOrdersList.value = data.items || []
  } finally { loadingRewardOrders.value = false }
}

const reviewRewardOrder = async (id, action) => {
  if (!confirm(`確定要${action === 'approve' ? '批准' : '拒絕'}此訂單嗎？`)) return
  try {
    const res  = await fetch(`/api/v1/admin-panel/reward-orders/${id}/review`, {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action }),
    })
    const data = await res.json()
    if (!res.ok) { alert(data.message || '操作失敗'); return }
    await loadRewardOrders()
  } catch { alert('操作失敗') }
}

onMounted(loadRewardOrders)
</script>
