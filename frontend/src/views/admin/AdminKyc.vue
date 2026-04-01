<template>
  <div class="panel">
    <div class="panel-header">
      <span class="panel-title"></span>
      <div class="tab-pills">
        <button v-for="t in kycTabs" :key="t.key" class="pill" :class="{ active: kycTab === t.key }" @click="kycTab = t.key; loadKyc()">{{ t.label }}</button>
      </div>
      <label class="dev-toggle" title="開啟後可對已通過/未通過的記錄執行退回操作">
        <input type="checkbox" v-model="kycDevMode" />
        <span>測試模式</span>
      </label>
      <button class="btn btn-outline" :disabled="loadingKyc" @click="loadKyc"><RefreshCw :size="14" /></button>
    </div>
    <div class="table-wrap">
      <div v-if="loadingKyc" class="state-msg">載入中...</div>
      <div v-else-if="kycList.length === 0" class="state-msg">目前無資料</div>
      <table v-else class="data-table">
        <thead>
          <tr>
            <th>Email</th><th>姓名</th><th>手機</th>
            <th>身分證字號</th><th>代表人姓名</th><th>狀態</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="u in kycList" :key="u.id">
            <td>{{ u.email }}</td>
            <td>{{ u.full_name || '-' }}</td>
            <td>{{ u.phone || '-' }}</td>
            <td>{{ u.verification_data?.idNumber || u.verification_data?.id_number || '-' }}</td>
            <td>{{ u.verification_data?.fullName || u.verification_data?.real_name || '-' }}</td>
            <td><span :class="['badge', kycBadgeClass(u.verify_status)]">{{ kycLabel(u.verify_status) }}</span></td>
            <td class="td-actions">
              <template v-if="kycTab === 'pending'">
                <button class="btn btn-sm btn-green" @click="reviewKyc(u.id, 'approve')">通過</button>
                <button class="btn btn-sm btn-danger" @click="openKycReject(u)">拒絕</button>
              </template>
              <template v-if="kycDevMode && kycTab !== 'pending'">
                <button class="btn btn-sm btn-outline" style="border-color:#f59e0b;color:#f59e0b" @click="reviewKyc(u.id, 'revoke')">↩ 退回</button>
              </template>
              <button class="btn btn-sm btn-outline" style="border-color:#6366f1;color:#6366f1" @click="openKycDetail(u)">詳情</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- KYC 詳情 Modal -->
  <div v-if="kycDetailModal.show" class="modal-overlay" @click.self="kycDetailModal.show = false">
    <div class="modal-box" style="max-width:600px">
      <div class="modal-hd">
        <span>KYC 詳情 — {{ kycDetailModal.user?.email }}</span>
        <button class="modal-x" @click="kycDetailModal.show = false">✕</button>
      </div>
      <div class="modal-bd">
        <div class="kyc-rows">
          <div class="kyc-row"><span class="kyc-lbl">Email</span><span>{{ kycDetailModal.user?.email }}</span></div>
          <div class="kyc-row"><span class="kyc-lbl">姓名</span><span>{{ kycDetailModal.user?.full_name }}</span></div>
          <div class="kyc-row"><span class="kyc-lbl">手機</span><span>{{ kycDetailModal.user?.phone }}</span></div>
          <div class="kyc-row"><span class="kyc-lbl">身分證字號</span><span>{{ kycDetailModal.user?.verification_data?.idNumber || kycDetailModal.user?.verification_data?.id_number || '-' }}</span></div>
          <div class="kyc-row"><span class="kyc-lbl">代表人姓名</span><span>{{ kycDetailModal.user?.verification_data?.fullName || kycDetailModal.user?.verification_data?.real_name || '-' }}</span></div>
          <div class="kyc-row"><span class="kyc-lbl">審核狀態</span>
            <span :class="['badge', kycBadgeClass(kycDetailModal.user?.verify_status)]">{{ kycLabel(kycDetailModal.user?.verify_status) }}</span>
          </div>
          <div v-if="kycDetailModal.user?.verification_data?.reject_reason" class="kyc-row">
            <span class="kyc-lbl">拒絕原因</span>
            <span style="color:#ef4444">{{ kycDetailModal.user.verification_data.reject_reason }}</span>
          </div>
        </div>
        <div v-if="kycDetailModal.user?.verification_data" class="kyc-imgs" style="margin-top:1rem">
          <!-- file_ids 格式 -->
          <template v-if="kycDetailModal.user.verification_data.file_ids">
            <div v-if="kycDetailModal.user.verification_data.file_ids.front" class="kyc-img-wrap">
              <div class="kyc-img-label">身分證正面</div>
              <img :src="`/api/v1/files/${kycDetailModal.user.verification_data.file_ids.front}/serve`" class="kyc-img kyc-img-zoom" @click="openLightbox(`/api/v1/files/${kycDetailModal.user.verification_data.file_ids.front}/serve`)" />
            </div>
            <div v-if="kycDetailModal.user.verification_data.file_ids.back" class="kyc-img-wrap">
              <div class="kyc-img-label">身分證背面</div>
              <img :src="`/api/v1/files/${kycDetailModal.user.verification_data.file_ids.back}/serve`" class="kyc-img kyc-img-zoom" @click="openLightbox(`/api/v1/files/${kycDetailModal.user.verification_data.file_ids.back}/serve`)" />
            </div>
            <div v-if="kycDetailModal.user.verification_data.file_ids.handheld" class="kyc-img-wrap">
              <div class="kyc-img-label">手持身分證</div>
              <img :src="`/api/v1/files/${kycDetailModal.user.verification_data.file_ids.handheld}/serve`" class="kyc-img kyc-img-zoom" @click="openLightbox(`/api/v1/files/${kycDetailModal.user.verification_data.file_ids.handheld}/serve`)" />
            </div>
          </template>
          <!-- URL 格式（相容舊資料） -->
          <template v-else>
            <div v-if="kycDetailModal.user.verification_data.front_image_url || kycDetailModal.user.verification_data.frontImageUrl" class="kyc-img-wrap">
              <div class="kyc-img-label">身分證正面</div>
              <img :src="kycDetailModal.user.verification_data.front_image_url || kycDetailModal.user.verification_data.frontImageUrl" class="kyc-img kyc-img-zoom" @click="openLightbox(kycDetailModal.user.verification_data.front_image_url || kycDetailModal.user.verification_data.frontImageUrl)" />
            </div>
            <div v-if="kycDetailModal.user.verification_data.back_image_url || kycDetailModal.user.verification_data.backImageUrl" class="kyc-img-wrap">
              <div class="kyc-img-label">身分證背面</div>
              <img :src="kycDetailModal.user.verification_data.back_image_url || kycDetailModal.user.verification_data.backImageUrl" class="kyc-img kyc-img-zoom" @click="openLightbox(kycDetailModal.user.verification_data.back_image_url || kycDetailModal.user.verification_data.backImageUrl)" />
            </div>
          </template>
        </div>
      </div>
      <div class="modal-ft">
        <template v-if="kycDetailModal.user?.verify_status === 'pending'">
          <button class="btn btn-green" @click="reviewKyc(kycDetailModal.user.id, 'approve'); kycDetailModal.show = false">通過</button>
          <button class="btn btn-danger" @click="openKycReject(kycDetailModal.user); kycDetailModal.show = false">拒絕</button>
        </template>
        <button class="btn btn-outline" @click="kycDetailModal.show = false">關閉</button>
      </div>
    </div>
  </div>

  <!-- 圖片放大 Lightbox -->
  <div v-if="lightboxSrc" class="lightbox-overlay" @click="lightboxSrc = null">
    <img :src="lightboxSrc" class="lightbox-img" @click.stop />
    <button class="lightbox-close" @click="lightboxSrc = null">✕</button>
  </div>

  <!-- KYC 拒絕 Modal -->
  <div v-if="kycRejectModal.show" class="modal-overlay" @click.self="kycRejectModal.show = false">
    <div class="modal-box" style="max-width:440px">
      <div class="modal-hd">
        <span>拒絕原因</span>
        <button class="modal-x" @click="kycRejectModal.show = false">✕</button>
      </div>
      <div class="modal-bd">
        <label class="f-label">請輸入拒絕原因（選填）</label>
        <textarea v-model="kycRejectModal.reason" class="f-input" rows="3" placeholder="例：身分證照片不清晰，請重新上傳" style="resize:vertical"></textarea>
      </div>
      <div class="modal-ft">
        <button class="btn btn-outline" @click="kycRejectModal.show = false">取消</button>
        <button class="btn btn-danger" :disabled="kycRejectModal.submitting" @click="submitKycReject">{{ kycRejectModal.submitting ? '處理中...' : '確認拒絕' }}</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RefreshCw } from 'lucide-vue-next'

