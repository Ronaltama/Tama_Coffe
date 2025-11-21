import { createRouter, createWebHistory } from "vue-router";
import axios from "axios";
import Swal from "sweetalert2"; // 💡 Import SweetAlert2

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
import AddAdmin from "../SuperAdmin/AddAdmin.vue";
import EditUser from "../SuperAdmin/EditUser.vue";
import Tables from "../SuperAdmin/Tables.vue";
import AddTable from "../SuperAdmin/AddTable.vue";
import EditTable from "../SuperAdmin/EditTable.vue";

// --- User Pages ---
import UserMenu from "../views/UserMenu.vue";
import ProductDetail from "../views/ProductDetail.vue";
import Cart from "../views/Cart.vue";
import Payment from "../views/Payment.vue";
import PaymentConfirmation from "../views/PaymentConfirmation.vue";
import PaymentSuccess from "../views/PaymentSuccess.vue";
import Reservation from "../views/Reservation.vue"; // 🆕 Import Reservation

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
    redirect: "/order/menu", // default ke halaman user
  },

  // --- LOGIN ---
  {
    path: "/login",
    name: "Login",
    component: () => import("../Auth/Login.vue"),
  },

    // --- USER untuk ORDER---
  {
    path: "/order/menu",
    name: "UserMenu",
    component: UserMenu,
  },
  {
    path: "/order/product/:id",
    name: "ProductDetail",
    component: ProductDetail,
    props: true,
  },
  {
    path: "/order/cart",
    name: "UserCart",
    component: Cart,
  },
  {
    path: "/order/payment",
    name: "UserPayment",
    component: Payment,
  },
  {
    path: "/order/payment/confirm",
    name: "PaymentConfirmation",
    component: PaymentConfirmation,
  },
  {
    path: "/order/payment/success",
    name: "PaymentSuccess",
    component: PaymentSuccess,
  },
  
  // 🆕 Route Reservation
  {
    path: "/user/reservation",
    name: "Reservation",
    component: Reservation,
  },

  // --- SUPERADMIN ---
  {
    path: "/superadmin",
    component: AdminLayout,
    props: { role: "superadmin" },
    children: [
      { path: "dashboard", component: DashboardSuperAdmin },
      { path: "users", component: Users },
      { path: "users/add", component: AddAdmin },
      {
        path: "users/edit/:id",
        name: "EditUser",
        component: EditUser,
        props: true, // supaya :id masuk sebagai props
      },
      { path: "products", component: Products },
      { path: "products/AddProducts", component: AddProducts },
      {
        path: "products/edit/:id",
        name: "EditProduct",
        component: () => import("../SuperAdmin/EditProduct.vue"),
        props: true,
      },
      { path: "tables", component: Tables },
      { path: "tables/add", component: AddTable },
      {
        path: "/superadmin/tables/edit/:id",
        name: "EditTable",
        component: EditTable,
        props: true, // penting supaya param id dikirim ke component
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

  // --- ORDER hanya simulasi ku saja(User Scan) ---
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

  // Jika belum login dan bukan halaman login/user → arahkan ke login
  if (!token && !to.path.startsWith("/order") && to.path !== "/login") {
    return next("/login");
  }

  // Jika sudah login, set Authorization header
  if (token) {
    axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
  }

  // Cegah user yang sudah login masuk ke halaman login lagi
  if (token && to.path === "/login") {
    if (user?.role === "superadmin") return next("/superadmin/dashboard");
    if (user?.role === "admin") return next("/admin/dashboard");
  }

  // Cek role user berdasarkan path
  if (to.path.startsWith("/superadmin") && user?.role !== "superadmin") {
    // ⚠️ Ganti alert() dengan SweetAlert2
    await Swal.fire({
      icon: "warning",
      title: "Akses Ditolak!",
      text: "Anda tidak memiliki hak akses Superadmin.",
      confirmButtonText: "Kembali",
    });
    return next("/login");
  }

  if (to.path.startsWith("/admin") && user?.role !== "admin") {
    await Swal.fire({
      icon: "warning",
      title: "Akses Ditolak!",
      text: "Anda tidak memiliki hak akses Admin.",
      confirmButtonText: "Kembali",
    });
    return next("/login");
  }

  // 5️⃣ Semua OK → lanjut
  next();
});

export default router;