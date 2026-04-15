<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import PageHeader from '../components/PageHeader.vue'

const route = useRoute()
const id    = computed(() => route.params.id)

const news    = ref(null)
const loading = ref(true)

onMounted(async () => {
  try {
    const token   = localStorage.getItem('token') || ''
    const headers = token ? { Authorization: `Bearer ${token}` } : {}
    const res     = await fetch(`/api/v1/announcements/${id.value}`, { headers })
    if (res.ok) {
      const data = await res.json()
      news.value = data.data || data
    }
  } catch {}
  loading.value = false
})
</script>

<template>
  <div class="announcement-detail-page">
    <PageHeader title="公告詳情" backTo="/?openMenu=news" />

    <div v-if="loading" class="content-container" style="color:#999;text-align:center">載入中...</div>
    <div v-else-if="news" class="content-container">
      <h1 class="news-title">{{ news.title }}</h1>
      <p class="news-date">{{ (news.published_at || news.date || '').substring(0, 16) }}</p>
      <div class="news-body">
        <template v-for="(line, index) in (news.content || '').split('\n')" :key="index">
          {{ line }}
          <br />
        </template>
      </div>
    </div>
    <div v-else class="content-container not-found">
      <p>找不到該公告。</p>
    </div>
  </div>
</template>

<style scoped>
.announcement-detail-page {
  background-color: #fff;
  min-height: 100vh;
}

.content-container {
  padding: 1.5rem;
  text-align: left;
}

.news-title {
  font-size: 1.25rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
  line-height: 1.4;
  color: #333;
}

.news-date {
  font-size: 0.85rem;
  color: #888;
  margin-bottom: 1.5rem;
}

.news-body {
  font-size: 1rem;
  line-height: 1.6;
  color: #444;
  word-wrap: break-word; /* 處理長單字換行 */
}

.not-found {
    text-align: center;
    color: #888;
    margin-top: 2rem;
}
</style>