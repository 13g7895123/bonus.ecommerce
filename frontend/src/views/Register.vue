<script setup>
import { ref, reactive, computed, nextTick } from 'vue'

// ── 條款/隱私 Modal ──
const legalModal = ref({ show: false, title: '', content: '', loading: false })
const openLegalModal = async (type) => {
  const isTerms = type === 'terms'
  legalModal.value = { show: true, title: isTerms ? '網站服務條款' : '隱私政策', content: '', loading: true }
  try {
    const key = isTerms ? 'terms_html' : 'privacy_html'
    const res = await fetch(`/api/v1/config/${key}`)
    const data = await res.json()
    legalModal.value.content = data.value || ''
  } catch {}
  legalModal.value.loading = false
}
import { useRouter } from 'vue-router'
import { useApi } from '../composables/useApi'
import { useToast } from '../composables/useToast'
import AppInput from '../components/AppInput.vue'
import AppButton from '../components/AppButton.vue'
import AuthHeader from '../components/AuthHeader.vue'
import DebugFillButton from '../components/DebugFillButton.vue'
import { 
  getRandomEmail, 
  getRandomName, 
  getRandomSurname, 
  getRandomDate, 
  getRandomPhone 
} from '../utils/random'
import { countries, getCountryName } from '../utils/countries'
import { useI18n } from 'vue-i18n'
import { firebaseSendOtp } from '../services/FirebaseService'

const { locale } = useI18n()
const currentLocale = computed(() => locale.value)

const router = useRouter()
const api = useApi()

const form = reactive({
  email: '',
  firstName: '',
  lastName: '',
  password: '',
  dob: '',
  country: '',
  dialCode: '',
  phone: '',
  inviteCode: '',
  terms: false
})

const fillRandomData = () => {
  form.email = getRandomEmail()
  form.firstName = getRandomName()
  form.lastName = getRandomSurname()
  form.password = 'password'
  form.dob = getRandomDate()
  form.country = 'TW'
  form.dialCode = '+886'
  form.phone = getRandomPhone()
  form.terms = true
}

const loading = ref(false)
const dobInput = ref(null)
const dobFocused = ref(false)
const showCountryPicker = ref(false)
const countrySearch = ref('')

const selectedCountryName = computed(() => {
  const c = countries.find(c => c.code === form.country)
  if (!c) return ''
  const name = getCountryName(c, currentLocale.value)
  return c.dialCode ? `${name}(${c.dialCode})` : name
})

const filteredCountries = computed(() => {
  const q = countrySearch.value.trim()
  if (!q) return countries
  return countries.filter(c =>
    c.name.includes(q) || c.en.toLowerCase().includes(q.toLowerCase())
  )
})

const openCountryPicker = () => {
  countrySearch.value = ''
  showCountryPicker.value = true
}

const closeCountryPicker = () => {
  showCountryPicker.value = false
  countrySearch.value = ''
}

const selectCountry = (code) => {
  form.country = code
  const c = countries.find(c => c.code === code)
  form.dialCode = c ? c.dialCode : ''
  closeCountryPicker()
}

const toast = useToast()

// dialCode + phone 直接拼接，後端負責正規化 E.164
// 使用者輸入本地格式即可（例如 0937067268），後端會去掉多餘的 0
const fullPhone = computed(() => form.dialCode + form.phone.replace(/\s/g, ''))

// ——— OTP Modal 狀態 ———
const showOtpModal = ref(false)
const otpDigits = ref(['', '', '', '', '', ''])
const otpRefs = []
const otpLoading = ref(false)
const otpCountdown = ref(0)
let otpTimer = null

const otpCode = computed(() => otpDigits.value.join(''))

const startOtpCountdown = () => {
  otpCountdown.value = 60
  clearInterval(otpTimer)
  otpTimer = setInterval(() => {
    otpCountdown.value--
    if (otpCountdown.value <= 0) clearInterval(otpTimer)
  }, 1000)
}

const onOtpInput = (i) => {
  otpDigits.value[i] = otpDigits.value[i].replace(/\D/g, '').slice(-1)
  if (otpDigits.value[i] && i < 5) {
    nextTick(() => otpRefs[i + 1]?.focus())
  }
}

