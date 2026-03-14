<template>
  <div class="app-input-group">
    <label v-if="label" class="app-input-label">{{ label }}</label>
    <input
      :type="type"
      :placeholder="placeholder"
      class="app-input"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
    />
  </div>
</template>

<script setup>
defineProps({
  type: { type: String, default: 'text' },
  placeholder: { type: String, default: '' },
  label: { type: String, default: '' },
  modelValue: { type: String, default: '' },
})
defineEmits(['update:modelValue'])
</script>

<style scoped>
.app-input-group {
  margin-bottom: 1rem;
}

.app-input-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 0.4rem;
}

.app-input {
  width: 100%;
  padding: 0.875rem 1rem;
  border: 1px solid #a8a8a9;
  border-radius: 8px;
  font-size: 0.95rem;
  color: #333;
  background-color: #ffffff;
  box-sizing: border-box;
  outline: none;
  transition: border-color 0.2s;
}

.app-input::placeholder {
  color: #676767;
}

.app-input:focus {
  border-color: #d71921;
  background-color: #ffffff;
}

.app-input::placeholder {
  color: #aaa;
}
</style>
