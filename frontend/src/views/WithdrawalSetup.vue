<template>
  <div class="ws-page">
    <div class="ws-header">
      <router-link to="/withdrawal/set-password" class="back-btn">
        <span class="arrow-left"></span>
      </router-link>
      <h2 class="header-title">提款申請</h2>
      <div class="header-placeholder"></div>
    </div>

    <div class="ws-content">
      <!-- 說明文字 -->
      <p class="ws-desc">為了保障帳戶安全體驗 請您綁定個人身份資訊</p>

      <!-- 上傳存摺 -->
      <div class="upload-box">
        <div class="upload-icon">📷</div>
        <p class="upload-hint">上傳銀行存摺</p>
      </div>

      <!-- 銀行認證資訊 -->
      <div class="section-label">銀行認證資訊</div>

      <div class="form-group">
        <input type="text" placeholder="請輸入銀行名稱" class="ws-input" />
      </div>
      <div class="form-group">
        <input type="text" placeholder="請輸入分行資訊" class="ws-input" />
      </div>
      <div class="form-group">
        <input type="text" placeholder="請輸入銀行帳號" class="ws-input" />
      </div>
      <div class="form-group">
        <input type="text" placeholder="請輸入銀行姓名" class="ws-input" />
      </div>

      <!-- 注意事項紅框 -->
      <div class="notice-box">
        <p class="notice-title">新增帳戶注意事項：</p>
        <ul class="notice-list">
          <li>會員首次填寫帳號後，即永久綁定，恕不任意變更。</li>
          <li>為防止有心人勢利用本站作為詐騙工具：</li>
          <li>申請前請確認自己的帳戶資料，才可提出申請。如有必要原因需要更改資料，請洽線上客服人員。例如有本平臺進行任何洗錢詐騙行為，本公司有權利審核會員帳戶或永久終止會員服務不另行通知。</li>
        </ul>
      </div>

      <!-- 送出按鈕 -->
      <router-link to="/withdrawal/apply" class="submit-btn">送出</router-link>
    </div>
  </div>
</template>

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
  padding: 1.5rem;
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
