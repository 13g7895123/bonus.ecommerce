// src/services/UserService.js
import { BaseService } from './BaseService';
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
    return this._put('/me', payload);
  }

  /* 提交身份驗證 — POST /users/me/verify (multipart) */
  async verifyIdentity(userId, data) {
    if (this.useMock) {
      return this._post(`/${userId}/verify`, data, async () => {
        const user = await mockDb.findOne(this.table, u => u.id === userId);
        if (!user) throw new Error('用戶不存在');
        await mockDb.update(this.table, userId, { verified: true, verificationData: data });
        return { message: '身份驗證資料已送出' };
      });
    }
    // 使用 FormData 上傳
    const formData = new FormData();
    if (data.real_name || data.fullName) formData.append('real_name', data.real_name || data.fullName);
    if (data.id_front || data.frontImage) formData.append('id_front', data.id_front || data.frontImage);
    if (data.id_back || data.backImage) formData.append('id_back', data.id_back || data.backImage);
    if (data.selfie) formData.append('selfie', data.selfie);
    const response = await this.http.post('/users/me/verify', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return response.data;
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
      return this._post(`/${userId}/balance`, { balance: amount }, async () => {
        const user = await mockDb.findOne(this.table, u => u.id === userId);
        if (!user) throw new Error('用戶不存在');
        if (!user.wallet) user.wallet = {};
        user.wallet.balance = Number(amount);
        await mockDb.update(this.table, userId, { wallet: user.wallet });
        return { message: '餘額更新成功', balance: user.wallet.balance };
      });
    }
    const response = await this.http.post(`/admin/users/${userId}/balance`, { amount, description });
    return response.data;
  }
}

