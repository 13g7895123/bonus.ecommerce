<template>
  <div class="cls-page">
    <PageHeader title="國家/地區和語言" back-to="/settings" />

    <div class="cls-content">
      <!-- 國家/地區 -->
      <div class="cls-section-title">國家/地區</div>
      <div class="cls-card">
        <div class="cls-item">
          <span class="cls-label">目前國家/地區</span>
          <span class="cls-value">{{ currentCountry }}</span>
        </div>
      </div>

      <!-- 語言 -->
      <div class="cls-section-title">語言</div>
      <div class="cls-card">
        <div
          v-for="lang in languages"
          :key="lang.code"
          class="cls-item cls-item-clickable"
          :class="{ active: currentLocale === lang.code }"
          @click="setLocale(lang.code)"
        >
          <span class="cls-label">{{ lang.name }}</span>
          <span v-if="currentLocale === lang.code" class="cls-check">✓</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import PageHeader from '../components/PageHeader.vue'

const { locale } = useI18n()

const currentLocale = computed(() => locale.value)

const languages = [
  { code: 'zh-TW', name: '繁體中文（台灣）' },
  { code: 'en',    name: 'English' },
]

const currentCountry = ref('台灣')

const setLocale = (code) => {
  locale.value = code
  localStorage.setItem('app_locale', code)
}
</script>

<style scoped>
.cls-page {
  min-height: 100vh;
  background: #f5f5f5;
}

.cls-content {
  padding: 1.25rem 1.5rem;
}

.cls-section-title {
  font-size: 0.8rem;
  font-weight: 600;
  color: #888;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin: 1.25rem 0 0.5rem;
}

.cls-card {
  background: #fff;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}

.cls-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid #f5f5f5;
  font-size: 0.95rem;
  color: #1a1a1a;
}

.cls-item:last-child {
  border-bottom: none;
}

.cls-item-clickable {
  cursor: pointer;
  transition: background 0.15s;
}

.cls-item-clickable:hover {
  background: #fafafa;
}

.cls-item-clickable.active {
  color: #d71921;
  font-weight: 600;
}

.cls-check {
  color: #d71921;
  font-weight: 700;
  font-size: 1.1rem;
}

.cls-value {
  color: #666;
  font-size: 0.9rem;
}
</style>
