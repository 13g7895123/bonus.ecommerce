<script setup>
import { ref } from 'vue'

const isMenuOpen = ref(false)
const isLoggedIn = ref(false) // 模擬登入狀態
const activeMenu = ref(null) // 當前顯示的子選單: 'member', 'help', 'news', 'lang'
const currentPage = ref('home') // 'home' or 'login'

const announcements = ref([
  { id: 1, date: '2024-03-09', title: '阿聯酋航空最新航班資訊公告' },
  { id: 2, date: '2024-03-08', title: 'Skywards 集哩程計畫更新提醒' },
  { id: 3, date: '2024-03-05', title: '全球機場貴賓室服務調整通知' },
])

const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value
  activeMenu.value = null
}

const selectMenu = (menu) => {
  if (menu === 'login') {
    currentPage.value = 'login'
    isMenuOpen.value = false
    activeMenu.value = null
  } else {
    activeMenu.value = menu
  }
}

const goBackToHome = () => {
  currentPage.value = 'home'
}

const goBack = () => {
  activeMenu.value = null
}
</script>

<template>
  <div class="app-container" :class="{ 'menu-active': isMenuOpen }">
    <!-- Navbar 僅在首頁顯示 -->
    <nav v-if="currentPage === 'home'" class="navbar">
      <div class="logo">
        <a href="/">
          <img src="/logo.png" alt="Logo" class="logo-image" />
        </a>
      </div>
      <div class="menu-button">
        <button class="hamburger" @click="toggleMenu" :class="{ 'is-active': isMenuOpen }" aria-label="Menu">
          <span class="icon"></span>
        </button>
      </div>
    </nav>

    <!-- 登入獨立頁面 (不包含 Navbar) -->
    <div v-if="currentPage === 'login'" class="standalone-login-page">
      <div class="login-header-nav">
        <button class="back-home-btn" @click="goBackToHome">
          <span class="arrow-left"></span>
        </button>
        <div class="login-logo-small">
          <img src="/logo.png" alt="Logo" />
        </div>
      </div>
      
      <div class="login-page">
        <h2 class="login-title">登錄阿聯酋航空</h2>
        <p class="login-desc">每次跟我們或合作夥伴聯乘都能賺取哩程數。還能使用 Skywards 會員哩程數換取各種獎勵。</p>
        
        <div class="login-form">
          <input type="email" placeholder="電子郵件" class="login-input" />
          <div class="password-group">
            <input type="password" placeholder="密碼" class="login-input" />
            <a href="#" class="forgot-password">忘記您的密碼了嗎?</a>
          </div>
          
          <button class="login-submit-btn">登錄</button>
          
          <hr class="login-divider" />
          
          <div class="join-now-group">
            <p class="join-label">還不是會員?</p>
            <button class="join-now-btn">現在加入</button>
          </div>
        </div>
      </div>
    </div>

    <template v-if="currentPage === 'home'">
      <!-- 全螢幕選單 -->
      <transition name="fade">
        <div v-if="isMenuOpen" class="full-menu-overlay">
          <div class="menu-container">
            <!-- 選單頭部：標題與返回/關閉 -->
            <div class="menu-header">
              <div v-if="activeMenu" class="header-left">
                <button class="back-button" @click="goBack" aria-label="Back">
                  <span class="arrow-left"></span>
                </button>
                <h2 class="menu-header-title">
                  {{ activeMenu === 'member' ? '會員' : activeMenu === 'help' ? '協助' : activeMenu === 'news' ? '公告' : '語言' }}
                </h2>
              </div>
              <div v-else class="header-left">
                <h2 class="menu-header-title">選單</h2>
              </div>
              <button class="close-button" @click="toggleMenu" aria-label="Close">
                <span class="cross-icon"></span>
              </button>
            </div>

            <!-- 主選單列表 -->
            <ul v-if="!activeMenu" class="menu-list">
              <li><a href="#" @click.prevent="selectMenu('member')">會員</a></li>
              <li><a href="#" @click.prevent="selectMenu('help')">協助</a></li>
              <li><a href="#" @click.prevent="selectMenu('news')">公告</a></li>
              <li><a href="#" @click.prevent="selectMenu('lang')">語言</a></li>
              <li v-if="isLoggedIn"><a href="#" @click.prevent="toggleMenu" class="logout-link">登出</a></li>
            </ul>

            <!-- 子選單內容 -->
            <div v-else class="submenu-content">
              <!-- 會員子選單 -->
              <ul v-if="activeMenu === 'member'" class="submenu-list">
                <li v-if="!isLoggedIn"><a href="#" @click.prevent="selectMenu('login')">註冊/登入 阿聯酋航空 Skywards</a></li>
                <li v-else><a href="#">Skywards 會員資訊</a></li>
              </ul>

              <!-- 協助子選單 -->
              <ul v-if="activeMenu === 'help'" class="submenu-list">
                <li><a href="#">在線客服</a></li>
              </ul>

              <!-- 語言子選單 -->
              <ul v-if="activeMenu === 'lang'" class="submenu-list">
                <li><a href="#">中文</a></li>
                <li><a href="#">英文</a></li>
              </ul>

              <!-- 公告頁面 (卡片顯示) -->
              <div v-if="activeMenu === 'news'" class="announcements-page">
                <div v-for="news in announcements" :key="news.id" class="news-card">
                  <p class="news-date">{{ news.date }}</p>
                  <h3 class="news-title">{{ news.title }}</h3>
                </div>
              </div>
            </div>
          </div>
        </div>
      </transition>

      <main class="hero-section">
        <div class="hero-content">
          <img src="/coin.png" alt="Coin" class="coin-image" />
          <h2 class="hero-subtitle">加入阿聯酋航空 Skywards</h2>
          <h1 class="hero-description">成為阿聯酋航空 Skywards 會員，即可享有航班獎勵、升等及其他福利</h1>
          <button class="cta-button" @click="currentPage = 'login'">立即加入</button>
        </div>
      </main>

      <section class="upgrade-section">
        <div class="upgrade-content">
          <h2 class="upgrade-subtitle">升等至商務艙</h2>
          <h1 class="upgrade-title">使用您的哩程，享受更舒適的旅程</h1>
          <p class="upgrade-description">只需 8,000 哩起，即可升等至商務艙，享受獲獎肯定的服務、美味餐飲及更多禮遇。</p>
          <button class="upgrade-button">立即升等</button>
        </div>
      </section>

      <section class="cabins-section">
        <img src="/cabins.png" alt="Cabins" class="full-width-image" />
      </section>

      <section class="about-section">
        <div class="about-container">
          <div class="about-header">
            <h2 class="about-title">關於我們</h2>
            <div class="title-underline"></div>
          </div>
          <div class="about-grid">
            <div class="about-item">
              <h3 class="item-title">我們的故事</h3>
              <p class="item-text">自 1985 年成立以來，阿聯酋航空已從只有兩架飛機的小型航空公司發展成為全球領先的航空品牌之一。我們以杜拜為基地，連接全球超過 150 個目的地。</p>
            </div>
            <div class="about-item">
              <h3 class="item-title">卓越服務</h3>
              <p class="item-text">我們因其卓越的產品 and 服務而獲得無數獎項。從機上廚師烹製的精緻美食到獲獎肯定的 ice 影音娛樂系統，我們致力於為每位乘客提供難忘的旅程。</p>
            </div>
            <div class="about-item">
              <h3 class="item-title">環境責任</h3>
              <p class="item-text">我們致力於減少營運對環境的影響。我們投資於最先進、燃油效率最高的飛機，並努力在整個供應鏈中實施可持續發展的做法。</p>
            </div>
          </div>
        </div>
      </section>

      <footer class="bottom-nav">
        <div class="nav-item">
          <span class="nav-icon">🏠</span>
          <span class="nav-label">首頁</span>
        </div>
        <div class="nav-item">
          <span class="nav-icon">✈️</span>
          <span class="nav-label">Skywards</span>
        </div>
        <div class="nav-item">
          <span class="nav-icon">👤</span>
          <span class="nav-label">我的</span>
        </div>
      </footer>
    </template>
  </div>
