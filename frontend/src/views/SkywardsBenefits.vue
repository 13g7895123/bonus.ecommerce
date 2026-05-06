<script setup>
import { ref, computed, onMounted } from 'vue'
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

const benefitTitle = computed(() => {
  const title = benefit.value?.label?.trim()
  if (title) return title
  return `${tierNames[currentTier.value] || 'Skywards'}權益`
})

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
          <img :src="benefit.image_url" :alt="benefitTitle" class="benefit-image" />
        </div>
        <div class="benefit-text-card">
          <h1 class="benefit-title">{{ benefitTitle }}</h1>
          <div v-if="benefit.content" class="benefit-rich-content" v-html="benefit.content"></div>
        </div>
      </article>
    </div>
  </PageLayout>
</template>

<style scoped>
.benefits-page-content {
  width: 100%;
  min-height: 100%;
  margin: 0;
  padding: 0 0 2.5rem;
  background: #f5f5f5;
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
  margin: 0;
  background: #e5e7eb;
  overflow: hidden;
}

.benefit-image {
  width: 100%;
  height: clamp(240px, 52vw, 520px);
  object-fit: cover;
  display: block;
}

.benefit-text-card {
  width: calc(100% - 2.5rem);
  max-width: 760px;
  margin: 0 auto;
  padding: 1.35rem 1.25rem 1.45rem;
  color: #333;
  background: #fff;
  border-radius: 0 0 4px 4px;
  box-shadow: 0 2px 12px rgba(15, 23, 42, 0.16);
  box-sizing: border-box;
  word-break: break-word;
  overflow-wrap: break-word;
}

.benefit-title {
  font-size: 1rem;
  line-height: 1.45;
  font-weight: 900;
  margin: 0 0 1rem;
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
.benefit-rich-content :deep(li) { margin-bottom: 0.35rem; }
.benefit-rich-content :deep(strong) { font-weight: 700; }
.benefit-rich-content :deep(em) { font-style: italic; }
.benefit-rich-content :deep(u) { text-decoration: underline; }
.benefit-rich-content :deep(s) { text-decoration: line-through; }
.benefit-rich-content :deep(h1), .benefit-rich-content :deep(h2), .benefit-rich-content :deep(h3) { font-weight: 700; line-height: 1.3; margin: 1.25rem 0 0.5rem; }
.benefit-rich-content :deep(h1) { font-size: 1.5rem; }
.benefit-rich-content :deep(h2) { font-size: 1.25rem; }
.benefit-rich-content :deep(h3) { font-size: 1.1rem; }
.benefit-rich-content :deep(blockquote) { border-left: 3px solid #ccc; padding-left: 1em; color: #666; margin: 0.75rem 0; }
.benefit-rich-content :deep(a) { color: #d71921; text-decoration: underline; }
.benefit-rich-content :deep(img) { max-width: 100%; height: auto; border-radius: 4px; }
.benefit-rich-content :deep(hr) { border: none; border-top: 1px solid #e0e0e0; margin: 1rem 0; }

@media (min-width: 768px) {
  .benefit-title {
    font-size: 1.08rem;
  }
}

@media (max-width: 480px) {
  .benefit-image {
    height: 260px;
  }

  .benefit-text-card {
    width: calc(100% - 2rem);
    padding: 1.15rem 1rem 1.25rem;
  }
}
</style>