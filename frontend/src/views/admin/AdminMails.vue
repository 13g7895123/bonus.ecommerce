<template>
  <div class="panel">
    <div class="panel-header">
      <span class="panel-title"></span>
      <div style="display:flex;gap:0.5rem;align-items:center">
        <button class="btn btn-primary" @click="openForm()"><Plus :size="14" /> 新增信件</button>
        <button class="btn btn-outline" :disabled="loading" @click="loadMails"><RefreshCw :size="14" /></button>
      </div>
    </div>

    <div class="table-wrap">
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
    <div class="modal-box" style="max-width:480px">
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
            @focus="send.dropdownOpen = true"
          />
          <div v-if="send.dropdownOpen && send.filtered.length" class="user-dropdown">
            <div
              v-for="u in send.filtered"
              :key="u.id"
              class="user-option"
              @mousedown.prevent="selectUser(u)"
            >
              <span class="user-email">{{ u.email }}</span>
              <span v-if="u.full_name" class="user-name">{{ u.full_name }}</span>
            </div>
          </div>
          <div v-if="send.dropdownOpen && send.search && !send.filtered.length" class="user-dropdown">
            <div class="user-option no-result">無符合結果</div>
          </div>
        </div>
        <div v-if="send.selectedUser" class="selected-user-tag">
          已選：{{ send.selectedUser.email }}{{ send.selectedUser.full_name ? ` (${send.selectedUser.full_name})` : '' }}
          <button class="clear-btn" @click="clearUser">✕</button>
        </div>
      </div>
      <div class="modal-ft">
        <button class="btn btn-outline" @click="send.show = false">取消</button>
        <button class="btn btn-primary" :disabled="send.submitting || !send.selectedUser" @click="submitSend">
          {{ send.submitting ? '發送中...' : '發送' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Plus, RefreshCw } from 'lucide-vue-next'

const loading = ref(false)
const mails   = ref([])
const allUsers = ref([])

const form = ref({
  show: false, id: null, subject: '', content: '', is_active: 1, sort_order: 0, submitting: false,
})

const send = ref({
  show: false, mailId: null, subject: '',
  search: '', filtered: [], dropdownOpen: false,
  selectedUser: null, submitting: false,
})

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
    selectedUser: null, submitting: false,
  }
}

const filterUsers = () => {
  const q = send.value.search.trim().toLowerCase()
  if (!q) {
    send.value.filtered = allUsers.value.slice(0, 20)
  } else {
    send.value.filtered = allUsers.value
      .filter(u =>
        (u.email || '').toLowerCase().includes(q) ||
        (u.full_name || '').toLowerCase().includes(q)
      )
      .slice(0, 20)
  }
  send.value.dropdownOpen = true
}

const selectUser = (user) => {
  send.value.selectedUser = user
  send.value.search = user.email
  send.value.dropdownOpen = false
}

const clearUser = () => {
  send.value.selectedUser = null
  send.value.search = ''
}

const submitSend = async () => {
  if (!send.value.selectedUser) return
  send.value.submitting = true
  try {
    const res = await fetch(`/api/v1/admin-panel/mails/${send.value.mailId}/send`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: send.value.selectedUser.id }),
    })
    if (res.ok) {
      send.value.show = false
      alert('信件已發送')
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

.selected-user-tag {
  margin-top: 0.5rem;
  padding: 0.4rem 0.75rem;
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  border-radius: 6px;
  font-size: 0.875rem;
  color: #1d4ed8;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.clear-btn {
  background: none;
  border: none;
  cursor: pointer;
  color: #6b7280;
  font-size: 0.85rem;
  padding: 0 0.25rem;
  line-height: 1;
}

.clear-btn:hover {
  color: #ef4444;
}
</style>
