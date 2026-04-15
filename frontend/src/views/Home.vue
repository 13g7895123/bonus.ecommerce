<script setup>
import { ref, onMounted } from 'vue'

const isLoggedIn = ref(false)
const announcements = ref([])

onMounted(async () => {
  const token = localStorage.getItem('token')
  isLoggedIn.value = !!token

  try {
    const res  = await fetch('/api/v1/announcements?limit=10')
    if (res.ok) {
      const json = await res.json()
      const items = json.data?.items || json.data || []
      announcements.value = items.map(a => ({
        id:    a.id,
        date:  (a.published_at || '').substring(0, 10),
        title: a.title,
      }))
    }
  } catch {}
})
</script>

<template>
  <main v-if="!isLoggedIn" class="hero-section">
    <div class="hero-content">
      <img src="/coin.png" alt="Coin" class="coin-image" />
      <h2 class="hero-subtitle">加入阿聯酋航空 Skywards</h2>
      <h1 class="hero-description">成為阿聯酋航空 Skywards 會員，即可享有航班獎勵、升等及其他福利</h1>
      <router-link to="/register" class="cta-button">立即加入</router-link>
    </div>
  </main>

  <section class="upgrade-section">
    <div class="upgrade-content">
      <h2 class="upgrade-subtitle">SKYWARDS+</h2>
      <h1 class="upgrade-title">利用 Skywards+ 升級您的權益</h1>
      <p class="upgrade-description">隨心所好，從您最喜愛的三款套裝行程中選擇，從機場貴賓休息室使用權限和額外行李限額，到專屬「現金+哩程數」票價與折扣，為您提供精彩可期的服務內容。</p>
      <router-link to="/skywards" class="upgrade-button">更多資訊</router-link>
    </div>
  </section>

  <section class="cabins-section">
    <img src="/home/dubai.png" alt="Dubai" class="full-width-image" />
    <img src="/home/first.png" alt="First Class" class="full-width-image" />
    <img src="/home/business.png" alt="Business Class" class="full-width-image" />
    <img src="/home/rich-classic.png" alt="Premium Economy" class="full-width-image" />
    <img src="/home/classic.png" alt="Economy" class="full-width-image" />
  </section>

  <section class="about-section">
    <div class="about-container">
      <div class="about-header">
        <h2 class="about-title">關於我們</h2>
        <!-- <div class="title-underline"></div> -->
        <p class="about-subtitle">瞭解更多關於我們的歷史沿革、企業和永續發展倡議的資訊</p>
      </div>
      <div class="about-circles">
        <div class="circle-img-wrap">
          <img src="/plane.png" alt="歷史沿革" class="circle-img" />
        </div>
        <div class="circle-img-wrap">
          <img src="/mountain.png" alt="企業文化" class="circle-img" />
        </div>
        <div class="circle-img-wrap">
          <img src="/people.png" alt="永續發展" class="circle-img" />
        </div>
        <div class="circle-img-wrap">
          <img src="/together.png" alt="社會責任" class="circle-img" />
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.hero-section {
  position: relative;
  width: 100%;
  height: calc(100vh - 30vh);
  background-image: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.8)), url('/join-background.png');
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
  width: 24vw;
  margin-bottom: 0.1rem;
}

.hero-subtitle {
  font-family: 'Playfair Display', serif;
  font-size: var(--fs-hero-subtitle);
  font-weight: 800;
  margin-bottom: 0.75rem;
}

.hero-description {
  font-family: 'Playfair Display', serif;
  font-size: var(--fs-hero-description);
  font-weight: 700;
  margin-bottom: 2.5rem;
  line-height: 1.3;
  max-width: var(--app-max-width);
}

.cta-button {
  background-color: #ffffff;
  color: #000000;
  border: none;
  padding: 0.5rem 7rem;
  font-size: var(--fs-hero-button);
  font-weight: 900;
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
  height: 84vh;
  background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('/upgrade-bg.png');
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
  padding: 0 5rem;
  width: 100%;
  max-width: var(--app-max-width);
}

.upgrade-subtitle {
  font-family: 'Playfair Display', serif;
  font-size: var(--fs-upgrade-label);
  font-weight: 500;
  margin-bottom: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.upgrade-title {
  font-family: 'Playfair Display', serif;
  font-size: var(--fs-upgrade-title);
  font-weight: 700;
  margin-bottom: 1.5rem;
  line-height: 1.2;
}

.upgrade-description {
  font-size: var(--fs-upgrade-body);
  font-weight: 800;
  margin-bottom: 2.5rem;
  line-height: 1.4;
  opacity: 0.9;
  text-align: justify;
  text-align-last: center;
  text-justify: inter-word;
  /* width: 100%; */
}

.upgrade-button {
  display: block;
  background-color: white;
  color: black;
  border: 2px solid white;
  font-size: var(--fs-upgrade-button);
  font-weight: 800;
  border-radius: 0;
  cursor: pointer;
  transition: all 0.3s ease;
  width: 100%;
  text-decoration: none;
  text-align: center;
  padding: 0.6rem 0;
  box-sizing: border-box;
}

.upgrade-button:hover {
  background-color: white;
  color: black;
}

.cabins-section {
  width: 100%;
  background-color: #ffffff;
}

.full-width-image {
  width: 100%;
  display: block;
}

.about-section {
  background-color: #ffffff;
  padding: 5rem 1.5rem 14rem;
  color: #111111;
}

.about-container {
  max-width: var(--app-max-width);
  margin: 0 auto;
}

.about-header {
  text-align: center;
  margin-bottom: 3rem;
}

.about-title {
  font-size: var(--fs-about-title);
  font-weight: 700;
  margin-bottom: 1rem;
  color: #111111;
}

.title-underline {
  width: 60px;
  height: 4px;
  background-color: #d71921;
  margin: 0 auto 1.5rem;
}

.about-subtitle {
  font-size: var(--fs-about-body);
  font-weight: 800;
  color: #555555;
  margin: 0;
  line-height: 1.6;
}

.about-circles {
  display: grid;
  grid-template-columns: repeat(2, 30%);
  gap: 5rem;
  justify-content: center;
}

.circle-img-wrap {
  width: 100%;
  aspect-ratio: 1 / 1;
  border-radius: 50%;
  overflow: hidden;
}

.circle-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* ================================================
 * 手機版配置（≤ 767px，涵蓋所有手機裝置）
 * ================================================ */
@media (max-width: 767px) {
  .hero-section {
    height: auto;
    min-height: 55vh;
    padding: 3rem 0;
  }

  .coin-image {
    width: 45vw;
  }

  .cta-button {
    padding: 0.5rem 3rem;
  }

  .upgrade-section {
    height: auto;
    min-height: 55vh;
    padding: 3rem 0;
  }

  .upgrade-content {
    padding: 0 1.5rem;
  }

  .about-section {
    padding: 3rem 1.5rem 6rem;
  }

  .about-circles {
    grid-template-columns: repeat(2, 40%);
    gap: 2.5rem;
  }
}
</style>
