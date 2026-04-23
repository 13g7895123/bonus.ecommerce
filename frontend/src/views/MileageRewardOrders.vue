<template>
  <PageLayout title="里程回饋紀錄" back-to="/settings" theme="white">
    <div v-if="loading" class="mro-hint">載入中...</div>
    <div v-else-if="errorMsg" class="mro-hint mro-error">{{ errorMsg }}</div>
    <div v-else-if="orders.length === 0" class="mro-empty">尚無里程回饋紀錄</div>
    <div v-else class="mro-list">
      <div v-for="order in orders" :key="order.id" class="mro-card">
        <p class="mro-time">{{ formatTime(order.created_at) }}</p>
        <div class="mro-body">
          <img
            v-if="order.product_image_url"
            :src="order.product_image_url"
            class="mro-img"
            alt=""
          />
          <div v-else class="mro-img-placeholder"></div>
          <span class="mro-name">{{ order.product_name }}</span>
          <span class="mro-miles">+{{ Number(order.mileage_reward_amount).toLocaleString() }}</span>
        </div>
      </div>
    </div>
  </PageLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import PageLayout from '../components/PageLayout.vue'
import { MileageService } from '../services/MileageService'

const mileageService = new MileageService()
const orders = ref([])
const loading = ref(true)
const errorMsg = ref('')

const formatTime = (val) => {
  if (!val) return ''
  return new Date(val).toLocaleString('zh-TW', { hour12: false }).slice(0, 19)
}

onMounted(async () => {
  try {
    const result = await mileageService.getMyRewardOrders()
    orders.value = result?.items || []
  } catch (e) {
    errorMsg.value = '載入失敗，請稍後再試'
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.mro-hint {
  padding: 2rem 1.5rem;
  text-align: center;
  color: #999;
  font-size: 0.9rem;
}
.mro-error {
  color: #e74c3c;
}
.mro-empty {
  padding: 3rem 1.5rem;
  text-align: center;
  color: #bbb;
  font-size: 0.9rem;
}
.mro-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  padding: 1rem 1rem;
}
.mro-card {
  background: #fff;
  border-radius: 10px;
  padding: 0.85rem 1rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}
.mro-time {
  font-size: 0.75rem;
  color: #aaa;
  margin: 0 0 0.6rem 0;
}
.mro-body {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}
.mro-img {
  width: 52px;
  height: 52px;
  object-fit: cover;
  border-radius: 6px;
  flex-shrink: 0;
  border: 1px solid #eee;
}
.mro-img-placeholder {
  width: 52px;
  height: 52px;
  border-radius: 6px;
  background: #f0f0f0;
  flex-shrink: 0;
}
.mro-name {
  flex: 1;
  font-size: 0.9rem;
  font-weight: 500;
  color: #333;
  line-height: 1.3;
}
.mro-miles {
  font-size: 0.95rem;
  font-weight: 700;
  color: #27ae60;
  white-space: nowrap;
  margin-left: 0.5rem;
}
</style>
