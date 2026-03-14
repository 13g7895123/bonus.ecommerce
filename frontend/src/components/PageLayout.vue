<template>
  <div class="page-layout" :class="`layout-${theme}`">
    <PageHeader 
      :title="title" 
      :back-to="backTo" 
      :use-history-back="useHistoryBack"
      :bordered="theme === 'gray'" 
    >
      <template #right>
        <slot name="header-right"></slot>
      </template>
    </PageHeader>

    <main class="page-content">
      <slot></slot>
    </main>
  </div>
</template>

<script setup>
import PageHeader from './PageHeader.vue'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  backTo: {
    type: String,
    default: ''
  },
  useHistoryBack: {
    type: Boolean,
    default: false
  },
  theme: {
    type: String,
    default: 'gray', // 'gray' or 'white'
    validator: (value) => ['gray', 'white'].includes(value)
  }
})
</script>

<style scoped>
.page-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.layout-gray {
  background-color: #f5f5f5;
}

.layout-white {
  background-color: #ffffff;
}

.page-content {
  flex: 1;
  display: flex;
  flex-direction: column;
}
</style>
