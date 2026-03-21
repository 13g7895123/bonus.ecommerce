<template>
  <div class="admin-shell">
    <!-- ── 側欄 ── -->
    <aside class="sidebar" :class="{ collapsed: sidebarCollapsed }">
      <div class="sidebar-logo">
        <img src="/logo.png" alt="Logo" class="logo-img" />
        <span v-if="!sidebarCollapsed" class="logo-text">Admin Panel</span>
      </div>
      <nav class="sidebar-nav">
        <div
          v-for="item in navItems"
          :key="item.key"
          class="nav-item"
          :class="{ active: currentSection === item.key }"
          @click="navigate(item.key)"
        >
          <component :is="item.icon" :size="18" />
          <span v-if="!sidebarCollapsed" class="nav-label">{{ item.label }}</span>
        </div>
      </nav>
      <div class="sidebar-footer">
        <button class="collapse-btn" @click="sidebarCollapsed = !sidebarCollapsed">
          <ChevronLeft v-if="!sidebarCollapsed" :size="16" />
          <ChevronRight v-else :size="16" />
        </button>
      </div>
    </aside>

    <!-- ── 主區域 ── -->
    <main class="main-area">
      <!-- Header -->
      <div class="top-bar">
        <h1 class="section-heading">{{ currentNavItem?.label }}</h1>
        <button class="icon-btn" @click="$router.push('/')">
          <Home :size="16" /><span>返回前台</span>
        </button>
      </div>

      <!-- ── 使用者管理 ── -->
      <div v-if="currentSection === 'users'" class="panel">
        <div class="panel-header">
          <span class="panel-title">使用者列表</span>
          <button class="btn btn-outline" :disabled="loadingUsers" @click="loadUsers">
            <RefreshCw :size="14" />{{ loadingUsers ? '載入中...' : '重新整理' }}
          </button>
        </div>
        <div class="table-wrap">
          <div v-if="loadingUsers" class="state-msg">載入中...</div>
          <table v-else class="data-table">
            <thead>
              <tr>
                <th>ID</th><th>姓名</th><th>Email</th><th>Email驗證</th>
                <th>電話</th><th>國家</th><th>角色</th><th>KYC</th>
                <th>餘額</th><th>里程</th><th>操作</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="u in usersList" :key="u.id">
                <td class="td-muted">{{ u.id }}</td>
                <td class="td-name">{{ u.full_name || '-' }}</td>
                <td>{{ u.email }}</td>
                <td><span :class="['badge', u.is_verified == 1 ? 'badge-green' : 'badge-gray']">{{ u.is_verified == 1 ? '已驗證' : '未驗證' }}</span></td>
                <td>{{ u.phone || '-' }}</td>
                <td>{{ u.country || '-' }}</td>
                <td><span :class="['badge', u.role === 'admin' ? 'badge-purple' : 'badge-blue']">{{ u.role || 'user' }}</span></td>
                <td><span :class="['badge', kycBadgeClass(u.verify_status)]">{{ kycLabel(u.verify_status) }}</span></td>
                <td class="td-num">${{ (u.balance || 0).toLocaleString() }}</td>
                <td class="td-num">{{ (u.miles_balance || 0).toLocaleString() }}</td>
                <td><button class="btn btn-sm btn-primary" @click="openDeposit(u)">儲值</button></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ── KYC 審核 ── -->
      <div v-if="currentSection === 'kyc'" class="panel">
        <div class="panel-header">
          <span class="panel-title">實名認證審核</span>
          <div class="tab-pills">
            <button v-for="t in kycTabs" :key="t.key" class="pill" :class="{ active: kycTab === t.key }" @click="kycTab = t.key; loadKyc()">{{ t.label }}</button>
          </div>
          <button class="btn btn-outline" :disabled="loadingKyc" @click="loadKyc"><RefreshCw :size="14" /></button>
        </div>
        <div class="table-wrap">
          <div v-if="loadingKyc" class="state-msg">載入中...</div>
          <div v-else-if="kycList.length === 0" class="state-msg">目前無資料</div>
          <table v-else class="data-table">
            <thead>
              <tr>
                <th>ID</th><th>Email</th><th>姓名</th><th>手機</th>
                <th>身分證字號</th><th>代表人姓名</th><th>狀態</th>
                <th v-if="kycTab === 'pending'">操作</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="u in kycList" :key="u.id">
                <td class="td-muted">{{ u.id }}</td>
                <td>{{ u.email }}</td>
                <td>{{ u.full_name || '-' }}</td>
                <td>{{ u.phone || '-' }}</td>
                <td>{{ u.verification_data?.idNumber || u.verification_data?.id_number || '-' }}</td>
                <td>{{ u.verification_data?.fullName || u.verification_data?.real_name || '-' }}</td>
                <td><span :class="['badge', kycBadgeClass(u.verify_status)]">{{ kycLabel(u.verify_status) }}</span></td>
                <td v-if="kycTab === 'pending'" class="td-actions">
                  <button class="btn btn-sm btn-green" @click="reviewKyc(u.id, 'approve')">通過</button>
                  <button class="btn btn-sm btn-danger" @click="openKycReject(u)">拒絕</button>
                  <button class="btn btn-sm btn-outline" @click="openKycDetail(u)">詳情</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ── 里程兌換項目 ── -->
      <div v-if="currentSection === 'mileage-items'" class="panel">
        <div class="panel-header">
          <span class="panel-title">里程兌換項目管理</span>
          <div style="display:flex;gap:0.5rem">
            <button class="btn btn-outline" :disabled="loadingMileageItems" @click="loadMileageItems"><RefreshCw :size="14" /></button>
            <button class="btn btn-primary" @click="openMileageForm()"><Plus :size="14" />新增項目</button>
          </div>
        </div>
        <div class="table-wrap">
          <div v-if="loadingMileageItems" class="state-msg">載入中...</div>
          <div v-else-if="mileageItemsList.length === 0" class="state-msg">尚無項目</div>
          <table v-else class="data-table">
            <thead><tr><th>ID</th><th>名稱</th><th>Logo</th><th>精選</th><th>狀態</th><th>排序</th><th>操作</th></tr></thead>
            <tbody>
              <tr v-for="item in mileageItemsList" :key="item.id">
                <td class="td-muted">{{ item.id }}</td>
                <td>
                  <div class="td-name">{{ item.name }}</div>
                  <div class="td-sub">{{ item.short_desc }}</div>
                </td>
                <td><div class="logo-chip" :style="{ backgroundColor: item.logo_color }">{{ item.logo_letter }}</div></td>
                <td><span :class="['badge', item.is_featured == 1 ? 'badge-yellow' : 'badge-gray']">{{ item.is_featured == 1 ? '精選' : '-' }}</span></td>
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

      <!-- ── 內容管理 ── -->
      <div v-if="currentSection === 'content'" class="panel">
        <div class="panel-header"><span class="panel-title">內容管理</span></div>

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
    </main>

    <!-- ── 儲值 Modal ── -->
    <div v-if="depositModal.show" class="modal-overlay" @click.self="depositModal.show = false">
      <div class="modal-box">
        <div class="modal-hd"><span>儲值 — {{ depositModal.user?.full_name || depositModal.user?.email }}</span><button class="modal-x" @click="depositModal.show = false">✕</button></div>
        <div class="modal-bd">
          <p style="font-size:0.85rem;color:#64748b;margin-bottom:0.75rem">目前餘額：<strong>${{ (depositModal.user?.balance || 0).toLocaleString() }}</strong></p>
          <label class="f-label">儲值金額</label>
          <input v-model="depositModal.amount" type="number" min="1" class="f-input" placeholder="請輸入金額" @keyup.enter="submitDeposit" />
          <label class="f-label" style="margin-top:0.75rem">備註（選填）</label>
          <input v-model="depositModal.description" type="text" class="f-input" placeholder="例：測試儲值" />
        </div>
        <div class="modal-ft">
          <button class="btn btn-outline" @click="depositModal.show = false">取消</button>
          <button class="btn btn-primary" :disabled="depositModal.submitting" @click="submitDeposit">{{ depositModal.submitting ? '處理中...' : '確認儲值' }}</button>
        </div>
      </div>
    </div>

    <!-- ── KYC 詳情 Modal ── -->
    <div v-if="kycDetailModal.show" class="modal-overlay" @click.self="kycDetailModal.show = false">
      <div class="modal-box" style="max-width:560px">
        <div class="modal-hd"><span>KYC 詳情 — {{ kycDetailModal.user?.email }}</span><button class="modal-x" @click="kycDetailModal.show = false">✕</button></div>
        <div class="modal-bd">
          <div class="kyc-rows">
            <div class="kyc-row"><span class="kyc-lbl">Email</span><span>{{ kycDetailModal.user?.email }}</span></div>
            <div class="kyc-row"><span class="kyc-lbl">姓名</span><span>{{ kycDetailModal.user?.full_name }}</span></div>
            <div class="kyc-row"><span class="kyc-lbl">手機</span><span>{{ kycDetailModal.user?.phone }}</span></div>
            <div class="kyc-row"><span class="kyc-lbl">身分證字號</span><span>{{ kycDetailModal.user?.verification_data?.idNumber || kycDetailModal.user?.verification_data?.id_number || '-' }}</span></div>
            <div class="kyc-row"><span class="kyc-lbl">代表人姓名</span><span>{{ kycDetailModal.user?.verification_data?.fullName || kycDetailModal.user?.verification_data?.real_name || '-' }}</span></div>
          </div>
          <div v-if="kycDetailModal.user?.verification_data" class="kyc-imgs">
            <div v-if="kycDetailModal.user.verification_data.front_image_url || kycDetailModal.user.verification_data.frontImageUrl" class="kyc-img-wrap">
              <div class="kyc-img-label">身分證正面</div>
              <img :src="kycDetailModal.user.verification_data.front_image_url || kycDetailModal.user.verification_data.frontImageUrl" class="kyc-img" />
            </div>
            <div v-if="kycDetailModal.user.verification_data.back_image_url || kycDetailModal.user.verification_data.backImageUrl" class="kyc-img-wrap">
              <div class="kyc-img-label">身分證背面</div>
              <img :src="kycDetailModal.user.verification_data.back_image_url || kycDetailModal.user.verification_data.backImageUrl" class="kyc-img" />
            </div>
          </div>
        </div>
        <div class="modal-ft"><button class="btn btn-outline" @click="kycDetailModal.show = false">關閉</button></div>
      </div>
    </div>

    <!-- ── KYC 拒絕 Modal ── -->
    <div v-if="kycRejectModal.show" class="modal-overlay" @click.self="kycRejectModal.show = false">
      <div class="modal-box" style="max-width:440px">
        <div class="modal-hd"><span>拒絕原因</span><button class="modal-x" @click="kycRejectModal.show = false">✕</button></div>
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

    <!-- ── 里程項目 Modal ── -->
    <div v-if="mileageForm.show" class="modal-overlay" @click.self="mileageForm.show = false">
      <div class="modal-box" style="max-width:560px;max-height:90vh;overflow-y:auto">
        <div class="modal-hd"><span>{{ mileageForm.id ? '編輯項目' : '新增項目' }}</span><button class="modal-x" @click="mileageForm.show = false">✕</button></div>
        <div class="modal-bd">
          <label class="f-label">名稱 *</label>
          <input v-model="mileageForm.name" class="f-input" placeholder="例：Skywards Miles Mall" />
          <label class="f-label" style="margin-top:0.75rem">簡短說明</label>
          <input v-model="mileageForm.short_desc" class="f-input" placeholder="列表副標題" />
          <label class="f-label" style="margin-top:0.75rem">詳細內容（支援 HTML 富文本）</label>
          <RichTextEditor v-model="mileageForm.details" />
          <button class="btn btn-outline btn-sm" style="margin-top:0.5rem" @click="mileageModalPreview = !mileageModalPreview">
            <Eye :size="14" />{{ mileageModalPreview ? '隱藏預覽' : '預覽 Modal' }}
          </button>
          <div v-if="mileageModalPreview" class="preview-box" style="margin-top:0.5rem">
            <div class="preview-label">Modal 預覽</div>
            <div class="mock-modal">
              <div class="mock-modal-header">
                <div style="display:flex;align-items:center;gap:0.75rem">
                  <div class="logo-chip" :style="{ backgroundColor: mileageForm.logo_color }">{{ mileageForm.logo_letter }}</div>
                  <h3 style="font-size:1rem;font-weight:700;margin:0">{{ mileageForm.name || '項目名稱' }}</h3>
                </div>
                <button class="mock-modal-close">✕</button>
              </div>
              <div class="mock-modal-body">
                <p v-if="mileageForm.short_desc" style="color:#555;margin-bottom:0.75rem">{{ mileageForm.short_desc }}</p>
                <div v-if="mileageForm.details" v-html="mileageForm.details"></div>
                <div v-else style="color:#999;font-style:italic">暫無詳細說明</div>
              </div>
              <button class="mock-modal-confirm">關閉</button>
            </div>
          </div>
          <div style="display:flex;gap:1rem;margin-top:0.75rem">
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
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Users, ShieldCheck, Coins, FileEdit, Home, RefreshCw, Plus, Eye, Save, ChevronLeft, ChevronRight } from 'lucide-vue-next'
import RichTextEditor from '../components/admin/RichTextEditor.vue'

