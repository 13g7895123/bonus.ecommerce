<template>
  <div class="page-header" :class="{ 'no-border': !bordered }">
    <a href="#" v-if="useHistoryBack" @click.prevent="goBack" class="back-btn">
      <span class="arrow-left"></span>
    </a>
    <router-link v-else-if="backTo" :to="backTo" class="back-btn">
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
import { useRouter } from 'vue-router'

const router = useRouter()
const props = defineProps({
  title: { type: String, required: true },
  backTo: { type: String, default: '' },
  useHistoryBack: { type: Boolean, default: false },
  bordered: { type: Boolean, default: true }
})

const goBack = () => {
    if (window.history.state && window.history.state.back) {
        router.back();
    } else {
        // Fallback or do nothing if no history. 
        // Or if props.backTo is provided as a fallback? 
        // The user specifically asked for history back to work.
        // If there is no history, we might want to go to home or just stay?
        // Let's assume standard router.back() is what is wanted.
        router.back();
    }
}
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

.page-header.no-border {
  border-bottom: none;
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
  font-size: 1.5rem;
  font-weight: 900;
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
