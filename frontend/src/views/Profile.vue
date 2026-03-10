<template>
  <div class="profile-page">
    <div class="profile-header">
      <router-link to="/skywards" class="back-btn">
        <span class="arrow-left-icon"></span>
      </router-link>
      <h2 class="header-title">個人檔案</h2>
      <div class="header-placeholder"></div>
    </div>

    <!-- 變更照片區塊 -->
    <div class="change-photo-section">
      <div class="camera-icon-wrapper">
        <span class="camera-emoji">📷</span>
      </div>
      <p class="change-text">變更個人檔案相片</p>
    </div>

    <!-- 連結列表 -->
    <div class="link-list">
      <router-link to="/profile/details" class="link-item">
        <span class="link-label">詳細的個人資料</span>
        <span class="arrow-right">></span>
      </router-link>
      <router-link to="/forgot-password" class="link-item">
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

<style scoped>
.profile-page {
  background-color: #f5f5f5;
  min-height: 100vh;
  color: #333;
}

.profile-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.5rem;
  background-color: #ffffff;
  border-bottom: 1px solid #eee;
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

.change-photo-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 3rem 0;
  background-color: #ffffff;
}

.camera-icon-wrapper {
  width: 60px;
  height: 60px;
  background-color: #f0f0f0;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1rem;
}

.camera-emoji {
  font-size: 1.5rem;
}

.change-text {
  font-size: 0.95rem;
  font-weight: 500;
  color: #d71921;
  text-decoration: underline;
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
  height: 40px;
  opacity: 0.8;
}
</style>