const router = useRouter()
const sidebarCollapsed = ref(false)
const currentSection = ref('users')

const navItems = [
  { key: 'users',         label: '使用者管理',  icon: Users },
  { key: 'kyc',           label: 'KYC 審核',     icon: ShieldCheck },
  { key: 'mileage-items', label: '里程兌換項目', icon: Coins },
  { key: 'content',       label: '內容管理',     icon: FileEdit },
]
const currentNavItem = computed(() => navItems.find(n => n.key === currentSection.value))

const navigate = (key) => {
  currentSection.value = key
  if (key === 'users' && usersList.value.length === 0) loadUsers()
  if (key === 'kyc' && kycList.value.length === 0) loadKyc()
  if (key === 'mileage-items' && mileageItemsList.value.length === 0) loadMileageItems()
  if (key === 'content') loadContentConfigs()
}

// ── 使用者 ──────────────────────────────────────────────────
const usersList    = ref([])
const loadingUsers = ref(false)
const loadUsers = async () => {
  loadingUsers.value = true
  try {
    const res  = await fetch('/api/v1/admin-panel/users?limit=200')
    const data = await res.json()
    usersList.value = data.items || []
  } finally { loadingUsers.value = false }
}

// ── 儲值 ────────────────────────────────────────────────────
const depositModal = ref({ show: false, user: null, amount: '', description: '', submitting: false })
const openDeposit  = (user) => { depositModal.value = { show: true, user, amount: '', description: '', submitting: false } }
const submitDeposit = async () => {
  const amount = parseFloat(depositModal.value.amount)
  if (!amount || amount <= 0) { alert('請輸入有效的正數金額'); return }
  depositModal.value.submitting = true
  try {
    const res  = await fetch(`/api/v1/admin-panel/users/${depositModal.value.user.id}/deposit`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ amount, description: depositModal.value.description || '管理員儲值' }) })
    const data = await res.json()
    if (!res.ok) { alert(data.message || '儲值失敗'); return }
    const u = usersList.value.find(u => u.id === depositModal.value.user.id)
    if (u) u.balance = data.balance
    depositModal.value.user.balance = data.balance
    alert(`儲值成功！新餘額：$${Number(data.balance).toLocaleString()}`)
    depositModal.value.show = false
  } finally { depositModal.value.submitting = false }
}

