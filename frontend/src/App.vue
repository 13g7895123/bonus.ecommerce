<script setup>
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import ToastNotification from '@/components/ToastNotification.vue'
import { useToast } from '@/composables/useToast'
import { apiFetch } from '@/utils/apiFetch'

const route = useRoute()
const router = useRouter()
const { t, locale } = useI18n()
const toast = useToast()

const isMenuOpen = ref(false)
const isLoggedIn = ref(false)
const activeMenu = ref(null)
const showDeviceUnboundDialog = ref(false)
const activeNotificationTab = ref('announcements')
const topAnnouncement = ref(null)
const announcements = ref([])
const notificationData = ref({
  announcements: { items: [], unread_count: 0 },
  mails: { items: [], unread_count: 0 },
})
const selectedMail = ref(null)
const announcementTabRef = ref(null)
const mailTabRef = ref(null)

const isHomePage = computed(() => route.path === '/')
const isSettingsPage = computed(() => route.path === '/settings')
const isSkywardsPage = computed(() => route.path === '/skywards')

const notificationAnnouncements = computed(() => {
  return announcements.value.map(item => ({ ...item, is_read: 1 }))
})

const notificationMails = computed(() => notificationData.value.mails.items)

const setLocale = (lang) => {
  locale.value = lang
  localStorage.setItem('app_locale', lang)
  activeMenu.value = null
}

const checkLoginStatus = () => {
  isLoggedIn.value = !!localStorage.getItem('token')
}

const formatDateTime = (value) => {
  if (!value) return ''
  return String(value).replace('T', ' ').substring(0, 16)
}

const badgeText = (count) => {
  const value = Number(count || 0)
  if (value <= 0) return ''
  return value > 99 ? '99+' : String(value)
}

const loadAnnouncements = async () => {
  try {
    const res = await apiFetch('/api/v1/announcements?limit=20')
    if (!res.ok) return

    const json = await res.json()
    const items = json.data?.items || []
    announcements.value = items.map(a => ({
      id: a.id,
      title: a.title,
      content: a.content,
      content_text: a.content_text || '',
      is_pinned: Number(a.is_pinned || 0),
      date: a.published_at || '',
      published_at: a.published_at || '',
    }))
    topAnnouncement.value = json.data?.top_announcement || announcements.value[0] || null
  } catch {}
}

const loadNotifications = async () => {
  if (!isLoggedIn.value) {
    notificationData.value = {
      announcements: { items: [], unread_count: 0 },
      mails: { items: [], unread_count: 0 },
    }
    return
  }

  try {
    const res = await apiFetch('/api/v1/me/notifications', { auth: true })
    if (!res.ok) return
    const json = await res.json()
    notificationData.value = json.data || notificationData.value
  } catch {}
}

const focusNotificationTab = async (tab = activeNotificationTab.value) => {
  await nextTick()
  const target = tab === 'mails' ? mailTabRef.value : announcementTabRef.value
  target?.focus()
}

const syncMenuState = async (newRoute = route) => {
  checkLoginStatus()
  if (newRoute.query.openMenu === 'news') {
    isMenuOpen.value = true
    activeMenu.value = 'news'
    activeNotificationTab.value = 'announcements'
    await loadNotifications()
    await focusNotificationTab('announcements')
  } else {
    isMenuOpen.value = false
    activeMenu.value = null
  }
}

watch(route, syncMenuState)

onMounted(async () => {
  checkLoginStatus()
  await loadAnnouncements()
  await syncMenuState(route)

  window.addEventListener('auth:expired', () => {
    isLoggedIn.value = false
    toast.warning(t('auth.sessionExpired') || '登入已過期，請重新登入')
    router.push('/')
  })

  window.addEventListener('device:unbound', () => {
    showDeviceUnboundDialog.value = true
  })
})

const handleLogout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  isLoggedIn.value = false
  isMenuOpen.value = false
  activeMenu.value = null
  router.push('/login')
}

