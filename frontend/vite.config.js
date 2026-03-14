import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vite.dev/config/
export default defineConfig({
  plugins: [vue()],
  server: {
    port: 3102,
    host: '0.0.0.0',
    allowedHosts: ['ecommerce.l'],
    proxy: {
      '/api': {
        target: 'http://ecommerce.l',
        changeOrigin: true,
      },
    },
  },
})
