// src/services/WalletService.js
import { BaseService } from './BaseService';
import { mockDb } from './mockDb';

export class WalletService extends BaseService {
  constructor() {
    super('/wallet', 'users');
    this.table = 'users';
  }

  /* 取得錢包資訊 — GET /wallet/info */
  async getWalletInfo() {
    if (this.useMock) {
      return this._get('/info', async () => {
        const token = localStorage.getItem('token');
        const userId = token ? parseInt(token.replace('mock-jwt-token-', '')) : null;
        const user = userId ? await mockDb.findOne(this.table, u => u.id === userId) : null;
        if (!user) throw new Error('用戶不存在');
        return user.wallet || {};
      });
    }
    return this._get('/info');
  }

  /* 提款申請 — POST /wallet/withdraw */
  async applyWithdrawal(userId, amount, withdrawPassword) {
    if (this.useMock) {
      return this._post('/withdraw', { userId, amount, withdrawPassword }, async () => {
        const user = await mockDb.findOne(this.table, u => u.id === userId);
        if (!user) throw new Error('用戶不存在');
        if (!user.wallet.password) throw new Error('請先設定提款密碼');
        if (user.wallet.password !== withdrawPassword) throw new Error('提款密碼錯誤');
        if (amount <= 0) throw new Error('提款金額必須大於0');
        if (user.wallet.balance < amount) throw new Error('餘額不足');
        user.wallet.balance -= amount;
        await mockDb.update(this.table, userId, { wallet: user.wallet });
        // 建立交易紀錄
        await mockDb.insert('transactions', {
          user_id:      userId,
          type:         'withdrawal',
          amount:       amount,
          status:       'completed',
          description:  '提款至銀行帳戶',
          reference_id: 'WD_' + Date.now() + '_' + userId,
          created_at:   new Date().toISOString(),
        });
        return { message: '提款成功', balance: user.wallet.balance };
      });
    }
    // Real API: userId 由 JWT 決定，body 使用 snake_case
    return this._post('/withdraw', {
      amount,
      withdrawal_password: withdrawPassword,
    });
  }

  /* 設定提款密碼 — POST /wallet/password */
  async setWithdrawPassword(userId, password, oldPassword) {
    if (this.useMock) {
      return this._post('/password', { userId, password }, async () => {
        const user = await mockDb.findOne(this.table, u => u.id === userId);
        if (!user) throw new Error('用戶不存在');
        if (!user.wallet) user.wallet = {};
        user.wallet.password = password;
        await mockDb.update(this.table, userId, { wallet: user.wallet });
        return { message: '提款密碼設定成功' };
      });
    }
    const body = { password };
    if (oldPassword) body.old_password = oldPassword;
    return this._post('/password', body);
  }

  /* 取得交易紀錄 — GET /wallet/transactions */
  async getTransactions(type = null, page = 1, limit = 50) {
    if (this.useMock) {
      return this._get('/transactions', async () => {
        const token = localStorage.getItem('token');
        const userId = token ? parseInt(token.replace('mock-jwt-token-', '')) : null;
        const all = (localStorage.getItem('skywards_db_transactions')
          ? JSON.parse(localStorage.getItem('skywards_db_transactions'))
          : []).filter(t => String(t.user_id) === String(userId));
        const filtered = type ? all.filter(t => t.type === type) : all;
        filtered.sort((a, b) => new Date(b.created_at || b.createdAt) - new Date(a.created_at || a.createdAt));
        const start = (page - 1) * limit;
        return { items: filtered.slice(start, start + limit), total: filtered.length };
      });
    }
    const params = { page, limit };
    if (type) params.type = type;
    return this._get('/transactions', params);
  }

  /* 綁定銀行帳戶 — POST /wallet/bank */
  async setupBank(userId, bankData) {
    if (this.useMock) {
      return this._post('/bank', { userId, ...bankData }, async () => {
        const user = await mockDb.findOne(this.table, u => u.id === userId);
        if (!user) throw new Error('用戶不存在');
        if (!user.wallet) user.wallet = {};
        user.wallet.bank = bankData;
        await mockDb.update(this.table, userId, { wallet: user.wallet });
        return { message: '銀行帳戶綁定成功' };
      });
    }
    // Real API: snake_case 欄位
    return this._post('/bank', {
      bank_name:             bankData.bankName             || bankData.bank_name,
      bank_branch:           bankData.branchName           || bankData.bank_branch || null,
      bank_account:          bankData.accountNo            || bankData.bank_account,
      bank_account_name:     bankData.accountName          || bankData.bank_account_name,
      bank_passbook_file_id: bankData.passbookFileId       || bankData.bank_passbook_file_id || null,
    });
  }

  /* 交易紀錄 — GET /wallet/transactions */
  async getTransactions({ type, page = 1, limit = 20 } = {}) {
    if (this.useMock) {
      return this._get('/transactions', async () => ({ items: [], total: 0 }));
    }
    return this._get('/transactions', { type, page, limit });
  }
}

