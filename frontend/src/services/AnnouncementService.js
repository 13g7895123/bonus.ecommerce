// src/services/AnnouncementService.js
import { BaseService } from './BaseService';

export class AnnouncementService extends BaseService {
  constructor() {
    super('/announcements', 'announcements');
  }

  /* 公告列表 — GET /announcements */
  async getList({ page = 1, limit = 20 } = {}) {
    if (this.useMock) {
      return this._get('', async () => ({ items: [], total: 0 }));
    }
    return this._get('', { page, limit });
  }

  /* 公告詳情 — GET /announcements/{id} */
  async getDetail(id) {
    if (this.useMock) {
      return this._get(`/${id}`, async () => null);
    }
    return this._get(`/${id}`);
  }
}
