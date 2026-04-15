<template>
  <div class="panel">
    <div class="panel-header">
      <span class="panel-title">公告管理</span>
      <div style="display:flex;gap:0.5rem">
        <button class="btn btn-primary" @click="openForm()"><Plus :size="14" />新增公告</button>
        <button class="btn btn-outline" :disabled="loading" @click="loadList"><RefreshCw :size="14" /></button>
      </div>
    </div>
    <div class="table-wrap">
      <div v-if="loading" class="state-msg">載入中...</div>
      <div v-else-if="list.length === 0" class="state-msg">尚無公告</div>
      <table v-else class="data-table">
        <thead>
          <tr>
            <th>標題</th>
            <th style="width:160px">發布時間</th>
            <th style="width:80px;text-align:center">狀態</th>
            <th style="width:120px;text-align:center">操作</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in list" :key="item.id">
            <td class="td-name">{{ item.title }}</td>
            <td>{{ formatDate(item.published_at) }}</td>
            <td style="text-align:center">
              <span :class="['badge', item.is_published == 1 ? 'badge-green' : 'badge-gray']">
                {{ item.is_published == 1 ? '已發布' : '草稿' }}
              </span>
            </td>
            <td class="td-actions">
              <button class="btn btn-sm btn-outline" @click="openForm(item)">編輯</button>
              <button class="btn btn-sm btn-danger" @click="deleteItem(item.id)">刪除</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- 新增/編輯 Modal -->
  <div v-if="form.show" class="modal-overlay" @click.self="form.show = false">
    <div class="modal-box" style="max-width:560px;max-height:90vh;overflow-y:auto">
      <div class="modal-hd">
        <span>{{ form.id ? '編輯公告' : '新增公告' }}</span>
        <button class="modal-x" @click="form.show = false">✕</button>
      </div>
      <div class="modal-bd">
        <label class="f-label">標題 *</label>
        <input v-model="form.title" class="f-input" placeholder="公告標題" />

        <label class="f-label" style="margin-top:0.75rem">內容</label>
        <textarea v-model="form.content" class="f-textarea" rows="8" placeholder="公告詳細內容..."></textarea>

        <div style="display:flex;gap:1rem;margin-top:0.75rem;align-items:flex-end">
          <div style="flex:1">
            <label class="f-label">發布時間</label>
            <input v-model="form.published_at" class="f-input" type="datetime-local" />
          </div>
          <label style="display:flex;align-items:center;gap:6px;cursor:pointer;padding-bottom:0.5rem">
            <input type="checkbox" v-model="form.is_published" :true-value="1" :false-value="0" style="width:16px;height:16px" />
            <span class="f-label" style="margin:0">立即發布</span>
          </label>
        </div>
      </div>
      <div class="modal-ft">
        <button class="btn btn-outline" @click="form.show = false">取消</button>
        <button class="btn btn-primary" :disabled="form.submitting" @click="submitForm">
          {{ form.submitting ? '處理中...' : (form.id ? '儲存' : '新增') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RefreshCw, Plus } from 'lucide-vue-next'

const list    = ref([])
const loading = ref(false)
const form    = ref({ show: false, id: null, title: '', content: '', is_published: 1, published_at: '', submitting: false })

const formatDate = (dt) => {
  if (!dt) return '—'
  return dt.replace('T', ' ').substring(0, 16)
}

const toLocalDatetimeInput = (dt) => {
  if (!dt) return ''
  return dt.replace(' ', 'T').substring(0, 16)
}

const loadList = async () => {
  loading.value = true
  try {
    const res  = await fetch('/api/v1/admin-panel/announcements')
    const data = await res.json()
    list.value = data.items || []
  } finally {
    loading.value = false
  }
}

const openForm = (item = null) => {
  if (item) {
    form.value = {
      show: true, submitting: false,
      id: item.id,
      title: item.title,
      content: item.content || '',
      is_published: Number(item.is_published),
      published_at: toLocalDatetimeInput(item.published_at),
    }
  } else {
    const now = new Date()
    const local = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().substring(0, 16)
    form.value = { show: true, submitting: false, id: null, title: '', content: '', is_published: 1, published_at: local }
  }
}

const submitForm = async () => {
  const f = form.value
  if (!f.title.trim()) { alert('請填寫標題'); return }
  f.submitting = true
  try {
    const url    = f.id ? `/api/v1/admin-panel/announcements/${f.id}` : '/api/v1/admin-panel/announcements'
    const method = f.id ? 'PUT' : 'POST'
    const res    = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        title:        f.title,
        content:      f.content,
        is_published: f.is_published,
        published_at: f.published_at ? f.published_at.replace('T', ' ') + ':00' : null,
      }),
    })
    if (!res.ok) { const d = await res.json(); alert(d.message || '操作失敗'); return }
    f.show = false
    await loadList()
  } finally {
    f.submitting = false
  }
}

const deleteItem = async (id) => {
  if (!confirm('確定要刪除此公告嗎？')) return
  await fetch(`/api/v1/admin-panel/announcements/${id}`, { method: 'DELETE' })
  await loadList()
}

onMounted(loadList)
</script>

<style scoped>
.f-textarea {
  width: 100%;
  padding: 0.6rem 0.75rem;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.9rem;
  color: #1e293b;
  resize: vertical;
  line-height: 1.6;
  font-family: inherit;
  box-sizing: border-box;
  margin-top: 0.25rem;
}

.f-textarea:focus {
  outline: none;
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}
</style>
