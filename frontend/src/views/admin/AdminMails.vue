<template>
  <div class="panel">
    <div class="panel-header">
      <span class="panel-title"></span>
      <div style="display:flex;gap:0.5rem;align-items:center">
        <button v-if="activeTab === 'mails'" class="btn btn-primary" @click="openForm()"><Plus :size="14" /> 新增信件</button>
        <button class="btn btn-outline" :disabled="loading" @click="activeTab === 'mails' ? loadMails() : loadRecords()"><RefreshCw :size="14" /></button>
      </div>
    </div>

    <!-- 分頁 Tab -->
    <div class="tab-bar">
      <button :class="['tab-btn', activeTab === 'mails' ? 'active' : '']" @click="switchTab('mails')">信件列表</button>
      <button :class="['tab-btn', activeTab === 'records' ? 'active' : '']" @click="switchTab('records')">發送紀錄</button>
    </div>

    <!-- 信件列表 -->
    <div v-if="activeTab === 'mails'" class="table-wrap">
      <div v-if="loading" class="state-msg">載入中...</div>
      <div v-else-if="mails.length === 0" class="state-msg">尚無信件</div>
      <table v-else class="data-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>主旨</th>
            <th>狀態</th>
            <th>排序</th>
            <th>建立時間</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="mail in mails" :key="mail.id">
            <td>{{ mail.id }}</td>
            <td class="td-name">{{ mail.subject }}</td>
            <td>
              <span :class="['badge', mail.is_active == 1 ? 'badge-green' : 'badge-red']">
                {{ mail.is_active == 1 ? '啟用' : '停用' }}
              </span>
            </td>
            <td>{{ mail.sort_order }}</td>
            <td>{{ mail.created_at }}</td>
            <td class="td-actions">
              <button class="btn btn-sm btn-outline" @click="openForm(mail)">編輯</button>
              <button class="btn btn-sm btn-primary" @click="openSend(mail)">發送</button>
              <button class="btn btn-sm btn-danger" @click="deleteMail(mail.id)">刪除</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- 發送紀錄 -->
    <div v-if="activeTab === 'records'" class="table-wrap">
      <!-- 篩選列 -->
      <div class="records-filter">
        <select v-model="records.mailFilter" class="f-input" style="max-width:220px" @change="loadRecords">
          <option value="">所有信件</option>
          <option v-for="m in mails" :key="m.id" :value="m.id">{{ m.subject }}</option>
        </select>
      </div>
      <div v-if="records.loading" class="state-msg">載入中...</div>
      <div v-else-if="records.items.length === 0" class="state-msg">尚無發送紀錄</div>
      <table v-else class="data-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>信件主旨</th>
            <th>收件帳號</th>
            <th>姓名</th>
            <th>已讀</th>
            <th>發送時間</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="r in records.items" :key="r.id">
            <td>{{ r.id }}</td>
            <td class="td-name">{{ r.subject }}</td>
            <td>{{ r.email }}</td>
            <td>{{ r.full_name || '-' }}</td>
            <td>
              <span :class="['badge', r.is_read == 1 ? 'badge-green' : 'badge-red']">
                {{ r.is_read == 1 ? '已讀' : '未讀' }}
              </span>
            </td>
            <td>{{ r.created_at }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- 建立/編輯 Modal -->
  <div v-if="form.show" class="modal-overlay" @click.self="form.show = false">
    <div class="modal-box" style="max-width:520px">
      <div class="modal-hd">
        <span>{{ form.id ? '編輯信件' : '新增信件' }}</span>
        <button class="modal-x" @click="form.show = false">✕</button>
      </div>
      <div class="modal-bd">
        <label class="f-label">主旨 *</label>
        <input v-model="form.subject" class="f-input" placeholder="信件主旨" />

        <label class="f-label" style="margin-top:0.75rem">內容 *</label>
        <textarea v-model="form.content" class="f-input" rows="8" placeholder="信件內容..." style="resize:vertical;line-height:1.6"></textarea>

        <div style="display:flex;gap:1rem;margin-top:0.75rem;align-items:flex-end">
          <div style="flex:1">
            <label class="f-label">排序</label>
            <input v-model.number="form.sort_order" class="f-input" type="number" min="0" />
          </div>
          <label style="display:flex;align-items:center;gap:6px;cursor:pointer;padding-bottom:0.5rem">
            <input type="checkbox" v-model="form.is_active" :true-value="1" :false-value="0" style="width:16px;height:16px" />
            <span class="f-label" style="margin:0">啟用</span>
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
  <!-- 發送信件給使用者 Modal -->
  <div v-if="send.show" class="modal-overlay" @click.self="send.show = false">
    <div class="modal-box" style="max-width:520px">
      <div class="modal-hd">
        <span>發送信件給使用者</span>
        <button class="modal-x" @click="send.show = false">✕</button>
      </div>
      <div class="modal-bd">
        <p style="margin:0 0 0.75rem;font-size:0.9rem;color:#555">
          信件：<strong>{{ send.subject }}</strong>
        </p>
        <label class="f-label">選擇使用者 *</label>
        <div class="user-select-wrap">
          <input
            v-model="send.search"
            class="f-input"
            placeholder="搜尋帳號或姓名..."
            @input="filterUsers"
            @focus="openDropdown"
            @blur="send.dropdownOpen = false"
          />
          <div v-if="send.dropdownOpen" class="user-dropdown" @mousedown.prevent>
            <div v-if="send.filtered.length" class="user-option select-all-option" @click="toggleSelectAll">
              <input type="checkbox" :checked="isAllSelected" :indeterminate.prop="isIndeterminate" style="width:15px;height:15px;cursor:pointer" />
              <span class="user-email">全選（{{ send.filtered.length }} 筆）</span>
            </div>
            <div v-if="send.filtered.length" class="dropdown-divider"></div>
            <div
              v-for="u in send.filtered"
              :key="u.id"
              class="user-option"
              @click="toggleUser(u)"
            >
              <input type="checkbox" :checked="isSelected(u.id)" style="width:15px;height:15px;cursor:pointer" />
              <span class="user-email">{{ u.email }}</span>
              <span v-if="u.full_name" class="user-name">{{ u.full_name }}</span>
            </div>
            <div v-if="!send.filtered.length" class="user-option no-result">無符合結果</div>
          </div>
        </div>
        <!-- 已選 tags -->
        <div v-if="send.selectedUsers.length" class="selected-tags-wrap">
          <div class="selected-count">已選 {{ send.selectedUsers.length }} 位使用者</div>
          <div class="tags-list">
            <span v-for="u in send.selectedUsers" :key="u.id" class="user-tag">
              {{ u.email }}
              <button class="tag-remove" @click="toggleUser(u)">✕</button>
            </span>
          </div>
        </div>
      </div>
      <div class="modal-ft">
        <button class="btn btn-outline" @click="send.show = false">取消</button>
        <button class="btn btn-primary" :disabled="send.submitting || !send.selectedUsers.length" @click="submitSend">
          {{ send.submitting ? '發送中...' : `發送（${send.selectedUsers.length}）` }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Plus, RefreshCw } from 'lucide-vue-next'

const loading = ref(false)
const mails   = ref([])
const allUsers = ref([])
const activeTab = ref('mails')

const records = ref({ loading: false, items: [], mailFilter: '' })

const switchTab = (tab) => {
  activeTab.value = tab
  if (tab === 'records') loadRecords()
  if (tab === 'mails') loadMails()
}

const loadRecords = async () => {
  records.value.loading = true
  try {
    const mailId = records.value.mailFilter
    const url = mailId
      ? `/api/v1/admin-panel/mails/${mailId}/records`
      : '/api/v1/admin-panel/mails/records'
    const res = await fetch(url)
    if (res.ok) {
      const data = await res.json()
      records.value.items = data.items || []
    }
  } finally {
    records.value.loading = false
  }
}

const form = ref({
  show: false, id: null, subject: '', content: '', is_active: 1, sort_order: 0, submitting: false,
})

const send = ref({
  show: false, mailId: null, subject: '',
  search: '', filtered: [], dropdownOpen: false,
  selectedUsers: [], submitting: false,
})

const isSelected = (id) => send.value.selectedUsers.some(u => u.id === id)

const isAllSelected = computed(() =>
  send.value.filtered.length > 0 &&
  send.value.filtered.every(u => isSelected(u.id))
)

const isIndeterminate = computed(() =>
  send.value.filtered.some(u => isSelected(u.id)) && !isAllSelected.value
)

const loadMails = async () => {
  loading.value = true
  try {
    const res = await fetch('/api/v1/admin-panel/mails')
    if (res.ok) {
      const data = await res.json()
      mails.value = data.items || []
    }
  } finally {
    loading.value = false
  }
}

const openForm = (mail = null) => {
  form.value = {
    show: true,
    id:         mail?.id ?? null,
    subject:    mail?.subject ?? '',
    content:    mail?.content ?? '',
    is_active:  mail?.is_active ?? 1,
    sort_order: mail?.sort_order ?? 0,
    submitting: false,
  }
}

const submitForm = async () => {
  if (!form.value.subject.trim() || !form.value.content.trim()) {
    alert('請填寫主旨與內容')
    return
  }
  form.value.submitting = true
  try {
    const url    = form.value.id ? `/api/v1/admin-panel/mails/${form.value.id}` : '/api/v1/admin-panel/mails'
    const method = form.value.id ? 'PUT' : 'POST'
    const res = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        subject:    form.value.subject,
        content:    form.value.content,
        is_active:  form.value.is_active,
        sort_order: form.value.sort_order,
      }),
    })
    if (res.ok) {
      form.value.show = false
      await loadMails()
    } else {
      const d = await res.json()
      alert(d.message || '操作失敗')
    }
  } finally {
    form.value.submitting = false
  }
}

