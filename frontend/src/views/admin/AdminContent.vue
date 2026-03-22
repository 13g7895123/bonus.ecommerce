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

    <div class="divider"></div>

    <div class="content-block">
      <div class="content-block-header">
        <div>
          <div class="cb-title">升級規則 Modal 內容（富文本）</div>
          <div class="cb-desc">點擊「檢視您的權益」後彈出的 Modal 內容</div>
        </div>
        <div style="display:flex;gap:0.5rem">
          <button class="btn btn-outline" @click="benefitsModalPreview = !benefitsModalPreview"><Eye :size="14" />{{ benefitsModalPreview ? '隱藏預覽' : '預覽Modal' }}</button>
          <button class="btn btn-primary" :disabled="savingBenefitsHtml" @click="saveBenefitsHtml"><Save :size="14" />{{ savingBenefitsHtml ? '儲存中...' : '儲存' }}</button>
        </div>
      </div>
      <RichTextEditor v-model="benefitsHtml" />
      <div v-if="benefitsModalPreview" class="preview-box" style="margin-top:1rem">
        <div class="preview-label">Modal 預覽</div>
        <div class="mock-modal">
          <div class="mock-modal-header">
            <h3 class="mock-modal-title">升級規則</h3>
            <button class="mock-modal-close">✕</button>
          </div>
          <div class="mock-modal-body" v-html="benefitsHtml || '<p style=\'color:#999\'>(尚無內容)</p>'"></div>
          <button class="mock-modal-confirm">我知道了</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Eye, Save } from 'lucide-vue-next'
import RichTextEditor from '../../components/admin/RichTextEditor.vue'

const silverCardDesc       = ref('')
const silverCardPreview    = ref(false)
const savingSilver         = ref(false)
const benefitsHtml         = ref('')
const benefitsModalPreview = ref(false)
const savingBenefitsHtml   = ref(false)

const loadContentConfigs = async () => {
  try {
    const [r1, r2] = await Promise.all([
      fetch('/api/v1/config/skywards_silver_card_desc'),
      fetch('/api/v1/config/skywards_benefits_html'),
    ])
    silverCardDesc.value = (await r1.json()).value || ''
    benefitsHtml.value   = (await r2.json()).value || ''
  } catch {}
}

const saveSilverCard = async () => {
  savingSilver.value = true
  try {
    await fetch('/api/v1/admin-panel/config/skywards_silver_card_desc', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ value: silverCardDesc.value }) })
    alert('儲存成功')
  } finally { savingSilver.value = false }
}

const saveBenefitsHtml = async () => {
  savingBenefitsHtml.value = true
  try {
    await fetch('/api/v1/admin-panel/config/skywards_benefits_html', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ value: benefitsHtml.value }) })
    alert('儲存成功')
  } finally { savingBenefitsHtml.value = false }
}

onMounted(loadContentConfigs)
</script>