const onOtpKeydown = (e, i) => {
  if (e.key === 'Backspace' && !otpDigits.value[i] && i > 0) {
    nextTick(() => otpRefs[i - 1]?.focus())
  }
}

const onOtpPaste = (e) => {
  const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6)
  for (let j = 0; j < 6; j++) {
    otpDigits.value[j] = text[j] || ''
  }
  nextTick(() => otpRefs[Math.min(text.length, 5)]?.focus())
}

const closeOtpModal = () => {
  showOtpModal.value = false
  otpDigits.value = ['', '', '', '', '', '']
  clearInterval(otpTimer)
}

const resendOtp = async () => {
  try {
    await sendPhoneOtpByProvider()
    startOtpCountdown()
    toast.success('驗證碼已重新發送')
  } catch {
    toast.error('發送失敗，請稍後再試')
  }
}

/**
 * 依目前 SMS 提供者發送 OTP
 *  - Twilio：直接呼叫後端 sendPhoneOtp，後端負責發送
 *  - Firebase：先用 Firebase SDK 觸發簡訊，再將 verificationId 回傳後端儲存
 * 回傳後端回應（含 verification_required 欄位）
 */
const sendPhoneOtpByProvider = async () => {
  // 取得目前提供者（後端 config，結果快取於 localStorage 5 分鐘）
  const provider = await getOtpProvider()

  if (provider === 'firebase') {
    // Firebase：前端觸發簡訊，回傳 verificationId 給後端
    const verificationId = await firebaseSendOtp(fullPhone.value, 'firebase-recaptcha')
    return await api.auth.sendPhoneOtp({ phone: fullPhone.value, session_info: verificationId })
  } else {
    // Twilio（或其他預設）：後端直接發送
    return await api.auth.sendPhoneOtp({ phone: fullPhone.value })
  }
}

/** 取得 OTP 完整設定（每次向後端查詢，確保 sadmin 開關即時生效）
 *  回傳 { provider: string, verification_required: boolean }
 */
const getOtpConfig = async () => {
  try {
    const res = await api.auth.otpProvider()
    const config = {
      provider:              res?.provider ?? 'twilio',
      verification_required: res?.verification_required !== false,
    }
    console.log('[OTP Config] 後端回應:', res)
    console.log('[OTP Config] 解析結果:', config)
    return config
  } catch (err) {
    console.error('[OTP Config] 查詢失敗，使用安全預設值（verification_required: true）', err)
    return { provider: 'twilio', verification_required: true }
  }
}

// 向下相容舊呼叫
const getOtpProvider = async () => (await getOtpConfig()).provider


const submitOtp = async () => {
  if (otpCode.value.length < 6) return
  otpLoading.value = true
  try {
    await api.auth.verifyPhoneOtp({ phone: fullPhone.value, code: otpCode.value })
    // OTP 驗證成功，才將資料送到後端建立帳號
    await api.auth.register({ ...form })
    closeOtpModal()
    toast.success('註冊成功！')
    router.push('/')
  } catch (e) {
    const msg = e.response?.data?.message || e.message || ''
    if (msg && (msg.includes('OTP') || msg.includes('驗證') || msg.includes('code') || msg.includes('expired') || msg.includes('approved'))) {
      toast.error(msg || '驗證碼錯誤，請重試')
      otpDigits.value = ['', '', '', '', '', '']
      nextTick(() => otpRefs[0]?.focus())
    } else {
      // 驗證成功但register失敗（例如email重複）——關閉modal顯示錯誤
      closeOtpModal()
      toast.error(msg || '註冊失敗，請稍後再試')
    }
  } finally {
    otpLoading.value = false
  }
}
// ——————————————————

