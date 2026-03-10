import { createRouter, createWebHistory } from 'vue-router'
import Home from '../views/Home.vue'
import Login from '../views/Login.vue'
import Register from '../views/Register.vue'
import ForgotPassword from '../views/ForgotPassword.vue'
import Settings from '../views/Settings.vue'
import SkywardsDashboard from '../views/SkywardsDashboard.vue'
import Profile from '../views/Profile.vue'
import ProfileDetails from '../views/ProfileDetails.vue'

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home
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
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
