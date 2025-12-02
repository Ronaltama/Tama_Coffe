<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";

// === API BASE URL ===
const API_BASE = "http://localhost:8000/api";

// === STATE ===
const loading = ref(true);
const error = ref(null);

const stats = ref({
  waiting: 0,
  processing: 0,
  completed: 0,
  finishedRevenue: 0,
});

const recentOrders = ref([]);

// === FETCH DATA FROM API ===
const fetchOrders = async () => {
  try {
    loading.value = true;
    error.value = null;

    const token = localStorage.getItem("token");
    const config = token
      ? { headers: { Authorization: `Bearer ${token}` } }
      : {};

    const res = await axios.get(`${API_BASE}/dashboard/stats`, config);

    if (res.data.success) {
      const data = res.data.data;

      // Set stats from API
      stats.value.waiting = data.stats.waiting;
      stats.value.processing = data.stats.processing;
      stats.value.completed = data.stats.completed;
      stats.value.finishedRevenue = data.stats.finishedRevenue;

      // Map recent orders
      recentOrders.value = data.recentOrders.map((order) => ({
        id: order.id,
        customer: order.customer_name,
        date: formatDate(order.created_at),
        status: mapStatus(order.status),
        total: order.total_price,
      }));
    }
  } catch (err) {
    console.error("Error fetching orders:", err);
    error.value = "Gagal memuat data. Silakan refresh halaman.";
  } finally {
    loading.value = false;
  }
};

// === HELPER FUNCTIONS ===
const formatDate = (datetime) => {
  if (!datetime) return "-";
  const date = new Date(datetime);
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
};

const mapStatus = (status) => {
  const statusMap = {
    pending: "Waiting",
    processing: "Processing",
    completed: "Finished",
    cancelled: "Cancelled",
  };
  return statusMap[status] || status;
};

// Helper untuk format mata uang
const formatCurrency = (value) => {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  }).format(value);
};

// Helper untuk mendapatkan kelas CSS berdasarkan status
const getStatusClasses = (status) => {
  switch (status) {
    case "Waiting":
      return "bg-yellow-100 text-yellow-700";
    case "Processing":
      return "bg-blue-100 text-blue-700";
    case "Finished":
      return "bg-green-100 text-green-700";
    case "Cancelled":
      return "bg-red-100 text-red-700";
    default:
      return "bg-gray-100 text-gray-700";
  }
};

const router = useRouter();

const viewOrder = (orderId) => {
  router.push({ name: "AdminOrderDetail", params: { id: orderId } });
};

// === LIFECYCLE ===
onMounted(() => {
  fetchOrders();
});
</script>

<template>
  <div class="space-y-8">
    <div>
      <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div
        class="animate-spin rounded-full h-12 w-12 border-b-2 border-gray-900 mx-auto"
      ></div>
      <p class="text-gray-500 mt-4">Loading...</p>
    </div>

    <!-- Error State -->
    <div
      v-else-if="error"
      class="bg-red-50 border border-red-200 rounded-xl p-6 text-center"
    >
      <p class="text-red-600">{{ error }}</p>
      <button
        @click="fetchOrders"
        class="mt-3 text-sm text-red-600 hover:text-red-800 underline"
      >
        Coba Lagi
      </button>
    </div>

    <!-- Content -->
    <template v-else>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
          <div class="text-sm text-gray-600 mb-2">Waiting (Today)</div>
          <div class="text-3xl font-bold text-gray-900">
            {{ stats.waiting }}
          </div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
          <div class="text-sm text-gray-600 mb-2">Processing (Today)</div>
          <div class="text-3xl font-bold text-gray-900">
            {{ stats.processing }}
          </div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
          <div class="text-sm text-gray-600 mb-2">Completed (Today)</div>
          <div class="text-3xl font-bold text-gray-900">
            {{ stats.completed }}
          </div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
          <div class="text-sm text-gray-600 mb-2">Finished (Today)</div>
          <div class="text-3xl font-bold text-gray-900">
            {{ formatCurrency(stats.finishedRevenue) }}
          </div>
        </div>
      </div>

      <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Recent Orders</h2>

        <div class="overflow-x-auto">
          <table class="w-full min-w-[640px]">
            <thead>
              <tr
                class="border-b border-gray-200 text-left text-sm font-semibold text-gray-700"
              >
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
                <tr
                  v-for="order in recentOrders"
                  :key="order.id"
                  class="hover:bg-gray-50"
                >
                  <td class="py-3 px-4 text-sm text-gray-900">
                    {{ order.id }}
                  </td>
                  <td class="py-3 px-4 text-sm text-gray-900">
                    {{ order.customer }}
                  </td>
                  <td class="py-3 px-4 text-sm text-gray-900">
                    {{ order.date }}
                  </td>
                  <td class="py-3 px-4">
                    <span
                      class="inline-block px-3 py-1 text-xs rounded-full"
                      :class="getStatusClasses(order.status)"
                    >
                      {{ order.status }}
                    </span>
                  </td>
                  <td class="py-3 px-4 text-sm text-gray-900">
                    {{ formatCurrency(order.total) }}
                  </td>
                  <td class="py-3 px-4 text-sm">
                    <button
                      @click="viewOrder(order.id)"
                      class="text-blue-600 hover:underline"
                    >
                      View
                    </button>
                  </td>
                </tr>
              </template>
              <template v-else>
                <tr>
                  <td
                    colspan="6"
                    class="py-8 text-center text-sm text-gray-500"
                  >
                    No recent orders found.
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>
    </template>
  </div>
</template>

<style scoped></style>