const handleRegister = async () => {
  if (!form.terms) {
    toast.error('請同意服務條款及隱私政策')
    return
  }
  if (!form.email || !form.password || !form.firstName || !form.lastName) {
    toast.error('請填寫必填欄位')
    return
  }

  loading.value = true
  try {
    // 先查模式開關，再決定是否需要發 OTP
    const { verification_required: verificationRequired } = await getOtpConfig()

    if (!verificationRequired) {
      // 測試模式：跳過 OTP，直接完成註冊
      await api.auth.register({ ...form })
      toast.success('註冊成功！')
      router.push('/')
      return
    }

    // 正式模式：先發 OTP，再顯示驗證視窗
    await sendPhoneOtpByProvider()
    otpDigits.value = ['', '', '', '', '', '']
    showOtpModal.value = true
    startOtpCountdown()
    nextTick(() => otpRefs[0]?.focus())
  } catch (error) {
    const errObj = error.response?.data || error
    toast.error(errObj.message || '發送驗證碼失敗，請稍後再試')
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="standalone-login-page">
    <div class="login-header-nav">
      <router-link to="/login" class="back-home-btn">
        <span class="arrow-left"></span>
      </router-link>
      <div class="login-logo-small">
        <img src="/logo.png" alt="Logo" />
      </div>
    </div>
    <div class="login-page">
      <AuthHeader title="加入阿聯酋航空 Skywards" description="每次旅行都能享有各種優惠。享受獎勵航班、艙位升等、專屬權益等優惠。" />
      
      <div class="login-form">
        <div class="form-field">
          <AppInput v-model="form.email" type="email" placeholder="電子郵件" />
        </div>
        <div class="form-field">
          <AppInput v-model="form.firstName" type="text" placeholder="名字 (First Name)" />
        </div>
        <div class="form-field">
          <AppInput v-model="form.lastName" type="text" placeholder="姓氏 (Last Name)" />
          <p class="field-instruction">您必須以英文輸入輸入姓名,且須與護照上顯示的完全相同。</p>
        </div>
        <div class="form-field">
          <AppInput v-model="form.password" type="password" placeholder="密碼" />
        </div>
        <div class="form-field">
          <div class="dob-wrapper" @click="dobInput?.showPicker?.()">
            <div class="dob-display" :class="{ 'dob-focused': dobFocused }">
              <span :class="form.dob ? 'dob-value' : 'dob-placeholder'">
                {{ form.dob ? form.dob : '出生日期' }}
              </span>
              <span class="dob-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <rect x="3" y="4" width="18" height="17" rx="2" stroke="#676767" stroke-width="2"/>
                  <line x1="3" y1="9" x2="21" y2="9" stroke="#676767" stroke-width="2"/>
                  <line x1="8" y1="2" x2="8" y2="6" stroke="#676767" stroke-width="2" stroke-linecap="round"/>
                  <line x1="16" y1="2" x2="16" y2="6" stroke="#676767" stroke-width="2" stroke-linecap="round"/>
                </svg>
              </span>
            </div>
            <input
              ref="dobInput"
              v-model="form.dob"
              type="date"
              class="dob-real-input"
              @focus="dobFocused = true"
              @blur="dobFocused = false"
            />
          </div>
        </div>
        <div class="form-field">
          <div class="country-selector" @click="openCountryPicker">
            <span :class="form.country ? 'country-value' : 'country-placeholder'">
              {{ form.country ? selectedCountryName : '居住國家/地區' }}
            </span>
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M6 9L12 15L18 9" stroke="#676767" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
        </div>
        <div class="form-field">
          <div class="phone-prefix-wrapper">
            <span v-if="form.dialCode" class="phone-dial-code">{{ form.dialCode }}</span>
            <span v-if="form.dialCode" class="phone-dial-sep"></span>
            <input
              v-model="form.phone"
              type="tel"
              class="phone-number-input"
              placeholder="手機號碼"
              inputmode="numeric"
            />
          </div>
        </div>
        <div class="form-field">
          <AppInput v-model="form.inviteCode" type="text" placeholder="輸入邀請碼(選填)" />
        </div>
        
        <div class="checkbox-group">
          <input v-model="form.terms" type="checkbox" id="terms" />
          <label for="terms">我同意<span class="red-text" @click.stop="openLegalModal('terms')">網站服務條款</span>及<span class="red-text" @click.stop="openLegalModal('privacy')">隱私政策</span></label>
        </div>
        
        <AppButton class="login-submit-btn" :disabled="loading" @click="handleRegister" block>
          {{ loading ? '註冊中...' : '註冊' }}
        </AppButton>
        
        <div class="footer-note">
          我已有帳號 <router-link to="/login" class="login-link-red">登入</router-link>
        </div>

        <DebugFillButton @fill="fillRandomData" />
      </div>
    </div>
  </div>

  <!-- 條款/隱私 Modal -->
  <Teleport to="body">
    <div v-if="legalModal.show"
      style="position:fixed;inset:0;background:rgba(0,0,0,0.6);display:flex;align-items:center;justify-content:center;z-index:9999;padding:1rem"
      @click.self="legalModal.show = false">
      <div style="width:100%;max-width:480px;max-height:80vh;background:#fff;border-radius:12px;display:flex;flex-direction:column;box-shadow:0 20px 60px rgba(0,0,0,0.3)">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-bottom:1px solid #e2e8f0">
          <h3 style="margin:0;font-size:1rem;font-weight:700">{{ legalModal.title }}</h3>
          <button @click="legalModal.show = false" style="background:none;border:none;font-size:1.2rem;cursor:pointer;color:#94a3b8">✕</button>
        </div>
        <div style="flex:1;overflow-y:auto;padding:1.25rem">
          <div v-if="legalModal.loading" style="text-align:center;color:#94a3b8;padding:2rem">載入中...</div>
          <div v-else-if="legalModal.content" v-html="legalModal.content"></div>
          <div v-else style="color:#94a3b8;text-align:center;padding:2rem">尚無內容</div>
        </div>
        <div style="padding:1rem 1.25rem;border-top:1px solid #e2e8f0;text-align:right">
          <button @click="legalModal.show = false" style="padding:0.5rem 1.5rem;background:#d71921;color:#fff;border:none;border-radius:8px;cursor:pointer;font-weight:600">我已閱讀</button>
        </div>
      </div>
    </div>
  </Teleport>

  <!-- 國家/地區選擇 Bottom Sheet -->
  <div v-if="showCountryPicker" class="country-overlay" @click.self="closeCountryPicker">
    <div class="country-sheet">
      <div class="country-sheet-header">
        <span class="country-sheet-title">選擇國家/地區</span>
        <button class="country-sheet-close" @click="closeCountryPicker">✕</button>
      </div>
      <div class="country-search-wrap">
        <svg class="country-search-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#aaa" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input
          v-model="countrySearch"
          class="country-search-input"
          type="text"
          placeholder="搜尋國家/地區"
          autocomplete="off"
        />
      </div>
      <ul class="country-list">
        <li
          v-for="country in filteredCountries"
          :key="country.code"
          class="country-option"
          :class="{ selected: form.country === country.code }"
          @click="selectCountry(country.code)"
        >
          <span>{{ getCountryName(country, currentLocale) }}{{ country.dialCode ? `(${country.dialCode})` : '' }}</span>
          <svg v-if="form.country === country.code" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#d71921" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        </li>
        <li v-if="filteredCountries.length === 0" class="country-no-result">查無結果</li>
      </ul>
    </div>
  </div>

  <!-- OTP 驗證彈窗 -->
  <div v-if="showOtpModal" class="otp-overlay" @click.self="closeOtpModal">
    <div class="otp-modal">
      <h2 class="otp-title">手機號碼驗證</h2>
      <p class="otp-subtitle">驗證碼已發送至<br /><strong>{{ form.dialCode }} {{ form.phone }}</strong></p>
      <div class="otp-inputs">
        <input
          v-for="(digit, i) in otpDigits"
          :key="i"
          :ref="el => { if (el) otpRefs[i] = el }"
          v-model="otpDigits[i]"
          class="otp-box"
          type="text"
          inputmode="numeric"
          maxlength="1"
          autocomplete="one-time-code"
          @input="onOtpInput(i)"
          @keydown="onOtpKeydown($event, i)"
          @paste.prevent="onOtpPaste($event)"
        />
      </div>
      <div class="otp-resend">
        <span v-if="otpCountdown > 0" class="otp-countdown">{{ otpCountdown }}秒後可重新發送</span>
        <span v-else class="otp-resend-link" @click="resendOtp">重新發送驗證碼</span>
      </div>
      <AppButton class="otp-confirm-btn" :disabled="otpLoading || otpCode.length < 6" @click="submitOtp" block>
        {{ otpLoading ? '驗證中...' : '確認' }}
      </AppButton>
      <button class="otp-cancel-btn" @click="closeOtpModal">取消</button>
    </div>
  </div>
  <!-- Firebase reCAPTCHA invisible container -->
  <div id="firebase-recaptcha-container"></div>
</template>

<style scoped>
.standalone-login-page {
  background-color: #ffffff;
  min-height: 100vh;
}

.login-header-nav {
  display: flex;
  align-items: center;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #eee;
  background-color: #ffffff;
}

.back-home-btn {
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 0.5rem;
  display: flex;
  align-items: center;
  text-decoration: none;
  color: #000;
}

.arrow-left {
  display: block;
  width: 12px;
  height: 12px;
  border-left: 2px solid currentColor;
  border-bottom: 2px solid currentColor;
  transform: rotate(45deg);
}

.login-logo-small {
  flex-grow: 1;
  display: flex;
  justify-content: center;
  padding-right: 40px;
}

.login-logo-small img {
  height: 35px;
}

.login-page {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 3rem 5rem;
  margin: 0 auto;
}

.login-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #333;
  margin-bottom: 1rem;
}

.login-desc {
  font-size: 1rem;
  color: #666;
  line-height: 1.6;
  margin-bottom: 2rem;
}

.login-form {
  width: 100%;
}

.login-input {
  width: 100%;
  padding: 1rem;
  margin-bottom: 1rem;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 1rem;
  box-sizing: border-box;
}

.form-field {
  width: 100%;
  margin-bottom: 1.25rem;
}

.field-instruction {
  font-size: 0.85rem;
  color: #666;
  text-align: left;
  margin-top: 0.4rem;
  line-height: 1.4;
}

.form-row {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1.25rem;
}

.half-input-container {
  flex: 1;
}

.half-input {
  /* No special style needed if wrapper handles width */
}

.checkbox-group {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  text-align: left;
  margin-bottom: 2rem;
  font-size: 0.95rem;
  color: #333;
}

.checkbox-group input {
  margin-top: 0.25rem;
  width: 18px;
  height: 18px;
}

.red-text {
  color: #d71921;
  cursor: pointer;
}

.login-submit-btn {
  margin-top: 1rem;
}

.error-msg {
  color: #d71921;
  font-size: 0.9rem;
  margin-bottom: 1rem;
  font-weight: 700;
  text-align: center;
}

.footer-note {
  margin-top: 2rem;
  font-size: 1rem;
  color: #333;
  font-weight: 500;
}

.login-link-red {
  color: #d71921;
  text-decoration: underline;
  font-weight: 700;
  margin-left: 0.5rem;
}

@media (max-width: 767px) {
  .login-page {
    padding: 1.5rem 1rem;
  }
}

.dob-wrapper {
  position: relative;
  width: 100%;
}

.dob-wrapper {
  position: relative;
  width: 100%;
}

.dob-display {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  padding: 0.875rem 1rem;
  border: 1px solid #a8a8a9;
  border-radius: 8px;
  font-size: 0.95rem;
  background-color: #ffffff;
  box-sizing: border-box;
  pointer-events: none;
}

.dob-display.dob-focused {
  border-color: #d71921;
}

.dob-placeholder {
  color: #676767;
}

.dob-value {
  color: #333;
}

.dob-icon {
  display: flex;
  align-items: center;
  flex-shrink: 0;
}

.dob-real-input {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
  cursor: pointer;
  box-sizing: border-box;
}

.country-selector {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  padding: 0.875rem 1rem;
  border: 1px solid #a8a8a9;
  border-radius: 8px;
  font-size: 0.95rem;
  background-color: #ffffff;
  box-sizing: border-box;
  cursor: pointer;
}

.country-placeholder {
  color: #676767;
}

.country-value {
  color: #333;
}

.country-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  z-index: 200;
  display: flex;
  align-items: flex-end;
  justify-content: center;
}

