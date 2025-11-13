// frontend/src/router/index.js

import { createRouter, createWebHistory } from "vue-router";
import axios from "axios";

// --- Layout ---
import AdminLayout from "../components/AdminLayout.vue";

// --- Admin Pages ---
import DashboardAdmin from "../Admin/Dashboard.vue";
import AddOrder from "../Admin/AddOrder.vue";
import ConfirmOrder from "../Admin/ConfirmOrder.vue";

// --- SuperAdmin Pages ---
import DashboardSuperAdmin from "../SuperAdmin/DashboardSuper.vue";
import Users from "../SuperAdmin/Users.vue";
import Products from "../SuperAdmin/Products.vue";
import AddProducts from "../SuperAdmin/AddProducts.vue";
import History from "../SuperAdmin/History.vue";

// --- User Pages ---
import UserMenu from "../views/UserMenu.vue";
import ProductDetail from "../views/ProductDetail.vue";
import Cart from "../views/Cart.vue";
import Payment from "../views/Payment.vue";

// --- Placeholder Pages (opsional testing) ---
const HistoryPlaceholder = {
  template: '<h1 class="text-2xl">Halaman Riwayat Order</h1>',
};
const ConfirmOrderPlaceholder = {
  template: '<h1 class="text-2xl">Halaman Konfirmasi Order</h1>',
};

const routes = [
  {
    path: "/",
    redirect: "/user/menu", // default ke halaman user
  },

  // --- LOGIN ---
  {
    path: "/login",
    name: "Login",
    component: () => import("../Auth/Login.vue"),
  },

  // --- USER ---
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
  {
    path: "/user/cart",
    name: "UserCart",
    component: Cart,
  },
  {
    path: "/user/payment",
    name: "UserPayment",
    component: Payment,
  },

  // --- SUPERADMIN ---
  {
    path: "/superadmin",
    component: AdminLayout,
    props: { role: "superadmin" },
    children: [
      { path: "dashboard", component: DashboardSuperAdmin },
      { path: "users", component: Users },
      { path: "products", component: Products },
      { path: "products/AddProducts", component: AddProducts },
      {
        path: "products/edit/:id",
        name: "EditProduct",
        component: () => import("../SuperAdmin/EditProduct.vue"),
        props: true,
      },
      { path: "history", component: History },
      { path: "history-test", component: HistoryPlaceholder },
    ],
  },

  // --- ADMIN ---
  {
    path: "/admin",
    component: AdminLayout,
    props: { role: "admin" },
    children: [
      { path: "dashboard", component: DashboardAdmin },
      { path: "history", component: History },
      { path: "history-test", component: HistoryPlaceholder },
      { path: "add-order", component: AddOrder },
      { path: "confirm-order", component: ConfirmOrder },
      { path: "confirm-order-test", component: ConfirmOrderPlaceholder },
    ],
  },

  // --- ORDER (User Scan) ---
  {
    path: "/order/:id",
    name: "orderUser",
    component: () => import("../Order/Order.vue"),
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// ==============================
// 🚧 ROUTE GUARD UNTUK LOGIN + ROLE
// ==============================
router.beforeEach(async (to, from, next) => {
  const token = localStorage.getItem("token");
  const user = JSON.parse(localStorage.getItem("user"));

  // 1️⃣ Jika belum login dan bukan halaman login → arahkan ke login
  if (!token && !to.path.startsWith("/user") && to.path !== "/login") {
    return next("/login");
  }

  // 2️⃣ Jika sudah login, set Authorization header
  if (token) {
    axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
  }

  // 3️⃣ Cegah user yang sudah login masuk ke halaman login lagi
  if (token && to.path === "/login") {
    if (user?.role === "superadmin") return next("/superadmin/dashboard");
    if (user?.role === "admin") return next("/admin/dashboard");
  }

  // 4️⃣ Cek role user berdasarkan path
  if (to.path.startsWith("/superadmin") && user?.role !== "superadmin") {
    alert("Akses ditolak: Anda bukan superadmin!");
    return next("/login");
  }

  if (to.path.startsWith("/admin") && user?.role !== "admin") {
    alert("Akses ditolak: Anda bukan admin!");
    return next("/login");
  }

  // 5️⃣ Semua OK → lanjut
  next();
});

export default router;
