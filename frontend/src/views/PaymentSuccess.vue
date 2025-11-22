<template>
  <div class="bg-white min-h-screen">
    <div class="max-w-md mx-auto bg-white min-h-screen font-sans flex flex-col">

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

      <main class="flex-1 px-6 py-8 overflow-y-auto" v-if="hasData">
        
        <section class="text-center mb-8">
          <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
             <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
          </div>

          <h2 class="text-2xl font-bold text-gray-900 mb-3">
            {{ isReservation ? 'Permintaan Reservasi Terkirim' : 'Pesanan Berhasil' }}
          </h2>
          <p class="text-sm text-gray-600 leading-relaxed">
            {{ isReservation 
               ? 'Reservasi Anda telah diterima dan sedang diproses.' 
               : 'Pesanan Anda telah masuk ke dapur dan segera disiapkan.' }}
          </p>
        </section>

        <section class="text-center mb-8">
          <div class="inline-block bg-neutral-50 rounded-xl px-8 py-4 border border-neutral-200">
            <p class="text-3xl font-bold text-gray-900 mb-1 tracking-wider">{{ displayNumber }}</p>
            <p class="text-xs text-gray-500 uppercase font-semibold">
              {{ isReservation ? 'Kode Booking' : 'Nomor Meja' }}
            </p>
          </div>
        </section>

        <section class="mb-6 bg-white border border-gray-100 rounded-xl shadow-sm p-4">
          <h3 class="text-base font-bold text-gray-900 mb-4 border-b border-gray-100 pb-2">Informasi Pesanan</h3>
          
          <div class="mb-4">
            <p class="text-xs text-gray-500 mb-1">Nama Pemesan</p>
            <div class="flex items-center gap-2 text-gray-900">
              <span class="text-base font-medium">{{ customerName }}</span>
            </div>
          </div>

          <div class="mb-4">
            <p class="text-xs text-gray-500 mb-1">Nomor Ponsel</p>
            <div class="flex items-center gap-2 text-gray-900">
              <span class="text-base font-medium">{{ phoneNumber }}</span>
            </div>
          </div>

          <div class="mb-4">
            <p class="text-xs text-gray-500 mb-1">Jumlah Tamu</p>
            <div class="flex items-center gap-2 text-gray-900">
              <span class="text-base font-medium">{{ numberOfPeople }} Orang</span>
            </div>
          </div>

          <div class="mb-2">
            <p class="text-xs text-gray-500 mb-1">
               {{ isReservation ? 'Jadwal Reservasi' : 'Waktu Transaksi' }}
            </p>
            <div class="flex items-center gap-2 text-gray-900">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-500" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
              </svg>
              <span class="text-base font-medium text-orange-700">{{ dateTime }}</span>
            </div>
          </div>

        </section>

      </main>

      <main v-else class="flex-1 flex flex-col items-center justify-center p-6 text-center">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-2">Data Tidak Ditemukan</h3>
        <p class="text-gray-500 text-sm">Sesi pesanan Anda telah berakhir. Silakan buat pesanan baru.</p>
      </main>

      <footer class="bg-white border-t border-gray-200 pb-4 pt-3 px-6">
        <button 
          @click="backToMenu"
          class="w-full py-4 bg-orange-600 hover:bg-orange-700 text-white text-base font-bold rounded-xl transition-all shadow-lg active:scale-[0.98]"
        >
          {{ hasData ? 'Buat Pesanan Baru' : 'Kembali ke Menu' }}
        </button>
      </footer>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

// State
const hasData = ref(false);
const isReservation = ref(false);
const displayNumber = ref('');
const customerName = ref('');
const phoneNumber = ref('');
const numberOfPeople = ref('');
const dateTime = ref('');

onMounted(() => {
  const finishedData = localStorage.getItem('finishedOrder');
  
  if (finishedData) {
    hasData.value = true;
    const orderData = JSON.parse(finishedData);
    
    isReservation.value = orderData.orderType === 'Reservasi';
    
    customerName.value = orderData.customer?.name || '-';
    phoneNumber.value = orderData.customer?.phone || '-';
    numberOfPeople.value = orderData.numberOfPeople || '-';

    if (isReservation.value) {
      displayNumber.value = orderData.finalOrderId || 'RES-' + Math.floor(Math.random() * 10000); 
      
      if (orderData.reservationMeta) {
        dateTime.value = `${orderData.reservationMeta.date} / ${orderData.reservationMeta.time}`;
      } else {
        dateTime.value = '-';
      }

    } else {
      displayNumber.value = orderData.table || '-';
      
      const now = orderData.transactionTime ? new Date(orderData.transactionTime) : new Date();
      const date = now.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
      const time = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
      dateTime.value = `${date}, ${time} WIB`;
    }

  } else {
    hasData.value = false;
  }
});

const closeModal = () => {
  backToMenu();
};

const backToMenu = () => {
  localStorage.removeItem('finishedOrder');
  
  router.push('/order/menu');
};
</script>

<style scoped>
main {
  overflow-y: auto;
}
</style>