// ── KYC ─────────────────────────────────────────────────────
const kycTabs     = [{ key: 'pending', label: '待審核' }, { key: 'approved', label: '已通過' }, { key: 'rejected', label: '未通過' }]
const kycTab      = ref('pending')
const kycList     = ref([])
const loadingKyc  = ref(false)
const kycDetailModal = ref({ show: false, user: null })
const kycRejectModal = ref({ show: false, user: null, reason: '', submitting: false })

const kycLabel     = (s) => ({ approved: '已通過', pending: '待審核', rejected: '未通過', none: '未提交' }[s] || '未提交')
const kycBadgeClass= (s) => ({ approved: 'badge-green', pending: 'badge-yellow', rejected: 'badge-red', none: 'badge-gray' }[s] || 'badge-gray')

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
    const res  = await fetch(`/api/v1/admin-panel/kyc/${userId}/review`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action, reason }) })
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

// ── 里程兌換項目 ─────────────────────────────────────────────
const mileageItemsList    = ref([])
const loadingMileageItems = ref(false)
const mileageModalPreview = ref(false)
const mileageForm = ref({ show: false, id: null, name: '', short_desc: '', details: '', logo_letter: 'S', logo_color: '#ffffff', is_featured: 0, featured_label: '精選', is_active: 1, sort_order: 0, submitting: false })