const kycTabs    = [{ key: 'pending', label: '待審核' }, { key: 'approved', label: '已通過' }, { key: 'rejected', label: '未通過' }]
const kycTab     = ref('pending')
const kycDevMode = ref(false)
const kycList    = ref([])
const loadingKyc = ref(false)
const kycDetailModal = ref({ show: false, user: null })
const kycRejectModal = ref({ show: false, user: null, reason: '', submitting: false })
const lightboxSrc    = ref(null)
const openLightbox   = (src) => { lightboxSrc.value = src }

const kycLabel      = (s) => ({ approved: '已通過', verified: '已通過', pending: '待審核', rejected: '未通過', none: '未提交' }[s] || '未提交')
const kycBadgeClass = (s) => ({ approved: 'badge-green', verified: 'badge-green', pending: 'badge-yellow', rejected: 'badge-red', none: 'badge-gray' }[s] || 'badge-gray')

const loadKyc = async () => {
  loadingKyc.value = true
  try {
    const res  = await fetch(`/api/v1/admin-panel/kyc?status=${kycTab.value}`)
    const data = await res.json()
    kycList.value = data.items || []
  } finally { loadingKyc.value = false }
}

const openKycDetail = (u) => { kycDetailModal.value = { show: true, user: u } }
const openKycReject = (u) => { kycRejectModal.value = { show: true, user: u, reason: '', submitting: false } }

