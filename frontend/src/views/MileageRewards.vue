<template>
  <div class="ml-page">
    <PageHeader :title="pageTitle" :back-to="backPath" :bordered="false" />

    <div v-if="loading" class="state-loading">{{ t('common.loading') }}</div>
    <div v-else-if="products.length === 0" class="state-empty">{{ t('mileageRewards.empty') }}</div>
    <div v-else class="product-grid">
      <div
        v-for="item in products"
        :key="item.id"
        :class="['product-card', item.is_active == 0 ? 'product-card--disabled' : '']"
        @click="goToDetail(item.id, item.is_active)"
      >
        <div class="product-img-wrapper">
          <img :src="item.image_url || '/product-1.png'" :alt="item.name" class="product-img" />
        </div>
        <div class="product-info">
          <div class="info-row name">{{ item.name }}</div>
          <div class="info-row gray">{{ t('mileageRewards.mileageReward') }}{{ Number(item.mileage_amount || 0).toFixed(0) }}%(${{ Math.round(Number(item.price) * Number(item.mileage_amount || 0) / 100).toLocaleString() }})</div>
          <div class="info-row red">${{ Number(item.price).toLocaleString() }}</div>
          <div class="info-row red">{{ t('mileageRewards.milesPoints') }}:<span class="red-text">{{ Number(item.miles_points || 0).toLocaleString() }}</span></div>
          <div class="info-row gray">數量：{{ item.stock }}</div>
          <div v-if="Number(item.purchase_target || 0) > 0" class="purchase-target-badge">
            🔥 已購買人數達 {{ Number(item.purchase_target).toLocaleString() }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useToast } from '../composables/useToast'
import PageHeader from '../components/PageHeader.vue'

const { t } = useI18n()
const router = useRouter()
const route  = useRoute()
const toast  = useToast()

const itemId   = computed(() => route.query.item_id ? Number(route.query.item_id) : null)
const itemName = computed(() => route.query.item_name ? decodeURIComponent(route.query.item_name) : null)
const pageTitle = computed(() => itemName.value || t('mileageRewards.title'))
const backPath  = computed(() => {
  if (itemId.value) {
    const base = '/mileage-redemption'
    return route.query.from ? `${base}?from=${route.query.from}` : base
  }
  return '/skywards'
})

const loading       = ref(true)
const products      = ref([])
const pendingOrders = ref([])

const hasPendingOrder = (productId) =>
  pendingOrders.value.some(o => Number(o.product_id) === Number(productId))

const goToDetail = (productId, isActive) => {
  if (isActive == 0) {
    toast.error('此商品目前已售罄')
    return
  }
  const q = { product_id: productId }
  if (itemId.value) q.item_id = itemId.value
  if (itemName.value) q.item_name = itemName.value
  if (route.query.from) q.from = route.query.from
  router.push({ path: '/mileage-reward-detail', query: q })
}

const loadData = async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('token') || ''
    const headers = token ? { Authorization: `Bearer ${token}` } : {}

    const [productsRes, pendingRes] = await Promise.all([
      fetch(`/api/v1/mileage/reward-products${itemId.value ? `?item_id=${itemId.value}` : ''}`, { headers }),
      fetch('/api/v1/mileage/reward-orders/my-pending', { headers }),
    ])

    if (productsRes.ok) {
      const data = await productsRes.json()
      products.value = data.items || data.data?.items || []
    }

    if (pendingRes.ok) {
      const data = await pendingRes.json()
      pendingOrders.value = data.items || data.data?.items || []
    }
  } catch {
    // silently ignore
  } finally {
    loading.value = false
  }
}

onMounted(loadData)
</script>

<style scoped>
.ml-page {
  background-color: #ffffff;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.state-loading,
.state-empty {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #999;
  font-size: 0.95rem;
  padding: 3rem 1rem;
}

/* 商品格狀列表 */
.product-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
  padding: 1rem;
}

/* 商品卡片 */
.product-card {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  border: 1px solid #ccc;
  border-radius: 8px;
  padding: 0.75rem 0.5rem;
  overflow: hidden;
  background-color: #ffffff;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
}

.product-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.product-card--disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.product-card--disabled:hover {
  transform: none;
  box-shadow: none;
}

/* 圖片區 */
.product-img-wrapper {
  position: relative;
  width: 100%;
  aspect-ratio: 1 / 1;
  overflow: hidden;
  background-color: #f0f0f0;
  border-radius: 4px;
}

.product-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.pending-badge {
  display: inline-flex;
  justify-content: center;
  align-items: center;
  background-color: #f15656;
  color: #ffffff;
  font-size: 0.68rem;
  font-weight: 600;
  height: 20px;
  padding: 0 16px;
  border-radius: 9999px;
  white-space: nowrap;
  margin-bottom: 4px;
}

/* 文字區 */
.product-info {
  width: 100%;
  text-align: left;
  padding: 0 0.25rem;
}

.info-row {
  margin-bottom: 0.3rem;
  line-height: 1.4;
}

.info-row.name {
  color: #000;
  font-weight: bold;
  font-size: 0.9rem;
}

.info-row.gray {
  color: #555;
  font-size: 0.82rem;
}

.info-row.red {
  color: #d71921;
  font-size: 0.82rem;
  font-weight: 600;
}

.red-text {
  color: #d71921;
  font-weight: 600;
}

.purchase-target-badge {
  display: inline-block;
  margin-top: 0.35rem;
  background: linear-gradient(135deg, #ff6b35, #f7c59f);
  color: #7a2400;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 999px;
  line-height: 1.4;
  letter-spacing: 0.01em;
  box-shadow: 0 1px 4px rgba(255,107,53,0.35);
}
</style>
