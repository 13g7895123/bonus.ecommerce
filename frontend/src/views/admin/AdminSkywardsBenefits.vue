<template>
  <div class="panel">
    <div class="panel-header">
      <span class="panel-title">Skywards 權益頁設定</span>
      <button class="btn btn-outline" :disabled="loading" @click="loadItems">
        <RefreshCw :size="14" />{{ loading ? '載入中...' : '重新整理' }}
      </button>
      <button class="btn btn-primary" @click="openForm()">
        <Plus :size="14" />新增等級內容
      </button>
    </div>

    <div v-if="loading" class="state-msg">載入中...</div>
    <div v-else-if="items.length === 0" class="state-msg">尚未建立權益內容</div>
    <div v-else class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th style="width:76px">圖片</th>
            <th>適用等級</th>
            <th>標題</th>
            <th>內容摘要</th>
            <th class="td-num">排序</th>
            <th>狀態</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in items" :key="item.id">
            <td>
              <div class="thumb-box">
                <img v-if="item.image_url" :src="item.image_url" :alt="item.label || 'Skywards 權益圖片'" />
                <ImageIcon v-else :size="18" />
              </div>
            </td>
            <td><span class="badge badge-blue">{{ tierLabel(item.tier) }}</span></td>
            <td class="td-name">{{ item.label || '未命名區塊' }}</td>
            <td class="summary-cell">{{ contentText(item.content) || '尚無文字內容' }}</td>
            <td class="td-num">{{ Number(item.sort_order || 0) }}</td>
            <td>
              <span :class="['badge', Number(item.is_active) ? 'badge-green' : 'badge-gray']">
                {{ Number(item.is_active) ? '顯示' : '隱藏' }}
              </span>
            </td>
            <td>
              <div class="td-actions">
                <button class="btn btn-outline btn-sm" @click="openForm(item)"><Pencil :size="13" />編輯</button>
                <button class="btn btn-danger btn-sm" @click="deleteItem(item)"><Trash2 :size="13" />刪除</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div v-if="form.show" class="modal-overlay" @click.self="closeForm">
    <div class="modal-box sky-benefit-modal">
      <div class="modal-hd">
        <span>{{ form.id ? '編輯 Skywards 等級權益' : '新增 Skywards 等級權益' }}</span>
        <button class="modal-x" @click="closeForm">×</button>
      </div>
      <div class="modal-bd">
        <div class="form-grid">
          <div>
            <label class="f-label">適用等級</label>
            <select v-model="form.tier" class="f-input">
              <option v-for="option in tierOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
            </select>
          </div>
          <div>
            <label class="f-label">標題</label>
            <input v-model="form.label" class="f-input" placeholder="例：銀卡權益" />
          </div>
          <div>
            <label class="f-label">排序</label>
            <input v-model.number="form.sort_order" class="f-input" type="number" min="0" />
          </div>
          <div>
            <label class="f-label">狀態</label>
            <select v-model.number="form.is_active" class="f-input">
              <option :value="1">顯示</option>
              <option :value="0">隱藏</option>
            </select>
          </div>
        </div>

        <div>
          <label class="f-label">上方圖片</label>
          <div class="image-control-row">
            <input v-model="form.image_url" class="f-input" placeholder="圖片 URL，或使用右側按鈕上傳" />
            <label class="btn btn-outline upload-btn" :class="{ disabled: uploading }">
              <Upload :size="14" />{{ uploading ? '上傳中...' : '上傳圖片' }}
              <input type="file" accept="image/*" :disabled="uploading" @change="uploadBenefitImage" />
            </label>
            <button v-if="form.image_url" type="button" class="btn btn-outline" @click="form.image_url = ''">
              <X :size="14" />移除
            </button>
          </div>
          <div v-if="form.image_url" class="image-preview-box">
            <img :src="form.image_url" alt="Skywards 權益圖片預覽" />
          </div>
        </div>

        <div>
          <label class="f-label">下方文字（富文本）</label>
          <RichTextEditor v-model="form.content" />
        </div>

        <div class="preview-box page-preview">
          <div class="preview-label">前台顯示預覽</div>
          <div class="benefit-page-preview">
            <img v-if="form.image_url" :src="form.image_url" alt="Skywards 權益圖片預覽" class="preview-hero-img" />
            <div class="preview-copy">
              <h3 v-if="form.label">{{ form.label }}</h3>
              <div v-if="form.content" v-html="form.content" class="preview-rich-content"></div>
              <p v-else class="preview-empty">尚無文字內容</p>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-ft">
        <button class="btn btn-outline" :disabled="form.submitting" @click="closeForm">取消</button>
        <button class="btn btn-primary" :disabled="form.submitting" @click="saveItem">
          <Save :size="14" />{{ form.submitting ? '儲存中...' : '儲存' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Image as ImageIcon, Pencil, Plus, RefreshCw, Save, Trash2, Upload, X } from 'lucide-vue-next'
import RichTextEditor from '../../components/admin/RichTextEditor.vue'
import { fileService } from '../../services/FileService'

const items = ref([])
const loading = ref(false)
const uploading = ref(false)

const tierOptions = [
  { value: 'regular', label: '藍卡' },
  { value: 'silver', label: '銀卡' },
  { value: 'gold', label: '金卡' },
  { value: 'platinum', label: '白金卡' },
]

const blankForm = () => ({
  show: false,
  id: null,
  tier: 'regular',
  label: '',
  image_url: '',
  content: '',
  sort_order: 0,
  is_active: 1,
  submitting: false,
})

const form = ref(blankForm())

const tierLabel = (tier) => tierOptions.find(option => option.value === tier)?.label || '未設定'

const contentText = (html) => {
  if (!html) return ''
  const parsed = new DOMParser().parseFromString(html, 'text/html')
  return (parsed.body.textContent || '').replace(/\s+/g, ' ').trim()
}

const normalizeItem = (item) => ({
  ...item,
  tier: item.tier || 'regular',
  image_url: item.image_url || '',
  sort_order: Number(item.sort_order || 0),
  is_active: Number(item.is_active ?? 1),
})

const loadItems = async () => {
  loading.value = true
  try {
    const response = await fetch('/api/v1/admin-panel/skywards-benefits')
    const data = await response.json()
    items.value = (data.items || data.data?.items || []).map(normalizeItem)
  } catch {
    alert('載入失敗，請稍後再試')
  } finally {
    loading.value = false
  }
}

const openForm = (item = null) => {
  uploading.value = false
  form.value = item
    ? {
        show: true,
        id: item.id,
        tier: item.tier || 'regular',
        label: item.label || '',
        image_url: item.image_url || '',
        content: item.content || '',
        sort_order: Number(item.sort_order || 0),
        is_active: Number(item.is_active ?? 1),
        submitting: false,
      }
    : { ...blankForm(), show: true }
}

const closeForm = () => {
  if (form.value.submitting) return
  form.value = blankForm()
}

const uploadBenefitImage = async (event) => {
  const file = event.target.files?.[0]
  if (!file) return
  uploading.value = true
  try {
    const result = await fileService.upload(file, 'general')
    form.value.image_url = result.url
  } catch {
    alert('圖片上傳失敗，請稍後再試')
  } finally {
    uploading.value = false
    event.target.value = ''
  }
}

const saveItem = async () => {
  form.value.submitting = true
  try {
    const payload = {
      tier: form.value.tier || 'regular',
      label: form.value.label || null,
      image_url: form.value.image_url || null,
      content: form.value.content || '',
      sort_order: Number(form.value.sort_order || 0),
      is_active: Number(form.value.is_active ?? 1),
    }
    const method = form.value.id ? 'PUT' : 'POST'
    const url = form.value.id
      ? `/api/v1/admin-panel/skywards-benefits/${form.value.id}`
      : '/api/v1/admin-panel/skywards-benefits'
    const response = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    })
    if (!response.ok) throw new Error('save failed')
    await loadItems()
    closeForm()
    alert('儲存成功')
  } catch {
    alert('儲存失敗，請稍後再試')
  } finally {
    form.value.submitting = false
  }
}

