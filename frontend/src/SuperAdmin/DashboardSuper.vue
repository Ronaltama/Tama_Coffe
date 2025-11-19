<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const stats = ref({
  totalOrders: 0,
  totalProducts: 0,
  totalUsers: 0,
  totalRevenue: 0,
  totalTables: 0,
  totalAdmins: 0,
  totalSuperadmin: 0,
});

const activeTab = ref("daily");
const topProducts = ref([]);
const recentAdmins = ref([]);

const formatCurrency = (value) => {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  }).format(value);
};

const fetchDashboardData = async () => {
  try {
    const token = localStorage.getItem("token");
    axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;

    // 🔥 MENGAMBIL LANGSUNG DARI DashboardSuperController
    const dashRes = await axios.get(
      "http://127.0.0.1:8000/api/superadmin/dashboard"
    );

    stats.value.totalOrders = dashRes.data.total_orders;
    stats.value.totalProducts = dashRes.data.total_products;
    stats.value.totalUsers = dashRes.data.total_users;
    stats.value.totalRevenue = 0; // nanti bisa ambil dari order
    stats.value.totalTables = dashRes.data.total_tables;
    stats.value.totalAdmins = dashRes.data.total_admins;
    stats.value.totalSuperadmin = dashRes.data.total_superadmin;

    // 🔥 Ambil admin saja
    const userRes = await axios.get("http://127.0.0.1:8000/api/users");
    const userList = userRes.data ?? [];

    recentAdmins.value = userList.map((u) => ({
      name: u.name,
      email: u.email,
      role: "Admin",
      status: "Active",
    }));

    // 🔥 Ambil top 3 produk
    const productRes = await axios.get("http://127.0.0.1:8000/api/products");
    const productList = productRes.data.data ?? [];

    topProducts.value = productList.slice(0, 3).map((p) => ({
      name: p.name,
      units_sold: 0,
      revenue: 0,
    }));

  } catch (error) {
    console.error("Error fetch dashboard:", error);
  }
};

onMounted(() => fetchDashboardData());
</script>


<template>
  <div class="space-y-8">
    
    <div>
      <h1 class="text-3xl font-bold text-gray-900">Superadmin Dashboard</h1>
    </div>

    <!-- --- STAT CARDS --- -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

      <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
        <div class="text-sm text-gray-600 mb-2">Total Orders</div>
        <div class="text-3xl font-bold text-gray-900">{{ stats.totalOrders }}</div>
      </div>

      <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
        <div class="text-sm text-gray-600 mb-2">Total Products</div>
        <div class="text-3xl font-bold text-gray-900">{{ stats.totalProducts }}</div>
      </div>

      <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
        <div class="text-sm text-gray-600 mb-2">Total Users</div>
        <div class="text-3xl font-bold text-gray-900">{{ stats.totalUsers }}</div>
      </div>

      <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
        <div class="text-sm text-gray-600 mb-2">Admin Accounts</div>
        <div class="text-3xl font-bold text-gray-900">{{ stats.totalAdmins }}</div>
      </div>

      <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
        <div class="text-sm text-gray-600 mb-2">Superadmin</div>
        <div class="text-3xl font-bold text-gray-900">{{ stats.totalSuperadmin }}</div>
      </div>

      <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
        <div class="text-sm text-gray-600 mb-2">Tables</div>
        <div class="text-3xl font-bold text-gray-900">{{ stats.totalTables }}</div>
      </div>

    </div>

    <!-- SALES REPORT SECTION -->
    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-gray-900">Sales Reports</h2>

        <div class="flex gap-1 border border-gray-300 rounded-lg p-1 text-sm">
          <button
            @click="activeTab = 'daily'"
            :class="activeTab === 'daily' ? 'bg-amber-700 text-white' : 'text-gray-600 hover:bg-gray-100'"
            class="px-3 py-1 rounded"
          >
            Daily
          </button>
          <button
            @click="activeTab = 'weekly'"
            :class="activeTab === 'weekly' ? 'bg-amber-700 text-white' : 'text-gray-600 hover:bg-gray-100'"
            class="px-3 py-1 rounded"
          >
            Weekly
          </button>
          <button
            @click="activeTab = 'monthly'"
            :class="activeTab === 'monthly' ? 'bg-amber-700 text-white' : 'text-gray-600 hover:bg-gray-100'"
            class="px-3 py-1 rounded"
          >
            Monthly
          </button>
          <button
            @click="activeTab = 'product'"
            :class="activeTab === 'product' ? 'bg-amber-700 text-white' : 'text-gray-600 hover:bg-gray-100'"
            class="px-3 py-1 rounded"
          >
            Per Product
          </button>
        </div>
      </div>

      <!-- Placeholder Chart -->
      <div class="bg-gray-50 rounded-lg h-64 flex items-center justify-center border border-dashed border-gray-300 mb-8">
        <p class="text-gray-500 italic">Tempat untuk Chart.js atau ApexCharts</p>
      </div>

      <!-- TOP PRODUCTS -->
      <div>
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Selling Products Today</h3>

        <div class="overflow-x-auto">
          <table class="w-full min-w-[640px]">
            <thead>
              <tr class="border-b border-gray-200 text-left text-sm font-semibold text-gray-700">
                <th class="py-3 px-4">Product</th>
                <th class="py-3 px-4">Units Sold</th>
                <th class="py-3 px-4">Revenue</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="p in topProducts" :key="p.name" class="hover:bg-gray-50">
                <td class="py-3 px-4 text-sm">{{ p.name }}</td>
                <td class="py-3 px-4 text-sm">{{ p.units_sold }}</td>
                <td class="py-3 px-4 text-sm">{{ formatCurrency(p.revenue) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>

    <!-- ADMIN LIST -->
    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
      <h2 class="text-xl font-bold text-gray-900 mb-6">Admin Users</h2>

      <div class="overflow-x-auto">
        <table class="w-full min-w-[640px]">
          <thead>
            <tr class="border-b border-gray-200 text-left text-sm font-semibold text-gray-700">
              <th class="py-3 px-4">Name</th>
              <th class="py-3 px-4">Email</th>
              <th class="py-3 px-4">Role</th>
              <th class="py-3 px-4">Status</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-gray-100">
            <tr v-for="admin in recentAdmins" :key="admin.email" class="hover:bg-gray-50">
              <td class="py-3 px-4 text-sm">{{ admin.name }}</td>
              <td class="py-3 px-4 text-sm">{{ admin.email }}</td>
              <td class="py-3 px-4 text-sm">
                <span class="inline-block px-3 py-1 bg-pink-100 text-pink-700 text-xs rounded-full">
                  Admin
                </span>
              </td>
              <td class="py-3 px-4 text-sm text-green-600">
                <span class="inline-flex items-center">
                  <span class="h-2 w-2 rounded-full bg-green-500 mr-1"></span> Active
                </span>
              </td>
            </tr>
          </tbody>

        </table>
      </div>
    </div>

  </div>
</template>
