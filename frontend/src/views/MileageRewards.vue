<template>
  <div class="ml-page">
    <PageHeader title="里程回饋" back-to="/settings" :bordered="false" />

    <div v-if="loading" class="state-loading">載入中...</div>
    <div v-else-if="!product" class="state-empty">目前無可購買的商品</div>
    <template v-else>
      <div class="product-section">
        <!-- 產品圖片 -->
        <div class="product-image-wrap">
          <img :src="product.image_url || '/product-1.png'" :alt="product.name" class="product-image" />
        </div>

        <!-- 數量列 -->
        <div class="qty-row">
          <span class="qty-label">{{ product.name }}</span>
          <div class="qty-controls">
            <div class="qty-box">
              <button class="qty-btn" @click="decQty">－</button>
              <span class="qty-num">{{ quantity }}</span>
              <button class="qty-btn" @click="incQty">＋</button>
            </div>
            <span class="stock-label">尚有庫存 {{ product.stock }} 件</span>
          </div>
        </div>
      </div>

      <!-- 金額資訊 -->
      <div class="amount-section">
        <div class="amount-row">
          <span class="amount-label">帳戶餘額</span>
          <span class="amount-value">$ {{ balance.toLocaleString() }}</span>
        </div>
        <div class="amount-row">
          <span class="amount-label">里程回饋</span>
          <span class="amount-value reward">$ {{ mileageReward.toLocaleString() }}</span>
        </div>
      </div>

      <!-- 確認按鈕 -->
      <div class="action-section">
        <AppButton block @click="confirmPurchase">確認</AppButton>
      </div>

      <!-- 客服連結 -->
      <div class="support-section">
        <span class="support-text">如有幫助，請</span>
        <router-link to="/customer-service" class="support-link">聯繫客服</router-link>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import PageHeader from '../components/PageHeader.vue'
import AppButton from '../components/AppButton.vue'

const loading  = ref(true)
const product  = ref(null)
const quantity = ref(1)
const balance  = ref(0)

const mileageReward = computed(() => {
  if (!product.value) return 0
  return Number(product.value.mileage_amount) * quantity.value
})

const decQty = () => {
  if (quantity.value > 1) quantity.value--
}

const incQty = () => {
  if (!product.value) return
  if (quantity.value < product.value.stock) quantity.value++
}

const confirmPurchase = () => {
  // TODO: call purchase API
  alert('購買功能開發中')
}

const loadData = async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('token') || ''
    const headers = token ? { Authorization: `Bearer ${token}` } : {}

    const [productsRes, walletRes] = await Promise.all([
      fetch('/api/v1/mileage/reward-products', { headers }),
      fetch('/api/v1/wallet/info', { headers }),
    ])

    if (productsRes.ok) {
      const data = await productsRes.json()
      const items = data.items || data.data?.items || []
      product.value = items[0] || null
    }

    if (walletRes.ok) {
      const data = await walletRes.json()
      balance.value = Number(data.balance ?? data.data?.balance ?? 0)
    }
  } catch {
    // silently ignore, show empty state
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
  display: flex;
  justify-content: center;
  margin-bottom: 1.25rem;
}

.product-image {
  width: 180px;
  height: 180px;
  object-fit: contain;
  border-radius: 8px;
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
  background: #f5f5f5;
  border: none;
  cursor: pointer;
  font-size: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.15s;
}

.qty-btn:hover {
  background: #e8e8e8;
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

.amount-value.reward {
  color: #d71921;
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
