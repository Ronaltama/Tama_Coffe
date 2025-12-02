<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const API_BASE = 'http://localhost:8000/api';

// === STATE ===
const loading = ref(true);
const error = ref(null);
const allOrders = ref([]);

// --- FILTER & SEARCH STATE ---
const searchTerm = ref('');
const selectedDateRange = ref('All'); // 'All', 'Today', 'Last 7 Days', 'This Month'
const selectedCustomer = ref('All'); // 'All', atau nama customer
const selectedStatus = ref('All'); // 'All', 'Completed', 'Pending', 'Shipped'

// === FETCH DATA FROM API ===
const fetchOrders = async () => {
  try {
    loading.value = true;
    error.value = null;

    const token = localStorage.getItem('token');
    const config = token ? { headers: { Authorization: `Bearer ${token}` } } : {};

    // Panggil endpoint history (authenticated)
    const res = await axios.get(`${API_BASE}/orders/history`, config);

    // Jika backend mengembalikan non-JSON (mis. HTML) axios akan error; tapi tangani status juga
    if (res.status === 200 && res.data && res.data.success) {
      const orders = res.data.data || [];

      allOrders.value = orders
        .map(order => ({
          id: order.id,
          date: formatDate(order.created_at),
          customer: order.customer_name || '-',
          total: order.total_price || 0,
          status: mapStatus(order.status),
          processedBy: order.processed_by || order.processedBy || '-'   // dukung kedua key
        }))
        .sort((a, b) => new Date(b.date) - new Date(a.date));
    } else {
      // tampilkan pesan dari server jika ada
      const msg = (res.data && res.data.message) ? res.data.message : `Unexpected response (${res.status})`;
      throw new Error(msg);
    }
  } catch (err) {
    console.error('Error fetching orders:', err);
    // jika axios error dan ada response, gunakan pesan dari server
    if (err.response) {
      if (err.response.status === 403) {
        error.value = 'Akses ditolak. Pastikan Anda login dengan akun yang memiliki hak akses.';
      } else if (err.response.data && err.response.data.message) {
        error.value = err.response.data.message;
      } else {
        error.value = `Server error (${err.response.status})`;
      }
    } else {
      error.value = 'Gagal memuat data. Silakan cek koneksi atau backend.';
    }
  } finally {
    loading.value = false;
  }
};

// === HELPER FUNCTIONS ===
const formatDate = (datetime) => {
  if (!datetime) return '-';
  const date = new Date(datetime);
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
};

const mapStatus = (status) => {
  const statusMap = {
    'pending': 'Pending',
    'processing': 'Shipped',
    'completed': 'Completed',
    'cancelled': 'Cancelled'
  };
  return statusMap[status] || status;
};

// List unik customer untuk dropdown
const uniqueCustomers = computed(() => {
  const customers = new Set(allOrders.value.map(order => order.customer));
  return ['All', ...Array.from(customers).sort()];
});

// Filter order berdasarkan semua kriteria
const filteredOrders = computed(() => {
  let result = allOrders.value;

  // 1. Search by ID, customer, or product (as in screenshot)
  if (searchTerm.value) {
    const searchLower = searchTerm.value.toLowerCase();
    result = result.filter(order =>
      order.id.toLowerCase().includes(searchLower) ||
      order.customer.toLowerCase().includes(searchLower)
    );
  }

  // 2. Filter by Date Range
  if (selectedDateRange.value !== 'All') {
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    result = result.filter(order => {
      const orderDate = new Date(order.date);
      orderDate.setHours(0, 0, 0, 0);

      switch (selectedDateRange.value) {
        case 'Today':
          return orderDate.getTime() === today.getTime();
        case 'Last 7 Days':
          const sevenDaysAgo = new Date(today);
          sevenDaysAgo.setDate(today.getDate() - 7);
          return orderDate >= sevenDaysAgo && orderDate <= today;
        case 'This Month':
          return orderDate.getMonth() === today.getMonth() && orderDate.getFullYear() === today.getFullYear();
        default:
          return true;
      }
    });
  }

  // 3. Filter by Customer
  if (selectedCustomer.value !== 'All') {
    result = result.filter(order => order.customer === selectedCustomer.value);
  }

  // 4. Filter by Status
  if (selectedStatus.value !== 'All') {
    result = result.filter(order => order.status === selectedStatus.value);
  }

  return result;
});

// Helper untuk format mata uang
const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 0
  }).format(value / 1000);
};

