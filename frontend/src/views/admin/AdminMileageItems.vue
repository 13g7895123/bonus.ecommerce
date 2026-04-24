<template>
  <div class="panel">
    <div class="panel-header">
      <span class="panel-title"></span>
      <div style="display:flex;gap:0.5rem">
        <button class="btn btn-primary" @click="openMileageForm()"><Plus :size="14" />新增項目</button>
        <button class="btn btn-outline" :disabled="loadingMileageItems" @click="loadMileageItems"><RefreshCw :size="14" /></button>
      </div>
    </div>
    <div class="table-wrap">
      <div v-if="loadingMileageItems" class="state-msg">載入中...</div>
      <div v-else-if="mileageItemsList.length === 0" class="state-msg">尚無項目</div>
      <table v-else class="data-table">
        <thead><tr><th>名稱</th><th style="text-align:center">Logo</th><th>精選</th><th>里程回饋(%)</th><th>狀態</th><th>排序</th><th>操作</th></tr></thead>
        <tbody>
          <tr v-for="item in mileageItemsList" :key="item.id">
            <td>
              <div class="td-name">{{ item.name }}</div>
              <div class="td-sub">{{ item.short_desc }}</div>
            </td>
            <td style="text-align:center">
              <img v-if="item.logo_url" :src="item.logo_url" style="width:32px;height:32px;object-fit:contain;border-radius:6px" />
              <div v-else class="logo-chip" :style="{ backgroundColor: item.logo_color, margin:'0 auto' }">{{ item.logo_letter }}</div>
            </td>
            <td><span :class="['badge', item.is_featured == 1 ? 'badge-yellow' : 'badge-gray']">{{ item.is_featured == 1 ? '精選' : '-' }}</span></td>
            <td class="td-num">{{ Number(item.mileage_amount ?? 0).toFixed(1) }}%</td>
            <td><span :class="['badge', item.is_active == 1 ? 'badge-green' : 'badge-red']">{{ item.is_active == 1 ? '啟用' : '停用' }}</span></td>
            <td>{{ item.sort_order }}</td>
            <td class="td-actions">
              <button class="btn btn-sm btn-outline" @click="openMileageForm(item)">編輯</button>
              <button class="btn btn-sm btn-danger" @click="deleteMileageItem(item.id)">刪除</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- 里程項目 Modal -->
  <div v-if="mileageForm.show" class="modal-overlay" @click.self="mileageForm.show = false">
    <div class="modal-box" style="max-width:560px;max-height:90vh;overflow-y:auto">
      <div class="modal-hd">
        <span>{{ mileageForm.id ? '編輯項目' : '新增項目' }}</span>
        <button class="modal-x" @click="mileageForm.show = false">✕</button>
      </div>
      <div class="modal-bd">
        <label class="f-label">名稱 *</label>
        <input v-model="mileageForm.name" class="f-input" placeholder="例：Skywards Miles Mall" />
        <label class="f-label" style="margin-top:0.75rem">簡短說明</label>
        <input v-model="mileageForm.short_desc" class="f-input" placeholder="列表副標題" />
        <div style="margin-top:0.75rem">
          <label class="f-label">Logo 模式</label>
          <div style="display:flex;gap:0.5rem;margin-bottom:0.75rem">
            <button type="button" @click="switchLogoMode('letter')" :style="{ padding:'4px 14px', borderRadius:'6px', border:'1px solid #e2e8f0', cursor:'pointer', background: mileageForm.logo_mode==='letter' ? '#3b82f6' : '#f8fafc', color: mileageForm.logo_mode==='letter' ? '#fff' : '#374151' }">字母</button>
            <button type="button" @click="switchLogoMode('image')" :style="{ padding:'4px 14px', borderRadius:'6px', border:'1px solid #e2e8f0', cursor:'pointer', background: mileageForm.logo_mode==='image' ? '#3b82f6' : '#f8fafc', color: mileageForm.logo_mode==='image' ? '#fff' : '#374151' }">上傳圖片</button>
            <button type="button" @click="switchLogoMode('pick')" :style="{ padding:'4px 14px', borderRadius:'6px', border:'1px solid #e2e8f0', cursor:'pointer', background: mileageForm.logo_mode==='pick' ? '#3b82f6' : '#f8fafc', color: mileageForm.logo_mode==='pick' ? '#fff' : '#374151' }">選擇已上傳</button>
          </div>
          <div v-if="mileageForm.logo_mode==='letter'" style="display:flex;gap:1rem">
            <div style="flex:1">
              <label class="f-label">Logo 字母</label>
              <input v-model="mileageForm.logo_letter" class="f-input" placeholder="S" maxlength="5" />
            </div>
            <div style="flex:1">
              <label class="f-label">Logo 背景色</label>
              <div style="display:flex;gap:0.5rem;align-items:center">
                <input v-model="mileageForm.logo_color" type="color" style="width:36px;height:36px;padding:2px;border:1px solid #e2e8f0;border-radius:6px;cursor:pointer" />
                <input v-model="mileageForm.logo_color" class="f-input" placeholder="#ffffff" />
              </div>
            </div>
          </div>
          <div v-else-if="mileageForm.logo_mode==='image'" style="display:flex;align-items:center;gap:1rem">
            <label style="display:inline-flex;align-items:center;gap:8px;padding:6px 14px;background:#f0f9ff;border:1px solid #bae6fd;border-radius:6px;cursor:pointer;font-size:0.85rem;color:#0369a1">
              <span>{{ logoUploading ? '上傳中...' : '選擇圖片' }}</span>
              <input type="file" accept="image/*" style="display:none" :disabled="logoUploading" @change="uploadLogoImage" />
            </label>
            <img v-if="mileageForm.logo_url" :src="mileageForm.logo_url" style="width:48px;height:48px;object-fit:contain;border-radius:8px;border:1px solid #e2e8f0" />
            <span v-else style="color:#999;font-size:0.85rem">尚未上傳圖片</span>
          </div>
          <div v-else style="margin-top:0.25rem">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem">
              <span style="font-size:0.8rem;color:#64748b">點選圖片即可套用</span>
              <button type="button" @click="loadLogoGallery" style="font-size:0.75rem;padding:2px 10px;border-radius:5px;border:1px solid #e2e8f0;background:#f8fafc;cursor:pointer;color:#374151">{{ logoGalleryLoading ? '載入中...' : '重新整理' }}</button>
            </div>
            <div v-if="logoGalleryLoading" style="text-align:center;padding:1rem;color:#94a3b8;font-size:0.85rem">載入中...</div>
            <template v-else>
              <div style="font-size:0.75rem;color:#94a3b8;margin-bottom:4px">內建圖片</div>
              <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(72px,1fr));gap:0.5rem;padding:4px;border:1px solid #e2e8f0;border-radius:8px;margin-bottom:0.6rem">
                <div
                  v-for="img in STATIC_LOGO_IMAGES" :key="img.id"
                  @click="mileageForm.logo_url = img.url"
                  :title="img.original_name"
                  :style="{ border: mileageForm.logo_url===img.url ? '2px solid #3b82f6' : '2px solid #e2e8f0', borderRadius:'8px', overflow:'hidden', cursor:'pointer', background: mileageForm.logo_url===img.url ? '#eff6ff' : '#f8fafc', flexShrink:0 }"
                >
                  <img :src="img.url" :alt="img.original_name" style="width:100%;height:64px;object-fit:contain;display:block" />
                  <div style="font-size:0.62rem;color:#64748b;padding:2px 4px;text-align:center;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ img.original_name }}</div>
                </div>
              </div>
              <div style="font-size:0.75rem;color:#94a3b8;margin-bottom:4px">已上傳圖片</div>
              <div v-if="logoGallery.length === 0" style="text-align:center;padding:0.75rem;color:#94a3b8;font-size:0.85rem;border:1px solid #e2e8f0;border-radius:8px">尚無已上傳圖片，請先使用「上傳圖片」模式</div>
              <div v-else style="display:grid;grid-template-columns:repeat(auto-fill,minmax(72px,1fr));gap:0.5rem;max-height:180px;overflow-y:auto;padding:4px;border:1px solid #e2e8f0;border-radius:8px">
                <div
                  v-for="img in logoGallery" :key="img.id"
                  @click="mileageForm.logo_url = img.url"
                  :title="img.original_name"
                  :style="{ border: mileageForm.logo_url===img.url ? '2px solid #3b82f6' : '2px solid #e2e8f0', borderRadius:'8px', overflow:'hidden', cursor:'pointer', background: mileageForm.logo_url===img.url ? '#eff6ff' : '#f8fafc', flexShrink:0 }"
                >
                  <img :src="img.url" :alt="img.original_name" style="width:100%;height:64px;object-fit:contain;display:block" />
                  <div style="font-size:0.62rem;color:#64748b;padding:2px 4px;text-align:center;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ img.original_name }}</div>
                </div>
              </div>
            </template>
            <div v-if="mileageForm.logo_url" style="margin-top:0.5rem;display:flex;align-items:center;gap:0.5rem">
              <img :src="mileageForm.logo_url" style="width:36px;height:36px;object-fit:contain;border-radius:6px;border:1px solid #e2e8f0" />
              <span style="font-size:0.8rem;color:#059669">✓ 已選擇</span>
              <button type="button" @click="mileageForm.logo_url=''" style="font-size:0.75rem;padding:2px 8px;border-radius:5px;border:1px solid #fca5a5;background:#fef2f2;cursor:pointer;color:#dc2626">取消選擇</button>
            </div>
          </div>
        </div>
        <div style="margin-top:0.75rem">
          <label class="f-label">里程回饋 (%)</label>
          <input v-model.number="mileageForm.mileage_amount" class="f-input" type="number" min="0" max="100" step="0.1" placeholder="10" />
        </div>
        <div style="display:flex;gap:1rem;margin-top:0.75rem;align-items:flex-end;flex-wrap:wrap">
          <label style="display:flex;align-items:center;gap:6px;cursor:pointer">
            <input type="checkbox" v-model="mileageForm.is_featured" :true-value="1" :false-value="0" style="width:16px;height:16px" />
            <span class="f-label" style="margin:0">精選</span>
          </label>
          <div style="flex:1;min-width:120px">
            <label class="f-label">精選標籤</label>
            <input v-model="mileageForm.featured_label" class="f-input" :disabled="!mileageForm.is_featured" placeholder="精選" />
          </div>
          <label style="display:flex;align-items:center;gap:6px;cursor:pointer">
            <input type="checkbox" v-model="mileageForm.is_active" :true-value="1" :false-value="0" style="width:16px;height:16px" />
            <span class="f-label" style="margin:0">啟用</span>
          </label>
          <div style="flex:1;min-width:80px">
            <label class="f-label">排序</label>
            <input v-model.number="mileageForm.sort_order" class="f-input" type="number" min="0" />
          </div>
        </div>
      </div>
      <div class="modal-ft">
        <button class="btn btn-outline" @click="mileageForm.show = false">取消</button>
        <button class="btn btn-primary" :disabled="mileageForm.submitting" @click="submitMileageItem">{{ mileageForm.submitting ? '處理中...' : (mileageForm.id ? '儲存' : '新增') }}</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RefreshCw, Plus } from 'lucide-vue-next'
