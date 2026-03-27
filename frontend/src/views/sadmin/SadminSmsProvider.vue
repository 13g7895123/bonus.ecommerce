<template>
  <div class="panel">
    <div class="panel-header">
      <span class="panel-title">SMS / OTP 提供者設定</span>
    </div>

    <!-- 來源狀態 -->
    <div class="source-banner" :class="info.source === 'db' ? 'source-db' : 'source-env'">
      <div class="source-info">
        <span class="source-dot"></span>
        <span v-if="info.source === 'db'">
          目前使用 <strong>DB Override</strong>：{{ label(info.active) }}
          <span class="source-hint">（.env 預設為 {{ label(info.env_value) }}）</span>
        </span>
        <span v-else>
          目前使用 <strong>.env 預設</strong>：{{ label(info.active) }}
          <span class="source-hint">（尚未設定 DB override）</span>
        </span>
      </div>
      <button
        v-if="info.source === 'db'"
        class="btn btn-outline btn-sm"
        :disabled="resetting"
        @click="resetToEnv"
      >
        <RotateCcw :size="13" />{{ resetting ? '重設中...' : '清除 Override，回復 .env' }}
      </button>
    </div>

    <!-- 提供者選擇 -->
    <div class="content-block">
      <div class="content-block-header">
        <div>
          <div class="cb-title">選擇要套用的提供者</div>
          <div class="cb-desc">儲存後會寫入 DB override，優先於 .env 設定</div>
        </div>
        <button class="btn btn-primary" :disabled="savingProvider" @click="saveProvider">
          <Save :size="14" /> {{ savingProvider ? '儲存中...' : '套用為 DB Override' }}
        </button>
      </div>

      <div class="provider-cards">
        <div
          class="provider-card"
          :class="{ active: selectedProvider === 'twilio' }"
          @click="selectedProvider = 'twilio'"
        >
          <div class="provider-radio">
            <span class="radio-dot" :class="{ checked: selectedProvider === 'twilio' }"></span>
          </div>
          <div class="provider-info">
            <div class="provider-name">Twilio Verify</div>
            <div class="provider-desc">全球主流電信簡訊服務，由 Twilio 管理驗證碼，支援短信與語音通話</div>
          </div>
          <span v-if="info.active === 'twilio'" class="badge-active">使用中</span>
        </div>

        <div
          class="provider-card"
          :class="{ active: selectedProvider === 'firebase' }"
          @click="selectedProvider = 'firebase'"
        >
          <div class="provider-radio">
            <span class="radio-dot" :class="{ checked: selectedProvider === 'firebase' }"></span>
          </div>
          <div class="provider-info">
            <div class="provider-name">Firebase Authentication</div>
            <div class="provider-desc">Google Firebase 電話驗證，由前端 SDK 處理 reCAPTCHA，後端負責最終驗證</div>
          </div>
          <span v-if="info.active === 'firebase'" class="badge-active">使用中</span>
        </div>

        <div
          class="provider-card"
          :class="{ active: selectedProvider === 'topmessage' }"
          @click="selectedProvider = 'topmessage'"
        >
          <div class="provider-radio">
            <span class="radio-dot" :class="{ checked: selectedProvider === 'topmessage' }"></span>
          </div>
          <div class="provider-info">
            <div class="provider-name">TopMessage SMS</div>
            <div class="provider-desc">純 SMS 閘道，後端自行生成並驗證 OTP，API Key 直接在後台設定，無需重新部署</div>
          </div>
          <span v-if="info.active === 'topmessage'" class="badge-active">使用中</span>
        </div>
      </div>
    </div>

    <div class="divider"></div>

    <!-- TopMessage 設定（後台直接設定，即時生效） -->
    <div class="content-block" :class="{ 'dimmed': selectedProvider !== 'topmessage' }">
      <div class="content-block-header">
        <div>
          <div class="cb-title">TopMessage 設定</div>
          <div class="cb-desc" style="margin-top:0.25rem">
            設定儲存於資料庫，無需修改 .env 或重新部署，儲存後即時生效
          </div>
        </div>
        <button class="btn btn-primary" :disabled="savingTm" @click="saveTopmessageConfig">
          <Save :size="14" /> {{ savingTm ? '儲存中...' : '儲存 TopMessage 設定' }}
        </button>
      </div>

      <div class="tm-status-row">
        <span v-if="tm.api_key_set" class="badge-configured">✓ API Key 已設定（{{ tm.api_key_mask }}）</span>
        <span v-else class="badge-unconfigured">⚠ 尚未設定 API Key</span>
      </div>

      <div class="form-grid">
        <div class="form-field">
          <label class="field-label">API Key<span class="required"> *</span></label>
          <input
            v-model="tmForm.apiKey"
            type="password"
            class="field-input"
            :placeholder="tm.api_key_set ? '留空則保留既有 Key' : '輸入 TopMessage API Key'"
            autocomplete="new-password"
          />
          <div class="field-hint">
            取得方式：登入
            <a href="https://app.topmessage.com/api/key" target="_blank" rel="noopener">app.topmessage.com</a>
            → API Keys
          </div>
        </div>
        <div class="form-field">
          <label class="field-label">寄件者名稱（Sender Name）<span class="required"> *</span></label>
          <input
            v-model="tmForm.sender"
            type="text"
            class="field-input"
            placeholder="最多 11 個字元，例如：MyShop"
            maxlength="11"
          />
          <div class="field-hint">顯示為 SMS 發送者名稱，最多 11 個字元</div>
        </div>
      </div>

      <div class="hint-box" style="margin-top:1rem">
        <strong>OTP 流程：</strong>後端自動生成 6 位數驗證碼 → 儲存至資料庫 → 透過 TopMessage API 發送 SMS
        → 使用者輸入驗證碼 → 後端比對資料庫記錄完成驗證。
        <br />
        <strong>API 文件：</strong>
        <a href="https://topmessage.com/documentation-api/send-message" target="_blank" rel="noopener">
          topmessage.com/documentation-api
        </a>
      </div>
    </div>

    <div class="divider"></div>

    <!-- Twilio 設定說明 -->
    <div class="content-block" :class="{ 'dimmed': selectedProvider !== 'twilio' }">
      <div class="cb-title">Twilio 設定（後端 .env）</div>
      <div class="cb-desc" style="margin-top:0.25rem">以下環境變數需填入 <code>.env.production</code> 並重新部署</div>
      <div class="env-list">
        <div class="env-item">
          <span class="env-key">TWILIO_ACCOUNT_SID</span>
          <span class="env-val">ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx</span>
        </div>
        <div class="env-item">
          <span class="env-key">TWILIO_AUTH_TOKEN</span>
          <span class="env-val">xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx</span>
        </div>
        <div class="env-item">
          <span class="env-key">TWILIO_VERIFY_SERVICE_SID</span>
          <span class="env-val">VSxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx</span>
        </div>
      </div>
      <div class="hint-box">
        <strong>取得方式：</strong>Twilio Console → Verify → Services → 建立 Service → 複製 Service SID
      </div>
    </div>

    <div class="divider"></div>

    <!-- Firebase 設定說明 -->
    <div class="content-block" :class="{ 'dimmed': selectedProvider !== 'firebase' }">
      <div class="cb-title">Firebase 設定</div>
      <div class="cb-desc" style="margin-top:0.25rem">Firebase Phone Auth 採用前後端混合架構：前端 SDK 發送 OTP，後端 REST API 驗證</div>

      <div style="margin-top:1rem">
        <div class="sub-title">後端 .env.production</div>
        <div class="env-list">
          <div class="env-item">
            <span class="env-key">FIREBASE_WEB_API_KEY</span>
            <span class="env-val">AIza... （Web API 金鑰）</span>
          </div>
        </div>
      </div>

      <div style="margin-top:1rem">
        <div class="sub-title">前端 .env（VITE 環境變數）</div>
        <div class="env-list">
          <div class="env-item">
            <span class="env-key">VITE_FIREBASE_API_KEY</span>
            <span class="env-val">AIza...</span>
          </div>
          <div class="env-item">
            <span class="env-key">VITE_FIREBASE_AUTH_DOMAIN</span>
            <span class="env-val">your-project.firebaseapp.com</span>
          </div>
          <div class="env-item">
            <span class="env-key">VITE_FIREBASE_PROJECT_ID</span>
            <span class="env-val">your-project-id</span>
          </div>
        </div>
      </div>

      <div class="hint-box" style="margin-top:1rem">
        <strong>取得方式：</strong>Firebase Console → 專案設定 → 一般 → 您的應用程式 → Firebase SDK 設定
        <br />
        <strong>前端流程說明：</strong>當 Firebase 為啟用提供者時，前端 Register 頁面會自動使用
        Firebase JS SDK 的 <code>signInWithPhoneNumber()</code> 發送 OTP（包含 reCAPTCHA 驗證），
        並將 <code>verificationId</code> 傳回後端儲存，驗證碼確認時由後端呼叫 Firebase Identity Toolkit REST API 完成驗證。
      </div>
    </div>

    <div class="divider"></div>

    <!-- 流程對照 -->
    <div class="content-block">
      <div class="cb-title">三種方案流程比較</div>
      <table class="compare-table">
        <thead>
          <tr>
            <th>步驟</th>
            <th>Twilio Verify</th>
            <th>Firebase</th>
            <th>TopMessage</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>1. 發送 OTP</td>
            <td>後端呼叫 Twilio Verify API</td>
            <td>前端 Firebase SDK（附 reCAPTCHA）</td>
            <td>後端生成 OTP，呼叫 TopMessage API</td>
          </tr>
          <tr>
            <td>2. OTP 管理</td>
            <td>由 Twilio 管理，DB 存識別碼</td>
            <td>由 Firebase 管理，DB 存 sessionInfo</td>
            <td>後端自行管理，DB 存實際驗證碼</td>
          </tr>
          <tr>
            <td>3. 驗證碼確認</td>
            <td>後端呼叫 Twilio Verify Check</td>
            <td>後端呼叫 Firebase Identity Toolkit</td>
            <td>後端比對 DB 中的驗證碼</td>
          </tr>
          <tr>
            <td>4. 設定方式</td>
            <td>.env 環境變數（需重新部署）</td>
            <td>.env + 前端 VITE 變數</td>
            <td>後台直接設定，即時生效</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Save, RotateCcw } from 'lucide-vue-next'

