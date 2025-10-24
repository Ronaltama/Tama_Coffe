// frontend/src/router/index.js

import { createRouter, createWebHistory } from 'vue-router';

// Impor Layout
import AdminLayout from '../components/AdminLayout.vue';

// Impor Halaman (Pages)
import DashboardAdmin from '../Admin/Dashboard.vue';
import DashboardSuperAdmin from '../SuperAdmin/DashboardSuper.vue';
import Users from '../SuperAdmin/Users.vue'; // (Asumsi dari langkah sebelumnya)
import Products from '../SuperAdmin/Products.vue';

// Buat halaman placeholder untuk testing
const History = { template: '<h1 class="text-2xl">Halaman Riwayat Order</h1>' };
const AddOrder = { template: '<h1 class="text-2xl">Halaman Tambah Order</h1>' };
const ConfirmOrder = { template: '<h1 class="text-2xl">Halaman Konfirmasi Order</h1>' };

const routes = [
  {
    path: '/',
    redirect: '/superadmin/dashboard',
  },
  
  // Rute untuk SuperAdmin
  {
    path: '/superadmin',
    component: AdminLayout,
    props: { role: 'superadmin' },
    children: [
      {
        path: 'dashboard',
        component: DashboardSuperAdmin,
      },
      {
        path: 'users',
        component: Users, 
      },
      {
        path: 'products',
        component: Products, // <-- Ini sekarang sudah benar
      },
      {
        path: 'history',
        component: History,
      },
    ],
  },

  // Rute untuk Admin Biasa (sisanya)
  // ... (kode router admin Anda) ...
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;