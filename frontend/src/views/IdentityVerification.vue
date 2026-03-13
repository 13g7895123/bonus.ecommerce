<template>
  <div class="iv-page">
    <LogoHeader back-to="/settings" />

    <div class="iv-content">
      <!-- 說明文字 -->
      <p class="iv-desc">為了保障帳戶安全體驗 請您綁定個人身份資訊</p>

      <UploadBox side="正面" hint="上傳您正面身分證" />
      <UploadBox side="背面" hint="上傳您背面身分證" />

      <AppInput label="身份證字號" placeholder="請輸入身份證號" />
      <AppInput label="代表人姓名" placeholder="請輸入代表人姓名" />

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

      <button class="next-btn">下一步</button>
    </div>
  </div>
</template>

<script setup>
import LogoHeader from '../components/LogoHeader.vue'
import UploadBox from '../components/UploadBox.vue'
import NoticeBox from '../components/NoticeBox.vue'
import AppInput from '../components/AppInput.vue'
</script>

<style scoped>
.iv-page {
  background-color: #ffffff;
  min-height: 100vh;
}

.iv-content {
  padding: 3rem 5rem;
}

.iv-desc {
  font-size: 0.875rem;
  color: #999;
  text-align: center;
  margin-bottom: 1.5rem;
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

.next-btn {
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
  transition: background-color 0.2s;
}

.next-btn:hover {
  background-color: #b8151b;
}
.iv-page {
  background-color: #ffffff;
  min-height: 100vh;
}

.iv-header-nav {
  display: flex;
  align-items: center;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #eee;
  position: relative;
}

.back-btn {
  text-decoration: none;
  color: #000;
  display: flex;
  align-items: center;
  padding: 0.5rem;
}

.arrow-left {
  display: block;
  width: 12px;
  height: 12px;
  border-left: 2px solid #333;
  border-bottom: 2px solid #333;
  transform: rotate(45deg);
}

.iv-logo {
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
}

.iv-logo img {
  height: 32px;
}

.iv-content {
  padding: 3rem 5rem;
}

.iv-desc {
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
  margin-bottom: 1rem;
  cursor: pointer;
}

.upload-icon {
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

.upload-side {
  font-size: 1rem;
  font-weight: 600;
  color: #333;
  margin: 0 0 0.25rem 0;
}

.upload-hint {
  font-size: 0.875rem;
  color: #999;
  margin: 0;
}

/* 輸入欄位 */
.input-group {
  margin-bottom: 1rem;
}

.input-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 0.4rem;
}

.iv-input {
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

.iv-input:focus {
  border-color: #d71921;
  background-color: #ffffff;
}

.iv-input::placeholder {
  color: #aaa;
}

/* 注意事項紅框 */
.notice-box {
  border: 1.5px solid #d71921;
  border-radius: 8px;
  padding: 1rem 1.25rem;
  margin: 1.25rem 0;
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

/* 下一步按鈕 */
.next-btn {
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
  transition: background-color 0.2s;
}

.next-btn:hover {
  background-color: #b8151b;
}
</style>
