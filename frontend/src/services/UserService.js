// src/services/UserService.js
import { BaseService } from './BaseService';
import { mockDb } from './mockDb';

export class UserService extends BaseService {
  constructor() {
    super('/users', 'users');
    this.table = 'users'; // mockTable
  }

  /* 取得用戶資料 */
  async getProfile(userId) {
    if (this.useMock) {
      return this._get(`/${userId}`, async () => {
        const user = await mockDb.findOne(this.table, u => u.id === userId);
        if (!user) throw new Error('用戶不存在');
        return user;
      });
    } else {
      return this._get(`/${userId}`);
    }
  }

  /* 更新個人資料 */
  async updateProfile(userId, updates) {
    if (this.useMock) {
      return this._post(`/${userId}`, updates, async () => {
        const user = await mockDb.findOne(this.table, u => u.id === userId);
        if (!user) throw new Error('用戶不存在');
        
        await mockDb.update(this.table, userId, updates);
        return { message: '更新成功' };
      });
    } else {
      return this._post(`/${userId}`, updates);
    }
  }

  /* 身份驗證 */
  async verifyIdentity(userId, data) {
    if (this.useMock) {
      return this._post(`/${userId}/verify`, data, async () => {
        const user = await mockDb.findOne(this.table, u => u.id === userId);
        if (!user) throw new Error('用戶不存在');
        
        // 模擬上傳與審核
        user.verified = true;
        user.verificationData = data;
        
        await mockDb.update(this.table, userId, { verified: true, verificationData: data });
        return { message: '身份驗證資料已送出' };
      });
    } else {
      return this._post(`/${userId}/verify`, data);
    }
  }
}
