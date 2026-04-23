import { createRouter, createWebHistory } from 'vue-router'
import { useToast } from '../composables/useToast'
import i18n from '../i18n'
import Home from '../views/Home.vue'
import Login from '../views/Login.vue'
import Register from '../views/Register.vue'
import ForgotPassword from '../views/ForgotPassword.vue'
import Settings from '../views/Settings.vue'
import SkywardsDashboard from '../views/SkywardsDashboard.vue'
import Profile from '../views/Profile.vue'
import ProfileDetails from '../views/ProfileDetails.vue'
import ChangePassword from '../views/ChangePassword.vue'
import IdentityVerification from '../views/IdentityVerification.vue'
import IdentityVerificationComplete from '../views/IdentityVerificationComplete.vue'
import CustomerService from '../views/CustomerService.vue'
import Transactions from '../views/Transactions.vue'
import TopUpRecords from '../views/TopUpRecords.vue'
import WithdrawalSetPassword from '../views/WithdrawalSetPassword.vue'
import WithdrawalSetup from '../views/WithdrawalSetup.vue'
import WithdrawalApply from '../views/WithdrawalApply.vue'
import MyMail from '../views/MyMail.vue'
import MileageRewards from '../views/MileageRewards.vue'
import MileageRewardDetail from '../views/MileageRewardDetail.vue'
import MileageRecords from '../views/MileageRecords.vue'
import MileageRewardOrders from '../views/MileageRewardOrders.vue'
import MileageRedemption from '../views/MileageRedemption.vue'
import MileageRewardConfirm from '../views/MileageRewardConfirm.vue'
import AnnouncementDetail from '../views/AnnouncementDetail.vue'
import Admin from '../views/Admin.vue'
import AdminUsers from '../views/admin/AdminUsers.vue'
import AdminKyc from '../views/admin/AdminKyc.vue'
import AdminMileageItems from '../views/admin/AdminMileageItems.vue'
import AdminMileageRewards from '../views/admin/AdminMileageRewards.vue'
import AdminRewardOrders from '../views/admin/AdminRewardOrders.vue'
import AdminMileageCodes from '../views/admin/AdminMileageCodes.vue'
import AdminContent from '../views/admin/AdminContent.vue'
import AdminCustomerService from '../views/admin/AdminCustomerService.vue'
import AdminPhoneVerifications from '../views/admin/AdminPhoneVerifications.vue'
import AdminSmsLogs from '../views/admin/AdminSmsLogs.vue'
import AdminSmsProvider from '../views/admin/AdminSmsProvider.vue'
import AdminBanks from '../views/admin/AdminBanks.vue'
import AdminMails from '../views/admin/AdminMails.vue'
import AdminAnnouncements from '../views/admin/AdminAnnouncements.vue'
import Sadmin from '../views/Sadmin.vue'
import SadminOverview from '../views/sadmin/SadminOverview.vue'
import SadminApiLogs from '../views/sadmin/SadminApiLogs.vue'
import SadminThirdPartyLogs from '../views/sadmin/SadminThirdPartyLogs.vue'
import SadminSmsProvider from '../views/sadmin/SadminSmsProvider.vue'
import SadminSmsLogs from '../views/sadmin/SadminSmsLogs.vue'
import SadminSmsSettings from '../views/sadmin/SadminSmsSettings.vue'
import CountryLanguageSettings from '../views/CountryLanguageSettings.vue'

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home
  },
  {
    path: '/mileage-redemption',
    name: 'MileageRedemption',
    component: MileageRedemption,
    meta: { requiresAuth: true }
  },
  {
    path: '/admin',
    component: Admin,
    children: [
      { path: '', redirect: '/admin/users' },
      { path: 'users',           name: 'AdminUsers',          component: AdminUsers },
      { path: 'kyc',             name: 'AdminKyc',            component: AdminKyc },
      { path: 'mileage-items',   name: 'AdminMileageItems',   component: AdminMileageItems },
      { path: 'mileage-rewards', name: 'AdminMileageRewards', component: AdminMileageRewards },
      { path: 'reward-orders',   name: 'AdminRewardOrders',   component: AdminRewardOrders },
      { path: 'mileage-codes',   name: 'AdminMileageCodes',   component: AdminMileageCodes },
      { path: 'content',              name: 'AdminContent',             component: AdminContent },
      { path: 'customer-service',     name: 'AdminCustomerService',    component: AdminCustomerService },
      { path: 'phone-verifications',  name: 'AdminPhoneVerifications',  component: AdminPhoneVerifications },
      { path: 'sms-logs',             name: 'AdminSmsLogs',             component: AdminSmsLogs },
      { path: 'banks',                name: 'AdminBanks',               component: AdminBanks },
      { path: 'mails',                name: 'AdminMails',               component: AdminMails },
      { path: 'announcements',         name: 'AdminAnnouncements',        component: AdminAnnouncements },
    ],
  },
  {
    path: '/sadmin',
    component: Sadmin,
    children: [
      { path: '', redirect: '/sadmin/overview' },
      { path: 'overview',         name: 'SadminOverview',         component: SadminOverview },
      { path: 'api-logs',         name: 'SadminApiLogs',          component: SadminApiLogs },
      { path: 'third-party-logs', name: 'SadminThirdPartyLogs',   component: SadminThirdPartyLogs },
      { path: 'sms-logs',         name: 'SadminSmsLogs',          component: SadminSmsLogs },
      { path: 'sms-settings',     name: 'SadminSmsSettings',      component: SadminSmsSettings },
      { path: 'sms-provider',     name: 'SadminSmsProvider',      component: SadminSmsProvider },
    ],
  },
  {
    path: '/settings/language',
    name: 'CountryLanguageSettings',
    component: CountryLanguageSettings
  },
  {
    path: '/announcement/:id',
    name: 'AnnouncementDetail',
    component: AnnouncementDetail
  },
  {
    path: '/login',
    name: 'Login',
    component: Login
  },
  {
    path: '/register',
    name: 'Register',
    component: Register
  },
  {
    path: '/forgot-password',
    name: 'ForgotPassword',
    component: ForgotPassword
  },
  {
    path: '/settings',
    name: 'Settings',
    component: Settings,
    meta: { requiresAuth: true }
  },
  {
    path: '/skywards',
    name: 'Skywards',
    component: SkywardsDashboard,
    meta: { requiresAuth: true }
  },
  {
    path: '/profile',
    name: 'Profile',
    component: Profile,
    meta: { requiresAuth: true }
  },
  {
    path: '/profile/details',
    name: 'ProfileDetails',
    component: ProfileDetails,
    meta: { requiresAuth: true }
  },
  {
    path: '/change-password',
    name: 'ChangePassword',
    component: ChangePassword,
    meta: { requiresAuth: true }
  },
  {
    path: '/identity-verification',
    name: 'IdentityVerification',
    component: IdentityVerification,
    meta: { requiresAuth: true }
  },
  {
    path: '/identity-verification/complete',
    name: 'IdentityVerificationComplete',
    component: IdentityVerificationComplete,
    meta: { requiresAuth: true }
  },
  {
    path: '/customer-service',
    name: 'CustomerService',
    component: CustomerService,
    meta: { requiresAuth: true }
  },
  {
    path: '/transactions',
    name: 'Transactions',
    component: Transactions,
    meta: { requiresAuth: true }
  },
  {
    path: '/transactions/topup',
    name: 'TopUpRecords',
    component: TopUpRecords,
    meta: { requiresAuth: true }
  },
  {
    path: '/transactions/withdrawal',
    name: 'WithdrawalSetPassword',
    component: WithdrawalSetPassword,
    meta: { requiresAuth: true }
  },
  {
    path: '/withdrawal/set-password',
    name: 'WithdrawalSetPasswordDirect',
    component: WithdrawalSetPassword,
    meta: { requiresAuth: true }
  },
  {
    path: '/withdrawal/setup',
    name: 'WithdrawalSetup',
    component: WithdrawalSetup,
    meta: { requiresAuth: true }
  },
  {
    path: '/withdrawal/apply',
    name: 'WithdrawalApply',
    component: WithdrawalApply,
    meta: { requiresAuth: true }
  },
  {
    path: '/my-mail',
    name: 'MyMail',
    component: MyMail,
    meta: { requiresAuth: true }
  },
  {
    path: '/mileage-rewards',
    name: 'MileageRewards',
    component: MileageRewards,
    meta: { requiresAuth: true }
  },
  {
    path: '/mileage-reward-detail',
    name: 'MileageRewardDetail',
    component: MileageRewardDetail,
    meta: { requiresAuth: true }
  },
  {
    path: '/mileage-reward-confirm',
    name: 'MileageRewardConfirm',
    component: MileageRewardConfirm,
    meta: { requiresAuth: true }
  },
  {
    path: '/mileage-records',
    name: 'MileageRecords',
    component: MileageRecords,
    meta: { requiresAuth: true }
  },
  {
    path: '/mileage-reward-orders',
    name: 'MileageRewardOrders',
    component: MileageRewardOrders,
    meta: { requiresAuth: true }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from) => {
  if (to.meta.requiresAuth) {
    const token = localStorage.getItem('token')
    if (!token) {
      const toast = useToast()
      const t = i18n.global.t
      toast.warning(t('auth.loginRequired'))
      return { path: '/' }
    }
  }
})

export default router
