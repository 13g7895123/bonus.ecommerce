<template>
  <PageLayout title="信件" back-to="/settings" theme="white">
    <ContentList>
      <ContentListItem v-for="mail in mails" :key="mail.id" class="mail-item">
        <div class="mail-logo">
          <img src="/logo.png" alt="Logo" class="logo-img" />
        </div>
        <div class="mail-body">
          <p class="mail-subject">{{ mail.subject }}</p>
          <p class="mail-time">{{ mail.time }}</p>
        </div>
      </ContentListItem>
    </ContentList>
  </PageLayout>
</template>

<script setup>
import PageLayout from '../components/PageLayout.vue'
import ContentList from '../components/ContentList.vue'
import ContentListItem from '../components/ContentListItem.vue'

const mails = [
  {
    id: 1,
    subject: '【賣家交易安全提醒】請賣家勿點選可疑QRcode或Line連結:',
    time: '2026/03/11 10:30',
  },
]
</script>

<style scoped>
.mail-item {
  gap: 1rem;
}

.mail-logo {
  flex-shrink: 0;
  width: 44px;
  height: 44px;
  background-color: #ffffff;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
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
