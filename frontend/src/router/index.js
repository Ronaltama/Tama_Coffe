import { createRouter, createWebHistory } from 'vue-router'
import DashboardSuper from '../SuperAdmin/DashboardSuper.vue'

const routes = [
  {
    path: '/',
    redirect: '/super-admin/dashboard'
  },
  {
    path: '/super-admin/dashboard',
    name: 'SuperAdminDashboard',
    component: DashboardSuper
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router