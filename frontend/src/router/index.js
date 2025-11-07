// frontend/src/router/index.js

import { createRouter, createWebHistory } from 'vue-router';

// Impor Layout
import AdminLayout from '../components/AdminLayout.vue';

// Impor Halaman (Pages)
import DashboardAdmin from '../Admin/Dashboard.vue';
import AddOrder from "../Admin/AddOrder.vue";

//impor superadmin pages
import DashboardSuperAdmin from '../SuperAdmin/DashboardSuper.vue';
import Users from '../SuperAdmin/Users.vue'; // (Asumsi dari langkah sebelumnya)
import Products from '../SuperAdmin/Products.vue';
import AddProducts from '../SuperAdmin/AddProducts.vue';

// --- TAMBAHAN ---
// Impor halaman User Menu
// (Sesuaikan path ini jika Anda menyimpannya di tempat lain)
import UserMenu from '../views/UserMenu.vue'; 
// --- AKHIR TAMBAHAN ---


// Buat halaman placeholder untuk testing
const History = { template: '<h1 class="text-2xl">Halaman Riwayat Order</h1>' };
const ConfirmOrder = { template: '<h1 class="text-2xl">Halaman Konfirmasi Order</h1>' };

const routes = [
  {
    path: "/",
    redirect: "/superadmin/dashboard",
  },

  {
    path: "/user/menu",
    name: "UserMenu",
    component: UserMenu,
  },

  // Rute untuk SuperAdmin
  {
    path: "/superadmin",
    component: AdminLayout,
    props: { role: "superadmin" },
    children: [
      {
        path: "dashboard",
        component: DashboardSuperAdmin,
      },
      {
        path: "users",
        component: Users,
      },
      {
        path: "products",
        component: Products,
      },
      {
        path: "products/AddProducts", // Halaman form tambah produk
        component: AddProducts,
      },
      {
        path: "history",
        component: History,
      },
    ],
  },

  // Rute untuk Admin
  {
    path: "/admin",
    component: AdminLayout,
    props: { role: "admin" },
    children: [
      {
        path: "dashboard",
        component: DashboardAdmin,
      },
      {
        path: "history",
        component: History,
      },
      {
        path: "AddOrder",
        component: AddOrder,
      },
      {
        path: "ConfirmOrder",
        component: ConfirmOrder,
      },
    ],
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;