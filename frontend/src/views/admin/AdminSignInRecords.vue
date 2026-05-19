<template>
  <div class="panel">
    <div class="panel-header">
      <span class="panel-title">每日簽到紀錄</span>
      <div style="display:flex;gap:0.5rem">
        <button class="btn btn-outline" :disabled="loading" @click="loadList">重新整理</button>
      </div>
    </div>

    <div class="filters">
      <input v-model="filters.year" class="f-input" type="number" placeholder="年" @keyup.enter="loadList" />
      <input v-model="filters.month" class="f-input" type="number" min="1" max="12" placeholder="月" @keyup.enter="loadList" />
      <input v-model="filters.keyword" class="f-input" placeholder="搜尋 Email 或姓名" @keyup.enter="loadList" />
      <button class="btn btn-primary" @click="loadList">查詢</button>
    </div>

    <div class="table-wrap">
      <div v-if="loading" class="state-msg">載入中...</div>
      <div v-else-if="items.length === 0" class="state-msg">尚無簽到紀錄</div>
      <table v-else class="data-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>活動</th>
            <th>Email</th>
            <th>姓名</th>
            <th>簽到日期</th>
            <th>建立時間</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in items" :key="item.id">
            <td>{{ item.id }}</td>
            <td class="td-name">{{ item.title }}</td>
            <td>{{ item.email }}</td>
            <td>{{ item.full_name || '-' }}</td>
            <td>{{ item.sign_in_date }}</td>
            <td>{{ item.created_at }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'

const loading = ref(false)
const items = ref([])
const now = new Date()
const filters = ref({
  year: now.getFullYear(),
  month: now.getMonth() + 1,
  keyword: '',
})

const loadList = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams({
      year: String(filters.value.year),
      month: String(filters.value.month),
      keyword: filters.value.keyword || '',
    })
    const res = await fetch(`/api/v1/admin-panel/sign-in-records?${params.toString()}`)
    const data = await res.json()
    items.value = data.items || []
  } finally {
    loading.value = false
  }
}

onMounted(loadList)
</script>
