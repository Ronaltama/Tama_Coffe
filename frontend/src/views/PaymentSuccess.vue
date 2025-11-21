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
        <h1 class="text-lg font-bold text-gray-900">Pesanan Diterima</h1>
        <div class="w-6"></div>
      </header>

      <!-- Main Content -->
      <main class="flex-1 px-6 py-8 overflow-y-auto">
        
        <!-- Success Message -->
        <section class="text-center mb-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-3">Reservasi Anda telah diterima</h2>
          <p class="text-sm text-gray-600 leading-relaxed">Reservasi Anda akan disiapkan</p>
        </section>

        <!-- Reservation Number -->
        <section class="text-center mb-8">
          <div class="inline-block">
            <p class="text-4xl font-bold text-gray-900 mb-1">{{ reservationNumber }}</p>
            <p class="text-xs text-gray-500">Nomor Reservasi</p>
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
            <p class="text-sm text-gray-600 mb-2">jumlah Orang</p>
            <div class="flex items-center gap-2 text-gray-900">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
              </svg>
              <span class="text-base">{{ numberOfPeople }}</span>
            </div>
          </div>

          <!-- Date & Time -->
          <div class="mb-4">
            <p class="text-sm text-gray-600 mb-2">Tanggal / Jam</p>
            <div class="flex items-center gap-2 text-gray-900">
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
const reservationNumber = ref('1234567890');
const customerName = ref('');
const phoneNumber = ref('');
const numberOfPeople = ref('');
const dateTime = ref('');

onMounted(() => {
  // Get data from localStorage (from previous order)
  const pendingData = localStorage.getItem('pendingOrder');
  
  if (pendingData) {
    const orderData = JSON.parse(pendingData);
    
    // Generate reservation number
    reservationNumber.value = Math.floor(1000000000 + Math.random() * 9000000000).toString();
    
    // Set customer data
    customerName.value = orderData.customer?.name || 'Guest';
    phoneNumber.value = orderData.customer?.phone || '-';
    numberOfPeople.value = orderData.numberOfPeople || orderData.table || '-';
    
    // Get reservation details if exists
    const reservationDetails = localStorage.getItem('reservationDetails');
    if (reservationDetails) {
      const details = JSON.parse(reservationDetails);
      dateTime.value = `${details.date} / ${details.time}`;
    } else {
      // Generate current date time
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
    dateTime.value = '16-10-2025 / 12.30 WIB';
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
  localStorage.removeItem('orderType'); // 🆕 Hapus orderType
  localStorage.removeItem('reservationDetails'); // 🆕 Hapus reservationDetails
  
  router.push('/order/menu');
};
</script>

<style scoped>
main {
  overflow-y: auto;
}
</style>