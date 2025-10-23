<script setup>
import { ref } from 'vue';

// --- DATA CONTOH ---
// Ganti ini dengan data dari API Anda, mungkin menggunakan onMounted
const stats = ref({
  waiting: 12,
  processing: 5,
  finishedRevenue: 890120
});

const recentOrders = ref([
  { id: '#12345', customer: 'Sophia Clark', date: '2023-08-15', status: 'Waiting', total: 120000 },
  { id: '#12346', customer: 'Ethan Carter', date: '2023-08-14', status: 'Processing', total: 85000 },
  { id: '#12347', customer: 'Olivia Bennett', date: '2023-08-13', status: 'Processing', total: 250000 },
  { id: '#12348', customer: 'Liam Foster', date: '2023-08-12', status: 'Cancelled', total: 50000 },
  { id: '#12349', customer: 'Ava Harper', date: '2023-08-11', status: 'Finished', total: 150000 },
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

// Helper untuk mendapatkan kelas CSS berdasarkan status
const getStatusClasses = (status) => {
  switch (status) {
    case 'Waiting':
      return 'bg-yellow-100 text-yellow-700';
    case 'Processing':
      return 'bg-blue-100 text-blue-700';
    case 'Finished':
      return 'bg-green-100 text-green-700';
    case 'Cancelled':
      return 'bg-red-100 text-red-700';
    default:
      return 'bg-gray-100 text-gray-700';
  }
};
</script>

<template>
  <div class="space-y-8">

    <div>
      <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
        <div class="text-sm text-gray-600 mb-2">Waiting</div>
        <div class="text-3xl font-bold text-gray-900">{{ stats.waiting }}</div>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
        <div class="text-sm text-gray-600 mb-2">Process</div>
        <div class="text-3xl font-bold text-gray-900">{{ stats.processing }}</div>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
        <div class="text-sm text-gray-600 mb-2">Finished</div>
        <div class="text-3xl font-bold text-gray-900">{{ formatCurrency(stats.finishedRevenue) }}</div>
      </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
      <h2 class="text-xl font-bold text-gray-900 mb-6">Recent Orders</h2>

      <div class="overflow-x-auto">
        <table class="w-full min-w-[640px]">
          <thead>
            <tr class="border-b border-gray-200 text-left text-sm font-semibold text-gray-700">
              <th class="py-3 px-4">Order ID</th>
              <th class="py-3 px-4">Customer</th>
              <th class="py-3 px-4">Date</th>
              <th class="py-3 px-4">Status</th>
              <th class="py-3 px-4">Total</th>
              <th class="py-3 px-4">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <template v-if="recentOrders.length > 0">
              <tr v-for="order in recentOrders" :key="order.id" class="hover:bg-gray-50">
                <td class="py-3 px-4 text-sm text-gray-900">{{ order.id }}</td>
                <td class="py-3 px-4 text-sm text-gray-900">{{ order.customer }}</td>
                <td class="py-3 px-4 text-sm text-gray-900">{{ order.date }}</td>
                <td class="py-3 px-4">
                  <span
                    class="inline-block px-3 py-1 text-xs rounded-full"
                    :class="getStatusClasses(order.status)"
                  >
                    {{ order.status }}
                  </span>
                </td>
                <td class="py-3 px-4 text-sm text-gray-900">{{ formatCurrency(order.total) }}</td>
                <td class="py-3 px-4 text-sm">
                  <a href="#" class="text-blue-600 hover:text-blue-800 mr-2">Edit</a>
                  <a href="#" class="text-red-600 hover:text-red-800">Delete</a>
                </td>
              </tr>
            </template>
            <template v-else>
              <tr>
                <td colspan="6" class="py-8 text-center text-sm text-gray-500">
                  No recent orders found.
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