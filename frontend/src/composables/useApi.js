// src/composables/useApi.js
import { AuthService } from '../services/AuthService';
import { UserService } from '../services/UserService';
import { WalletService } from '../services/WalletService';

// 單例模式：應用程式啟動時初始化
const authService = new AuthService();
const userService = new UserService();
const walletService = new WalletService();

export function useApi() {
  return {
    auth: authService,
    user: userService,
    wallet: walletService,
  };
}
