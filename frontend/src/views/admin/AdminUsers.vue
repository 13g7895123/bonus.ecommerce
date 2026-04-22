<template>
  <div class="panel">
    <div class="panel-header">
      <span class="panel-title"></span>
      <div style="display:flex;gap:0.5rem">
        <button class="btn btn-primary" @click="openCreateUser"><Plus :size="14" />新增使用者</button>
        <button class="btn btn-outline" :disabled="loadingUsers" @click="loadUsers">
          <RefreshCw :size="14" />{{ loadingUsers ? '載入中...' : '重新整理' }}
        </button>
      </div>
    </div>
    <div class="table-wrap">
      <div v-if="loadingUsers" class="state-msg">載入中...</div>
      <table v-else class="data-table">
        <thead>
          <tr>
            <th>姓名</th><th>Email</th><th>Email驗證</th>
            <th>電話</th><th>國家</th><th>角色</th><th>KYC</th>
            <th>餘額</th><th>里程</th><th>操作</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="u in usersList" :key="u.id">
            <td class="td-name">
              <div style="display:flex;align-items:center;gap:0.5rem">
                <img v-if="u.avatar" :src="normalizeFileUrl(u.avatar)" style="width:28px;height:28px;border-radius:50%;object-fit:cover;flex-shrink:0;border:1px solid #e2e8f0" alt="頭像" />
                <span v-else style="width:28px;height:28px;border-radius:50%;background:#e2e8f0;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;font-size:0.75rem;color:#94a3b8;font-weight:700">{{ (u.full_name || u.email || '?')[0].toUpperCase() }}</span>
                {{ u.full_name || '-' }}
              </div>
            </td>
            <td>{{ u.email }}</td>
            <td><span :class="['badge', u.is_verified == 1 ? 'badge-green' : 'badge-gray']">{{ u.is_verified == 1 ? '已驗證' : '未驗證' }}</span></td>
            <td>{{ u.phone || '-' }}</td>
            <td>{{ countryName(u.country) }}</td>
            <td><span :class="['badge', u.role === 'admin' ? 'badge-purple' : 'badge-blue']">{{ u.role || 'user' }}</span></td>
            <td><span :class="['badge', kycBadgeClass(u.verify_status)]">{{ kycLabel(u.verify_status) }}</span></td>
            <td class="td-num">${{ (u.balance || 0).toLocaleString() }}</td>
            <td class="td-num">{{ (u.miles_balance || 0).toLocaleString() }}</td>
            <td class="td-actions">
              <button class="btn btn-sm btn-primary" @click="openDeposit(u)">儲值</button>
              <button class="btn btn-sm btn-outline" @click="openChangePassword(u)">變更密碼</button>
              <button class="btn btn-sm btn-outline" style="border-color:#f59e0b;color:#f59e0b" @click="openChangeWithdrawalPassword(u)">提款密碼</button>
              <button class="btn btn-sm btn-outline" style="border-color:#6366f1;color:#6366f1" @click="openUserDetail(u)">詳細資料</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- 儲值 Modal -->
  <div v-if="depositModal.show" class="modal-overlay" @click.self="depositModal.show = false">
    <div class="modal-box">
      <div class="modal-hd">
        <span>儲值 — {{ depositModal.user?.full_name || depositModal.user?.email }}</span>
        <button class="modal-x" @click="depositModal.show = false">✕</button>
      </div>
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

  <!-- 變更密碼 Modal -->
  <div v-if="pwdModal.show" class="modal-overlay" @click.self="pwdModal.show = false">
    <div class="modal-box" style="max-width:420px">
      <div class="modal-hd">
        <span>變更密碼 — {{ pwdModal.user?.full_name || pwdModal.user?.email }}</span>
        <button class="modal-x" @click="pwdModal.show = false">✕</button>
      </div>
      <div class="modal-bd">
        <label class="f-label">新密碼（至少 6 字元）</label>
        <div class="pwd-wrap"><input v-model="pwdModal.newPassword" :type="showPwd['pwd_new'] ? 'text' : 'password'" class="f-input" placeholder="請輸入新密碼" autocomplete="new-password" /><button type="button" class="pwd-eye" @click="togglePwd('pwd_new')" tabindex="-1"><svg v-if="showPwd['pwd_new']" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg><svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button></div>
        <label class="f-label" style="margin-top:0.75rem">確認新密碼</label>
        <div class="pwd-wrap"><input v-model="pwdModal.confirmPassword" :type="showPwd['pwd_confirm'] ? 'text' : 'password'" class="f-input" placeholder="請再次輸入新密碼" autocomplete="new-password" @keyup.enter="submitChangePassword" /><button type="button" class="pwd-eye" @click="togglePwd('pwd_confirm')" tabindex="-1"><svg v-if="showPwd['pwd_confirm']" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg><svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button></div>
      </div>
      <div class="modal-ft">
        <button class="btn btn-outline" @click="pwdModal.show = false">取消</button>
        <button class="btn btn-primary" :disabled="pwdModal.submitting" @click="submitChangePassword">{{ pwdModal.submitting ? '處理中...' : '確認變更' }}</button>
      </div>
    </div>
  </div>

  <!-- 新增使用者 Modal -->
  <div v-if="createUserModal.show" class="modal-overlay" @click.self="createUserModal.show = false">
    <div class="modal-box" style="max-width:460px">
      <div class="modal-hd">
        <span>新增使用者</span>
        <button class="modal-x" @click="createUserModal.show = false">✕</button>
      </div>
      <div class="modal-bd">
        <label class="f-label">Email *</label>
        <input v-model="createUserModal.email" type="email" class="f-input" placeholder="user@example.com" autocomplete="off" />
        <label class="f-label" style="margin-top:0.75rem">密碼（至少 6 字元）*</label>
        <div class="pwd-wrap"><input v-model="createUserModal.password" :type="showPwd['create_pwd'] ? 'text' : 'password'" class="f-input" placeholder="請輸入密碼" autocomplete="new-password" /><button type="button" class="pwd-eye" @click="togglePwd('create_pwd')" tabindex="-1"><svg v-if="showPwd['create_pwd']" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg><svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button></div>
        <label class="f-label" style="margin-top:0.75rem">確認密碼 *</label>
        <div class="pwd-wrap"><input v-model="createUserModal.confirmPassword" :type="showPwd['create_confirm'] ? 'text' : 'password'" class="f-input" placeholder="請再次輸入密碼" autocomplete="new-password" /><button type="button" class="pwd-eye" @click="togglePwd('create_confirm')" tabindex="-1"><svg v-if="showPwd['create_confirm']" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg><svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button></div>
        <label class="f-label" style="margin-top:0.75rem">姓名（選填）</label>
        <input v-model="createUserModal.full_name" type="text" class="f-input" placeholder="中文姓名" />
        <label class="f-label" style="margin-top:0.75rem">手機（選填）</label>
        <input v-model="createUserModal.phone" type="text" class="f-input" placeholder="0912345678" />
        <label class="f-label" style="margin-top:0.75rem">角色</label>
        <select v-model="createUserModal.role" class="f-input">
          <option value="user">一般使用者 (user)</option>
          <option value="admin">管理員 (admin)</option>
        </select>
      </div>
      <div class="modal-ft">
        <button class="btn btn-outline" @click="createUserModal.show = false">取消</button>
        <button class="btn btn-primary" :disabled="createUserModal.submitting" @click="submitCreateUser">{{ createUserModal.submitting ? '建立中...' : '確認建立' }}</button>
      </div>
    </div>
  </div>

  <!-- 變更提款密碼 Modal -->
  <div v-if="wdPwdModal.show" class="modal-overlay" @click.self="wdPwdModal.show = false">
    <div class="modal-box" style="max-width:420px">
      <div class="modal-hd">
        <span>變更提款密碼 — {{ wdPwdModal.user?.full_name || wdPwdModal.user?.email }}</span>
        <button class="modal-x" @click="wdPwdModal.show = false">✕</button>
      </div>
      <div class="modal-bd">
        <label class="f-label">新提款密碼（至少 4 字元）</label>
        <div class="pwd-wrap"><input v-model="wdPwdModal.newPassword" :type="showPwd['wd_new'] ? 'text' : 'password'" class="f-input" placeholder="請輸入新提款密碼" autocomplete="new-password" /><button type="button" class="pwd-eye" @click="togglePwd('wd_new')" tabindex="-1"><svg v-if="showPwd['wd_new']" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg><svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button></div>
        <label class="f-label" style="margin-top:0.75rem">確認新提款密碼</label>
        <div class="pwd-wrap"><input v-model="wdPwdModal.confirmPassword" :type="showPwd['wd_confirm'] ? 'text' : 'password'" class="f-input" placeholder="請再次輸入新提款密碼" autocomplete="new-password" @keyup.enter="submitChangeWithdrawalPassword" /><button type="button" class="pwd-eye" @click="togglePwd('wd_confirm')" tabindex="-1"><svg v-if="showPwd['wd_confirm']" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg><svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button></div>
      </div>
      <div class="modal-ft">
        <button class="btn btn-outline" @click="wdPwdModal.show = false">取消</button>
        <button class="btn btn-primary" :disabled="wdPwdModal.submitting" @click="submitChangeWithdrawalPassword">{{ wdPwdModal.submitting ? '處理中...' : '確認變更' }}</button>
      </div>
    </div>
  </div>

  <!-- 詳細資料 Modal -->
  <div v-if="detailModal.show" class="modal-overlay" @click.self="detailModal.show = false">
    <div class="modal-box" style="max-width:620px">
      <div class="modal-hd">
        <span>使用者詳細資料 — {{ detailModal.user?.email }}</span>
        <button class="modal-x" @click="detailModal.show = false">✕</button>
      </div>
      <div class="modal-bd">
        <div v-if="detailModal.loading" class="state-msg">載入中...</div>
        <template v-else-if="detailModal.user">            <!-- 頭像 -->
            <div style="display:flex;justify-content:center;margin-bottom:1.25rem">
              <img v-if="detailModal.user.avatar" :src="normalizeFileUrl(detailModal.user.avatar)" style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:2px solid #e2e8f0" alt="頭像" />
              <div v-else style="width:80px;height:80px;border-radius:50%;background:#e2e8f0;display:flex;align-items:center;justify-content:center;font-size:1.75rem;color:#94a3b8;font-weight:700">{{ (detailModal.user.full_name || detailModal.user.email || '?')[0].toUpperCase() }}</div>
            </div>          <div class="detail-section">
            <div class="detail-section-title">基本資料</div>
            <div class="kyc-rows">
              <div class="kyc-row"><span class="kyc-lbl">ID</span><span>{{ detailModal.user.id }}</span></div>
              <div class="kyc-row"><span class="kyc-lbl">Email</span><span>{{ detailModal.user.email }}</span></div>
              <div class="kyc-row"><span class="kyc-lbl">姓名</span><span>{{ detailModal.user.full_name || '-' }}</span></div>
              <div class="kyc-row"><span class="kyc-lbl">電話</span><span>{{ detailModal.user.phone || '-' }}</span></div>
              <div class="kyc-row"><span class="kyc-lbl">生日</span><span>{{ detailModal.user.dob || '-' }}</span></div>
              <div class="kyc-row"><span class="kyc-lbl">國家</span><span>{{ countryName(detailModal.user.country) }}</span></div>
              <div class="kyc-row"><span class="kyc-lbl">角色</span><span>{{ detailModal.user.role || 'user' }}</span></div>
              <div class="kyc-row"><span class="kyc-lbl">會員等級</span><span>{{ detailModal.user.tier || '-' }}</span></div>
              <div class="kyc-row"><span class="kyc-lbl">Email驗證</span><span>{{ detailModal.user.is_verified == 1 ? '已驗證' : '未驗證' }}</span></div>
              <div class="kyc-row"><span class="kyc-lbl">帳號狀態</span><span>{{ detailModal.user.status || '-' }}</span></div>
              <div class="kyc-row"><span class="kyc-lbl">餘額</span><span>${{ (detailModal.user.balance || 0).toLocaleString() }}</span></div>
              <div class="kyc-row"><span class="kyc-lbl">里程</span><span>{{ (detailModal.user.miles_balance || 0).toLocaleString() }}</span></div>
              <div class="kyc-row"><span class="kyc-lbl">已綁定銀行</span><span>{{ detailModal.user.has_bank_account ? '是' : '否' }}</span></div>
              <div class="kyc-row"><span class="kyc-lbl">註冊時間</span><span>{{ detailModal.user.created_at }}</span></div>
            </div>
          </div>

          <!-- KYC 資料 -->
          <template v-if="detailModal.user.verify_status && detailModal.user.verify_status !== 'none' && detailModal.user.verification_data">
            <div class="detail-section" style="margin-top:1.25rem">
              <div class="detail-section-title">
                實名認證資料
                <span :class="['badge', kycBadgeClass(detailModal.user.verify_status)]" style="margin-left:0.5rem;font-size:0.75rem">{{ kycLabel(detailModal.user.verify_status) }}</span>
              </div>
              <div class="kyc-rows">
                <div class="kyc-row"><span class="kyc-lbl">真實姓名</span><span>{{ detailModal.user.verification_data.real_name || detailModal.user.verification_data.fullName || '-' }}</span></div>
                <div class="kyc-row"><span class="kyc-lbl">身分證字號</span><span>{{ detailModal.user.verification_data.id_number || detailModal.user.verification_data.idNumber || '-' }}</span></div>
                <div v-if="detailModal.user.verification_data.reject_reason" class="kyc-row">
                  <span class="kyc-lbl">拒絕原因</span>
                  <span style="color:#ef4444">{{ detailModal.user.verification_data.reject_reason }}</span>
                </div>
              </div>
              <!-- 上傳照片 -->
              <div class="kyc-imgs" style="margin-top:0.75rem">
                <template v-if="detailModal.user.verification_data.file_ids">
                  <div v-if="detailModal.user.verification_data.file_ids.front" class="kyc-img-wrap">
                    <div class="kyc-img-label">身分證正面</div>
                    <img :src="`/api/v1/files/${detailModal.user.verification_data.file_ids.front}/serve`" class="kyc-img kyc-img-zoom" @click="openLightbox(`/api/v1/files/${detailModal.user.verification_data.file_ids.front}/serve`)" />
                  </div>
                  <div v-if="detailModal.user.verification_data.file_ids.back" class="kyc-img-wrap">
                    <div class="kyc-img-label">身分證背面</div>
                    <img :src="`/api/v1/files/${detailModal.user.verification_data.file_ids.back}/serve`" class="kyc-img kyc-img-zoom" @click="openLightbox(`/api/v1/files/${detailModal.user.verification_data.file_ids.back}/serve`)" />
                  </div>
                  <div v-if="detailModal.user.verification_data.file_ids.handheld" class="kyc-img-wrap">
                    <div class="kyc-img-label">手持身分證</div>
                    <img :src="`/api/v1/files/${detailModal.user.verification_data.file_ids.handheld}/serve`" class="kyc-img kyc-img-zoom" @click="openLightbox(`/api/v1/files/${detailModal.user.verification_data.file_ids.handheld}/serve`)" />
                  </div>
                </template>
                <template v-else>
                  <div v-if="detailModal.user.verification_data.front_image_url || detailModal.user.verification_data.frontImageUrl" class="kyc-img-wrap">
                    <div class="kyc-img-label">身分證正面</div>
                    <img :src="detailModal.user.verification_data.front_image_url || detailModal.user.verification_data.frontImageUrl" class="kyc-img kyc-img-zoom" @click="openLightbox(detailModal.user.verification_data.front_image_url || detailModal.user.verification_data.frontImageUrl)" />
                  </div>
                  <div v-if="detailModal.user.verification_data.back_image_url || detailModal.user.verification_data.backImageUrl" class="kyc-img-wrap">
                    <div class="kyc-img-label">身分證背面</div>
                    <img :src="detailModal.user.verification_data.back_image_url || detailModal.user.verification_data.backImageUrl" class="kyc-img kyc-img-zoom" @click="openLightbox(detailModal.user.verification_data.back_image_url || detailModal.user.verification_data.backImageUrl)" />
                  </div>
                </template>
              </div>
            </div>
          </template>

          <!-- 銀行資料 -->
          <div class="detail-section" style="margin-top:1.25rem">
            <div class="detail-section-title" style="display:flex;align-items:center;justify-content:space-between">
              <span>提款銀行資料</span>
              <div style="display:flex;gap:0.5rem">
                <button class="btn btn-sm btn-outline" @click="openBankEdit(detailModal.user)">編輯</button>
                <button v-if="detailModal.user.has_bank_account" class="btn btn-sm btn-danger" @click="deleteBankInfo(detailModal.user)">刪除</button>
              </div>
            </div>
            <div class="kyc-rows">
              <template v-if="detailModal.user.has_bank_account && detailModal.user.bank_info">
                <div class="kyc-row"><span class="kyc-lbl">銀行名稱</span><span>{{ detailModal.user.bank_info.bank_name || '—' }}</span></div>
                <div class="kyc-row"><span class="kyc-lbl">分行</span><span>{{ detailModal.user.bank_info.bank_branch || '—' }}</span></div>
                <div class="kyc-row"><span class="kyc-lbl">帳號</span><span>{{ detailModal.user.bank_info.bank_account || '—' }}</span></div>
                <div class="kyc-row"><span class="kyc-lbl">戶名</span><span>{{ detailModal.user.bank_info.bank_account_name || '—' }}</span></div>
              </template>
              <div v-else class="kyc-row" style="color:#94a3b8">尚未綁定銀行帳戶</div>
            </div>
          </div>
        </template>
      </div>
      <div class="modal-ft"><button class="btn btn-outline" @click="detailModal.show = false">關閉</button></div>
    </div>
  </div>

  <!-- 圖片放大 Lightbox -->
  <div v-if="lightboxSrc" class="lightbox-overlay" @click="lightboxSrc = null">
    <img :src="lightboxSrc" class="lightbox-img" @click.stop />
    <button class="lightbox-close" @click="lightboxSrc = null">✕</button>
  </div>

  <!-- 編輯銀行資料 Modal -->
  <Teleport to="body">
    <div v-if="bankModal.show"
      style="position:fixed;inset:0;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:9999;padding:1rem"
      @click.self="bankModal.show = false">
      <div style="width:100%;max-width:420px;background:#fff;border-radius:12px;padding:1.5rem;box-shadow:0 20px 60px rgba(0,0,0,0.3)">
        <div class="modal-hd">
          <span>編輯銀行資料 — {{ bankModal.user?.full_name || bankModal.user?.email }}</span>
          <button class="modal-x" @click="bankModal.show = false">✕</button>
        </div>
        <div class="modal-bd">
          <label class="f-label">銀行名稱</label>
          <input v-model="bankModal.bank_name" class="f-input" placeholder="例：玉山銀行" />
          <label class="f-label" style="margin-top:0.75rem">分行</label>
          <input v-model="bankModal.bank_branch" class="f-input" placeholder="例：信義分行" />
          <label class="f-label" style="margin-top:0.75rem">銀行帳號</label>
          <input v-model="bankModal.bank_account" class="f-input" placeholder="例：1234567890123" />
          <label class="f-label" style="margin-top:0.75rem">戶名</label>
          <input v-model="bankModal.bank_account_name" class="f-input" placeholder="帳戶戴名" />
        </div>
        <div class="modal-ft">
          <button class="btn btn-outline" @click="bankModal.show = false">取消</button>
          <button class="btn btn-primary" :disabled="bankModal.submitting" @click="submitBankEdit">
            {{ bankModal.submitting ? '處理中...' : '儲存' }}
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RefreshCw, Plus } from 'lucide-vue-next'
import { countries } from '../../utils/countries'

