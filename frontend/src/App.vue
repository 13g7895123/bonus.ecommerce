<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()
const isMenuOpen = ref(false)
const isLoggedIn = ref(false)
const activeMenu = ref(null)

// 檢查登入狀態
const checkLoginStatus = () => {
  const token = localStorage.getItem('token')
  isLoggedIn.value = !!token
}

// 監聽路由變更以更新狀態
watch(route, (newRoute) => {
  checkLoginStatus()
  if (newRoute.query.openMenu === 'news') {
    isMenuOpen.value = true
    activeMenu.value = 'news'
  } else {
    isMenuOpen.value = false
    activeMenu.value = null
  }
})

onMounted(() => {
  checkLoginStatus()
  // 處理重新整理頁面時的狀態
  if (route.query.openMenu === 'news') {
    isMenuOpen.value = true
    activeMenu.value = 'news'
  }
})

// 登出處理
const handleLogout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  isLoggedIn.value = false
  isMenuOpen.value = false
  router.push('/login')
}

// 判斷當前頁面
const isHomePage = computed(() => route.path === '/')
const isSettingsPage = computed(() => route.path === '/settings')
const isSkywardsPage = computed(() => route.path === '/skywards')

const announcements = ref([
  { id: 1, date: '2024-03-09 10:30:00', title: '【賣家交易安全提醒】請賣家勿點選可疑QRcode或Line連結' },
  { id: 2, date: '2024-03-08 14:15:20', title: 'Skywards 集哩程計畫更新提醒' },
  { id: 3, date: '2024-03-05 09:45:10', title: '全球機場貴賓室服務調整通知' },
])

const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value
  activeMenu.value = null
}

const goBack = () => {
  activeMenu.value = null
}

const scrollToTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}
</script>

<template>
  <div class="app-container" :class="{ 'menu-active': isMenuOpen, 'white-bg': !isHomePage && !isSkywardsPage }">
    <!-- Navbar 僅在首頁顯示 -->
    <nav v-if="isHomePage" class="navbar">
      <div class="logo">
        <router-link to="/" @click="scrollToTop">
          <img src="/logo.png" alt="Logo" class="logo-image" />
        </router-link>
      </div>
      <div class="menu-button">
        <button class="hamburger" @click="toggleMenu" :class="{ 'is-active': isMenuOpen }">
          <span class="icon"></span>
        </button>
      </div>
    </nav>

    <!-- 主內容區 -->
    <router-view v-if="!isMenuOpen"></router-view>

    <!-- 全螢幕選單 (限制最大寬度防止滿版) -->
    <template v-if="isHomePage">
      <transition name="slide">
        <div v-if="isMenuOpen" class="full-menu-overlay">
          <div class="menu-clipper">
            <div class="menu-container-outer" :class="{ 'is-main-menu': !activeMenu }">
              <div class="menu-container">
                <div class="menu-header">
                  <div class="header-left-area">
                    <button v-if="activeMenu" class="back-button" @click="goBack">
                      <span class="arrow-left-icon"></span>
                    </button>
                  </div>
                  <h2 v-if="activeMenu" class="menu-header-title">
                    {{ activeMenu === 'member' ? '會員' : activeMenu === 'help' ? '協助' : activeMenu === 'news' ? '公告' : '語言' }}
                  </h2>
                  <div class="header-right-area">
                    <button class="close-button" @click="toggleMenu">
                      <span class="cross-icon"></span>
                    </button>
                  </div>
                </div>

                <ul v-if="!activeMenu" class="menu-list">
                <li><a href="#" @click.prevent="activeMenu = 'member'">會員</a></li>
                <li><a href="#" @click.prevent="activeMenu = 'help'">協助</a></li>
                <li><a href="#" @click.prevent="activeMenu = 'news'">公告</a></li>
                <li><a href="#" @click.prevent="activeMenu = 'lang'">語言</a></li>
                <li v-if="isLoggedIn"><a href="#" @click.prevent="handleLogout" class="logout-link">登出</a></li>
              </ul>

              <div v-else class="submenu-content">
                <ul v-if="activeMenu === 'member'" class="submenu-list">
                  <li v-if="!isLoggedIn">
                    <router-link to="/register" @click="isMenuOpen = false">註冊/登入 阿聯酋航空 Skywards</router-link>
                  </li>
                  <li v-else>
                     <router-link to="/skywards" @click="isMenuOpen = false">Skywards 會員資訊</router-link>
                  </li>
                </ul>
                <ul v-if="activeMenu === 'help'" class="submenu-list">
                  <li>
                    <router-link to="/customer-service" @click="isMenuOpen = false">在線客服</router-link>
                  </li>
                </ul>
                <ul v-if="activeMenu === 'lang'" class="submenu-list">
                  <li><a href="#">中文</a></li>
                  <li><a href="#">英文</a></li>
                </ul>
                <div v-if="activeMenu === 'news'" class="announcements-page">
                  <div v-for="news in announcements" :key="news.id" class="news-card" @click="router.push(`/announcement/${news.id}`)">
                    
                    <h3 class="news-title">{{ news.title }}</h3>
                    <p class="news-date">{{ news.date }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      </transition>
    </template>

    <!-- 底部導覽 (在首頁、設定頁、Skywards 頁顯示) -->
    <footer v-if="isHomePage || isSettingsPage || isSkywardsPage" class="bottom-nav">
      <router-link to="/" class="nav-item">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 9.75L12 3l9 6.75V21a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.75z"/>
          <polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
        <span class="nav-label">首頁</span>
      </router-link>
      <router-link to="/skywards" class="nav-item">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="8" r="4"/>
          <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
        </svg>
        <span class="nav-label">Skywards</span>
      </router-link>
      <router-link to="/settings" class="nav-item">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="3" y1="6" x2="21" y2="6"/>
          <line x1="3" y1="12" x2="21" y2="12"/>
          <line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
        <span class="nav-label">我的</span>
      </router-link>
    </footer>
  </div>
</template>

<style scoped>
.app-container {
  min-height: 100vh;
  background-color: #000000;
  margin: 0;
  padding: 0;
}

.app-container.white-bg {
  background-color: #ffffff;
}

.navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  background-color: white;
  position: sticky;
  top: 0;
  z-index: 10;
}

