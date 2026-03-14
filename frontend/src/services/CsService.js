// src/services/CsService.js
import { BaseService } from './BaseService';

export class CsService extends BaseService {
  constructor() {
    super('/cs', 'messages');
  }

  /* 取得客服訊息 — GET /cs/messages */
  async getMessages({ page = 1, limit = 50 } = {}) {
    if (this.useMock) {
      return this._get('/messages', async () => ({ ticket_id: null, items: [], total: 0 }));
    }
    return this._get('/messages', { page, limit });
  }

  /* 發送客服訊息 — POST /cs/messages */
  async sendMessage(content, image = null) {
    if (this.useMock) {
      return this._post('/messages', { content }, async () => ({ ticket_id: 'mock_ticket_1' }));
    }
    if (image) {
      const formData = new FormData();
      formData.append('content', content);
      formData.append('image', image);
      const response = await this.http.post('/cs/messages', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      return response.data;
    }
    return this._post('/messages', { content });
  }
}
