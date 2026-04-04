<template>
  <div class="mileage-redemption-page">
    <PageHeader title="使用Skywards 會員里程" back-to="/skywards" />
    
    <!-- Tab Navigation -->
    <div class="tabs-header">
      <div 
        class="tab-item" 
        :class="{ active: activeTab === 'spending' }"
        @click="activeTab = 'spending'"
      >
        消費及生活
      </div>
      <div 
        class="tab-item" 
        :class="{ active: activeTab === 'redemption' }"
        @click="activeTab = 'redemption'"
      >
        輸入里程數
      </div>
    </div>

    <!-- Spending & Lifestyle Tab -->
    <div v-if="activeTab === 'spending'" class="tab-content spending-content">
      <div class="opportunity-header">
        哩程數使用機會
      </div>
      
      <div class="items-list">
        <div v-if="loadingItems" class="loading-state">載入中...</div>
        <div v-else-if="redemptionItems.length === 0" class="empty-state">目前尚無項目</div>
        <div
          v-else
          v-for="item in redemptionItems"
          :key="item.id"
          class="list-item"
          @click="goToRewards(item)"
        >
          <div class="item-left">
            <div class="logo-box" :style="item.logo_url ? {} : (item.logo_color ? { backgroundColor: item.logo_color } : {})">
              <img v-if="item.logo_url" :src="item.logo_url" style="width:100%;height:100%;object-fit:contain;border-radius:inherit" />
              <span v-else class="logo-text">{{ item.logo_letter || 'S' }}</span>
            </div>
            <div class="item-info">
              <div v-if="item.is_featured == 1" class="featured-tag">
                <span class="star">★</span> {{ item.featured_label || '精選' }}
              </div>
              <h4 class="item-name">{{ item.name }}</h4>
              <p v-if="item.short_desc" class="item-desc">{{ item.short_desc }}</p>
            </div>
          </div>
          <div class="item-right">
            <span class="arrow-icon">›</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Enter Miles Tab -->
    <div v-if="activeTab === 'redemption'" class="tab-content redemption-content">
      <div class="redemption-box">
        <label class="input-label">輸入里程代碼</label>
        <AppInput 
          v-model="mileageCode" 
          placeholder="輸入里程代碼" 
          class="code-input"
        />
        <AppButton 
          :block="true" 
          class="submit-btn" 
          :disabled="loading"
          @click="submitCode"
        >
          {{ loading ? '處理中...' : '送出' }}
        </AppButton>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useToast } from '../composables/useToast'
import PageHeader from '../components/PageHeader.vue'
import AppInput from '../components/AppInput.vue'
import AppButton from '../components/AppButton.vue'
import { MileageService } from '../services/MileageService'

const toast = useToast()
const router = useRouter()
const route = useRoute()
const mileageService = new MileageService()
const activeTab = ref('spending')
const mileageCode = ref('')
const loading = ref(false)
const loadingItems = ref(false)
const redemptionItems = ref([])

const goToRewards = (item) => {
  router.push({ path: '/mileage-rewards', query: { item_id: item.id, item_name: item.name } })
}

const loadRedemptionItems = async () => {
  loadingItems.value = true
  try {
    const result = await mileageService.getRedemptionItems()
    redemptionItems.value = result.items || []
  } catch (e) {
    toast.error('無法載入項目')
  } finally {
    loadingItems.value = false
  }
}

const submitCode = async () => {
  if (!mileageCode.value) {
    toast.warning('請輸入代碼')
    return
  }

  loading.value = true
  try {
    const result = await mileageService.redeem(mileageCode.value.trim())
    toast.success(result.message || `成功兌換 ${result.miles_earned} 哩程數`)
    mileageCode.value = ''
  } catch (e) {
    toast.error(e?.response?.data?.message || '無效的里程代碼')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  if (route.query.tab) {
    activeTab.value = route.query.tab
  }
  loadRedemptionItems()
})
</script>