.country-sheet {
  background: #fff;
  width: 100%;
  max-width: var(--app-max-width, 480px);
  border-radius: 16px 16px 0 0;
  padding-bottom: env(safe-area-inset-bottom, 1rem);
  display: flex;
  flex-direction: column;
  max-height: 70vh;
}

.country-sheet-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #f0f0f0;
}

.country-sheet-title {
  font-size: 1rem;
  font-weight: 700;
  color: #1a1a1a;
}

.country-sheet-close {
  background: none;
  border: none;
  font-size: 1rem;
  color: #999;
  cursor: pointer;
  padding: 4px;
  line-height: 1;
}

.country-search-wrap {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 1rem;
  border-bottom: 1px solid #f0f0f0;
  flex-shrink: 0;
}

.country-search-icon {
  flex-shrink: 0;
}

.country-search-input {
  flex: 1;
  border: none;
  outline: none;
  font-size: 0.9rem;
  color: #1a1a1a;
  background: transparent;
}

.country-search-input::placeholder {
  color: #aaa;
}

.country-list {
  list-style: none;
  margin: 0;
  padding: 0;
  overflow-y: auto;
}

.country-option {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.5rem;
  font-size: 0.95rem;
  color: #1a1a1a;
  cursor: pointer;
  border-bottom: 1px solid #f8f8f8;
  transition: background 0.15s;
}

