<template>
  <div class="profile-page">
    <PageHeader title="我的個人檔案" back-to="/skywards" />

    <!-- 變更照片區塊 -->
    <div class="change-photo-section">
      <div class="camera-icon-wrapper" @click="triggerFileUpload">
        <img v-if="localAvatar" :src="localAvatar" class="avatar-preview" />
        <svg v-else class="camera-icon" viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
          <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
          <circle cx="12" cy="13" r="4"></circle>
        </svg>
      </div>
      <input type="file" ref="fileInput" accept="image/*" hidden @change="handleFileChange" />
      <p class="change-text" @click="triggerFileUpload">變更個人檔案相片</p>
    </div>

    <!-- 連結列表 -->
    <div class="link-list">
      <router-link to="/profile/details" class="link-item">
        <span class="link-label">詳細的個人資料</span>
        <span class="arrow-right">></span>
      </router-link>
      <router-link to="/change-password" class="link-item">
        <span class="link-label">重設密碼</span>
        <span class="arrow-right">></span>
      </router-link>
    </div>

    <!-- 底部 Logo -->
    <div class="profile-footer-logo">
      <img src="/logo.png" alt="Logo" class="footer-logo-img" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useToast } from '../composables/useToast'
import PageHeader from '../components/PageHeader.vue'
import { UserService } from '../services/UserService'

const fileInput = ref(null)
const localAvatar = ref(null)
const uploading = ref(false)
const toast = useToast()

onMounted(() => {
  const userStr = localStorage.getItem('user')
  if (userStr) {
    try {
      const user = JSON.parse(userStr)
      if (user.avatar) {
        localAvatar.value = user.avatar
      }
    } catch (e) {
      toast.error('載入場我資料失敗')
    }
  }
})

const triggerFileUpload = () => {
  fileInput.value.click()
}

const handleFileChange = async (event) => {
  const file = event.target.files[0]
  if (!file) return

  uploading.value = true
  try {
    const userStr = localStorage.getItem('user')
    const user = userStr ? JSON.parse(userStr) : null

    const userService = new UserService()
    const result = await userService.uploadAvatar(user?.id, file)

    localAvatar.value = result.avatar_url

    // 更新 localStorage 中的頭像網址
    if (user) {
      user.avatar = result.avatar_url
      localStorage.setItem('user', JSON.stringify(user))
    }
  } catch (e) {
    toast.error('頭像上傳失敗')
  } finally {
    uploading.value = false
    event.target.value = ''
  }
}
</script>

<style scoped>
.profile-page {
  background-color: #f5f5f5;
  min-height: 100vh;
  color: #333;
}

.change-photo-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 3rem 0;
  background-color: #f5f5f5; /* Light gray to match page, or stay white? User said "profile bottom color light gray", let's assume section should blend in or be white. */
  /* Re-reading: "我的個人檔案底色為淺灰" -> Likely page bg. */
}

.camera-icon-wrapper {
  width: 60px;
  height: 60px;
  background-color: #666666; /* Lighter gray circle (was #333333) */
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1rem;
  overflow: hidden; /* For avatar image */
  cursor: pointer;
}

.camera-icon {
  stroke: #ffffff;
}

.avatar-preview {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.camera-emoji {
  font-size: 1.5rem;
}

.change-text {
  font-size: 0.95rem;
  font-weight: 700; /* Bold */
  color: #666666; /* Same as circle bg */
  text-decoration: none; /* Remove underline */
  cursor: pointer;
}

.link-list {
  margin-top: 1rem;
  background-color: #ffffff;
}

.link-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.5rem;
  text-decoration: none;
  color: #333;
  border-bottom: 1px solid #f0f0f0;
}

.link-label {
  font-size: 1rem;
  font-weight: 500;
}

.arrow-right {
  color: #ccc;
  font-size: 1.25rem;
  font-family: serif;
}

.profile-footer-logo {
  display: flex;
  justify-content: center;
  padding: 4rem 0;
}

.footer-logo-img {
  height: 80px; 
  opacity: 0.8;
}
</style>