const loadMileageItems = async () => {
  loadingMileageItems.value = true
  try {
    const res  = await fetch('/api/v1/admin-panel/mileage-items')
    const data = await res.json()
    mileageItemsList.value = data.items || []
  } finally { loadingMileageItems.value = false }
}

const openMileageForm = (item = null) => {
  mileageModalPreview.value = false
  if (item) {
    mileageForm.value = { show: true, submitting: false, id: item.id, name: item.name, short_desc: item.short_desc || '', details: item.details || '', logo_letter: item.logo_letter || 'S', logo_color: item.logo_color || '#ffffff', is_featured: Number(item.is_featured), featured_label: item.featured_label || '精選', is_active: Number(item.is_active), sort_order: item.sort_order || 0 }
  } else {
    mileageForm.value = { show: true, submitting: false, id: null, name: '', short_desc: '', details: '', logo_letter: 'S', logo_color: '#ffffff', is_featured: 0, featured_label: '精選', is_active: 1, sort_order: 0 }
  }
}

const submitMileageItem = async () => {
  const f = mileageForm.value
  if (!f.name.trim()) { alert('請填寫名稱'); return }
  f.submitting = true
  try {
    const url    = f.id ? `/api/v1/admin-panel/mileage-items/${f.id}` : '/api/v1/admin-panel/mileage-items'
    const method = f.id ? 'PUT' : 'POST'
    const res    = await fetch(url, { method, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ name: f.name, short_desc: f.short_desc, details: f.details, logo_letter: f.logo_letter, logo_color: f.logo_color, is_featured: f.is_featured, featured_label: f.featured_label, is_active: f.is_active, sort_order: f.sort_order }) })
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

