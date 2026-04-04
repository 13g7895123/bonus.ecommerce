<template>
  <div class="details-page">
    <PageHeader title="詳細個人資料" back-to="/profile" />

    <!-- 聯絡詳細資料標題與編輯按鈕 -->
    <div class="section-container">
      <div class="title-row">
        <h3 class="contact-title">聯絡詳細資料</h3>
        <button class="edit-btn" @click="toggleEdit">
          <svg v-if="!isEditing" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
          </svg>
           <span v-else style="font-size: 0.9rem; font-weight: 700;">完成</span>
        </button>
      </div>

      <!-- 個人資訊子區塊 -->
      <div class="sub-section">
        <h4 class="sub-title">個人資訊</h4>
        <div class="form-group">
          <label>名字</label>
          <div v-if="!isEditing" class="field-value">{{ user?.firstName }}</div>
          <input v-else v-model="formData.firstName" class="edit-input" />
        </div>
        <div class="form-group">
          <label>姓氏</label>
          <div v-if="!isEditing" class="field-value">{{ user?.lastName }}</div>
          <input v-else v-model="formData.lastName" class="edit-input" />
        </div>
        <div class="form-group">
          <label>你的出生日期</label>
          <div v-if="!isEditing" class="field-value">{{ user?.dob }}</div>
          <input v-else v-model="formData.dob" type="date" class="edit-input" />
        </div>
        <div class="form-group">
          <label>居住國家/地區</label>
          <div v-if="!isEditing" class="field-value">{{ displayCountryName }}</div>
          <div v-else class="country-selector" @click="openCountryPicker">
            <span :class="formData.country ? 'country-value' : 'country-placeholder'">
              {{ editingCountryName || '選擇國家/地區' }}
            </span>
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M6 9L12 15L18 9" stroke="#676767" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
        </div>
        <div class="form-group">
          <label>行動號碼(偏好的聯絡方式)</label>
          <div v-if="!isEditing" class="field-value">{{ user?.phone }}</div>
           <input v-else v-model="formData.phone" class="edit-input" />
        </div>
      </div>

      <!-- 電子郵件子區塊 -->
      <div class="sub-section">
        <h4 class="sub-title">電子郵件</h4>
        <div class="email-group">
          <div class="email-value">{{ user?.email }}</div>
          <span v-if="isEmailVerified" class="verified-tag">已驗證</span>
          <span v-else class="unverified-tag">未驗證</span>
        </div>
        <button v-if="!isEmailVerified" class="verify-email-btn" :disabled="sendingVerify" @click="sendVerificationEmail">
          {{ sendingVerify ? '發送中...' : '驗證電子郵件' }}
        </button>
      </div>
    </div>

    <!-- 國家選擇 Bottom Sheet -->
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
            :class="{ selected: formData.country === country.code }"
            @click="selectCountry(country.code)"
          >
            <span>{{ getCountryName(country, locale) }}</span>
            <svg v-if="formData.country === country.code" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#d71921" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
          </li>
          <li v-if="filteredCountries.length === 0" class="country-no-result">查無結果</li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useToast } from '../composables/useToast'
import PageHeader from '../components/PageHeader.vue'
import { UserService } from '../services/UserService'
import { countries, getCountryName } from '../utils/countries'

const isEditing = ref(false)
const user = ref({})
const formData = ref({})
const sendingVerify = ref(false)
const toast = useToast()
const { locale } = useI18n()
const currentLocale = computed(() => locale.value)
const showCountryPicker = ref(false)
const countrySearch = ref('')

const filteredCountries = computed(() => {
  const q = countrySearch.value.trim()
  if (!q) return countries
  const ql = q.toLowerCase()
  return countries.filter(c =>
    c.name.includes(q) ||
    c.en.toLowerCase().includes(ql) ||
    getCountryName(c, locale.value).toLowerCase().includes(ql)
  )
})

const displayCountryName = computed(() => {
  const code = user.value?.country
  if (!code) return ''
  const c = countries.find(c => c.code === code)
  return c ? getCountryName(c, locale.value) : code
})

