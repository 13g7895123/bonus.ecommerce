<template>
  <div class="ml-page">
    <PageHeader :title="t('mileageRewards.title')" back-to="/mileage-rewards" :bordered="false" />

    <div v-if="loading" class="state-loading">{{ t('common.loading') }}</div>
    <div v-else-if="!product" class="state-empty">{{ t('mileageRewards.empty') }}</div>
    <template v-else>
      <div class="product-section">
        <!-- 產品圖片 -->
        <div class="product-image-wrap">
          <img :src="product.image_url || '/product-1.png'" :alt="product.name" class="product-image" />
          <!-- 審核中徽章 -->
          <span v-if="hasPendingOrder" class="pending-badge">{{ t('mileageRewards.pendingReview') }}</span>
        </div>

        <!-- 數量列 -->
        <div class="qty-row">
          <span class="qty-label">數量</span>
          <div class="qty-controls">
            <div class="qty-box">
              <button class="qty-btn" @click="decQty">－</button>
              <span class="qty-num">{{ quantity }}</span>
              <button class="qty-btn" @click="incQty">＋</button>
            </div>
            <span class="stock-label">尚有庫存</span>
          </div>
        </div>
      </div>

      <!-- 金額資訊 - 預設樣式 -->
      <div v-if="product.display_style !== 'horizontal'" class="amount-section">
        <div class="amount-row">
          <span class="amount-label">{{ t('mileageRewards.accountBalance') }}</span>
          <span class="amount-value red">$ {{ balance.toLocaleString() }}</span>
        </div>
        <div class="amount-row">
          <span class="amount-label">{{ t('mileageRewards.mileageReward') }}</span>
          <span class="amount-value red">$ {{ mileageReward.toLocaleString() }}</span>
        </div>
      </div>

      <!-- 金額資訊 - 水平樣式 -->
      <div v-else class="amount-section-horizontal">
        <div class="amount-card">
          <span class="amount-card-label">{{ t('mileageRewards.mileageReward') }}</span>
          <span class="amount-card-value">$ {{ mileageReward.toLocaleString() }}</span>
        </div>
        <div class="amount-card">
          <span class="amount-card-label">{{ t('mileageRewards.accountBalance') }}</span>
          <span class="amount-card-value">$ {{ balance.toLocaleString() }}</span>
        </div>
      </div>

      <!-- 錯誤提示 -->
      <div v-if="errorMsg" class="error-msg">{{ errorMsg }}</div>

      <!-- 確認按鈕 -->
      <div class="action-section">
        <AppButton block @click="confirmPurchase">{{ t('mileageRewards.confirm') }}</AppButton>
      </div>

      <!-- 客服連結 -->
      <div class="support-section">
        <span>{{ t('mileageRewards.helpText') }}</span>
        <router-link to="/customer-service" class="support-link">{{ t('mileageRewards.contactCS') }}</router-link>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import PageHeader from '../components/PageHeader.vue'
import AppButton from '../components/AppButton.vue'

const { t } = useI18n()
const router = useRouter()
const route  = useRoute()

const loading       = ref(true)
const product       = ref(null)
const quantity      = ref(1)
const balance       = ref(0)
const milesBalance  = ref(0)
const pendingOrders = ref([])
const errorMsg      = ref('')

const mileageReward = computed(() => {
  if (!product.value) return 0
  return Number(product.value.mileage_amount) * quantity.value
})

const milesPoints = computed(() => {
  if (!product.value) return 0
  return Number(product.value.miles_points || 0) * quantity.value
})

const hasPendingOrder = computed(() =>
  pendingOrders.value.some(o => Number(o.product_id) === Number(product.value?.id))
)

const decQty = () => {
  if (quantity.value > 1) quantity.value--
  errorMsg.value = ''
}

const incQty = () => {
  if (!product.value) return
  if (quantity.value < product.value.stock) quantity.value++
  errorMsg.value = ''
}

const confirmPurchase = () => {
  errorMsg.value = ''
  const totalPrice = Number(product.value.price) * quantity.value
  const totalMiles = Number(product.value.miles_points || 0) * quantity.value

  if (balance.value < totalPrice) {
    errorMsg.value = t('mileageRewards.errInsuffBalance')
    return
  }
  if (milesBalance.value < totalMiles) {
    errorMsg.value = t('mileageRewards.errInsuffMiles')
    return
  }

  router.push({
    path: '/mileage-reward-confirm',
    query: { product_id: product.value.id, qty: quantity.value },
  })
}

