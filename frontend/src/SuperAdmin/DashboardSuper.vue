<script setup>
import { ref } from 'vue';

// --- DATA CONTOH ---
// Ganti ini dengan data dari API Anda
const stats = ref({
  totalOrders: 1234,
  activeUsers: 567,
  totalRevenue: 8901200
});

const activeTab = ref('daily'); // Untuk mengontrol tab sales report

const topProducts = ref([
  { name: 'Artisan Coffee Beans', units_sold: 25, revenue: 3750000 },
  { name: 'Gourmet Chocolate Bar', units_sold: 18, revenue: 1440000 },
  { name: 'Organic Tea Selection', units_sold: 15, revenue: 2250000 },
]);

const recentAdmins = ref([
  { name: 'Rijal Cilacap', email: 'rijal@yahoo.com', role: 'Admin', status: 'Active' },
  { name: 'Sahroni', email: 'sahroni@yahoo.com', role: 'Admin', status: 'Active' },
]);
// --- AKHIR DATA CONTOH ---

// Helper untuk format mata uang
const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(value);
};
</script>

<template>
  <div class="space-y-8">

    <div>
      <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
        <div class="text-sm text-gray-600 mb-2">Total Orders</div>
        <div class="text-3xl font-bold text-gray-900">{{ stats.totalOrders }}</div>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
        <div class="text-sm text-gray-600 mb-2">Active Users</div>
        <div class="text-3xl font-bold text-gray-900">{{ stats.activeUsers }}</div>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
        <div class="text-sm text-gray-600 mb-2">Revenue</div>
        <div class="text-3xl font-bold text-gray-900">{{ formatCurrency(stats.totalRevenue) }}</div>
      </div>
    </div>

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

      <div v-if="activeTab === 'daily'">
        <div class="mb-4 flex justify-between items-baseline">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Daily Sales</h3>
            <p class="text-sm text-gray-600">October 23, 2025</p> </div>
          <p class="text-sm text-gray-600">Total Revenue: <span class="font-semibold">{{ formatCurrency(1250000) }}</span></p>
        </div>

        <div class="bg-gray-50 rounded-lg h-64 flex items-center justify-center border border-dashed border-gray-300 mb-8">
          <p class="text-gray-500 italic">Tempat untuk Chart.js atau ApexCharts</p>
        </div>
      </div>
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
              <template v-if="topProducts.length > 0">
                <tr v-for="product in topProducts" :key="product.name" class="hover:bg-gray-50">
                  <td class="py-3 px-4 text-sm text-gray-900">{{ product.name }}</td>
                  <td class="py-3 px-4 text-sm text-gray-900">{{ product.units_sold }}</td>
                  <td class="py-3 px-4 text-sm text-gray-900">{{ formatCurrency(product.revenue) }}</td>
                </tr>
              </template>
              <template v-else>
                 <tr>
                    <td colspan="3" class="py-8 text-center text-sm text-gray-500">
                      No top selling products today.
                    </td>
                  </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
      <h2 class="text-xl font-bold text-gray-900 mb-6">User Management</h2>
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
            <template v-if="recentAdmins.length > 0">
              <tr v-for="admin in recentAdmins" :key="admin.email" class="hover:bg-gray-50">
                <td class="py-3 px-4 text-sm text-gray-900">{{ admin.name }}</td>
                <td class="py-3 px-4 text-sm text-gray-900">{{ admin.email }}</td>
                <td class="py-3 px-4">
                  <span class="inline-block px-3 py-1 bg-pink-100 text-pink-700 text-xs rounded-full">
                    {{ admin.role }}
                  </span>
                </td>
                <td class="py-3 px-4">
                  <span class="inline-flex items-center text-sm text-green-600">
                    <span class="mr-1.5 h-2 w-2 rounded-full bg-green-500"></span>
                    {{ admin.status }}
                  </span>
                </td>
              </tr>
            </template>
            <template v-else>
               <tr>
                  <td colspan="4" class="py-8 text-center text-sm text-gray-500">
                    No admin users found.
                  </td>
                </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</template>

<style scoped>
/* Gaya khusus komponen bisa ditambahkan di sini jika perlu */
</style>