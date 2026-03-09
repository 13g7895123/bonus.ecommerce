<script setup>
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const isMenuOpen = ref(false)
const isLoggedIn = ref(false)
const activeMenu = ref(null)

// 判斷當前頁面
const isHomePage = computed(() => route.path === '/')
const isSettingsPage = computed(() => route.path === '/settings')
const isSkywardsPage = computed(() => route.path === '/skywards')

const announcements = ref([
  { id: 1, date: '2024-03-09', title: '阿聯酋航空最新航班資訊公告' },
  { id: 2, date: '2024-03-08', title: 'Skywards 集哩程計畫更新提醒' },
  { id: 3, date: '2024-03-05', title: '全球機場貴賓室服務調整通知' },
])

const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value
  activeMenu.value = null
}

const goBack = () => {
  activeMenu.value = null
}
</script>

<template>
  <div class="app-container" :class="{ 'menu-active': isMenuOpen, 'white-bg': !isHomePage && !isSkywardsPage }">
    <!-- Navbar 僅在首頁顯示 -->
    <nav v-if="isHomePage" class="navbar">
      <div class="logo">
        <router-link to="/">
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
      <transition name="fade">
        <div v-if="isMenuOpen" class="full-menu-overlay">
          <div class="menu-container-outer">
            <div class="menu-container">
              <div class="menu-header">
                <div v-if="activeMenu" class="header-left">
                  <button class="back-button" @click="goBack">
                    <span class="arrow-left-icon"></span>
                  </button>
                  <h2 class="menu-header-title">
                    {{ activeMenu === 'member' ? '會員' : activeMenu === 'help' ? '協助' : activeMenu === 'news' ? '公告' : '語言' }}
                  </h2>
                </div>
                <div v-else class="header-left">
                  <h2 class="menu-header-title">選單</h2>
                </div>
                <button class="close-button" @click="toggleMenu">
                  <span class="cross-icon"></span>
                </button>
              </div>

              <ul v-if="!activeMenu" class="menu-list">
                <li><a href="#" @click.prevent="activeMenu = 'member'">會員</a></li>
                <li><a href="#" @click.prevent="activeMenu = 'help'">協助</a></li>
                <li><a href="#" @click.prevent="activeMenu = 'news'">公告</a></li>
                <li><a href="#" @click.prevent="activeMenu = 'lang'">語言</a></li>
                <li v-if="isLoggedIn"><a href="#" @click.prevent="toggleMenu" class="logout-link">登出</a></li>
              </ul>

              <div v-else class="submenu-content">
                <ul v-if="activeMenu === 'member'" class="submenu-list">
                  <li v-if="!isLoggedIn">
                    <router-link to="/login" @click="isMenuOpen = false">註冊/登入 阿聯酋航空 Skywards</router-link>
                  </li>
                  <li v-else><a href="#">Skywards 會員資訊</a></li>
                </ul>
                <ul v-if="activeMenu === 'help'" class="submenu-list">
                  <li><a href="#">在線客服</a></li>
                </ul>
                <ul v-if="activeMenu === 'lang'" class="submenu-list">
                  <li><a href="#">中文</a></li>
                  <li><a href="#">英文</a></li>
                </ul>
                <div v-if="activeMenu === 'news'" class="announcements-page">
                  <div v-for="news in announcements" :key="news.id" class="news-card">
                    <p class="news-date">{{ news.date }}</p>
                    <h3 class="news-title">{{ news.title }}</h3>
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
        <span class="nav-icon">🏠</span>
        <span class="nav-label">首頁</span>
      </router-link>
      <router-link to="/skywards" class="nav-item">
        <span class="nav-icon">✈️</span>
        <span class="nav-label">Skywards</span>
      </router-link>
      <router-link to="/settings" class="nav-item">
        <span class="nav-icon">👤</span>
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
  background-color: #000000;
  position: relative;
  z-index: 10;
}

.logo-image {
  height: 50px;
  display: block;
}

.hamburger {
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 10px;
  z-index: 1001;
}

.hamburger .icon {
  display: block;
  width: 24px;
  height: 2px;
  background-color: white;
  position: relative;
}

.hamburger .icon::before,
.hamburger .icon::after {
  content: '';
  position: absolute;
  width: 24px;
  height: 2px;
  background-color: white;
  left: 0;
}

.hamburger .icon::before { top: -8px; }
.hamburger .icon::after { bottom: -8px; }

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
  justify-content: center;
}

.menu-container-outer {
  width: 100%;
  max-width: 1024px;
  height: 100%;
  background-color: #ffffff;
  overflow-y: auto;
}

.menu-container {
  padding: 20px;
  color: #333;
}

.menu-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #eee;
}

.menu-header-title {
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0;
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
  background-color: #333;
  top: 50%;
  left: 0;
}

.cross-icon::before { transform: rotate(45deg); }
.cross-icon::after { transform: rotate(-45deg); }

.arrow-left-icon {
  display: block;
  width: 12px;
  height: 12px;
  border-left: 2px solid #333;
  border-bottom: 2px solid #333;
  transform: rotate(45deg);
}

.menu-list, .submenu-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.menu-list li, .submenu-list li {
  border-bottom: 1px solid #f5f5f5;
}

.menu-list a, .submenu-list a {
  display: block;
  padding: 1.25rem 0;
  color: #333;
  text-decoration: none;
  font-size: 1.1rem;
  font-weight: 500;
}

.logout-link {
  color: #d71921 !important;
}

/* 底部導覽 */
.bottom-nav {
  position: fixed;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 100%;
  max-width: 1024px;
  height: 60px;
  background-color: #111;
  display: flex;
  justify-content: space-around;
  align-items: center;
  z-index: 100;
  border-top: 1px solid #333;
}

.nav-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  color: #999;
  text-decoration: none;
}

.nav-icon {
  font-size: 1.25rem;
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
</style>
