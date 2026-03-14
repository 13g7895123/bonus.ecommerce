<template>
  <div class="iv-page">
    <PageHeader title="實名認證" back-to="/settings" />

    <div class="iv-content">
      <!-- 說明文字 -->
      <p class="iv-desc">為了保障帳戶安全體驗 請您綁定個人身份資訊</p>

      <UploadBox side="正面" hint="上傳您正面身分證" v-model="frontImage" />
      <UploadBox side="背面" hint="上傳您背面身分證" v-model="backImage" />

      <AppInput v-model="idNumber" label="身份證字號" placeholder="請輸入身份證號" />
      <AppInput v-model="fullName" label="代表人姓名" placeholder="請輸入代表人姓名" />

      <NoticeBox>
        <div class="notice-section">
          <p class="notice-title">上傳身份證正反面：</p>
          <p class="notice-text">
            正面：需清晰顯示姓名、身分證字號、出生日期及照片。<br />
            反面：需包含發證日期及有效期限。
          </p>
        </div>
        <div class="notice-section">
          <p class="notice-title">建議：</p>
          <p class="notice-text">
            *光線充足，避免反光或陰影*<br />
            *圖片清晰可辨，避免模糊或裁切到重要資訊*
          </p>
        </div>
        <div class="notice-section">
          <p class="notice-title">身分證號格式：</p>
          <p class="notice-text">
            確保填寫正確的格式：1個大寫英文字母＋9位阿拉伯數字（例如：A123456789）
          </p>
        </div>
      </NoticeBox>

      <AppButton block @click="handleNext" class="iv-next-btn">下一步</AppButton>
      <DebugFillButton @fill="fillRandomData" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import PageHeader from '../components/PageHeader.vue'
import UploadBox from '../components/UploadBox.vue'
import NoticeBox from '../components/NoticeBox.vue'
import AppInput from '../components/AppInput.vue'
import AppButton from '../components/AppButton.vue'
import DebugFillButton from '../components/DebugFillButton.vue'
import { getRandomName } from '../utils/random'

const router = useRouter()
const idNumber = ref('')
const fullName = ref('')
const frontImage = ref('')
const backImage = ref('')

onMounted(() => {
  idNumber.value = localStorage.getItem('iv_idNumber') || ''
  fullName.value = localStorage.getItem('iv_fullName') || ''
  frontImage.value = localStorage.getItem('iv_frontImage') || ''
  backImage.value = localStorage.getItem('iv_backImage') || ''
})

const fillRandomData = () => {
  idNumber.value = 'A123456789'
  fullName.value = getRandomName()
}

const handleNext = () => {
  localStorage.setItem('iv_idNumber', idNumber.value)
  localStorage.setItem('iv_fullName', fullName.value)
  localStorage.setItem('iv_frontImage', frontImage.value)
  localStorage.setItem('iv_backImage', backImage.value)
  alert('資料已暫存於 Local Storage')
  router.push('/settings')
}
</script>

<style scoped>
.iv-page {
  background-color: #ffffff;
  min-height: 100vh;
}

.iv-content {
  padding: 3rem 5rem;
  padding-top: 0;
}

.iv-desc {
  font-size: 0.875rem;
  color: #999;
  text-align: center;
  margin-bottom: 1.5rem;
}

:deep(.app-input-label) {
  text-align: left;
}

:deep(.notice-box) {
  border-radius: 4px;
  text-align: left;
}

.notice-section {
  margin-bottom: 0.875rem;
}

.notice-section:last-child {
  margin-bottom: 0;
}

.notice-title {
  font-size: 0.875rem;
  font-weight: 700;
  color: #d71921;
  margin: 0 0 0.25rem 0;
}

.notice-text {
  font-size: 0.8rem;
  color: #555;
  margin: 0;
  line-height: 1.6;
}

.iv-next-btn {
  margin-top: 0.5rem;
}
</style>
