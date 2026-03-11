<template>
  <div class="logo-header">
    <router-link :to="backTo" class="back-btn">
      <span class="arrow-left"></span>
    </router-link>
    <div class="logo-center">
      <img src="/logo.png" alt="Logo" />
    </div>
  </div>
</template>

<script setup>
defineProps({
  backTo: { type: String, required: true },
})
</script>

<style scoped>
.logo-header {
  display: flex;
  align-items: center;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #eee;
  background-color: #ffffff;
  position: relative;
}

.back-btn {
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 0.5rem;
  display: flex;
  align-items: center;
  text-decoration: none;
  color: #000;
  z-index: 1;
}

.arrow-left {
  display: block;
  width: 12px;
  height: 12px;
  border-left: 2px solid #333;
  border-bottom: 2px solid #333;
  transform: rotate(45deg);
}

.logo-center {
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
}

.logo-center img {
  height: 32px;
}
</style>
