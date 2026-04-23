<template>
  <div class="cs-page">
    <PageHeader :title="$t('cs.title')" :back-to="backTo" />

    <!-- 載入狀態 -->
    <div v-if="initialLoading" class="loading-overlay">
      <div class="loading-spinner"></div>
      <p>載入中...</p>
    </div>

    <!-- 訊息列表 -->
    <div ref="messagesContainer" class="messages-container" @scroll="handleScroll">
      <!-- 頂部拉取更多 -->
      <div v-if="loadingMore" class="loading-more">載入中...</div>

      <!-- 無訊息提示 -->
      <div v-if="!initialLoading && groupedMessages.length === 0" class="empty-messages">
        <p>{{ $t('cs.empty') }}</p>
      </div>

      <template v-for="group in groupedMessages" :key="group.date">
        <!-- 日期分隔線 -->
        <div class="date-divider">
          <span>{{ group.date }}</span>
        </div>

        <!-- 訊息氣泡 -->
        <div
          v-for="msg in group.messages"
          :key="msg.id"
          :class="['message-row', msg.sender_type === 'user' ? 'self' : 'other']"
        >
          <div v-if="msg.sender_type !== 'user'" class="avatar">客服</div>
          <div :class="['bubble', msg.sender_type === 'user' ? 'self-bubble' : 'other-bubble']">
            <!-- 圖片訊息 -->
            <img
              v-if="msg.image_url"
              :src="msg.image_url"
              class="message-image"
              @click="openImage(msg.image_url)"
            />
            <!-- 文字訊息 -->
            <span v-if="msg.content">{{ msg.content }}</span>
            <span class="msg-time">{{ formatTime(msg.created_at) }}</span>
          </div>
        </div>
      </template>

      <!-- 發送中佔位 -->
      <div v-if="sending" class="message-row self">
        <div class="bubble self-bubble sending-bubble">{{ inputText }}<span class="sending-dots">...</span></div>
      </div>
    </div>

    <!-- 圖片預覽 -->
    <div v-if="imagePreview" class="image-preview-bar">
      <img :src="imagePreview" class="preview-thumb" />
      <button class="remove-preview" @click="removeImage">✕</button>
    </div>

    <!-- 固定輸入列 -->
    <div class="input-bar">
      <input ref="fileInput" type="file" accept="image/*" style="display:none" @change="handleImageSelect" />
      <button class="plus-btn" @click="fileInput?.click()">＋</button>
      <input
        v-model="inputText"
        type="text"
        class="chat-input"
        :placeholder="$t('cs.inputPlaceholder')"
        @keyup.enter="sendMessage"
        :disabled="sending"
      />
      <button class="send-btn" :disabled="sending || (!inputText.trim() && !selectedImage)" @click="sendMessage">
        {{ sending ? $t('cs.sending') : $t('cs.send') }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute } from 'vue-router'
import PageHeader from '../components/PageHeader.vue'
import { useApi } from '../composables/useApi'

const { t } = useI18n()
const api = useApi()
const route = useRoute()

const backTo = computed(() => route.query.from === 'skywards' ? '/skywards' : '/settings')

// 狀態
const messages = ref([])       // 所有訊息
const inputText = ref('')       // 輸入框
const sending = ref(false)      // 發送中
const initialLoading = ref(true) // 首次載入
const loadingMore = ref(false)   // 載入更多
const messagesContainer = ref(null)
const fileInput = ref(null)
const selectedImage = ref(null)
const imagePreview = ref(null)
const page = ref(1)
const hasMore = ref(true)
const ticketId = ref(null)
let pollTimer = null
let ws = null
let reconnectTimer = null

// 按日期分組訊息
const groupedMessages = computed(() => {
  const groups = []
  let currentDate = ''
  const sorted = [...messages.value].sort((a, b) =>
    new Date(a.created_at) - new Date(b.created_at)
  )
  for (const msg of sorted) {
    const d = formatDate(msg.created_at)
    if (d !== currentDate) {
      currentDate = d
      groups.push({ date: d, messages: [] })
    }
    groups[groups.length - 1].messages.push(msg)
  }
  return groups
})

// 格式化日期
const formatDate = (dateStr) => {
  const d = new Date(dateStr)
  return `${d.getFullYear()}年${d.getMonth() + 1}月${d.getDate()}日`
}

// 格式化時間
const formatTime = (dateStr) => {
  const d = new Date(dateStr)
  const hh = String(d.getHours()).padStart(2, '0')
  const mm = String(d.getMinutes()).padStart(2, '0')
  return `${hh}:${mm}`
}

// 滾動至底部
const scrollToBottom = async (smooth = false) => {
  await nextTick()
  const el = messagesContainer.value
  if (el) {
    el.scrollTo({ top: el.scrollHeight, behavior: smooth ? 'smooth' : 'auto' })
  }
}

