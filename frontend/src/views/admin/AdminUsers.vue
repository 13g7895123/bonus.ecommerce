<template>
  <div class="panel">
    <div class="panel-header">
      <span class="panel-title"></span>
      <button class="btn btn-outline" :disabled="loadingUsers" @click="loadUsers">
        <RefreshCw :size="14" />{{ loadingUsers ? '載入中...' : '重新整理' }}
      </button>
    </div>
    <div class="table-wrap">
      <div v-if="loadingUsers" class="state-msg">載入中...</div>
      <table v-else class="data-table">
        <thead>
          <tr>
            <th>姓名</th><th>Email</th><th>Email驗證</th>
            <th>電話</th><th>國家</th><th>角色</th><th>KYC</th>
            <th>餘額</th><th>里程</th><th>操作</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="u in usersList" :key="u.id">
            <td class="td-name">{{ u.full_name || '-' }}</td>
            <td>{{ u.email }}</td>
            <td><span :class="['badge', u.is_verified == 1 ? 'badge-green' : 'badge-gray']">{{ u.is_verified == 1 ? '已驗證' : '未驗證' }}</span></td>
            <td>{{ u.phone || '-' }}</td>
            <td>{{ u.country || '-' }}</td>
            <td><span :class="['badge', u.role === 'admin' ? 'badge-purple' : 'badge-blue']">{{ u.role || 'user' }}</span></td>
            <td><span :class="['badge', kycBadgeClass(u.verify_status)]">{{ kycLabel(u.verify_status) }}</span></td>
            <td class="td-num">${{ (u.balance || 0).toLocaleString() }}</td>
            <td class="td-num">{{ (u.miles_balance || 0).toLocaleString() }}</td>
            <td><button class="btn btn-sm btn-primary" @click="openDeposit(u)">儲值</button></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- 儲值 Modal -->
  <div v-if="depositModal.show" class="modal-overlay" @click.self="depositModal.show = false">
    <div class="modal-box">
      <div class="modal-hd">
        <span>儲值 — {{ depositModal.user?.full_name || depositModal.user?.email }}</span>
        <button class="modal-x" @click="depositModal.show = false">✕</button>
      </div>
      <div class="modal-bd">
        <p style="font-size:0.85rem;color:#64748b;margin-bottom:0.75rem">目前餘額：<strong>${{ (depositModal.user?.balance || 0).toLocaleString() }}</strong></p>
        <label class="f-label">儲值金額</label>
        <input v-model="depositModal.amount" type="number" min="1" class="f-input" placeholder="請輸入金額" @keyup.enter="submitDeposit" />
        <label class="f-label" style="margin-top:0.75rem">備註（選填）</label>
        <input v-model="depositModal.description" type="text" class="f-input" placeholder="例：測試儲值" />
      </div>
      <div class="modal-ft">
        <button class="btn btn-outline" @click="depositModal.show = false">取消</button>
        <button class="btn btn-primary" :disabled="depositModal.submitting" @click="submitDeposit">{{ depositModal.submitting ? '處理中...' : '確認儲值' }}</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RefreshCw } from 'lucide-vue-next'

const usersList    = ref([])
const loadingUsers = ref(false)

const kycLabel      = (s) => ({ approved: '已通過', verified: '已通過', pending: '待審核', rejected: '未通過', none: '未提交' }[s] || '未提交')
const kycBadgeClass = (s) => ({ approved: 'badge-green', verified: 'badge-green', pending: 'badge-yellow', rejected: 'badge-red', none: 'badge-gray' }[s] || 'badge-gray')

const loadUsers = async () => {
  loadingUsers.value = true
  try {
    const res  = await fetch('/api/v1/admin-panel/users?limit=200')
    const data = await res.json()
    usersList.value = data.items || []
  } finally { loadingUsers.value = false }
}

const depositModal = ref({ show: false, user: null, amount: '', description: '', submitting: false })
const openDeposit  = (user) => { depositModal.value = { show: true, user, amount: '', description: '', submitting: false } }

const submitDeposit = async () => {
  const amount = parseFloat(depositModal.value.amount)
  if (!amount || amount <= 0) { alert('請輸入有效的正數金額'); return }
  depositModal.value.submitting = true
  try {
    const res  = await fetch(`/api/v1/admin-panel/users/${depositModal.value.user.id}/deposit`, {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ amount, description: depositModal.value.description || '管理員儲值' }),
    })
    const data = await res.json()
    if (!res.ok) { alert(data.message || '儲值失敗'); return }
    const u = usersList.value.find(u => u.id === depositModal.value.user.id)
    if (u) u.balance = data.balance
    depositModal.value.user.balance = data.balance
    alert(`儲值成功！新餘額：$${Number(data.balance).toLocaleString()}`)
    depositModal.value.show = false
  } finally { depositModal.value.submitting = false }
}

onMounted(loadUsers)
</script>
