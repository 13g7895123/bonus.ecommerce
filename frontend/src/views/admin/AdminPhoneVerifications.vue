<template>
  <div class="panel">
    <div class="panel-header">
      <span class="panel-title">手機驗證紀錄</span>
      <div style="display:flex;gap:0.5rem;align-items:center;flex-wrap:wrap">
        <input
          v-model="filterPhone"
          class="f-input"
          style="width:160px"
          placeholder="搜尋電話號碼"
          @keyup.enter="load"
        />
        <select v-model="filterStatus" class="f-input" style="width:120px" @change="load">
          <option value="">全部狀態</option>
          <option value="verified">已驗證</option>
          <option value="pending">待驗證</option>
          <option value="expired">已過期</option>
        </select>
        <button class="btn btn-outline" @click="load">
          <RefreshCw :size="14" />{{ loading ? '載入中...' : '重新整理' }}
        </button>
      </div>
    </div>

    <div class="table-wrap">
      <div v-if="loading" class="state-msg">載入中...</div>
      <div v-else-if="!items.length" class="state-msg">暫無驗證紀錄</div>
      <table v-else class="data-table">
        <thead>
          <tr>
            <th>#ID</th>
            <th>電話號碼</th>
            <th>驗證碼</th>
            <th>嘗試次數</th>
            <th>狀態</th>
            <th>到期時間</th>
            <th>驗證時間</th>
            <th>建立時間</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in items" :key="item.id">
            <td>{{ item.id }}</td>
            <td>{{ item.phone }}</td>
            <td class="td-code">{{ item.verified_at ? '******' : item.code }}</td>
            <td>{{ item.attempts }} / 5</td>
            <td>
              <span :class="['badge', statusBadge(item)]">{{ statusLabel(item) }}</span>
            </td>
            <td>{{ formatDate(item.expires_at) }}</td>
            <td>{{ item.verified_at ? formatDate(item.verified_at) : '-' }}</td>
            <td>{{ formatDate(item.created_at) }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- 分頁 -->
    <div v-if="total > limit" class="pagination">
      <button class="btn btn-outline btn-sm" :disabled="page <= 1" @click="changePage(page - 1)">上一頁</button>
      <span class="page-info">第 {{ page }} 頁，共 {{ Math.ceil(total / limit) }} 頁（{{ total }} 筆）</span>
      <button class="btn btn-outline btn-sm" :disabled="page >= Math.ceil(total / limit)" @click="changePage(page + 1)">下一頁</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RefreshCw } from 'lucide-vue-next'

const items        = ref([])
const loading      = ref(false)
const total        = ref(0)
const page         = ref(1)
const limit        = 50
const filterPhone  = ref('')
const filterStatus = ref('')

const statusLabel = (item) => {
  if (item.verified_at)  return '已驗證'
  if (item.is_used)      return '已失效'
  if (new Date(item.expires_at) < new Date()) return '已過期'
  return '待驗證'
}

const statusBadge = (item) => {
  if (item.verified_at)  return 'badge-green'
  if (item.is_used)      return 'badge-gray'
  if (new Date(item.expires_at) < new Date()) return 'badge-red'
  return 'badge-yellow'
}

const formatDate = (dt) => {
  if (!dt) return '-'
  return new Date(dt).toLocaleString('zh-TW', { hour12: false })
}

const load = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams({ page: page.value, limit })
    if (filterPhone.value)  params.set('phone', filterPhone.value)
    if (filterStatus.value) params.set('status', filterStatus.value)
    const res  = await fetch(`/api/v1/admin-panel/phone-verifications?${params}`)
    const data = await res.json()
    items.value = data.items || []
    total.value = data.total || 0
  } finally {
    loading.value = false
  }
}

const changePage = (p) => { page.value = p; load() }

onMounted(load)
</script>

<style scoped>
.panel-title {
  font-size: 1rem;
  font-weight: 600;
}

.td-code {
  font-family: monospace;
  font-size: 1rem;
  letter-spacing: 0.1em;
  font-weight: 600;
}

.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  padding: 1rem 0 0.5rem;
}

.page-info {
  font-size: 0.875rem;
  color: #64748b;
}

.btn-sm {
  font-size: 0.8rem;
  padding: 0.3rem 0.7rem;
}
</style>
