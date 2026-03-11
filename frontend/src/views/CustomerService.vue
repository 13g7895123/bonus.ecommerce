<template>
  <div class="cs-page">
    <!-- Header -->
    <div class="cs-header">
      <router-link to="/settings" class="back-btn">
        <span class="arrow-left"></span>
      </router-link>
      <h2 class="header-title">在線客服</h2>
      <div class="header-placeholder"></div>
    </div>

    <!-- 訊息列表 -->
    <div class="messages-container">
      <!-- 日期分隔：3月10日 -->
      <div class="date-divider">
        <span>2026年3月10日</span>
      </div>

      <!-- 對方訊息 -->
      <div class="message-row other">
        <div class="avatar">客服</div>
        <div class="bubble other-bubble">您好！請問有什麼可以協助您的嗎？</div>
      </div>

      <!-- 使用者訊息 -->
      <div class="message-row self">
        <div class="bubble self-bubble">你好，我想詢問儲值相關問題</div>
      </div>

      <!-- 對方訊息 -->
      <div class="message-row other">
        <div class="avatar">客服</div>
        <div class="bubble other-bubble">好的，請問您需要了解哪方面的詳情呢？</div>
      </div>

      <!-- 日期分隔：3月11日（今天） -->
      <div class="date-divider">
        <span>2026年3月11日</span>
      </div>

      <!-- 對方訊息 -->
      <div class="message-row other">
        <div class="avatar">客服</div>
        <div class="bubble other-bubble">早安！今天有什麼需要幫助的嗎？</div>
      </div>

      <!-- 使用者訊息 -->
      <div class="message-row self">
        <div class="bubble self-bubble">請問儲值後多久會到帳？</div>
      </div>

      <!-- 對方訊息 -->
      <div class="message-row other">
        <div class="avatar">客服</div>
        <div class="bubble other-bubble">儲值通常在10至30分鐘內到帳，若超過時間請提供交易截圖給我們確認。</div>
      </div>
    </div>

    <!-- 固定輸入列 -->
    <div class="input-bar">
      <button class="plus-btn">＋</button>
      <input type="text" class="chat-input" placeholder="輸入訊息..." />
      <button class="send-btn">發送</button>
    </div>
  </div>
</template>

<style scoped>
.cs-page {
  background-color: #f5f5f5;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

/* Header */
.cs-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.5rem;
  background-color: #ffffff;
  border-bottom: 1px solid #eee;
  position: sticky;
  top: 0;
  z-index: 10;
}

.back-btn {
  text-decoration: none;
  color: #333;
  padding: 5px;
  display: flex;
  align-items: center;
}

.arrow-left {
  display: block;
  width: 12px;
  height: 12px;
  border-left: 2px solid #333;
  border-bottom: 2px solid #333;
  transform: rotate(45deg);
}

.header-title {
  font-size: 1.1rem;
  font-weight: 700;
  margin: 0;
}

.header-placeholder {
  width: 22px;
}

/* 訊息區域 */
.messages-container {
  flex: 1;
  padding: 1rem 1rem 6rem 1rem;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

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
  left: 0;
  right: 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  background-color: #ffffff;
  border-top: 1px solid #eee;
  z-index: 20;
}

.plus-btn {
  width: 36px;
  height: 36px;
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
}

.chat-input {
  flex: 1;
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
  background-color: #7b2ff7;
  color: #ffffff;
  border: none;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  flex-shrink: 0;
  transition: background-color 0.2s;
}

.send-btn:hover {
  background-color: #6620d4;
}
</style>
