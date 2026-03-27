<template>
  <div class="panel">
    <div class="panel-header">
      <span class="panel-title">系統總覽</span>
      <button class="btn btn-outline" :disabled="loading" @click="load">
        <RefreshCw :size="14" />{{ loading ? '載入中...' : '重新整理' }}
      </button>
    </div>

    <div v-if="loading" class="state-msg">載入中...</div>
    <div v-else class="stats-grid">
      <div class="stat-card">
        <div class="stat-label">API 紀錄總數</div>
        <div class="stat-value">{{ stats.total_api_logs?.toLocaleString() ?? '-' }}</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">API 請求 (24h)</div>
        <div class="stat-value">{{ stats.api_requests_24h?.toLocaleString() ?? '-' }}</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">第三方呼叫總數</div>
        <div class="stat-value">{{ stats.total_third_party_logs?.toLocaleString() ?? '-' }}</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">第三方呼叫 (24h)</div>
        <div class="stat-value">{{ stats.third_party_calls_24h?.toLocaleString() ?? '-' }}</div>
      </div>
      <div class="stat-card stat-card-warn">
        <div class="stat-label">第三方失敗 (24h)</div>
        <div class="stat-value">{{ stats.third_party_failures_24h?.toLocaleString() ?? '-' }}</div>
      </div>
    </div>

    <div class="quick-links">
      <button class="btn btn-primary" @click="$router.push('/sadmin/api-logs')">查看所有 API 紀錄 →</button>
      <button class="btn btn-secondary" @click="$router.push('/sadmin/third-party-logs')">查看第三方 API 紀錄 →</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RefreshCw } from 'lucide-vue-next'

const stats   = ref({})
const loading = ref(false)

const load = async () => {
  loading.value = true
  try {
    const res = await fetch('/api/v1/sadmin/stats')
    stats.value = await res.json()
  } finally { loading.value = false }
}

onMounted(load)
</script>

<style scoped>
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1rem;
  padding: 1.25rem;
}
.stat-card {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 1.1rem 1.25rem;
}
.stat-card-warn { border-color: #fca5a5; background: #fff5f5; }
.stat-label { font-size: 0.78rem; color: #64748b; margin-bottom: 0.4rem; }
.stat-value { font-size: 1.6rem; font-weight: 700; color: #1e293b; }
.stat-card-warn .stat-value { color: #dc2626; }
.quick-links { display: flex; gap: 0.75rem; padding: 0 1.25rem 1.25rem; flex-wrap: wrap; }
.btn-secondary {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.875rem;
  cursor: pointer; transition: background 0.15s;
  background: #7c3aed; color: #fff; border: none;
}
.btn-secondary:hover { background: #6d28d9; }
</style>
