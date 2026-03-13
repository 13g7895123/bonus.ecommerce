// src/services/config.js

// 預設設定
const config = {
  useMock: import.meta.env.VITE_USE_MOCK_API === 'true', // true: Local Storage, false: Real API
  apiUrl: import.meta.env.VITE_API_URL || '/api',
};

export default config;