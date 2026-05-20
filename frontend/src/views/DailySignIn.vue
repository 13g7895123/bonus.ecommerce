<template>
  <PageLayout title="每日簽到" back-to="/settings" theme="white">
    <div class="sign-page">
      <div class="calendar-card">
        <h2 class="card-title">{{ campaign.title }}</h2>
        <div class="reward-summary">
          <div class="reward-chip">每日簽到 +{{ campaign.base_reward_miles || 0 }} 里程點數</div>
          <div v-if="campaign.streak_days > 0 && campaign.streak_bonus_miles > 0" class="reward-chip reward-chip--accent">
            連續 {{ campaign.streak_days }} 天加贈 {{ campaign.streak_bonus_miles }} 點
          </div>
          <div class="reward-note">漏簽無法補簽，目前連續 {{ currentStreakDays }} 天</div>
        </div>
        <div class="weekday-grid">
          <div v-for="label in weekdayLabels" :key="label" class="weekday-label">{{ label }}</div>
        </div>
        <div class="week-grid">
          <div
            v-for="day in calendarCells"
            :key="day.key"
            class="day-cell"
            :class="{ signed: day.signed, today: day.isToday, placeholder: !day.date }"
          >
            <template v-if="day.date">
              <div class="day-number">{{ day.label }}</div>
              <div class="day-circle">
                <span v-if="day.signed">✓</span>
              </div>
            </template>
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
import { apiFetch } from '../utils/apiFetch'

const toast = useToast()
const loading = ref(false)
const campaign = ref({ title: '', base_reward_miles: 0, streak_days: 0, streak_bonus_miles: 0 })
const today = ref('')
const signedDates = ref([])
const currentStreakDays = ref(0)
const weekdayLabels = ['日', '一', '二', '三', '四', '五', '六']

const calendarCells = computed(() => {
  const year = Number(campaign.value.year || new Date().getFullYear())
  const month = Number(campaign.value.month || new Date().getMonth() + 1)
  const count = new Date(year, month, 0).getDate()
  const firstWeekday = new Date(year, month - 1, 1).getDay()
  const placeholders = Array.from({ length: firstWeekday }, (_, index) => ({
    key: `placeholder-${index}`,
    date: '',
    label: '',
    signed: false,
    isToday: false,
  }))

  const days = Array.from({ length: count }, (_, index) => {
    const day = index + 1
    const date = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`
    return {
      key: date,
      date,
      label: `${String(month).padStart(2, '0')}/${String(day).padStart(2, '0')}`,
      signed: signedDates.value.includes(date),
      isToday: date === today.value,
    }
  })

  return [...placeholders, ...days]
})

const hasSignedToday = computed(() => signedDates.value.includes(today.value))

const handleApiError = async (res, fallbackMessage) => {
  let message = fallbackMessage

  try {
    const json = await res.json()
    message = json?.message || fallbackMessage
  } catch {}

  throw new Error(message)
}

const loadStatus = async () => {
  loading.value = true
  try {
    const res = await apiFetch('/api/v1/me/sign-in', { auth: true })
    if (!res.ok) await handleApiError(res, '載入簽到資料失敗')
    const json = await res.json()
    const data = json.data || {}
    campaign.value = data.campaign || { title: '', base_reward_miles: 0, streak_days: 0, streak_bonus_miles: 0 }
    today.value = data.today || ''
    signedDates.value = data.signed_dates || []
    currentStreakDays.value = Number(data.current_streak_days || 0)
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
    const res = await apiFetch('/api/v1/me/sign-in', {
      method: 'POST',
      auth: true,
    })
    if (!res.ok) await handleApiError(res, '簽到失敗')
    const json = await res.json()
    if (!signedDates.value.includes(json.data?.sign_in_date)) {
      signedDates.value = [...signedDates.value, json.data?.sign_in_date]
    }
    currentStreakDays.value = Number(json.data?.streak_days || currentStreakDays.value)
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

.reward-summary {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.reward-chip {
  display: inline-flex;
  align-items: center;
  min-height: 32px;
  padding: 0.35rem 0.75rem;
  border-radius: 999px;
  background: #f3f4f6;
  color: #374151;
  font-size: 0.82rem;
  font-weight: 700;
}

.reward-chip--accent {
  background: #fff1f2;
  color: #be123c;
}

.reward-note {
  width: 100%;
  font-size: 0.8rem;
  color: #6b7280;
}

.weekday-grid,
.week-grid {
  display: grid;
  grid-template-columns: repeat(7, minmax(0, 1fr));
}

.weekday-grid {
  margin-bottom: 0.5rem;
}

.weekday-label {
  text-align: center;
  font-size: 0.78rem;
  font-weight: 700;
  color: #8a8a8a;
}

.week-grid {
  gap: 0.75rem 0.5rem;
}

.day-cell {
  min-height: 62px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.4rem;
}

.day-cell.placeholder {
  pointer-events: none;
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
