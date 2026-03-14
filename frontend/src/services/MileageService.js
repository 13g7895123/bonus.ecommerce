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
}