// ── 內容管理（Config）────────────────────────────────────────
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
    const d1 = await r1.json()
    const d2 = await r2.json()
    silverCardDesc.value = d1.value || ''
    benefitsHtml.value   = d2.value || ''
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

onMounted(() => { loadUsers() })
</script>

<style scoped>
@import '../admin.css';

.admin-shell { display: flex; min-height: 100vh; background: #f1f5f9; color: #1e293b; font-family: 'Inter','Segoe UI',system-ui,sans-serif; }

/* Sidebar */
.sidebar { width: 240px; min-height: 100vh; background: #1e293b; color: #cbd5e1; display: flex; flex-direction: column; transition: width 0.2s; flex-shrink: 0; position: sticky; top: 0; height: 100vh; overflow: hidden; }
.sidebar.collapsed { width: 60px; }
.sidebar-logo { display: flex; align-items: center; gap: 10px; padding: 1.25rem 1rem; border-bottom: 1px solid #334155; overflow: hidden; }
.logo-img { width: 32px; height: 32px; object-fit: contain; flex-shrink: 0; }
.logo-text { font-size: 0.95rem; font-weight: 700; color: #f8fafc; white-space: nowrap; }
.sidebar-nav { flex: 1; padding: 0.75rem 0.5rem; display: flex; flex-direction: column; gap: 2px; }
.nav-item { display: flex; align-items: center; gap: 10px; padding: 0.6rem 0.75rem; border-radius: 8px; cursor: pointer; transition: all 0.15s; white-space: nowrap; color: #94a3b8; font-size: 0.875rem; }
.nav-item:hover { background: #334155; color: #f8fafc; }
.nav-item.active { background: #3b82f6; color: #fff; }
.nav-label { flex: 1; overflow: hidden; }
.sidebar-footer { padding: 0.75rem 0.5rem; border-top: 1px solid #334155; }
.collapse-btn { background: transparent; border: none; color: #94a3b8; cursor: pointer; width: 100%; display: flex; justify-content: center; padding: 6px; border-radius: 6px; transition: background 0.15s; }
.collapse-btn:hover { background: #334155; color: #fff; }

/* Main */
.main-area { flex: 1; display: flex; flex-direction: column; min-width: 0; }
.top-bar { display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.5rem; background: #fff; border-bottom: 1px solid #e2e8f0; position: sticky; top: 0; z-index: 10; }
.section-heading { font-size: 1.1rem; font-weight: 700; margin: 0; }
.icon-btn { display: inline-flex; align-items: center; gap: 6px; padding: 0.45rem 0.85rem; border: 1px solid #e2e8f0; background: #fff; border-radius: 8px; font-size: 0.85rem; cursor: pointer; transition: background 0.15s; }
.icon-btn:hover { background: #f8fafc; }

/* Panel */
.panel { background: #fff; border-radius: 12px; margin: 1.25rem; box-shadow: 0 1px 4px rgba(0,0,0,0.06); overflow: hidden; }
.panel-header { display: flex; align-items: center; gap: 0.75rem; padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; flex-wrap: wrap; }
.panel-title { font-size: 1rem; font-weight: 600; flex: 1; }

/* Table */
.table-wrap { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
.data-table th { text-align: left; padding: 0.6rem 1rem; background: #f8fafc; color: #64748b; font-weight: 500; border-bottom: 1px solid #e2e8f0; white-space: nowrap; }
.data-table td { padding: 0.65rem 1rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.data-table tr:last-child td { border-bottom: none; }
.data-table tr:hover td { background: #f8fafc; }
.td-muted { color: #94a3b8; }
.td-name { font-weight: 500; }
.td-sub { font-size: 0.75rem; color: #94a3b8; }
.td-num { text-align: right; font-variant-numeric: tabular-nums; }
.td-actions { display: flex; gap: 6px; align-items: center; flex-wrap: wrap; }

/* Buttons */
.btn { display: inline-flex; align-items: center; gap: 5px; padding: 0.45rem 0.9rem; font-size: 0.85rem; font-weight: 500; border-radius: 8px; border: none; cursor: pointer; transition: all 0.15s; white-space: nowrap; }
.btn:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-primary { background: #3b82f6; color: #fff; }
.btn-primary:hover:not(:disabled) { background: #2563eb; }
.btn-outline { background: transparent; border: 1px solid #e2e8f0; color: #374151; }
.btn-outline:hover:not(:disabled) { background: #f8fafc; }
.btn-danger { background: #ef4444; color: #fff; }
.btn-danger:hover:not(:disabled) { background: #dc2626; }
.btn-green { background: #22c55e; color: #fff; }
.btn-green:hover:not(:disabled) { background: #16a34a; }
.btn-sm { padding: 0.3rem 0.65rem; font-size: 0.8rem; border-radius: 6px; }

/* Badges */
.badge { display: inline-block; padding: 2px 8px; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; }
.badge-green { background: #dcfce7; color: #166534; }
.badge-red { background: #fee2e2; color: #991b1b; }
.badge-yellow { background: #fef9c3; color: #854d0e; }
.badge-blue { background: #dbeafe; color: #1d4ed8; }
.badge-purple { background: #ede9fe; color: #5b21b6; }
.badge-gray { background: #f1f5f9; color: #64748b; }

/* States */
.state-msg { padding: 3rem 1.5rem; text-align: center; color: #94a3b8; font-size: 0.9rem; }

/* Tab Pills */
.tab-pills { display: flex; gap: 4px; }
.pill { padding: 0.3rem 0.75rem; border-radius: 9999px; border: 1px solid #e2e8f0; background: transparent; color: #64748b; font-size: 0.8rem; cursor: pointer; transition: all 0.15s; }
.pill.active { background: #1e293b; color: #fff; border-color: #1e293b; }

/* Logo Chip */
.logo-chip { width: 32px; min-width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8rem; color: #1e293b; border: 1px solid rgba(0,0,0,0.08); }

/* Content Management */
.content-block { padding: 1.25rem; }
.content-block-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 0.75rem; gap: 1rem; }
.cb-title { font-weight: 600; font-size: 0.95rem; margin-bottom: 3px; }
.cb-desc { font-size: 0.8rem; color: #64748b; }
.cfg-textarea { width: 100%; padding: 0.6rem 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.9rem; resize: vertical; outline: none; font-family: inherit; color: #1e293b; }
.cfg-textarea:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
.divider { height: 1px; background: #f1f5f9; }

/* Preview */
.preview-box { margin-top: 1rem; border: 1px dashed #cbd5e1; border-radius: 10px; padding: 1rem; background: #f8fafc; }
.preview-label { font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; }
.mock-card { display: flex; gap: 0.75rem; background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; padding: 0.75rem; }
.mock-card-img { width: 110px; height: 75px; object-fit: cover; border-radius: 6px; flex-shrink: 0; }
.mock-card-body { flex: 1; }
.mock-card-title { font-size: 1rem; font-weight: 700; margin-bottom: 6px; }
.mock-card-desc { font-size: 0.85rem; color: #555; }
.mock-modal { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; max-width: 380px; }
.mock-modal-header { display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; }
.mock-modal-title { font-size: 1rem; font-weight: 700; flex: 1; text-align: center; }
.mock-modal-close { background: none; border: none; color: #94a3b8; cursor: pointer; }
.mock-modal-body { padding: 1rem 1.25rem; font-size: 0.88rem; color: #374151; min-height: 60px; }
.mock-modal-body :deep(ul) { padding-left: 1.5em; list-style: disc; }
.mock-modal-body :deep(ol) { padding-left: 1.5em; list-style: decimal; }
.mock-modal-confirm { display: block; width: calc(100% - 2.5rem); margin: 0 1.25rem 1rem; padding: 0.6rem; background: #1e293b; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-size: 0.9rem; }

/* Modal Overlay */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 100; padding: 1rem; }
.modal-box { background: #fff; border-radius: 14px; width: 100%; max-width: 480px; box-shadow: 0 20px 60px rgba(0,0,0,0.2); overflow: hidden; }
.modal-hd { display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; font-weight: 600; font-size: 0.95rem; }
.modal-x { background: none; border: none; color: #94a3b8; font-size: 1.1rem; cursor: pointer; padding: 4px; line-height: 1; }
.modal-x:hover { color: #1e293b; }
.modal-bd { padding: 1.25rem; display: flex; flex-direction: column; gap: 0.25rem; }
.modal-ft { display: flex; justify-content: flex-end; gap: 0.5rem; padding: 1rem 1.25rem; border-top: 1px solid #f1f5f9; }

/* Form */
.f-label { display: block; font-size: 0.8rem; font-weight: 500; color: #64748b; margin-bottom: 4px; }
.f-input { width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.9rem; outline: none; box-sizing: border-box; color: #1e293b; background: #fff; }
.f-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
.f-input:disabled { background: #f8fafc; color: #94a3b8; cursor: not-allowed; }

/* KYC */
.kyc-rows { display: flex; flex-direction: column; gap: 0.5rem; }
.kyc-row { display: flex; gap: 1rem; align-items: baseline; font-size: 0.9rem; }
.kyc-lbl { min-width: 100px; flex-shrink: 0; color: #94a3b8; font-size: 0.8rem; }
.kyc-imgs { display: flex; gap: 1rem; margin-top: 1rem; flex-wrap: wrap; }
.kyc-img-wrap { flex: 1; min-width: 140px; }
.kyc-img-label { font-size: 0.8rem; color: #64748b; margin-bottom: 6px; }
.kyc-img { width: 100%; max-width: 200px; border-radius: 8px; border: 1px solid #e2e8f0; }
</style>