.country-option:last-child {
  border-bottom: none;
}

.country-option:hover {
  background: #fafafa;
}

.country-option.selected {
  color: #d71921;
  font-weight: 600;
}

.country-no-result {
  padding: 1.5rem;
  text-align: center;
  font-size: 0.9rem;
  color: #aaa;
}

/* ── 手機號碼前綴 ── */
.phone-prefix-wrapper {
  display: flex;
  align-items: center;
  width: 100%;
  border: 1px solid #a8a8a9;
  border-radius: 8px;
  background: #fff;
  padding: 0 1rem;
  box-sizing: border-box;
  min-height: 52px;
}

.phone-dial-code {
  font-size: 0.95rem;
  color: #333;
  white-space: nowrap;
  flex-shrink: 0;
}

.phone-dial-sep {
  width: 1px;
  height: 20px;
  background: #ccc;
  margin: 0 0.75rem;
  flex-shrink: 0;
}

.phone-number-input {
  flex: 1;
  border: none;
  outline: none;
  font-size: 0.95rem;
  color: #333;
  background: transparent;
  padding: 0.875rem 0;
}

.phone-number-input::placeholder {
  color: #676767;
}

/* ── OTP 驗證彈窗 ── */
.otp-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 300;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

.otp-modal {
  background: #fff;
  border-radius: 16px;
  padding: 2rem 1.5rem;
  width: 100%;
  max-width: 360px;
  text-align: center;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
}

