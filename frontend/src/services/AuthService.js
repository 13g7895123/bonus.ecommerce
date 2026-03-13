import { BaseService } from './BaseService';
import { mockDb } from './mockDb';

export class AuthService extends BaseService {
  constructor() {
    super('/auth', 'users');
    this.table = 'users'; // Mock table name
  }

  /* 登入 */
  async login(credentials) {
    if (this.useMock) {
      // Mock Login
      return this._post('/login', credentials, async (data) => {
        const user = await mockDb.findOne(this.table, u => u.email === data.email);
        
        if (!user || user.password !== data.password) {
          throw new Error('帳號或密碼錯誤');
        }
        
        const token = 'mock-jwt-token-' + user.id;
        localStorage.setItem('token', token);
        localStorage.setItem('user', JSON.stringify(user));

        return { token, user };
      });
    } else {
      // Real Login
      return this._post('/login', credentials);
    }
  }

  /* 註冊 */
  async register(userData) {
    if (this.useMock) {
      return this._post('/register', userData, async (data) => {
        const existing = await mockDb.findOne(this.table, u => u.email === data.email);
        if (existing) {
          throw new Error('此電子郵件已被註冊');
        }
        
        // 建立新用戶 (包含預設錢包與等級)
        const newUser = {
          ...data,
          role: 'user',
          tier: 'blue',
          miles: 0,
          wallet: {
            balance: 0,
            password: null, // 未設定提款密碼
            bank: null, // 未綁定銀行
          },
          verified: false // 身份未驗證
        };
        
        const user = await mockDb.insert(this.table, newUser);
        const token = 'mock-jwt-token-' + user.id;
        localStorage.setItem('token', token);
        localStorage.setItem('user', JSON.stringify(user));
        
        return { token, user };
      });
    } else {
      return this._post('/register', userData);
    }
  }

  /* 忘記密碼 */
  async forgotPassword(phone) {
    if (this.useMock) {
       return this._post('/forgot-password', { phone }, async (data) => {
         // 在 mock 模式下我們假設找回成功
         await new Promise(resolve => setTimeout(resolve, 500));
         return { message: '密碼重置連結已發送' };
       });
    } else {
      return this._post('/forgot-password', { phone });
    }
  }

  /* 登出 */
  logout() {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    window.location.href = '/login';
  }
  
  /* 取得當前用戶 */
  getCurrentUser() {
    const u = localStorage.getItem('user');
    return u ? JSON.parse(u) : null;
  }
}
