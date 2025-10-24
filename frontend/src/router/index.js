// frontend/src/router/index.js

import { createRouter, createWebHistory } from 'vue-router';

// Impor Layout
import AdminLayout from '../components/AdminLayout.vue';

// Impor Halaman (Pages)
import DashboardAdmin from '../Admin/Dashboard.vue';
import DashboardSuperAdmin from '../SuperAdmin/DashboardSuper.vue';
import Users from '../SuperAdmin/Users.vue'; // (Asumsi dari langkah sebelumnya)
import Products from '../SuperAdmin/Products.vue';
import AddProducts from '../SuperAdmin/AddProducts.vue';
import ProductCreate from '../SuperAdmin/ProductCreate.vue';
// Buat halaman placeholder untuk testing
const History = { template: '<h1 class="text-2xl">Halaman Riwayat Order</h1>' };
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
        component: Products,
      },
      {
        path: 'products/AddProducts', // Halaman form tambah produk
        component: AddProducts,
      },
      {
        path: 'history',
        component: History,
      },
    ],
  },

  {
    path: '/admin',
    component: AdminLayout,
    props: { role: 'admin' },
    children: [
      {
        path: 'dashboard',
        component: DashboardAdmin, // Komponen Dashboard Admin
      },
      {
        path: 'add-order',
        component: AddOrder, // Komponen POS (Langkah 1)
      },
      {
        path: 'confirm-order',
        component: ConfirmOrder, // Komponen Confirm Order (Langkah 2)
      },
      {
        path: 'history',
        component: AdminHistory, // Komponen History Admin (Langkah 3)
      },
    ],
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;