<template>
  <div class="panel cs-panel">
    <!-- 左側：對話列表 -->
    <div class="cs-list">
      <div class="cs-list-header">
        <span class="panel-title">在線客服對話</span>
        <button class="btn btn-outline btn-sm" :disabled="loadingList" @click="loadConversations">
          <RefreshCw :size="14" />{{ loadingList ? '載入中...' : '重新整理' }}
        </button>
      </div>
      <div v-if="loadingList" class="state-msg">載入中...</div>
      <div v-else-if="conversations.length === 0" class="state-msg">暫無對話記錄</div>
      <div
        v-for="conv in conversations"
        :key="conv.ticket_id"
        class="cs-conv-item"
        :class="{ active: activeTicket === conv.ticket_id }"
        @click="openConversation(conv)"
      >
        <div class="cs-conv-name">{{ conv.full_name || conv.email }}</div>
        <div class="cs-conv-email">{{ conv.email }}</div>
        <div class="cs-conv-last">{{ conv.last_message || '（圖片）' }}</div>
        <div class="cs-conv-time">{{ formatTime(conv.last_at) }}</div>
      </div>
    </div>

    <!-- 右側：訊息內容 -->
    <div class="cs-chat">
      <div v-if="!activeTicket" class="cs-chat-empty">
        <MessageSquare :size="40" style="opacity:0.3;margin-bottom:0.75rem" />
        <p>請從左側選擇一個對話</p>
      </div>

      <template v-else>
        <!-- 聊天頭部 -->
        <div class="cs-chat-header">
          <div>
            <div class="cs-chat-title">{{ activeConv?.full_name || activeConv?.email }}</div>
            <div class="cs-chat-sub">{{ activeConv?.email }} · {{ activeConv?.total_messages }} 則訊息</div>
          </div>
        </div>

        <!-- 訊息列表 -->
        <div ref="msgContainer" class="cs-messages">
          <div v-if="loadingMsgs" class="state-msg">載入中...</div>
          <template v-else>
            <div
              v-for="msg in messages"
              :key="msg.id"
              :class="['cs-msg-row', msg.sender_type === 'admin' ? 'admin-side' : 'user-side']"
            >
              <div :class="['cs-bubble', msg.sender_type === 'admin' ? 'admin-bubble' : 'user-bubble']">
                <span class="cs-bubble-text">{{ msg.content || '（圖片）' }}</span>
                <span class="cs-bubble-time">{{ formatTime(msg.created_at) }}</span>
              </div>
            </div>
          </template>
        </div>

        <!-- 回覆輸入框 -->
        <div class="cs-input-bar">
          <input
            v-model="replyText"
            type="text"
            class="cs-input"
            placeholder="輸入客服回覆訊息..."
            :disabled="sending"
            @keyup.enter="sendReply"
          />
          <button class="btn btn-primary" :disabled="sending || !replyText.trim()" @click="sendReply">
            {{ sending ? '發送中...' : '發送' }}
          </button>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, nextTick } from 'vue'
import { RefreshCw, MessageSquare } from 'lucide-vue-next'

const conversations  = ref([])
const loadingList    = ref(false)
const activeTicket   = ref(null)
const activeConv     = ref(null)
const messages       = ref([])
const loadingMsgs    = ref(false)
const replyText      = ref('')
const sending        = ref(false)
const msgContainer   = ref(null)

const formatTime = (dt) => {
  if (!dt) return ''
  const d = new Date(dt)
  const pad = n => String(n).padStart(2, '0')
  return `${d.getFullYear()}/${pad(d.getMonth() + 1)}/${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}`
}

const scrollToBottom = async () => {
  await nextTick()
  if (msgContainer.value) msgContainer.value.scrollTop = msgContainer.value.scrollHeight
}

const loadConversations = async () => {
  loadingList.value = true
  try {
    const res  = await fetch('/api/v1/admin-panel/cs/conversations')
    const data = await res.json()
    conversations.value = data.items || []
  } finally { loadingList.value = false }
}