.otp-title {
  font-size: 1.2rem;
  font-weight: 700;
  color: #d71921;
  margin: 0 0 0.5rem;
}

.otp-subtitle {
  font-size: 0.9rem;
  color: #555;
  margin: 0 0 1.5rem;
  line-height: 1.5;
}

.otp-inputs {
  display: flex;
  justify-content: center;
  gap: 0.6rem;
  margin-bottom: 1.25rem;
}

.otp-box {
  width: 44px;
  height: 52px;
  border: 1.5px solid #ccc;
  border-radius: 10px;
  text-align: center;
  font-size: 1.3rem;
  font-weight: 700;
  color: #1a1a1a;
  outline: none;
  transition: border-color 0.15s;
  background: #f9f9f9;
}

.otp-box:focus {
  border-color: #d71921;
  background: #fff;
}

.otp-resend {
  font-size: 0.875rem;
  margin-bottom: 1.25rem;
  min-height: 1.5rem;
}

.otp-countdown {
  color: #999;
}

.otp-resend-link {
  color: #d71921;
  cursor: pointer;
  font-weight: 600;
  text-decoration: underline;
}

.otp-confirm-btn {
  margin-bottom: 0.75rem;
}

.otp-cancel-btn {
  width: 100%;
  background: none;
  border: none;
  color: #999;
  font-size: 0.9rem;
  cursor: pointer;
  padding: 0.5rem;
}

.otp-cancel-btn:hover {
  color: #555;
}
</style>