const selectedProvider = ref('twilio')
const info             = ref({ env_value: 'twilio', db_override: null, active: 'twilio', source: 'env' })
const savingProvider   = ref(false)
const resetting        = ref(false)

// TopMessage config
const tm       = ref({ api_key_set: false, api_key_mask: '', sender: '' })
const tmForm   = ref({ apiKey: '', sender: '' })
const savingTm = ref(false)

onMounted(async () => {
  try {
    const [provRes, tmRes] = await Promise.all([
      fetch('/api/v1/sadmin/sms-provider'),
      fetch('/api/v1/sadmin/topmessage-config'),
    ])
    const provData = await provRes.json()
    const tmData   = await tmRes.json()
    info.value           = provData
    selectedProvider.value = provData.active
    tm.value             = tmData
    tmForm.value.sender  = tmData.sender || ''
  } catch {
    // 保持預設值
  }
})

const saveProvider = async () => {
  savingProvider.value = true
  try {
    const res  = await fetch('/api/v1/sadmin/sms-provider', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ provider: selectedProvider.value }),
    })
    const data = await res.json()
    if (!res.ok) { alert(data.message || '儲存失敗'); return }
    info.value = { ...info.value, db_override: selectedProvider.value, active: selectedProvider.value, source: 'db' }
    alert(`已套用 DB override：${label(selectedProvider.value)}`)
  } catch { alert('儲存失敗，請稍後再試') }
  finally { savingProvider.value = false }
}

