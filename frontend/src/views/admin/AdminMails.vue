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
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Plus, RefreshCw } from 'lucide-vue-next'

const loading = ref(false)
const mails   = ref([])

const form = ref({
  show: false, id: null, subject: '', content: '', is_active: 1, sort_order: 0, submitting: false,
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

onMounted(loadMails)
</script>