const toggleMenu = async () => {
  isMenuOpen.value = !isMenuOpen.value
  if (isMenuOpen.value) {
    activeMenu.value = null
    await loadNotifications()
  } else {
    activeMenu.value = null
    selectedMail.value = null
  }
}

const openMenuSection = async (menu) => {
  activeMenu.value = menu
  if (menu === 'news') {
    activeNotificationTab.value = 'announcements'
    await loadNotifications()
    await focusNotificationTab('announcements')
  }
}

const goBack = () => {
  activeMenu.value = null
  selectedMail.value = null
}

const scrollToTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const openMailModal = async (mail) => {
  if (!isLoggedIn.value) {
    toast.warning(t('auth.loginRequired'))
    return
  }

  selectedMail.value = mail
  if (Number(mail.is_read) === 1) return

  try {
    await apiFetch(`/api/v1/me/mails/${mail.id}`, {
      method: 'PATCH',
      auth: true,
    })
  } catch {}

  mail.is_read = 1
  notificationData.value.mails.unread_count = Math.max(0, Number(notificationData.value.mails.unread_count || 0) - 1)
}

const closeMailModal = () => {
  selectedMail.value = null
}

const openAnnouncement = async (item) => {
  if (isLoggedIn.value && Number(item.is_read) === 0) {
    try {
      await apiFetch(`/api/v1/me/announcements/${item.id}`, {
        method: 'PATCH',
        auth: true,
      })
    } catch {}

    item.is_read = 1
    notificationData.value.announcements.unread_count = Math.max(0, Number(notificationData.value.announcements.unread_count || 0) - 1)
  }

  isMenuOpen.value = false
  activeMenu.value = null
  router.push(`/announcement/${item.id}`)
}

const activeMenuLabel = computed(() => {
  const map = {
    member: t('nav.member'),
    help: t('nav.help'),
    news: t('nav.news'),
    lang: t('nav.language'),
  }
  return map[activeMenu.value] || ''
})
</script>

