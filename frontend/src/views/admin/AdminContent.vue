<template>
  <div class="panel">
    <div class="panel-header"><span class="panel-title"></span></div>

    <div class="content-block">
      <div class="content-block-header">
        <div>
          <div class="cb-title">銀卡進階說明（Skywards 頁面卡片）</div>
          <div class="cb-desc">顯示在「達到銀卡」卡片的說明文字</div>
        </div>
        <div style="display:flex;gap:0.5rem">
          <button class="btn btn-outline" @click="silverCardPreview = !silverCardPreview"><Eye :size="14" />{{ silverCardPreview ? '隱藏預覽' : '預覽卡片' }}</button>
          <button class="btn btn-primary" :disabled="savingSilver" @click="saveSilverCard"><Save :size="14" />{{ savingSilver ? '儲存中...' : '儲存' }}</button>
        </div>
      </div>
      <textarea v-model="silverCardDesc" class="cfg-textarea" rows="3" placeholder="例：在2027年2月28日之前賺取25,000級哩程數,或再搭乘 25 次合格航班"></textarea>
      <div v-if="silverCardPreview" class="preview-box">
        <div class="preview-label">預覽效果</div>
        <div class="mock-card">
          <img src="/go-silver.png" alt="Silver" class="mock-card-img" />
          <div class="mock-card-body">
            <h4 class="mock-card-title">達到 <span style="color:#a0a0a0">銀卡</span></h4>
            <p class="mock-card-desc">{{ silverCardDesc || '(空白)' }}</p>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- 網站條款 -->
  <div class="content-block">
    <div class="content-block-header">
      <div>
        <div class="cb-title">網站使用條款</div>
        <div class="cb-desc">顯示在「條款與條件」頁面的內容（富文本）</div>
      </div>
      <button class="btn btn-primary" :disabled="savingTerms" @click="saveTerms">
        <Save :size="14" />{{ savingTerms ? '儲存中...' : '儲存' }}
      </button>
    </div>
    <RichTextEditor v-model="termsHtml" />
  </div>

  <div class="divider"></div>

  <!-- 隱私政策 -->
  <div class="content-block">
    <div class="content-block-header">
      <div>
        <div class="cb-title">隱私政策</div>
        <div class="cb-desc">顯示在「隱私政策」頁面的內容（富文本）</div>
      </div>
      <button class="btn btn-primary" :disabled="savingPrivacy" @click="savePrivacy">
        <Save :size="14" />{{ savingPrivacy ? '儲存中...' : '儲存' }}
      </button>
    </div>
    <RichTextEditor v-model="privacyHtml" />
  </div>

  <div class="divider"></div>

  <!-- Skywards 等級升級閾值 -->
  <div class="content-block">
    <div class="content-block-header">
      <div>
        <div class="cb-title">Skywards 等級升級里程閾值</div>
        <div class="cb-desc">設定使用者累積多少里程後自動升級會員等級（兌換里程代碼時觸發）</div>
      </div>
      <button class="btn btn-primary" :disabled="savingTiers" @click="saveTierThresholds">
        <Save :size="14" />{{ savingTiers ? '儲存中...' : '儲存' }}
      </button>
    </div>
    <div class="tier-grid">
      <div class="tier-row">
        <label class="tier-label">銀卡 (Silver) 最低里程</label>
        <input class="f-input" type="number" min="0" v-model.number="tierSilver" placeholder="例：25000" />
      </div>
      <div class="tier-row">
        <label class="tier-label">金卡 (Gold) 最低里程</label>
        <input class="f-input" type="number" min="0" v-model.number="tierGold" placeholder="例：50000" />
      </div>
      <div class="tier-row">
        <label class="tier-label">白金卡 (Platinum) 最低里程</label>
        <input class="f-input" type="number" min="0" v-model.number="tierPlatinum" placeholder="例：100000" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Eye, Save } from 'lucide-vue-next'
import RichTextEditor from '../../components/admin/RichTextEditor.vue'
import { apiFetch } from '../../utils/apiFetch'

const silverCardDesc       = ref('')
const silverCardPreview    = ref(false)
const savingSilver         = ref(false)
const termsHtml            = ref('')
const savingTerms          = ref(false)
const privacyHtml          = ref('')
const savingPrivacy        = ref(false)
const tierSilver           = ref(25000)
const tierGold             = ref(50000)
const tierPlatinum         = ref(100000)
const savingTiers          = ref(false)

const loadContentConfigs = async () => {
  try {
    const [r1, r2, r3, r4, r5, r6] = await Promise.all([
      apiFetch('/api/v1/config/skywards_silver_card_desc'),
      apiFetch('/api/v1/config/terms_html'),
      apiFetch('/api/v1/config/privacy_html'),
      apiFetch('/api/v1/config/tier_silver_miles'),
      apiFetch('/api/v1/config/tier_gold_miles'),
      apiFetch('/api/v1/config/tier_platinum_miles'),
    ])
    silverCardDesc.value = (await r1.json()).value || ''
    termsHtml.value      = (await r2.json()).value || ''
    privacyHtml.value    = (await r3.json()).value || ''
    tierSilver.value     = parseInt((await r4.json()).value) || 25000
    tierGold.value       = parseInt((await r5.json()).value) || 50000
    tierPlatinum.value   = parseInt((await r6.json()).value) || 100000
  } catch {}
}

const saveSilverCard = async () => {
  savingSilver.value = true
  try {
    await apiFetch('/api/v1/admin-panel/config/skywards_silver_card_desc', { method: 'POST', auth: true, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ value: silverCardDesc.value }) })
    alert('儲存成功')
  } finally { savingSilver.value = false }
}

const saveTerms = async () => {
  savingTerms.value = true
  try {
    await apiFetch('/api/v1/admin-panel/config/terms_html', { method: 'POST', auth: true, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ value: termsHtml.value }) })
    alert('儲存成功')
  } finally { savingTerms.value = false }
}

const savePrivacy = async () => {
  savingPrivacy.value = true
  try {
    await apiFetch('/api/v1/admin-panel/config/privacy_html', { method: 'POST', auth: true, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ value: privacyHtml.value }) })
    alert('儲存成功')
  } finally { savingPrivacy.value = false }
}

const saveTierThresholds = async () => {
  savingTiers.value = true
  try {
    await Promise.all([
      apiFetch('/api/v1/admin-panel/config/tier_silver_miles', { method: 'POST', auth: true, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ value: String(tierSilver.value) }) }),
      apiFetch('/api/v1/admin-panel/config/tier_gold_miles', { method: 'POST', auth: true, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ value: String(tierGold.value) }) }),
      apiFetch('/api/v1/admin-panel/config/tier_platinum_miles', { method: 'POST', auth: true, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ value: String(tierPlatinum.value) }) }),
    ])
    alert('等級閾值儲存成功')
  } finally { savingTiers.value = false }
}

onMounted(loadContentConfigs)
</script>

<style scoped>
.tier-grid { display: flex; flex-direction: column; gap: 0.75rem; margin-top: 0.5rem; }
.tier-row { display: flex; align-items: center; gap: 1rem; }
.tier-label { width: 220px; font-size: 14px; color: #374151; flex-shrink: 0; }
.tier-row .f-input { flex: 1; max-width: 200px; }
</style>
