<template>
  <div class="mail-page">
    <PageHeader title="信件" back-to="/settings" />

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
import PageHeader from '../components/PageHeader.vue'

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