// 載入訊息
const loadMessages = async (loadPage = 1, append = false) => {
  try {
    const result = await api.cs.getMessages({ page: loadPage, limit: 50 })
    const newMsgs = result?.items || []
    ticketId.value = result?.ticket_id || null
    if (append) {
      // 前置舊訊息
      messages.value = [...newMsgs, ...messages.value]
      hasMore.value = newMsgs.length >= 50
    } else {
      messages.value = newMsgs
      hasMore.value = newMsgs.length >= 50
      await scrollToBottom()
    }
  } catch (e) {
    console.error('載入訊息失敗', e)
  }
}

// 初始載入
const initLoad = async () => {
  initialLoading.value = true
  await loadMessages(1, false)
  initialLoading.value = false
}

// 輪詢新訊息
const pollMessages = async () => {
  try {
    const result = await api.cs.getMessages({ page: 1, limit: 50 })
    const newMsgs = result?.items || []
    if (newMsgs.length !== messages.value.length) {
      const el = messagesContainer.value
      const isAtBottom = !el || el.scrollHeight - el.scrollTop - el.clientHeight < 80
      messages.value = newMsgs
      if (isAtBottom) await scrollToBottom(true)
    }
  } catch (e) {
    // silent
  }
}

// 處理上滑載入更多
const handleScroll = async () => {
  const el = messagesContainer.value
  if (!el || loadingMore.value || !hasMore.value) return
  if (el.scrollTop < 50) {
    loadingMore.value = true
    page.value += 1
    await loadMessages(page.value, true)
    loadingMore.value = false
  }
}

// 選擇圖片
const handleImageSelect = (e) => {
  const file = e.target.files?.[0]
  if (!file) return
  selectedImage.value = file
  const reader = new FileReader()
  reader.onload = (ev) => { imagePreview.value = ev.target.result }
  reader.readAsDataURL(file)
}

const removeImage = () => {
  selectedImage.value = null
  imagePreview.value = null
  if (fileInput.value) fileInput.value.value = ''
}

// 發送訊息
const sendMessage = async () => {
  const text = inputText.value.trim()
  if (!text && !selectedImage.value) return
  sending.value = true
  const tempText = text
  try {
    await api.cs.sendMessage(text || null, selectedImage.value || null)
    inputText.value = ''
    removeImage()
    // 若 WebSocket 已連線，訊息會即時 push 進來；否則 fallback reload
    if (!ws || ws.readyState !== WebSocket.OPEN) {
      await loadMessages(1, false)
    }
  } catch (e) {
    inputText.value = tempText
    console.error('發送失敗', e)
  } finally {
    sending.value = false
  }
}

// 開啟圖片
const openImage = (url) => {
  window.open(url, '_blank')
}

// ── WebSocket 即時連線 ──────────────────────────────────────────────────────
const connectWs = () => {
  const token = localStorage.getItem('token')
  if (!token) return

  const protocol = window.location.protocol === 'https:' ? 'wss' : 'ws'
  const url = `${protocol}://${window.location.host}/ws?token=${encodeURIComponent(token)}`

  ws = new WebSocket(url)

  ws.onopen = () => {
    // WS 連上後降低輪詢頻率（保留作為最終 fallback）
    if (pollTimer) { clearInterval(pollTimer); pollTimer = null }
  }

  ws.onmessage = (evt) => {
    try {
      const payload = JSON.parse(evt.data)
      if (payload.type === 'message') {
        const msg = payload.data
        // 避免重複（傳送後 REST 也會 reload）
        if (!messages.value.find(m => m.id === msg.id)) {
          messages.value.push(msg)
          const el = messagesContainer.value
          const isAtBottom = !el || el.scrollHeight - el.scrollTop - el.clientHeight < 80
          if (isAtBottom) scrollToBottom(true)
        }
      }
    } catch (e) {
      // ignore malformed frames
    }
  }

  ws.onclose = () => {
    ws = null
    // 斷線後恢復輪詢，並嘗試 5 秒後重連
    if (!pollTimer) pollTimer = setInterval(pollMessages, 10000)
    reconnectTimer = setTimeout(connectWs, 5000)
  }

  ws.onerror = () => {
    ws?.close()
  }
}
// ────────────────────────────────────────────────────────────────────────────

onMounted(async () => {
  await initLoad()
  connectWs()
  // 啟動輪詢作為 WebSocket 斷線時的 fallback（30 秒間隔）
  pollTimer = setInterval(pollMessages, 30000)
})

onUnmounted(() => {
  if (pollTimer) clearInterval(pollTimer)
  if (reconnectTimer) clearTimeout(reconnectTimer)
  if (ws) { ws.onclose = null; ws.close() }
})
</script>

