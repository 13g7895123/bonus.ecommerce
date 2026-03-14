import { ref } from 'vue'

export function usePasswordForm() {
  const password = ref('')
  const confirmPassword = ref('')

  const validate = () => {
    if (!password.value) {
      alert('請輸入密碼')
      return false
    }

    if (password.value.length < 6 || password.value.length > 12) {
      alert('密碼長度需為 6-12 位')
      return false
    }

    if (!confirmPassword.value) {
      alert('請再次輸入密碼')
      return false
    }

    if (password.value !== confirmPassword.value) {
      alert('兩次輸入的密碼不一致')
      return false
    }

    return true
  }

  return { password, confirmPassword, validate }
}
