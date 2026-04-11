<template>
  <PageLayout title="信件" back-to="/settings" theme="white">
    <ContentList>
      <ContentListItem 
        v-for="mail in mails" 
        :key="mail.id" 
        class="mail-item"
        @click="openMail(mail)"
      >
        <div class="mail-logo">
          <img src="/logo.png" alt="Logo" class="logo-img" />
        </div>
        <div class="mail-body">
          <p class="mail-subject">{{ mail.subject }}</p>
          <p class="mail-time">{{ mail.time }}</p>
        </div>
      </ContentListItem>
    </ContentList>

    <!-- Mail Detail Modal -->
    <div v-if="selectedMail" class="mail-modal" @click="closeMail">
      <div class="mail-modal-content" @click.stop>
        <div class="modal-header">
          <div class="mail-logo">
            <img src="/logo.png" alt="Logo" class="logo-img" />
          </div>
          <div class="header-info">
            <h3 class="header-subject">{{ selectedMail.subject }}</h3>
            <p class="header-time">{{ selectedMail.time }}</p>
          </div>
          <button class="close-btn" @click="closeMail">&times;</button>
        </div>
        <div class="modal-body">
          <div class="modal-text">
            {{ selectedMail.content }}
          </div>
        </div>
      </div>
    </div>
  </PageLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import PageLayout from '../components/PageLayout.vue'
import ContentList from '../components/ContentList.vue'
import ContentListItem from '../components/ContentListItem.vue'

const selectedMail = ref(null)
const mails        = ref([])

const openMail = (mail) => {
  selectedMail.value = mail
}

const closeMail = () => {
  selectedMail.value = null
}

onMounted(async () => {
  try {
    const res = await fetch('/api/v1/mails')
    if (res.ok) {
      const data = await res.json()
      mails.value = (data.items || []).map(m => ({
        id:      m.id,
        subject: m.subject,
        content: m.content,
        time:    m.created_at ? m.created_at.replace('T', ' ').substring(0, 16) : '',
      }))
    }
  } catch {}
  // 若 API 沒有資料，顯示預設信件
  if (!mails.value.length) {
    mails.value = [
      {
        id: 1,
        subject: '【賣家交易安全提醒】請賣家勿點選可疑QRcode或Line連結:',
        time: '2026/03/11 10:30',
        content: `親愛的用戶您好：\n\n近期詐騙集團猖獗，常假冒買家要求賣家點選不明連結或QRcode進行匯款或個資驗證。\n在此這別提醒您：\n1. 本平臺不會要求您私下加Line聯繫。\n2. 請勿點選不明連結或掃描QRcode。\n3. 若有疑慮，請直接聯繫客服中心確認。\n\n感謝您的配合！\n`,
      },
    ]
  }
})
</script>

<style scoped>
.mail-item {
  gap: 1rem;
  cursor: pointer; /* indicate clickable */
  display: flex;
  align-items: center; /* Ensure vertical centering */
  justify-content: flex-start; /* Ensure left alignment */
  text-align: left; /* Ensure text alignment */
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
  font-size: 1rem;
  color: #333;
  margin-bottom: 0.2rem;
  /* Add ellipsis for long subjects */
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis; 
}

.mail-time {
  font-size: 0.85rem;
  color: #999;
  font-family: 'Avram Sans', sans-serif;
}

/* Modal Styles */
.mail-modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
}

.mail-modal-content {
  background-color: white;
  width: 100%;
  max-width: 500px;
  border-radius: 12px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  max-height: 80vh;
}

.modal-header {
  padding: 1rem;
  border-bottom: 1px solid #eee;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.header-info {
  flex: 1;
  text-align: left;
  min-width: 0;
}

.header-subject {
  margin: 0;
  font-size: 1rem;
  font-weight: bold;
  color: #333;
  line-height: 1.4;
}

.header-time {
  margin: 0.2rem 0 0 0;
  font-size: 0.85rem;
  color: #999;
  font-family: 'Avram Sans', sans-serif;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 0;
  line-height: 1;
  color: #999;
  align-self: flex-start; /* Align with top if multi-line title */
  margin-top: -5px;
}

.close-btn:focus {
  outline: none;
}

.modal-body {
  padding: 1rem;
  overflow-y: auto;
}

.modal-text {
  text-align: left;
  white-space: pre-wrap;
  line-height: 1.6;
  color: #333;
}
</style>
