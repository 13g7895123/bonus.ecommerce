import { BaseService } from './BaseService';
import { mockDb } from './mockDb';

export class AuthService extends BaseService {
  constructor() {
    super('/auth', 'users');
    this.table = 'users';
  }

  /* 登入 */
  async login(credentials) {
    if (this.useMock) {
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
    }
    // Real API: POST /auth/login → { token, user }
    const data = await this._post('/login', credentials);
    if (data?.token) localStorage.setItem('token', data.token);
    if (data?.user) localStorage.setItem('user', JSON.stringify(this._mapUser(data.user)));
    return data;
  }

  /* 註冊 */
  async register(userData) {
    if (this.useMock) {
      return this._post('/register', userData, async (data) => {
        const existing = await mockDb.findOne(this.table, u => u.email === data.email);
        if (existing) {
          throw new Error('此電子郵件已被註冊');
        }
        const newUser = {
          ...data,
          role: 'user',
          tier: 'blue',
          miles: 0,
          wallet: { balance: 0, password: null, bank: null },
          verified: false,
        };
        const user = await mockDb.insert(this.table, newUser);
        const token = 'mock-jwt-token-' + user.id;
        localStorage.setItem('token', token);
        localStorage.setItem('user', JSON.stringify(user));
        return { token, user };
      });
    }
    // Real API: 映射前端欄位 → 後端欄位
    const payload = {
      email: userData.email,
      password: userData.password,
      full_name: [userData.firstName, userData.lastName].filter(Boolean).join(' ') || userData.full_name || '',
      phone: userData.phone || '',
    };
    const data = await this._post('/register', payload);
    // 不在此儲存 token，待手機 OTP 驗證通過後儲存
    return data;
  }

  /* 忘記密碼 */
  async forgotPassword(emailOrPhone) {
    if (this.useMock) {
      return this._post('/forgot-password', { email: emailOrPhone }, async () => {
        await new Promise(resolve => setTimeout(resolve, 500));
        return { message: '密碼重置連結已發送' };
      });
    }
    // Real API 使用 email
    return this._post('/forgot-password', { email: emailOrPhone });
  }

  async sendPhoneOtp(data) {
    if (this.useMock) {
      return this._post('/send-phone-otp', data, async () => {
        await new Promise(resolve => setTimeout(resolve, 500));
        return { message: 'OTP 已發送' };
      });
    }
    return this._post('/send-phone-otp', data);
  }

  async verifyPhoneOtp(data) {
    if (this.useMock) {
      return this._post('/verify-phone-otp', data, async () => {
        await new Promise(resolve => setTimeout(resolve, 500));
        // Mock: 任何6位數均通過
        return { message: '驗證成功' };
      });
    }
    const result = await this._post('/verify-phone-otp', data);
    if (result?.token) localStorage.setItem('token', result.token);
    if (result?.user) localStorage.setItem('user', JSON.stringify(this._mapUser(result.user)));
    return result;
  }

  /* 登出 */
  logout() {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    window.location.href = '/login';
  }

  /* 取得當前用戶（從 localStorage） */
  getCurrentUser() {
    const u = localStorage.getItem('user');
    return u ? JSON.parse(u) : null;
  }

  /* 映射後端欄位到前端慣用欄位 */
  _mapUser(user) {
    const nameParts = (user.full_name || '').trim().split(/\s+/)
    return {
      ...user,
      name: user.full_name || user.name || '',
      firstName: nameParts[0] || '',
      lastName: nameParts.slice(1).join(' ') || '',
      miles: user.wallet?.miles_balance ?? user.miles ?? 0,
    }
  }
}

