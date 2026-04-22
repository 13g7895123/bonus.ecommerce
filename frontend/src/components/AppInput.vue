<template>
  <div class="app-input-group">
    <label v-if="label" class="app-input-label">{{ label }}</label>
    <div class="app-input-wrapper">
      <input
        :type="inputType"
        :placeholder="placeholder"
        class="app-input"
        :class="{ 'app-input--readonly': readonly, 'app-input--has-toggle': type === 'password' }"
        :value="modelValue"
        :readonly="readonly"
        :disabled="disabled"
        @input="!readonly && $emit('update:modelValue', $event.target.value)"
      />
      <button v-if="type === 'password'" type="button" class="pwd-toggle" @click="showPassword = !showPassword" tabindex="-1">
        <svg v-if="showPassword" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  type: { type: String, default: 'text' },
  placeholder: { type: String, default: '' },
  label: { type: String, default: '' },
  modelValue: { type: String, default: '' },
  readonly: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
})
defineEmits(['update:modelValue'])

const showPassword = ref(false)
const inputType = computed(() => {
  if (props.type === 'password') return showPassword.value ? 'text' : 'password'
  return props.type
})
</script>

<style scoped>
.app-input-group {
  width: 100%;
  margin-bottom: 1rem;
}

.app-input-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 0.4rem;
}

.app-input-wrapper {
  position: relative;
  width: 100%;
}

.app-input {
  width: 100%;
  min-width: 0;
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

.app-input--has-toggle {
  padding-right: 2.75rem;
}

.app-input::placeholder {
  color: #676767;
}

.app-input:focus {
  border-color: #d71921;
  background-color: #ffffff;
}

.app-input--readonly {
  background-color: #f5f5f5;
  cursor: default;
}

.pwd-toggle {
  position: absolute;
  right: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  padding: 0;
  color: #888;
  display: flex;
  align-items: center;
}

.pwd-toggle:hover {
  color: #333;
}
</style>
