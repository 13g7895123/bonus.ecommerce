<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()

const handleLogout = () => {
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    router.push('/login')
}
</script>
z
<template>
  <div class="settings-page">
    <div class="settings-header">
      <router-link to="/" class="back-btn">
        <span class="arrow-left-icon"></span>
      </router-link>
      <h2 class="header-title">我的設定</h2>
      <div class="header-placeholder"></div>
    </div>

    <!-- 旅行必需品區塊 -->
    <div class="section" style="background-color: transparent; padding: 0 4rem;">
      <h3 class="section-title">旅行必需品</h3>
      <div class="essentials-grid">
        <router-link to="/identity-verification" class="essential-card">
          <div class="icon-circle">
            <svg class="card-icon" viewBox="0 0 24 24" width="28" height="28" stroke="#d71921" stroke-width="1.5" fill="none">
              <rect x="3" y="4" width="18" height="16" rx="2" />
              <circle cx="12" cy="10" r="3" />
              <path d="M12 16a6 6 0 0 0-6 3v1h12v-1a6 6 0 0 0-6-3z" />
            </svg>
          </div>
          <p class="card-text">實名認證</p>
        </router-link>
        <router-link to="/my-mail" class="essential-card">
          <div class="icon-circle">
            <svg class="card-icon" viewBox="0 0 24 24" width="28" height="28" stroke="#d71921" stroke-width="1.5" fill="none">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
              <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
          </div>
          <p class="card-text">我的信件</p>
        </router-link>
        <router-link to="/transactions" class="essential-card">
          <div class="icon-circle">
            <svg viewBox="0 0 24 24" width="28" height="28" stroke="#d71921" stroke-width="1.5" fill="none" class="card-icon">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
              <polyline points="14 2 14 8 20 8"></polyline>
              <line x1="16" y1="13" x2="8" y2="13"></line>
              <line x1="16" y1="17" x2="8" y2="17"></line>
              <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
          </div>
          <p class="card-text">交易紀錄</p>
        </router-link>
        <router-link to="/withdrawal/set-password" class="essential-card">
          <div class="icon-circle">
             <svg viewBox="0 0 24 24" width="28" height="28" stroke="#d71921" stroke-width="1.5" fill="none" class="card-icon">
              <rect x="2" y="5" width="20" height="14" rx="2" />
              <line x1="2" y1="10" x2="22" y2="10" />
              <path d="M12 15h.01"/>
            </svg>
          </div>
          <p class="card-text">提款申請</p>
        </router-link>
        <router-link to="/mileage-rewards" class="essential-card">
          <div class="icon-circle">
             <svg viewBox="0 0 24 24" width="28" height="28" stroke="#d71921" stroke-width="1.5" fill="none" class="card-icon">
               <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
             </svg>
          </div>
          <p class="card-text">里程回饋</p>
        </router-link>
        <router-link to="/customer-service" class="essential-card">
          <div class="icon-circle">
             <svg viewBox="0 0 24 24" width="28" height="28" stroke="#d71921" stroke-width="1.5" fill="none" class="card-icon">
               <path d="M3 18v-6a9 9 0 0 1 18 0v6"></path>
               <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path>
             </svg>
          </div>
          <p class="card-text">在線客服</p>
        </router-link>
      </div>
    </div>

    <!-- 設定區塊 (標題再次顯示) -->
    <div class="section" style="background-color: transparent; padding: 0 4rem;">
      <h3 class="section-title">設定</h3>
      <div>
        <button class="settings-rounded-btn">
          <span class="btn-label">國家/地區和語言</span>
        </button>
      </div>
    </div>

    <!-- 通告卡片區塊 -->
    <div class="section" style="background-color: transparent; padding: 1.5rem;">
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
      <button class="logout-btn" @click="handleLogout">登出</button>
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
  padding-left: 1rem; /* Add left padding for better alignment */
  color: #000;
  text-align: left; /* Ensure left alignment */
}

.essentials-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
  /* Removed background-color: #e5e5e5; to remove the extra background/border effect */
  /* border-radius: 12px; */ /* Handled by individual items now */
  /* overflow: hidden; */
}

.essential-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 1.5rem 0.5rem;
  background-color: #ffffff;
  text-align: center;
  text-decoration: none;
  color: #333;
  width: 100%; /* Ensure full width */
  box-sizing: border-box; /* Include padding in width */
  
  /* Remove radius and shadow for inner items */
  border-radius: 0;
  box-shadow: none;
}

/* Specific corner rounding */
.essential-card:nth-child(1) {
  border-top-left-radius: 12px;
}
.essential-card:nth-child(2) {
  border-top-right-radius: 12px;
}
.essential-card:nth-child(5) {
  border-bottom-left-radius: 12px;
}
.essential-card:nth-child(6) {
  border-bottom-right-radius: 12px;
}

.icon-circle {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 60px;
  height: 60px;
  background-color: #f5f5f5;
  border-radius: 50%;
  margin-bottom: 0.5rem;
}

.card-icon {
  width: 32px;
  height: 32px;
}

.settings-rounded-btn {
  width: 100%;
  /* Removed max-width: 400px; so it matches the grid width */
  /* Removed margin: 0 auto; */
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  background-color: #ffffff;
  border: none;
  border-radius: 12px; /* Match grid radius */
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
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
  padding: 1.2rem;
  background-color: transparent;
  color: #000000; /* Black text */
  border: 1px solid #000000; /* Black border */
  border-radius: 4px;
  font-size: 1.25rem; /* Larger font */
  font-weight: 700;
  cursor: pointer;
  transition: background-color 0.2s;
}

.logout-btn:hover {
  background-color: #f0f0f0;
}
</style>