<template>
  <div class="app-container" :class="{ 'menu-active': isMenuOpen, 'white-bg': !isHomePage && !isSkywardsPage }">
    <ToastNotification />

    <nav v-if="isHomePage" class="navbar">
      <div class="logo">
        <router-link to="/" @click="scrollToTop">
          <img src="/logo.png" alt="Logo" class="logo-image" />
        </router-link>
      </div>
      <div class="marquee-wrap">
        <div class="marquee-track" :class="{ moving: topAnnouncement?.content_text }">
          <span class="marquee-text">{{ topAnnouncement?.content_text || '' }}</span>
        </div>
      </div>
      <div class="menu-button">
        <button class="hamburger" @click="toggleMenu" :class="{ 'is-active': isMenuOpen }">
          <span class="icon"></span>
        </button>
      </div>
    </nav>

    <router-view v-if="!isMenuOpen"></router-view>

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
                  <h2 v-if="activeMenu" class="menu-header-title">{{ activeMenuLabel }}</h2>
                  <div class="header-right-area">
                    <button class="close-button" @click="toggleMenu">
                      <span class="cross-icon"></span>
                    </button>
                  </div>
                </div>

                <ul v-if="!activeMenu" class="menu-list">
                  <li><a href="#" @click.prevent="openMenuSection('member')">{{ $t('nav.member') }}</a></li>
                  <li><a href="#" @click.prevent="openMenuSection('help')">{{ $t('nav.help') }}</a></li>
                  <li>
                    <a href="#" class="menu-link-with-badge" @click.prevent="openMenuSection('news')">
                      <span>{{ $t('nav.news') }}</span>
                    </a>
                  </li>
                  <li><a href="#" @click.prevent="openMenuSection('lang')">{{ $t('nav.language') }}</a></li>
                  <li v-if="isLoggedIn"><a href="#" @click.prevent="handleLogout" class="logout-link">{{ $t('nav.logout') }}</a></li>
                </ul>

                <div v-else class="submenu-content">
                  <ul v-if="activeMenu === 'member'" class="submenu-list">
                    <li v-if="!isLoggedIn">
                      <router-link to="/login" @click="isMenuOpen = false">{{ $t('nav.register') }} / {{ $t('nav.login') }} {{ $t('nav.emiratesSkywards') }}</router-link>
                    </li>
                    <li v-else>
                      <router-link to="/skywards" @click="isMenuOpen = false">Skywards {{ $t('nav.member') }}</router-link>
                    </li>
                  </ul>

                  <ul v-if="activeMenu === 'help'" class="submenu-list">
                    <li>
                      <router-link to="/customer-service" @click="isMenuOpen = false">{{ $t('nav.onlineCS') }}</router-link>
                    </li>
                  </ul>

                  <ul v-if="activeMenu === 'lang'" class="submenu-list lang-list">
                    <li>
                      <a href="#" :class="{ active: $i18n.locale === 'zh-TW' }" @click.prevent="setLocale('zh-TW')">{{ $t('lang.zhTW') }}</a>
                    </li>
                    <li>
                      <a href="#" :class="{ active: $i18n.locale === 'en' }" @click.prevent="setLocale('en')">{{ $t('lang.en') }}</a>
                    </li>
                  </ul>

                  <div v-if="activeMenu === 'news'" class="announcements-page">
                    <div class="notification-tabs">
                      <button
                        ref="announcementTabRef"
                        class="notification-tab"
                        :class="{ active: activeNotificationTab === 'announcements' }"
                        @click="activeNotificationTab = 'announcements'; focusNotificationTab('announcements')"
                      >
                        <span>{{ $t('notifications.latest') }}</span>
                        <span
                          v-if="notificationAnnouncements.length > 0"
                          class="tab-badge"
                        >
                          {{ badgeText(notificationAnnouncements.length) }}
                        </span>
                      </button>
                      <button
                        ref="mailTabRef"
                        class="notification-tab"
                        :class="{ active: activeNotificationTab === 'mails' }"
                        @click="activeNotificationTab = 'mails'; focusNotificationTab('mails')"
                      >
                        <span>{{ $t('notifications.personal') }}</span>
                        <span
                          v-if="isLoggedIn && notificationData.mails.unread_count > 0"
                          class="tab-badge"
                        >
                          {{ badgeText(notificationData.mails.unread_count) }}
                        </span>
                      </button>
                    </div>

                    <div v-if="activeNotificationTab === 'announcements'">
                      <div
                        v-for="news in notificationAnnouncements"
                        :key="news.id"
                        class="news-card"
                        :class="{ unread: Number(news.is_read) === 0 }"
                        @click="openAnnouncement(news)"
                      >
                        <div class="card-topline">
                          <h3 class="news-title">{{ news.title }}</h3>
                          <span v-if="Number(news.is_read) === 0" class="unread-dot"></span>
                        </div>
                        <p class="news-date">{{ formatDateTime(news.published_at || news.date) }}</p>
                      </div>
                      <div v-if="notificationAnnouncements.length === 0" class="empty-state">{{ $t('notifications.emptyAnnouncements') }}</div>
                    </div>

                    <div v-else>
                      <div v-if="!isLoggedIn" class="empty-state">{{ $t('auth.loginRequired') }}</div>
                      <template v-else>
                        <div
                          v-for="mail in notificationMails"
                          :key="mail.id"
                          class="news-card"
                          :class="{ unread: Number(mail.is_read) === 0 }"
                          @click="openMailModal(mail)"
                        >
                          <div class="card-topline">
                            <h3 class="news-title">{{ mail.subject }}</h3>
                            <span v-if="Number(mail.is_read) === 0" class="unread-dot"></span>
                          </div>
                          <p class="news-date">{{ formatDateTime(mail.created_at) }}</p>
                        </div>
                        <div v-if="notificationMails.length === 0" class="empty-state">{{ $t('notifications.emptyMails') }}</div>
                      </template>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </template>

    <footer v-if="isHomePage || isSettingsPage || isSkywardsPage" class="bottom-nav">
      <router-link to="/" class="nav-item">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 9.75L12 3l9 6.75V21a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.75z"/>
          <polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
        <span class="nav-label">{{ $t('nav.home') }}</span>
      </router-link>
      <a class="nav-item" :class="{ 'router-link-active': isSkywardsPage }" style="cursor:pointer" @click="isLoggedIn ? router.push('/skywards') : router.push('/login')">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M12 13.5C15.7266 13.5 18.75 10.4766 18.75 6.75C18.75 3.02344 15.7266 0 12 0C8.27344 0 5.25 3.02344 5.25 6.75C5.25 10.4766 8.27344 13.5 12 13.5ZM18 15H15.4172C14.3766 15.4781 13.2188 15.75 12 15.75C10.7812 15.75 9.62813 15.4781 8.58281 15H6C2.68594 15 0 17.6859 0 21V21.75C0 22.9922 1.00781 24 2.25 24H21.75C22.9922 24 24 22.9922 24 21.75V21C24 17.6859 21.3141 15 18 15Z" fill="currentColor"/>
        </svg>
        <span class="nav-label">{{ $t('nav.skywards') }}</span>
      </a>
      <a class="nav-item" :class="{ 'router-link-active': isSettingsPage }" style="cursor:pointer" @click="isLoggedIn ? router.push('/settings') : router.push('/login')">
        <svg width="24" height="19" viewBox="0 0 24 19" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M0.857143 3.375H23.1429C23.6163 3.375 24 3.03923 24 2.625V0.75C24 0.335766 23.6163 0 23.1429 0H0.857143C0.383732 0 0 0.335766 0 0.75V2.625C0 3.03923 0.383732 3.375 0.857143 3.375ZM0.857143 10.875H23.1429C23.6163 10.875 24 10.5392 24 10.125V8.25C24 7.83577 23.6163 7.5 23.1429 7.5H0.857143C0.383732 7.5 0 7.83577 0 8.25V10.125C0 10.5392 0.383732 10.875 0.857143 10.875ZM0.857143 18.375H23.1429C23.6163 18.375 24 18.0392 24 17.625V15.75C24 15.3358 23.6163 15 23.1429 15H0.857143C0.383732 15 0 15.3358 0 15.75V17.625C0 18.0392 0.383732 18.375 0.857143 18.375Z" fill="currentColor"/>
        </svg>
        <span class="nav-label">{{ $t('nav.settings') }}</span>
      </a>
    </footer>

    <Teleport to="body">
      <div v-if="selectedMail" class="mail-modal" @click="closeMailModal">
        <div class="mail-modal-content" @click.stop>
          <div class="mail-modal-header">
            <h3 class="mail-modal-title">{{ selectedMail.subject }}</h3>
            <button class="mail-modal-close" @click="closeMailModal">&times;</button>
          </div>
          <p class="mail-modal-time">{{ formatDateTime(selectedMail.created_at) }}</p>
          <div class="mail-modal-body">{{ selectedMail.content }}</div>
        </div>
      </div>
    </Teleport>

    <Teleport to="body">
      <div v-if="showDeviceUnboundDialog" class="device-dialog-overlay" @click.self="showDeviceUnboundDialog = false">
        <div class="device-dialog">
          <div class="device-dialog-icon">⚠️</div>
          <h3 class="device-dialog-title">尚未綁定設備</h3>
          <p class="device-dialog-body">此操作需要先完成設備綁定。如需解除綁定或重新綁定，請聯繫客服人員協助處理。</p>
          <button class="device-dialog-btn" @click="showDeviceUnboundDialog = false">我知道了</button>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<style scoped>