<style scoped>
.mileage-redemption-page {
  background-color: #f5f5f5;
  min-height: 100vh;
  color: #333;
}

/* Tab Navigation */
.tabs-header {
  display: flex;
  background-color: #ffffff;
  padding: 0 1.5rem;
  border-bottom: 1px solid #eee;
}

.tab-item {
  padding: 1rem 0;
  margin-right: 2rem;
  font-weight: 500;
  color: #666;
  cursor: pointer;
  border-bottom: 3px solid transparent;
  transition: all 0.3s;
}

.tab-item.active {
  color: #d71921;
  border-bottom-color: #d71921;
  font-weight: 700;
}

/* Spending Content */
.spending-content {}

.opportunity-header {
  background-color: #eaeaea;
  padding: 0.75rem 1.5rem;
  font-weight: 700;
  color: #333;
  font-size: 0.95rem;
  text-align: left;
}

.items-list {
  background-color: #ffffff;
}

.loading-state,
.empty-state {
  padding: 2rem 1.5rem;
  text-align: center;
  color: #999;
  font-size: 0.9rem;
}

.list-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #f0f0f0;
  cursor: pointer;
}

.list-item:last-child {
  border-bottom: none;
}

.item-left {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.logo-box {
  width: 50px;
  height: 50px;
  background-color: #ffffff;
  border: 1px solid #ddd;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
  flex-shrink: 0;
}

.logo-text {
  font-weight: 700;
  color: #999;
}

.item-info {
  display: flex;
  flex-direction: column;
  text-align: left;
  align-items: flex-start;
}

.featured-tag {
  color: #E65100;
  font-size: 0.75rem;
  font-weight: 700;
  margin-bottom: 4px;
  display: flex;
  align-items: center;
}

.star {
  margin-right: 4px;
  font-size: 0.9rem;
}

.item-name {
  margin: 0;
  font-size: 1rem;
  font-weight: 700;
  color: #333;
}

.item-desc {
  margin: 4px 0 0;
  font-size: 0.8rem;
  color: #888;
}

.arrow-icon {
  font-size: 1.5rem;
  color: #999;
  font-weight: 300;
}

/* Redemption Content */
.redemption-content {
  background-color: #f5f5f5;
  padding: 1.5rem;
  min-height: calc(100vh - 120px);
}

.redemption-box {
  background-color: #ffffff;
  border-radius: 8px;
  padding: 2rem 1.5rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.input-label {
  font-weight: 700;
  font-size: 0.9rem;
  margin-bottom: -0.5rem;
}

.code-input {
  width: 100%;
}

.submit-btn {
  background-color: #d71921;
  color: white;
  border: none;
  padding: 0.75rem;
  border-radius: 4px;
  font-weight: 700;
  font-size: 1rem;
  cursor: pointer;
  margin-top: 1rem;
}

.submit-btn:disabled {
  background-color: #e57373;
  cursor: not-allowed;
}

/* Item Detail Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
}

.item-modal {
  background: #fff;
  border-radius: 16px;
  width: 90%;
  max-width: 480px;
  max-height: 80vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.item-modal-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #f0f0f0;
  position: relative;
}

.item-modal-logo {
  width: 48px;
  height: 48px;
  background-color: #f5f5f5;
  border: 1px solid #ddd;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.item-modal-letter {
  font-weight: 700;
  font-size: 1.2rem;
  color: #555;
}

.item-modal-title-wrap {
  flex: 1;
}

.item-modal-title {
  font-size: 1.05rem;
  font-weight: 700;
  color: #222;
  margin: 0;
}

.item-modal-close {
  background: none;
  border: none;
  font-size: 1.1rem;
  color: #999;
  cursor: pointer;
  padding: 4px;
  line-height: 1;
}

.item-modal-body {
  flex: 1;
  overflow-y: auto;
  padding: 1.25rem 1.5rem;
}

.item-modal-short {
  font-size: 0.9rem;
  color: #555;
  margin-bottom: 1rem;
  line-height: 1.5;
}

.item-modal-details {
  font-size: 0.88rem;
  color: #444;
  line-height: 1.7;
  text-align: left;
}

.item-modal-empty {
  color: #aaa;
  font-size: 0.9rem;
  text-align: center;
  padding: 1.5rem 0;
}

.item-modal-confirm {
  margin: 0 1.5rem 1.5rem;
  background-color: #d71921;
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 0.875rem;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  width: calc(100% - 3rem);
}

/* Transition */
.slide-up-enter-active,
.slide-up-leave-active {
  transition: transform 0.3s ease;
}
.slide-up-enter-from,
.slide-up-leave-to {
  transform: translateY(100%);
}
</style>


<style scoped>
.mileage-redemption-page {
  background-color: #f5f5f5;
  min-height: 100vh;
  color: #333;
}

/* Tab Navigation */
.tabs-header {
  display: flex;
  background-color: #ffffff;
  padding: 0 1.5rem;
  border-bottom: 1px solid #eee;
}

.tab-item {
  padding: 1rem 0;
  margin-right: 2rem;
  font-weight: 500;
  color: #666;
  cursor: pointer;
  border-bottom: 3px solid transparent;
  transition: all 0.3s;
}

.tab-item.active {
  color: #d71921;
  border-bottom-color: #d71921;
  font-weight: 700;
}

/* Spending Content */
.spending-content {
  /* background-color: #f5f5f5; */
}

.opportunity-header {
  background-color: #eaeaea; /* Light gray bg */
  padding: 0.75rem 1.5rem;
  font-weight: 700;
  color: #333;
  font-size: 0.95rem;
  text-align: left;
}

.items-list {
  background-color: #ffffff;
}

.list-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #f0f0f0;
  cursor: pointer;
}

.list-item:last-child {
  border-bottom: none;
}

.item-left {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.logo-box {
  width: 50px;
  height: 50px;
  background-color: #ffffff;
  border: 1px solid #ddd; /* Gray border */
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
  flex-shrink: 0;
}

.logo-text {
  font-weight: 700;
  color: #999;
}

.item-info {
  display: flex;
  flex-direction: column;
  text-align: left;
  align-items: flex-start;
}

.featured-tag {
  color: #E65100; /* Orange-Red */
  font-size: 0.75rem;
  font-weight: 700;
  margin-bottom: 4px;
  display: flex;
  align-items: center;
}

.star {
  margin-right: 4px;
  font-size: 0.9rem;
}

.item-name {
  margin: 0;
  font-size: 1rem;
  font-weight: 700;
  color: #333;
}

.item-desc {
  margin: 4px 0 0;
  font-size: 0.8rem;
  color: #888;
}

.arrow-icon {
  font-size: 1.5rem;
  color: #999;
  font-weight: 300;
}

/* Redemption Content */
.redemption-content {
  background-color: #f5f5f5; /* Gray background */
  padding: 1.5rem;
  min-height: calc(100vh - 120px); /* Fill remaining height */
}

.redemption-box {
  background-color: #ffffff; /* White content area */
  border-radius: 8px;
  padding: 2rem 1.5rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.input-label {
  font-weight: 700;
  font-size: 0.9rem;
  margin-bottom: -0.5rem;
}

.code-input {
  width: 100%;
}

.submit-btn {
  background-color: #d71921; /* Emirates Red */
  color: white;
  border: none;
  padding: 0.75rem;
  border-radius: 4px;
  font-weight: 700;
  font-size: 1rem;
  cursor: pointer;
  margin-top: 1rem;
}

.submit-btn:disabled {
  background-color: #e57373;
  cursor: not-allowed;
}

.message {
  text-align: center;
  font-weight: 700;
  margin: 0;
}
.message.success { color: #2e7d32; }
.message.error { color: #d32f2f; }

</style>
