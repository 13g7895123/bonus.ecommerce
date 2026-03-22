<template>
  <div class="ml-page">
    <PageHeader title="里程回饋" back-to="/settings" :bordered="false" />

    <div class="rewards-content">
      <div class="time-display">{{ currentTime }}</div>
      <div class="divider"></div>

      <div class="product-list">
        <!-- Product 1 -->
        <div class="product-item">
          <div class="product-image">
            <img src="/product-1.png" alt="Product 1" />
          </div>
          <div class="product-info">
            <div class="info-row name">APIVITA艾蜜塔仙人掌滋潤再生面膜(8mlX12)</div>
            <div class="info-row gray">積分回饋10%($188)</div>
            <div class="info-row gray">$1880</div>
            <div class="info-row gray">數量 : 1</div>
          </div>
        </div>

        <!-- Product 2 -->
        <div class="product-item">
          <div class="product-image">
            <img src="/product-3.png" alt="Product 2" />
          </div>
          <div class="product-info">
            <div class="info-row name">APIVITA艾蜜塔仙人掌滋潤再生面膜(8mlX12)</div>
            <div class="info-row gray">積分回饋10%($188)</div>
            <div class="info-row gray">$1880</div>
            <div class="info-row gray">數量 : 2</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import PageHeader from '../components/PageHeader.vue'

const currentTime = ref('')
let timer = null

const updateTime = () => {
  const now = new Date()
  const year = now.getFullYear()
  const month = String(now.getMonth() + 1).padStart(2, '0')
  const day = String(now.getDate()).padStart(2, '0')
  const hours = String(now.getHours()).padStart(2, '0')
  const minutes = String(now.getMinutes()).padStart(2, '0')
  const seconds = String(now.getSeconds()).padStart(2, '0')
  
  currentTime.value = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`
}

onMounted(() => {
  updateTime()
  timer = setInterval(updateTime, 1000)
})

onUnmounted(() => {
  if (timer) clearInterval(timer)
})
</script>

<style scoped>
.ml-page {
  background-color: #ffffff; /* White background */
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.rewards-content {
  flex: 1;
  padding: 1rem;
}

.time-display {
  text-align: left;
  font-size: 1rem;
  margin-bottom: 0.5rem;
  color: #333;
  padding-left: 0.5rem;
  font-family: 'Avram Sans', sans-serif;
}

.divider {
  border-bottom: 2px solid #666; /* Dark gray line */
  width: 100%;
  margin: 0 auto 1.5rem;
}

.product-list {
  display: grid;
  grid-template-columns: repeat(2, 1fr); /* Two items per row */
  gap: 1rem;
}

.product-item {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  border: 1px solid #ccc; /* Gray border */
  border-radius: 8px; /* Rounded corners */
  padding: 1rem 0.5rem;
  overflow: hidden;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
}

.product-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.product-image img {
  width: 100%;
  height: auto;
  object-fit: contain;
  border-radius: 4px;
  display: block;
  margin: 0 auto;
}

.product-info {
  width: 100%;
  text-align: left;
  padding: 0 0.5rem;
}

.info-row {
  margin-bottom: 0.4rem;
  line-height: 1.4;
}

.info-row.name {
  color: #000;
  font-weight: bold;
  font-size: 1.1rem;
}

.info-row.gray {
  color: #555; /* Dark gray */
  font-size: 0.95rem;
}
</style>
