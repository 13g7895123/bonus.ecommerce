<template>
  <div class="ml-page">
    <PageHeader :title="t('mileageRewards.title')" :back-to="backPath" :bordered="false" />

    <div v-if="loading" class="state-loading">{{ t('common.loading') }}</div>
    <div v-else-if="!product" class="state-empty">{{ t('mileageRewards.empty') }}</div>
    <template v-else>
      <div class="page-body">
        <div class="content-wrap">
          <div class="product-section">
          <!-- 產品圖片 -->
          <div class="product-image-wrap">
            <img :src="product.image_url || '/product-1.png'" :alt="product.name" class="product-image" />
            <!-- 審核中徽章 -->
            <span v-if="hasPendingOrder" class="pending-badge">{{ t('mileageRewards.pendingReview') }}</span>
          </div>

          <!-- 品名 -->
          <div class="product-name">{{ product.name }}</div>

          <!-- 數量列 -->
          <div class="qty-row" :class="{ 'qty-row--horizontal': displayStyle === 'horizontal' }">
            <span class="qty-label">數量</span>
            <div class="qty-controls" :class="{ 'qty-controls--horizontal': displayStyle === 'horizontal' }">
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
          <div v-if="displayStyle !== 'horizontal'" class="amount-section">
            <div class="amount-row">
              <span class="amount-label">{{ t('mileageRewards.accountBalance') }}</span>
              <span class="amount-value red">$ {{ balance.toLocaleString() }}</span>
            </div>
            <div class="amount-row">
              <span class="amount-label">里程點數餘額</span>
              <span class="amount-value red">{{ milesBalance.toLocaleString() }} 點</span>
            </div>
            <hr class="amount-divider" />
            <div class="amount-row">
              <span class="amount-label">商品金額</span>
              <span class="amount-value red">$ {{ totalPrice.toLocaleString() }}</span>
            </div>
            <div v-if="totalMiles > 0" class="amount-row">
              <span class="amount-label">所需里程</span>
              <span class="amount-value red">{{ totalMiles.toLocaleString() }} 點</span>
            </div>
            <div class="amount-row">
              <span class="amount-label">回饋（{{ mileagePercent }}%）</span>
              <span class="amount-value green">$ {{ mileageReward.toLocaleString() }}</span>
            </div>
          </div>

          <!-- 金額資訊 - 水平樣式 -->
          <div v-else class="amount-section-horizontal">
            <div class="amount-card amount-card-left">
              <span class="amount-card-label">商品金額</span>
              <span class="amount-card-value">$ {{ totalPrice.toLocaleString() }}</span>
            </div>
            <div class="amount-card amount-card-right">
              <span class="amount-card-label">回饋（{{ mileagePercent }}%）</span>
              <span class="amount-card-value green">$ {{ mileageReward.toLocaleString() }}</span>
            </div>
          </div>
          <div v-if="displayStyle === 'horizontal' && totalMiles > 0" class="miles-hint">
            所需里程：{{ totalMiles.toLocaleString() }} 點
          </div>

        <!-- 錯誤提示 -->
          <div v-if="errorMsg" class="error-msg">{{ errorMsg }}</div>

          <!-- 垂直模式：確認按鈕在里程回饋下方 -->
          <div v-if="displayStyle !== 'horizontal'" class="action-section">
            <AppButton block @click="confirmPurchase">{{ t('mileageRewards.confirm') }}</AppButton>
            <div class="support-section">
              <span>{{ t('mileageRewards.helpText') }}</span>
              <router-link to="/customer-service" class="support-link">{{ t('mileageRewards.contactCS') }}</router-link>
            </div>
          </div>
        </div><!-- end content-wrap -->
      </div>

      <!-- 底部：水平模式確認按鈕 + 客服連結 -->
      <div class="page-footer" :class="{ 'page-footer--no-border': displayStyle === 'horizontal' }">
        <div v-if="displayStyle === 'horizontal'" class="content-wrap">
          <div class="action-section">
            <AppButton block @click="confirmPurchase">{{ t('mileageRewards.confirm') }}</AppButton>
            <div class="support-section">
              <span>{{ t('mileageRewards.helpText') }}</span>
              <router-link to="/customer-service" class="support-link">{{ t('mileageRewards.contactCS') }}</router-link>
            </div>
          </div>
        </div>
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
const displayStyle  = ref('default')