const deleteMail = async (id) => {
  if (!confirm('確定要刪除此信件？')) return
  const res = await fetch(`/api/v1/admin-panel/mails/${id}`, { method: 'DELETE' })
  if (res.ok) {
    await loadMails()
  } else {
    alert('刪除失敗')
  }
}

const loadUsers = async () => {
  try {
    const res = await fetch('/api/v1/admin-panel/users?limit=500')
    if (res.ok) {
      const data = await res.json()
      allUsers.value = data.items || []
    }
  } catch {}
}

const openSend = (mail) => {
  send.value = {
    show: true, mailId: mail.id, subject: mail.subject,
    search: '', filtered: [], dropdownOpen: false,
    selectedUsers: [], submitting: false,
  }
}

const openDropdown = () => {
  if (!send.value.filtered.length) {
    send.value.filtered = allUsers.value.slice(0, 50)
  }
  send.value.dropdownOpen = true
}

const filterUsers = () => {
  const q = send.value.search.trim().toLowerCase()
  if (!q) {
    send.value.filtered = allUsers.value.slice(0, 50)
  } else {
    send.value.filtered = allUsers.value
      .filter(u =>
        (u.email || '').toLowerCase().includes(q) ||
        (u.full_name || '').toLowerCase().includes(q)
      )
      .slice(0, 50)
  }
  send.value.dropdownOpen = true
}

