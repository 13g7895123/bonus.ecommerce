<script setup>
import { ref, reactive } from 'vue'
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

const router = useRouter()
const api = useApi()

const form = reactive({
  email: '',
  firstName: '',
  lastName: '',
  password: '',
  dob: '',
  country: '',
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
  form.country = 'Taiwan'
  form.phone = getRandomPhone()
  form.terms = true
}

const loading = ref(false)
const dobFocused = ref(false)

const toast = useToast()

const handleRegister = async () => {
  if (!form.terms) {
    toast.error('請同意服務時款及隱私政策')
    return
  }

  // 簡單驗證
  if (!form.email || !form.password || !form.firstName || !form.lastName) {
    toast.error('請填寫必填欄位')
    return
  }

  loading.value = true

  try {
    const response = await api.auth.register({
      ...form
    })
    console.log('Register success:', response)
    // 註冊成功，導向首頁
    router.push('/')
  } catch (error) {
    const errObj = error.response?.data || error
    toast.error(errObj.message || '註冊失敗，請稍後再試')
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
          <div class="dob-wrapper">
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
              v-model="form.dob"
              type="date"
              class="dob-real-input"
              @focus="dobFocused = true"
              @blur="dobFocused = false"
            />
          </div>
        </div>
        <div class="form-field">
          <AppInput v-model="form.country" type="text" placeholder="居住國家/地區" />
        </div>
        <div class="form-field">
          <AppInput v-model="form.phone" type="tel" placeholder="手機號碼" />
        </div>
        <div class="form-field">
          <AppInput v-model="form.inviteCode" type="text" placeholder="輸入邀請碼(選填)" />
        </div>
        
        <div class="checkbox-group">
          <input v-model="form.terms" type="checkbox" id="terms" />
          <label for="terms">我同意<span class="red-text">網站服務條款</span>及<span class="red-text">隱私政策</span></label>
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
</style>
