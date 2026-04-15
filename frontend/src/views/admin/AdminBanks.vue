<template>
  <div class="panel">
    <div class="panel-header">
      <span class="panel-title">銀行清單管理</span>
      <div style="display:flex;gap:0.5rem">
        <button class="btn btn-primary" @click="openAddModal">＋ 新增銀行</button>
      </div>
    </div>

    <div class="panel-body">
      <p class="hint-text">設定提款申請時的銀行下拉選單。</p>
      <div v-if="bankList.length === 0" class="state-empty">尚無銀行資料</div>
      <table v-else class="banks-table">
        <thead>
          <tr><th>銀行名稱</th><th style="width:160px;text-align:center">操作</th></tr>
        </thead>
        <tbody>
          <tr v-for="(bank, index) in bankList" :key="index">
            <td>{{ bank }}</td>
            <td style="text-align:center">
              <button class="btn btn-sm btn-outline" @click="openEditModal(index)">編輯</button>
              <button class="btn btn-sm btn-danger" style="margin-left:0.5rem" @click="deleteBank(index)">刪除</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- 新增/編輯 Modal -->
  <Teleport to="body">
    <div v-if="modal.show"
      style="position:fixed;inset:0;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:9999;padding:1rem"
      @click.self="modal.show = false">
      <div class="modal-box" style="max-width:380px;width:100%;background:#fff;border-radius:12px;padding:1.5rem;box-shadow:0 20px 60px rgba(0,0,0,0.3)">
        <div class="modal-hd">
          <span>{{ modal.editIndex >= 0 ? '編輯銀行' : '新增銀行' }}</span>
          <button class="modal-x" @click="modal.show = false">✕</button>
        </div>
        <div class="modal-bd">
          <label class="f-label">銀行名稱 *</label>
          <input v-model="modal.name" class="f-input" placeholder="例：玉山銀行" @keyup.enter="confirmModal" />
        </div>
        <div class="modal-ft">
          <button class="btn btn-outline" @click="modal.show = false">取消</button>
          <button class="btn btn-primary" @click="confirmModal">{{ modal.editIndex >= 0 ? '儲存' : '新增' }}</button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const bankList = ref([])
const saving   = ref(false)
const modal    = ref({ show: false, name: '', editIndex: -1 })

const DEFAULT_BANKS = [
  '臺灣銀行', '土地銀行', '合作金庫', '第一銀行', '華南銀行',
  '彰化銀行', '兆豐銀行', '台灣企銀', '玉山銀行', '國泰世華銀行',
  '台北富邦', '富邦銀行', '中國信託', '聯邦銀行', '永豐銀行',
  '台新銀行', '遠東銀行', '元大銀行', '星展銀行', '渣打銀行',
  '匯豐銀行', '花旗銀行', '郵政劃撥', '農業金庫', '新光銀行'
]

const loadBanks = async () => {
  try {
    const res = await fetch('/api/v1/admin-panel/config/bank_list')
    if (res.ok) {
      const data = await res.json()
      const raw = data.value
      if (raw) {
        const list = typeof raw === 'string' ? JSON.parse(raw) : raw
        if (Array.isArray(list) && list.length > 0) {
          bankList.value = list
          return
        }
      }
    }
  } catch {}
  bankList.value = [...DEFAULT_BANKS]
}

const persistBanks = async () => {
  saving.value = true
  try {
    const res = await fetch('/api/v1/admin-panel/config/bank_list', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ value: JSON.stringify(bankList.value) }),
    })
    if (!res.ok) alert('儲存失敗')
  } catch (e) {
    alert('儲存失敗：' + e.message)
  } finally {
    saving.value = false
  }
}

const openAddModal = () => {
  modal.value = { show: true, name: '', editIndex: -1 }
}

const openEditModal = (index) => {
  modal.value = { show: true, name: bankList.value[index], editIndex: index }
}

const confirmModal = async () => {
  const name = modal.value.name.trim()
  if (!name) { alert('請輸入銀行名稱'); return }
  if (modal.value.editIndex >= 0) {
    bankList.value[modal.value.editIndex] = name
  } else {
    bankList.value.push(name)
  }
  modal.value.show = false
  await persistBanks()
}

const deleteBank = async (index) => {
  if (!confirm(`確定要刪除「${bankList.value[index]}」嗎？`)) return
  bankList.value.splice(index, 1)
  await persistBanks()
}

onMounted(loadBanks)
</script>

<style scoped>
.panel {
  background: #fff;
  border-radius: 12px;
  padding: 1.5rem;
}

.panel-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.25rem;
}

.panel-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1e293b;
}

.panel-body {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.hint-text {
  font-size: 0.875rem;
  color: #64748b;
  margin: 0 0 0.75rem;
}

.state-empty {
  text-align: center;
  padding: 2rem;
  color: #94a3b8;
  font-size: 0.9rem;
}

.banks-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.9rem;
}

.banks-table th,
.banks-table td {
  padding: 0.6rem 0.75rem;
  border-bottom: 1px solid #f1f5f9;
  text-align: left;
}

.banks-table thead th {
  background: #f8fafc;
  font-weight: 600;
  color: #475569;
  font-size: 0.8rem;
}
</style>