const openConversation = async (conv) => {
  activeTicket.value = conv.ticket_id
  activeConv.value   = conv
  loadingMsgs.value  = true
  messages.value     = []
  try {
    const res  = await fetch(`/api/v1/admin-panel/cs/conversations/${conv.ticket_id}`)
    const data = await res.json()
    messages.value = data.items || []
    await scrollToBottom()
  } finally { loadingMsgs.value = false }
}

const sendReply = async () => {
  const text = replyText.value.trim()
  if (!text || sending.value) return
  sending.value = true
  try {
    const res  = await fetch(`/api/v1/admin-panel/cs/conversations/${activeTicket.value}/reply`, {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ content: text }),
    })
    const data = await res.json()
    if (!res.ok) { alert(data.message || '發送失敗'); return }
    replyText.value = ''
    // Reload messages
    const res2 = await fetch(`/api/v1/admin-panel/cs/conversations/${activeTicket.value}`)
    const data2 = await res2.json()
    messages.value = data2.items || []
    await scrollToBottom()
  } finally { sending.value = false }
}

loadConversations()
</script>

<style scoped>
.cs-panel {
  display: flex;
  gap: 0;
  height: calc(100vh - 64px);
  padding: 0;
  overflow: hidden;
}

/* 左側列表 */
.cs-list {
  width: 300px;
  flex-shrink: 0;
  border-right: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}
.cs-list-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem;
  border-bottom: 1px solid #e2e8f0;
  background: #f8fafc;
}
.cs-conv-item {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #f1f5f9;
  cursor: pointer;
  transition: background 0.15s;
}
.cs-conv-item:hover { background: #f1f5f9; }
.cs-conv-item.active { background: #eff6ff; border-left: 3px solid #3b82f6; }
.cs-conv-name { font-weight: 600; font-size: 0.875rem; color: #1e293b; }
.cs-conv-email { font-size: 0.75rem; color: #64748b; margin-top: 1px; }
.cs-conv-last {
  font-size: 0.8rem; color: #94a3b8;
  margin-top: 4px;
  overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.cs-conv-time { font-size: 0.7rem; color: #cbd5e1; margin-top: 2px; }
.cs-list { overflow-y: auto; }

/* 右側聊天 */
.cs-chat {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}
.cs-chat-empty {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  height: 100%; color: #94a3b8; font-size: 0.9rem;
}
.cs-chat-header {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #e2e8f0;
  background: #f8fafc;
}
.cs-chat-title { font-weight: 600; font-size: 0.9rem; color: #1e293b; }
.cs-chat-sub   { font-size: 0.75rem; color: #64748b; margin-top: 2px; }

.cs-messages {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}
.cs-msg-row { display: flex; }
.cs-msg-row.admin-side { justify-content: flex-end; }
.cs-msg-row.user-side  { justify-content: flex-start; }

.cs-bubble {
  max-width: 70%;
  padding: 0.5rem 0.75rem;
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.admin-bubble {
  background: #3b82f6;
  color: #fff;
  border-bottom-right-radius: 4px;
}
.user-bubble {
  background: #f1f5f9;
  color: #1e293b;
  border-bottom-left-radius: 4px;
}
.cs-bubble-text { font-size: 0.875rem; line-height: 1.4; word-break: break-word; }
.cs-bubble-time { font-size: 0.65rem; opacity: 0.7; align-self: flex-end; }

.cs-input-bar {
  padding: 0.75rem 1rem;
  border-top: 1px solid #e2e8f0;
  display: flex;
  gap: 0.5rem;
  background: #f8fafc;
}
.cs-input {
  flex: 1;
  height: 38px;
  padding: 0 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 0.875rem;
  outline: none;
}
.cs-input:focus { border-color: #3b82f6; }

.state-msg { text-align: center; color: #94a3b8; padding: 2rem; font-size: 0.875rem; }
</style>
