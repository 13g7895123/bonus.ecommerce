<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useApi } from '../composables/useApi'
import { useToast } from '../composables/useToast'

const api = useApi()
const toast = useToast()
const router = useRouter()
const user = ref(null)
const activeTab = ref('miles') // 'miles' or 'tier'
const showModal = ref(false)
const tierCardDescs = ref({
  silver: '',
  gold: '',
  platinum: '',
})
const benefitsHtml = ref('')

const TIER_ORDER = ['blue', 'silver', 'gold', 'platinum']
const TIER_NAMES = { blue: '藍卡', silver: '銀卡', gold: '金卡', platinum: '白金卡' }

const tierIndex = computed(() => {
  const idx = TIER_ORDER.indexOf(user.value?.tier)
  return idx >= 0 ? idx : 0
})

const nextTierInfo = computed(() => {
  const next = TIER_ORDER[tierIndex.value + 1]
  if (!next) return null
  return {
    key: next,
    name: TIER_NAMES[next],
    desc: tierCardDescs.value[next],
  }
})

const setTab = (tab) => {
  activeTab.value = tab
  router.replace({ hash: '#' + tab })
}

const openModal = () => { showModal.value = true }
const closeModal = () => { showModal.value = false }

const loadConfigs = async () => {
  try {
    const [r1, r2, r3, r4] = await Promise.all([
      fetch('/api/v1/config/skywards_silver_card_desc'),
      fetch('/api/v1/config/skywards_gold_card_desc'),
      fetch('/api/v1/config/skywards_platinum_card_desc'),
      fetch('/api/v1/config/skywards_benefits_html'),
    ])
    const [d1, d2, d3, d4] = await Promise.all([r1.json(), r2.json(), r3.json(), r4.json()])
    tierCardDescs.value.silver   = d1.value || ''
    tierCardDescs.value.gold     = d2.value || ''
    tierCardDescs.value.platinum = d3.value || ''
    benefitsHtml.value           = d4.value || ''
  } catch {}
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

onMounted(async () => {
  // 從 URL hash 恢復 tab
  const hash = window.location.hash.replace('#', '')
  if (hash === 'miles' || hash === 'tier') {
    activeTab.value = hash
  }
  try {
    const data = await api.user.getProfile()
    if (data) user.value = data
  } catch (e) {
    toast.error('無法載入使用者資訊')
  }
  await loadConfigs()
})
</script>

<template>
  <div class="skywards-page">
    <!-- Header 區塊 -->
    <header class="skywards-header">
      <div class="user-profile">
        <router-link to="/profile" class="profile-more-btn">
          <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="1"></circle>
            <circle cx="19" cy="12" r="1"></circle>
            <circle cx="5" cy="12" r="1"></circle>
          </svg>
        </router-link>
        <div class="avatar-circle">
          <img v-if="user?.avatar" :src="user.avatar" class="avatar-img" alt="Avatar" />
          <svg v-else class="avatar-icon" viewBox="0 0 24 24" width="40" height="40" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
        </div>
        <h2 class="user-name">{{ user?.name || 'Guest User' }}</h2>
        <p class="user-id">ID: {{ user?.email || 'N/A' }}</p>
        <div class="user-balance">
          <span class="currency">$</span>
          <span class="amount">{{ user?.wallet?.balance?.toLocaleString() || '0' }}</span>
        </div>
      </div>
      <button class="details-btn" @click="$router.push('/mileage-records')">我的明細表</button>
    </header>

    <!-- 狀態切換與等級區塊 -->
    <section class="membership-section">
      <!-- 會員里程數與等級切換 -->
      <div class="stats-container">
        <div 
          class="stat-box" 
          :class="{ active: activeTab === 'miles' }"
          @click="setTab('miles')"
        >
          <span class="stat-value">{{ user?.miles?.toLocaleString() || '0' }}</span>
          <p class="stat-label">Skywards會員里程數</p>
        </div>
        
        <div class="stat-divider-vertical"></div>

        <div 
          class="stat-box" 
          :class="{ active: activeTab === 'tier' }"
          @click="setTab('tier')"
        >
          <span class="stat-value large">{{ user ? getTierName(user.tier) : '藍卡' }}</span>
          <p class="stat-label small">等級</p>
        </div>
      </div>

      <!-- 等級內容 (僅在切換到等級時顯示) -->
      <div v-if="activeTab === 'tier'" class="tier-content">
        <div class="tier-progress-container">
          <div class="tier-rail">
            <div class="tier-line" :class="tierIndex >= 1 ? 'active' : 'gray'"></div>
            <div class="tier-line" :class="tierIndex >= 2 ? 'active' : 'gray'"></div>
            <div class="tier-line" :class="tierIndex >= 3 ? 'active' : 'gray'"></div>
          </div>
          <div class="tier-points">
            <div class="tier-point" :class="tierIndex >= 0 ? 'active' : 'gray'">
              <span class="point-dot"></span>
              <span class="point-label">藍卡</span>
            </div>
            <div class="tier-point" :class="tierIndex >= 1 ? 'active' : 'gray'">
              <span class="point-dot"></span>
              <span class="point-label">銀卡</span>
            </div>
            <div class="tier-point" :class="tierIndex >= 2 ? 'active' : 'gray'">
              <span class="point-dot"></span>
              <span class="point-label">金卡</span>
            </div>
            <div class="tier-point" :class="tierIndex >= 3 ? 'active' : 'gray'">
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
      <!-- <h3 class="section-hint">用哩程數享受更多好康</h3> -->
      <div class="benefit-cards">
        <div class="benefit-card" @click="$router.push('/mileage-redemption')">
          <img src="/use-now.png" alt="Spend" class="benefit-img" />
          <div class="benefit-content">
            <h4 class="benefit-title">立即使用</h4>
            <p class="benefit-desc">將您的 Skywards會員里程使用在我們和全球夥伴提供的專屬優惠</p>
            <a href="#" class="benefit-link" @click.prevent="$router.push('/mileage-redemption')">探索優惠</a>
          </div>
        </div>
        <div class="benefit-card" @click="$router.push('/customer-service?from=skywards')">
          <img src="/make-now.png" alt="Earn" class="benefit-img" />
          <div class="benefit-content">
            <h4 class="benefit-title">立即賺取</h4>
            <p class="benefit-desc">探索透過我們和全球夥伴賺取 Skywards 會員里程數的多種方法</p>
            <a href="#" class="benefit-link" @click.prevent="$router.push('/customer-service?from=skywards')">開始賺取</a>
          </div>
        </div>
      </div>
      <span class="comfortable-text">用哩程數享受更多好康</span>
      <div class="comfortable-banner">
        <img src="/comfortable.png" alt="舒適體驗" class="comfortable-img" />
      </div>
    </section>

    <section v-if="activeTab === 'tier'" class="cards-section">
      <div class="benefit-cards">
        <div v-if="nextTierInfo" class="benefit-card no-pointer">
          <img src="/go-silver.png" alt="Tier" class="benefit-img" />
          <div class="benefit-content">
            <h4 class="benefit-title">達到 <span class="silver-text">{{ nextTierInfo.name }}</span></h4>
            <p class="benefit-desc">{{ nextTierInfo.desc || '繼續累積里程數以升級至 ' + nextTierInfo.name }}</p>
          </div>
        </div>
        <div v-else class="benefit-card no-pointer">
          <img src="/go-silver.png" alt="白金卡" class="benefit-img" />
          <div class="benefit-content">
            <h4 class="benefit-title">恭喜！您已達到 <span class="silver-text">白金卡</span> 最高等級</h4>
            <p class="benefit-desc">{{ tierCardDescs.platinum || '您已享有最頂級的會員權益，感謝您的支持。' }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- 彈窗組件 -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <transition name="pop">
        <div class="modal-content">
          <div class="modal-header">
            <h3 class="modal-title" style="text-align:center">升級規則</h3>
            <button class="modal-close" @click="closeModal">✕</button>
          </div>
          <div class="modal-body">
            <div v-if="benefitsHtml" v-html="benefitsHtml" class="benefits-rich-content"></div>
            <div v-else class="modal-empty">目前尚無升級規則說明</div>
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
  overflow: hidden; /* Ensure image fits in circle */
}

.avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-icon { font-size: 2.5rem; }
.user-name { font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem; }
.user-id { font-size: 0.9rem; opacity: 0.8; margin-bottom: 1.5rem; }
.user-balance { 
  margin-bottom: 1.5rem; 
  display: flex;
  align-items: baseline;
  justify-content: center;
}
.currency { font-size: 1.25rem; margin-right: 4px; font-weight: 700; }
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
.stats-container { display: flex; justify-content: space-between; text-align: center; margin-bottom: 2rem; position: relative; }
.stat-box { 
  flex: 1; 
  display: flex; 
  flex-direction: column; 
  padding: 0 1rem 1rem; 
  cursor: pointer; 
  border-bottom: 3px solid transparent; 
  transition: border-color 0.3s;
  justify-content: flex-end; /* Align content to bottom so values align if needed, or use space-between */
}
.stat-box.active {
  border-bottom-color: #E6007E; /* Peach/Pink Highlight */
}
.stat-value { font-size: 3rem; font-weight: 700; color: #000; margin-bottom: 5px; line-height: 1; min-height: 3rem; display: flex; align-items: flex-end; justify-content: center; } /* Add min-height to ensure alignment */
.stat-value.large { font-size: 2rem; color: #000; padding-bottom: 0.4rem; } /* Adjust padding to visual align with larger text baseline if needed */

/* .stat-divider { width: 60%; height: 1px; background-color: #ddd; margin: 0 auto 10px; } */
.stat-divider-vertical {
  width: 1px;
  height: 90%; 
  background-color: #ddd;
  align-self: center; 
  margin: 0 10px; /* Add margin to avoid touching */
}
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
.tier-point.active .point-dot { background-color: #d71921; border-color: #d71921; box-shadow: 0 0 5px rgba(215, 25, 33, 0.5); }
.tier-point.active .point-label { color: #333; font-weight: 700; }
.point-label { font-size: 0.85rem; color: #999; white-space: nowrap; }
.tier-update-hint { font-size: 0.95rem; color: #333; margin-bottom: 2rem; opacity: 0.9; }
.view-benefits-btn { background-color: transparent; border: 1px solid #333; color: #333; padding: 0.75rem 2rem; border-radius: 4px; font-size: 0.9rem; font-weight: 700; cursor: pointer; }

.cards-section { padding: 1.5rem; }
.benefit-cards { display: flex; flex-direction: column; gap: 1.5rem; }
.benefit-card { 
  background-color: #ffffff; 
  border-radius: 12px; 
  overflow: hidden; 
  box-shadow: 0 4px 12px rgba(0,0,0,0.05); 
  transition: transform 0.2s, box-shadow 0.2s;
  cursor: pointer;
}
.benefit-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(0,0,0,0.1);
}
.benefit-card.no-pointer {
  cursor: default;
}
.benefit-card.no-pointer:hover {
  transform: none;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}
.benefit-img { width: 100%; height: 340px; object-fit: cover; }
.benefit-content { padding: 1.25rem; padding-top: 0; }
.benefit-title { font-size: 1.25rem; font-weight: 700; margin-bottom: 0.75rem; }
.benefit-desc { font-size: 0.95rem; color: #666; line-height: 1.4; margin-bottom: 1rem; }
.benefit-link { display: block; font-weight: 700; color: #000; text-decoration: none; font-size: 1rem; text-transform: uppercase; }
.section-hint { font-size: 1rem; font-weight: 700; margin-bottom: 1.25rem; color: #000; }
.comfortable-banner { position: relative; border-radius: 0; overflow: hidden; margin-left: -1.5rem; margin-right: -1.5rem; }
.comfortable-img { width: 100%; display: block; }
.comfortable-text { display: block; margin-top: 1.5rem; margin-bottom: 0.75rem; color: #333; font-size: 1.1rem; font-weight: 700; text-align: left; }
.silver-text { margin-left: 0rem; color: #000; }

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

.modal-header { position: relative; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem; }
.modal-title { font-size: 1.25rem; font-weight: 800; margin: 0; text-align: center; }
.modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #999; position: absolute; right: 0; top: 50%; transform: translateY(-50%); }
.benefits-rich-content { text-align: left; }
.benefits-rich-content :deep(ul) { padding-left: 1.5em; list-style: disc; margin: 0.5rem 0; }
.benefits-rich-content :deep(ol) { padding-left: 1.5em; list-style: decimal; margin: 0.5rem 0; }
.benefits-rich-content :deep(p) { margin: 0 0 0.5rem 0; }
.benefits-rich-content :deep(strong) { font-weight: 700; }
.rule-hint { font-weight: 700; margin-bottom: 1rem; }
.rule-list { padding-left: 1.25rem; margin-bottom: 1.5rem; }
.rule-list li { margin-bottom: 0.75rem; font-size: 0.95rem; line-height: 1.4; }
.rule-note { font-size: 0.8rem; color: #888; border-top: 1px solid #eee; padding-top: 1rem; }
.modal-loading { text-align: center; color: #999; padding: 1.5rem 0; font-size: 0.9rem; }
.modal-empty { text-align: center; color: #bbb; padding: 1.5rem 0; font-size: 0.9rem; }
.modal-confirm-btn {
  width: 100%; background-color: #d71921; color: white; border: none;
  padding: 1rem; border-radius: 4px; font-weight: 700; margin-top: 1.5rem; cursor: pointer;
}

/* 動畫 */
.pop-enter-active, .pop-leave-active { transition: all 0.3s ease; }
.pop-enter-from, .pop-leave-to { opacity: 0; transform: scale(0.9); }
</style>
