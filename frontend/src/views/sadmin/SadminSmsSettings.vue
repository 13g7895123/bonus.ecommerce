<template>
  <div class="panel">
    <div class="panel-header">
      <span class="panel-title">簡訊防濫用設定</span>
    </div>

    <!-- 手機號碼唯一性說明 -->
    <div class="content-block">
      <div class="cb-title">手機號碼唯一性</div>
      <div class="cb-desc" style="margin-top:0.35rem">已內建，無需設定。每個手機號碼只能對應一個帳號，若嘗試對已註冊的號碼發送 OTP 驗證碼將被拒絕。</div>
      <div class="rule-tag">
        <span class="rule-dot active"></span> 恆常啟用
      </div>
    </div>

    <div class="divider"></div>

    <!-- IP 速率限制 -->
    <div class="content-block">
      <div class="content-block-header">
        <div>
          <div class="cb-title">IP 速率限制</div>
          <div class="cb-desc" style="margin-top:0.25rem">
            同一 IP 在指定時間窗口內超出發送次數後，將被暫時封鎖
          </div>
        </div>
        <div style="display:flex;gap:0.5rem">
          <button class="btn btn-outline" :disabled="saving" @click="load">
            <RefreshCw :size="13" /> 重新整理
          </button>
          <button class="btn btn-primary" :disabled="saving" @click="save">
            <Save :size="14" /> {{ saving ? '儲存中...' : '儲存設定' }}
          </button>
        </div>
      </div>

      <!-- 啟用 Toggle -->
      <div class="toggle-row">
        <div>
          <div class="tg-label">啟用 IP 速率限制</div>
          <div class="tg-hint">停用後所有 IP 均不受發送次數限制</div>
        </div>
        <button class="toggle-btn" :class="{ on: form.enabled }" @click="form.enabled = !form.enabled">
          <span class="toggle-knob"></span>
        </button>
      </div>

      <!-- 參數設定 -->
      <div class="params-grid" :class="{ 'dimmed': !form.enabled }">
        <div class="param-card">
          <label class="param-label">時間窗口（分鐘）</label>
          <div class="param-input-row">
            <input v-model.number="form.window" type="number" min="1" max="1440" class="param-input" :disabled="!form.enabled" />
            <span class="param-unit">分鐘</span>
          </div>
          <div class="param-hint">計算發送次數的時間範圍</div>
        </div>

        <div class="param-card">
          <label class="param-label">最大發送次數</label>
          <div class="param-input-row">
            <input v-model.number="form.max" type="number" min="1" max="100" class="param-input" :disabled="!form.enabled" />
            <span class="param-unit">次</span>
          </div>
          <div class="param-hint">窗口內超出此次數將封鎖</div>
        </div>

        <div class="param-card">
          <label class="param-label">封鎖時間（分鐘）</label>
          <div class="param-input-row">
            <input v-model.number="form.block" type="number" min="1" max="10080" class="param-input" :disabled="!form.enabled" />
            <span class="param-unit">分鐘</span>
          </div>
          <div class="param-hint">超出上限後的封鎖時長</div>
        </div>
      </div>

      <div class="rule-summary">
        <span class="rule-dot" :class="{ active: form.enabled }"></span>
        <span v-if="form.enabled">
          {{ form.window }} 分鐘內同一 IP 最多發送 {{ form.max }} 次，超出後封鎖 {{ form.block }} 分鐘
        </span>
        <span v-else>IP 速率限制已停用</span>
      </div>
    </div>

    <div class="divider"></div>

    <!-- 封鎖中 IP 清單 -->
    <div class="content-block">
      <div class="content-block-header">
        <div>
          <div class="cb-title">封鎖中的 IP</div>
          <div class="cb-desc" style="margin-top:0.25rem">
            目前因超出發送上限而被封鎖的 IP 清單
          </div>
        </div>
        <div style="display:flex;gap:0.5rem">
          <button class="btn btn-outline" @click="loadBlocked">
            <RefreshCw :size="13" /> 重新整理
          </button>
          <button
            class="btn btn-danger"
            :disabled="clearing"
            @click="clearAll"
          >
            {{ clearing ? '清除中...' : '清除全部速率紀錄' }}
          </button>
        </div>
      </div>

      <div v-if="loadingBlocked" class="state-msg">載入中...</div>
      <div v-else-if="!blockedIps.length" class="empty-block">目前無封鎖中的 IP</div>
      <table v-else class="data-table">
        <thead>
          <tr>
            <th>IP 位址</th>
            <th>封鎖解除時間</th>
            <th>剩餘時間</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in blockedIps" :key="row.ip">
            <td><code>{{ row.ip }}</code></td>
            <td>{{ formatDate(row.blocked_until) }}</td>
            <td>{{ remainingTime(row.blocked_until) }}</td>
            <td>
              <button class="btn btn-outline btn-sm" @click="unblock(row.ip)">解封</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Save, RefreshCw } from 'lucide-vue-next'

const form = ref({ enabled: true, window: 10, max: 3, block: 30 })
const saving        = ref(false)
const blockedIps    = ref([])
const loadingBlocked = ref(false)
const clearing      = ref(false)

const load = async () => {
  try {
    const res  = await fetch('/api/v1/sadmin/sms-security')
    const data = await res.json()
    form.value = {
      enabled: !!data.enabled,
      window:  data.window  ?? 10,
      max:     data.max     ?? 3,
      block:   data.block   ?? 30,
    }
  } catch {
    // 保持預設值
  }
}

