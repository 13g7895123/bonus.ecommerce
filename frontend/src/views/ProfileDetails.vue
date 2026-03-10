<template>
  <div class="details-page">
    <div class="details-header">
      <router-link to="/profile" class="back-btn">
        <span class="arrow-left-icon"></span>
      </router-link>
      <h2 class="header-title">詳細個人資料</h2>
      <div class="header-placeholder"></div>
    </div>

    <!-- 聯絡詳細資料標題與編輯按鈕 -->
    <div class="section-container">
      <div class="title-row">
        <h3 class="contact-title">聯絡詳細資料</h3>
        <button class="edit-btn">✎</button>
      </div>

      <!-- 個人資訊子區塊 -->
      <div class="sub-section">
        <h4 class="sub-title">個人資訊</h4>
        <div class="form-group">
          <label>名字</label>
          <div class="field-value">Admin</div>
        </div>
        <div class="form-group">
          <label>姓氏</label>
          <div class="field-value">User</div>
        </div>
        <div class="form-group">
          <label>你的出生日期</label>
          <div class="field-value">1990/01/01</div>
        </div>
        <div class="form-group">
          <label>居住國家/地區</label>
          <div class="field-value">Taiwan</div>
        </div>
        <div class="form-group">
          <label>行動號碼(偏好的聯絡方式)</label>
          <div class="field-value">+886 0912345678</div>
        </div>
      </div>

      <!-- 電子郵件子區塊 -->
      <div class="sub-section">
        <h4 class="sub-title">電子郵件</h4>
        <div class="email-group">
          <div class="email-value">admin@emirates.com</div>
          <span class="verified-tag">已驗證</span>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.details-page {
  background-color: #f5f5f5;
  min-height: 100vh;
  color: #333;
}

.details-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.5rem;
  background-color: #ffffff;
  border-bottom: 1px solid #eee;
  margin-bottom: 1rem;
}

.header-title {
  font-size: 1.25rem;
  font-weight: 700;
  margin: 0;
}

.back-btn {
  text-decoration: none;
  color: #333;
  padding: 5px;
}

.arrow-left-icon {
  display: block;
  width: 12px;
  height: 12px;
  border-left: 2px solid #333;
  border-bottom: 2px solid #333;
  transform: rotate(45deg);
}

.header-placeholder {
  width: 22px;
}

.section-container {
  background-color: #ffffff;
  padding: 2rem 1.5rem;
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
}

.edit-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: #666;
  cursor: pointer;
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

.verified-tag {
  background-color: #e6f7ed;
  color: #2e7d32;
  font-size: 0.75rem;
  padding: 2px 8px;
  border-radius: 4px;
  font-weight: 700;
}
</style>
