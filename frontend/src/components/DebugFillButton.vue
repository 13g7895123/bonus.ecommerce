<template>
  <button
    v-if="!hideDebugButtons"
    type="button"
    class="debug-fill-btn"
    @click="$emit('fill')"
    title="Fill with random data"
  >
    🎲 隨機資料
  </button>
</template>

<script setup>
import config from '../services/config';

const hideDebugButtons = config.hideDebugButtons;

defineEmits(['fill']);
</script>

<style scoped>
.debug-fill-btn {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 9999;
  background-color: #333;
  color: #fff;
  border: none;
  border-radius: 50px;
  padding: 10px 20px;
  font-size: 14px;
  cursor: pointer;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  opacity: 0.8;
  transition: opacity 0.3s;
}

.debug-fill-btn:hover {
  opacity: 1;
}
</style>