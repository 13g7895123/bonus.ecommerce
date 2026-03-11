<template>
  <div class="wsp-page">
    <div class="wsp-header">
      <router-link to="/transactions" class="back-btn">
        <span class="arrow-left"></span>
      </router-link>
      <h2 class="header-title">提款申請</h2>
      <div class="header-placeholder"></div>
    </div>

    <div class="wsp-content">
      <p class="wsp-desc">為保護您的帳戶安全，請先設定提款密碼</p>

      <div class="form-group">
        <label class="form-label">設定提款密碼</label>
        <input type="password" placeholder="請設定6-12位數字密碼" class="form-input" />
      </div>
      <div class="form-group">
        <label class="form-label">確認提款密碼</label>
        <input type="password" placeholder="再次輸入密碼" class="form-input" />
      </div>

      <router-link to="/withdrawal/setup" class="submit-btn">確認</router-link>
    </div>
  </div>
</template>

<style scoped>
.wsp-page {
  background-color: #ffffff;
  min-height: 100vh;
}

.wsp-header {
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

.wsp-content {
  padding: 2rem 1.5rem;
}

.wsp-desc {
  font-size: 0.875rem;
  color: #999;
  text-align: center;
  margin-bottom: 2rem;
}

.form-group {
  margin-bottom: 1.25rem;
}

.form-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 0.4rem;
}

.form-input {
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

.form-input:focus {
  border-color: #d71921;
  background-color: #ffffff;
}

.form-input::placeholder {
  color: #aaa;
}

.submit-btn {
  display: block;
  width: 100%;
  padding: 1rem;
  background-color: #d71921;
  color: #ffffff;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  margin-top: 2rem;
  text-align: center;
  text-decoration: none;
  box-sizing: border-box;
  transition: background-color 0.2s;
}

.submit-btn:hover {
  background-color: #b8151b;
}
</style>
