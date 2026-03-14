<template>
  <div class="upload-box" @click="triggerFileInput">
    <input
      type="file"
      accept="image/*"
      class="hidden-input"
      @change="handleFileChange"
      ref="fileInput"
    />
    
    <div v-if="modelValue" class="preview-container">
      <img :src="modelValue" class="preview-img" alt="Preview" />
    </div>

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
  modelValue: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue'])
const fileInput = ref(null)

const triggerFileInput = () => {
  fileInput.value.click()
}

const handleFileChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    const reader = new FileReader()
    reader.onload = (e) => {
      emit('update:modelValue', e.target.result)
    }
    reader.readAsDataURL(file)
  }
}

// Expose triggerFileInput to the template (although usually automatic in script setup, explicitly using props in template call was incorrect above, corrected in template)
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
</style>
