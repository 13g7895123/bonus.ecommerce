// src/services/MileageService.js
import { BaseService } from './BaseService';

export class MileageService extends BaseService {
  constructor() {
    super('/mileage', 'mileage');
  }

  /* 里程歷史紀錄 — GET /mileage/history */
  async getHistory({ page = 1, limit = 20 } = {}) {
    if (this.useMock) {
      return this._get('/history', async () => ({ items: [], total: 0 }));
    }
    return this._get('/history', { page, limit });
  }

  /* 消費及生活兌換項目 — GET /mileage/redemption-items */
  async getRedemptionItems() {
    if (this.useMock) {
      return { items: [] };
    }
    return this._get('/redemption-items');
  }

  /* 兌換里程代碼 — POST /mileage/redeem */
  async redeem(code) {
    if (this.useMock) {
      if (code.toUpperCase().startsWith('BONUS')) {
        return { miles_earned: 500, miles_balance: 500, message: '成功兌換 500 哩程數（mock）' };
      }
      throw new Error('無效的里程代碼');
    }
    return this._post('/redeem', { code });
  }

  /* 我的里程回饋訂單 — GET /mileage/reward-orders/my */
  async getMyRewardOrders() {
    if (this.useMock) {
      return { items: [] }
    }
    return this._get('/reward-orders/my')
  }
}