const countryName = (code) => {
  if (!code) return '-'
  const c = countries.find(c => c.code === code)
  return c ? c.name : code
}

const usersList    = ref([])
const loadingUsers = ref(false)

const kycLabel      = (s) => ({ approved: '已通過', verified: '已通過', pending: '待審核', rejected: '未通過', none: '未提交' }[s] || '未提交')
const kycBadgeClass = (s) => ({ approved: 'badge-green', verified: 'badge-green', pending: 'badge-yellow', rejected: 'badge-red', none: 'badge-gray' }[s] || 'badge-gray')

// 統一處理頭像/檔案 URL：將舊 domain 的絕對路徑轉為相對路徑，避免跨域或 domain 變更問題
const normalizeFileUrl = (url) => {
  if (!url) return null
  if (url.startsWith('http')) {
    try { return new URL(url).pathname } catch (e) { return url }
  }
  return url
}

const loadUsers = async () => {
  loadingUsers.value = true
  try {
    const res  = await fetch('/api/v1/admin-panel/users?limit=200')
    const data = await res.json()
    usersList.value = data.items || []
  } finally { loadingUsers.value = false }
}

// ── 儲值 ──
const depositModal = ref({ show: false, user: null, amount: '', description: '', submitting: false })
const openDeposit  = (user) => { depositModal.value = { show: true, user, amount: '', description: '', submitting: false } }

