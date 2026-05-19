<template>
  <PageLayout :title="campaign.title || '簽到活動'" back-to="/settings" theme="white">
    <div class="sign-page">
      <div class="calendar-card">
        <h2 class="card-title">{{ campaign.title }}</h2>
        <div class="week-grid">
          <div v-for="day in days" :key="day.date" class="day-cell" :class="{ signed: day.signed, today: day.isToday }">
            <div class="day-number">{{ day.day }}</div>
            <div class="day-circle">
              <span v-if="day.signed">✓</span>
            </div>
          </div>
        </div>
        <button class="sign-btn" :disabled="loading || hasSignedToday" @click="signInNow">
          {{ hasSignedToday ? '今日已簽到' : '立即簽到' }}
        </button>
      </div>
    </div>
  </PageLayout>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import PageLayout from '../components/PageLayout.vue'
import { useToast } from '../composables/useToast'

const toast = useToast()
const loading = ref(false)
const campaign = ref({ title: '' })
const today = ref('')
const signedDates = ref([])

const days = computed(() => {
  const year = Number(campaign.value.year || new Date().getFullYear())
  const month = Number(campaign.value.month || new Date().getMonth() + 1)
  const count = new Date(year, month, 0).getDate()

  return Array.from({ length: count }, (_, index) => {
    const day = index + 1
    const date = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`
    return {
      date,
      day,
      signed: signedDates.value.includes(date),
      isToday: date === today.value,
    }
  })
})

const hasSignedToday = computed(() => signedDates.value.includes(today.value))

const authHeaders = () => {
  const token = localStorage.getItem('token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

const loadStatus = async () => {
  loading.value = true
  try {
    const res = await fetch('/api/v1/me/sign-in', { headers: authHeaders() })
    if (!res.ok) throw new Error('load failed')
    const json = await res.json()
    const data = json.data || {}
    campaign.value = data.campaign || { title: '' }
    today.value = data.today || ''
    signedDates.value = data.signed_dates || []
  } catch {
    toast.error('載入簽到資料失敗')
  } finally {
    loading.value = false
  }
}

const signInNow = async () => {
  if (hasSignedToday.value) return
  loading.value = true
  try {
    const res = await fetch('/api/v1/me/sign-in', {
      method: 'POST',
      headers: authHeaders(),
    })
    const json = await res.json()
    if (!res.ok) throw new Error(json.message || 'error')
    if (!signedDates.value.includes(json.data?.sign_in_date)) {
      signedDates.value = [...signedDates.value, json.data?.sign_in_date]
    }
    toast.success(json.message || '簽到成功')
  } catch (error) {
    toast.error(error.message || '簽到失敗')
  } finally {
    loading.value = false
  }
}

onMounted(loadStatus)
</script>

<style scoped>
.sign-page {
  padding: 1rem;
  background: #f5f5f5;
  min-height: calc(100vh - 56px);
}

.calendar-card {
  background: #fff;
  border-radius: 18px;
  padding: 1.25rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
}

.card-title {
  margin: 0 0 1rem;
  font-size: 1.1rem;
  font-weight: 800;
  color: #222;
}

.week-grid {
  display: grid;
  grid-template-columns: repeat(7, minmax(0, 1fr));
  gap: 0.75rem 0.5rem;
}

.day-cell {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.4rem;
}

.day-number {
  font-size: 0.82rem;
  color: #666;
}

.day-circle {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  border: 2px solid #d6d6d6;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 0.95rem;
  font-weight: 700;
}

.day-cell.signed .day-circle {
  background: #d71921;
  border-color: #d71921;
}

.day-cell.today .day-number {
  color: #d71921;
  font-weight: 700;
}

.sign-btn {
  width: 100%;
  margin-top: 1.25rem;
  border: none;
  border-radius: 999px;
  background: #d71921;
  color: #fff;
  font-size: 1rem;
  font-weight: 700;
  padding: 0.9rem 1rem;
}

.sign-btn:disabled {
  background: #c4c4c4;
}
</style>
