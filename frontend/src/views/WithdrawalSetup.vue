<template>
  <div class="ws-page">
    <PageHeader title="提款申請" back-to="/settings" />

    <div class="ws-content">
      <!-- 載入中 -->
      <div v-if="pageLoading" class="ws-loading">
        <span>資料載入中...</span>
      </div>

      <template v-else>
      <p class="ws-desc">為了保障帳戶安全體驗 請您綁定個人身份資訊</p>

      <!-- 已上傳過存摺 → 顯示存摺圖片 -->
      <div v-if="hasExistingPassbook">
        <div v-if="passbookUrl" class="passbook-img-wrap">
          <p class="passbook-img-label">銀行存摺</p>
          <img :src="passbookUrl" class="passbook-img" alt="銀行存摺" @click="viewPassbook" style="cursor:pointer" />
        </div>
        <div v-else class="passbook-uploaded">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2e7d32" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
          <span>銀行存摺 已上傳</span>
        </div>
      </div>
      <UploadBox
        v-else
        v-model="passbookPreview"
        hint="上傳銀行存摺"
        :uploading="uploading"
        @file-selected="handleFileSelected"
      />

      <div class="section-label">銀行認證資訊</div>

      <div v-if="hasExistingBank" class="readonly-field">
        <span class="readonly-label">銀行名稱</span>
        <span class="readonly-value">{{ bankName }}</span>
      </div>
      <div v-else class="bank-select-wrap">
        <select v-model="bankName" class="bank-select">
          <option value="" disabled>請選擇銀行</option>
          <option v-for="b in bankList" :key="b" :value="b">{{ b }}</option>
        </select>
      </div>
      <AppInput v-model="branchName"  placeholder="請輸入分行資訊" :readonly="hasExistingBank" />
      <AppInput v-model="accountNo"   placeholder="請輸入銀行帳號" :readonly="hasExistingBank" />
      <AppInput v-model="accountName" placeholder="請輸入銀行姓名" :readonly="hasExistingBank" />

      <NoticeBox>
        <p class="notice-title">新增帳戶注意事項</p>
        <ul class="notice-list">
          <li>會員首次填寫帳號後，即永久綁定，恕不任意變更。</li>
          <li>為防止有心人勢利用本站作為詐騙工具：</li>
          <li>申請前請確認自己的帳戶資料，才可提出申請。如有必要原因需要更改資料，請洽線上客服人員。例如有本平臺進行任何洗錢詐騙行為，本公司有權利審核會員帳戶或永久終止會員服務不另行通知。</li>
        </ul>
      </NoticeBox>

      <div style="margin-top: 20px;">
        <AppButton block :disabled="loading" @click="handleSubmit">
          {{ loading ? '處理中...' : '送出' }}
        </AppButton>
      </div>
      <DebugFillButton @fill="fillRandomData" />
      </template><!-- end v-else pageLoading -->
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
import { useToast } from '../composables/useToast'
import { getRandomName } from '../utils/random'
import { FileService } from '../services/FileService'
import { WalletService } from '../services/WalletService'

const router = useRouter()
const toast = useToast()
const fileService = new FileService()
const walletService = new WalletService()

const passbookPreview    = ref('')
const passbookFileId     = ref(null)
const passbookUrl        = ref('')
const uploading          = ref(false)
const bankName           = ref('')
const branchName         = ref('')
const accountNo          = ref('')
const accountName        = ref('')
const loading            = ref(false)
const hasExistingBank    = ref(false)
const hasExistingPassbook = ref(false)
const bankList           = ref([])
const pageLoading        = ref(true)

onMounted(async () => {
  // 載入銀行清單
  try {
    const res = await fetch('/api/v1/config/bank_list')
    if (res.ok) {
      const data = await res.json()
      const raw = data.value
      if (raw) {
        bankList.value = typeof raw === 'string' ? JSON.parse(raw) : raw
      }
    }
  } catch {}
  if (!bankList.value.length) {
    bankList.value = [
      '臺灣銀行', '土地銀行', '合作金庫', '第一銀行', '華南銀行',
      '彰化銀行', '兆豐銀行', '台灣企銀', '玉山銀行', '國泰世華銀行',
      '台北富邦', '富邦銀行', '中國信託', '聯邦銀行', '永豐銀行',
      '台新銀行', '遠東銀行', '元大銀行', '星展銀行', '渣打銀行',
      '匯豐銀行', '花旗銀行', '郵政劃撥', '農業金庫', '新光銀行'
    ]
  }

  try {
    const wallet = await walletService.getWalletInfo()
    if (wallet?.has_passbook) {
      hasExistingPassbook.value = true
      passbookUrl.value = wallet.bank_passbook_url || ''
    }
    if (wallet?.has_bank_account) {
      hasExistingBank.value = true
      bankName.value    = wallet.bank_name        || ''
      branchName.value  = wallet.bank_branch      || ''
      accountNo.value   = wallet.bank_account_masked || ''
      accountName.value = wallet.bank_account_name  || ''
    }
  } catch (e) {
    // 讀取失敗不阻斷，讓使用者依然可以填入資料
  } finally {
    pageLoading.value = false
  }
})

