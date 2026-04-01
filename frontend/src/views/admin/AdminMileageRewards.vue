<template>
  <div class="panel">
    <div class="panel-header">
      <span class="panel-title"></span>
      <div style="display:flex;gap:0.5rem;align-items:center">
        <!-- 詳情頁版面樣式：切換即套用 -->
        <select v-model="rewardDetailDisplayStyle" class="f-input" style="width:auto;min-width:130px" @change="saveRewardDisplayStyle">
          <option value="default">垂直列表</option>
          <option value="horizontal">水平並排</option>
        </select>
        <button class="btn btn-primary" @click="openRewardProductForm()"><Plus :size="14" />新增商品</button>
        <button class="btn btn-outline" :disabled="loadingRewardProducts" @click="loadRewardProducts"><RefreshCw :size="14" /></button>
      </div>
    </div>
    <div class="table-wrap">
      <div v-if="loadingRewardProducts" class="state-msg">載入中...</div>
      <div v-else-if="rewardProductsList.length === 0" class="state-msg">尚無商品</div>
      <table v-else class="data-table">
        <thead><tr><th>所屬對換項目</th><th>商品名稱</th><th>圖片</th><th>售僳</th><th>里程回饋</th><th>里程點數</th><th>庫存</th><th>狀態</th><th>排序</th><th>操作</th></tr></thead>
        <tbody>
          <tr v-for="item in rewardProductsList" :key="item.id">
            <td>{{ itemName(item.mileage_item_id) }}</td>
            <td class="td-name">{{ item.name }}</td>
            <td>
              <img v-if="item.image_url" :src="item.image_url" style="width:48px;height:48px;object-fit:contain;border-radius:6px;border:1px solid #e2e8f0" />
              <span v-else style="color:#94a3b8">—</span>
            </td>
            <td class="td-num">${{ Number(item.price).toLocaleString() }}</td>
            <td class="td-num">${{ Number(item.mileage_amount).toLocaleString() }}</td>
            <td class="td-num">{{ item.miles_points ?? 0 }}</td>
            <td class="td-num">{{ item.stock }}</td>
            <td><span :class="['badge', item.is_active == 1 ? 'badge-green' : 'badge-red']">{{ item.is_active == 1 ? '啟用' : '停用' }}</span></td>
            <td>{{ item.sort_order }}</td>
            <td class="td-actions">
              <button class="btn btn-sm btn-outline" @click="openRewardProductForm(item)">編輯</button>
              <button class="btn btn-sm btn-danger" @click="deleteRewardProduct(item.id)">刪除</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- 里程回饋商品 Modal -->
  <div v-if="rewardProductForm.show" class="modal-overlay" @click.self="rewardProductForm.show = false">
    <div class="modal-box" style="max-width:480px">
      <div class="modal-hd">
        <span>{{ rewardProductForm.id ? '編輯商品' : '新增商品' }}</span>
        <button class="modal-x" @click="rewardProductForm.show = false">✕</button>
      </div>
      <div class="modal-bd">
        <label class="f-label">所屬對換項目 *</label>
        <select v-model.number="rewardProductForm.mileage_item_id" class="f-input">
          <option value="" disabled>-- 請選擇對換項目 --</option>
          <option v-for="mi in mileageItemsList" :key="mi.id" :value="mi.id">{{ mi.name }}</option>
        </select>
        <label class="f-label" style="margin-top:0.75rem">商品名稱 *</label>
        <input v-model="rewardProductForm.name" class="f-input" placeholder="例：APIVITA 面膜 (8mlX12)" />
        <label class="f-label" style="margin-top:0.75rem">商品圖片 URL</label>
        <div style="display:flex;gap:0.5rem;align-items:center">
          <input v-model="rewardProductForm.image_url" class="f-input" placeholder="例：/product-1.png" />
          <label style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;background:#f0f9ff;border:1px solid #bae6fd;border-radius:6px;cursor:pointer;font-size:0.82rem;color:#0369a1;white-space:nowrap">
            {{ rewardImgUploading ? '上傳中...' : '上傳圖片' }}
            <input type="file" accept="image/*" style="display:none" :disabled="rewardImgUploading" @change="uploadRewardProductImage" />
          </label>
        </div>
        <img v-if="rewardProductForm.image_url" :src="rewardProductForm.image_url" style="width:80px;height:80px;object-fit:contain;border-radius:8px;border:1px solid #e2e8f0;margin-top:0.5rem" />
        <div style="display:flex;gap:1rem;margin-top:0.75rem">
          <div style="flex:1">
            <label class="f-label">售價 ($)</label>
            <input v-model.number="rewardProductForm.price" class="f-input" type="number" min="0" step="0.01" placeholder="1880" />
          </div>
          <div style="flex:1">
            <label class="f-label">里程回饋 ($)</label>
            <input v-model.number="rewardProductForm.mileage_amount" class="f-input" type="number" min="0" step="0.01" placeholder="188" />
          </div>
          <div style="flex:1">
            <label class="f-label">里程點數</label>
            <input v-model.number="rewardProductForm.miles_points" class="f-input" type="number" min="0" placeholder="0" />
          </div>
        </div>
        <div style="display:flex;gap:1rem;margin-top:0.75rem;align-items:flex-end">
          <div style="flex:1">
            <label class="f-label">庫存數量</label>
            <input v-model.number="rewardProductForm.stock" class="f-input" type="number" min="0" placeholder="100" />
          </div>
          <div style="flex:1">
            <label class="f-label">排序</label>
            <input v-model.number="rewardProductForm.sort_order" class="f-input" type="number" min="0" />
          </div>
          <label style="display:flex;align-items:center;gap:6px;cursor:pointer;padding-bottom:0.5rem">
            <input type="checkbox" v-model="rewardProductForm.is_active" :true-value="1" :false-value="0" style="width:16px;height:16px" />
            <span class="f-label" style="margin:0">啟用</span>
          </label>
        </div>
      </div>
      <div class="modal-ft">
        <button class="btn btn-outline" @click="rewardProductForm.show = false">取消</button>
        <button class="btn btn-primary" :disabled="rewardProductForm.submitting" @click="submitRewardProduct">{{ rewardProductForm.submitting ? '處理中...' : (rewardProductForm.id ? '儲存' : '新增') }}</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RefreshCw, Plus } from 'lucide-vue-next'