const reviewKyc = async (userId, action, reason = null) => {
  try {
    const res  = await fetch(`/api/v1/admin-panel/kyc/${userId}/review`, {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action, reason }),
    })
    const data = await res.json()
    alert(data.message || '操作成功')
    await loadKyc()
  } catch { alert('操作失敗') }
}

const submitKycReject = async () => {
  kycRejectModal.value.submitting = true
  await reviewKyc(kycRejectModal.value.user.id, 'reject', kycRejectModal.value.reason || null)
  kycRejectModal.value.show = false
  kycRejectModal.value.submitting = false
}

onMounted(loadKyc)
</script>

<style scoped>
.kyc-img-zoom {
  cursor: zoom-in;
  transition: opacity 0.15s;
}
.kyc-img-zoom:hover { opacity: 0.85; }

.lightbox-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.85);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
}
.lightbox-img {
  max-width: 90vw;
  max-height: 90vh;
  border-radius: 6px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.5);
  object-fit: contain;
}
.lightbox-close {
  position: absolute;
  top: 1.25rem;
  right: 1.5rem;
  background: rgba(255,255,255,0.15);
  border: 1px solid rgba(255,255,255,0.3);
  color: #fff;
  font-size: 1.25rem;
  line-height: 1;
  padding: 0.35rem 0.6rem;
  border-radius: 6px;
  cursor: pointer;
}
.lightbox-close:hover { background: rgba(255,255,255,0.25); }
</style>
