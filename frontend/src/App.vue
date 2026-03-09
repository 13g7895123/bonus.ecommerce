<script setup>
</script>

<template>
  <div class="app-container">
    <nav class="navbar">
      <div class="logo">
        <a href="/">
          <img src="/logo.png" alt="Logo" class="logo-image" />
        </a>
      </div>
      <div class="menu-button">
        <button class="hamburger" aria-label="Menu">
          <span class="icon"></span>
        </button>
      </div>
    </nav>
    <main class="hero-section">
      <div class="hero-content">
        <img src="/coin.png" alt="Coin" class="coin-image" />
        <h2 class="hero-subtitle">加入阿聯酋航空 Skywards</h2>
        <h1 class="hero-description">成為阿聯酋航空 Skywards 會員，即可享有航班獎勵、升等及其他福利</h1>
        <button class="cta-button">立即加入</button>
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
  </div>
</template>

<style scoped>
.app-container {
  min-height: 100vh;
  background-color: #000000;
  margin: 0;
  padding: 0;
}

.navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  background-color: #000000;
  position: relative;
  z-index: 10;
}

.logo-image {
  height: 50px;
  display: block;
}

.logo a {
  display: flex;
  align-items: center;
  text-decoration: none;
}

.menu-button {
  display: flex;
  align-items: center;
  justify-content: center;
}

.hamburger {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background-color: #f0f0f0;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
  transition: background-color 0.2s;
}

.hamburger:hover {
  background-color: #e5e5e5;
}

.icon {
  position: relative;
  display: block;
  width: 24px;
  height: 2px;
  background-color: #333;
}

.icon::before,
.icon::after {
  content: '';
  position: absolute;
  width: 24px;
  height: 2px;
  background-color: #333;
  left: 0;
}

.icon::before {
  top: -8px;
}

.icon::after {
  bottom: -8px;
}

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
  color: #000000;
}
</style>
