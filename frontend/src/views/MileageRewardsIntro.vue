<template>
  <PageLayout title="使用 Skywards 會員里程" :back-to="backPath" theme="white">
    <div class="intro-page">
      <section class="intro-card">
        <div class="intro-head">
          <p class="eyebrow">Mileage Rewards</p>
          <h2 class="intro-title">{{ itemName || '里程兌換商品介紹' }}</h2>
        </div>

        <div class="intro-body" v-html="introHtml"></div>

        <button class="intro-button" @click="goToRewards">前往里程兌換商品</button>
      </section>
    </div>
  </PageLayout>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import PageLayout from '../components/PageLayout.vue'
import { apiFetch } from '../utils/apiFetch'

const route = useRoute()
const router = useRouter()

const defaultIntroHtml = `
  <p>您可使用累積的 Skywards 會員里程兌換指定商品優惠，進入商品頁後可查看可兌換項目、折抵比例與所需點數。</p>
  <p>兌換前請先確認商品庫存、可折抵里程與個人可用哩程數，送出後請依頁面指示完成後續流程。</p>
`

const introHtml = ref(defaultIntroHtml)
const itemId = computed(() => route.query.item_id ? Number(route.query.item_id) : 0)

const itemName = computed(() => route.query.item_name ? decodeURIComponent(route.query.item_name) : '')
const backPath = computed(() => {
  const base = '/mileage-redemption'
  return route.query.from ? `${base}?from=${route.query.from}` : base
})

const goToRewards = () => {
  router.push({
    path: '/mileage-rewards',
    query: { ...route.query },
  })
}

const loadIntroContent = async () => {
  try {
    const res = await apiFetch('/api/v1/mileage/redemption-items', { auth: true })
    if (!res.ok) return
    const json = await res.json()
    const items = json.data?.items || json.items || []
    const item = items.find(entry => Number(entry.id) === itemId.value)
    introHtml.value = item?.intro_html || defaultIntroHtml
  } catch {}
}

onMounted(loadIntroContent)
</script>

<style scoped>
.intro-page {
  flex: 1;
  padding: 1rem;
  background:
    radial-gradient(circle at top left, rgba(215, 25, 33, 0.08), transparent 34%),
    linear-gradient(180deg, #f8f5ef 0%, #ffffff 55%);
}

.intro-card {
  max-width: 760px;
  margin: 0 auto;
  background: #ffffff;
  border: 1px solid rgba(215, 25, 33, 0.08);
  border-radius: 24px;
  padding: 1.5rem;
  box-shadow: 0 18px 48px rgba(0, 0, 0, 0.06);
}

.intro-head {
  margin-bottom: 1.25rem;
}

.eyebrow {
  margin: 0 0 0.5rem;
  font-size: 0.78rem;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #b7791f;
}

.intro-title {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 800;
  color: #1f2937;
}

.intro-body {
  color: #4b5563;
  line-height: 1.8;
  font-size: 0.95rem;
}

.intro-body :deep(p) {
  margin: 0 0 1rem;
}

.intro-body :deep(h1),
.intro-body :deep(h2),
.intro-body :deep(h3) {
  color: #111827;
  margin: 1.25rem 0 0.75rem;
}

.intro-body :deep(ul),
.intro-body :deep(ol) {
  padding-left: 1.25rem;
  margin: 0 0 1rem;
}

.intro-button {
  width: 100%;
  margin-top: 1.5rem;
  border: none;
  border-radius: 999px;
  background: linear-gradient(135deg, #d71921, #f15656);
  color: #ffffff;
  font-size: 1rem;
  font-weight: 700;
  padding: 0.95rem 1rem;
}
</style>
