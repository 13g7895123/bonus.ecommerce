<!-- 已移至 /sadmin/sms-provider -->
<template>
  <div>
    <!-- 提供者選擇 -->
    <div class="content-block">
      <div class="content-block-header">
        <div>
          <div class="cb-title">目前使用的簡訊提供者</div>
          <div class="cb-desc">切換後，所有新的 OTP 發送請求都會使用選定的提供者</div>
        </div>
        <button class="btn btn-primary" :disabled="savingProvider" @click="saveProvider">
          <Save :size="14" /> {{ savingProvider ? '儲存中...' : '儲存設定' }}
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
          <span v-if="currentProvider === 'twilio'" class="badge-active">使用中</span>
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
          <span v-if="currentProvider === 'firebase'" class="badge-active">使用中</span>
        </div>
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
      <div class="cb-title">兩種方案流程比較</div>
      <table class="compare-table">
        <thead>
          <tr>
            <th>步驟</th>
            <th>Twilio</th>
            <th>Firebase</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>1. 發送 OTP</td>
            <td>後端呼叫 Twilio Verify API 直接發送</td>
            <td>前端 Firebase SDK 觸發（附 reCAPTCHA），取得 verificationId</td>
          </tr>
          <tr>
            <td>2. 儲存紀錄</td>
            <td>後端寫入 phone_verifications（provider=twilio）</td>
            <td>前端傳送 verificationId 至後端後寫入（provider=firebase）</td>
          </tr>
          <tr>
            <td>3. 驗證碼確認</td>
            <td>後端呼叫 Twilio Verify Check API</td>
            <td>後端呼叫 Firebase signInWithPhoneNumber REST API</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Save } from 'lucide-vue-next'
import axios from 'axios'
import config from '../../services/config'

const selectedProvider = ref('twilio')
const currentProvider  = ref('twilio')
const savingProvider   = ref(false)

const api = axios.create({ baseURL: config.apiUrl })

onMounted(async () => {
  try {
    const res = await api.get('/admin-panel/config/sms_provider')
    const val = res.data?.value ?? res.data?.data?.value ?? 'twilio'
    selectedProvider.value = val
    currentProvider.value  = val
  } catch {
    // 保持預設值
  }
})

const saveProvider = async () => {
  savingProvider.value = true
  try {
    await api.post('/admin-panel/config/sms_provider', { value: selectedProvider.value })
    currentProvider.value = selectedProvider.value
    alert(`已切換為 ${selectedProvider.value === 'firebase' ? 'Firebase Authentication' : 'Twilio Verify'}`)
  } catch {
    alert('儲存失敗，請稍後再試')
  } finally {
    savingProvider.value = false
  }
}
</script>

<style scoped>
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
</style>
