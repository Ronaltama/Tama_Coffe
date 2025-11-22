<template>
  <div class="bg-white min-h-screen">
    <div class="max-w-md mx-auto bg-white min-h-screen font-sans flex flex-col">

      <!-- Header -->
      <header class="bg-white border-b border-gray-200 px-4 py-4 flex items-center justify-between">
        <button @click="closeModal" class="p-1 -ml-2 text-gray-800 hover:text-gray-600 transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
        <h1 class="text-lg font-bold text-gray-900">
          {{ isReservation ? 'Pesanan Diterima' : 'Pesanan Berhasil' }}
        </h1>
        <div class="w-6"></div>
      </header>

      <!-- Main Content -->
      <main class="flex-1 px-6 py-8 overflow-y-auto">
        
        <!-- Success Message -->
        <section class="text-center mb-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-3">
            {{ isReservation ? 'Reservasi Anda telah diterima' : 'Pesanan Berhasil' }}
          </h2>
          <p class="text-sm text-gray-600 leading-relaxed">
            {{ isReservation ? 'Reservasi Anda akan disiapkan' : 'Pesanan Anda akan segera disiapkan' }}
          </p>
        </section>

        <!-- Reservation Number / Table Number -->
        <section class="text-center mb-8">
          <div class="inline-block">
            <p class="text-4xl font-bold text-gray-900 mb-1">{{ displayNumber }}</p>
            <p class="text-xs text-gray-500">{{ isReservation ? 'Nomor Reservasi' : 'Nomor Meja' }}</p>
          </div>
        </section>

        <!-- Information Section -->
        <section class="mb-6">
          <h3 class="text-base font-bold text-gray-900 mb-4">Informasi Pesanan</h3>
          
          <!-- Name -->
          <div class="mb-4">
            <p class="text-sm text-gray-600 mb-2">Nama Lengkap</p>
            <div class="flex items-center gap-2 text-gray-900">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
              </svg>
              <span class="text-base">{{ customerName }}</span>
            </div>
          </div>

          <!-- Phone Number -->
          <div class="mb-4">
            <p class="text-sm text-gray-600 mb-2">Nomor Ponsel</p>
            <div class="flex items-center gap-2 text-gray-900">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
              </svg>
              <span class="text-base">{{ phoneNumber }}</span>
            </div>
          </div>

          <!-- Number of People -->
          <div class="mb-4">
            <p class="text-sm text-gray-600 mb-2">Jumlah Orang</p>
            <div class="flex items-center gap-2 text-gray-900">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
              </svg>
              <span class="text-base">{{ numberOfPeople }}</span>
            </div>
          </div>

          <!-- Date & Time -->
          <div v-if="isReservation" class="mb-4">
            <p class="text-sm text-gray-600 mb-2">Tanggal / Jam Reservasi</p>
            <div class="flex items-center gap-2 text-gray-900">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
              </svg>
              <span class="text-base">{{ dateTime }}</span>
            </div>
          </div>

          <!-- Order Time (for regular orders) -->
          <div v-if="!isReservation" class="mb-4">
            <p class="text-sm text-gray-600 mb-2">Waktu Pemesanan</p>
            <div class="flex items-center gap-2 text-gray-900">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
              </svg>
              <span class="text-base">{{ dateTime }}</span>
            </div>
          </div>

        </section>

      </main>

      <!-- Footer Button -->
      <footer class="bg-white border-t border-gray-200 pb-4 pt-3 px-6">
        <button 
          @click="backToMenu"
          class="w-full py-4 bg-orange-600 hover:bg-orange-700 text-white text-base font-bold rounded-xl transition-all shadow-lg active:scale-[0.98]"
        >
          Kembali
        </button>
      </footer>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

// Data refs
const isReservation = ref(false);
const displayNumber = ref('');
const reservationNumber = ref('');
const tableNumber = ref('');
const customerName = ref('');
const phoneNumber = ref('');
const numberOfPeople = ref('');
const dateTime = ref('');

onMounted(() => {
  // Check order type
  const orderType = localStorage.getItem('orderType');
  isReservation.value = orderType === 'reservation';
  
  // Get data from localStorage (from previous order)
  const pendingData = localStorage.getItem('pendingOrder');
  
  if (pendingData) {
    const orderData = JSON.parse(pendingData);
    
    // Set customer data
    customerName.value = orderData.customer?.name || 'Guest';
    phoneNumber.value = orderData.customer?.phone || '-';
    numberOfPeople.value = orderData.numberOfPeople || orderData.table || '-';
    
    if (isReservation.value) {
      // Generate reservation number (10 digits)
      reservationNumber.value = Math.floor(1000000000 + Math.random() * 9000000000).toString();
      displayNumber.value = reservationNumber.value;
      
      // Get reservation details
      const reservationDetails = localStorage.getItem('reservationDetails');
      if (reservationDetails) {
        const details = JSON.parse(reservationDetails);
        dateTime.value = `${details.date} / ${details.time}`;
      } else {
        // Fallback to current date/time
        const now = new Date();
        const date = now.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' }).replace(/\//g, '-');
        const time = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        dateTime.value = `${date} / ${time} WIB`;
      }
    } else {
      // Regular order - use table number
      tableNumber.value = orderData.table || '01';
      displayNumber.value = tableNumber.value;
      
      // Generate current order time
      const now = new Date();
      const date = now.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' }).replace(/\//g, '-');
      const time = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
      dateTime.value = `${date} / ${time} WIB`;
    }
  } else {
    // Dummy data jika tidak ada
    customerName.value = 'Rijal Cilacap';
    phoneNumber.value = '085777733352';
    numberOfPeople.value = '12';
    
    if (isReservation.value) {
      reservationNumber.value = '1234567890';
      displayNumber.value = reservationNumber.value;
      dateTime.value = '16-10-2025 / 12.30 WIB';
    } else {
      tableNumber.value = '01';
      displayNumber.value = tableNumber.value;
      const now = new Date();
      const date = now.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' }).replace(/\//g, '-');
      const time = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
      dateTime.value = `${date} / ${time} WIB`;
    }
  }
});

const closeModal = () => {
  router.push('/order/menu');
};

const backToMenu = () => {
  // Clear order data
  localStorage.removeItem('cart');
  localStorage.removeItem('cartNotes');
  localStorage.removeItem('pendingOrder');
  localStorage.removeItem('orderType');
  localStorage.removeItem('reservationDetails');
  
  router.push('/order/menu');
};
</script>

<style scoped>
main {
  overflow-y: auto;
}
</style>