<script setup>
import { ref } from 'vue'

const announcements = ref([
  { id: 1, date: '2024-03-09', title: '阿聯酋航空最新航班資訊公告' },
  { id: 2, date: '2024-03-08', title: 'Skywards 集哩程計畫更新提醒' },
  { id: 3, date: '2024-03-05', title: '全球機場貴賓室服務調整通知' },
])
</script>

<template>
  <main class="hero-section">
    <div class="hero-content">
      <img src="/coin.png" alt="Coin" class="coin-image" />
      <h2 class="hero-subtitle">加入阿聯酋航空 Skywards</h2>
      <h1 class="hero-description">成為阿聯酋航空 Skywards 會員，即可享有航班獎勵、升等及其他福利</h1>
      <router-link to="/register" class="cta-button">立即加入</router-link>
    </div>
  </main>

  <section class="upgrade-section">
    <div class="upgrade-content">
      <h2 class="upgrade-subtitle">升等至商務艙</h2>
      <h1 class="upgrade-title">使用您的哩程，享受更舒適的旅程</h1>
      <p class="upgrade-description">只需 8,000 哩起，即可升等至商務艙，享受獲獎肯定的服務、美味餐飲及更多禮遇。</p>
      <button class="upgrade-button">立即升等</button>
    </div>
  </section>

  <section class="cabins-section">
    <img src="/cabins.png" alt="Cabins" class="full-width-image" />
  </section>

  <section class="about-section">
    <div class="about-container">
      <div class="about-header">
        <h2 class="about-title">關於我們</h2>
        <div class="title-underline"></div>
      </div>
      <div class="about-grid">
        <div class="about-item">
          <h3 class="item-title">我們的故事</h3>
          <p class="item-text">自 1985 年成立以來，阿聯酋航空已從只有兩架飛機的小型航空公司發展成為全球領先的航空品牌之一。我們以杜拜為基地，連接全球超過 150 個目的地。</p>
        </div>
        <div class="about-item">
          <h3 class="item-title">卓越服務</h3>
          <p class="item-text">我們因其卓越的產品 and 服務而獲得無數獎項。從機上廚師烹製的精緻美食到獲獎肯定的 ice 影音娛樂系統，我們致力於為每位乘客提供難忘的旅程。</p>
        </div>
        <div class="about-item">
          <h3 class="item-title">環境責任</h3>
          <p class="item-text">我們致力於減少營運對環境的影響。我們投資於最先進、燃油效率最高的飛機，並努力在整個供應鏈中實施可持續發展的做法。</p>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.hero-section {
  position: relative;
  width: 100%;
  height: calc(100vh - 82px);
  background-image: url('/join-background.png');
  background-size: cover;
  background-position: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: white;
  text-align: center;
}

.hero-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0 1.5rem;
}

.coin-image {
  width: 180px;
  margin-bottom: 2rem;
}

.hero-subtitle {
  font-size: 1.75rem;
  font-weight: 500;
  margin-bottom: 0.75rem;
}

.hero-description {
  font-size: 2.25rem;
  font-weight: 700;
  margin-bottom: 2.5rem;
  line-height: 1.3;
  max-width: 800px;
}

.cta-button {
  background-color: #ffffff;
  color: #000000;
  border: none;
  padding: 1.25rem 3rem;
  font-size: 1.2rem;
  font-weight: 700;
  border-radius: 0;
  cursor: pointer;
  text-decoration: none;
  transition: opacity 0.2s;
}

.cta-button:hover {
  opacity: 0.9;
}

.upgrade-section {
  position: relative;
  width: 100%;
  height: 100vh;
  background-image: url('/upgrade-bg.png');
  background-size: cover;
  background-position: center;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  text-align: center;
}

.upgrade-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0 1.5rem;
  max-width: 850px;
}

.upgrade-subtitle {
  font-size: 1.5rem;
  font-weight: 500;
  margin-bottom: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.upgrade-title {
  font-size: 3rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
  line-height: 1.2;
}

.upgrade-description {
  font-size: 1.25rem;
  font-weight: 400;
  margin-bottom: 2.5rem;
  line-height: 1.6;
  opacity: 0.9;
}

.upgrade-button {
  background-color: transparent;
  color: white;
  border: 2px solid white;
  padding: 1rem 3rem;
  font-size: 1.1rem;
  font-weight: 600;
  border-radius: 0;
  cursor: pointer;
  transition: all 0.3s ease;
}

.upgrade-button:hover {
  background-color: white;
  color: black;
}

.cabins-section {
  width: 100%;
}

.full-width-image {
  width: 100%;
  display: block;
}

.about-section {
  background-color: #000000;
  padding: 5rem 1.5rem;
  color: white;
}

.about-container {
  max-width: 1000px;
  margin: 0 auto;
}

.about-header {
  text-align: center;
  margin-bottom: 4rem;
}

.about-title {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 1rem;
}

.title-underline {
  width: 60px;
  height: 4px;
  background-color: #d71921;
  margin: 0 auto;
}

.about-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 3rem;
}

.about-item {
  text-align: center;
}

.item-title {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 1.25rem;
  color: #ffffff;
}

.item-text {
  font-size: 1rem;
  line-height: 1.7;
  color: #cccccc;
}
</style>