const submitDeposit = async () => {
  const amount = parseFloat(depositModal.value.amount)
  if (!amount || amount <= 0) { alert('請輸入有效的正數金額'); return }
  depositModal.value.submitting = true
  try {
    const res  = await fetch(`/api/v1/admin-panel/users/${depositModal.value.user.id}/deposit`, {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ amount, description: depositModal.value.description || '管理員儲值' }),
    })
    const data = await res.json()
    if (!res.ok) { alert(data.message || '儲值失敗'); return }
    const u = usersList.value.find(u => u.id === depositModal.value.user.id)
    if (u) u.balance = data.balance
    depositModal.value.user.balance = data.balance
    alert(`儲值成功！新餘額：$${Number(data.balance).toLocaleString()}`)
    depositModal.value.show = false
  } finally { depositModal.value.submitting = false }
}

// ── 變更密碼 ──
const pwdModal = ref({ show: false, user: null, newPassword: '', confirmPassword: '', submitting: false })
const showPwd = ref({})
const togglePwd = (key) => { showPwd.value[key] = !showPwd.value[key] }
const openChangePassword = (user) => { pwdModal.value = { show: true, user, newPassword: '', confirmPassword: '', submitting: false } }

const submitChangePassword = async () => {
  if (pwdModal.value.newPassword.length < 6) { alert('密碼至少需要 6 個字元'); return }
  if (pwdModal.value.newPassword !== pwdModal.value.confirmPassword) { alert('兩次輸入的密碼不一致'); return }
  pwdModal.value.submitting = true
  try {
    const res  = await fetch(`/api/v1/admin-panel/users/${pwdModal.value.user.id}/change-password`, {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ new_password: pwdModal.value.newPassword }),
    })
    const data = await res.json()
    if (!res.ok) { alert(data.message || '密碼變更失敗'); return }
    alert('密碼已成功變更')
    pwdModal.value.show = false
  } finally { pwdModal.value.submitting = false }
}

