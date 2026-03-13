<template>
  <div class="upload-box">
    <div class="upload-icon">
      <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
        <circle cx="12" cy="13" r="4"></circle>
      </svg>
    </div>
    <p v-if="side" class="upload-side">{{ side }}</p>
    <p class="upload-hint">{{ hint }}</p>
  </div>
</template>

<script setup>
defineProps({
  hint: { type: String, required: true },
  side: { type: String, default: '' },
})
</script>

<style scoped>
.upload-box {
  border: 2px dashed #ccc;
  border-radius: 12px;
  padding: 2rem 1rem;
  text-align: center;
  margin-bottom: 1rem;
  cursor: pointer;
}

.upload-icon {
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

.upload-side {
  font-size: 1rem;
  font-weight: 600;
  color: #333;
  margin: 0 0 0.25rem 0;
}

.upload-hint {
  font-size: 0.875rem;
  color: #999;
  margin: 0;
}
</style>
