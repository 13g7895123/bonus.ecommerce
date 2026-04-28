<template>
  <div class="confirm-page">
    <PageHeader :title="t('mileageRewardConfirm.title')" back-to="/mileage-rewards" :bordered="false" />

    <div v-if="loading" class="state-loading">{{ t('common.loading') }}</div>
    <div v-else-if="!product" class="state-empty">{{ t('mileageRewards.empty') }}</div>
    <template v-else>
      <!-- 訂單摘要 -->
      <div class="summary-card">
        <div class="product-row">
          <img :src="product.image_url || '/product-1.png'" :alt="product.name" class="product-thumb" />
          <span class="product-name">{{ product.name }}</span>
        </div>

        <div class="divider"></div>

        <div class="info-row">
          <span class="info-label">{{ t('mileageRewardConfirm.quantity') }}</span>
          <span class="info-value">× {{ quantity }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">{{ t('mileageRewardConfirm.totalPrice') }}</span>
          <span class="info-value">$ {{ totalPrice.toLocaleString() }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">{{ t('mileageRewardConfirm.totalMilesPoints') }}</span>
          <span class="info-value miles">{{ totalMilesPoints.toLocaleString() }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">{{ t('mileageRewardConfirm.mileageReward') }}</span>
          <span class="info-value reward">$ {{ totalMileageReward.toLocaleString() }}</span>
        </div>
      </div>

      <!-- 錯誤訊息 -->
      <div v-if="errorMsg" class="error-msg">{{ errorMsg }}</div>

      <!-- 操作按鈕 -->
      <div class="action-section">
        <AppButton block :disabled="submitting" @click="submitPurchase">
          {{ submitting ? t('common.loading') : t('mileageRewardConfirm.submit') }}
        </AppButton>
      </div>
    </template>

    <!-- 送出成功提示視窗 -->
    <div v-if="showSuccessDialog" class="dialog-overlay" @click.self="closeSuccessDialog">
      <div class="dialog-box">
        <div class="dialog-icon">
          <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
          </svg>
        </div>
        <div class="dialog-title">已成功送出</div>
        <div class="dialog-desc">該筆訂單審核中，請耐心等候</div>
        <button class="dialog-btn" @click="closeSuccessDialog">確定</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import PageHeader from '../components/PageHeader.vue'
import AppButton from '../components/AppButton.vue'

const { t } = useI18n()
const route   = useRoute()
const router  = useRouter()

const loading   = ref(true)
const submitting = ref(false)
const errorMsg  = ref('')
const product   = ref(null)
const quantity  = ref(1)
const showSuccessDialog = ref(false)

const closeSuccessDialog = () => {
  showSuccessDialog.value = false
  const q = new URLSearchParams()
  if (route.query.item_id)   q.set('item_id',   route.query.item_id)
  if (route.query.item_name) q.set('item_name', route.query.item_name)
  if (route.query.from)      q.set('from',      route.query.from)
  const qs = q.toString()
  router.replace(`/mileage-rewards${qs ? '?' + qs : ''}`)
}

const totalPrice = computed(() =>
  product.value ? Number(product.value.price) * quantity.value : 0
)
const totalMilesPoints = computed(() =>
  product.value ? Number(product.value.miles_points || 0) * quantity.value : 0
)
const totalMileageReward = computed(() =>
  product.value
    ? Math.round(Number(product.value.price) * Number(product.value.mileage_amount) / 100 * quantity.value)
    : 0
)

const submitPurchase = async () => {
  if (!product.value || submitting.value) return
  submitting.value = true
  errorMsg.value   = ''
  try {
    const token = localStorage.getItem('token') || ''
    const res = await fetch(`/api/v1/mileage/reward-products/${product.value.id}/purchase`, {
      method:  'POST',
      headers: {
        'Content-Type':  'application/json',
        Authorization:   `Bearer ${token}`,
      },
      body: JSON.stringify({ quantity: quantity.value }),
    })
    const data = await res.json()
    if (!res.ok) {
      errorMsg.value = data.message || t('common.error')
      return
    }
    // 購買成功，顯示審核提示視窗
    showSuccessDialog.value = true
  } catch {
    errorMsg.value = t('common.error')
  } finally {
    submitting.value = false
  }
}

onMounted(async () => {
  const productId = parseInt(route.query.product_id)
  quantity.value  = parseInt(route.query.qty) || 1

  if (!productId) {
    loading.value = false
    return
  }

  try {
    const token = localStorage.getItem('token') || ''
    const res = await fetch('/api/v1/mileage/reward-products', {
      headers: token ? { Authorization: `Bearer ${token}` } : {},
    })
    if (res.ok) {
      const data = await res.json()
      const items = data.items || data.data?.items || []
      product.value = items.find(p => Number(p.id) === productId) || null
    }
  } catch {
    // ignore
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.confirm-page {
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

.summary-card {
  margin: 1.25rem 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 1rem;
}

.product-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.75rem;
}

.product-thumb {
  width: 60px;
  height: 60px;
  object-fit: contain;
  border-radius: 8px;
  border: 1px solid #f3f4f6;
}

.product-name {
  font-size: 0.95rem;
  font-weight: 600;
  color: #1f2937;
  flex: 1;
}

.divider {
  border-bottom: 1px solid #f3f4f6;
  margin: 0.75rem 0;
}

.info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.35rem 0;
}

.info-label {
  font-size: 0.9rem;
  color: #6b7280;
}

.info-value {
  font-size: 0.95rem;
  font-weight: 600;
  color: #1f2937;
}

.info-value.reward {
  color: #d71921;
}

.info-value.miles {
  color: #2563eb;
}

.error-msg {
  margin: 0 1rem 0.75rem;
  padding: 0.6rem 0.9rem;
  background: #fef2f2;
  border: 1px solid #fca5a5;
  border-radius: 8px;
  color: #dc2626;
  font-size: 0.88rem;
}

.action-section {
  padding: 0.5rem 1rem 1.5rem;
}

/* 送出成功提示視窗 */
.dialog-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 1rem;
}

.dialog-box {
  background: #ffffff;
  border-radius: 16px;
  padding: 2rem 1.5rem 1.25rem;
  width: 100%;
  max-width: 320px;
  text-align: center;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

.dialog-icon {
  display: flex;
  justify-content: center;
  color: #16a34a;
  margin-bottom: 0.75rem;
}

.dialog-title {
  font-size: 1.15rem;
  font-weight: 700;
  color: #111827;
  margin-bottom: 0.5rem;
}

.dialog-desc {
  font-size: 0.92rem;
  color: #4b5563;
  margin-bottom: 1.25rem;
  line-height: 1.5;
}

.dialog-btn {
  width: 100%;
  padding: 0.7rem 1rem;
  background: #d71921;
  color: #ffffff;
  border: none;
  border-radius: 8px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
}

.dialog-btn:hover {
  background: #b81117;
}
</style>
