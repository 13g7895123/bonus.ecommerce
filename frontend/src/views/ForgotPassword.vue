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
      <h2 class="login-title">忘記密碼</h2>
      <div class="login-form">
        <input type="tel" placeholder="請輸入您的電話號碼" class="login-input" />
        <p class="forgot-instructions">我們會向您發送訊息，以設定或重設您的新密碼</p>
        <button class="login-submit-btn">提交</button>
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

.forgot-instructions {
  font-size: 0.95rem;
  color: #666;
  text-align: left;
  margin-bottom: 1.5rem;
  line-height: 1.4;
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
</style>
