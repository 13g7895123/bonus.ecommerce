<template>
  <div class="upload-box">
    <div class="upload-icon">📷</div>
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
