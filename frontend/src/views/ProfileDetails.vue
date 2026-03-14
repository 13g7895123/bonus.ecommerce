<template>
  <div class="details-page">
    <PageHeader title="詳細個人資料" back-to="/profile" />

    <!-- 聯絡詳細資料標題與編輯按鈕 -->
    <div class="section-container">
      <div class="title-row">
        <h3 class="contact-title">聯絡詳細資料</h3>
        <button class="edit-btn" @click="toggleEdit">
          <svg v-if="!isEditing" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
          </svg>
           <span v-else style="font-size: 0.9rem; font-weight: 700;">完成</span>
        </button>
      </div>

      <!-- 個人資訊子區塊 -->
      <div class="sub-section">
        <h4 class="sub-title">個人資訊</h4>
        <div class="form-group">
          <label>名字</label>
          <div v-if="!isEditing" class="field-value">{{ user?.firstName || 'Admin' }}</div>
          <input v-else v-model="formData.firstName" class="edit-input" />
        </div>
        <div class="form-group">
          <label>姓氏</label>
          <div v-if="!isEditing" class="field-value">{{ user?.lastName || 'User' }}</div>
          <input v-else v-model="formData.lastName" class="edit-input" />
        </div>
        <div class="form-group">
          <label>你的出生日期</label>
          <div v-if="!isEditing" class="field-value">{{ user?.dob || '1990/01/01' }}</div>
          <input v-else v-model="formData.dob" type="date" class="edit-input" />
        </div>
        <div class="form-group">
          <label>居住國家/地區</label>
          <div v-if="!isEditing" class="field-value">{{ user?.country || 'Taiwan' }}</div>
           <input v-else v-model="formData.country" class="edit-input" />
        </div>
        <div class="form-group">
          <label>行動號碼(偏好的聯絡方式)</label>
          <div v-if="!isEditing" class="field-value">{{ user?.phone || '+886 0912345678' }}</div>
           <input v-else v-model="formData.phone" class="edit-input" />
        </div>
      </div>

      <!-- 電子郵件子區塊 -->
      <div class="sub-section">
        <h4 class="sub-title">電子郵件</h4>
        <div class="email-group">
          <div class="email-value">{{ user?.email || 'admin@emirates.com' }}</div>
          <span class="verified-tag">已驗證</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useToast } from '../composables/useToast'
import PageHeader from '../components/PageHeader.vue'
import { UserService } from '../services/UserService'

const isEditing = ref(false)
const user = ref({})
const formData = ref({})
const toast = useToast()

onMounted(() => {
  const userStr = localStorage.getItem('user')
  if (userStr) {
    try {
      user.value = JSON.parse(userStr)
      formData.value = { ...user.value }
    } catch (e) {
      toast.error('載入場我資料失敗')
    }
  }
})

const toggleEdit = () => {
  if (isEditing.value) {
    saveChanges()
  } else {
    // 進入編輯模式時，確保 firstName/lastName 從 full_name 拆分
    const u = { ...user.value }
    if (!u.firstName && u.full_name) {
      const parts = u.full_name.trim().split(/\s+/)
      u.firstName = parts[0] || ''
      u.lastName = parts.slice(1).join(' ') || ''
    }
    formData.value = u
    isEditing.value = true
  }
}

const saveChanges = async () => {
  // 合併 firstName + lastName → full_name
  const full_name = [formData.value.firstName, formData.value.lastName].filter(Boolean).join(' ')

  try {
    const userService = new UserService()
    await userService.updateProfile(user.value.id, {
      full_name,
      phone: formData.value.phone,
      dob: formData.value.dob,
      country: formData.value.country,
    })
  } catch (e) {
    toast.error('更新個人資料失敗')
  }

  // 更新本地 user 物件與 localStorage
  user.value = {
    ...user.value,
    ...formData.value,
    full_name,
    name: full_name,
  }
  localStorage.setItem('user', JSON.stringify(user.value))

  isEditing.value = false
}
</script>

<style scoped>
.details-page {
  background-color: #f5f5f5;
  min-height: 100vh;
  color: #333;
}

.section-container {
  background-color: #ffffff;
  padding: 2rem 1.5rem;
  margin: 1rem;
  border-radius: 12px; /* Rounded corners */
  text-align: left; /* Ensure left alignment */
}

.title-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #f0f0f0;
}

.contact-title {
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0;
  position: relative;
  display: inline-block;
}

/* Peach/Fuchsia underline */
.contact-title::after {
  content: '';
  display: block;
  width: 100%;
  height: 3px;
  background-color: #E6007E; 
  margin-top: 4px;
}

.edit-btn {
  background-color: #e0e0e0; /* Light gray background */
  border: none;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  padding: 0;
}

/* Ensure svg within button is black */
.edit-btn svg {
  stroke: #000000;
}

.sub-section {
  margin-bottom: 2.5rem;
}

.sub-title {
  font-size: 1.1rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
  color: #000;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  font-size: 0.85rem;
  color: #666;
  margin-bottom: 0.5rem;
}

.field-value {
  font-size: 1rem;
  font-weight: 500;
  color: #333;
  padding-bottom: 0.25rem;
  border-bottom: 1px solid #eee;
}

.email-group {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.email-value {
  font-size: 1rem;
  font-weight: 500;
}

.edit-input {
  width: 100%;
  padding: 0.5rem;
  font-size: 1rem;
  border: 1px solid #ccc;
  border-radius: 4px;
}

.verified-tag {
  background-color: #e6f7ed;
  color: #2e7d32;
  font-size: 0.75rem;
  padding: 2px 8px;
  border-radius: 4px;
  font-weight: 700;
}
</style>