const resetToEnv = async () => {
  if (!confirm(`確定要清除 DB override，回復使用 .env 預設（${label(info.value.env_value)}）嗎？`)) return
  resetting.value = true
  try {
    const res  = await fetch('/api/v1/sadmin/sms-provider', { method: 'DELETE' })
    const data = await res.json()
    if (!res.ok) { alert(data.message || '重設失敗'); return }
    info.value = { ...info.value, db_override: null, active: info.value.env_value, source: 'env' }
    selectedProvider.value = info.value.env_value
    alert(`已重設為 .env 預設：${label(info.value.env_value)}`)
  } catch { alert('重設失敗，請稍後再試') }
  finally { resetting.value = false }
}

const saveTopmessageConfig = async () => {
  if (!tmForm.value.sender) {
    alert('請輸入寄件者名稱')
    return
  }
  if (!tmForm.value.apiKey && !tm.value.api_key_set) {
    alert('首次設定時請輸入 TopMessage API Key')
    return
  }
  savingTm.value = true
  try {
    const body = { sender: tmForm.value.sender }
    if (tmForm.value.apiKey) body.api_key = tmForm.value.apiKey
    const res  = await fetch('/api/v1/sadmin/topmessage-config', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body),
    })
    const data = await res.json()
    if (!res.ok) { alert(data.message || '儲存失敗'); return }
    tm.value = { ...tm.value, api_key_set: true, sender: tmForm.value.sender }
    tmForm.value.apiKey = ''
    alert('TopMessage 設定已儲存')
  } catch { alert('儲存失敗，請稍後再試') }
  finally { savingTm.value = false }
}

const label = (p) => p === 'firebase' ? 'Firebase Authentication' : p === 'topmessage' ? 'TopMessage SMS' : 'Twilio Verify'
</script>

