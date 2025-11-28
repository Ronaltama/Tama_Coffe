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
import OrderDetail from "../Admin/OrderDetail.vue";

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

const routes = [
  // --- LOGIN ---
  {
    path: "/login",
    name: "Login",
    component: () => import("../Auth/Login.vue"),
  },

  // Simulasi Scan Order
  {
    path: "/",
    redirect: "/simulasi",
  },
  {
    path: "/simulasi",
    name: "SimulasiScanOrder",
    component: SimulasiScanOrder,
  },

  // --- USER ORDER ROUTES dengan table parameter ---
  {
    path: "/order/:tableId/menu",
    name: "UserMenu",
    component: UserMenu,
    props: true,
  },
  {
    path: "/order/:tableId/product/:id",
    name: "ProductDetail",
    component: ProductDetail,
    props: true,
  },
  {
    path: "/order/:tableId/cart",
    name: "UserCart",
    component: Cart,
    props: true,
  },
  {
    path: "/order/:tableId/payment",
    name: "UserPayment",
    component: Payment,
    props: true,
  },
  {
    path: "/order/:tableId/payment/confirm",
    name: "PaymentConfirmation",
    component: PaymentConfirmation,
    props: true,
  },
  {
    path: "/order/:tableId/payment/success",
    name: "PaymentSuccess",
    component: PaymentSuccess,
    props: true,
  },
  {
    path: "/order/:tableId/reservation",
    name: "Reservation",
    component: Reservation,
    props: true,
  },

  // 🔥 FIX: Redirect dari QR code URL (/order/TB001) ke (/order/TB001/menu)
  {
    path: "/order/:tableId",
    redirect: to => {
      const tableId = to.params.tableId;
      localStorage.setItem('currentTableId', tableId);

      const fetchTableInfo = async () => {
        try {
          const API_BASE = "http://localhost:8000/api/guest";
          const response = await axios.get(`${API_BASE}/table-info/${tableId}`);
          if (response.data.success) {
            const tableData = response.data.data;
            localStorage.setItem('currentTableNumber', tableData.table_number);
            localStorage.setItem('tableCapacity', tableData.capacity);
          }
        } catch (err) {
          console.error('Error fetching table info:', err);
        }
      };

      fetchTableInfo();

      return { path: `/order/${tableId}/menu` };
    }
  },

  // Redirect dari path lama ke path baru dengan tableId
  {
    path: "/order/menu",
    redirect: to => {
      const tableId = localStorage.getItem('currentTableId');
      if (tableId) {
        return { path: `/order/${tableId}/menu` };
      }
      return { path: '/simulasi' };
    }
  },
  {
    path: "/order/product/:id",
    redirect: to => {
      const tableId = localStorage.getItem('currentTableId');
      if (tableId) {
        return { path: `/order/${tableId}/product/${to.params.id}` };
      }
      return { path: '/simulasi' };
    }
  },
  {
    path: "/order/cart",
    redirect: to => {
      const tableId = localStorage.getItem('currentTableId');
      if (tableId) {
        return { path: `/order/${tableId}/cart` };
      }
      return { path: '/simulasi' };
    }
  },
  {
    path: "/order/payment",
    redirect: to => {
      const tableId = localStorage.getItem('currentTableId');
      if (tableId) {
        return { path: `/order/${tableId}/payment` };
      }
      return { path: '/simulasi' };
    }
  },

  // RESERVASI
  {
    path: "/user/reservation",
    name: "UserReservation",
    component: Reservation,
  },

  // {
  //   path: "/user/reservation",
  //   redirect: to => {
  //     const tableId = localStorage.getItem('currentTableId');
  //     if (tableId) {
  //       return { path: `/order/${tableId}/reservation` };
  //     }
  //     return { path: '/simulasi' };
  //   }
  // },

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
      {
        path: "order-detail/:id",
        name: "AdminOrderDetail",
        component: OrderDetail,
        props: true,
      },
    ],
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Helper function untuk validasi table
const validateTableAccess = (to) => {
  const tableId = to.params.tableId;
  const storedTableId = localStorage.getItem('currentTableId');

  if (to.path === `/order/${tableId}` && !to.params.id) {
    return true;
  }

  if (!tableId || tableId !== storedTableId) {
    return false;
  }

  return true;
};

router.beforeEach(async (to, from, next) => {
  const token = localStorage.getItem("token");
  const user = JSON.parse(localStorage.getItem("user"));

  // Route untuk guest/order
  if (to.path.startsWith("/order/")) {
    if (!validateTableAccess(to)) {
      await Swal.fire({
        icon: "warning",
        title: "Akses Ditolak!",
        text: "Anda harus scan meja terlebih dahulu untuk mengakses halaman ini.",
        confirmButtonText: "Scan Meja",
      });
      return next("/simulasi");
    }

    if (to.path === `/order/${to.params.tableId}` && !to.params.id) {
      localStorage.setItem('currentTableId', to.params.tableId);
    }

    if (token) {
      axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
    }
    return next();
  }

  // Route simulasi dan login bebas diakses
  if (to.path === "/simulasi" || to.path === "/login") {
    if (token) {
      axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
    }
    return next();
  }

  // Untuk route admin/superadmin, perlu token
  if (!token) {
    return next("/login");
  }

  axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;

  if (to.path === "/login") {
    if (user?.role === "superadmin") return next("/superadmin/dashboard");
    if (user?.role === "admin") return next("/admin/dashboard");
  }

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