// Helper untuk mendapatkan kelas CSS berdasarkan Status
const getStatusClasses = (status) => {
  switch (status) {
    case 'Completed':
      return 'bg-green-100 text-green-700';
    case 'Shipped':
      return 'bg-blue-100 text-blue-700';
    case 'Pending':
      return 'bg-yellow-100 text-yellow-700';
    case 'Cancelled':
      return 'bg-red-100 text-red-700';
    default:
      return 'bg-gray-100 text-gray-700';
  }
};

// Fungsi untuk melihat detail order
const viewOrderDetails = (id) => {
  console.log(`Viewing order details ${id}`);
};

// === LIFECYCLE ===
onMounted(() => {
  fetchOrders();
});
</script>

<template>
  <div class="space-y-8">
    <div>
      <h1 class="text-3xl font-bold text-gray-900">Order History</h1>
      <p class="text-gray-600">View and manage all past orders.</p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-gray-900 mx-auto"></div>
      <p class="text-gray-500 mt-4">Loading...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-xl p-6 text-center">
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
      <div class="flex items-center gap-4 flex-wrap">
        <div class="relative flex-grow max-w-xs">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
          <input
            type="text"
            v-model="searchTerm"
            placeholder="Search orders by ID, customer, or product"
            class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg w-full focus:ring-amber-500 focus:border-amber-500"
          />
        </div>

        <div class="relative">
          <select
            v-model="selectedDateRange"
            class="block appearance-none w-full bg-white border border-gray-300 text-gray-700 py-2 px-4 pr-8 rounded-lg leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
          >
            <option value="All">Date Range</option>
            <option value="Today">Today</option>
            <option value="Last 7 Days">Last 7 Days</option>
            <option value="This Month">This Month</option>
          </select>
          <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
          </div>
        </div>

        <div class="relative">
          <select
            v-model="selectedCustomer"
            class="block appearance-none w-full bg-white border border-gray-300 text-gray-700 py-2 px-4 pr-8 rounded-lg leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
          >
            <option value="All">Customer</option>
            <option v-for="customer in uniqueCustomers" :key="customer" :value="customer">{{ customer }}</option>
          </select>
          <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
          </div>
        </div>

        <div class="relative">
          <select
            v-model="selectedStatus"
            class="block appearance-none w-full bg-white border border-gray-300 text-gray-700 py-2 px-4 pr-8 rounded-lg leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
          >
            <option value="All">Status</option>
            <option value="Completed">Completed</option>
            <option value="Pending">Pending</option>
            <option value="Shipped">Shipped</option>
            <option value="Cancelled">Cancelled</option>
          </select>
          <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
          </div>
        </div>
      </div>

      <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
        <div class="overflow-x-auto">
          <table class="w-full min-w-[700px]">
            <thead>
              <tr class="border-b border-gray-200 text-left text-sm font-semibold text-gray-700">
                <th class="py-3 px-4">ORDER ID</th>
                <th class="py-3 px-4">DATE</th>
                <th class="py-3 px-4">CUSTOMER</th>
                <th class="py-3 px-4">TOTAL AMOUNT</th>
                <th class="py-3 px-4">STATUS</th>
                <th class="py-3 px-4">PROCESSED BY</th> <!-- kolom baru -->
                <th class="py-3 px-4">ACTIONS</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <template v-if="filteredOrders.length > 0">
                <tr v-for="order in filteredOrders" :key="order.id" class="hover:bg-gray-50">
                  <td class="py-3 px-4 text-sm text-gray-900 font-medium">{{ order.id }}</td>
                  <td class="py-3 px-4 text-sm text-gray-700">{{ order.date }}</td>
                  <td class="py-3 px-4 text-sm text-gray-700">{{ order.customer }}</td>
                  <td class="py-3 px-4 text-sm text-gray-700">{{ formatCurrency(order.total) }}</td>
                  <td class="py-3 px-4">
                    <span class="inline-block px-3 py-1 text-xs rounded-full font-medium" :class="getStatusClasses(order.status)">
                      {{ order.status }}
                    </span>
                  </td>
                  <td class="py-3 px-4 text-sm text-gray-700">{{ order.processedBy }}</td> <!-- tampilkan nama -->
                  <td class="py-3 px-4 text-sm">
                    <button @click="viewOrderDetails(order.id)" class="text-blue-600 hover:text-blue-800 font-medium">View Details</button>
                  </td>
                </tr>
              </template>
              <template v-else>
                <tr>
                  <td colspan="6" class="py-8 text-center text-sm text-gray-500">
                    No orders found matching your criteria.
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

<style scoped>
/* Tailwind CSS seharusnya cukup untuk styling ini */
</style>
