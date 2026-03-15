<template>
  <div class="empty-state">
    <div class="empty-icon">
      <svg width="84" height="84" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="12" cy="12" r="11" stroke="#bdbdbd" stroke-width="1.5"/>
        <line x1="12" y1="10" x2="12" y2="16" stroke="#bdbdbd" stroke-width="1.8" stroke-linecap="round"/>
        <circle cx="12" cy="7.5" r="1" fill="#bdbdbd"/>
      </svg>
    </div>
    <p class="empty-title">無交易內容</p>
    <p class="empty-desc">當您開始賺取和使用哩程數後，系統將會顯示交易內容</p>
  </div>
</template>

<style scoped>
.empty-state {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background-color: #f0f0f0;
  text-align: center;
  gap: 0.75rem;
  padding: 2rem 1.5rem;
}

.empty-icon {
  margin-bottom: 0.5rem;
}

.empty-title {
  font-size: 1.8rem;
  font-weight: 700;
  color: #444;
  margin: 0;
}

.empty-desc {
  font-size: 1rem;
  color: #888;
  margin: 0;
  line-height: 1.6;
}
</style>
