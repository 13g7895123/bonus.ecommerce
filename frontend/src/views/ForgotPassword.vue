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
        <AppInput v-model="phone" type="tel" placeholder="請輸入您的電話號碼" />
        <p class="forgot-instructions">我們會向您發送訊息，以設定或重設您的新密碼</p>
        <button class="login-submit-btn" @click="handleSubmit" :disabled="loading">
            {{ loading ? '處理中...' : '提交' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import AppInput from '../components/AppInput.vue'
import Swal from 'sweetalert2'
import config from '../services/config'
import { mockDb } from '../services/mockDb'

const router = useRouter()
const phone = ref('')
const loading = ref(false)

const handleSubmit = async () => {
    if (!phone.value) {
        Swal.fire({
            icon: 'warning',
            title: '請輸入電話號碼',
            confirmButtonColor: '#d71921'
        })
        return
    }

    loading.value = true
    
    // 模擬處理時間
    setTimeout(async () => {
        loading.value = false
        
        if (config.useMock) {
            try {
                // 在 Mock DB 中尋找該電話號碼的使用者
                const user = await mockDb.findOne('users', u => u.phone === phone.value)
                
                if (user) {
                    Swal.fire({
                        title: '模擬簡訊發送成功',
                        html: `
                            <p>親愛的用戶，您的密碼是：<strong>${user.password}</strong></p>
                            <p style="color: red; margin-top: 10px;">請登入後務必修改密碼！</p>
                        `,
                        icon: 'info',
                        confirmButtonText: '前往登入',
                        confirmButtonColor: '#d71921'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            router.push('/login')
                        }
                    })
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '找不到使用者',
                        text: '該電話號碼未註冊',
                        confirmButtonColor: '#d71921'
                    })
                }
            } catch (error) {
                console.error(error)
                Swal.fire({
                    icon: 'error',
                    title: '發生錯誤',
                    text: '請稍後再試',
                    confirmButtonColor: '#d71921'
                })
            }
        } else {
            // 真實 API 模式 (暫未實作)
            Swal.fire({
                icon: 'info',
                title: '訊息已發送',
                text: '如果該號碼已註冊，您將收到重設密碼簡訊',
                confirmButtonColor: '#d71921'
            })
        }
    }, 1000)
}
</script>

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
    margin-right: 32px; /* Balance the back button width to center efficiently */
}

.login-logo-small img {
    height: 40px;
}

</style>

<style scoped>
.standalone-login-page {
  background-color: #ffffff;
  min-height: 100vh;
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

.login-form {
  width: 100%;
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