const loadBlocked = async () => {
  loadingBlocked.value = true
  try {
    const res  = await fetch('/api/v1/sadmin/sms-security/blocked-ips')
    const data = await res.json()
    blockedIps.value = data.items || []
  } finally {
    loadingBlocked.value = false
  }
}

const save = async () => {
  saving.value = true
  try {
    const res  = await fetch('/api/v1/sadmin/sms-security', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(form.value),
    })
    const data = await res.json()
    if (!res.ok) { alert(data.message || '儲存失敗'); return }
    alert('SMS 安全設定已儲存')
  } catch { alert('儲存失敗，請稍後再試') }
  finally { saving.value = false }
}

const unblock = async (ip) => {
  if (!confirm(`確定要解封 ${ip} 嗎？`)) return
  try {
    const res  = await fetch('/api/v1/sadmin/sms-security/unblock', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ip }),
    })
    const data = await res.json()
    if (!res.ok) { alert(data.message || '解封失敗'); return }
    blockedIps.value = blockedIps.value.filter(r => r.ip !== ip)
    alert(data.message)
  } catch { alert('操作失敗，請稍後再試') }
}

const clearAll = async () => {
  if (!confirm('確定要清除所有速率限制紀錄嗎？（封鎖中的 IP 也會被解封）')) return
  clearing.value = true
  try {
    const res  = await fetch('/api/v1/sadmin/sms-security/rate-limits', { method: 'DELETE' })
    const data = await res.json()
    if (!res.ok) { alert(data.message || '清除失敗'); return }
    alert(data.message)
    blockedIps.value = []
  } catch { alert('清除失敗，請稍後再試') }
  finally { clearing.value = false }
}

const formatDate = (dt) => dt ? new Date(dt).toLocaleString('zh-TW', { hour12: false }) : '-'

const remainingTime = (dt) => {
  if (!dt) return '-'
  const diff = Math.max(0, Math.floor((new Date(dt) - Date.now()) / 1000))
  if (diff === 0)  return '即將解封'
  const m = Math.floor(diff / 60)
  const s = diff % 60
  return m > 0 ? `${m} 分 ${s} 秒` : `${s} 秒`
}

onMounted(() => { load(); loadBlocked() })
</script>

<style scoped>
.cb-title { font-size: 0.95rem; font-weight: 600; color: #1e293b; }
.cb-desc  { font-size: 0.82rem; color: #64748b; }

.rule-tag {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  margin-top: 0.75rem;
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  color: #166534;
  font-size: 0.78rem;
  font-weight: 600;
  padding: 0.25rem 0.75rem;
  border-radius: 999px;
}

.rule-dot {
  display: inline-block;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #94a3b8;
  flex-shrink: 0;
}
.rule-dot.active { background: #22c55e; }

/* Toggle */
.toggle-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 0;
  border-bottom: 1px solid #f1f5f9;
}
.tg-label { font-weight: 600; font-size: 0.9rem; color: #1e293b; }
.tg-hint  { font-size: 0.78rem; color: #94a3b8; margin-top: 0.15rem; }

.toggle-btn {
  position: relative;
  width: 48px;
  height: 26px;
  border-radius: 999px;
  background: #cbd5e1;
  border: none;
  cursor: pointer;
  transition: background 0.2s;
  flex-shrink: 0;
  padding: 0;
}
.toggle-btn.on { background: #3b82f6; }
.toggle-knob {
  position: absolute;
  top: 3px;
  left: 3px;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #fff;
  box-shadow: 0 1px 3px rgba(0,0,0,0.2);
  transition: transform 0.2s;
  display: block;
}
.toggle-btn.on .toggle-knob { transform: translateX(22px); }

/* Params */
.params-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 1rem;
  margin-top: 1rem;
}
.dimmed { opacity: 0.4; pointer-events: none; }

.param-card {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}
.param-label { font-size: 0.8rem; font-weight: 600; color: #475569; }
.param-input-row { display: flex; align-items: center; gap: 0.4rem; }
.param-input {
  width: 80px;
  padding: 0.4rem 0.6rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 6px;
  font-size: 1rem;
  font-weight: 600;
  color: #1e293b;
  background: #fff;
  outline: none;
  text-align: center;
}
.param-input:focus { border-color: #3b82f6; }
.param-unit { font-size: 0.8rem; color: #64748b; }
.param-hint { font-size: 0.72rem; color: #94a3b8; }

.rule-summary {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 1rem;
  font-size: 0.85rem;
  color: #475569;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 0.65rem 1rem;
}

/* Table */
.empty-block {
  padding: 2rem;
  text-align: center;
  color: #94a3b8;
  font-size: 0.88rem;
}

code {
  background: #f1f5f9;
  padding: 0.15rem 0.45rem;
  border-radius: 4px;
  font-size: 0.85rem;
  font-family: monospace;
  color: #0f172a;
}

.btn-sm { font-size: 0.8rem; padding: 0.3rem 0.7rem; }

.btn-danger {
  background: #fee2e2;
  color: #991b1b;
  border: none;
  border-radius: 8px;
  padding: 0.45rem 1rem;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  transition: background 0.15s;
}
.btn-danger:hover:not(:disabled) { background: #fecaca; }
.btn-danger:disabled { opacity: 0.5; cursor: not-allowed; }
</style>
