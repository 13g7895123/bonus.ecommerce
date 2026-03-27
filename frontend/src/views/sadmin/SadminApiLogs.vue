<template>
  <div class="panel">
    <div class="panel-header">
      <span class="panel-title">所有 API 紀錄</span>
      <button class="btn btn-outline" :disabled="loading" @click="load">
        <RefreshCw :size="14" />{{ loading ? '載入中...' : '重新整理' }}
      </button>
    </div>

    <!-- 篩選器 -->
    <div class="filter-bar">
      <select v-model="filters.method" class="f-select">
        <option value="">所有方法</option>
        <option>GET</option><option>POST</option><option>PUT</option>
        <option>DELETE</option><option>PATCH</option>
      </select>
      <input v-model="filters.uri" class="f-input" placeholder="URI 關鍵字" @keyup.enter="search" />
      <input v-model="filters.user_id" class="f-input" placeholder="User ID" style="width:100px" @keyup.enter="search" />
      <input v-model="filters.response_code" class="f-input" placeholder="HTTP 狀態碼" style="width:120px" @keyup.enter="search" />
      <input v-model="filters.date_from" type="datetime-local" class="f-input" style="width:180px" />
      <input v-model="filters.date_to"   type="datetime-local" class="f-input" style="width:180px" />
      <button class="btn btn-primary" @click="search"><Search :size="14" />搜尋</button>
      <button class="btn btn-outline"  @click="clearFilters">清除</button>
    </div>

    <div class="table-wrap">
      <div v-if="loading" class="state-msg">載入中...</div>
      <table v-else class="data-table">
        <thead>
          <tr>
            <th>ID</th><th>時間</th><th>方法</th><th>URI</th>
            <th>IP</th><th>User</th><th>狀態碼</th><th>耗時(ms)</th><th>操作</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="log in items" :key="log.id">
            <td class="td-num">{{ log.id }}</td>
            <td class="td-time">{{ fmtTime(log.created_at) }}</td>
            <td><span :class="['badge', methodBadge(log.method)]">{{ log.method }}</span></td>
            <td class="td-uri" :title="log.uri">{{ log.uri }}</td>
            <td>{{ log.ip_address }}</td>
            <td class="td-num">{{ log.user_id ?? '-' }}</td>
            <td><span :class="['badge', statusBadge(log.response_code)]">{{ log.response_code }}</span></td>
            <td class="td-num">{{ log.duration_ms }}</td>
            <td><button class="btn btn-sm btn-outline" @click="openDetail(log.id)">詳情</button></td>
          </tr>
          <tr v-if="!items.length"><td colspan="9" class="state-msg">無資料</td></tr>
        </tbody>
      </table>
    </div>

    <!-- 分頁 -->
    <div class="pagination">
      <button class="btn btn-outline btn-sm" :disabled="page <= 1" @click="goPage(page - 1)">上一頁</button>
      <span class="page-info">第 {{ page }} 頁，共 {{ Math.ceil(total / limit) }} 頁（{{ total }} 筆）</span>
      <button class="btn btn-outline btn-sm" :disabled="page * limit >= total" @click="goPage(page + 1)">下一頁</button>
    </div>
  </div>

  <!-- 詳情 Modal -->
  <div v-if="detail.show" class="modal-overlay" @click.self="detail.show = false">
    <div class="modal-box modal-wide">
      <div class="modal-hd">
        <span>API Log #{{ detail.data?.id }}</span>
        <button class="modal-x" @click="detail.show = false">✕</button>
      </div>
      <div class="modal-bd" v-if="detail.data">
        <div class="detail-row"><b>時間：</b>{{ detail.data.created_at }}</div>
        <div class="detail-row"><b>方法：</b>{{ detail.data.method }}</div>
        <div class="detail-row"><b>URI：</b>{{ detail.data.uri }}</div>
        <div class="detail-row"><b>IP：</b>{{ detail.data.ip_address }}</div>
        <div class="detail-row"><b>User ID：</b>{{ detail.data.user_id ?? '-' }}</div>
        <div class="detail-row"><b>狀態碼：</b>{{ detail.data.response_code }}</div>
        <div class="detail-row"><b>耗時：</b>{{ detail.data.duration_ms }} ms</div>
        <div class="detail-section">
          <b>Request Headers：</b>
          <pre class="code-block">{{ JSON.stringify(detail.data.request_headers, null, 2) }}</pre>
        </div>
        <div class="detail-section">
          <b>Request Body：</b>
          <pre class="code-block">{{ prettyJson(detail.data.request_body) }}</pre>
        </div>
        <div class="detail-section">
          <b>Response Body：</b>
          <pre class="code-block">{{ prettyJson(detail.data.response_body) }}</pre>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RefreshCw, Search } from 'lucide-vue-next'

