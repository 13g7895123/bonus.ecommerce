<template>
  <div class="page-header">
    <router-link v-if="backTo" :to="backTo" class="back-btn">
      <span class="arrow-left"></span>
    </router-link>
    <div v-else class="header-placeholder"></div>
    <h2 class="header-title">{{ title }}</h2>
    <div class="header-right">
      <slot name="right">
        <div class="header-placeholder"></div>
      </slot>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, required: true },
  backTo: { type: String, default: '' },
})
</script>

<style scoped>
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.5rem;
  background-color: #ffffff;
  border-bottom: 1px solid #eee;
  position: sticky;
  top: 0;
  z-index: 10;
}

.back-btn {
  text-decoration: none;
  color: #333;
  padding: 5px;
  display: flex;
  align-items: center;
}

.arrow-left {
  display: block;
  width: 12px;
  height: 12px;
  border-left: 2px solid #333;
  border-bottom: 2px solid #333;
  transform: rotate(45deg);
}

.header-title {
  color: black;
  font-size: 1.1rem;
  font-weight: 700;
  margin: 0;
}

.header-placeholder {
  width: 22px;
}

.header-right {
  display: flex;
  align-items: center;
}
</style>
