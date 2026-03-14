import { createRouter, createWebHistory } from 'vue-router'
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
import CustomerService from '../views/CustomerService.vue'
import Transactions from '../views/Transactions.vue'
import TopUpRecords from '../views/TopUpRecords.vue'
import WithdrawalSetPassword from '../views/WithdrawalSetPassword.vue'
import WithdrawalSetup from '../views/WithdrawalSetup.vue'
import WithdrawalApply from '../views/WithdrawalApply.vue'
import MyMail from '../views/MyMail.vue'
import MileageRewards from '../views/MileageRewards.vue'
import AnnouncementDetail from '../views/AnnouncementDetail.vue'
import Admin from '../views/Admin.vue'

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home
  },
  {
    path: '/admin',
    name: 'Admin',
    component: Admin
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
    component: Settings
  },
  {
    path: '/skywards',
    name: 'Skywards',
    component: SkywardsDashboard
  },
  {
    path: '/profile',
    name: 'Profile',
    component: Profile
  },
  {
    path: '/profile/details',
    name: 'ProfileDetails',
    component: ProfileDetails
  },
  {
    path: '/change-password',
    name: 'ChangePassword',
    component: ChangePassword
  },
  {
    path: '/identity-verification',
    name: 'IdentityVerification',
    component: IdentityVerification
  },
  {
    path: '/customer-service',
    name: 'CustomerService',
    component: CustomerService
  },
  {
    path: '/transactions',
    name: 'Transactions',
    component: Transactions
  },
  {
    path: '/transactions/topup',
    name: 'TopUpRecords',
    component: TopUpRecords
  },
  {
    path: '/transactions/withdrawal',
    name: 'WithdrawalSetPassword',
    component: WithdrawalSetPassword
  },
  {
    path: '/withdrawal/set-password',
    name: 'WithdrawalSetPasswordDirect',
    component: WithdrawalSetPassword
  },
  {
    path: '/withdrawal/setup',
    name: 'WithdrawalSetup',
    component: WithdrawalSetup
  },
  {
    path: '/withdrawal/apply',
    name: 'WithdrawalApply',
    component: WithdrawalApply
  },
  {
    path: '/my-mail',
    name: 'MyMail',
    component: MyMail
  },
  {
    path: '/mileage-rewards',
    name: 'MileageRewards',
    component: MileageRewards
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
