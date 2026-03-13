<template>
  <div class="auth-header">
    <h2 v-if="title" class="auth-title">{{ title }}</h2>
    <p v-if="description" class="auth-desc">{{ description }}</p>
  </div>
</template>

<script setup>
defineProps({
  title: String,
  description: String
})
</script>

<style scoped>
.auth-header {
  margin-bottom: 2rem;
  text-align: center;
}

.auth-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #333;
  margin-bottom: 1rem;
}

.auth-desc {
  font-size: 1rem;
  color: #666;
  line-height: 1.6;
  margin-bottom: 0;
}
</style>