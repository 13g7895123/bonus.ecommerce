<template>
  <div class="settings-page">
    <div class="settings-header">
      <router-link to="/" class="back-btn">
        <span class="arrow-left-icon"></span>
      </router-link>
      <h2 class="header-title">我的</h2>
      <div class="header-placeholder"></div>
    </div>

    <!-- 旅行必需品區塊 -->
    <div class="section">
      <h3 class="section-title">旅行必需品</h3>
      <div class="essentials-grid">
        <div class="essential-card">
          <div class="card-icon">🪪</div>
          <p class="card-text">實名認證</p>
        </div>
        <div class="essential-card">
          <div class="card-icon">✉️</div>
          <p class="card-text">我的信件</p>
        </div>
        <div class="essential-card">
          <div class="card-icon">📜</div>
          <p class="card-text">交易紀錄</p>
        </div>
        <div class="essential-card">
          <div class="card-icon">💰</div>
          <p class="card-text">提款申請</p>
        </div>
        <div class="essential-card">
          <div class="card-icon">🎖️</div>
          <p class="card-text">里程回饋</p>
        </div>
        <div class="essential-card">
          <div class="card-icon">🎧</div>
          <p class="card-text">在線客服</p>
        </div>
      </div>
    </div>

    <!-- 設定區塊 -->
    <div class="section">
      <h3 class="section-title">設定</h3>
      <button class="setting-item-btn">
        <span class="btn-label">國家/地區和語言</span>
        <span class="arrow-right-icon"></span>
      </button>
    </div>

    <!-- 通告卡片區塊 -->
    <div class="section">
      <div class="promo-card">
        <img src="/upgrade-bg.png" alt="Promo" class="promo-image" />
        <div class="promo-content">
          <h4 class="promo-title">專屬優惠</h4>
          <p class="promo-text">查看您目前的專屬會員禮遇與限時活動。</p>
        </div>
      </div>
    </div>

    <!-- 登出按鈕 -->
    <div class="logout-container">
      <button class="logout-btn">登出</button>
    </div>
  </div>
</template>

<style scoped>
.settings-page {
  background-color: #f5f5f5;
  min-height: 100vh;
  padding-bottom: 80px;
  color: #333;
}

.settings-header {
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

.section {
  padding: 1.5rem;
  background-color: #ffffff;
  margin-top: 1rem;
}

.section-title {
  font-size: 1.1rem;
  font-weight: 700;
  margin-bottom: 1.25rem;
  color: #000;
}

.essentials-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
}

.essential-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 1rem 0.5rem;
  background-color: #f9f9f9;
  border-radius: 8px;
  text-align: center;
}

.card-icon {
  font-size: 1.75rem;
  margin-bottom: 0.5rem;
}

.card-text {
  font-size: 0.85rem;
  font-weight: 500;
  margin: 0;
}

.setting-item-btn {
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 0;
  background: none;
  border: none;
  border-top: 1px solid #eee;
  cursor: pointer;
  font-size: 1rem;
  color: #333;
}

.arrow-right-icon {
  width: 8px;
  height: 8px;
  border-top: 2px solid #ccc;
  border-right: 2px solid #ccc;
  transform: rotate(45deg);
}

.promo-card {
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  background-color: #fff;
}

.promo-image {
  width: 100%;
  height: 150px;
  object-fit: cover;
}

.promo-content {
  padding: 1rem;
}

.promo-title {
  font-size: 1.1rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.promo-text {
  font-size: 0.9rem;
  color: #666;
  margin: 0;
}

.logout-container {
  padding: 2rem 1.5rem;
}

.logout-btn {
  width: 100%;
  padding: 1rem;
  background-color: #ffffff;
  color: #d71921;
  border: 1px solid #d71921;
  border-radius: 4px;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
}
</style>
