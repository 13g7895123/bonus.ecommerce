<script setup>
import { ref, onMounted } from 'vue'
import { useApi } from '../composables/useApi'

const api = useApi()
const user = ref(null)
const activeTab = ref('miles') // 'miles' or 'tier'
const showModal = ref(false)

const openModal = () => {
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

const getTierName = (tier) => {
  const tiers = {
    'blue': '藍卡',
    'silver': '銀卡',
    'gold': '金卡',
    'platinum': '白金卡'
  }
  return tiers[tier] || '藍卡'
}

onMounted(() => {
  try {
    const currentUser = api.auth.getCurrentUser()
    if (currentUser) {
      user.value = currentUser
    }
  } catch (e) {
    console.error('Failed to load user info', e)
  }
})
</script>

<template>
  <div class="skywards-page">
    <!-- Header 區塊 -->
    <header class="skywards-header">
      <div class="user-profile">
        <router-link to="/profile" class="profile-more-btn">
          <span class="dots-icon">...</span>
        </router-link>
        <div class="avatar-circle">
          <span class="avatar-icon">👤</span>
        </div>
        <h2 class="user-name">{{ user?.name || 'Guest User' }}</h2>
        <p class="user-id">ID: {{ user?.email || 'N/A' }}</p>
        <div class="user-balance">
          <span class="currency">$</span>
          <span class="amount">{{ user?.wallet?.balance?.toLocaleString() || '0' }}</span>
        </div>
      </div>
      <button class="details-btn">我的明細表</button>
    </header>

    <!-- 狀態切換與等級區塊 -->
    <section class="membership-section">
      <!-- 會員里程數與等級切換 -->
      <div class="stats-container">
        <div 
          class="stat-box" 
          :class="{ active: activeTab === 'miles' }"
          @click="activeTab = 'miles'"
        >
          <span class="stat-value">{{ user?.miles?.toLocaleString() || '0' }}</span>
          <div class="stat-divider"></div>
          <p class="stat-label">Skywards會員里程數</p>
        </div>
        <div 
          class="stat-box" 
          :class="{ active: activeTab === 'tier' }"
          @click="activeTab = 'tier'"
        >
          <span class="stat-value large">{{ user ? getTierName(user.tier) : '藍卡' }}</span>
          <p class="stat-label small">等級</p>
        </div>
      </div>

      <!-- 等級內容 (僅在切換到等級時顯示) -->
      <div v-if="activeTab === 'tier'" class="tier-content">
        <div class="tier-progress-container">
          <div class="tier-rail">
            <div class="tier-line active"></div>
            <div class="tier-line gray"></div>
            <div class="tier-line gray"></div>
          </div>
          <div class="tier-points">
            <div class="tier-point active">
              <span class="point-dot"></span>
              <span class="point-label">藍卡</span>
            </div>
            <div class="tier-point gray">
              <span class="point-dot"></span>
              <span class="point-label">銀卡</span>
            </div>
            <div class="tier-point gray">
              <span class="point-dot"></span>
              <span class="point-label">金卡</span>
            </div>
            <div class="tier-point gray">
              <span class="point-dot"></span>
              <span class="point-label">白金卡</span>
            </div>
          </div>
        </div>
        
        <p class="tier-update-hint">截至2026年2月21日為止,您已擁有0級哩程數</p>
        <button class="view-benefits-btn" @click="openModal">檢視您的權益</button>
      </div>
    </section>

    <!-- 分頁內容 -->
    <section v-if="activeTab === 'miles'" class="cards-section">
      <h3 class="section-hint">用哩程數享受更多好康</h3>
      <div class="benefit-cards">
        <div class="benefit-card">
          <img src="/upgrade-bg.png" alt="Spend" class="benefit-img" />
          <div class="benefit-content">
            <h4 class="benefit-title">立即使用</h4>
            <p class="benefit-desc">將您的 Skywards會員里程使用在我們和全球夥伴提供的專屬優惠</p>
            <a href="#" class="benefit-link">探索優惠</a>
          </div>
        </div>
        <div class="benefit-card">
          <img src="/join-background.png" alt="Earn" class="benefit-img" />
          <div class="benefit-content">
            <h4 class="benefit-title">立即賺取</h4>
            <p class="benefit-desc">探索透過我們和全球夥伴賺取 Skywards 會員里程數的多種方法</p>
            <a href="#" class="benefit-link">開始賺取</a>
          </div>
        </div>
      </div>
    </section>

    <section v-if="activeTab === 'tier'" class="cards-section">
      <div class="benefit-cards">
        <div class="benefit-card">
          <img src="/upgrade-bg.png" alt="Silver Tier" class="benefit-img" />
          <div class="benefit-content">
            <h4 class="benefit-title">達到 <span class="silver-text">銀卡</span></h4>
            <p class="benefit-desc">在2027年2月28日之前賺取25,000級哩程數,或再搭乘 25 次合格航班</p>
          </div>
        </div>
      </div>
    </section>

    <!-- 彈窗組件 -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <transition name="pop">
        <div class="modal-content">
          <div class="modal-header">
            <h3 class="modal-title">升級規則</h3>
            <button class="modal-close" @click="closeModal">✕</button>
          </div>
          <div class="modal-body">
            <p class="rule-hint">掌握 Skywards 等級晉升祕訣：</p>
            <ul class="rule-list">
              <li><strong>銀卡：</strong>累積 25,000 級哩程或搭乘 25 次合格航班。</li>
              <li><strong>金卡：</strong>累積 50,000 級哩程或搭乘 50 次合格航班。</li>
              <li><strong>白金卡：</strong>累積 150,000 級哩程。</li>
            </ul>
            <p class="rule-note">※ 合格航班指由阿聯酋航空或杜拜航空營運且符合積分資格之航班。</p>
          </div>
          <button class="modal-confirm-btn" @click="closeModal">我知道了</button>
        </div>
      </transition>
    </div>
  </div>
</template>

<style scoped>
.skywards-page {
  background-color: #f5f5f5;
  min-height: 100vh;
  padding-bottom: 80px;
  color: #333;
}

.skywards-header {
  background-image: linear-gradient(rgba(0, 32, 91, 0.85), rgba(0, 32, 91, 0.85)), url('/join-background.png');
  background-size: cover;
  background-position: center;
  padding: 3rem 1.5rem 2rem;
  color: white;
  text-align: center;
  position: relative;
}

.profile-more-btn {
  position: absolute;
  top: 1rem;
  right: 1.5rem;
  color: white;
  text-decoration: none;
  font-size: 1.5rem;
  font-weight: 700;
  display: block;
}

.avatar-circle {
  width: 80px;
  height: 80px;
  background-color: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1rem;
  border: 2px solid white;
}

.avatar-icon { font-size: 2.5rem; }
.user-name { font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem; }
.user-id { font-size: 0.9rem; opacity: 0.8; margin-bottom: 1.5rem; }
.user-balance { margin-bottom: 1.5rem; }
.currency { font-size: 1.25rem; vertical-align: top; margin-right: 4px; }
.amount { font-size: 2.5rem; font-weight: 700; }

.details-btn {
  background: transparent;
  border: 1px solid white;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  font-size: 0.85rem;
  cursor: pointer;
  
  /* Modified Logic: Position at bottom left */
  position: absolute;
  bottom: 0.5rem; /* Adjust based on padding of header (currently 3rem top, 2rem bottom) */
  left: 1.5rem;
  margin: 0;
}

.membership-section { background-color: #ffffff; padding: 1.5rem; margin-bottom: 1rem; }
/* .toggle-container { display: flex; background-color: #f0f0f0; border-radius: 30px; padding: 4px; margin-bottom: 2rem; } */
/* .toggle-btn { flex: 1; padding: 0.75rem; border: none; background: transparent; border-radius: 25px; font-size: 1rem; font-weight: 600; cursor: pointer; } */
/* .toggle-btn.active { background-color: #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.1); } */
.stats-container { display: flex; justify-content: space-between; text-align: center; margin-bottom: 2rem; }
.stat-box { 
  flex: 1; 
  display: flex; 
  flex-direction: column; 
  padding: 0 1rem 1rem; 
  cursor: pointer; 
  border-bottom: 3px solid transparent; 
  transition: border-color 0.3s;
}
.stat-box.active {
  border-bottom-color: #E6007E; /* Peach/Pink Highlight */
}
.stat-value { font-size: 1.5rem; font-weight: 700; color: #000; margin-bottom: 10px; }
.stat-value.large { font-size: 1.75rem; color: #00205B; }
.stat-divider { width: 60%; height: 1px; background-color: #ddd; margin: 0 auto 10px; }
.stat-label { font-size: 0.85rem; color: #666; }
.stat-label.small { font-size: 0.75rem; }

.tier-content { text-align: center; padding: 1rem 0; }
.tier-progress-container { position: relative; margin: 3rem 1rem 4rem; }
.tier-rail { position: absolute; top: 10px; left: 10%; right: 10%; height: 2px; background-color: #ddd; display: flex; z-index: 1; }
.tier-line { flex: 1; height: 100%; }
.tier-line.active { background-color: #d71921; }
.tier-points { display: flex; justify-content: space-between; position: relative; z-index: 2; }
.tier-point { display: flex; flex-direction: column; align-items: center; width: 60px; }
.point-dot { width: 20px; height: 20px; background-color: white; border: 2px solid #ddd; border-radius: 50%; margin-bottom: 15px; }
.tier-point.active .point-dot { background-color: #d71921; border-color: #d71921; shadow: 0 0 5px rgba(215, 25, 33, 0.5); }
.tier-point.active .point-label { color: #333; font-weight: 700; }
.point-label { font-size: 0.85rem; color: #999; white-space: nowrap; }
.tier-update-hint { font-size: 0.95rem; color: #333; margin-bottom: 2rem; opacity: 0.9; }
.view-benefits-btn { background-color: transparent; border: 1px solid #333; color: #333; padding: 0.75rem 2rem; border-radius: 4px; font-size: 0.9rem; font-weight: 700; cursor: pointer; }

.cards-section { padding: 1.5rem; }
.benefit-cards { display: flex; flex-direction: column; gap: 1.5rem; }
.benefit-card { background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
.benefit-img { width: 100%; height: 140px; object-fit: cover; }
.benefit-content { padding: 1.25rem; }
.benefit-title { font-size: 1.25rem; font-weight: 700; margin-bottom: 0.75rem; }
.benefit-desc { font-size: 0.95rem; color: #666; line-height: 1.4; margin-bottom: 1rem; }
.benefit-link { display: block; font-weight: 700; color: #000; text-decoration: none; font-size: 1rem; text-transform: uppercase; }
.section-hint { font-size: 1rem; font-weight: 700; margin-bottom: 1.25rem; color: #000; }
.silver-text { margin-left: 0.5rem; color: #000; }

/* 彈窗樣式 */
.modal-overlay {
  position: fixed;
  top: 0; left: 0; width: 100%; height: 100%;
  background-color: rgba(0, 0, 0, 0.7);
  display: flex; justify-content: center; align-items: center;
  z-index: 2000;
}

.modal-content {
  background: white;
  width: 90%; max-width: 400px;
  border-radius: 12px;
  padding: 2rem;
  position: relative;
  box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

.modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.modal-title { font-size: 1.25rem; font-weight: 800; margin: 0; }
.modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #999; }
.rule-hint { font-weight: 700; margin-bottom: 1rem; }
.rule-list { padding-left: 1.25rem; margin-bottom: 1.5rem; }
.rule-list li { margin-bottom: 0.75rem; font-size: 0.95rem; line-height: 1.4; }
.rule-note { font-size: 0.8rem; color: #888; border-top: 1px solid #eee; padding-top: 1rem; }
.modal-confirm-btn {
  width: 100%; background-color: #d71921; color: white; border: none;
  padding: 1rem; border-radius: 4px; font-weight: 700; margin-top: 1.5rem; cursor: pointer;
}

/* 動畫 */
.pop-enter-active, .pop-leave-active { transition: all 0.3s ease; }
.pop-enter-from, .pop-leave-to { opacity: 0; transform: scale(0.9); }
</style>