.app-container {
  min-height: 100vh;
  background-color: #000;
  margin: 0;
  padding: 0;
}

.app-container.white-bg {
  background-color: #fff;
}

.navbar {
  display: grid;
  grid-template-columns: auto 1fr auto;
  gap: 0.75rem;
  align-items: center;
  padding: 1rem 1.5rem;
  background-color: #fff;
  position: sticky;
  top: 0;
  z-index: 10;
}

.logo-image {
  height: 60px;
  display: block;
}

.marquee-wrap {
  height: 42px;
  border-radius: 999px;
  background: linear-gradient(90deg, #fff1f1 0%, #f8f8f8 100%);
  border: 1px solid #f0d0d0;
  overflow: hidden;
  position: relative;
}

.marquee-track {
  display: flex;
  align-items: center;
  height: 100%;
  padding-left: 1rem;
  white-space: nowrap;
}

.marquee-track.moving .marquee-text {
  display: inline-block;
  padding-right: 3rem;
  animation: marquee 18s linear infinite;
}

.marquee-text {
  color: #8e1117;
  font-size: 0.92rem;
  font-weight: 600;
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

.hamburger .icon,
.hamburger .icon::before,
.hamburger .icon::after {
  display: block;
  width: 24px;
  height: 2px;
  background-color: #000;
  position: relative;
}

.hamburger .icon::before,
.hamburger .icon::after {
  content: '';
  position: absolute;
  left: 0;
}

.hamburger .icon::before { top: -8px; }
.hamburger .icon::after { bottom: -8px; width: 16px; }

.full-menu-overlay {
  position: fixed;
  inset: 0;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 1000;
  display: flex;
  justify-content: center;
  align-items: flex-start;
  overflow: hidden;
}

.menu-clipper,
.menu-container-outer {
  width: 100%;
  max-width: var(--app-max-width);
  height: 100%;
}

.menu-clipper {
  overflow: hidden;
  position: relative;
}

.menu-container-outer {
  background-color: #fff;
  overflow-y: auto;
  position: relative;
}

.menu-container-outer.is-main-menu {
  background-color: #333;
  color: #fff;
}

.menu-container-outer.is-main-menu .menu-list a {
  background-color: #333;
  color: #fff;
  text-align: center;
}

.menu-container-outer.is-main-menu .menu-list li {
  border-bottom: 1px solid #777;
}

.menu-container {
  color: #333;
  width: 100%;
  height: 100%;
}

.menu-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  background-color: #333;
  color: #fff;
}

.header-left-area,
.header-right-area {
  flex: 0 0 44px;
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
}

.close-button,
.back-button {
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
  background-color: #fff;
  top: 50%;
  left: 0;
}

.cross-icon::before { transform: rotate(45deg); }
.cross-icon::after { transform: rotate(-45deg); }

.arrow-left-icon {
  display: block;
  width: 12px;
  height: 12px;
  border-left: 2px solid #fff;
  border-bottom: 2px solid #fff;
  transform: rotate(45deg);
}

.menu-list,
.submenu-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.menu-list li:first-child {
  border-top: 1px solid #f0f0f0;
}

.menu-container-outer.is-main-menu .menu-list li:first-child {
  border-top: 1px solid #777;
}

.menu-list li,
.submenu-list li {
  border-bottom: 1px solid #f0f0f0;
}

.menu-list a,
.submenu-list a {
  display: block;
  padding: 1.25rem 1.5rem;
  color: #333;
  text-align: left;
  text-decoration: none;
  font-size: 1.25rem;
  font-weight: 500;
  background-color: #fff;
}

.menu-link-with-badge {
  display: flex !important;
  justify-content: center;
  align-items: center;
  gap: 0.5rem;
}

.lang-list a.active {
  color: #d71921;
  font-weight: 700;
}

.submenu-content {
  background: #fff;
  min-height: calc(100vh - 72px);
}

.notification-tabs {
  display: grid;
  grid-template-columns: 1fr 1fr;
  border-bottom: 1px solid #ececec;
  background: #fff;
  position: sticky;
  top: 0;
  z-index: 1;
}

.notification-tab {
  border: none;
  background: transparent;
  padding: 1rem 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  font-size: 1rem;
  font-weight: 700;
  color: #737373;
  cursor: pointer;
}

.notification-tab.active {
  color: #d71921;
  box-shadow: inset 0 -3px 0 #d71921;
}

.tab-badge {
  min-width: 22px;
  height: 22px;
  padding: 0 6px;
  border-radius: 999px;
  background: #df1b24;
  color: #fff;
  font-size: 0.75rem;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.announcements-page {
  background-color: #fff;
}

.news-card {
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #ddd;
  background-color: #fff;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  cursor: pointer;
  transition: background-color 0.2s;
}

.news-card.unread {
  background: #fff8f8;
}

.news-card:hover {
  background-color: #f9f9f9;
}

.card-topline {
  width: 100%;
  display: flex;
  justify-content: space-between;
  gap: 0.75rem;
  align-items: center;
}

.news-title {
  font-size: 1.05rem;
  font-weight: 600;
  margin: 0 0 0.5rem;
  color: #333;
  line-height: 1.4;
}

.news-date {
  font-size: 0.8rem;
  color: #999;
  margin: 0;
}

.unread-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: #df1b24;
  flex-shrink: 0;
}

.empty-state {
  padding: 2rem 1.5rem;
  color: #888;
  text-align: center;
}

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

.bottom-nav {
  position: fixed;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 100%;
  max-width: var(--app-max-width);
  height: 60px;
  background-color: #fff;
  display: flex;
  justify-content: space-around;
  align-items: center;
  z-index: 100;
  border-top: 1px solid #ddd;
}

.nav-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  color: #333;
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

.mail-modal {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1200;
  padding: 1rem;
}

.mail-modal-content {
  width: 100%;
  max-width: 520px;
  background: #fff;
  border-radius: 18px;
  padding: 1.25rem;
}

.mail-modal-header {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  align-items: flex-start;
}

.mail-modal-title {
  margin: 0;
  font-size: 1.1rem;
  color: #222;
}

.mail-modal-close {
  border: none;
  background: transparent;
  font-size: 1.6rem;
  line-height: 1;
  cursor: pointer;
  color: #999;
}

.mail-modal-time {
  margin: 0.5rem 0 1rem;
  font-size: 0.82rem;
  color: #999;
}

.mail-modal-body {
  white-space: pre-wrap;
  line-height: 1.7;
  color: #444;
}

.device-dialog-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.55);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

