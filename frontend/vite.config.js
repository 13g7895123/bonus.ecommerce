import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'
import { fileURLToPath, URL } from 'node:url'

// https://vite.dev/config/
export default defineConfig({
  plugins: [vue(), tailwindcss()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  server: {
    port: 3102,
    host: '0.0.0.0',
    allowedHosts: 'all',
    proxy: {
      // WebSocket → local Swoole server (Docker exposes WS_PORT to host)
      '/ws': {
        target: `ws://localhost:${process.env.WS_PORT || 9501}`,
        ws: true,
        changeOrigin: true,
      },
      '/api': {
        target: 'https://demo.mercylife.cc',
        changeOrigin: true,
      },
    },
  },
})
