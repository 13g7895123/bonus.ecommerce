<template>
  <div class="standalone-login-page">
    <div class="login-header-nav">
      <router-link to="/login" class="back-home-btn">
        <span class="arrow-left"></span>
      </router-link>
      <div class="login-logo-small">
        <img src="/logo.png" alt="Logo" />
      </div>
    </div>
    <div class="login-page">
      <h2 class="login-title">加入阿聯酋航空 Skywards</h2>
      <p class="login-desc">每次旅行都能享有各種優惠。享受獎勵航班、艙位升等、專屬權益等優惠。</p>
      
      <div class="login-form">
        <div class="form-field">
          <input type="email" placeholder="電子郵件" class="login-input" />
        </div>
        <div class="form-field">
          <input type="text" placeholder="名字" class="login-input" />
        </div>
        <div class="form-field">
          <input type="text" placeholder="姓名" class="login-input" />
          <p class="field-instruction">您必須以英文輸入輸入姓名,且須與護照上顯示的完全相同。</p>
        </div>
        <div class="form-field">
          <input type="password" placeholder="密碼" class="login-input" />
        </div>
        <div class="form-field">
          <input type="text" placeholder="出生日期" class="login-input" />
        </div>
        <div class="form-field">
          <input type="text" placeholder="居住國家/地區" class="login-input" />
        </div>
        <div class="form-row">
          <input type="text" placeholder="國家/地區代碼" class="login-input half" />
          <input type="tel" placeholder="手機號碼" class="login-input half" />
        </div>
        <div class="form-field">
          <input type="text" placeholder="輸入邀請碼(選填)" class="login-input" />
        </div>
        
        <div class="checkbox-group">
          <input type="checkbox" id="terms" />
          <label for="terms">我同意<span class="red-text">網站服務條款</span>及<span class="red-text">隱私政策</span></label>
        </div>
        
        <button class="login-submit-btn">註冊</button>
        
        <div class="footer-note">
          我已有帳號 <router-link to="/login" class="login-link-red">登入</router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.standalone-login-page {
  background-color: #ffffff;
  min-height: 100vh;
}

.login-header-nav {
  display: flex;
  align-items: center;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #eee;
  background-color: #ffffff;
}

.back-home-btn {
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 0.5rem;
  display: flex;
  align-items: center;
  text-decoration: none;
  color: #000;
}

.arrow-left {
  display: block;
  width: 12px;
  height: 12px;
  border-left: 2px solid currentColor;
  border-bottom: 2px solid currentColor;
  transform: rotate(45deg);
}

.login-logo-small {
  flex-grow: 1;
  display: flex;
  justify-content: center;
  padding-right: 40px;
}

.login-logo-small img {
  height: 35px;
}

.login-page {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 3rem 1.5rem;
  max-width: 500px;
  margin: 0 auto;
}

.login-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #333;
  margin-bottom: 1rem;
}

.login-desc {
  font-size: 1rem;
  color: #666;
  line-height: 1.6;
  margin-bottom: 2rem;
}

.login-form {
  width: 100%;
}

.login-input {
  width: 100%;
  padding: 1rem;
  margin-bottom: 1rem;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 1rem;
  box-sizing: border-box;
}

.form-field {
  width: 100%;
  margin-bottom: 1.25rem;
}

.field-instruction {
  font-size: 0.85rem;
  color: #666;
  text-align: left;
  margin-top: 0.4rem;
  line-height: 1.4;
}

.form-row {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.25rem;
}

.login-input.half {
  flex: 1;
}

.checkbox-group {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  text-align: left;
  margin-bottom: 2rem;
  font-size: 0.95rem;
  color: #333;
}

.checkbox-group input {
  margin-top: 0.25rem;
  width: 18px;
  height: 18px;
}

.red-text {
  color: #d71921;
  cursor: pointer;
}

.login-submit-btn {
  width: 100%;
  background-color: #d71921;
  color: white;
  border: none;
  padding: 1.1rem;
  font-size: 1.1rem;
  font-weight: 700;
  border-radius: 4px;
  cursor: pointer;
}

.footer-note {
  margin-top: 2rem;
  font-size: 1rem;
  color: #333;
  font-weight: 500;
}

.login-link-red {
  color: #d71921;
  text-decoration: underline;
  font-weight: 700;
  margin-left: 0.5rem;
}
</style>
