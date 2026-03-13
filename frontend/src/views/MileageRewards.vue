<template>
  <div class="ml-page">
    <PageHeader title="里程回饋" back-to="/settings">
      <template #right>
        <button class="cart-btn">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="9" cy="21" r="1"></circle>
            <circle cx="20" cy="21" r="1"></circle>
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
          </svg>
        </button>
      </template>
    </PageHeader>

    <div class="product-grid">
      <div class="product-card" v-for="product in products" :key="product.id">
        <div class="product-img-wrapper">
          <img :src="product.image" :alt="product.name" class="product-img" />
        </div>
        <p class="product-name">{{ product.name }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import PageHeader from '../components/PageHeader.vue'

const products = [
  { id: 1, name: '頭等艙升等券', image: '/upgrade-bg.png' },
  { id: 2, name: '機場貴賓室一日票', image: '/upgrade-bg.png' },
  { id: 3, name: '行李加購20KG', image: '/upgrade-bg.png' },
  { id: 4, name: '飛機餐升級套餐', image: '/upgrade-bg.png' },
  { id: 5, name: '優先登機服務', image: '/upgrade-bg.png' },
  { id: 6, name: '里程兌換折扣券', image: '/upgrade-bg.png' },
]
</script>

<style scoped>
.ml-page {
  background-color: #f5f5f5;
  min-height: 100vh;
}

.cart-btn {
  background: none;
  border: none;
  font-size: 1.4rem;
  cursor: pointer;
  padding: 0;
  line-height: 1;
}

.product-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
  padding: 1rem;
}

.product-card {
  background-color: #ffffff;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.product-img-wrapper {
  width: 100%;
  aspect-ratio: 4 / 3;
  overflow: hidden;
  background-color: #f0f0f0;
}

.product-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.product-name {
  font-size: 0.875rem;
  font-weight: 600;
  color: #333;
  padding: 0.65rem 0.75rem;
  margin: 0;
}
</style>
