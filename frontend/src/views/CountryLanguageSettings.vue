<template>
  <div class="cls-page">
    <PageHeader title="國家/地區和語言" back-to="/settings" />

    <div class="cls-content">
      <!-- 兩行列表，帶底線 -->
      <div class="cls-list">
        <!-- 國家/地區（可點擊） -->
        <div class="cls-row cls-row-clickable" @click="openCountryPicker">
          <span class="cls-row-label">國家/地區</span>
          <div class="cls-row-right">
            <span class="cls-row-value">{{ currentCountryName }}</span>
            <svg class="cls-chevron" width="7" height="12" viewBox="0 0 7 12" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M1 1L6 6L1 11" stroke="#bbb" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
        </div>

        <!-- 語言（可點擊） -->
        <div class="cls-row cls-row-clickable" @click="showLangPicker = true">
          <span class="cls-row-label">語言</span>
          <div class="cls-row-right">
            <span class="cls-row-value">{{ currentLangName }}</span>
            <svg class="cls-chevron" width="7" height="12" viewBox="0 0 7 12" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M1 1L6 6L1 11" stroke="#bbb" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- 國家/地區選擇 Bottom Sheet -->
    <div v-if="showCountryPicker" class="lang-overlay" @click.self="closeCountryPicker">
      <div class="lang-sheet lang-sheet-country">
        <div class="lang-sheet-header">
          <span class="lang-sheet-title">選擇國家/地區</span>
          <button class="lang-sheet-close" @click="closeCountryPicker">✕</button>
        </div>
        <div class="lang-search-wrap">
          <svg class="lang-search-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#aaa" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input
            v-model="countrySearch"
            class="lang-search-input"
            type="text"
            placeholder="搜尋國家/地區"
            autocomplete="off"
          />
        </div>
        <ul class="lang-list">
          <li
            v-for="country in filteredCountries"
            :key="country.code"
            class="lang-option"
            :class="{ selected: currentCountry === country.code }"
            @click="setCountry(country.code)"
          >
            <span>{{ getCountryName(country, currentLocale) }}</span>
            <svg v-if="currentCountry === country.code" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#d71921" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
          </li>
          <li v-if="filteredCountries.length === 0" class="lang-no-result">查無結果</li>
        </ul>
      </div>
    </div>

    <!-- 語言選擇 Bottom Sheet -->
    <div v-if="showLangPicker" class="lang-overlay" @click.self="showLangPicker = false">
      <div class="lang-sheet">
        <div class="lang-sheet-header">
          <span class="lang-sheet-title">選擇語言</span>
          <button class="lang-sheet-close" @click="showLangPicker = false">✕</button>
        </div>
        <ul class="lang-list">
          <li
            v-for="lang in languages"
            :key="lang.code"
            class="lang-option"
            :class="{ selected: currentLocale === lang.code }"
            @click="setLocale(lang.code)"
          >
            <span>{{ lang.name }}</span>
            <svg v-if="currentLocale === lang.code" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#d71921" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import PageHeader from '../components/PageHeader.vue'
import { countries, getCountryName } from '../utils/countries'

const { locale } = useI18n()

const currentLocale = computed(() => locale.value)
const showLangPicker = ref(false)
const showCountryPicker = ref(false)
const countrySearch = ref('')

const currentCountry = ref(localStorage.getItem('app_country') || 'TW')
const currentCountryName = computed(() => {
  const c = countries.find(c => c.code === currentCountry.value)
  return c ? getCountryName(c, currentLocale.value) : '台灣'
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

const setCountry = (code) => {
  currentCountry.value = code
  localStorage.setItem('app_country', code)
  closeCountryPicker()
}

const languages = [
  { code: 'zh-TW', name: '繁體中文（台灣）' },
  { code: 'en',    name: 'English' },
]

const currentLangName = computed(
  () => languages.find(l => l.code === currentLocale.value)?.name ?? '繁體中文（台灣）'
)

const setLocale = (code) => {
  locale.value = code
  localStorage.setItem('app_locale', code)
  showLangPicker.value = false
}
</script>

<style scoped>
.cls-page {
  min-height: 100vh;
  background: #f5f5f5;
}

/* Content */
.cls-content {
  padding: 1rem 0;
}

/* List */
.cls-list {
  background: #fff;
}

.cls-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.05rem 1.5rem;
  border-bottom: 1px solid #efefef;
}

.cls-row:last-child {
  border-bottom: none;
}

.cls-row-clickable {
  cursor: pointer;
  transition: background 0.15s;
}

.cls-row-clickable:active {
  background: #f9f9f9;
}

.cls-row-label {
  font-size: 0.95rem;
  color: #1a1a1a;
}

.cls-row-right {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.cls-row-value {
  font-size: 0.9rem;
  color: #999;
}

.cls-chevron {
  flex-shrink: 0;
}

/* Language bottom sheet */
.lang-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  z-index: 200;
  display: flex;
  align-items: flex-end;
  justify-content: center;
}

.lang-sheet {
  background: #fff;
  width: 100%;
  max-width: var(--app-max-width, 480px);
  border-radius: 16px 16px 0 0;
  padding-bottom: env(safe-area-inset-bottom, 1rem);
}

.lang-sheet-country {
  display: flex;
  flex-direction: column;
  max-height: 70vh;
}

.lang-sheet-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #f0f0f0;
}

.lang-sheet-title {
  font-size: 1rem;
  font-weight: 700;
  color: #1a1a1a;
}

.lang-sheet-close {
  background: none;
  border: none;
  font-size: 1rem;
  color: #999;
  cursor: pointer;
  padding: 4px;
  line-height: 1;
}

.lang-search-wrap {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 1rem;
  border-bottom: 1px solid #f0f0f0;
  flex-shrink: 0;
}

.lang-search-icon {
  flex-shrink: 0;
}

.lang-search-input {
  flex: 1;
  border: none;
  outline: none;
  font-size: 0.9rem;
  color: #1a1a1a;
  background: transparent;
}

.lang-search-input::placeholder {
  color: #aaa;
}

.lang-list {
  list-style: none;
  margin: 0;
  padding: 0;
  overflow-y: auto;
}

.lang-option {
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

.lang-option:last-child {
  border-bottom: none;
}

.lang-option:hover {
  background: #fafafa;
}

.lang-option.selected {
  color: #d71921;
  font-weight: 600;
}

.lang-no-result {
  padding: 1.5rem;
  text-align: center;
  font-size: 0.9rem;
  color: #aaa;
}
</style>
