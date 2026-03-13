<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import PageHeader from '../components/PageHeader.vue'

const route = useRoute()
const router = useRouter()
const id = computed(() => route.params.id)

// 返回上一頁功能 (如果沒有上一頁，則返回首頁)
const goBack = () => {
    // 檢查是否有歷史紀錄，或簡單地回首頁。
    // 因為這裡是詳情頁，通常從選單進來，返回首頁較合理，
    // 但因為 PageHeader 直接使用 router-link to="/"，這裡不需要額外邏輯。
    // 但是 PageHeader 的 backTo prop 是 required? No, default ''.
    // 不過在 AnnouncementDetail.vue 中我寫了 backTo="/"。
}

const announcements = [
  { 
    id: 1, 
    date: '2024-03-09 10:30:00',
    title: '【賣家交易安全提醒】請賣家勿點選可疑QRcode或Line連結',
    content: `親愛的用戶您好：

近期詐騙集團猖獗，常假冒買家聯繫賣家，聲稱「無法下單」、「訂單被凍結」或「需要簽署金流協議」，並誘導賣家掃描 QR Code 或點擊不明連結進行所謂的「身分驗證」或「解除限制」。

**請注意！這些都是詐騙手法！**

阿聯酋航空與相關合作夥伴 **絕不會** 要求您：
1. 掃描任何 QR Code 進行驗證。
2. 點擊外部連結輸入銀行帳戶密碼或信用卡號。
3. 透過 LINE 加入所謂的「線上客服」處理金流問題。

若您收到類似訊息，請立即忽略並檢舉。
保護您的帳戶安全，是我們共同的責任。

祝您交易愉快！`
  },
  { 
    id: 2, 
    date: '2024-03-08 14:15:20', 
    title: 'Skywards 集哩程計畫更新提醒',
    content: `親愛的 Skywards 會員您好：

感謝您長期以來的支持。我們不斷致力於優化您的飛行體驗。

自 2024 年 4 月 1 日起，Skywards 集哩程計畫將進行以下調整：
- 搭乘經濟艙 Flex Plus 票價的哩程累積比例將提升至 125%。
- 銀卡會員現在可以在杜拜機使用專屬報到櫃檯。
- 新增更多哩程兌換合作夥伴，包含多家頂級飯店與租車服務。

詳細的更新條款，請參閱我們的官方網站或是會員手冊。
我們期待在雲端與您相遇。`
  },
  { 
    id: 3, 
    date: '2024-03-05 09:45:10', 
    title: '全球機場貴賓室服務調整通知',
    content: `貴賓室服務公告：

為了提供更舒適的休憩空間，以下機場的阿聯酋航空貴賓室將進行整修工程：
- 倫敦希斯洛機場 (LHR) - 預計工期：2024/4/1 - 2024/6/30
- 紐約甘迺迪機場 (JFK) - 預計工期：2024/5/15 - 2024/7/15

施工期間，部分區域將暫停開放。我們已安排鄰近的合作貴賓室供您使用，請洽詢現場地勤人員。
造成您的不便，敬請見諒。`
  },
]

const news = computed(() => announcements.find(n => n.id == id.value))

</script>

<template>
  <div class="announcement-detail-page">
    <!-- 使用 PageHeader，backTo 設為首頁 '/' -->
    <PageHeader title="公告詳情" backTo="/" />
    
    <div v-if="news" class="content-container">
      <h1 class="news-title">{{ news.title }}</h1>
      <p class="news-date">{{ news.date }}</p>
      <div class="news-body">
        <template v-for="(line, index) in news.content.split('\n')" :key="index">
          {{ line }}
          <br />
        </template>
      </div>
    </div>
    <div v-else class="content-container not-found">
      <p>找不到該公告。</p>
    </div>
  </div>
</template>

<style scoped>
.announcement-detail-page {
  background-color: #fff;
  min-height: 100vh;
}

.content-container {
  padding: 1.5rem;
}

.news-title {
  font-size: 1.25rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
  line-height: 1.4;
  color: #333;
}

.news-date {
  font-size: 0.85rem;
  color: #888;
  margin-bottom: 1.5rem;
}

.news-body {
  font-size: 1rem;
  line-height: 1.6;
  color: #444;
  word-wrap: break-word; /* 處理長單字換行 */
}

.not-found {
    text-align: center;
    color: #888;
    margin-top: 2rem;
}
</style>