// ── 變更提款密碼 ──
const wdPwdModal = ref({ show: false, user: null, newPassword: '', confirmPassword: '', submitting: false })
const openChangeWithdrawalPassword = (user) => { wdPwdModal.value = { show: true, user, newPassword: '', confirmPassword: '', submitting: false } }

const submitChangeWithdrawalPassword = async () => {
  if (wdPwdModal.value.newPassword.length < 4) { alert('提款密碼至少需要 4 個字元'); return }
  if (wdPwdModal.value.newPassword !== wdPwdModal.value.confirmPassword) { alert('兩次輸入的密碼不一致'); return }
  wdPwdModal.value.submitting = true
  try {
    const res  = await fetch(`/api/v1/admin-panel/users/${wdPwdModal.value.user.id}/change-withdrawal-password`, {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ new_password: wdPwdModal.value.newPassword }),
    })
    const data = await res.json()
    if (!res.ok) { alert(data.message || '提款密碼變更失敗'); return }
    alert('提款密碼已成功變更')
    wdPwdModal.value.show = false
  } finally { wdPwdModal.value.submitting = false }
}

const lightboxSrc  = ref(null)
const openLightbox = (src) => { lightboxSrc.value = src }

// ── 詳細資料 ──
const detailModal = ref({ show: false, user: null, loading: false })