import { fileService } from '../../services/FileService'

const mileageItemsList      = ref([])
const rewardProductsList    = ref([])
const loadingRewardProducts = ref(false)
const rewardDetailDisplayStyle = ref('default')
const rewardImgUploading       = ref(false)
const rewardProductForm = ref({ show: false, id: null, mileage_item_id: '', name: '', image_url: '', price: 0, mileage_amount: 0, miles_points: 0, stock: 0, is_active: 1, sort_order: 0, submitting: false })

const itemName = (id) => {
  const found = mileageItemsList.value.find(mi => Number(mi.id) === Number(id))
  return found ? found.name : ('-')
}

const loadMileageItems = async () => {
  try {
    const res  = await fetch('/api/v1/admin-panel/mileage-items')
    const data = await res.json()
    mileageItemsList.value = data.items || []
  } catch {}
}

const loadRewardProducts = async () => {
  loadingRewardProducts.value = true
  try {
    const res  = await fetch('/api/v1/admin-panel/mileage-reward-products')
    const data = await res.json()
    rewardProductsList.value = data.items || []
  } finally { loadingRewardProducts.value = false }
}

const loadRewardDisplayStyle = async () => {
  try {
    const res  = await fetch('/api/v1/config/reward_detail_display_style')
    const data = await res.json()
    rewardDetailDisplayStyle.value = data.value || 'default'
  } catch {}
}

const saveRewardDisplayStyle = async () => {
  try {
    await fetch('/api/v1/admin-panel/config/reward_detail_display_style', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ value: rewardDetailDisplayStyle.value }),
    })
  } catch {}
}

const openRewardProductForm = (item = null) => {
  rewardImgUploading.value = false
  if (item) {
    rewardProductForm.value = { show: true, submitting: false, id: item.id, mileage_item_id: item.mileage_item_id || '', name: item.name, image_url: item.image_url || '', price: Number(item.price), mileage_amount: Number(item.mileage_amount), miles_points: Number(item.miles_points || 0), stock: Number(item.stock), is_active: Number(item.is_active), sort_order: item.sort_order || 0 }
  } else {
    const defaultItemId = mileageItemsList.value.length > 0 ? mileageItemsList.value[0].id : ''
    rewardProductForm.value = { show: true, submitting: false, id: null, mileage_item_id: defaultItemId, name: '', image_url: '', price: 0, mileage_amount: 0, miles_points: 0, stock: 0, is_active: 1, sort_order: 0 }
  }
}

const uploadRewardProductImage = async (e) => {
  const file = e.target.files?.[0]
  if (!file) return
  rewardImgUploading.value = true
  try {
    const result = await fileService.upload(file, 'general')
    rewardProductForm.value.image_url = result.url
  } catch { alert('圖片上傳失敗') }
  finally { rewardImgUploading.value = false; e.target.value = '' }
}

const submitRewardProduct = async () => {
  const f = rewardProductForm.value
  if (!f.name.trim()) { alert('請填寫商品名稱'); return }
  f.submitting = true
  try {
    const url    = f.id ? `/api/v1/admin-panel/mileage-reward-products/${f.id}` : '/api/v1/admin-panel/mileage-reward-products'
    const method = f.id ? 'PUT' : 'POST'
    const res    = await fetch(url, { method, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ mileage_item_id: f.mileage_item_id || null, name: f.name, image_url: f.image_url || null, price: f.price, mileage_amount: f.mileage_amount, miles_points: f.miles_points, stock: f.stock, is_active: f.is_active, sort_order: f.sort_order }) })
    if (!res.ok) { const d = await res.json(); alert(d.message || '操作失敗'); return }
    f.show = false
    await loadRewardProducts()
  } finally { f.submitting = false }
}

const deleteRewardProduct = async (id) => {
  if (!confirm('確定要刪除此商品嗎？')) return
  await fetch(`/api/v1/admin-panel/mileage-reward-products/${id}`, { method: 'DELETE' })
  await loadRewardProducts()
}

onMounted(() => {
  loadMileageItems()
  loadRewardDisplayStyle()
  loadRewardProducts()
})
</script>
