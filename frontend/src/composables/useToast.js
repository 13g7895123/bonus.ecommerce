import { ref, readonly } from 'vue'

/**
 * 全域 Toast 通知系統
 * 使用方式：
 *   import { useToast } from '@/composables/useToast'
 *   const toast = useToast()
 *   toast.success('操作成功')
 *   toast.error('發生錯誤')
 *   toast.info('提示訊息')
 *   toast.warning('警告訊息')
 */

const toasts = ref([])
let nextId = 1

function addToast(message, type = 'info', duration = 3000) {
  const id = nextId++
  toasts.value.push({ id, message, type })
  if (duration > 0) {
    setTimeout(() => removeToast(id), duration)
  }
  return id
}

function removeToast(id) {
  const idx = toasts.value.findIndex(t => t.id === id)
  if (idx !== -1) {
    toasts.value.splice(idx, 1)
  }
}

export function useToast() {
  return {
    toasts: readonly(toasts),
    success: (msg, duration) => addToast(msg, 'success', duration),
    error:   (msg, duration) => addToast(msg, 'error',   duration ?? 4000),
    info:    (msg, duration) => addToast(msg, 'info',    duration),
    warning: (msg, duration) => addToast(msg, 'warning', duration),
    remove:  removeToast,
  }
}