const toggleUser = (user) => {
  const idx = send.value.selectedUsers.findIndex(u => u.id === user.id)
  if (idx === -1) {
    send.value.selectedUsers.push(user)
  } else {
    send.value.selectedUsers.splice(idx, 1)
  }
}

const toggleSelectAll = () => {
  if (isAllSelected.value) {
    // 取消全選（只取消目前 filtered 中的）
    const filteredIds = new Set(send.value.filtered.map(u => u.id))
    send.value.selectedUsers = send.value.selectedUsers.filter(u => !filteredIds.has(u.id))
  } else {
    // 全選 filtered 中尚未選取的
    const selectedIds = new Set(send.value.selectedUsers.map(u => u.id))
    send.value.filtered.forEach(u => {
      if (!selectedIds.has(u.id)) send.value.selectedUsers.push(u)
    })
  }
}

const submitSend = async () => {
  if (!send.value.selectedUsers.length) return
  send.value.submitting = true
  try {
    const res = await fetch(`/api/v1/admin-panel/mails/${send.value.mailId}/send`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_ids: send.value.selectedUsers.map(u => u.id) }),
    })
    if (res.ok) {
      send.value.show = false
      alert(`信件已發送給 ${send.value.selectedUsers.length} 位使用者`)
    } else {
      const d = await res.json()
      alert(d.message || '發送失敗')
    }
  } finally {
    send.value.submitting = false
  }
}

onMounted(() => {
  loadMails()
  loadUsers()
})
</script>

<style scoped>
.tab-bar {
  display: flex;
  gap: 0;
  border-bottom: 2px solid #e5e7eb;
  margin-bottom: 0;
}

.tab-btn {
  padding: 0.6rem 1.25rem;
  border: none;
  background: none;
  cursor: pointer;
  font-size: 0.9rem;
  color: #6b7280;
  border-bottom: 2px solid transparent;
  margin-bottom: -2px;
  transition: color 0.15s, border-color 0.15s;
}

.tab-btn:hover {
  color: #111827;
}

.tab-btn.active {
  color: #1d4ed8;
  border-bottom-color: #1d4ed8;
  font-weight: 600;
}

.records-filter {
  padding: 0.75rem 0 0.5rem;
  display: flex;
  gap: 0.5rem;
}

.user-select-wrap {
  position: relative;
}

.user-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: #fff;
  border: 1px solid #d1d5db;
  border-top: none;
  border-radius: 0 0 6px 6px;
  max-height: 200px;
  overflow-y: auto;
  z-index: 100;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.user-option {
  padding: 0.5rem 0.75rem;
  cursor: pointer;
  display: flex;
  gap: 0.5rem;
  align-items: center;
  font-size: 0.875rem;
}

.user-option:hover {
  background: #f3f4f6;
}

.user-option.no-result {
  color: #9ca3af;
  cursor: default;
}

.user-email {
  font-weight: 500;
  color: #111827;
}

.user-name {
  color: #6b7280;
  font-size: 0.8rem;
}

.select-all-option {
  background: #f9fafb;
  font-weight: 600;
}

.dropdown-divider {
  height: 1px;
  background: #e5e7eb;
  margin: 0;
}

.selected-tags-wrap {
  margin-top: 0.65rem;
}

.selected-count {
  font-size: 0.8rem;
  color: #6b7280;
  margin-bottom: 0.4rem;
}

.tags-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.35rem;
  max-height: 100px;
  overflow-y: auto;
}

.user-tag {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  border-radius: 999px;
  padding: 0.2rem 0.6rem;
  font-size: 0.8rem;
  color: #1d4ed8;
}

.tag-remove {
  background: none;
  border: none;
  cursor: pointer;
  color: #93c5fd;
  font-size: 0.75rem;
  padding: 0;
  line-height: 1;
}

.tag-remove:hover {
  color: #ef4444;
}
</style>
