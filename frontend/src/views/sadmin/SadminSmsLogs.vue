<template>
  <div class="panel">
    <!-- SMS 驗證模式開關 ────────────────────────────── -->
    <div class="verify-mode-bar">
      <div class="verify-mode-info">
        <span class="verify-mode-label">SMS 驗證模式</span>
        <span :class="['verify-mode-badge', verifyEnabled ? 'badge-green' : 'badge-yellow']">
          {{ verifyEnabled ? '已啟用（正式）' : '已停用（測試）' }}
        </span>
        <span class="verify-mode-desc">
          {{ verifyEnabled
            ? '用戶必須輸入驗證碼才能完成註冊'
            : '發出簡訊後前台自動完成註冊，無需輸入驗證碼（測試用）' }}
        </span>
      </div>
      <div style="display:flex;align-items:center;gap:0.75rem">
        <!-- 封鎖中 IP 提示 -->
        <button
          v-if="blockedCount > 0"
          class="blocked-badge"
          @click="$router.push('/sadmin/sms-settings')"
          title="點擊前往 SMS 防濫用設定頁解封"
        >
          🚫 {{ blockedCount }} 個 IP 封鎖中
        </button>
        <label class="toggle-switch">
          <input
            type="checkbox"
            :checked="verifyEnabled"
            :disabled="verifyLoading"
            @change="toggleVerifyMode"
          />
          <span class="toggle-slider"></span>
        </label>
      </div>
    </div>
    <!-- ─────────────────────────────────────────────── -->

    <div class="panel-header">
      <span class="panel-title">SMS 簡訊紀錄</span>
      <div style="display:flex;gap:0.5rem;align-items:center;flex-wrap:wrap">
        <select v-model="filterService" class="f-input" style="width:130px" @change="load">
          <option value="">全部服務</option>
          <option value="twilio">Twilio</option>
          <option value="firebase">Firebase</option>
          <option value="topmessage">TopMessage</option>
        </select>
        <select v-model="filterAction" class="f-input" style="width:120px" @change="load">
          <option value="">全部動作</option>
          <option value="sendOtp">sendOtp</option>
          <option value="verifyOtp">verifyOtp</option>
        </select>
        <select v-model="filterSuccess" class="f-input" style="width:100px" @change="load">
          <option value="">全部結果</option>
          <option value="1">成功</option>
          <option value="0">失敗</option>
        </select>
        <input v-model="filterDateFrom" type="date" class="f-input" style="width:140px" @change="load" />
        <input v-model="filterDateTo"   type="date" class="f-input" style="width:140px" @change="load" />
        <button class="btn btn-outline" @click="load">
          <RefreshCw :size="14" />{{ loading ? '載入中...' : '重新整理' }}
        </button>
      </div>
    </div>

    <div class="table-wrap">
      <div v-if="loading" class="state-msg">載入中...</div>
      <div v-else-if="!items.length" class="state-msg">暫無簡訊紀錄</div>
      <table v-else class="data-table">
        <thead>
          <tr>
            <th>#ID</th>
            <th>服務</th>
            <th>動作</th>
            <th>結果</th>
            <th>HTTP</th>
            <th>耗時(ms)</th>
            <th>時間</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in items" :key="row.id">
            <td>{{ row.id }}</td>
            <td><span :class="['badge', serviceBadge(row.service)]">{{ row.service }}</span></td>
            <td><span class="action-text">{{ row.action }}</span></td>
            <td>
              <span :class="['badge', row.success == 1 ? 'badge-green' : 'badge-red']">
                {{ row.success == 1 ? '成功' : '失敗' }}
              </span>
            </td>
            <td>
              <span :class="['badge', httpBadge(row.response_code)]">{{ row.response_code || '-' }}</span>
            </td>
            <td>{{ row.duration_ms }}</td>
            <td>{{ formatDate(row.created_at) }}</td>
            <td>
              <button class="btn btn-outline btn-sm" @click="openDetail(row)">詳情</button>
            </td>
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

    <!-- 詳情 Modal -->
    <div v-if="detail" class="modal-overlay" @click.self="detail = null">
      <div class="modal-box">
        <div class="modal-header">
          <span>紀錄 #{{ detail.id }} 詳情</span>
          <button class="close-btn" @click="detail = null">✕</button>
        </div>
        <div class="modal-body">
          <div class="detail-row"><span class="dk">服務</span><span class="dv">{{ detail.service }}</span></div>
          <div class="detail-row"><span class="dk">動作</span><span class="dv">{{ detail.action }}</span></div>
          <div class="detail-row"><span class="dk">URL</span><span class="dv">{{ detail.url }}</span></div>
          <div class="detail-row"><span class="dk">HTTP</span><span class="dv">{{ detail.response_code }}</span></div>
          <div class="detail-row"><span class="dk">成功</span><span class="dv">{{ detail.success == 1 ? '是' : '否' }}</span></div>
          <div v-if="detail.error_message" class="detail-row">
            <span class="dk">錯誤訊息</span><span class="dv error-text">{{ detail.error_message }}</span>
          </div>
          <div class="detail-row"><span class="dk">耗時</span><span class="dv">{{ detail.duration_ms }} ms</span></div>
          <div class="detail-row"><span class="dk">時間</span><span class="dv">{{ formatDate(detail.created_at) }}</span></div>
          <div class="detail-section">
            <div class="ds-title">Request Body</div>
            <pre class="json-pre">{{ prettyJson(detail.request_body) }}</pre>
          </div>
          <div class="detail-section">
            <div class="ds-title">Response Body</div>
            <pre class="json-pre">{{ prettyJson(detail.response_body) }}</pre>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RefreshCw } from 'lucide-vue-next'