.logo-image {
  height: 60px;
  display: block;
}

.hamburger {
  background-color: #f0f0f0;
  border-radius: 50%;
  border: none;
  cursor: pointer;
  width: 44px;
  height: 44px;
  padding: 0; 
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1001;
  transition: background-color 0.3s ease;
}

.hamburger:hover {
  background-color: #d0d0d0;
}

.hamburger .icon {
  display: block;
  width: 24px;
  height: 2px;
  background-color: #000000;
  position: relative;
}

.hamburger .icon::before,
.hamburger .icon::after {
  content: '';
  position: absolute;
  width: 24px;
  height: 2px;
  background-color: #000000;
  left: 0;
}

.hamburger .icon::before { top: -8px; }
.hamburger .icon::after { 
  bottom: -8px; 
  width: 16px;
}

/* 菜單覆蓋層 */
.full-menu-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0,0,0,0.5);
  z-index: 1000;
  display: flex;
  justify-content: center; /* Center the constrained area */
  align-items: flex-start; /* Ensure top alignment */
  overflow: hidden;
}

.menu-container-outer {
  width: 100%;
  max-width: var(--app-max-width);
  height: 100%;
  background-color: #ffffff; 
  overflow-y: auto;
  position: relative;
}

/* 主選單 (Main Menu) 黑色風格 */
.menu-container-outer.is-main-menu {
  background-color: #333333;
  color: #ffffff;
}

.menu-container-outer.is-main-menu .menu-list a {
  background-color: #333333;
  color: #ffffff;
  text-align: center;
}

.menu-container-outer.is-main-menu .menu-list li {
  border-bottom: 1px solid #777777; /* Light grey separator for dark theme */
}

.menu-container-outer.is-main-menu .menu-header {
  border-bottom: 1px solid #333333;
}

.menu-clipper {
  width: 100%;
  max-width: var(--app-max-width);
  height: 100%;
  overflow: hidden;
  position: relative;
}

.menu-container {
  padding: 0;
  color: #333;
  width: 100%;
  height: 100%;
}

.menu-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0;
  padding: 1rem 1.5rem;
  background-color: #333333;
  color: #ffffff;
}

.header-left-area, .header-right-area {
  flex: 0 0 44px; /* fixed width ensure title is centered relative to container */
  display: flex;
  align-items: center;
}
.header-right-area {
  justify-content: flex-end;
}

.menu-header-title {
  flex-grow: 1;
  text-align: center;
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0;
  color: #ffffff;
}

.close-button, .back-button {
  background: none;
  border: none;
  cursor: pointer;
  padding: 10px;
}