const backPath = computed(() => {
  const q = new URLSearchParams()
  if (route.query.item_id)   q.set('item_id',   route.query.item_id)
  if (route.query.item_name) q.set('item_name', route.query.item_name)
  const qs = q.toString()
  return `/mileage-rewards${qs ? '?' + qs : ''}`
})

const mileagePercent = computed(() => {
  if (!product.value) return 0
  return Number(product.value.mileage_amount)
})

const mileageReward = computed(() => {
  if (!product.value) return 0
  return Math.round(Number(product.value.price) * Number(product.value.mileage_amount) / 100 * quantity.value)
})

const milesPoints = computed(() => {
  if (!product.value) return 0
  return Number(product.value.miles_points || 0) * quantity.value
})

const totalPrice = computed(() => {
  if (!product.value) return 0
  return Number(product.value.price) * quantity.value
})

const totalMiles = computed(() => {
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

    const [productsRes, walletRes, pendingRes, configRes] = await Promise.all([
      fetch('/api/v1/mileage/reward-products', { headers }),
      fetch('/api/v1/wallet/info', { headers }),
      fetch('/api/v1/mileage/reward-orders/my-pending', { headers }),
      fetch('/api/v1/config/reward_detail_display_style'),
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

    if (configRes.ok) {
      const data = await configRes.json()
      displayStyle.value = data.value || 'default'
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
  height: 100vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
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
  width: 60%;
  max-width: 220px;
  height: auto;
  aspect-ratio: 1;
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

/* 品名 */
.product-name {
  font-size: 1rem;
  font-weight: 600;
  color: #1e293b;
  padding: 0 0.25rem;
  margin-bottom: 0.75rem;
  text-align: center;
}

/* 數量列 */
.qty-row {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 0.75rem;
  padding: 0 0.25rem;
}

.qty-label {
  font-size: 0.95rem;
  text-align: left;
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

/* 水平模式：數量靠左與加減同列，尚有庫存在下一列 */
.qty-row--horizontal {
  align-items: flex-start; /* 讓 label 與 controls 從頂端對齊 */
}

.qty-row--horizontal .qty-label {
  height: 32px;            /* 與 qty-btn 同高 */
  display: flex;
  align-items: center;     /* 文字在 32px 內垂直置中 */
}

.qty-controls--horizontal {
  flex-direction: column;
  align-items: flex-end;   /* qty-box 與 stock-label 靠右 */
  gap: 0;
}

.qty-controls--horizontal .stock-label {
  margin-top: 0.5rem;
}

/* 金額資訊 */
.amount-section {
  margin-top: 1.5rem;
  padding: 0 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
}

.amount-divider {
  border: none;
  border-top: 1px solid #e5e7eb;
  margin: 0.25rem 0;
}

/* 水平樣式 */
.amount-section-horizontal {
  margin-top: 1.5rem;
  padding: 0 1rem;
  display: flex;
  justify-content: space-between;
}

.amount-card {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  padding: 0.5rem 0;
}

.amount-card-left {
  align-items: flex-start;
}

.amount-card-right {
  align-items: flex-start;
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
  justify-content: space-between;
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

.amount-value.green {
  color: #2e7d32;
}

.amount-card-value.green {
  color: #2e7d32;
}

.miles-hint {
  text-align: center;
  font-size: 0.82rem;
  color: #d71921;
  margin: 0.4rem 1rem 0;
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

/* 頁面主體（可捲動部份） */
.page-body {
  flex: 1;
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
  width: 100%;
  box-sizing: border-box;
}

/* 內容最大寬度居中 */
.content-wrap {
  max-width: 560px;
  margin: 0 auto;
  width: 100%;
  box-sizing: border-box;
}

/* 固定在底部的操作區 */
.page-footer {
  flex-shrink: 0;
  background: #fff;
  padding-top: 0.25rem;
  border-top: 1px solid #f1f5f9;
}

.page-footer--no-border {
  border-top: none;
  padding-top: 0;
}

/* 確認按鈕 */
.action-section {
  padding: 0.75rem 1rem 0.5rem;
}

/* 客服連結 */
.support-section {
  text-align: center;
  padding: 0.5rem 0 0.25rem;
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
