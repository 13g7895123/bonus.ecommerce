<template>
  <div class="admin-page">
    <!-- Header -->
    <div class="admin-header-bar">
      <div class="header-left">
        <button class="back-btn" @click="handleBack">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 12H5M12 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
        <h1>Admin Dashboard</h1>
      </div>
      <div class="header-right">
        <button class="bg-red-btn" @click="clearStorage">Clear Storage</button>
      </div>
    </div>
    
    <div class="admin-layout">
      <!-- Sidebar -->
      <div class="admin-sidebar">
        <div class="sidebar-header">
            Management
        </div>
        <div class="sidebar-list">
             <div class="sidebar-item" :class="{ active: selectedKey === 'users' }" @click="selectKey({ key: 'users' })">
                <div class="sidebar-item-content">
                    <div class="sidebar-item-title">Users List</div>
                </div>
             </div>
             <div class="sidebar-item" :class="{ active: selectedKey === 'mileage-items' }" @click="selectKey({ key: 'mileage-items' })">
                <div class="sidebar-item-content">
                    <div class="sidebar-item-title">里程兌換項目管理</div>
                </div>
             </div>
             <div class="sidebar-item" :class="{ active: selectedKey === 'skywards-benefits' }" @click="selectKey({ key: 'skywards-benefits' })">
                <div class="sidebar-item-content">
                    <div class="sidebar-item-title">Skywards 權益管理</div>
                </div>
             </div>
             <!-- Original LocalStorage inspector below -->
        </div>

        <!-- Storage Keys section hidden -->
      </div>

      <!-- Main Content -->
      <div class="admin-main">
        <div v-if="selectedKey === 'users'" class="content-panel">
            <div class="panel-header">
                <h2>使用者管理</h2>
                <button class="refresh-btn" :disabled="loadingUsers" @click="loadUsers">
                  {{ loadingUsers ? '載入中...' : '重新整理' }}
                </button>
            </div>
            <div class="panel-body">
                <div v-if="loadingUsers" class="loading-state">載入中...</div>
                <div v-else-if="usersList.length === 0" class="empty-state">尚無使用者資料</div>
                <div v-else class="table-container">
                  <table class="user-table">
                      <thead>
                          <tr>
                              <th>ID</th>
                              <th>姓名</th>
                              <th>Email</th>
                              <th>Email 驗證</th>
                              <th>電話</th>
                              <th>國家</th>
                              <th>角色</th>
                              <th>驗證狀態</th>
                              <th>餘額</th>
                              <th>里程數</th>
                              <th>銀行綁定</th>
                              <th>操作</th>
                          </tr>
                      </thead>
                      <tbody>
                          <tr v-for="user in usersList" :key="user.id">
                              <td>{{ user.id }}</td>
                              <td>{{ user.full_name || '-' }}</td>
                              <td>{{ user.email }}</td>
                              <td>
                                <span :class="['email-verified-badge', (user.is_verified == 1 || user.is_verified === true) ? 'yes' : 'no']">
                                  {{ (user.is_verified == 1 || user.is_verified === true) ? '已驗證' : '未驗證' }}
                                </span>
                              </td>
                              <td>{{ user.phone || '-' }}</td>
                              <td>{{ user.country || '-' }}</td>
                              <td><span :class="['role-badge', user.role]">{{ user.role || 'user' }}</span></td>
                              <td><span :class="['verify-badge', user.verify_status || 'none']">{{ verifyLabel(user.verify_status) }}</span></td>
                              <td class="amount-cell">${{ user.balance?.toLocaleString() || '0' }}</td>
                              <td class="amount-cell">{{ user.miles_balance?.toLocaleString() || '0' }}</td>
                              <td><span :class="['bank-badge', user.has_bank_account ? 'yes' : 'no']">{{ user.has_bank_account ? '已綁定' : '未綁定' }}</span></td>
                              <td>
                                  <button class="deposit-btn" @click="openDeposit(user)">儲值</button>
                              </td>
                          </tr>
                      </tbody>
                  </table>
                </div>
            </div>
        </div>

        <!-- 里程兌換項目管理 -->
        <div v-else-if="selectedKey === 'mileage-items'" class="content-panel">
            <div class="panel-header">
                <h2>里程兌換項目管理</h2>
                <div style="display:flex;gap:0.5rem;">
                  <button class="refresh-btn" :disabled="loadingMileageItems" @click="loadMileageItems">{{ loadingMileageItems ? '載入中...' : '重新整理' }}</button>
                  <button class="add-btn" @click="openMileageItemForm()">＋ 新增項目</button>
                </div>
            </div>
            <div class="panel-body">
                <div v-if="loadingMileageItems" class="loading-state">載入中...</div>
                <div v-else-if="mileageItemsList.length === 0" class="empty-state">尚無項目，請點擊「新增項目」</div>
                <div v-else class="table-container">
                  <table class="user-table">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>名稱</th>
                        <th>簡短說明</th>
                        <th>Logo字母</th>
                        <th>Logo顏色</th>
                        <th>精選</th>
                        <th>精選標籤</th>
                        <th>狀態</th>
                        <th>排序</th>
                        <th>操作</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="item in mileageItemsList" :key="item.id">
                        <td>{{ item.id }}</td>
                        <td>{{ item.name }}</td>
                        <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ item.short_desc || '-' }}</td>
                        <td>{{ item.logo_letter }}</td>
                        <td>
                          <span class="color-swatch" :style="{ backgroundColor: item.logo_color }"></span>
                          {{ item.logo_color }}
                        </td>
                        <td><span :class="['status-badge', item.is_featured == 1 ? 'active' : 'inactive']">{{ item.is_featured == 1 ? '是' : '否' }}</span></td>
                        <td>{{ item.featured_label || '-' }}</td>
                        <td><span :class="['status-badge', item.is_active == 1 ? 'active' : 'inactive']">{{ item.is_active == 1 ? '啟用' : '停用' }}</span></td>
                        <td>{{ item.sort_order }}</td>
                        <td>
                          <button class="edit-btn" @click="openMileageItemForm(item)">編輯</button>
                          <button class="delete-item-btn" @click="deleteMileageItem(item.id)">刪除</button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
            </div>
        </div>

        <!-- Skywards 權益管理 -->
        <div v-else-if="selectedKey === 'skywards-benefits'" class="content-panel">
            <div class="panel-header">
                <h2>Skywards 權益管理</h2>
                <div style="display:flex;gap:0.5rem;">
                  <button class="refresh-btn" :disabled="loadingBenefits" @click="loadSkywardsBenefits">{{ loadingBenefits ? '載入中...' : '重新整理' }}</button>
                  <button class="add-btn" @click="openBenefitForm()">＋ 新增權益</button>
                </div>
            </div>
            <div class="panel-body">
                <div v-if="loadingBenefits" class="loading-state">載入中...</div>
                <div v-else-if="benefitsList.length === 0" class="empty-state">尚無資料，請點擊「新增權益」</div>
                <div v-else class="table-container">
                  <table class="user-table">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>類型</th>
                        <th>標籤</th>
                        <th>內容</th>
                        <th>狀態</th>
                        <th>排序</th>
                        <th>操作</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="item in benefitsList" :key="item.id">
                        <td>{{ item.id }}</td>
                        <td><span :class="['type-badge', item.type]">{{ { hint: '提示文字', rule: '規則條文', note: '備註' }[item.type] || item.type }}</span></td>
                        <td>{{ item.label || '-' }}</td>
                        <td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ item.content }}</td>
                        <td><span :class="['status-badge', item.is_active == 1 ? 'active' : 'inactive']">{{ item.is_active == 1 ? '啟用' : '停用' }}</span></td>
                        <td>{{ item.sort_order }}</td>
                        <td>
                          <button class="edit-btn" @click="openBenefitForm(item)">編輯</button>
                          <button class="delete-item-btn" @click="deleteBenefit(item.id)">刪除</button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
            </div>
        </div>

        <div v-else-if="!selectedItem" class="empty-selection">
          <div class="empty-icon">👈</div>
          <p>Please select a key from the sidebar</p>
        </div>

        <div v-else class="content-panel">
          <div class="panel-header">
            <h2>{{ getLabel(selectedItem.key) }}</h2>
            <span class="panel-badge">{{ selectedItem.key }}</span>
          </div>

          <div class="panel-body">
            <!-- Image View -->
            <div v-if="isImage(selectedItem.parsedValue)" class="image-viewer">
              <img :src="selectedItem.parsedValue" alt="Content Image" />
            </div>

            <!-- Array Table View -->
            <div v-else-if="Array.isArray(selectedItem.parsedValue) && selectedItem.parsedValue.length > 0" class="table-view">
               <div class="table-container">
                  <table>
                    <thead>
                      <tr>
                        <th v-for="header in getHeaders(selectedItem.parsedValue)" :key="header">
                          {{ getLabel(header) }}
                          <span class="sub-header">{{ header }}</span>
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(row, idx) in selectedItem.parsedValue" :key="idx">
                        <td v-for="header in getHeaders(selectedItem.parsedValue)" :key="header" :title="String(row[header])">
                          <template v-if="isImage(row[header])">
                            <img :src="row[header]" class="thumbnail" />
                          </template>
                          <template v-else-if="isObject(row[header])">
                            <div class="nested-obj">
                                <div v-for="(subVal, subKey) in row[header]" :key="subKey" class="nested-item">
                                    <span class="nested-label">{{ getLabel(subKey) }}: </span>
                                    <template v-if="isImage(subVal)">
                                      <img :src="subVal" class="nested-img" @click.stop="openImage(subVal)" />
                                    </template>
                                    <template v-else>
                                      <span class="nested-text" :title="String(subVal)">{{ formatCell(subVal) }}</span>
                                    </template>
                                </div>
                            </div>
                          </template>
                          <template v-else>
                            <div class="cell-content">{{ formatCell(row[header]) }}</div>
                          </template>
                        </td>
                      </tr>
                    </tbody>
                  </table>
               </div>
            </div>

             <!-- Object Key-Value View -->
             <div v-else-if="isObject(selectedItem.parsedValue)" class="object-view">
                <div class="kv-table">
                    <div v-for="(val, key) in selectedItem.parsedValue" :key="key" class="kv-row">
                        <div class="kv-key">
                            <div class="kv-label-primary">{{ getLabel(key) }}</div>
                            <div class="kv-label-secondary">{{ key }}</div>
                        </div>
                        <div class="kv-value">
                            <template v-if="isImage(val)">
                                <img :src="val" class="preview-image" />
                            </template>
                            <template v-else-if="isObject(val) || Array.isArray(val)">
                                <pre>{{ JSON.stringify(val, null, 2) }}</pre>
                            </template>
                            <template v-else>
                                {{ formatCell(val) }}
                            </template>
                        </div>
                    </div>
                </div>
             </div>

             <!-- Simple Value View -->
             <div v-else class="simple-view">
               <pre>{{ String(selectedItem.parsedValue) }}</pre>
             </div>

          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- 儲值 Modal -->
  <div v-if="depositModal.show" class="modal-overlay" @click.self="depositModal.show = false">
    <div class="modal-box">
      <div class="modal-header">
        <h3>儲值</h3>
        <button class="modal-close" @click="depositModal.show = false">✕</button>
      </div>
      <div class="modal-body">
        <div class="modal-user-info">
          <div class="modal-user-name">{{ depositModal.user?.full_name || '(無姓名)' }}</div>
          <div class="modal-user-email">{{ depositModal.user?.email }}</div>
          <div class="modal-user-balance">目前餘額：<strong>${{ depositModal.user?.balance?.toLocaleString() || '0' }}</strong></div>
        </div>
        <label class="modal-label">儲值金額</label>
        <input
          v-model="depositModal.amount"
          type="number"
          min="1"
          step="1"
          placeholder="請輸入金額"
          class="modal-input"
          @keyup.enter="submitDeposit"
        />
        <label class="modal-label" style="margin-top: 0.75rem;">備註（選填）</label>
        <input
          v-model="depositModal.description"
          type="text"
          placeholder="例：測試儲值"
          class="modal-input"
        />
      </div>
      <div class="modal-footer">
        <button class="modal-cancel-btn" @click="depositModal.show = false">取消</button>
        <button class="modal-submit-btn" :disabled="depositModal.submitting" @click="submitDeposit">
          {{ depositModal.submitting ? '處理中...' : '確認儲值' }}
        </button>
      </div>
    </div>
  </div>

  <!-- 里程兌換項目 Modal -->
  <div v-if="mileageItemForm.show" class="modal-overlay" @click.self="mileageItemForm.show = false">
    <div class="modal-box" style="width:520px;max-height:90vh;overflow-y:auto;">
      <div class="modal-header">
        <h3>{{ mileageItemForm.id ? '編輯項目' : '新增項目' }}</h3>
        <button class="modal-close" @click="mileageItemForm.show = false">✕</button>
      </div>
      <div class="modal-body">
        <label class="modal-label">名稱 *</label>
        <input v-model="mileageItemForm.name" class="modal-input" placeholder="例：Skywards Miles Mall" />
        <label class="modal-label" style="margin-top:0.75rem;">簡短說明</label>
        <input v-model="mileageItemForm.short_desc" class="modal-input" placeholder="顯示在列表的副標題" />
        <label class="modal-label" style="margin-top:0.75rem;">詳細內容（支援 HTML）</label>
        <textarea v-model="mileageItemForm.details" class="modal-input" rows="4" placeholder="點開 modal 後顯示的完整說明，支援 HTML 標籤" style="resize:vertical;"></textarea>
        <div style="display:flex;gap:1rem;margin-top:0.75rem;">
          <div style="flex:1;">
            <label class="modal-label">Logo 字母</label>
            <input v-model="mileageItemForm.logo_letter" class="modal-input" placeholder="S" maxlength="5" />
          </div>
          <div style="flex:1;">
            <label class="modal-label">Logo 背景色</label>
            <input v-model="mileageItemForm.logo_color" class="modal-input" placeholder="#ffffff" />
          </div>
        </div>
        <div style="display:flex;gap:1rem;margin-top:0.75rem;align-items:center;">
          <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
            <input type="checkbox" v-model="mileageItemForm.is_featured" :true-value="1" :false-value="0" />
            <span class="modal-label" style="margin:0;">精選項目</span>
          </label>
          <div style="flex:1;">
            <input v-model="mileageItemForm.featured_label" class="modal-input" placeholder="精選標籤文字（預設：精選）" :disabled="!mileageItemForm.is_featured" />
          </div>
        </div>
        <div style="display:flex;gap:1rem;margin-top:0.75rem;align-items:center;">
          <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
            <input type="checkbox" v-model="mileageItemForm.is_active" :true-value="1" :false-value="0" />
            <span class="modal-label" style="margin:0;">啟用</span>
          </label>
          <div style="flex:1;">
            <label class="modal-label">排序（數字越小越前）</label>
            <input v-model.number="mileageItemForm.sort_order" class="modal-input" type="number" min="0" />
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="modal-cancel-btn" @click="mileageItemForm.show = false">取消</button>
        <button class="modal-submit-btn" :disabled="mileageItemForm.submitting" @click="submitMileageItem">
          {{ mileageItemForm.submitting ? '處理中...' : (mileageItemForm.id ? '儲存變更' : '新增') }}
        </button>
      </div>
    </div>
  </div>

  <!-- Skywards 權益 Modal -->
  <div v-if="benefitForm.show" class="modal-overlay" @click.self="benefitForm.show = false">
    <div class="modal-box" style="width:480px;max-height:90vh;overflow-y:auto;">
      <div class="modal-header">
        <h3>{{ benefitForm.id ? '編輯權益' : '新增權益' }}</h3>
        <button class="modal-close" @click="benefitForm.show = false">✕</button>
      </div>
      <div class="modal-body">
        <label class="modal-label">類型 *</label>
        <select v-model="benefitForm.type" class="modal-input">
          <option value="hint">提示文字（hint）</option>
          <option value="rule">規則條文（rule）</option>
          <option value="note">備註（note）</option>
        </select>
        <label class="modal-label" style="margin-top:0.75rem;">標籤（rule 類型顯示為粗體前置文字）</label>
        <input v-model="benefitForm.label" class="modal-input" placeholder="例：銀卡" />
        <label class="modal-label" style="margin-top:0.75rem;">內容 *</label>
        <textarea v-model="benefitForm.content" class="modal-input" rows="3" placeholder="輸入說明文字" style="resize:vertical;"></textarea>
        <div style="display:flex;gap:1rem;margin-top:0.75rem;align-items:center;">
          <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
            <input type="checkbox" v-model="benefitForm.is_active" :true-value="1" :false-value="0" />
            <span class="modal-label" style="margin:0;">啟用</span>
          </label>
          <div style="flex:1;">
            <label class="modal-label">排序（數字越小越前）</label>
            <input v-model.number="benefitForm.sort_order" class="modal-input" type="number" min="0" />
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="modal-cancel-btn" @click="benefitForm.show = false">取消</button>
        <button class="modal-submit-btn" :disabled="benefitForm.submitting" @click="submitBenefit">
          {{ benefitForm.submitting ? '處理中...' : (benefitForm.id ? '儲存變更' : '新增') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useApi } from '../composables/useApi'

const router = useRouter()
const { user: userService } = useApi()
const items = ref([])
const selectedKey = ref('users')

const selectedItem = computed(() => items.value.find(i => i.key === selectedKey.value))

// ── 使用者列表 (from API) ──────────────────────────────────────────────────
const usersList    = ref([])
const loadingUsers = ref(false)

const loadUsers = async () => {
  loadingUsers.value = true
  try {
    const res  = await fetch('/api/v1/admin-panel/users?limit=100')
    const data = await res.json()
    usersList.value = data.items || []
  } catch (e) {
    console.error('載入使用者失敗', e)
  } finally {
    loadingUsers.value = false
  }
}

// ── 儲值 Modal ──────────────────────────────────────────────────────────────
const depositModal = ref({
  show: false,
  user: null,
  amount: '',
  description: '',
  submitting: false,
})

const openDeposit = (user) => {
  depositModal.value = { show: true, user, amount: '', description: '', submitting: false }
}

const submitDeposit = async () => {
  const amount = parseFloat(depositModal.value.amount)
  if (!amount || amount <= 0) {
    alert('請輸入有效的正數金額')
    return
  }
  depositModal.value.submitting = true
  try {
    // 使用 service 層，相容 mock 與真實 API，並自動帶入 JWT
    const data = await userService.updateUserBalance(
      depositModal.value.user.id,
      amount,
      depositModal.value.description || '管理員儲值',
    )
    // 更新本地列表中的餘額
    const newBalance = data?.balance ?? data?.data?.balance
    const user = usersList.value.find(u => u.id === depositModal.value.user.id)
    if (user && newBalance != null) user.balance = newBalance
    if (newBalance != null) depositModal.value.user.balance = newBalance
    alert(`儲值成功！新餘額：$${Number(newBalance ?? 0).toLocaleString()}`)
    depositModal.value.show = false
  } catch (e) {
    alert(e?.response?.data?.message || e?.message || '儲值失敗，請稍後再試')
  } finally {
    depositModal.value.submitting = false
  }
}

// ── 驗證狀態標籤 ────────────────────────────────────────────────────────────
const verifyLabel = (status) => {
  const map = { approved: '已驗證', pending: '審核中', rejected: '已拒絕', none: '未提交' }
  return map[status] || '未提交'
}

// Dictionary for Labels
const fieldMap = {
  // Storage Keys
  'user': '使用者資訊',
  'token': '存取憑證',
  'iv_idNumber': '身分證字號',
  'iv_fullName': '姓名(驗證)',
  'iv_frontImage': '身分證正面',
  'iv_backImage': '身分證背面',
  'skywards_db_users': 'Skywards 使用者資料庫',
  'skywards_db_transactions': 'Skywards 交易紀錄',
  'skywards_db_announcements': 'Skywards 公告列表',
  'mock_db_transactions': '交易紀錄',
  'mock_db_users': '使用者資料庫',
  'mock_db_announcements': '公告列表',

  // Common Fields
  'id': 'ID',
  'username': '使用者名稱',
  'email': '電子郵件',
  'phone': '電話號碼',
  'wallet': '錢包資訊',
  'balance': '餘額',
  'password': '密碼', 
  'bankName': '銀行名稱',
  'branchName': '分行名稱',
  'accountNo': '銀行帳號',
  'accountName': '戶名',
  'amount': '金額',
  'status': '狀態',
  'date': '日期',
  'type': '類型',
  'title': '標題',
  'content': '內容',
  'createdAt': '建立時間',
  'updatedAt': '更新時間',
  'name': '名稱',
  'role': '角色',
  'gender': '性別',
  'birthday': '生日',
  'address': '地址',
  'isVerified': '已驗證',
  'avatar': '頭像',
  'nickname': '暱稱',
  'level': '等級',
  'points': '積分',
  'vip': 'VIP等級',
  'isActive': '啟用狀態',
  'lastLogin': '最後登入',
  'registerIp': '註冊IP',
  'loginIp': '登入IP',
  'signature': '個性簽名',
  'firstName': '名字',
  'lastName': '姓氏',
  'country': '國家',
  'city': '城市',
  'zipCode': '郵遞區號',
  'memo': '備註',
  'remark': '備註',
  'description': '描述',
  'category': '分類',
  'orderNo': '訂單編號',
  'paymentMethod': '付款方式',
  'currency': '貨幣',
  'exchangeRate': '匯率',
  'fee': '手續費',
  'tax': '稅金',
  'discount': '折扣',
  'total': '總計',
  'quantity': '數量',
  'price': '價格',
  'productName': '商品名稱',
  'productId': '商品ID',
  'sku': 'SKU',
  'imageUrl': '圖片連結',
  'link': '連結',
  'sort': '排序',
  'isVisible': '是否顯示',
  'isDeleted': '是否刪除',
  'creator': '建立者',
  'updater': '更新者',
  'ip': 'IP位址',
  'device': '裝置',
  'browser': '瀏覽器',
  'os': '作業系統',
  'userAgent': 'User Agent',
  'action': '動作',
  'target': '目標',
  'result': '結果',
  'message': '訊息',
  'code': '代碼',
  'data': '資料',
  'verificationData': '實名驗證資料',
  'frontImage': '正面照片',
  'backImage': '背面照片',
  'idNumber': '身分證字號',
  'fullName': '真實姓名'
}

const getLabel = (key) => {
  return fieldMap[key] || key
}

const handleBack = () => {
  router.push('/')
}

const clearStorage = () => {
  if (confirm('Are you sure you want to clear all localStorage data?')) {
    localStorage.clear()
    items.value = []
    selectedKey.value = null
  }
}

const selectKey = (item) => {
  selectedKey.value = item.key
  if (item.key === 'mileage-items' && mileageItemsList.value.length === 0) loadMileageItems()
  if (item.key === 'skywards-benefits' && benefitsList.value.length === 0) loadSkywardsBenefits()
}

const openImage = (src) => {
  const w = window.open('about:blank')
  const image = new Image()
  image.src = src
  setTimeout(function() {
    w.document.write(image.outerHTML)
  }, 0)
}

const isImage = (val) => {
  if (typeof val !== 'string') return false
  // Check for data URI or image URL extension
  return val.startsWith('data:image') || /\.(jpeg|jpg|gif|png|webp)($|\?)/i.test(val)
}

const isObject = (val) => {
  return val && typeof val === 'object' && !Array.isArray(val)
}

const getHeaders = (data) => {
  if (!Array.isArray(data) || data.length === 0) return []
  // Collect all unique keys from all objects in the data array
  const keys = new Set()
  data.forEach(item => {
    if (isObject(item)) {
      Object.keys(item).forEach(k => keys.add(k))
    }
  })
  return Array.from(keys)
}

const formatCell = (val) => {
  if (val === null || val === undefined) return '-'
  if (typeof val === 'boolean') return val ? 'Yes' : 'No'
  // If it's an object/array inside a cell, stringify it
  if (typeof val === 'object') return JSON.stringify(val)
  return String(val)
}

const deleteKey = (key, event) => {
  event.stopPropagation()
  if (confirm(`確定要刪除 "${getLabel(key)}" (${key}) 嗎？`)) {
    localStorage.removeItem(key)
    updateView()
    if (selectedKey.value === key) {
      selectedKey.value = null
    }
  }
}

const updateView = () => {
    // Force Vue to re-render via a small tick if needed, but reactivity handles it.
    // However, we need to re-read localStorage
    const keys = Object.keys(localStorage)
    items.value = keys.map(key => {
        const rawVal = localStorage.getItem(key)
        let parsedVal = rawVal
        try {
        parsedVal = JSON.parse(rawVal)
        } catch (e) {
        // Keep as string
        }
        return {
        key: key,
        rawValue: rawVal,
        parsedValue: parsedVal
        }
    })
}

// ── 里程兌換項目 ────────────────────────────────────────────────────────────
const mileageItemsList    = ref([])
const loadingMileageItems = ref(false)
const mileageItemForm = ref({ show: false, id: null, name: '', short_desc: '', details: '', logo_letter: 'S', logo_color: '#ffffff', is_featured: 0, featured_label: '精選', is_active: 1, sort_order: 0, submitting: false })

const loadMileageItems = async () => {
  loadingMileageItems.value = true
  try {
    const res  = await fetch('/api/v1/admin-panel/mileage-items')
    const data = await res.json()
    mileageItemsList.value = data.items || []
  } catch (e) {
    console.error('載入里程兌換項目失敗', e)
  } finally {
    loadingMileageItems.value = false
  }
}

const openMileageItemForm = (item = null) => {
  if (item) {
    mileageItemForm.value = { show: true, submitting: false, id: item.id, name: item.name, short_desc: item.short_desc || '', details: item.details || '', logo_letter: item.logo_letter || 'S', logo_color: item.logo_color || '#ffffff', is_featured: Number(item.is_featured), featured_label: item.featured_label || '精選', is_active: Number(item.is_active), sort_order: item.sort_order || 0 }
  } else {
    mileageItemForm.value = { show: true, submitting: false, id: null, name: '', short_desc: '', details: '', logo_letter: 'S', logo_color: '#ffffff', is_featured: 0, featured_label: '精選', is_active: 1, sort_order: 0 }
  }
}

const submitMileageItem = async () => {
  const f = mileageItemForm.value
  if (!f.name.trim()) { alert('請填寫名稱'); return }
  f.submitting = true
  try {
    const url    = f.id ? `/api/v1/admin-panel/mileage-items/${f.id}` : '/api/v1/admin-panel/mileage-items'
    const method = f.id ? 'PUT' : 'POST'
    const body   = JSON.stringify({ name: f.name, short_desc: f.short_desc, details: f.details, logo_letter: f.logo_letter, logo_color: f.logo_color, is_featured: f.is_featured, featured_label: f.featured_label, is_active: f.is_active, sort_order: f.sort_order })
    const res    = await fetch(url, { method, headers: { 'Content-Type': 'application/json' }, body })
    if (!res.ok) { const d = await res.json(); alert(d.message || '操作失敗'); return }
    f.show = false
    await loadMileageItems()
  } catch (e) {
    alert('操作失敗，請稍後再試')
  } finally {
    f.submitting = false
  }
}

const deleteMileageItem = async (id) => {
  if (!confirm('確定要刪除此項目嗎？')) return
  try {
    await fetch(`/api/v1/admin-panel/mileage-items/${id}`, { method: 'DELETE' })
    await loadMileageItems()
  } catch (e) {
    alert('刪除失敗')
  }
}

// ── Skywards 權益 ────────────────────────────────────────────────────────────
const benefitsList    = ref([])
const loadingBenefits = ref(false)
const benefitForm = ref({ show: false, id: null, type: 'rule', label: '', content: '', is_active: 1, sort_order: 0, submitting: false })

const loadSkywardsBenefits = async () => {
  loadingBenefits.value = true
  try {
    const res  = await fetch('/api/v1/admin-panel/skywards-benefits')
    const data = await res.json()
    benefitsList.value = data.items || []
  } catch (e) {
    console.error('載入Skywards權益失敗', e)
  } finally {
    loadingBenefits.value = false
  }
}

const openBenefitForm = (item = null) => {
  if (item) {
    benefitForm.value = { show: true, submitting: false, id: item.id, type: item.type, label: item.label || '', content: item.content, is_active: Number(item.is_active), sort_order: item.sort_order || 0 }
  } else {
    benefitForm.value = { show: true, submitting: false, id: null, type: 'rule', label: '', content: '', is_active: 1, sort_order: 0 }
  }
}

const submitBenefit = async () => {
  const f = benefitForm.value
  if (!f.content.trim()) { alert('請填寫內容'); return }
  f.submitting = true
  try {
    const url    = f.id ? `/api/v1/admin-panel/skywards-benefits/${f.id}` : '/api/v1/admin-panel/skywards-benefits'
    const method = f.id ? 'PUT' : 'POST'
    const body   = JSON.stringify({ type: f.type, label: f.label, content: f.content, is_active: f.is_active, sort_order: f.sort_order })
    const res    = await fetch(url, { method, headers: { 'Content-Type': 'application/json' }, body })
    if (!res.ok) { const d = await res.json(); alert(d.message || '操作失敗'); return }
    f.show = false
    await loadSkywardsBenefits()
  } catch (e) {
    alert('操作失敗，請稍後再試')
  } finally {
    f.submitting = false
  }
}

const deleteBenefit = async (id) => {
  if (!confirm('確定要刪除此權益項目嗎？')) return
  try {
    await fetch(`/api/v1/admin-panel/skywards-benefits/${id}`, { method: 'DELETE' })
    await loadSkywardsBenefits()
  } catch (e) {
    alert('刪除失敗')
  }
}

onMounted(() => {
  // Disable max-width constraint
  const appEl = document.getElementById('app')
  if (appEl) {
    appEl.style.maxWidth = 'none'
    appEl.style.backgroundColor = '#f0f2f5'
    appEl.style.padding = '0'
  }

  updateView()
  loadUsers()
})

onUnmounted(() => {
  // Restore max-width constraint
  const appEl = document.getElementById('app')
  if (appEl) {
    appEl.style.maxWidth = ''
    appEl.style.backgroundColor = ''
    appEl.style.padding = ''
  }
})
</script>

<style scoped>
.admin-page {
  height: 100vh;
  display: flex;
  flex-direction: column;
  background-color: #f0f2f5;
  color: #1f2937;
  overflow: hidden;
}

.admin-header-bar {
  background: #ffffff;
  padding: 0 1.5rem;
  height: 60px;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #e5e7eb;
  flex-shrink: 0;
  z-index: 10;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.header-left h1 {
  margin: 0;
  font-size: 1.25rem;
  color: #111827;
  font-weight: 700;
}

.back-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.5rem;
  color: #6b7280;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.2s;
}

.back-btn:hover {
  background-color: #f3f4f6;
  color: #111827;
}

.bg-red-btn {
  background-color: #ef4444;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  font-size: 0.875rem;
  cursor: pointer;
  font-weight: 500;
  transition: background-color 0.2s;
}

.bg-red-btn:hover {
  background-color: #dc2626;
}

/* Layout */
.admin-layout {
  display: flex;
  flex: 1;
  overflow: hidden;
}

/* Sidebar */
.admin-sidebar {
  width: 280px;
  background: #ffffff;
  border-right: 1px solid #e5e7eb;
  display: flex;
  flex-direction: column;
  overflow-y: auto;
  flex-shrink: 0;
}

.sidebar-header {
  padding: 1rem 1.5rem;
  font-weight: 600;
  color: #6b7280;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  border-bottom: 1px solid #f3f4f6;
  background: #f9fafb;
}

.sidebar-list {
  padding: 0.5rem;
}

.sidebar-item {
  display: flex;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  cursor: pointer;
  margin-bottom: 0.25rem;
  transition: all 0.2s;
  align-items: center;
  gap: 0.75rem;
}

.sidebar-item:hover {
  background-color: #f9fafb;
}

.sidebar-item.active {
  background-color: #eff6ff;
  border: 1px solid #bfdbfe;
}

.sidebar-item.active .sidebar-item-title {
  color: #2563eb;
}

.sidebar-item.active .icon {
  color: #2563eb;
}

.sidebar-item-content {
  overflow: hidden;
}

.sidebar-item-title {
  font-weight: 600;
  color: #374151;
  font-size: 0.95rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.sidebar-item-key {
  font-size: 0.75rem;
  color: #9ca3af;
  font-family: monospace;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.delete-icon-btn {
  background: none;
  border: none;
  color: #9ca3af;
  opacity: 0;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  transition: all 0.2s;
}

.delete-icon-btn:hover {
  background-color: #fee2e2;
  color: #ef4444;
}

.sidebar-item:hover .delete-icon-btn {
  opacity: 1;
}

/* Main Content */
.admin-main {
  flex: 1;
  overflow-y: auto;
  padding: 2rem;
  background-color: #f8fafc;
}

.empty-selection {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: #9ca3af;
}

.empty-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.content-panel {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  border: 1px solid #e5e7eb;
  /* Ensure panel doesn't overflow horizontally without scroll inside */
  max-width: 100%; 
}

.panel-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #ffffff;
  border-radius: 12px 12px 0 0;
}

.panel-header h2 {
  margin: 0;
  font-size: 1.5rem;
  color: #111827;
  font-weight: 700;
}

.panel-badge {
  background: #f3f4f6;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-family: monospace;
  font-size: 0.875rem;
  color: #4b5563;
  border: 1px solid #e5e7eb;
}

.panel-body {
  padding: 1.5rem;
  overflow-x: auto;
}

/* Image View */
.image-viewer {
  display: flex;
  justify-content: center;
  background: #f9fafb;
  padding: 2rem;
  border-radius: 8px;
  border: 1px dashed #e5e7eb;
}

.image-viewer img {
  max-width: 100%;
  max-height: 600px;
  object-fit: contain;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  border-radius: 4px;
}

.thumbnail {
  width: 50px;
  height: 50px;
  object-fit: cover;
  border-radius: 4px;
  border: 1px solid #e5e7eb;
}

.preview-image {
  max-width: 300px;
  max-height: 200px;
  border-radius: 6px;
  border: 1px solid #e5e7eb;
  object-fit: contain;
  background: #f9fafb;
}

/* Table View */
.table-view {
  width: 100%;
  display: flex;
  flex-direction: column;
}

.table-container {
  border-radius: 8px;
  overflow: auto;
  border: 1px solid #e5e7eb;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
  max-width: 100%;
  max-height: calc(100vh - 200px); /* Limit height for vertical scroll */
}

table {
  width: max-content; /* Allow table to grow horizontally */
  min-width: 100%;
  border-collapse: collapse;
  font-size: 0.9rem;
  text-align: left;
}

th {
  background: #f9fafb;
  font-weight: 600;
  color: #374151;
  padding: 1rem;
  border-bottom: 1px solid #e5e7eb;
  white-space: nowrap;
  position: sticky;
  top: 0;
  z-index: 10;
  box-shadow: 0 1px 0 #e5e7eb; /* Separation line for sticky header */
}

.sub-header {
  display: block;
  font-size: 0.75rem;
  color: #9ca3af;
  font-family: monospace;
  font-weight: 400;
  margin-top: 0.25rem;
}

td {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #f3f4f6;
  color: #1f2937;
  vertical-align: middle;
  max-width: 300px; /* Limit cell width */
}

.cell-content {
  white-space: nowrap; 
  overflow: hidden;
  text-overflow: ellipsis;
}

.nested-obj {
  display: flex;
  flex-direction: column;
  gap: 4px;
  font-size: 0.85rem;
  background: #fff;
  padding: 4px;
  border-radius: 4px;
  border: 1px solid #f0f0f0;
}

.nested-item {
  display: flex;
  align-items: center;
  gap: 6px;
  max-width: 100%;
  overflow: hidden;
}

.nested-label {
  color: #6b7280;
  font-size: 0.75rem;
  flex-shrink: 0;
}

.nested-img {
  width: 40px;
  height: 25px;
  object-fit: cover;
  border: 1px solid #e5e7eb;
  border-radius: 2px;
  cursor: zoom-in;
}

.nested-text {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  color: #374151;
}



tr:last-child td {
  border-bottom: none;
}

tr:hover td {
  background: #f9fafb;
}

/* Object View */
.kv-table {
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

.kv-row {
  display: flex;
  border-bottom: 1px solid #e5e7eb;
}

.kv-row:last-child {
  border-bottom: none;
}

.kv-row:hover {
  background-color: #f9fafb;
}

.kv-key {
  width: 20%;
  min-width: 180px;
  background: #f9fafb;
  padding: 1rem;
  border-right: 1px solid #e5e7eb;
  flex-shrink: 0;
}

.kv-label-primary {
  font-weight: 600;
  color: #374151;
  font-size: 0.95rem;
}

.kv-label-secondary {
  font-size: 0.75rem;
  color: #9ca3af;
  font-family: monospace;
  margin-top: 0.25rem;
}

.kv-value {
  padding: 1rem;
  flex: 1;
  overflow-x: auto;
  color: #1f2937;
  display: flex;
  align-items: center;
}

.simple-view {
    background: #1f2937;
    border-radius: 8px;
    padding: 1.5rem;
    color: #e5e7eb;
}

.simple-view pre {
  margin: 0;
  white-space: pre-wrap;
  word-break: break-all;
  font-family: monospace;
}

/* User table specifics */
.user-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.875rem;
}

.user-table th {
  background: #f9fafb;
  font-weight: 600;
  color: #374151;
  padding: 0.75rem 1rem;
  text-align: left;
  border-bottom: 2px solid #e5e7eb;
  white-space: nowrap;
}

.user-table td {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #f3f4f6;
  color: #1f2937;
  vertical-align: middle;
  max-width: none;
}

.user-table tr:hover td {
  background: #f9fafb;
}

.amount-cell {
  font-family: monospace;
  font-weight: 600;
  color: #065f46;
}

.role-badge, .verify-badge, .bank-badge {
  display: inline-block;
  padding: 0.2rem 0.6rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 600;
}

.role-badge.admin { background: #fef3c7; color: #92400e; }
.role-badge.user  { background: #eff6ff; color: #1d4ed8; }

.verify-badge.approved { background: #d1fae5; color: #065f46; }
.verify-badge.pending  { background: #fef9c3; color: #854d0e; }
.verify-badge.rejected { background: #fee2e2; color: #991b1b; }
.verify-badge.none     { background: #f3f4f6; color: #6b7280; }

.bank-badge.yes { background: #d1fae5; color: #065f46; }
.bank-badge.no  { background: #f3f4f6; color: #6b7280; }

.email-verified-badge {
  display: inline-block;
  padding: 0.2rem 0.6rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 600;
}
.email-verified-badge.yes { background: #d1fae5; color: #065f46; }
.email-verified-badge.no  { background: #fee2e2; color: #991b1b; }

.deposit-btn {
  background: #d71921;
  color: #fff;
  border: none;
  padding: 0.4rem 0.9rem;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}
.deposit-btn:hover { background: #b71418; }

.refresh-btn {
  background: #f3f4f6;
  border: 1px solid #d1d5db;
  padding: 0.4rem 0.9rem;
  border-radius: 6px;
  font-size: 0.85rem;
  cursor: pointer;
  color: #374151;
}
.refresh-btn:hover { background: #e5e7eb; }
.refresh-btn:disabled { opacity: 0.6; cursor: default; }

.loading-state, .empty-state {
  padding: 3rem;
  text-align: center;
  color: #9ca3af;
}

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.45);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-box {
  background: #fff;
  border-radius: 12px;
  width: 420px;
  max-width: 90vw;
  box-shadow: 0 20px 60px rgba(0,0,0,0.2);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 700;
  color: #111827;
}

.modal-close {
  background: none;
  border: none;
  font-size: 1.1rem;
  cursor: pointer;
  color: #9ca3af;
  padding: 0.25rem;
}
.modal-close:hover { color: #374151; }

.modal-body {
  padding: 1.5rem;
}

.modal-user-info {
  background: #f9fafb;
  border-radius: 8px;
  padding: 0.75rem 1rem;
  margin-bottom: 1.25rem;
  border: 1px solid #e5e7eb;
}

.modal-user-name  { font-weight: 700; color: #111827; font-size: 1rem; }
.modal-user-email { font-size: 0.85rem; color: #6b7280; margin-top: 0.2rem; }
.modal-user-balance { font-size: 0.875rem; color: #374151; margin-top: 0.5rem; }

.modal-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.4rem;
}

.modal-input {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 0.95rem;
  color: #111827;
  box-sizing: border-box;
  outline: none;
  transition: border-color 0.2s;
}
.modal-input:focus { border-color: #d71921; }

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  border-top: 1px solid #e5e7eb;
}

.modal-cancel-btn {
  background: #f3f4f6;
  border: 1px solid #d1d5db;
  padding: 0.6rem 1.2rem;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.9rem;
  color: #374151;
}
.modal-cancel-btn:hover { background: #e5e7eb; }

.modal-submit-btn {
  background: #d71921;
  color: #fff;
  border: none;
  padding: 0.6rem 1.4rem;
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}
.modal-submit-btn:hover:not(:disabled) { background: #b71418; }
.modal-submit-btn:disabled { opacity: 0.6; cursor: default; }

.add-btn {
  background: #16a34a;
  color: #fff;
  border: none;
  padding: 0.4rem 0.9rem;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}
.add-btn:hover { background: #15803d; }

.edit-btn {
  background: #2563eb;
  color: #fff;
  border: none;
  padding: 0.3rem 0.7rem;
  border-radius: 5px;
  font-size: 0.78rem;
  font-weight: 600;
  cursor: pointer;
  margin-right: 0.4rem;
  transition: background 0.2s;
}
.edit-btn:hover { background: #1d4ed8; }

.delete-item-btn {
  background: #ef4444;
  color: #fff;
  border: none;
  padding: 0.3rem 0.7rem;
  border-radius: 5px;
  font-size: 0.78rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}
.delete-item-btn:hover { background: #dc2626; }

.status-badge {
  display: inline-block;
  padding: 0.15rem 0.5rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 600;
}
.status-badge.active   { background: #d1fae5; color: #065f46; }
.status-badge.inactive { background: #f3f4f6; color: #6b7280; }

.type-badge {
  display: inline-block;
  padding: 0.15rem 0.5rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 600;
  background: #eff6ff;
  color: #1d4ed8;
}
.type-badge.note { background: #fef9c3; color: #854d0e; }
.type-badge.hint { background: #f3f4f6; color: #374151; }

.color-swatch {
  display: inline-block;
  width: 14px;
  height: 14px;
  border-radius: 3px;
  border: 1px solid #d1d5db;
  vertical-align: middle;
  margin-right: 4px;
}
</style>