<template>
  <div class="mail-page">
    <div class="mail-header">
      <router-link to="/settings" class="back-btn">
        <span class="arrow-left"></span>
      </router-link>
      <h2 class="header-title">信件</h2>
      <div class="header-placeholder"></div>
    </div>

    <div class="mail-list">
      <div class="mail-item" v-for="mail in mails" :key="mail.id">
        <div class="mail-logo">
          <img src="/logo.png" alt="Logo" class="logo-img" />
        </div>
        <div class="mail-body">
          <p class="mail-subject">{{ mail.subject }}</p>
          <p class="mail-time">{{ mail.time }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const mails = [
  {
    id: 1,
    subject: '【賣家交易安全提醒】請賣家勿點選可疑QRcode或Line連結:',
    time: '2026/03/11 10:30',
  },
]
</script>

<style scoped>
.mail-page {
  background-color: #f5f5f5;
  min-height: 100vh;
}

.mail-header {
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

.mail-list {
  margin-top: 1rem;
  background-color: #ffffff;
}

.mail-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #f0f0f0;
  cursor: pointer;
}

.mail-logo {
  flex-shrink: 0;
  width: 44px;
  height: 44px;
  background-color: #f5f5f5;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.logo-img {
  width: 32px;
  height: 32px;
  object-fit: contain;
}

.mail-body {
  flex: 1;
  min-width: 0;
}

.mail-subject {
  font-size: 0.9rem;
  font-weight: 500;
  color: #222;
  margin: 0 0 0.35rem 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.mail-time {
  font-size: 0.775rem;
  color: #999;
  margin: 0;
}
</style>