// ── SMS 驗證模式開關 ───────────────────────────────────────────
const verifyEnabled = ref(true)
const verifyLoading = ref(false)
const blockedCount  = ref(0)

const loadVerifyMode = async () => {
  try {
    const res  = await fetch('/api/v1/sadmin/sms-verification-mode')
    const data = await res.json()
    verifyEnabled.value = !!data.enabled
  } catch {
    verifyEnabled.value = true
  }
}

const loadBlockedCount = async () => {
  try {
    const res  = await fetch('/api/v1/sadmin/sms-security/blocked-ips')
    const data = await res.json()
    blockedCount.value = data.total ?? 0
  } catch {
    blockedCount.value = 0
  }
}

const toggleVerifyMode = async (e) => {
  const next = e.target.checked
  verifyLoading.value = true
  try {
    const res  = await fetch('/api/v1/sadmin/sms-verification-mode', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ enabled: next }),
    })
    const data = await res.json()
    if (data.success) {
      verifyEnabled.value = !!data.enabled
    } else {
      // 還原 checkbox
      e.target.checked = verifyEnabled.value
    }
  } catch {
    e.target.checked = verifyEnabled.value
  } finally {
    verifyLoading.value = false
  }
}
// ──────────────────────────────────────────────────────────────

const items          = ref([])
const loading        = ref(false)
const total          = ref(0)
const page           = ref(1)
const limit          = 20
const filterService  = ref('')
const filterAction   = ref('')
const filterSuccess  = ref('')
const filterDateFrom = ref('')
const filterDateTo   = ref('')
const detail         = ref(null)

const load = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams({ page: page.value, limit })
    if (filterService.value) params.set('service', filterService.value)
    if (filterAction.value)  params.set('action',  filterAction.value)
    if (filterSuccess.value !== '') params.set('success', filterSuccess.value)
    if (filterDateFrom.value) params.set('date_from', filterDateFrom.value)
    if (filterDateTo.value)   params.set('date_to',   filterDateTo.value)
    const res  = await fetch(`/api/v1/sadmin/sms-logs?${params}`)
    const data = await res.json()
    items.value = data.items || []
    total.value = data.total || 0
  } finally {
    loading.value = false
  }
}

const changePage = (p) => { page.value = p; load() }
const openDetail = (row) => { detail.value = row }

const formatDate = (dt) => dt ? new Date(dt).toLocaleString('zh-TW', { hour12: false }) : '-'

const prettyJson = (str) => {
  try { return JSON.stringify(JSON.parse(str), null, 2) } catch { return str || '-' }
}

