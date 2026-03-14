<template>
  <div class="content-list-item" :class="{'no-border': noBorder}">
    <slot></slot>
  </div>
</template>

<script setup>
defineProps({
  noBorder: {
    type: Boolean,
    default: false
  }
})
</script>

<style scoped>
.content-list-item {
  display: flex;
  align-items: center;
  padding: 1.1rem 0;
  border-bottom: 1px solid #e0e0e0;
  cursor: inherit;
}

.content-list-item.no-border {
  border-bottom: none;
}
</style>