</template>

<style scoped>
.app-container {
  min-height: 100vh;
  background-color: #000000;
  margin: 0;
  padding: 0;
}

.navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  background-color: #000000;
  position: relative;
  z-index: 10;
}

.logo-image {
  height: 50px;
  display: block;
}

.logo a {
  display: flex;
  align-items: center;
  text-decoration: none;
}

.menu-button {
  display: flex;
  align-items: center;
  justify-content: center;
}

.hamburger {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background-color: #f0f0f0;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
  transition: all 0.3s;
  z-index: 1001; /* 確保選單打開時這顆按鈕能被遮擋或自成一體 */
}

/* 漢堡選單內圖示 */
.icon {
  position: relative;
  display: block;
  width: 24px;
  height: 2px;
  background-color: #333;
  transition: all 0.3s;
}

.is-active .icon {
  background-color: transparent;
}

.icon::before,
.icon::after {
  content: '';
  position: absolute;
  width: 24px;
  height: 2px;
  background-color: #333;
  left: 0;
  transition: all 0.3s;
}

.icon::before {
  top: -8px;
}

.icon::after {
  bottom: -8px;
}

/* 全螢幕選單 */
.full-menu-overlay {
  position: fixed;
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 100%;
  max-width: 1024px;
  height: 100vh;
  background-color: #ffffff;
  z-index: 2000;
  display: flex;
  flex-direction: column;
}

