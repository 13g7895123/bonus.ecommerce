<template>
  <button
    class="app-btn"
    :class="{ 'app-btn-block': block, 'app-btn-disabled': disabled }"
    :disabled="disabled"
    @click="$emit('click', $event)"
  >
    <slot></slot>
  </button>
</template>

<script setup>
defineProps({
  block: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
})
defineEmits(['click'])
</script>

<style scoped>
.app-btn {
  padding: 1rem;
  background-color: #d71921;
  color: #ffffff;
  border: none;
  border-radius: 4px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s;
}

.app-btn:hover:not(:disabled) {
  background-color: #b8151b;
}

.app-btn-block {
  width: 100%;
  display: block;
}

.app-btn-disabled {
  background-color: #ccc;
  cursor: not-allowed;
}
</style>