<style scoped>
.cs-page {
  background-color: #f5f5f5;
  height: 100dvh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* 載入中遮罩 */
.loading-overlay {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: rgba(255,255,255,0.85);
  z-index: 50;
  gap: 0.75rem;
  font-size: 0.9rem;
  color: #666;
}

.loading-spinner {
  width: 36px;
  height: 36px;
  border: 3px solid #e0e0e0;
  border-top-color: #7b2ff7;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

.loading-more {
  text-align: center;
  font-size: 0.8rem;
  color: #999;
  padding: 0.5rem 0;
}

.empty-messages {
  text-align: center;
  color: #999;
  font-size: 0.9rem;
  padding: 3rem 0;
}

/* 訊息區域 */
.messages-container {
  flex: 1;
  padding: 1rem 1rem 0.5rem 1rem;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  /* 為固定輸入列空出足夠空間 */
  padding-bottom: calc(68px + env(safe-area-inset-bottom, 0px));
}

/* 圖片預覽列 */
.image-preview-bar {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: #fff;
  border-top: 1px solid #f0f0f0;
  position: fixed;
  bottom: calc(56px + env(safe-area-inset-bottom, 0px));
  left: 50%;
  transform: translateX(-50%);
  width: 100%;
  max-width: var(--app-max-width);
  z-index: 19;
  box-sizing: border-box;
}

.preview-thumb {
  width: 48px;
  height: 48px;
  object-fit: cover;
  border-radius: 8px;
  border: 1px solid #ddd;
}

.remove-preview {
  background: #e0e0e0;
  border: none;
  border-radius: 50%;
  width: 22px;
  height: 22px;
  font-size: 0.75rem;
  cursor: pointer;
  color: #555;
  line-height: 1;
}

/* 訊息圖片 */
.message-image {
  max-width: 180px;
  border-radius: 10px;
  cursor: pointer;
  display: block;
  margin-bottom: 0.25rem;
}

/* 時間戳 */
.msg-time {
  display: block;
  font-size: 0.65rem;
  color: rgba(255,255,255,0.65);
  margin-top: 0.25rem;
  text-align: right;
}
.self-bubble .msg-time {
  color: rgba(0,0,0,0.4);
}

/* 發送中動畫 */
.sending-bubble { opacity: 0.6; }
.sending-dots { animation: blink 1.2s infinite; }
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:0.2} }

/* 日期分隔線 */
.date-divider {
  text-align: center;
  margin: 0.5rem 0;
}

.date-divider span {
  font-size: 0.75rem;
  color: #999;
  background-color: #e8e8e8;
  padding: 0.2rem 0.75rem;
  border-radius: 12px;
}

/* 訊息行 */
.message-row {
  display: flex;
  align-items: flex-end;
  gap: 0.5rem;
}

.message-row.other {
  flex-direction: row;
}

.message-row.self {
  flex-direction: row-reverse;
}

/* 頭像 */
.avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background-color: #7b2ff7;
  color: #fff;
  font-size: 0.65rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

/* 氣泡 */
.bubble {
  max-width: 70%;
  padding: 0.65rem 0.9rem;
  border-radius: 16px;
  font-size: 0.9rem;
  line-height: 1.5;
  word-break: break-word;
}

.other-bubble {
  background-color: #7b2ff7;
  color: #ffffff;
  border-bottom-left-radius: 4px;
}

.self-bubble {
  background-color: #e5e5e5;
  color: #333333;
  border-bottom-right-radius: 4px;
}

/* 固定輸入列 */
.input-bar {
  position: fixed;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 100%;
  max-width: var(--app-max-width);
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  padding-bottom: max(0.75rem, env(safe-area-inset-bottom));
  background-color: #ffffff;
  border-top: 1px solid #eee;
  z-index: 20;
  box-sizing: border-box;
}

.plus-btn {
  width: 36px;
  height: 36px;
  min-width: 36px;
  min-height: 36px;
  aspect-ratio: 1 / 1;
  border: 1.5px solid #ccc;
  border-radius: 50%;
  background: none;
  font-size: 1.25rem;
  color: #666;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  line-height: 1;
  padding: 0;
  box-sizing: border-box;
}

.chat-input {
  flex: 1;
  min-width: 0;
  padding: 0.55rem 0.875rem;
  border: 1px solid #e0e0e0;
  border-radius: 20px;
  font-size: 0.9rem;
  outline: none;
  background-color: #f5f5f5;
}

.chat-input::placeholder {
  color: #aaa;
}

.send-btn {
  padding: 0.55rem 1rem;
  background-color: #D71921;
  color: #ffffff;
  border: none;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  flex-shrink: 0;
  transition: background-color 0.2s;
}

.send-btn:active,
.send-btn:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}
</style>
