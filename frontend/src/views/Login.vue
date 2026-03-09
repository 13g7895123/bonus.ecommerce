<template>
  <div class="standalone-login-page">
    <div class="login-header-nav">
      <router-link to="/" class="back-home-btn">
        <span class="arrow-left"></span>
      </router-link>
      <div class="login-logo-small">
        <img src="/logo.png" alt="Logo" />
      </div>
    </div>
    
    <div class="login-page">
      <h2 class="login-title">登錄阿聯酋航空</h2>
      <p class="login-desc">每次跟我們或合作夥伴聯乘都能賺取哩程數。還能使用 Skywards 會員哩程數換取各種獎勵。</p>
      
      <div class="login-form">
        <input type="email" placeholder="電子郵件" class="login-input" />
        <div class="password-group">
          <input type="password" placeholder="密碼" class="login-input" />
          <router-link to="/forgot-password" class="forgot-password">忘記您的密碼了嗎?</router-link>
        </div>
        
        <button class="login-submit-btn">登錄</button>
        
        <hr class="login-divider" />
        
        <div class="join-now-group">
          <p class="join-label">還不是會員?</p>
          <router-link to="/register" class="join-now-btn">現在加入</router-link>
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

.password-group {
  text-align: left;
  margin-bottom: 2rem;
}

.forgot-password {
  color: #d71921;
  font-size: 0.9rem;
  text-decoration: none;
  font-weight: 700;
  display: inline-block;
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

.login-divider {
  border: 0;
  border-top: 1px solid #ddd;
  margin: 2.5rem 0;
}

.join-now-group {
  text-align: left;
}

.join-label {
  font-size: 0.9rem;
  color: #333;
  margin-bottom: 0.5rem;
  font-weight: 600;
}

.join-now-btn {
  width: 100%;
  background-color: #ffffff;
  color: #000000;
  border: 1px solid #000000;
  padding: 1.1rem;
  font-size: 1.1rem;
  font-weight: 700;
  border-radius: 4px;
  cursor: pointer;
  display: block;
  text-align: center;
  text-decoration: none;
}
</style>