const deleteItem = async (item) => {
  if (!confirm(`確定要刪除「${item.label || '未命名區塊'}」嗎？`)) return
  try {
    const response = await fetch(`/api/v1/admin-panel/skywards-benefits/${item.id}`, { method: 'DELETE' })
    if (!response.ok) throw new Error('delete failed')
    await loadItems()
  } catch {
    alert('刪除失敗，請稍後再試')
  }
}

onMounted(loadItems)
</script>

<style scoped>
.thumb-box {
  width: 56px;
  height: 42px;
  border-radius: 6px;
  border: 1px solid #e2e8f0;
  background: #f8fafc;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  color: #94a3b8;
}

.thumb-box img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.summary-cell {
  max-width: 360px;
  color: #64748b;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.sky-benefit-modal {
  max-width: 880px;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.9rem;
}

.image-control-row {
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto auto;
  gap: 0.5rem;
  align-items: center;
}

.upload-btn {
  position: relative;
  overflow: hidden;
}

.upload-btn input {
  display: none;
}

.upload-btn.disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.image-preview-box {
  width: 100%;
  max-width: 360px;
  margin-top: 0.75rem;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: #f8fafc;
  overflow: hidden;
}

.image-preview-box img {
  width: 100%;
  max-height: 180px;
  object-fit: contain;
  display: block;
}

.page-preview {
  margin-top: 0;
}

.benefit-page-preview {
  max-width: 520px;
  margin: 0 auto;
  text-align: left;
}

.preview-hero-img {
  width: 100%;
  max-width: 520px;
  max-height: 280px;
  object-fit: contain;
  display: block;
  margin: 0 auto 1rem;
  background: #f1f5f9;
  border-radius: 8px;
}

.preview-copy h3 {
  margin: 0 0 0.75rem;
  font-size: 1.1rem;
  color: #1e293b;
}

.preview-rich-content {
  color: #334155;
  line-height: 1.65;
  word-break: break-word;
  overflow-wrap: break-word;
}

.preview-rich-content :deep(p) { margin: 0 0 0.75rem; }
.preview-rich-content :deep(ul) { padding-left: 1.5em; list-style: disc; }
.preview-rich-content :deep(ol) { padding-left: 1.5em; list-style: decimal; }

.preview-empty {
  color: #94a3b8;
  margin: 0;
}

@media (max-width: 720px) {
  .form-grid,
  .image-control-row {
    grid-template-columns: 1fr;
  }
}
</style>