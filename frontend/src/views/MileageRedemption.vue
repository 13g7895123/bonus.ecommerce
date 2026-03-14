<template>
  <div class="mileage-rewards-page">
    <PageHeader title="里程數兌換" back-to="/skywards" />
    <div class="content-container">
      <div class="reward-section">
        <h3>精選兌換</h3>
        <p>目前沒有可用的兌換項目。</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import PageHeader from '../components/PageHeader.vue'
</script>

<style scoped>
.mileage-rewards-page {
  background-color: #f5f5f5;
  min-height: 100vh;
  color: #333;
}

.content-container {
  padding: 1.5rem;
}

.reward-section {
  background-color: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

h3 {
  margin-top: 0;
  margin-bottom: 1rem;
  font-weight: 700;
}
</style>