import { fileService } from '../../services/FileService'

const mileageItemsList    = ref([])
const loadingMileageItems = ref(false)
const logoUploading       = ref(false)
const logoGallery         = ref([])
const logoGalleryLoading  = ref(false)
const mileageForm = ref({ show: false, id: null, name: '', short_desc: '', logo_letter: 'S', logo_color: '#ffffff', logo_url: '', logo_mode: 'letter', mileage_amount: 0, is_featured: 0, featured_label: '精選', is_active: 1, sort_order: 0, submitting: false })

const STATIC_LOGO_IMAGES = [
  { id: 'static-logo',     url: '/logo.png',        original_name: 'logo.png' },
  { id: 'static-coin',    url: '/coin.png',         original_name: 'coin.png' },
  { id: 'static-plane',   url: '/plane.png',        original_name: 'plane.png' },
  { id: 'static-product1',url: '/product-1.png',    original_name: 'product-1.png' },
  { id: 'static-product2',url: '/product-2.png',    original_name: 'product-2.png' },
  { id: 'static-gosilver',url: '/go-silver.png',    original_name: 'go-silver.png' },
]

const loadMileageItems = async () => {
  loadingMileageItems.value = true
  try {
    const res  = await fetch('/api/v1/admin-panel/mileage-items')
    const data = await res.json()
    mileageItemsList.value = data.items || []
  } finally { loadingMileageItems.value = false }
}

