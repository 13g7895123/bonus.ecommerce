<template>
  <div class="ws-page">
    <PageHeader title="提款申請" back-to="/transactions" />

    <div class="ws-content">
      <p class="ws-desc">為了保障帳戶安全體驗 請您綁定個人身份資訊</p>

      <UploadBox hint="上傳銀行存摺" />

      <div class="section-label">銀行認證資訊</div>

      <AppInput v-model="bankName" placeholder="請輸入銀行名稱" />
      <AppInput v-model="branchName" placeholder="請輸入分行資訊" />
      <AppInput v-model="accountNo" placeholder="請輸入銀行帳號" />
      <AppInput v-model="accountName" placeholder="請輸入銀行姓名" />

      <NoticeBox>
        <p class="notice-title">新增帳戶注意事項：</p>
        <ul class="notice-list">
          <li>會員首次填寫帳號後，即永久綁定，恕不任意變更。</li>
          <li>為防止有心人勢利用本站作為詐騙工具：</li>
          <li>申請前請確認自己的帳戶資料，才可提出申請。如有必要原因需要更改資料，請洽線上客服人員。例如有本平臺進行任何洗錢詐騙行為，本公司有權利審核會員帳戶或永久終止會員服務不另行通知。</li>
        </ul>
      </NoticeBox>

      <router-link to="/withdrawal/apply" class="submit-btn">送出</router-link>
      <DebugFillButton @fill="fillRandomData" />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import PageHeader from '../components/PageHeader.vue'
import UploadBox from '../components/UploadBox.vue'
import NoticeBox from '../components/NoticeBox.vue'
import AppInput from '../components/AppInput.vue'
import DebugFillButton from '../components/DebugFillButton.vue'
import { getRandomName } from '../utils/random'

const bankName = ref('')
const branchName = ref('')
const accountNo = ref('')
const accountName = ref('')

const fillRandomData = () => {
  bankName.value = '玉山銀行'
  branchName.value = '信義分行'
  accountNo.value = '1234567890123'
  accountName.value = getRandomName()
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

.section-label {
  font-size: 0.95rem;
  font-weight: 700;
  color: #333;
  margin-bottom: 1rem;
}

.notice-title {
  font-size: 0.875rem;
  font-weight: 700;
  color: #d71921;
  margin: 0 0 0.5rem 0;
}

.notice-list {
  padding-left: 1.25rem;
  margin: 0;
}

.notice-list li {
  font-size: 0.8rem;
  color: #555;
  line-height: 1.6;
  margin-bottom: 0.4rem;
}

.notice-list li:last-child {
  margin-bottom: 0;
}

.submit-btn {
  display: block;
  width: 100%;
  padding: 1rem;
  background-color: #d71921;
  color: #ffffff;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  margin-top: 0.5rem;
  text-align: center;
  text-decoration: none;
  box-sizing: border-box;
  transition: background-color 0.2s;
}

.submit-btn:hover {
  background-color: #b8151b;
}
</style>

<style scoped>
.ws-page {
  background-color: #ffffff;
  min-height: 100vh;
}

.ws-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.5rem;
  background-color: #ffffff;
  border-bottom: 1px solid #eee;
  position: sticky;
  top: 0;
  z-index: 10;
}

.back-btn {
  text-decoration: none;
  color: #333;
  padding: 5px;
  display: flex;
  align-items: center;
}

.arrow-left {
  display: block;
  width: 12px;
  height: 12px;
  border-left: 2px solid #333;
  border-bottom: 2px solid #333;
  transform: rotate(45deg);
}

.header-title {
  font-size: 1.1rem;
  font-weight: 700;
  margin: 0;
}

.header-placeholder {
  width: 22px;
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

/* 上傳區塊 */
.upload-box {
  border: 2px dashed #ccc;
  border-radius: 12px;
  padding: 2rem 1rem;
  text-align: center;
  margin-bottom: 1.5rem;
  cursor: pointer;
}

.upload-icon {
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

.upload-hint {
  font-size: 0.875rem;
  color: #999;
  margin: 0;
}

/* 銀行認證資訊標籤 */
.section-label {
  font-size: 0.95rem;
  font-weight: 700;
  color: #333;
  margin-bottom: 1rem;
}

.form-group {
  margin-bottom: 1rem;
}

.ws-input {
  width: 100%;
  padding: 0.875rem 1rem;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  font-size: 0.95rem;
  color: #333;
  background-color: #f9f9f9;
  box-sizing: border-box;
  outline: none;
  transition: border-color 0.2s;
}

.ws-input:focus {
  border-color: #d71921;
  background-color: #ffffff;
}

.ws-input::placeholder {
  color: #aaa;
}

/* 注意事項紅框 */
.notice-box {
  border: 1.5px solid #d71921;
  border-radius: 8px;
  padding: 1rem 1.25rem;
  margin: 1.25rem 0;
}

.notice-title {
  font-size: 0.875rem;
  font-weight: 700;
  color: #d71921;
  margin: 0 0 0.5rem 0;
}

.notice-list {
  padding-left: 1.25rem;
  margin: 0;
}

.notice-list li {
  font-size: 0.8rem;
  color: #555;
  line-height: 1.6;
  margin-bottom: 0.4rem;
}

.notice-list li:last-child {
  margin-bottom: 0;
}

/* 送出按鈕 */
.submit-btn {
  display: block;
  width: 100%;
  padding: 1rem;
  background-color: #d71921;
  color: #ffffff;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  margin-top: 0.5rem;
  text-align: center;
  text-decoration: none;
  box-sizing: border-box;
  transition: background-color 0.2s;
}

.submit-btn:hover {
  background-color: #b8151b;
}
</style>
