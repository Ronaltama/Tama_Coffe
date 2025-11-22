<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import Swal from "sweetalert2";
import { useRouter } from "vue-router";

const tables = ref([]);
const loading = ref(true);

const router = useRouter();
const API_BASE = "http://localhost:8000/api";

// Fetch all tables
const fetchTables = async () => {
  try {
    const res = await axios.get(`${API_BASE}/tables`);
    tables.value = res.data.data || [];
  } catch (err) {
    Swal.fire({
      icon: "error",
      title: "Gagal memuat meja",
      text: err.response?.data?.message || "Server bermasalah",
    });
  } finally {
    loading.value = false;
  }
};

// Go to order menu with table ID
const goToOrder = (tableId, tableNumber, status) => {
  // Cek status meja
  if (status === 'maintenance') {
    Swal.fire({
      icon: "warning",
      title: "Meja Tidak Tersedia",
      text: "Meja ini sedang dalam perbaikan",
    });
    return;
  }

  if (status === 'occupied') {
    Swal.fire({
      icon: "info",
      title: "Meja Sedang Digunakan",
      text: `Meja ${tableNumber} sedang digunakan pelanggan lain`,
    });
    return;
  }

  // Clear previous order data
  localStorage.removeItem('cart');
  localStorage.removeItem('cartNotes');
  localStorage.removeItem('pendingOrder');
  localStorage.removeItem('reservationDetails');
  localStorage.setItem('orderType', 'Dine In');

  // Redirect ke order menu dengan table ID sebagai query parameter
  router.push({
    name: 'UserMenu',
    query: { table: tableId }
  });
};

onMounted(fetchTables);
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-orange-50 to-amber-50 p-6">
    <div class="max-w-6xl mx-auto">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-800 mb-3">
          🍽️ Simulasi Scan QR Order
        </h1>
        <p class="text-gray-600 text-lg">
          Klik salah satu meja untuk memulai pemesanan
        </p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-20">
        <div class="inline-block animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-orange-600"></div>
        <p class="text-gray-600 mt-4 text-lg">Memuat data meja...</p>
      </div>

      <!-- Tables Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div
          v-for="table in tables"
          :key="table.id"
          @click="goToOrder(table.id, table.table_number, table.status)"
          class="group relative bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 cursor-pointer transform hover:-translate-y-2 overflow-hidden"
          :class="{
            'opacity-60 cursor-not-allowed': table.status === 'occupied' || table.status === 'maintenance'
          }"
        >
          <!-- Status Badge -->
          <div class="absolute top-4 right-4 z-10">
            <span
              class="px-3 py-1.5 rounded-full text-xs font-bold text-white shadow-lg"
              :class="{
                'bg-green-500': table.status === 'available',
                'bg-yellow-500': table.status === 'reserved',
                'bg-red-500': table.status === 'occupied',
                'bg-gray-600': table.status === 'maintenance'
              }"
            >
              {{ table.status.toUpperCase() }}
            </span>
          </div>

          <!-- Card Content -->
          <div class="p-6">
            <!-- Table Icon -->
            <div class="mb-4 flex justify-center">
              <div class="w-20 h-20 bg-gradient-to-br from-orange-400 to-amber-500 rounded-full flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
              </div>
            </div>

            <!-- Table Info -->
            <div class="text-center">
              <h2 class="text-2xl font-bold text-gray-800 mb-2">
                {{ table.table_number }}
              </h2>
              
              <div class="space-y-2 text-sm text-gray-600">
                <div class="flex items-center justify-center gap-2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                  <span>Kapasitas: {{ table.capacity }} orang</span>
                </div>
                
                <div class="flex items-center justify-center gap-2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                  <span>{{ table.type || 'Standard' }}</span>
                </div>
              </div>
            </div>

            <!-- Action Indicator -->
            <div class="mt-6 text-center">
              <div 
                v-if="table.status === 'available'"
                class="inline-flex items-center gap-2 text-orange-600 font-semibold group-hover:gap-3 transition-all"
              >
                <span>Mulai Order</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
              </div>
              <div 
                v-else-if="table.status === 'occupied'"
                class="text-gray-500 font-medium"
              >
                Sedang digunakan
              </div>
              <div 
                v-else-if="table.status === 'maintenance'"
                class="text-gray-500 font-medium"
              >
                Dalam perbaikan
              </div>
              <div 
                v-else
                class="text-yellow-600 font-medium"
              >
                Direservasi
              </div>
            </div>
          </div>

          <!-- Decorative Border -->
          <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-orange-400 to-amber-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!loading && tables.length === 0" class="text-center py-20">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
        </svg>
        <p class="text-gray-500 text-lg">Belum ada meja yang tersedia</p>
      </div>
    </div>
  </div>
</template>