const openUserDetail = async (user) => {
  detailModal.value = { show: true, user: { ...user }, loading: true }
  try {
    const res  = await fetch(`/api/v1/admin-panel/users/${user.id}`)
    const data = await res.json()
    detailModal.value.user    = data
    detailModal.value.loading = false
  } catch {
    detailModal.value.loading = false
  }
}

// ── 新增使用者 ──
const createUserModal = ref({ show: false, email: '', password: '', confirmPassword: '', full_name: '', phone: '', role: 'user', submitting: false })

const openCreateUser = () => {
  createUserModal.value = { show: true, email: '', password: '', confirmPassword: '', full_name: '', phone: '', role: 'user', submitting: false }
}

const submitCreateUser = async () => {
  const f = createUserModal.value
  if (!f.email) { alert('請輸入 Email'); return }
  if (f.password.length < 6) { alert('密碼至少需要 6 個字元'); return }
  if (f.password !== f.confirmPassword) { alert('兩次輸入的密碼不一致'); return }
  f.submitting = true
  try {
    const res  = await fetch('/api/v1/admin-panel/users', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email: f.email, password: f.password, full_name: f.full_name, phone: f.phone, role: f.role }),
    })
    const data = await res.json()
    if (!res.ok) { alert(data.message || '建立失敗'); return }
    alert('使用者已成功建立')
    createUserModal.value.show = false
    await loadUsers()
  } finally { f.submitting = false }
}