.menu-container {
  display: flex;
  flex-direction: column;
  height: 100%;
  padding: 1.5rem;
}

.menu-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #333;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.menu-header-title {
  color: #333;
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0;
}

/* 返回按鈕 */
.back-button {
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 0.5rem;
  display: flex;
  align-items: center;
}

.arrow-left {
  width: 12px;
  height: 12px;
  border-left: 3px solid #333;
  border-bottom: 3px solid #333;
  transform: rotate(45deg);
  display: block;
}

/* 白色叉叉改為深色 */
.close-button {
  width: 40px;
  height: 40px;
  background: transparent;
  border: none;
  cursor: pointer;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}

.cross-icon::before,
.cross-icon::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 24px;
  height: 2px;
  background-color: #333;
}

.cross-icon::before {
  transform: translate(-50%, -50%) rotate(45deg);
}

.cross-icon::after {
  transform: translate(-50%, -50%) rotate(-45deg);
}

/* 選單列表 */
.menu-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
}

.menu-list li a {
  color: #333;
  text-decoration: none;
  font-size: 1.25rem;
  font-weight: 600;
  display: block;
  padding: 1.25rem 0;
  border-bottom: 1px solid #444; /* 深灰分隔線 */
  transition: color 0.2s;
}

/* 子選單列表 */
.submenu-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.submenu-list li a {
  color: #555; /* 深灰字 */
  text-decoration: none;
  font-size: 1.15rem;
  font-weight: 500;
  display: block;
  padding: 1rem 0;
  border-bottom: 1px solid #444; /* 深灰分隔線 */
}

/* 公告頁面樣式 */
.announcements-page {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  height: calc(100vh - 150px);
  overflow-y: auto;
}

.news-card {
  background-color: #f9f9f9;
  padding: 1.5rem;
  border-radius: 8px;
  border-left: 4px solid #d71921;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  text-align: left;
}

.news-date {
  font-size: 0.85rem;
  color: #888;
  margin-bottom: 0.5rem;
}

.news-title {
  font-size: 1.1rem;
  color: #333;
  margin: 0;
  line-height: 1.4;
}

.logout-link {
  color: #d71921 !important;
}

/* 登入頁面樣式 */
.login-page {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 1rem 0;
  max-width: 500px;
  margin: 0 auto;
}

.login-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #333;
  margin-bottom: 1rem;
}

.login-desc {
  font-size: 1rem;
  color: #666;
  line-height: 1.6;
  margin-bottom: 2rem;
}

.login-form {
  width: 100%;
}

.login-input {
  width: 100%;
  padding: 1rem;
  margin-bottom: 1rem;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 1rem;
  box-sizing: border-box;
}

.password-group {
  text-align: left;
  margin-bottom: 2rem;
}

.forgot-password {
  color: #d71921;
  font-size: 0.9rem;
  text-decoration: none;
  font-weight: 700;
  display: inline-block;
}

.login-submit-btn {
  width: 100%;
  background-color: #d71921;
  color: white;
  border: none;
  padding: 1.1rem;
  font-size: 1.1rem;
  font-weight: 700;
  border-radius: 4px;
  cursor: pointer;
}

.login-divider {
  border: 0;
  border-top: 1px solid #ddd;
  margin: 2.5rem 0;
}

.join-now-group {
  text-align: left;
}

.join-label {
  font-size: 0.9rem;
  color: #333;
  margin-bottom: 0.5rem;
  font-weight: 600;
}

