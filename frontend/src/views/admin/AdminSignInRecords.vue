<template>
  <div class="panel">
    <div class="panel-header">
      <span class="panel-title">每日簽到紀錄</span>
      <div style="display:flex;gap:0.5rem">
        <button class="btn btn-outline" :disabled="loading" @click="loadList">重新整理</button>
      </div>
    </div>

    <div class="content-block">
      <div class="content-block-header">
        <div>
          <div class="cb-title">活動設定</div>
          <div class="cb-desc">設定當前年月的每日簽到點數與連續簽到加碼規則</div>
        </div>
        <button class="btn btn-primary" :disabled="campaignSaving" @click="saveCampaignConfig">
          {{ campaignSaving ? '儲存中...' : '儲存設定' }}
        </button>
      </div>
      <div class="filters">
        <input v-model="campaignForm.title" class="f-input" placeholder="活動標題" />
        <input v-model.number="campaignForm.base_reward_miles" class="f-input" type="number" min="0" placeholder="每日點數" />
        <input v-model.number="campaignForm.streak_days" class="f-input" type="number" min="0" placeholder="連續幾天" />
        <input v-model.number="campaignForm.streak_bonus_miles" class="f-input" type="number" min="0" placeholder="額外獎勵點數" />
        <label style="display:flex;align-items:center;gap:6px;white-space:nowrap">
          <input v-model="campaignForm.is_active" type="checkbox" :true-value="1" :false-value="0" />
          <span>啟用活動</span>
        </label>
      </div>
      <div class="campaign-note">規則：漏簽無法補簽；當連續天數達到設定值時，當天會額外加送一次獎勵。</div>
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
            <th>獎勵點數</th>
            <th>連續天數</th>
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
            <td>{{ item.awarded_miles }}</td>
            <td>{{ item.streak_day_count }}<span v-if="Number(item.is_streak_bonus) === 1"> +加碼</span></td>
            <td>{{ item.created_at }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { apiFetch } from '../../utils/apiFetch'

const loading = ref(false)
const items = ref([])
const campaignSaving = ref(false)
const now = new Date()
const filters = ref({
  year: now.getFullYear(),
  month: now.getMonth() + 1,
  keyword: '',
})
const campaignForm = ref({
  title: '',
  base_reward_miles: 0,
  streak_days: 0,
  streak_bonus_miles: 0,
  is_active: 1,
})

const loadCampaignConfig = async () => {
  const params = new URLSearchParams({
    year: String(filters.value.year),
    month: String(filters.value.month),
  })
  const res = await apiFetch(`/api/v1/admin-panel/sign-in-campaign?${params.toString()}`, { auth: true })
  const data = await res.json()
  const item = data.item || {}
  campaignForm.value = {
    title: item.title || '',
    base_reward_miles: Number(item.base_reward_miles || 0),
    streak_days: Number(item.streak_days || 0),
    streak_bonus_miles: Number(item.streak_bonus_miles || 0),
    is_active: Number(item.is_active ?? 1),
  }
}

const loadList = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams({
      year: String(filters.value.year),
      month: String(filters.value.month),
      keyword: filters.value.keyword || '',
    })
    const res = await apiFetch(`/api/v1/admin-panel/sign-in-records?${params.toString()}`, { auth: true })
    const data = await res.json()
    items.value = data.items || []
    await loadCampaignConfig()
  } finally {
    loading.value = false
  }
}

const saveCampaignConfig = async () => {
  campaignSaving.value = true
  try {
    const res = await apiFetch('/api/v1/admin-panel/sign-in-campaign', {
      method: 'POST',
      auth: true,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        year: filters.value.year,
        month: filters.value.month,
        title: campaignForm.value.title,
        base_reward_miles: campaignForm.value.base_reward_miles,
        streak_days: campaignForm.value.streak_days,
        streak_bonus_miles: campaignForm.value.streak_bonus_miles,
        is_active: campaignForm.value.is_active,
      }),
    })
    const data = await res.json()
    if (!res.ok) {
      alert(data.message || '儲存失敗')
      return
    }
    alert(data.message || '儲存成功')
  } finally {
    campaignSaving.value = false
  }
}

onMounted(loadList)
</script>
