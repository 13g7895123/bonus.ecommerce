<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useApi } from '../composables/useApi'
import AppInput from '../components/AppInput.vue'
import AuthHeader from '../components/AuthHeader.vue'
import DebugFillButton from '../components/DebugFillButton.vue'

const router = useRouter()
const api = useApi()

const email = ref('')
const password = ref('')
const loading = ref(false)
const errorMessage = ref('')

const fillRandomData = () => {
  email.value = 'admin@example.com'
  password.value = 'password'
}

const handleLogin = async () => {
  if (!email.value || !password.value) {
    errorMessage.value = '請輸入電子郵件和密碼'
    return
  }

  loading.value = true
  errorMessage.value = ''

  try {
    const response = await api.auth.login({
      email: email.value,
      password: password.value
    })
    console.log('Login success:', response)
    // 登入成功，導向首頁
    router.push('/')
  } catch (error) {
    console.error('Login failed:', error)
    const errObj = error.response?.data || error
    errorMessage.value = errObj.message || '登入失敗，請稍後再試'
  } finally {
    loading.value = false
  }
}
</script>

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
      <AuthHeader title="登錄阿聯酋航空" description="每次跟我們或合作夥伴聯乘都能賺取哩程數。還能使用 Skywards 會員哩程數換取各種獎勵。" />
      
      <div class="login-form">
        <AppInput v-model="email" type="email" placeholder="電子郵件" />
        <div class="password-group">
          <AppInput v-model="password" type="password" placeholder="密碼" />
          <router-link to="/forgot-password" class="forgot-password">忘記您的密碼了嗎?</router-link>
        </div>
        
        <p v-if="errorMessage" class="error-msg">{{ errorMessage }}</p>

        <button class="login-submit-btn" :disabled="loading" @click="handleLogin">
          {{ loading ? '登入中...' : '登入' }}
        </button>
        
        <hr class="login-divider" />
        
        <div class="join-now-group">
          <p class="join-label">還不是會員?</p>
          <router-link to="/register" class="join-now-btn">現在加入</router-link>
        </div>
        <DebugFillButton @fill="fillRandomData" />
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
  padding: 3rem 5rem;
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

.password-group input {
  margin-bottom: 0.5rem;
}

.error-msg {
  color: #d71921;
  font-weight: 700;
  margin-bottom: 1rem;
  font-size: 0.9rem;
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

.login-submit-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
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
  border: 1px solid #a8a8a9;
  padding: 1.1rem;
  font-size: 1.1rem;
  font-weight: 700;
  border-radius: 4px;
  cursor: pointer;
  display: block;
  text-align: center;
  text-decoration: none;
  box-sizing: border-box;
}
</style>