const items   = ref([])
const total   = ref(0)
const page    = ref(1)
const limit   = 20
const loading = ref(false)

const filters = ref({ method: '', uri: '', user_id: '', response_code: '', date_from: '', date_to: '' })
const detail  = ref({ show: false, data: null })

const buildQuery = () => {
  const p = new URLSearchParams({ page: page.value, limit })
  if (filters.value.method)        p.set('method', filters.value.method)
  if (filters.value.uri)           p.set('uri', filters.value.uri)
  if (filters.value.user_id)       p.set('user_id', filters.value.user_id)
  if (filters.value.response_code) p.set('response_code', filters.value.response_code)
  if (filters.value.date_from)     p.set('date_from', filters.value.date_from.replace('T', ' ') + ':00')
  if (filters.value.date_to)       p.set('date_to', filters.value.date_to.replace('T', ' ') + ':00')
  return p.toString()
}

const load = async () => {
  loading.value = true
  try {
    const res  = await fetch(`/api/v1/sadmin/api-logs?${buildQuery()}`)
    const data = await res.json()
    items.value = data.items || []
    total.value = data.total || 0
  } finally { loading.value = false }
}

const search      = () => { page.value = 1; load() }
const goPage      = (p) => { page.value = p; load() }
const clearFilters = () => { filters.value = { method: '', uri: '', user_id: '', response_code: '', date_from: '', date_to: '' }; search() }

const openDetail = async (id) => {
  const res  = await fetch(`/api/v1/sadmin/api-logs/${id}`)
  const data = await res.json()
  detail.value = { show: true, data }
}

const fmtTime     = (t) => t ? t.replace('T', ' ').slice(0, 19) : '-'
const prettyJson  = (v) => { try { return JSON.stringify(JSON.parse(v), null, 2) } catch { return v || '-' } }
const methodBadge = (m) => ({ GET: 'badge-blue', POST: 'badge-green', PUT: 'badge-yellow', DELETE: 'badge-red', PATCH: 'badge-purple' }[m] ?? 'badge-gray')
const statusBadge = (c) => c >= 500 ? 'badge-red' : c >= 400 ? 'badge-yellow' : c >= 200 ? 'badge-green' : 'badge-gray'

onMounted(load)
</script>

<style scoped>
.filter-bar { display: flex; flex-wrap: wrap; gap: 0.5rem; padding: 0.75rem 1.25rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
.f-select { padding: 0.4rem 0.65rem; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.85rem; background: #fff; }
.f-input { padding: 0.4rem 0.65rem; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.85rem; min-width: 150px; }
.pagination { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1.25rem; justify-content: center; border-top: 1px solid #e2e8f0; }
.page-info { font-size: 0.85rem; color: #64748b; }
.td-uri { max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-family: monospace; font-size: 0.8rem; }
.td-time { font-size: 0.8rem; white-space: nowrap; }
.td-num { text-align: right; }
.modal-wide { width: min(860px, 95vw); max-height: 90vh; overflow-y: auto; }
.detail-row { font-size: 0.875rem; margin-bottom: 0.4rem; }
.detail-section { margin-top: 0.75rem; }
.code-block { background: #1e293b; color: #e2e8f0; border-radius: 6px; padding: 0.75rem; font-size: 0.78rem; overflow: auto; max-height: 250px; white-space: pre-wrap; word-break: break-all; margin-top: 0.3rem; }
</style>
