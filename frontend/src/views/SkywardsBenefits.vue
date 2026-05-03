<script setup>
import { ref, onMounted } from 'vue'
import PageLayout from '../components/PageLayout.vue'

const loading = ref(true)
const errorMsg = ref('')
const benefit = ref(null)
const currentTier = ref('regular')

const tierNames = {
  regular: '藍卡',
  blue: '藍卡',
  silver: '銀卡',
  gold: '金卡',
  platinum: '白金卡',
}

const loadBenefits = async () => {
  loading.value = true
  errorMsg.value = ''
  try {
    const token = localStorage.getItem('token') || ''
    const headers = token ? { Authorization: `Bearer ${token}` } : {}
    const response = await fetch('/api/v1/skywards/benefits', { headers })
    if (!response.ok) throw new Error('load failed')
    const data = await response.json()
    const payload = data.data || data
    currentTier.value = payload.tier || 'regular'
    const item = payload.item || payload.items?.[0] || null
    benefit.value = item
      ? {
          ...item,
          image_url: item.image_url || '',
          sort_order: Number(item.sort_order || 0),
        }
      : null
  } catch {
    errorMsg.value = '載入失敗，請稍後再試'
  } finally {
    loading.value = false
  }
}

onMounted(loadBenefits)
</script>

<template>
  <PageLayout title="我的權益" back-to="/skywards#tier" theme="white">
    <div class="benefits-page-content">
      <div v-if="loading" class="state-message">載入中...</div>
      <div v-else-if="errorMsg" class="state-message state-error">{{ errorMsg }}</div>
      <div v-else-if="!benefit" class="state-message">目前尚無{{ tierNames[currentTier] || '此等級' }}權益說明</div>
      <article v-else class="benefit-section">
        <div v-if="benefit.image_url" class="benefit-image-wrap">
          <img :src="benefit.image_url" :alt="benefit.label || 'Skywards 權益圖片'" class="benefit-image" />
        </div>
        <div class="benefit-text">
          <h1 v-if="benefit.label" class="benefit-title">{{ benefit.label }}</h1>
          <div v-if="benefit.content" class="benefit-rich-content" v-html="benefit.content"></div>
        </div>
      </article>
    </div>
  </PageLayout>
</template>

<style scoped>
.benefits-page-content {
  width: 100%;
  max-width: 760px;
  margin: 0 auto;
  padding: 1rem 1rem 2rem;
  box-sizing: border-box;
}

.state-message {
  color: #999;
  text-align: center;
  padding: 3rem 1rem;
  font-size: 0.95rem;
}

.state-error {
  color: #d71921;
}

.benefit-section {
  margin-bottom: 2rem;
  text-align: left;
}

.benefit-section:last-child {
  margin-bottom: 0;
}

.benefit-image-wrap {
  width: 100%;
  display: flex;
  justify-content: center;
  margin-bottom: 1.25rem;
  background: #f7f7f7;
  border-radius: 8px;
  overflow: hidden;
}

.benefit-image {
  width: 100%;
  max-width: 720px;
  max-height: 420px;
  object-fit: contain;
  display: block;
}

.benefit-text {
  color: #333;
  word-break: break-word;
  overflow-wrap: break-word;
}

.benefit-title {
  font-size: 1.35rem;
  line-height: 1.35;
  font-weight: 800;
  margin: 0 0 0.85rem;
  color: #111;
}

.benefit-rich-content {
  font-size: 1rem;
  line-height: 1.75;
  color: #444;
}

.benefit-rich-content :deep(p) { margin: 0 0 0.85rem; }
.benefit-rich-content :deep(ul) { padding-left: 1.5em; list-style: disc; margin: 0.75rem 0; }
.benefit-rich-content :deep(ol) { padding-left: 1.5em; list-style: decimal; margin: 0.75rem 0; }
.benefit-rich-content :deep(strong) { font-weight: 700; }
.benefit-rich-content :deep(img) { max-width: 100%; height: auto; }

@media (min-width: 768px) {
  .benefits-page-content {
    padding: 1.5rem 1.25rem 3rem;
  }

  .benefit-title {
    font-size: 1.5rem;
  }
}

@media (max-width: 480px) {
  .benefit-image {
    max-height: 260px;
  }
}
</style>