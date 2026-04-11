<template>
  <div class="panel">
    <div class="panel-header">
      <span class="panel-title">銀行清單管理</span>
      <button class="btn btn-primary" @click="saveBanks" :disabled="saving">
        {{ saving ? '儲存中...' : '儲存' }}
      </button>
    </div>

    <div class="panel-body">
      <p class="hint-text">設定提款申請時的銀行下拉選單，每行一個銀行名稱。</p>
      <textarea
        v-model="banksText"
        class="banks-textarea"
        placeholder="臺灣銀行&#10;土地銀行&#10;合作金庫&#10;..."
        rows="20"
      ></textarea>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const banksText = ref('')
const saving    = ref(false)

const loadBanks = async () => {
  try {
    const res = await fetch('/api/v1/admin-panel/config/bank_list')
    if (res.ok) {
      const data = await res.json()
      const raw = data.value
      if (raw) {
        const list = typeof raw === 'string' ? JSON.parse(raw) : raw
        banksText.value = Array.isArray(list) ? list.join('\n') : ''
      }
    }
  } catch {}
  if (!banksText.value) {
    banksText.value = [
      '臺灣銀行', '土地銀行', '合作金庫', '第一銀行', '華南銀行',
      '彰化銀行', '兆豐銀行', '台灣企銀', '玉山銀行', '國泰世華銀行',
      '台北富邦', '富邦銀行', '中國信託', '聯邦銀行', '永豐銀行',
      '台新銀行', '遠東銀行', '元大銀行', '星展銀行', '渣打銀行',
      '匯豐銀行', '花旗銀行', '郵政劃撥', '農業金庫', '新光銀行'
    ].join('\n')
  }
}

const saveBanks = async () => {
  saving.value = true
  try {
    const list = banksText.value
      .split('\n')
      .map(s => s.trim())
      .filter(s => s.length > 0)
    const res = await fetch('/api/v1/admin-panel/config/bank_list', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ value: JSON.stringify(list) }),
    })
    if (res.ok) {
      alert('銀行清單已儲存')
    } else {
      alert('儲存失敗')
    }
  } catch (e) {
    alert('儲存失敗：' + e.message)
  } finally {
    saving.value = false
  }
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
  margin: 0;
}

.banks-textarea {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.9rem;
  color: #1e293b;
  resize: vertical;
  line-height: 1.7;
  font-family: inherit;
  box-sizing: border-box;
}

.banks-textarea:focus {
  outline: none;
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}
</style>