const openMileageForm = (item = null) => {
  logoUploading.value = false
  if (item) {
    mileageForm.value = { show: true, submitting: false, id: item.id, name: item.name, short_desc: item.short_desc || '', logo_letter: item.logo_letter || 'S', logo_color: item.logo_color || '#ffffff', logo_url: item.logo_url || '', logo_mode: item.logo_url ? 'image' : 'letter', mileage_amount: Number(item.mileage_amount || 0), is_featured: Number(item.is_featured), featured_label: item.featured_label || '精選', is_active: Number(item.is_active), sort_order: item.sort_order || 0 }
  } else {
    mileageForm.value = { show: true, submitting: false, id: null, name: '', short_desc: '', logo_letter: 'S', logo_color: '#ffffff', logo_url: '', logo_mode: 'letter', mileage_amount: 0, is_featured: 0, featured_label: '精選', is_active: 1, sort_order: 0 }
  }
}

const uploadLogoImage = async (e) => {
  const file = e.target.files?.[0]
  if (!file) return
  logoUploading.value = true
  try {
    const result = await fileService.upload(file, 'general')
    mileageForm.value.logo_url = result.url
  } catch { alert('圖片上傳失敗') }
  finally { logoUploading.value = false; e.target.value = '' }
}

const loadLogoGallery = async () => {
  logoGalleryLoading.value = true
  try {
    const files     = await fileService.getMyFiles('general')
    const uploaded  = (files || []).filter(f => f.mime_type && f.mime_type.startsWith('image/'))
    const allKnown  = new Set([...uploaded.map(f => f.url), ...STATIC_LOGO_IMAGES.map(f => f.url)])
    const fromItems = mileageItemsList.value.filter(i => i.logo_url && !allKnown.has(i.logo_url)).map(i => ({ id: `item-${i.id}`, url: i.logo_url, original_name: i.name }))
    logoGallery.value = [...uploaded, ...fromItems]
  } catch { logoGallery.value = [] }
  finally { logoGalleryLoading.value = false }
}

