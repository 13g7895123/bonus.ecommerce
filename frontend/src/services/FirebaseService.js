/**
 * FirebaseService.js
 *
 * 封裝 Firebase Authentication 電話驗證（Phone Auth）流程。
 * 僅在 sms_provider 為 'firebase' 時由 Register.vue 使用。
 *
 * 流程：
 *  1. initApp()      — 初始化 Firebase App（冪等，只初始化一次）
 *  2. sendOtp(phone) — 使用 signInWithPhoneNumber 觸發簡訊發送
 *                       回傳 verificationId (= sessionInfo)，供後端儲存
 *
 * 必填的 VITE 環境變數：
 *   VITE_FIREBASE_API_KEY
 *   VITE_FIREBASE_AUTH_DOMAIN
 *   VITE_FIREBASE_PROJECT_ID
 */

let _app  = null
let _auth = null

async function initApp() {
  if (_auth) return _auth

  const { initializeApp, getApps } = await import('firebase/app')
  const { getAuth }                = await import('firebase/auth')

  const firebaseConfig = {
    apiKey:    import.meta.env.VITE_FIREBASE_API_KEY,
    authDomain: import.meta.env.VITE_FIREBASE_AUTH_DOMAIN,
    projectId: import.meta.env.VITE_FIREBASE_PROJECT_ID,
  }

  _app  = getApps().length ? getApps()[0] : initializeApp(firebaseConfig)
  _auth = getAuth(_app)

  return _auth
}

/**
 * 使用 Firebase SDK 發送 OTP 簡訊
 *
 * @param {string}      phone         收件電話（E.164 格式，例如 +886912345678）
 * @param {HTMLElement} recaptchaEl   用於掛載 invisible reCAPTCHA 的 DOM 元素（id）或元素本身
 * @returns {Promise<string>}         verificationId（即 Firebase sessionInfo）
 */
export async function firebaseSendOtp(phone, recaptchaEl = 'firebase-recaptcha') {
  const { RecaptchaVerifier, signInWithPhoneNumber } = await import('firebase/auth')
  const auth = await initApp()

  // 清理舊的 RecaptchaVerifier（避免重複掛載報錯）
  if (window._firebaseRecaptchaVerifier) {
    try { window._firebaseRecaptchaVerifier.clear() } catch (_) {}
    window._firebaseRecaptchaVerifier = null
  }

  const verifier = new RecaptchaVerifier(auth, recaptchaEl, { size: 'invisible' })
  window._firebaseRecaptchaVerifier = verifier

  const confirmationResult = await signInWithPhoneNumber(auth, phone, verifier)
  // confirmationResult.verificationId 即 Firebase 的 sessionInfo
  return confirmationResult.verificationId
}

export default { firebaseSendOtp }
