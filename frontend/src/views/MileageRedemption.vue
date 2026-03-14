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
        <!-- Skywards Miles Mall -->
        <div class="list-item">
          <div class="item-left">
            <div class="logo-box">
              <span class="logo-text">S</span>
            </div>
            <div class="item-info">
              <h4 class="item-name">Skywards Miles Mall</h4>
            </div>
          </div>
          <div class="item-right">
            <span class="arrow-icon">›</span>
          </div>
        </div>

        <!-- Emirates Official Store -->
        <div class="list-item">
          <div class="item-left">
            <div class="logo-box">
              <span class="logo-text">E</span>
            </div>
            <div class="item-info">
              <div class="featured-tag">
                <span class="star">★</span> 精選
              </div>
              <h4 class="item-name">阿聯酋航空官方商店</h4>
              <p class="item-desc">累積至2,000點哩程數即可開始兌換獎勵</p>
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
import { ref } from 'vue'
import { useToast } from '../composables/useToast'
import PageHeader from '../components/PageHeader.vue'
import AppInput from '../components/AppInput.vue'
import AppButton from '../components/AppButton.vue'

const toast = useToast()
const activeTab = ref('spending')
const mileageCode = ref('')
const loading = ref(false)

const submitCode = () => {
  if (!mileageCode.value) {
    toast.warning('請輸入代碼')
    return
  }

  loading.value = true

  // Simulate API call
  setTimeout(() => {
    try {
      // Mock logic: Update generic "miles" in localStorage for demo
      // Validation: assume code starting with "BONUS" gives miles
      if (mileageCode.value.toUpperCase().startsWith('BONUS')) {
         const bonus = 500
         const userStr = localStorage.getItem('user')
         if (userStr) {
             const user = JSON.parse(userStr)
             user.miles = (user.miles || 0) + bonus
             localStorage.setItem('user', JSON.stringify(user))
             
             // Also update mock_db
             const mockUsersStr = localStorage.getItem('mock_db_users')
             if (mockUsersStr) {
                 const mockUsers = JSON.parse(mockUsersStr)
                 const dbUserIdx = mockUsers.findIndex(u => u.id === user.id)
                 if (dbUserIdx !== -1) {
                     mockUsers[dbUserIdx].miles = user.miles
                     localStorage.setItem('mock_db_users', JSON.stringify(mockUsers))
                 }
             }
             
             toast.success(`成功兑換! 獲得 ${bonus} 哩程數`)
             mileageCode.value = ''
         } else {
             toast.error('用戶未登入')
         }
      } else {
          toast.error('無效的里程代碼 (試試 BONUS)')
      }
    } catch (e) {
      toast.error('發生錯誤，請稍後再試')
    } finally {
      loading.value = false
    }
  }, 1000)
}
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