.device-dialog {
  background: #fff;
  border-radius: 16px;
  padding: 2rem 1.5rem;
  max-width: 360px;
  width: 100%;
  text-align: center;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.18);
}

.device-dialog-icon {
  font-size: 2.5rem;
  margin-bottom: 0.75rem;
}

.device-dialog-title {
  font-size: 1.2rem;
  font-weight: 700;
  color: #1a1a1a;
  margin: 0 0 0.75rem;
}

.device-dialog-body {
  font-size: 0.9rem;
  color: #555;
  line-height: 1.6;
  margin: 0 0 1.5rem;
}

.device-dialog-btn {
  width: 100%;
  padding: 0.75rem;
  background: #d71921;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
}

@keyframes marquee {
  0% { transform: translateX(100%); }
  100% { transform: translateX(-100%); }
}

@media (max-width: 767px) {
  .navbar {
    grid-template-columns: auto 1fr auto;
    padding: 0.5rem 1rem;
  }

  .logo-image {
    height: 40px;
  }

  .marquee-wrap {
    height: 34px;
  }

  .marquee-text {
    font-size: 0.78rem;
  }

  .hamburger {
    width: 36px;
    height: 36px;
  }

  .bottom-nav {
    height: 50px;
  }

  .nav-icon {
    width: 20px;
    height: 20px;
    margin-bottom: 2px;
  }

  .nav-label {
    font-size: 0.65rem;
  }
}
</style>