<style scoped>
.source-banner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 0.75rem 1.25rem;
  font-size: 0.85rem;
  flex-wrap: wrap;
}
.source-env { background: #f0fdf4; border-bottom: 1px solid #bbf7d0; color: #166534; }
.source-db  { background: #fefce8; border-bottom: 1px solid #fde68a; color: #92400e; }
.source-info { display: flex; align-items: center; gap: 0.5rem; }
.source-dot {
  display: inline-block;
  width: 8px; height: 8px;
  border-radius: 50%;
  background: currentColor;
  flex-shrink: 0;
}
.source-hint { opacity: 0.7; font-size: 0.8rem; }
.provider-cards {
  display: flex;
  gap: 1rem;
  margin-top: 1rem;
  flex-wrap: wrap;
}

.provider-card {
  flex: 1;
  min-width: 260px;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  padding: 1.25rem 1rem;
  display: flex;
  align-items: flex-start;
  gap: 0.875rem;
  cursor: pointer;
  transition: border-color 0.2s, background 0.2s;
  background: #fff;
  position: relative;
}

.provider-card:hover { border-color: #94a3b8; }
.provider-card.active { border-color: #3b82f6; background: #eff6ff; }

.provider-radio { padding-top: 2px; flex-shrink: 0; }

.radio-dot {
  display: inline-block;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  border: 2px solid #94a3b8;
  position: relative;
  transition: border-color 0.15s;
}

.radio-dot.checked {
  border-color: #3b82f6;
  background: #3b82f6;
  box-shadow: inset 0 0 0 3px #fff;
}

.provider-name { font-weight: 600; font-size: 0.95rem; color: #1e293b; }
.provider-desc { font-size: 0.8rem; color: #64748b; margin-top: 0.25rem; line-height: 1.5; }

.badge-active {
  position: absolute;
  top: 0.75rem;
  right: 0.75rem;
  background: #dcfce7;
  color: #15803d;
  font-size: 0.72rem;
  font-weight: 600;
  padding: 0.2rem 0.5rem;
  border-radius: 999px;
}

.dimmed { opacity: 0.45; pointer-events: none; }

.sub-title { font-weight: 600; font-size: 0.82rem; color: #475569; margin-bottom: 0.5rem; }

.env-list { display: flex; flex-direction: column; gap: 0.4rem; }
.env-item { display: flex; align-items: center; gap: 0.75rem; font-size: 0.82rem; }
.env-key  { font-family: monospace; background: #f1f5f9; color: #0f172a; padding: 0.2rem 0.5rem; border-radius: 4px; white-space: nowrap; }
.env-val  { color: #64748b; }

.hint-box {
  background: #f8fafc;
  border-left: 3px solid #3b82f6;
  border-radius: 0 8px 8px 0;
  padding: 0.75rem 1rem;
  font-size: 0.82rem;
  color: #475569;
  line-height: 1.7;
  margin-top: 1rem;
}

code {
  background: #f1f5f9;
  padding: 0.1rem 0.4rem;
  border-radius: 4px;
  font-size: 0.8rem;
  color: #0f172a;
}

.compare-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.82rem;
  margin-top: 0.75rem;
}

.compare-table th,
.compare-table td {
  border: 1px solid #e2e8f0;
  padding: 0.6rem 0.75rem;
  text-align: left;
  vertical-align: top;
}

.compare-table th {
  background: #f8fafc;
  font-weight: 600;
  color: #475569;
}

.compare-table td:first-child { font-weight: 600; color: #334155; white-space: nowrap; }

/* TopMessage form */
.tm-status-row { margin: 0.75rem 0 1rem; display: flex; align-items: center; gap: 0.5rem; }
.badge-configured   { background: #dcfce7; color: #15803d; font-size: 0.78rem; font-weight: 600; padding: 0.25rem 0.75rem; border-radius: 999px; }
.badge-unconfigured { background: #fef3c7; color: #92400e; font-size: 0.78rem; font-weight: 600; padding: 0.25rem 0.75rem; border-radius: 999px; }

.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.75rem; }
@media (max-width: 640px) { .form-grid { grid-template-columns: 1fr; } }

.form-field { display: flex; flex-direction: column; gap: 0.4rem; }
.field-label { font-size: 0.83rem; font-weight: 600; color: #374151; }
.required { color: #ef4444; }
.field-input {
  padding: 0.5rem 0.75rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.9rem;
  color: #1e293b;
  background: #fff;
  outline: none;
  transition: border-color 0.15s;
}
.field-input:focus { border-color: #3b82f6; }
.field-hint { font-size: 0.75rem; color: #94a3b8; }
.field-hint a { color: #3b82f6; text-decoration: none; }
.field-hint a:hover { text-decoration: underline; }
</style>

