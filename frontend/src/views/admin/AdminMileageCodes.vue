<template>
  <div class="panel">
    <div class="panel-header">
      <span class="panel-title"></span>
      <div style="display:flex;gap:0.5rem">
        <button class="btn btn-primary" @click="openForm()"><Plus :size="14" />新增代碼</button>
        <button class="btn btn-outline" :disabled="loading" @click="load"><RefreshCw :size="14" /></button>
      </div>
    </div>

    <div class="table-wrap">
      <div v-if="loading" class="state-msg">載入中...</div>
      <div v-else-if="list.length === 0" class="state-msg">尚無里程代碼</div>
      <table v-else class="data-table">
        <thead>
          <tr>
            <th>代碼</th>
            <th>說明</th>
            <th style="text-align:right">里程數</th>
            <th style="text-align:right">使用上限</th>
            <th style="text-align:right">已使用</th>
            <th style="text-align:center">狀態</th>
            <th>到期日</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in list" :key="item.id">
            <td><code style="background:#f1f5f9;padding:2px 8px;border-radius:4px;font-size:0.88rem">{{ item.code }}</code></td>
            <td class="td-sub">{{ item.description || '-' }}</td>
            <td class="td-num">{{ item.miles_amount.toLocaleString() }}</td>
            <td class="td-num">{{ item.usage_limit ?? '無限' }}</td>
            <td class="td-num">{{ item.used_count }}</td>
            <td style="text-align:center">
              <span :class="['badge', item.is_active == 1 ? 'badge-green' : 'badge-red']">
                {{ item.is_active == 1 ? '啟用' : '停用' }}
              </span>
            </td>
            <td style="font-size:0.82rem">{{ formatExpiresAt(item.expires_at) }}</td>
            <td class="td-actions">
              <button class="btn btn-sm btn-outline" @click="openForm(item)">編輯</button>
              <button class="btn btn-sm btn-danger" @click="remove(item.id)">刪除</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="panel" style="margin-top:1rem">
    <div class="panel-header">
      <span class="panel-title"></span>
      <div style="display:flex;gap:0.5rem;align-items:center">
        <input v-model="recordFilters.code" class="f-input" style="width:180px" placeholder="搜尋代碼" @keyup.enter="loadRecords" />
        <input v-model="recordFilters.user_id" class="f-input" style="width:140px" placeholder="用戶 ID" @keyup.enter="loadRecords" />
        <button class="btn btn-outline" :disabled="recordsLoading" @click="loadRecords"><RefreshCw :size="14" /></button>
      </div>
    </div>

    <div class="table-wrap">
      <div v-if="recordsLoading" class="state-msg">載入中...</div>
      <div v-else-if="records.length === 0" class="state-msg">尚無使用紀錄</div>
      <table v-else class="data-table">
        <thead>
          <tr>
            <th>#</th>
            <th>代碼</th>
            <th style="text-align:right">用戶 ID</th>
            <th>用戶資訊</th>
            <th style="text-align:right">兌換里程</th>
            <th>時間</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="record in records" :key="record.id">
            <td class="td-muted">{{ record.id }}</td>
            <td><code style="background:#f1f5f9;padding:2px 8px;border-radius:4px;font-size:0.88rem">{{ record.code || '-' }}</code></td>
            <td class="td-num">{{ record.user_id }}</td>
            <td>
              <div class="td-name">{{ record.full_name || '-' }}</div>
              <div class="td-sub">{{ record.email || '-' }}</div>
            </td>
            <td class="td-num">{{ Number(record.amount || 0).toLocaleString() }}</td>
            <td class="td-sub">{{ formatDateTime(record.created_at) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal -->
  <div v-if="form.show" class="modal-overlay" @click.self="form.show = false">
    <div class="modal-box" style="max-width:480px">
      <div class="modal-hd">
        <span>{{ form.id ? '編輯里程代碼' : '新增里程代碼' }}</span>
        <button class="modal-x" @click="form.show = false">✕</button>
      </div>
      <div class="modal-bd">
        <label class="f-label">代碼 *</label>
        <input v-model="form.code" class="f-input" placeholder="例：BONUS2025" :disabled="!!form.id" />

        <label class="f-label" style="margin-top:0.75rem">說明</label>
        <input v-model="form.description" class="f-input" placeholder="可留空" />

        <label class="f-label" style="margin-top:0.75rem">回饋里程數 *</label>
        <input v-model.number="form.miles_amount" type="number" min="1" class="f-input" placeholder="例：500" />

        <label class="f-label" style="margin-top:0.75rem">使用上限（留空 = 無限）</label>
        <input v-model="form.usage_limit" type="number" min="1" class="f-input" placeholder="留空表示無限制" />

        <label class="f-label" style="margin-top:0.75rem">到期日（留空 = 永久）</label>
        <input v-model="form.expires_at" type="date" class="f-input" />

        <label class="f-label" style="margin-top:0.75rem">狀態</label>
        <select v-model="form.is_active" class="f-input">
          <option :value="1">啟用</option>
          <option :value="0">停用</option>
        </select>

        <div v-if="formError" class="f-error">{{ formError }}</div>
      </div>
      <div class="modal-ft">
        <button class="btn btn-outline" @click="form.show = false">取消</button>
        <button class="btn btn-primary" :disabled="saving" @click="save">
          {{ saving ? '儲存中...' : '儲存' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Plus, RefreshCw } from 'lucide-vue-next'

const BASE = '/api/v1/admin-panel/mileage-codes'

const list      = ref([])
const loading   = ref(false)
const saving    = ref(false)
const formError = ref('')
const records = ref([])
const recordsLoading = ref(false)
const recordFilters = ref({ code: '', user_id: '' })

const defaultForm = () => ({
  show: false, id: null,
  code: '', description: '', miles_amount: 500,
  usage_limit: '', expires_at: '', is_active: 1,
})
const form = ref(defaultForm())

const load = async () => {
  loading.value = true
  try {
    const res  = await fetch(BASE)
    const data = await res.json()
    list.value = data.items || []
  } finally {
    loading.value = false
  }
}

const loadRecords = async () => {
  recordsLoading.value = true
  try {
    const params = new URLSearchParams()
    if (recordFilters.value.code.trim()) params.set('code', recordFilters.value.code.trim())
    if (recordFilters.value.user_id.trim()) params.set('user_id', recordFilters.value.user_id.trim())
    const qs = params.toString() ? `?${params.toString()}` : ''
    const res = await fetch(`/api/v1/admin-panel/mileage-code-records${qs}`)
    const data = await res.json()
    records.value = data.items || []
  } finally {
    recordsLoading.value = false
  }
}

const openForm = (item = null) => {
  formError.value = ''
  if (item) {
    form.value = {
      show: true, id: item.id,
      code: item.code,
      description: item.description || '',
      miles_amount: item.miles_amount,
      usage_limit: item.usage_limit ?? '',
      expires_at: normalizeExpiresAt(item.expires_at),
      is_active: item.is_active,
    }
  } else {
    form.value = defaultForm()
    form.value.show = true
  }
}

const normalizeExpiresAt = (value) => {
  if (!value || value === '0000-00-00 00:00:00') return ''
  return value.slice(0, 10)
}

const formatExpiresAt = (value) => {
  if (!value || value === '0000-00-00 00:00:00') return '永久'
  return value.slice(0, 10)
}

const formatDateTime = (value) => {
  if (!value) return '-'
  return value.replace('T', ' ').slice(0, 19)
}

const save = async () => {
  formError.value = ''
  if (!form.value.code.trim()) { formError.value = '請輸入代碼'; return }
  if (!form.value.miles_amount || form.value.miles_amount <= 0) { formError.value = '里程數必須大於 0'; return }

  saving.value = true
  try {
    const payload = {
      code:         form.value.code.trim(),
      description:  form.value.description,
      miles_amount: form.value.miles_amount,
      usage_limit:  form.value.usage_limit !== '' ? form.value.usage_limit : '',
      expires_at:   form.value.expires_at || null,
      is_active:    form.value.is_active,
    }
    const url    = form.value.id ? `${BASE}/${form.value.id}` : BASE
    const method = form.value.id ? 'PUT' : 'POST'
    const res    = await fetch(url, { method, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) })
    const data   = await res.json()
    if (!res.ok) { formError.value = data.message || '發生錯誤'; return }
    form.value.show = false
    await load()
  } finally {
    saving.value = false
  }
}

const remove = async (id) => {
  if (!confirm('確定刪除此代碼？')) return
  await fetch(`${BASE}/${id}`, { method: 'DELETE' })
  await load()
}

onMounted(async () => {
  await Promise.all([load(), loadRecords()])
})
</script>
