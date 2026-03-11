<template>
  <div class="tx-page">
    <div class="tx-header">
      <router-link to="/settings" class="back-btn">
        <span class="arrow-left"></span>
      </router-link>
      <h2 class="header-title">交易紀錄</h2>
      <div class="header-placeholder"></div>
    </div>

    <div class="tx-list">
      <router-link to="/transactions/topup" class="tx-item">
        <div class="tx-icon">💳</div>
        <span class="tx-label">儲值紀錄</span>
        <span class="arrow-right"></span>
      </router-link>
      <router-link to="/transactions/withdrawal" class="tx-item">
        <div class="tx-icon">💸</div>
        <span class="tx-label">提款紀錄</span>
        <span class="arrow-right"></span>
      </router-link>
    </div>
  </div>
</template>

<style scoped>
.tx-page {
  background-color: #f5f5f5;
  min-height: 100vh;
}

.tx-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.5rem;
  background-color: #ffffff;
  border-bottom: 1px solid #eee;
  position: sticky;
  top: 0;
  z-index: 10;
}

.back-btn {
  text-decoration: none;
  color: #333;
  padding: 5px;
  display: flex;
  align-items: center;
}

.arrow-left {
  display: block;
  width: 12px;
  height: 12px;
  border-left: 2px solid #333;
  border-bottom: 2px solid #333;
  transform: rotate(45deg);
}

.header-title {
  font-size: 1.1rem;
  font-weight: 700;
  margin: 0;
}

.header-placeholder {
  width: 22px;
}

.tx-list {
  margin-top: 1rem;
  background-color: #ffffff;
}

.tx-item {
  display: flex;
  align-items: center;
  padding: 1.25rem 1.5rem;
  text-decoration: none;
  color: #333;
  border-bottom: 1px solid #f0f0f0;
  gap: 0.875rem;
}

.tx-icon {
  font-size: 1.5rem;
  width: 36px;
  text-align: center;
}

.tx-label {
  flex: 1;
  font-size: 1rem;
  font-weight: 500;
}

.arrow-right {
  display: block;
  width: 8px;
  height: 8px;
  border-top: 2px solid #ccc;
  border-right: 2px solid #ccc;
  transform: rotate(45deg);
}
</style>
