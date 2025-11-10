<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

// --- DATA CONTOH ---
// (Anda akan mengganti ini dengan data dari API nanti)
const allOrders = ref([
  { id: '#12345', date: '2024-01-15', customer: 'Sophia Clark', total: 150000, status: 'Completed' },
  { id: '#12346', date: '2024-01-16', customer: 'Ethan Carter', total: 200000, status: 'Shipped' },
  { id: '#12347', date: '2024-01-17', customer: 'Olivia Bennett', total: 75000, status: 'Pending' },
  { id: '#12348', date: '2024-01-18', customer: 'Liam Foster', total: 300000, status: 'Completed' },
  { id: '#12349', date: '2024-01-19', customer: 'Ava Harper', total: 120000, status: 'Pending' },
  { id: '#12350', date: '2024-01-20', customer: 'Noah Turner', total: 180000, status: 'Pending' },
  { id: '#12351', date: '2024-01-21', customer: 'Isabella Reed', total: 250000, status: 'Completed' },
  { id: '#12352', date: '2024-01-22', customer: 'Jackson Hayes', total: 90000, status: 'Shipped' },
  { id: '#12353', date: '2024-01-23', customer: 'Mia Coleman', total: 160000, status: 'Pending' },
  { id: '#12354', date: '2024-01-24', customer: 'Aiden Brooks', total: 220000, status: 'Completed' },
]);

// --- FILTER & SEARCH STATE ---
const searchTerm = ref('');
const selectedDateRange = ref('All'); // 'All', 'Today', 'Last 7 Days', 'This Month'
const selectedCustomer = ref('All'); // 'All', atau nama customer
const selectedStatus = ref('All'); // 'All', 'Completed', 'Pending', 'Shipped'

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
      // Nanti bisa tambah filter product jika ada detail order
    );
  }

  // 2. Filter by Date Range
  if (selectedDateRange.value !== 'All') {
    const today = new Date();
    today.setHours(0, 0, 0, 0); // Reset time to start of day

    result = result.filter(order => {
      const orderDate = new Date(order.date);
      orderDate.setHours(0, 0, 0, 0); // Reset time for comparison

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
  }).format(value / 1000); // Divided by 1000 to match screenshot example $150 instead of $150,000
};

// Helper untuk mendapatkan kelas CSS berdasarkan Status (menyesuaikan screenshot)
const getStatusClasses = (status) => {
  switch (status) {
    case 'Completed':
      return 'bg-green-100 text-green-700';
    case 'Shipped': // Example status from screenshot
      return 'bg-blue-100 text-blue-700';
    case 'Pending':
      return 'bg-yellow-100 text-yellow-700';
    case 'Cancelled': // If you decide to add this status
      return 'bg-red-100 text-red-700';
    default:
      return 'bg-gray-100 text-gray-700';
  }
};

// Fungsi untuk melihat detail order
const viewOrderDetails = (id) => {
  console.log(`Viewing order details ${id}`);
  // Nanti Anda bisa arahkan ke halaman detail, contoh:
  // router.push(`/superadmin/history/${id}`);
};

onMounted(() => {
  // Fetch data from API here
  // For now, using static data
});
</script>

<template>
  <div class="space-y-8">
    <div>
      <h1 class="text-3xl font-bold text-gray-900">Order History</h1>
      <p class="text-gray-600">View and manage all past orders.</p>
    </div>

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
              <th class="py-3 px-4">ACTIONS</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <template v-if="filteredOrders.length > 0">
              <tr v-for="order in filteredOrders" :key="order.id" class="hover:bg-gray-50">
                <td class="py-3 px-4 text-sm text-gray-900 font-medium">
                  {{ order.id }}
                </td>
                <td class="py-3 px-4 text-sm text-gray-700">
                  {{ order.date }}
                </td>
                <td class="py-3 px-4 text-sm text-gray-700">
                  {{ order.customer }}
                </td>
                <td class="py-3 px-4 text-sm text-gray-700">
                  {{ formatCurrency(order.total) }}
                </td>
                <td class="py-3 px-4">
                  <span
                    class="inline-block px-3 py-1 text-xs rounded-full font-medium"
                    :class="getStatusClasses(order.status)"
                  >
                    {{ order.status }}
                  </span>
                </td>
                <td class="py-3 px-4 text-sm">
                  <button
                    @click="viewOrderDetails(order.id)"
                    class="text-blue-600 hover:text-blue-800 font-medium"
                  >
                    View Details
                  </button>
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
  </div>
</template>

<style scoped>
/* Tailwind CSS seharusnya cukup untuk styling ini */
/* Pastikan Anda memiliki font awesome jika ingin ikon search */
</style>
