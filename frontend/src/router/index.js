// frontend/src/router/index.js

import { createRouter, createWebHistory } from 'vue-router';

// Impor Layout
import AdminLayout from '../components/AdminLayout.vue';

// Impor Halaman (Pages)
import DashboardAdmin from '../Admin/Dashboard.vue';
import DashboardSuperAdmin from '../SuperAdmin/DashboardSuper.vue';

// --- PERUBAHAN DI SINI ---
// 1. HAPUS placeholder 'Users' yang lama
// const Users = { template: '<h1 class="text-2xl">Halaman Manajemen User</h1>' };

// 2. IMPOR komponen Users.vue yang baru
import Users from '../SuperAdmin/Users.vue';
// --- AKHIR PERUBAHAN ---


// Buat halaman placeholder untuk testing
const Products = { template: '<h1 class="text-2xl">Halaman Manajemen Produk</h1>' };
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
        component: Users, // <-- Ini sekarang sudah benar
      },
      {
        path: 'products',
        component: Products, 
      },
      {
        path: 'history',
        component: History, 
      },
    ],
  },

  // Rute untuk Admin Biasa
  {
    path: '/admin',
    component: AdminLayout,
    props: { role: 'admin' },
    children: [
      {
        path: 'dashboard',
        component: DashboardAdmin,
      },
      {
        path: 'add-order',
        component: AddOrder, 
      },
      {
        path: 'confirm-order',
        component: ConfirmOrder,
      },
      {
        path: 'history',
        component: History,
      },
    ],
  },
  
  // { path: '/login', component: HalamanLogin },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;