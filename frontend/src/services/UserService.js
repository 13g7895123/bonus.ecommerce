// src/services/UserService.js
import { BaseService } from './BaseService';
import { fileService } from './FileService';
import { mockDb } from './mockDb';

export class UserService extends BaseService {
  constructor() {
    super('/users', 'users');
    this.table = 'users';
  }

  /* 取得個人資料 — GET /users/me */
  async getProfile(userId) {
    if (this.useMock) {
      return this._get(`/${userId}`, async () => {
        const user = await mockDb.findOne(this.table, u => u.id === userId);
        if (!user) throw new Error('用戶不存在');
        return user;
      });
    }
    const data = await this._get('/me');
    // 映射欄位以相容舊版 view
    return {
      ...data,
      name: data.full_name || '',
      miles: data.wallet?.miles_balance ?? 0,
      wallet: {
        ...data.wallet,
        // view 存取 wallet.bank.account，has_bank_account=true 時給予佔位值
        bank: data.wallet?.has_bank_account ? { account: data.wallet.bank_account_masked || '已綁定' } : null,
      },
    };
  }

  /* 更新個人資料 — PUT /users/me */
  async updateProfile(userId, updates) {
    if (this.useMock) {
      return this._post(`/${userId}`, updates, async () => {
        const user = await mockDb.findOne(this.table, u => u.id === userId);
        if (!user) throw new Error('用戶不存在');
        await mockDb.update(this.table, userId, updates);
        return { message: '更新成功' };
      });
    }
    // 只傳 API 支援的欄位
    const payload = {};
    if (updates.full_name !== undefined) payload.full_name = updates.full_name;
    if (updates.phone !== undefined) payload.phone = updates.phone;
    if (updates.dob !== undefined) payload.dob = updates.dob;
    if (updates.country !== undefined) payload.country = updates.country;
    return this._put('/me', payload);
  }

  /* 上傳頭像 — 透過 FileService，POST /files/upload (type=avatar) */
  async uploadAvatar(userId, file) {
    const result = await fileService.upload(file, 'avatar')
    // 真實模式：呼叫 users/me/avatar 更新 DB 中的 avatar 欄位
    if (!this.useMock) {
      const formData = new FormData()
      formData.append('avatar', file)
      await this.http.post('/users/me/avatar', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
    } else {
      // Mock 模式：更新 localStorage
      await mockDb.findOne(this.table, u => u.id === userId).then(user => {
        if (user) mockDb.update(this.table, userId, { avatar: result.url })
      })
    }
    return { avatar_url: result.url, file: result }
  }

  /* 更改登入密碼 — PUT /users/me/password */
  async changePassword(newPassword, confirmPassword) {
    if (this.useMock) {
      return { message: '密碼已更新' }
    }
    const response = await this.http.put('/users/me/password', {
      new_password:     newPassword,
      confirm_password: confirmPassword,
    })
    return response.data
  }

  /* 提交身份驗證 — 接受 file ID（由 FileService 預先上傳） */
  async verifyIdentity(userId, data) {
    if (this.useMock) {
      return this._post(`/${userId}/verify`, data, async () => {
        const user = await mockDb.findOne(this.table, u => u.id === userId);
        if (!user) throw new Error('用戶不存在');
        // 只儲存文字欄位與 file ID，不存 base64 圖片（避免 localStorage quota 超限）
        await mockDb.update(this.table, userId, {
          verified: true,
          verificationStatus: 'pending',
          verificationData: {
            idNumber:    data.idNumber    || data.id_number  || '',
            fullName:    data.fullName    || data.real_name  || '',
            frontFileId: data.frontFileId || null,
            backFileId:  data.backFileId  || null,
          },
        });
        return { message: '身份驗證資料已送出' };
      });
    }
    // 使用 JSON 傳送（file 已由 FileService 預先上傳，只傳 ID）
    const response = await this.http.post('/users/me/verify', {
      real_name:    data.real_name    || data.fullName   || '',
      id_number:    data.id_number    || data.idNumber   || '',
      front_file_id: data.frontFileId || data.front_file_id || null,
      back_file_id:  data.backFileId  || data.back_file_id  || null,
    })
    return response.data
  }

  /* 取得使用者列表（管理員）— GET /admin/users */
  async listUsers() {
    if (this.useMock) {
      return this._get('/', async () => {
        return await mockDb.list(this.table);
      });
    }
    const response = await this.http.get('/admin/users');
    return response.data;
  }

  /* 調整使用者餘額（管理員）— POST /admin/users/{id}/balance */
  async updateUserBalance(userId, amount, description = '') {
    if (this.useMock) {
      return this._post(`/${userId}/balance`, { amount, description }, async () => {
        const user = await mockDb.findOne(this.table, u => u.id === userId);
        if (!user) throw new Error('用戶不存在');
        if (!user.wallet) user.wallet = {};
        // 加法調整（而非覆寫）
        user.wallet.balance = (Number(user.wallet.balance) || 0) + Number(amount);
        await mockDb.update(this.table, userId, { wallet: user.wallet });
        // 同步建立交易紀錄，讓「我的明細表」可查詢
        await mockDb.insert('transactions', {
          user_id:      userId,
          type:         'adjustment',
          amount:       Number(amount),
          status:       'completed',
          description:  description || '管理員儲值',
          reference_id: 'ADJ_' + Date.now() + '_' + userId,
          created_at:   new Date().toISOString(),
        });
        return { message: '餘額更新成功', balance: user.wallet.balance };
      });
    }
    const response = await this.http.post(`/admin/users/${userId}/balance`, { amount, description });
    return response.data;
  }
}

