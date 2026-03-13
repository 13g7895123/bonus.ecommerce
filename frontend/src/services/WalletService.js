// src/services/WalletService.js
import { BaseService } from './BaseService';
import { mockDb } from './mockDb';

export class WalletService extends BaseService {
  constructor() {
    super('/wallet', 'users');
    this.table = 'users'; // 錢包資料放在 Users 表的 wallet 欄位
  }

  /* 提款申請 */
  async applyWithdrawal(userId, amount, withdrawPassword) {
    if (this.useMock) {
      return this._post('/withdraw', { userId, amount, withdrawPassword }, async () => {
        const user = await mockDb.findOne(this.table, u => u.id === userId);
        
        if (!user) throw new Error('用戶不存在');
        if (!user.wallet.password) throw new Error('請先設定提款密碼');
        if (user.wallet.password !== withdrawPassword) throw new Error('提款密碼錯誤');
        if (amount <= 0) throw new Error('提款金額必須大於0');
        if (user.wallet.balance < amount) throw new Error('餘額不足');
        
        // 扣款
        user.wallet.balance -= amount;
        await mockDb.update(this.table, userId, { wallet: user.wallet });
        
        return { message: '提款申請成功', balance: user.wallet.balance };
      });
    } else {
      return this._post('/withdraw', { userId, amount, withdrawPassword });
    }
  }

  /* 設定提款密碼 */
  async setWithdrawPassword(userId, password) {
    if (this.useMock) {
      return this._post('/password', { userId, password }, async () => {
        const user = await mockDb.findOne(this.table, u => u.id === userId);
        if (!user) throw new Error('用戶不存在');
        
        if (!user.wallet) user.wallet = {};
        user.wallet.password = password;
        
        await mockDb.update(this.table, userId, { wallet: user.wallet });
        return { message: '提款密碼設定成功' };
      });
    } else {
      return this._post('/password', { userId, password });
    }
  }

  /* 綁定銀行帳戶 */
  async setupBank(userId, bankData) {
    if (this.useMock) {
      return this._post('/bank', { userId, ...bankData }, async () => {
        const user = await mockDb.findOne(this.table, u => u.id === userId);
        if (!user) throw new Error('用戶不存在');
        
        if (!user.wallet) user.wallet = {};
        user.wallet.bank = bankData; // bankData: { name, branch, account, ownerName }
        
        await mockDb.update(this.table, userId, { wallet: user.wallet });
        return { message: '銀行帳戶綁定成功' };
      });
    } else {
      return this._post('/bank', { userId, ...bankData });
    }
  }
}
