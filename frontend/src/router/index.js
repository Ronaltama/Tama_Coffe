import { createRouter, createWebHistory } from 'vue-router'
import AdminLayout from '../components/AdminLayout.vue'
import DashboardSuper from '../SuperAdmin/DashboardSuper.vue'

const routes = [
  {
    path: '/super-admin',
    component: AdminLayout,
    // pass role prop ke layout (contoh: superadmin)
    props: { role: 'superadmin' },
    children: [
      { path: 'dashboard', name: 'SuperDashboard', component: DashboardSuper },
      // { path: 'users', component: UsersComponent }, // tambahkan sesuai kebutuhan
    ]
  },
  // tambahkan route untuk admin jika perlu
  { path: '/', redirect: '/super-admin/dashboard' }
]

export default createRouter({
  history: createWebHistory(),
  routes
})