const handleFileSelected = async (file) => {
  uploading.value = true
  try {
    const result = await fileService.upload(file, 'passbook')
    passbookFileId.value = result.id
    passbookUrl.value    = result.url
    // 若銀行已綁定，立即將存折儲存到後端
    if (hasExistingBank.value) {
      await walletService.setupBank(null, {
        bankName:       bankName.value,
        branchName:     branchName.value,
        accountNo:      accountNo.value,
        accountName:    accountName.value,
        passbookFileId: result.id,
      })
      hasExistingPassbook.value = true
    }
    toast.success('存折上傳成功')
  } catch (e) {
    toast.error(e?.response?.data?.message || '存摺上傳失敗')
    passbookPreview.value = ''
  } finally {
    uploading.value = false
  }
}

const handleSubmit = async () => {
  if (!bankName.value || !accountNo.value || !accountName.value) {
    toast.warning('請填寫銀行名稱、帳號與姓名')
    return
  }

  loading.value = true
  try {
    await walletService.setupBank(null, {
      bankName:           bankName.value,
      branchName:         branchName.value,
      accountNo:          accountNo.value,
      accountName:        accountName.value,
      passbookFileId:     passbookFileId.value,
    })
    toast.success('銀行帳戶綁定成功')
    router.push('/withdrawal/apply')
  } catch (e) {
    toast.error(e?.response?.data?.message || '綁定失敗，請確認提款密碼是否正確')
  } finally {
    loading.value = false
  }
}

const fillRandomData = () => {
  bankName.value           = '玉山銀行'
  branchName.value         = '信義分行'
  accountNo.value          = '1234567890123'
  accountName.value        = getRandomName()
}

const viewPassbook = () => {
  if (passbookUrl.value) {
    window.open(passbookUrl.value, '_blank')
  }
}
</script>

<style scoped>
.ws-page {
  background-color: #ffffff;
  min-height: 100vh;
}

.ws-content {
  padding: 0 5rem 3rem 5rem;
}

.ws-desc {
  font-size: 0.875rem;
  color: #999;
  text-align: center;
  margin-bottom: 1.5rem;
}

.ws-loading {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 3rem 0;
  font-size: 0.875rem;
  color: #999;
}

.section-label {
  font-size: 0.95rem;
  font-weight: 700;
  color: #333;
  margin-bottom: 1rem;
}

.passbook-uploaded {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.875rem 1rem;
  margin-bottom: 1rem;
  border: 1px solid #a5d6a7;
  border-radius: 8px;
  background-color: #f1f8f1;
  color: #2e7d32;
  font-size: 0.95rem;
  font-weight: 600;
}

.passbook-img-wrap {
  margin-bottom: 1rem;
}

.bank-select-wrap {
  margin-bottom: 1rem;
}

.bank-select {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  font-size: 1rem;
  color: #333;
  background: #fff;
  appearance: auto;
}

.readonly-field {
  display: flex;
  align-items: center;
  padding: 0.75rem 0;
  margin-bottom: 0.5rem;
  border-bottom: 1px solid #f0f0f0;
}

.readonly-label {
  font-size: 0.85rem;
  color: #999;
  width: 80px;
  flex-shrink: 0;
}

.readonly-value {
  font-size: 1rem;
  color: #333;
}

.passbook-img-label {
  font-size: 0.85rem;
  color: #666;
  margin-bottom: 0.5rem;
}

.passbook-img {
  width: 100%;
  max-height: 200px;
  object-fit: contain;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  background: #f9f9f9;
}

.notice-title {
  color: #d71921;
  text-align: center;
  margin: 0 0 1rem 0;
}

.notice-list {
  padding-left: 0;
  margin: 0;
  list-style: none;
}

.notice-list li {
  font-size: 1rem;
  color: #555;
  line-height: 1.6;
  margin-bottom: 0.8rem;
  text-align: left;
}

.notice-list li:last-child {
  margin-bottom: 0;
}

:deep(.upload-hint) {
  color: #c60c33 !important;
  font-weight: 700 !important;
}

</style>
