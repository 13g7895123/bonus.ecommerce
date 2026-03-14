<template>
  <div class="upload-box" @click="triggerFileInput">
    <input
      type="file"
      accept="image/*"
      class="hidden-input"
      @change="handleFileChange"
      ref="fileInput"
    />

    <!-- 上傳中 -->
    <div v-if="uploading" class="upload-placeholder">
      <div class="upload-icon-wrapper">
        <svg class="spin-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#c60c33" stroke-width="2">
          <circle cx="12" cy="12" r="10" stroke-dasharray="40 20" />
        </svg>
      </div>
      <p class="upload-hint">上傳中...</p>
    </div>

    <!-- 已選取預覽 -->
    <div v-else-if="modelValue" class="preview-container">
      <img :src="modelValue" class="preview-img" alt="Preview" />
    </div>

    <!-- 空白預設 placeholder -->
    <div v-else class="upload-placeholder">
      <div class="upload-icon-wrapper">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z" fill="#ffffff" stroke="#ffffff"></path>
          <circle cx="12" cy="13" r="4" stroke="#c60c33"></circle>
        </svg>
      </div>
      <p v-if="side" class="upload-side">{{ side }}</p>
      <p class="upload-hint">{{ hint }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  hint: { type: String, required: true },
  side: { type: String, default: '' },
  /** v-model — 預覽圖用的字串（base64 或遠端 URL） */
  modelValue: { type: String, default: '' },
  /** 上傳中狀態（由父元件傳入，顯示 loading spinner） */
  uploading: { type: Boolean, default: false },
})

const emit = defineEmits([
  'update:modelValue', // 更新 preview
  'file-selected',     // 帶出原始 File 物件，讓父元件自行上傳
])

const fileInput = ref(null)

const triggerFileInput = () => {
  fileInput.value.click()
}

const handleFileChange = (event) => {
  const file = event.target.files[0]
  if (!file) return

  // 先用 FileReader 產生本地 preview
  const reader = new FileReader()
  reader.onload = (e) => {
    emit('update:modelValue', e.target.result)
  }
  reader.readAsDataURL(file)

  // 同時把 File 物件傳給父元件，由父元件呼叫 FileService 上傳
  emit('file-selected', file)

  // 清除 input，讓同一張圖可重複選取
  event.target.value = ''
}
</script>

<style scoped>
.upload-box {
  border: 2px dashed #ccc;
  border-radius: 12px;
  padding: 2rem 1rem;
  text-align: center;
  margin-bottom: 1rem;
  cursor: pointer;
  background-color: #fff;
  overflow: hidden;
  position: relative;
  transition: all 0.2s ease;
}

.upload-box:hover {
  border-color: #c60c33;
  background-color: #fff5f5;
}

.hidden-input {
  display: none;
}

.upload-icon-wrapper {
  background-color: #c60c33;
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 0.5rem auto;
}

/* Adjust svg size inside wrapper if needed, stroke is set inline */

.upload-side {
  font-size: 1rem;
  font-weight: 600;
  color: #c60c33;
  margin: 0 0 0.25rem 0;
}

.upload-hint {
  font-size: 0.875rem;
  color: #999;
  margin: 0;
}

.preview-container {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
  height: 100%;
}

.preview-img {
  max-width: 100%;
  max-height: 200px;
  object-fit: contain;
  border-radius: 8px;
}

.spin-icon {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to   { transform: rotate(360deg); }
}
</style>