const loadData = async () => {
  loading.value = true
  const productId = Number(route.query.product_id)
  try {
    const token = localStorage.getItem('token') || ''
    const headers = token ? { Authorization: `Bearer ${token}` } : {}

    const [productsRes, walletRes, pendingRes] = await Promise.all([
      fetch('/api/v1/mileage/reward-products', { headers }),
      fetch('/api/v1/wallet/info', { headers }),
      fetch('/api/v1/mileage/reward-orders/my-pending', { headers }),
    ])

    if (productsRes.ok) {
      const data  = await productsRes.json()
      const items = data.items || data.data?.items || []
      product.value = items.find(p => Number(p.id) === productId) || items[0] || null
    }

    if (walletRes.ok) {
      const data = await walletRes.json()
      const info = data.data || data
      balance.value      = Number(info.balance ?? 0)
      milesBalance.value = Number(info.miles_balance ?? 0)
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

/* 產品圖片區 */
.product-section {
  padding: 1.25rem 1rem 0;
}

.product-image-wrap {
  position: relative;
  display: flex;
  justify-content: center;
  margin-bottom: 1.25rem;
}

.product-image {
  width: 240px;
  height: 240px;
  object-fit: contain;
  border-radius: 8px;
}

/* 審核中徽章 */
.pending-badge {
  position: absolute;
  display: flex;
  align-items: center;
  justify-content: center;
  bottom: 0;
  left: 50%;
  transform: translateX(calc(-50% - 60px));
  background-color: #f15656;
  color: #ffffff;
  font-size: 0.75rem;
  font-weight: 600;
  padding: 3px 12px;
  border-radius: 9999px;
  white-space: nowrap;
}

/* 數量列 */
.qty-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  flex-wrap: wrap;
  padding: 0 0.25rem;
}

.qty-label {
  font-size: 0.95rem;
  color: #333;
  flex: 1;
  min-width: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.qty-controls {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-shrink: 0;
}

.qty-box {
  display: flex;
  align-items: center;
  border: 1px solid #ccc;
  border-radius: 6px;
  overflow: hidden;
}

.qty-btn {
  width: 32px;
  height: 32px;
  background: transparent;
  border: none;
  border-radius: 0;
  cursor: pointer;
  font-size: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.15s;
}

.qty-btn:first-child {
  border-right: 1px solid #ccc;
}

.qty-btn:last-child {
  border-left: 1px solid #ccc;
}

.qty-btn:hover {
  background: #f5f5f5;
}

.qty-num {
  min-width: 32px;
  text-align: center;
  font-size: 0.95rem;
  padding: 0 0.5rem;
  color: #333;
}

.stock-label {
  font-size: 0.85rem;
  color: #555;
  white-space: nowrap;
}

/* 金額資訊 */
.amount-section {
  margin-top: 1.5rem;
  padding: 0 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
}

/* 水平樣式 */
.amount-section-horizontal {
  margin-top: 1.5rem;
  padding: 0 1rem;
  display: flex;
  gap: 0.75rem;
}

.amount-card {
  flex: 1;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  padding: 0.75rem 0.5rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.4rem;
}

.amount-card-label {
  font-size: 0.85rem;
  color: #555;
}

.amount-card-value {
  font-size: 1.1rem;
  font-weight: 700;
  color: #d71921;
}

.amount-row {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 0.75rem;
}

.amount-label {
  font-size: 0.9rem;
  color: #555;
}

.amount-value {
  font-size: 1rem;
  font-weight: 600;
  color: #333;
  min-width: 80px;
  text-align: right;
}

.amount-value.red {
  color: #d71921;
}

/* 錯誤訊息 */
.error-msg {
  margin: 0.75rem 1rem 0;
  padding: 0.6rem 0.9rem;
  background: #fef2f2;
  border: 1px solid #fca5a5;
  border-radius: 8px;
  color: #dc2626;
  font-size: 0.88rem;
}

/* 確認按鈕 */
.action-section {
  padding: 1.5rem 1rem 0.75rem;
}

/* 客服連結 */
.support-section {
  text-align: center;
  padding: 0.5rem 1rem 1.5rem;
  font-size: 0.88rem;
  color: #666;
}

.support-link {
  color: #1a6fd4;
  text-decoration: none;
  font-weight: 500;
}

.support-link:hover {
  text-decoration: underline;
}
</style>
