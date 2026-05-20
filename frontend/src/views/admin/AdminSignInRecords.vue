<template>
  <div class="sign-admin-page">
    <section class="hero-card">
      <div class="hero-copy">
        <p class="eyebrow">Daily Sign-In Console</p>
        <h2 class="hero-title">每日簽到活動管理</h2>
        <p class="hero-desc">
          這裡集中管理當月簽到獎勵、連續加碼規則與簽到紀錄。漏簽不可補簽，實際發點以當日設定為準。
        </p>
      </div>
      <div class="hero-actions">
        <button class="btn btn-outline hero-btn" :disabled="loading" @click="loadList">重新整理</button>
      </div>
    </section>

    <section class="metrics-grid">
      <article class="metric-card">
        <span class="metric-label">查詢月份</span>
        <strong class="metric-value">{{ activeMonthLabel }}</strong>
      </article>
      <article class="metric-card">
        <span class="metric-label">簽到筆數</span>
        <strong class="metric-value">{{ summary.totalSignIns }}</strong>
      </article>
      <article class="metric-card">
        <span class="metric-label">參與人數</span>
        <strong class="metric-value">{{ summary.uniqueUsers }}</strong>
      </article>
      <article class="metric-card">
        <span class="metric-label">發放點數</span>
        <strong class="metric-value">{{ summary.totalRewardMiles }}</strong>
      </article>
      <article class="metric-card metric-card--accent">
        <span class="metric-label">連續加碼次數</span>
        <strong class="metric-value">{{ summary.streakBonusCount }}</strong>
      </article>
    </section>

    <section class="workspace-grid">
      <article class="editor-card">
        <div class="card-head">
          <div>
            <h3 class="card-title">活動設定</h3>
            <p class="card-subtitle">設定當前年月的每日里程點數與連續簽到加碼。</p>
          </div>
          <span :class="['status-pill', Number(campaignForm.is_active) === 1 ? 'status-pill--active' : 'status-pill--paused']">
            {{ Number(campaignForm.is_active) === 1 ? '啟用中' : '已停用' }}
          </span>
        </div>

        <div class="period-strip">
          <div class="period-pill">
            <span class="period-label">活動月份</span>
            <strong>{{ activeMonthLabel }}</strong>
          </div>
          <label class="toggle-row">
            <input v-model="campaignForm.is_active" type="checkbox" :true-value="1" :false-value="0" />
            <span>啟用本月活動</span>
          </label>
        </div>

        <div class="form-grid">
          <label class="field-block field-block--wide">
            <span class="field-label">活動標題</span>
            <input v-model="campaignForm.title" class="f-input field-input" placeholder="例：2026 年 5 月每日簽到活動" />
          </label>

          <label class="field-block">
            <span class="field-label">每日獎勵點數</span>
            <input v-model.number="campaignForm.base_reward_miles" class="f-input field-input" type="number" min="0" placeholder="0" />
          </label>

          <label class="field-block">
            <span class="field-label">連續天數門檻</span>
            <input v-model.number="campaignForm.streak_days" class="f-input field-input" type="number" min="0" placeholder="0" />
          </label>

          <label class="field-block">
            <span class="field-label">額外獎勵點數</span>
            <input v-model.number="campaignForm.streak_bonus_miles" class="f-input field-input" type="number" min="0" placeholder="0" />
          </label>
        </div>

        <div class="rule-preview">
          <div class="rule-item">
            <span class="rule-kicker">規則預覽</span>
            <strong>每日簽到 +{{ campaignForm.base_reward_miles || 0 }} 點</strong>
          </div>
          <div class="rule-item">
            <span class="rule-kicker">連續加碼</span>
            <strong v-if="campaignForm.streak_days > 0 && campaignForm.streak_bonus_miles > 0">
              每連續 {{ campaignForm.streak_days }} 天，加送 {{ campaignForm.streak_bonus_miles }} 點
            </strong>
            <strong v-else>目前未啟用</strong>
          </div>
          <div class="rule-note">漏簽無法補簽，連續天數中斷後將重新計算。</div>
        </div>

        <div class="card-foot">
          <button class="btn btn-primary save-btn" :disabled="campaignSaving" @click="saveCampaignConfig">
            {{ campaignSaving ? '儲存中...' : '儲存活動設定' }}
          </button>
        </div>
      </article>

      <article class="insight-card">
        <div class="card-head">
          <div>
            <h3 class="card-title">本月觀察</h3>
            <p class="card-subtitle">快速查看活動設定與紀錄表現是否合理。</p>
          </div>
        </div>

        <div class="insight-list">
          <div class="insight-row">
            <span>平均每次發點</span>
            <strong>{{ summary.averageRewardMiles }}</strong>
          </div>
          <div class="insight-row">
            <span>最高連續天數</span>
            <strong>{{ summary.maxStreakDays }}</strong>
          </div>
          <div class="insight-row">
            <span>目前活動狀態</span>
            <strong>{{ Number(campaignForm.is_active) === 1 ? '可簽到' : '已關閉' }}</strong>
          </div>
        </div>

        <div class="insight-callout">
          <p class="callout-title">管理提醒</p>
          <p class="callout-body">
            若要調整當月規則，請先確認前台說明與後台設定一致，避免會員在活動期間看到不同的獎勵文案。
          </p>
        </div>
      </article>
    </section>

    <section class="records-card">
      <div class="card-head card-head--records">
        <div>
          <h3 class="card-title">簽到紀錄</h3>
          <p class="card-subtitle">依月份與關鍵字篩選會員簽到資料、里程發放與連續加碼狀態。</p>
        </div>
      </div>

      <div class="filters-panel">
        <label class="filter-block">
          <span class="filter-label">年份</span>
          <input v-model="filters.year" class="f-input field-input" type="number" placeholder="年" @keyup.enter="loadList" />
        </label>
        <label class="filter-block">
          <span class="filter-label">月份</span>
          <input v-model="filters.month" class="f-input field-input" type="number" min="1" max="12" placeholder="月" @keyup.enter="loadList" />
        </label>
        <label class="filter-block filter-block--search">
          <span class="filter-label">關鍵字</span>
          <input v-model="filters.keyword" class="f-input field-input" placeholder="搜尋 Email 或姓名" @keyup.enter="loadList" />
        </label>
        <div class="filter-actions">
          <button class="btn btn-outline" @click="resetFilters">清除</button>
          <button class="btn btn-primary" :disabled="loading" @click="loadList">查詢</button>
        </div>
      </div>

      <div class="records-shell">
        <div v-if="loading" class="state-panel">載入中...</div>
        <div v-else-if="items.length === 0" class="state-panel state-panel--empty">這個月份目前沒有簽到紀錄</div>
        <div v-else class="records-table-wrap">
          <table class="records-table">
            <thead>
              <tr>
                <th>會員</th>
                <th>活動</th>
                <th>簽到日期</th>
                <th>獎勵</th>
                <th>連續狀態</th>
                <th>建立時間</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <td>
                  <div class="user-cell">
                    <strong>{{ item.full_name || '未填姓名' }}</strong>
                    <span>{{ item.email }}</span>
                  </div>
                </td>
                <td>
                  <div class="campaign-cell">
                    <span class="campaign-name">{{ item.title }}</span>
                    <span class="campaign-id">#{{ item.id }}</span>
                  </div>
                </td>
                <td>{{ item.sign_in_date }}</td>
                <td>
                  <span class="reward-badge">+{{ item.awarded_miles }}</span>
                </td>
                <td>
                  <div class="streak-cell">
                    <strong>{{ item.streak_day_count }} 天</strong>
                    <span v-if="Number(item.is_streak_bonus) === 1" class="streak-tag">加碼觸發</span>
                    <span v-else class="streak-tag streak-tag--muted">一般簽到</span>
                  </div>
                </td>
                <td>{{ item.created_at }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
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

const activeMonthLabel = computed(() => {
  const year = Number(filters.value.year || now.getFullYear())
  const month = String(filters.value.month || now.getMonth() + 1).padStart(2, '0')
  return `${year}/${month}`
})

const summary = computed(() => {
  const rewardMiles = items.value.reduce((sum, item) => sum + Number(item.awarded_miles || 0), 0)
  const streakBonusCount = items.value.filter(item => Number(item.is_streak_bonus) === 1).length
  const userIds = new Set(items.value.map(item => Number(item.user_id)).filter(Boolean))
  const maxStreakDays = items.value.reduce((max, item) => Math.max(max, Number(item.streak_day_count || 0)), 0)
  const averageRewardMiles = items.value.length ? Math.round(rewardMiles / items.value.length) : 0

  return {
    totalSignIns: items.value.length,
    uniqueUsers: userIds.size,
    totalRewardMiles: rewardMiles,
    streakBonusCount,
    maxStreakDays,
    averageRewardMiles,
  }
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

const resetFilters = () => {
  filters.value = {
    year: now.getFullYear(),
    month: now.getMonth() + 1,
    keyword: '',
  }
  loadList()
}

onMounted(loadList)
</script>

<style scoped>
.sign-admin-page {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.hero-card,
.metric-card,
.editor-card,
.insight-card,
.records-card {
  border-radius: 24px;
  background: #ffffff;
  box-shadow: 0 20px 45px rgba(15, 23, 42, 0.06);
  border: 1px solid rgba(148, 163, 184, 0.18);
}

.hero-card {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  padding: 1.5rem;
  background:
    radial-gradient(circle at top left, rgba(249, 115, 22, 0.12), transparent 30%),
    linear-gradient(135deg, #ffffff 0%, #f8fafc 55%, #eef6ff 100%);
}

.eyebrow {
  margin: 0 0 0.5rem;
  font-size: 0.76rem;
  font-weight: 800;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: #c2410c;
}

.hero-title {
  margin: 0;
  font-size: 1.8rem;
  font-weight: 800;
  color: #0f172a;
}

.hero-desc {
  max-width: 780px;
  margin: 0.65rem 0 0;
  line-height: 1.7;
  color: #475569;
}

.hero-actions {
  display: flex;
  align-items: flex-start;
}

.hero-btn {
  min-width: 104px;
}

.metrics-grid {
  display: grid;
  grid-template-columns: repeat(5, minmax(0, 1fr));
  gap: 1rem;
}

.metric-card {
  padding: 1.1rem 1.2rem;
}

.metric-card--accent {
  background: linear-gradient(135deg, #fff7ed, #ffffff);
}

.metric-label {
  display: block;
  margin-bottom: 0.5rem;
  font-size: 0.78rem;
  font-weight: 700;
  letter-spacing: 0.05em;
  color: #64748b;
}

.metric-value {
  font-size: 1.6rem;
  font-weight: 800;
  color: #0f172a;
}

.workspace-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.7fr) minmax(300px, 0.9fr);
  gap: 1rem;
}

.editor-card,
.insight-card,
.records-card {
  padding: 1.35rem;
}

.card-head {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 1rem;
}

.card-head--records {
  margin-bottom: 1.15rem;
}

.card-title {
  margin: 0;
  font-size: 1.08rem;
  font-weight: 800;
  color: #0f172a;
}

.card-subtitle {
  margin: 0.35rem 0 0;
  color: #64748b;
  line-height: 1.6;
  font-size: 0.9rem;
}

.status-pill {
  display: inline-flex;
  align-items: center;
  border-radius: 999px;
  padding: 0.35rem 0.7rem;
  font-size: 0.75rem;
  font-weight: 800;
}

.status-pill--active {
  background: #ecfdf5;
  color: #047857;
}

.status-pill--paused {
  background: #fef2f2;
  color: #b91c1c;
}

.period-strip {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  align-items: center;
  padding: 0.95rem 1rem;
  border-radius: 18px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  margin-bottom: 1rem;
}

.period-pill {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
}

.period-label,
.field-label,
.filter-label,
.rule-kicker {
  font-size: 0.76rem;
  font-weight: 800;
  letter-spacing: 0.04em;
  color: #64748b;
}

.toggle-row {
  display: inline-flex;
  align-items: center;
  gap: 0.55rem;
  font-size: 0.9rem;
  font-weight: 700;
  color: #334155;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 0.9rem;
}

.field-block,
.filter-block {
  display: flex;
  flex-direction: column;
  gap: 0.42rem;
}

.field-block--wide {
  grid-column: 1 / -1;
}

.field-input {
  min-height: 46px;
  border-radius: 14px;
  border-color: #dbe4ee;
  background: #ffffff;
}

.rule-preview {
  margin-top: 1rem;
  padding: 1rem;
  border-radius: 18px;
  background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
  border: 1px solid #e2e8f0;
}

.rule-item {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  align-items: center;
  padding: 0.55rem 0;
  border-bottom: 1px dashed #e2e8f0;
}

.rule-item:last-of-type {
  border-bottom: none;
}

.rule-note {
  margin-top: 0.8rem;
  color: #64748b;
  font-size: 0.85rem;
}

.card-foot {
  margin-top: 1rem;
}

.save-btn {
  min-width: 140px;
}

.insight-list {
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
}

.insight-row {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  align-items: center;
  padding: 0.9rem 1rem;
  border-radius: 16px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  color: #334155;
}

.insight-row strong {
  color: #0f172a;
}

.insight-callout {
  margin-top: 1rem;
  padding: 1rem;
  border-radius: 18px;
  background: linear-gradient(135deg, #0f172a, #1e293b);
  color: #e2e8f0;
}

.callout-title {
  margin: 0 0 0.45rem;
  font-size: 0.86rem;
  font-weight: 800;
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

.callout-body {
  margin: 0;
  line-height: 1.7;
  font-size: 0.9rem;
}

.filters-panel {
  display: grid;
  grid-template-columns: 120px 120px minmax(240px, 1fr) auto;
  gap: 0.85rem;
  align-items: end;
  padding: 1rem;
  border-radius: 18px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
}

.filter-block--search {
  min-width: 0;
}

.filter-actions {
  display: flex;
  gap: 0.6rem;
}

.records-shell {
  margin-top: 1rem;
}

.state-panel {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 220px;
  border-radius: 18px;
  background: #f8fafc;
  border: 1px dashed #cbd5e1;
  color: #64748b;
  font-weight: 700;
}

.state-panel--empty {
  background: linear-gradient(180deg, #fff, #f8fafc);
}

.records-table-wrap {
  overflow: auto;
  border-radius: 18px;
  border: 1px solid #e2e8f0;
}

.records-table {
  width: 100%;
  border-collapse: collapse;
  background: #ffffff;
}

.records-table th,
.records-table td {
  padding: 0.95rem 1rem;
  text-align: left;
  border-bottom: 1px solid #edf2f7;
  vertical-align: middle;
}

.records-table th {
  position: sticky;
  top: 0;
  background: #f8fafc;
  font-size: 0.78rem;
  font-weight: 800;
  letter-spacing: 0.04em;
  color: #64748b;
  z-index: 1;
}

.records-table tbody tr:hover {
  background: #fcfdff;
}

.user-cell,
.campaign-cell,
.streak-cell {
  display: flex;
  flex-direction: column;
  gap: 0.28rem;
}

.user-cell strong,
.campaign-name,
.streak-cell strong {
  color: #0f172a;
}

.user-cell span,
.campaign-id {
  color: #64748b;
  font-size: 0.84rem;
}

.reward-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 72px;
  padding: 0.35rem 0.7rem;
  border-radius: 999px;
  background: #eff6ff;
  color: #1d4ed8;
  font-weight: 800;
}

.streak-tag {
  display: inline-flex;
  align-items: center;
  width: fit-content;
  border-radius: 999px;
  padding: 0.2rem 0.55rem;
  background: #fff7ed;
  color: #c2410c;
  font-size: 0.75rem;
  font-weight: 800;
}

.streak-tag--muted {
  background: #f1f5f9;
  color: #475569;
}

@media (max-width: 1200px) {
  .metrics-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .workspace-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 840px) {
  .hero-card,
  .editor-card,
  .insight-card,
  .records-card {
    padding: 1.1rem;
    border-radius: 20px;
  }

  .hero-card,
  .period-strip,
  .rule-item {
    flex-direction: column;
    align-items: flex-start;
  }

  .form-grid,
  .filters-panel,
  .metrics-grid {
    grid-template-columns: 1fr;
  }

  .filter-actions {
    width: 100%;
  }

  .filter-actions .btn {
    flex: 1;
  }
}
</style>
