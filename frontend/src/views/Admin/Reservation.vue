<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Manajemen Reservasi</h1>
      <button @click="fetchReservations" class="p-2 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-orange-500"></div>
    </div>

    <!-- Empty State -->
    <div v-else-if="reservations.length === 0" class="text-center py-12 bg-white rounded-xl shadow-sm">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
      </svg>
      <p class="text-gray-500">Belum ada data reservasi.</p>
    </div>

    <!-- Reservation List -->
    <div v-else class="space-y-4">
      <div v-for="reservation in reservations" :key="reservation.id" class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
        <div class="flex justify-between items-start">
          <div>
            <div class="flex items-center gap-2 mb-1">
              <span class="font-bold text-lg text-gray-900">{{ reservation.name }}</span>
              <span 
                class="px-2 py-0.5 text-xs font-medium rounded-full"
                :class="{
                  'bg-yellow-100 text-yellow-800': reservation.status === 'pending',
                  'bg-green-100 text-green-800': reservation.status === 'confirmed',
                  'bg-red-100 text-red-800': reservation.status === 'rejected' || reservation.status === 'cancelled',
                  'bg-blue-100 text-blue-800': reservation.status === 'completed'
                }"
              >
                {{ reservation.status.toUpperCase() }}
              </span>
            </div>
            <p class="text-sm text-gray-500 mb-2">{{ reservation.phone }}</p>
            
            <div class="flex items-center gap-4 text-sm text-gray-600">
              <div class="flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                {{ formatDate(reservation.date) }}
              </div>
              <div class="flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ reservation.time }}
              </div>
            </div>
            
            <!-- Order Details Preview -->
            <div v-if="reservation.order" class="mt-3 pt-3 border-t border-gray-100">
              <p class="text-xs font-semibold text-gray-500 mb-1">Pesanan:</p>
              <ul class="text-sm text-gray-700 space-y-1">
                <li v-for="item in reservation.order.items" :key="item.id">
                  {{ item.quantity }}x {{ item.product?.name }}
                </li>
              </ul>
              <p class="text-sm font-bold text-orange-600 mt-2">Total: Rp{{ formatPrice(reservation.order.total_price) }}</p>
            </div>
          </div>

          <!-- Actions -->
          <div v-if="reservation.status === 'pending'" class="flex flex-col gap-2">
            <button 
              @click="updateStatus(reservation.id, 'confirmed')"
              class="px-3 py-1.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors"
            >
              Terima
            </button>
            <button 
              @click="updateStatus(reservation.id, 'rejected')"
              class="px-3 py-1.5 bg-red-100 text-red-600 text-sm font-medium rounded-lg hover:bg-red-200 transition-colors"
            >
              Tolak
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const reservations = ref([]);
const isLoading = ref(false);

const fetchReservations = async () => {
  isLoading.value = true;
  try {
    const response = await axios.get('http://localhost:8000/api/admin/reservations');
    if (response.data.success) {
      reservations.value = response.data.data;
    }
  } catch (error) {
    console.error('Error fetching reservations:', error);
    Swal.fire('Error', 'Gagal mengambil data reservasi', 'error');
  } finally {
    isLoading.value = false;
  }
};

const updateStatus = async (id, status) => {
  try {
    const result = await Swal.fire({
      title: 'Konfirmasi',
      text: `Apakah Anda yakin ingin mengubah status menjadi ${status}?`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Ya, Lanjutkan',
      cancelButtonText: 'Batal'
    });

    if (result.isConfirmed) {
      const response = await axios.put(`http://localhost:8000/api/admin/reservations/${id}/status`, { status });
      if (response.data.success) {
        Swal.fire('Sukses', 'Status berhasil diperbarui', 'success');
        fetchReservations(); // Refresh list
      }
    }
  } catch (error) {
    console.error('Error updating status:', error);
    Swal.fire('Error', 'Gagal memperbarui status', 'error');
  }
};

const formatDate = (dateString) => {
  if (!dateString) return '-';
  const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
  return new Date(dateString).toLocaleDateString('id-ID', options);
};

const formatPrice = (price) => {
  return (price || 0).toLocaleString('id-ID');
};

onMounted(() => {
  fetchReservations();
});
</script>
