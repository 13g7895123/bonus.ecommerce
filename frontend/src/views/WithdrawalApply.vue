<template>
  <div class="wa-page">
    <div class="wa-header">
      <router-link to="/withdrawal/setup" class="back-btn">
        <span class="arrow-left"></span>
      </router-link>
      <h2 class="header-title">提款申請</h2>
      <div class="header-placeholder"></div>
    </div>

    <div class="wa-content">
      <!-- 可提款現金 -->
      <div class="info-section">
        <p class="info-label">可提款現金</p>
        <p class="info-value cash-value">$236,802</p>
      </div>

      <div class="divider"></div>

      <!-- 銀行存摺 -->
      <div class="info-section">
        <p class="info-label">銀行存摺</p>
        <p class="info-value account-value">XXXXXXXXXXX</p>
      </div>

      <div class="divider"></div>

      <!-- 提款現金 -->
      <div class="info-section">
        <p class="info-label">提款現金</p>
        <p class="info-hint">單次提款金額為1000元，最大可提款100000000元</p>
      </div>

      <!-- 輸入框 -->
      <div class="form-group">
        <input type="number" placeholder="請輸入提款金額" class="wa-input" />
      </div>
      <div class="form-group">
        <input type="password" placeholder="請輸入提款密碼" class="wa-input" />
      </div>

      <!-- 提款申請按鈕 -->
      <button class="apply-btn">提款申請</button>
    </div>
  </div>
</template>

<style scoped>
.wa-page {
  background-color: #ffffff;
  min-height: 100vh;
}

.wa-header {
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

.wa-content {
  padding: 1.5rem;
}

/* 資訊區塊 */
.info-section {
  padding: 0.75rem 0;
}

.info-label {
  font-size: 0.8rem;
  color: #999;
  margin: 0 0 0.35rem 0;
}

.info-value {
  font-size: 1.25rem;
  font-weight: 700;
  margin: 0;
  color: #333;
}

.cash-value {
  color: #d71921;
  font-size: 1.5rem;
}

.account-value {
  font-size: 1rem;
  letter-spacing: 0.05em;
}

.info-hint {
  font-size: 0.8rem;
  color: #999;
  margin: 0;
  line-height: 1.5;
}

.divider {
  height: 1px;
  background-color: #f0f0f0;
  margin: 0.25rem 0;
}

/* 輸入框 */
.form-group {
  margin-bottom: 1rem;
  margin-top: 0.75rem;
}

.wa-input {
  width: 100%;
  padding: 0.875rem 1rem;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  font-size: 0.95rem;
  color: #333;
  background-color: #f9f9f9;
  box-sizing: border-box;
  outline: none;
  transition: border-color 0.2s;
}

.wa-input:focus {
  border-color: #d71921;
  background-color: #ffffff;
}

.wa-input::placeholder {
  color: #aaa;
}

/* 提款申請按鈕 */
.apply-btn {
  width: 100%;
  padding: 1rem;
  background-color: #d71921;
  color: #ffffff;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  margin-top: 1rem;
  transition: background-color 0.2s;
}

.apply-btn:hover {
  background-color: #b8151b;
}
</style>