.join-now-btn {
  width: 100%;
  background-color: #ffffff;
  color: #000000;
  border: 1px solid #000000;
  padding: 1.1rem;
  font-size: 1.1rem;
  font-weight: 700;
  border-radius: 4px;
  cursor: pointer;
}

/* 獨立登入頁面導覽樣式 */
.standalone-login-page {
  background-color: #ffffff;
  min-height: 100vh;
}

.login-header-nav {
  display: flex;
  align-items: center;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #eee;
  background-color: #ffffff;
}

.back-home-btn {
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 0.5rem;
  display: flex;
  align-items: center;
}

.login-logo-small {
  flex-grow: 1;
  display: flex;
  justify-content: center;
  padding-right: 40px; /* 抵消返回按鈕的寬度，讓 Logo 居中 */
}

.login-logo-small img {
  height: 35px;
}

/* 過渡動畫 */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}

.hero-section {
  position: relative;
  width: 100%;
  height: calc(100vh - 82px);
  background-image: url('/join-background.png');
  background-size: cover;
  background-position: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: white;
  text-align: center;
}

.hero-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0 1.5rem;
}

.coin-image {
  width: 180px;
  margin-bottom: 2rem;
}

.hero-subtitle {
  font-size: 1.75rem;
  font-weight: 500;
  margin-bottom: 0.75rem;
}

.hero-description {
  font-size: 2.25rem;
  font-weight: 700;
  margin-bottom: 2.5rem;
  line-height: 1.3;
  max-width: 800px;
}

.cta-button {
  background-color: #ffffff;
  color: #000000;
  border: none;
  padding: 1.25rem 3rem;
  font-size: 1.2rem;
  font-weight: 700;
  border-radius: 0;
  cursor: pointer;
  transition: opacity 0.2s;
}

.cta-button:hover {
  opacity: 0.9;
}

.upgrade-section {
  position: relative;
  width: 100%;
  height: 100vh;
  background-image: url('/upgrade-bg.png');
  background-size: cover;
  background-position: center;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  text-align: center;
}

.upgrade-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0 1.5rem;
  max-width: 850px;
}

.upgrade-subtitle {
  font-size: 1.5rem;
  font-weight: 500;
  margin-bottom: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.upgrade-title {
  font-size: 3rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
  line-height: 1.2;
}

.upgrade-description {
  font-size: 1.25rem;
  font-weight: 400;
  margin-bottom: 2.5rem;
  line-height: 1.6;
  opacity: 0.9;
}

.upgrade-button {
  background-color: transparent;
  color: white;
  border: 2px solid white;
  padding: 1rem 3rem;
  font-size: 1.1rem;
  font-weight: 600;
  border-radius: 0;
  cursor: pointer;
  transition: all 0.3s ease;
}

.upgrade-button:hover {
  background-color: white;
  color: #000000;
}

.cabins-section {
  width: 100%;
}

.full-width-image {
  width: 100%;
  display: block;
  height: auto;
}

.about-section {
  padding: 6rem 2rem 10rem 2rem;
  background-color: #000000;
  color: white;
  text-align: left;
}

.about-container {
  max-width: 900px;
  margin: 0 auto;
}

.bottom-nav {
  position: fixed;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 100%;
  max-width: 1024px;
  height: 70px;
  background-color: #1a1a1a;
  border-top: 1px solid #333;
  display: flex;
  justify-content: space-around;
  align-items: center;
  z-index: 100;
  padding-bottom: env(safe-area-inset-bottom);
}

.nav-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #888;
  cursor: pointer;
  transition: color 0.2s;
}

.nav-item:hover {
  color: #ffffff;
}

.nav-icon {
  font-size: 1.5rem;
  margin-bottom: 4px;
}

.nav-label {
  font-size: 0.75rem;
  font-weight: 500;
}

.about-header {
  margin-bottom: 4rem;
}

.about-title {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 1rem;
}

.title-underline {
  width: 60px;
  height: 4px;
  background-color: #ffffff;
}

.about-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 3rem;
}

.item-title {
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 1.5rem;
  color: #ffffff;
}

.item-text {
  font-size: 1.1rem;
  line-height: 1.8;
  color: #cccccc;
}

/* 適應手機版 */
@media (max-width: 768px) {
  .about-grid {
    grid-template-columns: 1fr;
    gap: 2.5rem;
  }
}
</style>
