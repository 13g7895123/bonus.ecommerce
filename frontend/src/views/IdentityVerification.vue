<template>
  <div class="iv-page">
    <PageHeader title="實名認證" back-to="/settings" />

    <div class="iv-content">
      <!-- 說明文字 -->
      <p class="iv-desc">為了保障帳戶安全體驗 請您綁定個人身份資訊</p>

      <!-- 已送出：狀態橫幅 -->
      <div v-if="isSubmitted" class="iv-status-banner" :class="`iv-status--${verificationStatus}`">
        <span class="iv-status-icon">{{ statusIcon }}</span>
        <div class="iv-status-body">
          <p class="iv-status-text">{{ statusText }}</p>
          <p v-if="rejectionReason" class="iv-status-reason">拒絕原因：{{ rejectionReason }}</p>
        </div>
      </div>

      <!-- 正面身分證：已送出顯示預覽或已上傳標記，否則顯示上傳框 -->
      <template v-if="isSubmitted">
        <div v-if="frontPreview" class="iv-img-block">
          <p class="iv-img-label">身分證正面</p>
          <img :src="frontPreview" class="iv-id-img" alt="身分證正面" />
        </div>
        <div v-else class="iv-uploaded-placeholder">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2e7d32" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
          <span>正面身分證 已上傳</span>
        </div>
      </template>
      <UploadBox v-else side="正面" hint="上傳您正面身分證"
        v-model="frontPreview"
        :uploading="frontUploading"
        @file-selected="handleFrontSelected"
      />

      <!-- 背面身分證 -->
      <template v-if="isSubmitted">
        <div v-if="backPreview" class="iv-img-block">
          <p class="iv-img-label">身分證背面</p>
          <img :src="backPreview" class="iv-id-img" alt="身分證背面" />
        </div>
        <div v-else class="iv-uploaded-placeholder">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2e7d32" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
          <span>背面身分證 已上傳</span>
        </div>
      </template>
      <UploadBox v-else side="背面" hint="上傳您背面身分證"
        v-model="backPreview"
        :uploading="backUploading"
        @file-selected="handleBackSelected"
      />

      <AppInput v-model="idNumber" label="身份證字號" placeholder="請輸入身份證號" :readonly="isSubmitted" />
      <AppInput v-model="fullName" label="代表人姓名" placeholder="請輸入代表人姓名" :readonly="isSubmitted" />

      <!-- 提示框與操作按鈕：僅未送出時顯示 -->
      <template v-if="!isSubmitted">
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
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from '../composables/useToast'
import { fileService } from '../services/FileService'
import PageHeader from '../components/PageHeader.vue'
import UploadBox from '../components/UploadBox.vue'
import NoticeBox from '../components/NoticeBox.vue'
import AppInput from '../components/AppInput.vue'
import AppButton from '../components/AppButton.vue'
import DebugFillButton from '../components/DebugFillButton.vue'
import { getRandomName } from '../utils/random'
import { useApi } from '../composables/useApi'

const router = useRouter()
const { user: userService } = useApi()
const toast = useToast()

const idNumber = ref('')
const fullName = ref('')
// preview 字串（base64 或 URL），用於 UploadBox v-model
const frontPreview = ref('')
const backPreview  = ref('')
// 上傳後從 API 取得的 file ID
const frontFileId  = ref(null)
const backFileId   = ref(null)
// 各別上傳狀態
const frontUploading = ref(false)
const backUploading  = ref(false)

// 已送出的認證狀態：'none' | 'pending' | 'approved' | 'rejected'
const verificationStatus = ref('none')
const rejectionReason    = ref('')

const isSubmitted = computed(() => verificationStatus.value !== 'none')

const statusText = computed(() => {
  const map = { pending: '審核中', approved: '已通過', verified: '已通過', rejected: '已拒絕' }
  return map[verificationStatus.value] || ''
})

const statusIcon = computed(() => {
  const map = { pending: '⏳', approved: '✓', verified: '✓', rejected: '✗' }
  return map[verificationStatus.value] || ''
})

