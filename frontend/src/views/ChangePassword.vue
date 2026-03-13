<template>
  <div class="standalone-page">
    <div class="page-header-nav">
      <router-link to="/profile" class="back-home-btn">
        <span class="arrow-left"></span>
      </router-link>
      <div class="page-logo-small">
        <img src="/logo.png" alt="Logo" />
      </div>
    </div>
    <div class="page-content">
      <h2 class="page-title">修改密碼</h2>
      <div class="form-group">
        <input
          type="password"
          placeholder="請設定6-12位數字密碼"
          class="form-input"
        />
      </div>
      <div class="form-group">
        <input
          type="password"
          placeholder="再次輸入密碼"
          class="form-input"
        />
      </div>
      <button class="submit-btn">送出</button>
    </div>
  </div>
</template>

<style scoped>
.standalone-page {
  background-color: #ffffff;
  min-height: 100vh;
}

.page-header-nav {
  display: flex;
  align-items: center;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #eee;
  background-color: #ffffff;
  position: relative;
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
  border-left: 2px solid #333;
  border-bottom: 2px solid #333;
  transform: rotate(45deg);
}

.page-logo-small {
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
}

.page-logo-small img {
  height: 32px;
}

.page-content {
  padding: 3rem 5rem;
}

.page-title {
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0 0 2rem 0;
  color: #333;
}

.form-group {
  margin-bottom: 1rem;
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
  width: 100%;
  padding: 1rem;
  background-color: #d71921;
  color: #ffffff;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  margin-top: 1.5rem;
  transition: background-color 0.2s;
}

.submit-btn:hover {
  background-color: #b8151b;
}
</style>
