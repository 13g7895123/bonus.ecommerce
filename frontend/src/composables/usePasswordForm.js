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

  const saveToLocalStorage = (fieldPath) => {
    const userStr = localStorage.getItem('user')
    if (userStr) {
      const user = JSON.parse(userStr)
      
      // Handle nested paths like 'wallet.password'
      const fields = fieldPath.split('.')
      let target = user
      
      for (let i = 0; i < fields.length - 1; i++) {
        if (!target[fields[i]]) {
            target[fields[i]] = {} // Initialize if missing
        }
        target = target[fields[i]]
      }
      
      target[fields[fields.length - 1]] = password.value
      
      localStorage.setItem('user', JSON.stringify(user))

      // Also update mock_db_users if user exists there
      const mockUsersStr = localStorage.getItem('mock_db_users')
      if (mockUsersStr) {
        try {
          const mockUsers = JSON.parse(mockUsersStr)
          const dbUser = mockUsers.find(u => u.id === user.id)
          if (dbUser) {
             let dbTarget = dbUser
             for (let i = 0; i < fields.length - 1; i++) {
                if (!dbTarget[fields[i]]) {
                    dbTarget[fields[i]] = {}
                }
                dbTarget = dbTarget[fields[i]]
             }
             dbTarget[fields[fields.length - 1]] = password.value
             localStorage.setItem('mock_db_users', JSON.stringify(mockUsers))
          }
        } catch (e) {
          console.error('Failed to update mock_db_users', e)
        }
      }
      return true
    }
    return false
  }

  return {
    password,
    confirmPassword,
    validate,
    saveToLocalStorage
  }
}