onMounted(async () => {
  try {
    const userStr = localStorage.getItem('user')
    const userId  = userStr ? JSON.parse(userStr).id : null
    if (!userId) return
    const { user: userService } = useApi()
    const profile = await userService.getProfile(userId)

    // 判斷驗證狀態（相容真實 API verify_status 欄位 與 mock verificationStatus）
    const status =
      profile?.verify_status        ||   // 真實 API DB 欄位名
      profile?.verification_status  ||   // 備用
      profile?.verificationStatus   ||   // mock 新版
      (profile?.verificationData ? 'pending' : 'none')  // mock 舊版
    verificationStatus.value = status || 'none'

    // 從 API 返回的 verification_data 預填表單
    const vd = profile?.verification_data
      ? (typeof profile.verification_data === 'string'
          ? JSON.parse(profile.verification_data)
          : profile.verification_data)
      : (profile?.verificationData || null)

    // 拒絕原因：後端存在 verification_data.reject_reason
    rejectionReason.value =
      profile?.verification_rejection_reason ||
      vd?.reject_reason ||
      ''

    if (vd) {
      idNumber.value = vd.idNumber || vd.id_number || ''
      // 真實 API 欄位叫 real_name；mock 叫 fullName
      fullName.value = vd.fullName || vd.real_name || vd.full_name || ''
      // 圖片 URL（mock 模式不存 base64，只顯示 placeholder）
      frontPreview.value = vd.frontImageUrl || vd.front_image_url || ''
      backPreview.value  = vd.backImageUrl  || vd.back_image_url  || ''
    }
  } catch (e) {
    // 預填失敗不阻斷流程
  }
})

/** UploadBox @file-selected 回調：立即上傳並記錄 file ID */
const handleFrontSelected = async (file) => {
  frontUploading.value = true
  try {
    const result = await fileService.upload(file, 'kyc')
    frontFileId.value  = result.id
    frontPreview.value = result.url  // 遠端 URL 取代 base64
  } catch (e) {
    toast.error('正面身分證上傳失敗')
  } finally {
    frontUploading.value = false
  }
}

const handleBackSelected = async (file) => {
  backUploading.value = true
  try {
    const result = await fileService.upload(file, 'kyc')
    backFileId.value  = result.id
    backPreview.value = result.url
  } catch (e) {
    toast.error('背面身分證上傳失敗')
  } finally {
    backUploading.value = false
  }
}

const fillRandomData = () => {
  idNumber.value = 'A123456789'
  fullName.value = getRandomName()
}

const handleNext = async () => {
  try {
    const userStr = localStorage.getItem('user')
    if (!userStr) {
      toast.error('請先登入')
      router.push('/login')
      return
    }

    if (!frontFileId.value || !backFileId.value) {
      toast.warning('請上傳正、反面身分證圖片')
      return
    }

    const currentUser = JSON.parse(userStr)

    // 將 file ID 傳給後端，後端直接查 files 表
    // frontImageUrl / backImageUrl 供 mock 模式持久化預覽圖
    await userService.verifyIdentity(currentUser.id, {
      idNumber:      idNumber.value,
      fullName:      fullName.value,
      frontFileId:   frontFileId.value,
      backFileId:    backFileId.value,
      frontImageUrl: frontPreview.value,
      backImageUrl:  backPreview.value,
    })

    toast.success('實名認證資料已送出')
    router.push('/settings')
  } catch (error) {
    toast.error('儲存失敗: ' + (error?.message || ''))
  }
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

/* ── 已送出後的狀態樣式 ── */
.iv-status-banner {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  border-radius: 8px;
  padding: 0.875rem 1rem;
  margin-bottom: 1.25rem;
  font-size: 0.875rem;
}

.iv-status--pending {
  background-color: #fff8e1;
  border: 1px solid #f9a825;
  color: #7a5c00;
}

.iv-status--approved {
  background-color: #e8f5e9;
  border: 1px solid #2e7d32;
  color: #1b5e20;
}

.iv-status--rejected {
  background-color: #ffebee;
  border: 1px solid #c62828;
  color: #b71c1c;
}

.iv-status-icon {
  font-size: 1.1rem;
  line-height: 1.4;
  flex-shrink: 0;
}

.iv-status-body {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.iv-status-text {
  margin: 0;
  font-weight: 700;
  font-size: 0.9rem;
}

.iv-status-reason {
  margin: 0;
  font-size: 0.8rem;
  opacity: 0.85;
}

.iv-img-block {
  margin-bottom: 1rem;
}

.iv-img-label {
  font-size: 0.8rem;
  font-weight: 600;
  color: #555;
  margin: 0 0 0.375rem 0;
}

.iv-id-img {
  width: 100%;
  border-radius: 8px;
  border: 1px solid #ddd;
  object-fit: cover;
  max-height: 180px;
}

.iv-uploaded-placeholder {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background-color: #f0faf0;
  border: 1px solid #a5d6a7;
  border-radius: 8px;
  padding: 0.75rem 1rem;
  margin-bottom: 1rem;
  font-size: 0.875rem;
  color: #2e7d32;
  font-weight: 600;
}

@media (max-width: 767px) {
  .iv-content {
    padding: 0 1.5rem 2rem 1.5rem;
  }
}
</style>