const switchLogoMode = (mode) => {
  mileageForm.value.logo_mode = mode
  if (mode === 'pick' && logoGallery.value.length === 0) loadLogoGallery()
}

const submitMileageItem = async () => {
  const f = mileageForm.value
  if (!f.name.trim()) { alert('請填寫名稱'); return }
  f.submitting = true
  try {
    const url    = f.id ? `/api/v1/admin-panel/mileage-items/${f.id}` : '/api/v1/admin-panel/mileage-items'
    const method = f.id ? 'PUT' : 'POST'
    const logoUrl = (f.logo_mode === 'image' || f.logo_mode === 'pick') ? (f.logo_url || null) : null
    const res    = await fetch(url, { method, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ name: f.name, short_desc: f.short_desc, logo_letter: f.logo_letter, logo_color: f.logo_color, logo_url: logoUrl, mileage_amount: f.mileage_amount, is_featured: f.is_featured, featured_label: f.featured_label, is_active: f.is_active, sort_order: f.sort_order }) })
    if (!res.ok) { const d = await res.json(); alert(d.message || '操作失敗'); return }
    f.show = false
    await loadMileageItems()
  } finally { f.submitting = false }
}

const deleteMileageItem = async (id) => {
  if (!confirm('確定要刪除此項目嗎？')) return
  await fetch(`/api/v1/admin-panel/mileage-items/${id}`, { method: 'DELETE' })
  await loadMileageItems()
}

onMounted(loadMileageItems)
</script>
