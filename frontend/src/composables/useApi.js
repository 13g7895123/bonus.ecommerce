// src/composables/useApi.js
import { AuthService } from '../services/AuthService';
import { UserService } from '../services/UserService';
import { WalletService } from '../services/WalletService';
import { MileageService } from '../services/MileageService';
import { AnnouncementService } from '../services/AnnouncementService';
import { CsService } from '../services/CsService';

// 單例模式：應用程式啟動時初始化
const authService = new AuthService();
const userService = new UserService();
const walletService = new WalletService();
const mileageService = new MileageService();
const announcementService = new AnnouncementService();
const csService = new CsService();

export function useApi() {
  return {
    auth: authService,
    user: userService,
    wallet: walletService,
    mileage: mileageService,
    announcement: announcementService,
    cs: csService,
  };
}