const serviceBadge = (s) => {
  if (s === 'twilio')     return 'badge-blue'
  if (s === 'firebase')   return 'badge-orange'
  if (s === 'topmessage') return 'badge-purple'
  return 'badge-gray'
}

const httpBadge = (code) => {
  if (!code || code === 0) return 'badge-gray'
  if (code >= 200 && code < 300) return 'badge-green'
  if (code >= 400) return 'badge-red'
  return 'badge-yellow'
}

onMounted(() => { loadVerifyMode(); loadBlockedCount(); load() })
</script>

<style scoped>
.panel-title { font-size: 1rem; font-weight: 600; }

.action-text { font-family: monospace; font-size: 0.85rem; }

.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  padding: 1rem 0 0.5rem;
}

.page-info { font-size: 0.875rem; color: #64748b; }
.btn-sm { font-size: 0.8rem; padding: 0.3rem 0.7rem; }

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  z-index: 999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

.modal-box {
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 12px;
  width: 100%;
  max-width: 720px;
  max-height: 85vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 25px 60px rgba(0,0,0,0.4);
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid #334155;
  font-weight: 600;
  font-size: 0.95rem;
  color: #f8fafc;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.1rem;
  cursor: pointer;
  color: #94a3b8;
  padding: 0.2rem 0.4rem;
}

.modal-body {
  overflow-y: auto;
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.detail-row { display: flex; gap: 0.75rem; font-size: 0.85rem; }
.dk { font-weight: 600; color: #94a3b8; min-width: 80px; flex-shrink: 0; }
.dv { color: #e2e8f0; word-break: break-all; }
.error-text { color: #f87171; }

.detail-section { margin-top: 0.75rem; }
.ds-title { font-size: 0.78rem; font-weight: 600; color: #94a3b8; margin-bottom: 0.35rem; }
.json-pre {
  background: #0f172a;
  border: 1px solid #334155;
  border-radius: 6px;
  padding: 0.75rem;
  font-size: 0.78rem;
  font-family: monospace;
  color: #93c5fd;
  overflow-x: auto;
  white-space: pre-wrap;
  word-break: break-all;
  max-height: 250px;
  overflow-y: auto;
}

.badge-blue   { background: #1e3a5f; color: #93c5fd; }
.badge-orange { background: #431407; color: #fb923c; }
.badge-purple { background: #2e1065; color: #c4b5fd; }

/* SMS 驗證模式開關 */
.verify-mode-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 0.85rem 1.25rem;
  background: #0f172a;
  border-bottom: 1px solid #334155;
  flex-wrap: wrap;
}

.verify-mode-info {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  flex-wrap: wrap;
}

.verify-mode-label {
  font-size: 0.9rem;
  font-weight: 600;
  color: #f1f5f9;
}

.badge-yellow { background: #422006; color: #fbbf24; }

.verify-mode-desc {
  font-size: 0.8rem;
  color: #94a3b8;
}

/* Toggle Switch */
.toggle-switch {
  position: relative;
  display: inline-block;
  width: 52px;
  height: 28px;
  flex-shrink: 0;
}

.toggle-switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.toggle-slider {
  position: absolute;
  cursor: pointer;
  inset: 0;
  background: #334155;
  border-radius: 28px;
  transition: background 0.2s;
}

.toggle-slider::before {
  content: '';
  position: absolute;
  left: 4px;
  top: 4px;
  width: 20px;
  height: 20px;
  background: #fff;
  border-radius: 50%;
  transition: transform 0.2s;
}

.toggle-switch input:checked + .toggle-slider {
  background: #22c55e;
}

.toggle-switch input:checked + .toggle-slider::before {
  transform: translateX(24px);
}

.toggle-switch input:disabled + .toggle-slider {
  opacity: 0.5;
  cursor: not-allowed;
}

/* 封鎖 IP 提示徽章 */
.blocked-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  background: #450a0a;
  color: #fca5a5;
  border: 1px solid #7f1d1d;
  border-radius: 999px;
  padding: 0.3rem 0.85rem;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.15s;
  white-space: nowrap;
}
.blocked-badge:hover {
  background: #7f1d1d;
}
</style>