// ── 銀行資料 ──
const bankModal = ref({ show: false, user: null, bank_name: '', bank_branch: '', bank_account: '', bank_account_name: '', submitting: false })

const openBankEdit = (user) => {
  const b = user.bank_info || {}
  bankModal.value = {
    show: true, user, submitting: false,
    bank_name:         b.bank_name || '',
    bank_branch:       b.bank_branch || '',
    bank_account:      b.bank_account || '',
    bank_account_name: b.bank_account_name || '',
  }
}

const submitBankEdit = async () => {
  const f = bankModal.value
  f.submitting = true
  try {
    const res  = await fetch(`/api/v1/admin-panel/users/${f.user.id}/bank`, {
      method: 'PUT', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ bank_name: f.bank_name, bank_branch: f.bank_branch, bank_account: f.bank_account, bank_account_name: f.bank_account_name }),
    })
    const data = await res.json()
    if (!res.ok) { alert(data.message || '更新失敗'); return }
    // Update local state
    detailModal.value.user.bank_info = { bank_name: f.bank_name, bank_branch: f.bank_branch, bank_account: f.bank_account, bank_account_name: f.bank_account_name }
    detailModal.value.user.has_bank_account = !!f.bank_account
    const u = usersList.value.find(u => u.id === f.user.id)
    if (u) u.has_bank_account = !!f.bank_account
    alert('銀行資料已更新')
    bankModal.value.show = false
  } finally { f.submitting = false }
}

const deleteBankInfo = async (user) => {
  if (!confirm('確定要刪除此使用者的銀行資料嗎？')) return
  const res  = await fetch(`/api/v1/admin-panel/users/${user.id}/bank`, { method: 'DELETE' })
  const data = await res.json()
  if (!res.ok) { alert(data.message || '刪除失敗'); return }
  detailModal.value.user.bank_info = null
  detailModal.value.user.has_bank_account = false
  const u = usersList.value.find(u => u.id === user.id)
  if (u) u.has_bank_account = false
  alert('銀行資料已刪除')
}

onMounted(loadUsers)
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
.pwd-wrap { position: relative; }
.pwd-wrap .f-input { padding-right: 2.5rem; }
.pwd-eye { position: absolute; right: 0.6rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #888; padding: 0; display: flex; align-items: center; }
.pwd-eye:hover { color: #333; }
</style>