.cross-icon {
  display: block;
  width: 20px;
  height: 20px;
  position: relative;
}

.cross-icon::before,
.cross-icon::after {
  content: '';
  position: absolute;
  width: 20px;
  height: 2px;
  background-color: #ffffff;
  top: 50%;
  left: 0;
}

.cross-icon::before { transform: rotate(45deg); }
.cross-icon::after { transform: rotate(-45deg); }

.arrow-left-icon {
  display: block;
  width: 12px;
  height: 12px;
  border-left: 2px solid #ffffff;
  border-bottom: 2px solid #ffffff;
  transform: rotate(45deg); /* Originally 45deg makes a < when left/bottom borders used. Wait.
  border-left lines up |, border-bottom lines up _. 45deg rotates it.
  Original: border-left, border-bottom.
  If square is:
    |
   _
  Rotate 45deg clockwise:
    \
     \
  That's a down-left arrow? Or V shape pointing down?
  Let's verify: 
  Unrotated: L shape (bottom-left corner).
  Rotate 45: The corner points Down.
  So original code made a Down arrow?
  Wait, previous code was:
  border-left: 2px solid #333;
  border-bottom: 2px solid #333;
  transform: rotate(45deg);
  
  If I want a DOWN arrow (chevron), I need the corner to point DOWN.
  An L shape rotated 45deg... 
  Let's just use CSS transform to be sure.
  If I want `v`, I need border-right and border-bottom, rotated 45deg.
  (`_` `|` -> `v`)
  
  User asked for "向下的'>'". This is confusing. "Downwards >".
  Maybe they mean a chevron that points down `v`.
  So I will ensure it's a `v` shape.
  border-right + border-bottom + rotate(45deg) -> v.
  
  Let's assume "向下的'>'" means "Chevron that points down".
  */
  border: 0; /* Reset */
  border-right: 2px solid #ffffff;
  border-bottom: 2px solid #ffffff;
  transform: rotate(45deg); 
  margin-top: -5px; /* Adjust alignment */
}

.menu-list, .submenu-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.menu-list li:first-child {
  border-top: 1px solid #f0f0f0;
}

.menu-container-outer.is-main-menu .menu-list li:first-child {
  border-top: 1px solid #777777;
}

.menu-list li, .submenu-list li {
  border-bottom: 1px solid #f0f0f0;
}

.menu-list a, .submenu-list a {
  display: block;
  padding: 1.25rem 1.5rem; /* Add padding here since container lost it */
  color: #333333;
  text-align: left; /* Ensure text aligns left */
  text-decoration: none;
  font-size: 1.25rem;
  font-weight: 500;
  background-color: #ffffff;
}

/* Slide Transition */
.slide-enter-active,
.slide-leave-active {
  transition: background-color 0.3s ease;
}

.slide-enter-from,
.slide-leave-to {
  background-color: rgba(0, 0, 0, 0);
}

.slide-enter-active .menu-container-outer,
.slide-leave-active .menu-container-outer {
  transition: transform 0.3s ease-out;
}

.slide-enter-from .menu-container-outer,
.slide-leave-to .menu-container-outer {
  transform: translateX(100%);
}

.logout-link {
  color: #ff4d4f !important;
}

/* 底部導覽 */
.bottom-nav {
  position: fixed;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 100%;
  max-width: var(--app-max-width);
  height: 60px;
  background-color: #ffffff;
  display: flex;
  justify-content: space-around;
  align-items: center;
  z-index: 100;
  border-top: 1px solid #dddddd;
}

.nav-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  color: #333333;
  text-decoration: none;
}

.nav-icon {
  width: 24px;
  height: 24px;
  margin-bottom: 4px;
}

.nav-label {
  font-size: 0.75rem;
}

.router-link-active.nav-item {
  color: #d71921;
}


/* 動畫 */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

/* 公告樣式調整 */
.announcements-page {
  background-color: #fff;
}

.news-card {
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #ddd;
  background-color: #ffffff;
  display: flex;
  flex-direction: column;
  align-items: flex-start; /* Left align */
  cursor: pointer;
  transition: background-color 0.2s;
}

.news-card:hover {
  background-color: #f9f9f9;
}

.news-title {
  font-size: 1.1rem;
  font-weight: 500;
  margin: 0 0 0.5rem 0;
  color: #333;
  line-height: 1.4;
  text-align: left;
}

.news-date {
  font-size: 0.8rem;
  color: #999; /* Grey color */
  margin: 0;
  align-self: flex-start;
}
</style>