const editingCountryName = computed(() => {
  const code = formData.value?.country
  if (!code) return ''
  const c = countries.find(c => c.code === code)
  return c ? getCountryName(c, locale.value) : code
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
  formData.value.country = code
  closeCountryPicker()
}

const isEmailVerified = computed(() => !!(user.value?.is_verified || user.value?.verified))
// 注意：is_verified 已在 UserService.getProfile 正規化為 boolean，
// 此處直接用即可

onMounted(async () => {
  try {
    const userStr = localStorage.getItem('user')
    const userId  = userStr ? JSON.parse(userStr).id : null
    const userService = new UserService()
    const data = await userService.getProfile(userId)
    user.value     = data || {}
    formData.value = { ...user.value }
  } catch (e) {
    toast.error('載入個人資料失敗')
  }
})

const toggleEdit = () => {
  if (isEditing.value) {
    saveChanges()
  } else {
    // 進入編輯模式時，確保 firstName/lastName 從 full_name 拆分
    const u = { ...user.value }
    if (!u.firstName && u.full_name) {
      const parts = u.full_name.trim().split(/\s+/)
      u.firstName = parts[0] || ''
      u.lastName = parts.slice(1).join(' ') || ''
    }
    formData.value = u
    isEditing.value = true
  }
}

const sendVerificationEmail = async () => {
  sendingVerify.value = true
  try {
    const userService = new UserService()
    await userService.sendVerificationEmail()
    toast.success('驗證信已發送，請查收電子郵件')
  } catch (e) {
    toast.error('發送失敗，請稍後再試')
  } finally {
    sendingVerify.value = false
  }
}

const saveChanges = async () => {
  // 合併 firstName + lastName → full_name
  const full_name = [formData.value.firstName, formData.value.lastName].filter(Boolean).join(' ')

  try {
    const userService = new UserService()
    await userService.updateProfile(user.value.id, {
      full_name,
      phone: formData.value.phone,
      dob: formData.value.dob,
      country: formData.value.country,
    })
    // 更新本地 reactive 狀態（不再寫 localStorage）
    user.value = { ...user.value, ...formData.value, full_name, name: full_name }
    toast.success('個人資料已更新')
  } catch (e) {
    toast.error('更新個人資料失敗')
  }

  isEditing.value = false
}
</script>

<style scoped>
.details-page {
  background-color: #f5f5f5;
  min-height: 100vh;
  color: #333;
}

.section-container {
  background-color: #ffffff;
  padding: 2rem 1.5rem;
  margin: 1rem;
  border-radius: 12px; /* Rounded corners */
  text-align: left; /* Ensure left alignment */
}

.title-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #f0f0f0;
}

.contact-title {
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0;
  position: relative;
  display: inline-block;
}

/* Peach/Fuchsia underline */
.contact-title::after {
  content: '';
  display: block;
  width: 100%;
  height: 3px;
  background-color: #E6007E; 
  margin-top: 4px;
}

.edit-btn {
  background-color: #e0e0e0; /* Light gray background */
  border: none;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  padding: 0;
  outline: none;
}

.edit-btn:focus-visible {
  outline: none;
}

/* Ensure svg within button is black */
.edit-btn svg {
  stroke: #000000;
}

.sub-section {
  margin-bottom: 2.5rem;
}

.sub-title {
  font-size: 1.1rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
  color: #000;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  font-size: 0.85rem;
  color: #666;
  margin-bottom: 0.5rem;
}

.field-value {
  font-size: 1rem;
  font-weight: 500;
  color: #333;
  padding-bottom: 0.25rem;
  border-bottom: 1px solid #eee;
}

.email-group {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.email-value {
  font-size: 1rem;
  font-weight: 500;
}

.edit-input {
  width: 100%;
  padding: 0.5rem;
  font-size: 1rem;
  border: 1px solid #ccc;
  border-radius: 4px;
}

.verified-tag {
  background-color: #e6f7ed;
  color: #2e7d32;
  font-size: 0.75rem;
  padding: 2px 8px;
  border-radius: 4px;
  font-weight: 700;
}

.unverified-tag {
  background-color: #fff3e0;
  color: #e65100;
  font-size: 0.75rem;
  padding: 2px 8px;
  border-radius: 4px;
  font-weight: 700;
}

.verify-email-btn {
  margin-top: 0.75rem;
  padding: 0.5rem 1.25rem;
  font-size: 0.85rem;
  font-weight: 600;
  color: #fff;
  background-color: #d71921;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.verify-email-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.country-selector {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  padding: 0.5rem;
  font-size: 1rem;
  border: 1px solid #ccc;
  border-radius: 4px;
  cursor: pointer;
  background: #fff;
  box-sizing: border-box;
}

.country-value { color: #333; }
.country-placeholder { color: #aaa; }

.country-overlay {
  position: fixed;
  top: 0; left: 0; width: 100%; height: 100%;
  background: rgba(0,0,0,0.45);
  z-index: 3000;
  display: flex;
  align-items: flex-end;
  justify-content: center;
}

.country-sheet {
  background: #fff;
  width: 100%;
  max-width: 480px;
  border-radius: 16px 16px 0 0;
  padding: 1.25rem 0 0;
  max-height: 75vh;
  display: flex;
  flex-direction: column;
}

.country-sheet-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 1.25rem 1rem;
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
  font-size: 1.2rem;
  color: #999;
  cursor: pointer;
  padding: 0;
  line-height: 1;
}

.country-search-wrap {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.25rem;
  border-bottom: 1px solid #f0f0f0;
}

.country-search-icon { flex-shrink: 0; }

.country-search-input {
  flex: 1;
  border: none;
  outline: none;
  font-size: 0.95rem;
  color: #333;
  background: transparent;
}

.country-list {
  list-style: none;
  margin: 0;
  padding: 0;
  overflow-y: auto;
  flex: 1;
}

.country-option {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.9rem 1.25rem;
  font-size: 0.95rem;
  color: #1a1a1a;
  cursor: pointer;
  border-bottom: 1px solid #f5f5f5;
}

.country-option:active { background: #f9f9f9; }
.country-option.selected { color: #d71921; font-weight: 600; }

.country-no-result {
  text-align: center;
  color: #bbb;
  padding: 2rem;
  font-size: 0.9rem;
}
</style>
