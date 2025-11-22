import { createRouter, createWebHistory } from "vue-router";
import axios from "axios";
import Swal from "sweetalert2";

// --- Layout ---
import AdminLayout from "../components/AdminLayout.vue";

// simulasi scanorder
import SimulasiScanOrder from "../views/SimulasiScanOrder/SimulasiScanOrder.vue";

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
import Reservation from "../views/Reservation.vue";

const HistoryPlaceholder = {
  template: '<h1 class="text-2xl">Halaman Riwayat Order</h1>',
};
const ConfirmOrderPlaceholder = {
  template: '<h1 class="text-2xl">Halaman Konfirmasi Order</h1>',
};

const routes = [
  {
    path: "/",
    redirect: "/simulasi", // Redirect ke simulasi scan
  },

  // --- SIMULASI SCAN ORDER ---
  {
    path: "/simulasi",
    name: "SimulasiScanOrder",
    component: SimulasiScanOrder,
  },

  // --- SCAN QR REDIRECT (dari QR Code yang di-scan) ---
  {
    path: "/order/:tableId",
    redirect: to => {
      // Redirect ke /order/menu dengan tableId sebagai query parameter
      return {
        name: "UserMenu",
        query: { table: to.params.tableId }
      };
    }
  },

  // --- LOGIN ---
  {
    path: "/login",
    name: "Login",
    component: () => import("../Auth/Login.vue"),
  },

  // --- USER untuk ORDER ---
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

  // Route Reservation
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
        props: true,
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
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach(async (to, from, next) => {
  const token = localStorage.getItem("token");
  const user = JSON.parse(localStorage.getItem("user"));

  // Allow access to order pages and simulasi without login
  if (to.path.startsWith("/order") || to.path === "/simulasi" || to.path === "/login") {
    if (token) {
      axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
    }
    return next();
  }

  // Jika belum login dan bukan halaman public → arahkan ke login
  if (!token) {
    return next("/login");
  }

  // Jika sudah login, set Authorization header
  axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;

  // Cegah user yang sudah login masuk ke halaman login lagi
  if (to.path === "/login") {
    if (user?.role === "superadmin") return next("/superadmin/dashboard");
    if (user?.role === "admin") return next("/admin/dashboard");
  }

  // Cek role user berdasarkan path
  if (to.path.startsWith("/superadmin") && user?.role !== "superadmin") {
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

  next();
});

export default router;