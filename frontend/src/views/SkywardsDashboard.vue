<script setup>
import { ref } from 'vue'

const activeTab = ref('miles') // 'miles' or 'tier'
</script>

<template>
  <div class="skywards-page">
    <!-- Header 區塊 -->
    <header class="skywards-header">
      <div class="user-profile">
        <div class="avatar-circle">
          <span class="avatar-icon">👤</span>
        </div>
        <h2 class="user-name">Admin User</h2>
        <p class="user-id">ID: admin@emirates.com</p>
        <div class="user-balance">
          <span class="currency">$</span>
          <span class="amount">25,400</span>
        </div>
        <button class="details-btn">我的明細表</button>
      </div>
    </header>

    <!-- 狀態切換與等級區塊 -->
    <section class="membership-section">
      <div class="toggle-container">
        <button 
          class="toggle-btn" 
          :class="{ active: activeTab === 'miles' }"
          @click="activeTab = 'miles'"
        >會員里程數</button>
        <button 
          class="toggle-btn" 
          :class="{ active: activeTab === 'tier' }"
          @click="activeTab = 'tier'"
        >等級</button>
      </div>

      <!-- 會員里程數內容 -->
      <div v-if="activeTab === 'miles'" class="stats-container">
        <div class="stat-box">
          <span class="stat-value">25,400</span>
          <div class="stat-divider"></div>
          <p class="stat-label">Skywards會員里程數</p>
        </div>
        <div class="stat-box">
          <span class="stat-value large">藍卡</span>
          <p class="stat-label small">等級</p>
        </div>
      </div>

      <!-- 等級內容 -->
      <div v-else class="tier-content">
        <div class="tier-progress-container">
          <!-- 等級進度條 -->
          <div class="tier-rail">
            <div class="tier-line active"></div> <!-- 藍卡起始是紅色的基礎 -->
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
        <button class="view-benefits-btn">檢視您的權益</button>
      </div>
    </section>

    <!-- 會員里程數下方的卡片 (僅在 activeTab === 'miles' 顯示或根據需求顯示) -->
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

    <!-- 等級下方的卡片 -->
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

  </div>
</template>

<style scoped>
.skywards-page {
  background-color: #f5f5f5;
  min-height: 100vh;
  padding-bottom: 80px;
  color: #333;
}

/* Header 樣式 */
.skywards-header {
  background-image: linear-gradient(rgba(0, 32, 91, 0.85), rgba(0, 32, 91, 0.85)), url('/join-background.png');
  background-size: cover;
  background-position: center;
  padding: 3rem 1.5rem 2rem;
  color: white;
  text-align: center;
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

.avatar-icon {
  font-size: 2.5rem;
}

.user-name {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 0.25rem;
}

.user-id {
  font-size: 0.9rem;
  opacity: 0.8;
  margin-bottom: 1.5rem;
}

.user-balance {
  margin-bottom: 1.5rem;
}

.currency {
  font-size: 1.25rem;
  vertical-align: top;
  margin-right: 4px;
}

.amount {
  font-size: 2.5rem;
  font-weight: 700;
}

.details-btn {
  background: transparent;
  border: 1px solid white;
  color: white;
  padding: 0.75rem 1.5rem;
  border-radius: 4px;
  font-size: 0.9rem;
  cursor: pointer;
  display: block;
  margin: 0 auto;
}

/* 狀態切換與統計 */
.membership-section {
  background-color: #ffffff;
  padding: 1.5rem;
  margin-bottom: 1rem;
}

.toggle-container {
  display: flex;
  background-color: #f0f0f0;
  border-radius: 30px;
  padding: 4px;
  margin-bottom: 2rem;
}

.toggle-btn {
  flex: 1;
  padding: 0.75rem;
  border: none;
  background: transparent;
  border-radius: 25px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.toggle-btn.active {
  background-color: #ffffff;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.stats-container {
  display: flex;
  justify-content: space-between;
  text-align: center;
}

.stat-box {
  flex: 1;
  display: flex;
  flex-direction: column;
  padding: 0 1rem;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #000;
  margin-bottom: 10px;
}

.stat-value.large {
  font-size: 1.75rem;
  color: #00205B;
}

.stat-divider {
  width: 60%;
  height: 1px;
  background-color: #ddd;
  margin: 0 auto 10px;
}

.stat-label {
  font-size: 0.85rem;
  color: #666;
}

.stat-label.small {
  font-size: 0.75rem;
}

/* 等級切換內容樣式 */
.tier-content {
  text-align: center;
  padding: 1rem 0;
}

.tier-progress-container {
  position: relative;
  margin: 3rem 1rem 4rem;
}

.tier-rail {
  position: absolute;
  top: 10px;
  left: 10%;
  right: 10%;
  height: 2px;
  background-color: #ddd;
  display: flex;
  z-index: 1;
}

.tier-line {
  flex: 1;
  height: 100%;
}

.tier-line.active {
  background-color: #d71921;
}

.tier-points {
  display: flex;
  justify-content: space-between;
  position: relative;
  z-index: 2;
}

.tier-point {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 60px;
}

.point-dot {
  width: 20px;
  height: 20px;
  background-color: white;
  border: 2px solid #ddd;
  border-radius: 50%;
  margin-bottom: 15px;
}

.tier-point.active .point-dot {
  background-color: #d71921;
  border-color: #d71921;
}

.tier-point.active .point-label {
  color: #333;
  font-weight: 700;
}

.point-label {
  font-size: 0.85rem;
  color: #999;
  white-space: nowrap;
}

.tier-update-hint {
  font-size: 0.95rem;
  color: #333;
  margin-bottom: 2rem;
  opacity: 0.9;
}

.view-benefits-btn {
  background-color: transparent;
  border: 1px solid #333;
  color: #333;
  padding: 0.75rem 2rem;
  border-radius: 4px;
  font-size: 0.9rem;
  font-weight: 700;
  cursor: pointer;
}

.silver-text {
  margin-left: 0.5rem;
  color: #000;
}

/* 卡片區域 */
.cards-section {
  padding: 1.5rem;
}

.benefit-cards {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.benefit-card {
  background-color: #ffffff;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.benefit-img {
  width: 100%;
  height: 140px;
  object-fit: cover;
}

.benefit-content {
  padding: 1.25rem;
}

.benefit-title {
  font-size: 1.25rem;
  font-weight: 700;
  margin-bottom: 0.75rem;
}

.benefit-desc {
  font-size: 0.95rem;
  color: #666;
  line-height: 1.4;
  margin-bottom: 1rem;
}

.benefit-link {
  display: block;
  font-weight: 700;
  color: #000;
  text-decoration: none;
  font-size: 1rem;
  text-transform: uppercase;
}

.section-hint {
  font-size: 1rem;
  font-weight: 700;
  margin-bottom: 1.25rem;
  color: #000;
}

.skywards-footer-img {
  width: 100%;
}

.full-width-image {
  width: 100%;
  display: block;
}
</style>
