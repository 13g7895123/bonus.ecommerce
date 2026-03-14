<template>
  <Teleport to="body">
    <div class="toast-container" aria-live="polite" aria-atomic="false">
      <TransitionGroup name="toast">
        <div
          v-for="t in toasts"
          :key="t.id"
          class="toast"
          :class="`toast--${t.type}`"
          role="alert"
          @click="remove(t.id)"
        >
          <span class="toast__icon">{{ icons[t.type] }}</span>
          <span class="toast__msg">{{ t.message }}</span>
          <button class="toast__close" @click.stop="remove(t.id)" aria-label="關閉">✕</button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup>
import { useToast } from '@/composables/useToast'

const { toasts, remove } = useToast()

const icons = {
  success: '✓',
  error:   '✕',
  warning: '⚠',
  info:    'ℹ',
}
</script>

<style scoped>
.toast-container {
  position: fixed;
  top: 1.25rem;
  right: 1.25rem;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: .5rem;
  pointer-events: none;
  max-width: 360px;
  width: 100%;
}

.toast {
  display: flex;
  align-items: center;
  gap: .6rem;
  padding: .75rem 1rem;
  border-radius: .5rem;
  font-size: .9rem;
  font-weight: 500;
  box-shadow: 0 4px 16px rgba(0,0,0,.25);
  pointer-events: all;
  cursor: pointer;
  backdrop-filter: blur(4px);
  line-height: 1.4;
  word-break: break-word;
}

.toast--success { background: #166534; color: #dcfce7; border-left: 4px solid #22c55e; }
.toast--error   { background: #7f1d1d; color: #fee2e2; border-left: 4px solid #ef4444; }
.toast--warning { background: #78350f; color: #fef3c7; border-left: 4px solid #f59e0b; }
.toast--info    { background: #1e3a5f; color: #dbeafe; border-left: 4px solid #3b82f6; }

.toast__icon { font-size: 1rem; flex-shrink: 0; }
.toast__msg  { flex: 1; }
.toast__close {
  background: none; border: none; cursor: pointer;
  color: inherit; opacity: .7; font-size: .85rem; flex-shrink: 0;
  padding: 0 .2rem;
  line-height: 1;
}
.toast__close:hover { opacity: 1; }

/* transition */
.toast-enter-active { transition: all .25s ease; }
.toast-leave-active { transition: all .2s ease; }
.toast-enter-from   { opacity: 0; transform: translateX(60px); }
.toast-leave-to     { opacity: 0; transform: translateX(60px); }
</style>
