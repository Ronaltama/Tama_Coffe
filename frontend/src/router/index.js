// frontend/src/router/index.js

import { createRouter, createWebHistory } from "vue-router";

// Impor Layout
import AdminLayout from "../components/AdminLayout.vue";

// Impor Halaman (Pages)
import DashboardAdmin from "../Admin/Dashboard.vue";
import AddOrder from "../Admin/AddOrder.vue";
import ConfirmOrder from "../Admin/ConfirmOrder.vue";

//impor superadmin pages
import DashboardSuperAdmin from "../SuperAdmin/DashboardSuper.vue";
import Users from "../SuperAdmin/Users.vue"; // (Asumsi dari langkah sebelumnya)
import Products from "../SuperAdmin/Products.vue";
import AddProducts from "../SuperAdmin/AddProducts.vue";
import History from "../SuperAdmin/History.vue";

// import halaman user
import UserMenu from "../views/UserMenu.vue";
import ProductDetail from "../views/ProductDetail.vue";

// Buat halaman placeholder untuk testing (dari versi pertama)
const HistoryPlaceholder = {
  template: '<h1 class="text-2xl">Halaman Riwayat Order</h1>',
};
const ConfirmOrderPlaceholder = {
  template: '<h1 class="text-2xl">Halaman Konfirmasi Order</h1>',
};

const routes = [
  {
    path: "/",
    redirect: "/user/menu", // dari versi pertama (user)
  },

  // --- RUTE USER ---
  {
    path: "/user/menu",
    name: "UserMenu",
    component: UserMenu,
  },
  {
    path: "/user/product/:id",
    name: "ProductDetail",
    component: ProductDetail,
    props: true,
  },
  // --- AKHIR RUTE USER ---

  // --- RUTE SUPERADMIN ---
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
        path: "products/AddProducts",
        component: AddProducts,
      },
      // Dua versi history (real + placeholder)
      {
        path: "history",
        component: History,
      },
      {
        path: "history-test",
        component: HistoryPlaceholder,
      },
    ],
  },

  // --- RUTE ADMIN ---
  {
    path: "/admin",
    component: AdminLayout,
    props: { role: "admin" },
    children: [
      {
        path: "dashboard",
        component: DashboardAdmin,
      },
      // Dua versi history (real + placeholder)
      {
        path: "history",
        component: History,
      },
      {
        path: "history-test",
        component: HistoryPlaceholder,
      },
      {
        path: "add-order",
        component: AddOrder,
      },
      {
        path: "confirm-order",
        component: ConfirmOrder,
      },
      {
        path: "confirm-order-test",
        component: ConfirmOrderPlaceholder,
      },
    ],
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
