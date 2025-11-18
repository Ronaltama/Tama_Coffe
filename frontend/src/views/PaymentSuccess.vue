<template>
  <div class="bg-white min-h-screen">
    <div class="max-w-md mx-auto bg-white min-h-screen font-sans flex flex-col">

      <!-- Header -->
      <header class="bg-white border-b border-gray-200 px-4 py-4 flex items-center justify-between">
        <button @click="backToMenu" class="p-1 -ml-2 text-gray-800 hover:text-gray-600 transition-colors">
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
          <h2 class="text-2xl font-bold text-gray-900 mb-3">Pesanan Anda telah diterima</h2>
          <p class="text-sm text-gray-600 leading-relaxed">Pesanan Anda akan segera disiapkan</p>
        </section>

        <!-- Queue Number -->
        <section class="text-center mb-8">
          <div class="inline-block">
            <p class="text-5xl font-bold text-gray-900 mb-2">{{ queueNumber }}</p>
            <p class="text-xs text-gray-500 uppercase tracking-wider font-bold">Nomor Antrian</p>
          </div>
        </section>

        <!-- Information Section -->
        <section class="mb-6" v-if="orderData">
          <h3 class="text-base font-bold text-gray-900 mb-4">Informasi Pesanan</h3>
          
          <!-- Name -->
          <div class="mb-4">
            <p class="text-sm text-gray-600 mb-2">Nama Lengkap</p>
            <div class="flex items-center gap-2 text-gray-900">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
              </svg>
              <span class="text-base font-medium">{{ orderData.customer.name }}</span>
            </div>
          </div>

          <!-- Phone Number -->
          <div class="mb-4">
            <p class="text-sm text-gray-600 mb-2">Nomor Ponsel</p>
            <div class="flex items-center gap-2 text-gray-900">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
              </svg>
              <span class="text-base font-medium">{{ orderData.customer.phone || '-' }}</span>
            </div>
          </div>

          <!-- Table Number (Menggantikan Jumlah Orang) -->
          <div class="mb-4">
            <p class="text-sm text-gray-600 mb-2">Nomor Meja</p>
            <div class="flex items-center gap-2 text-gray-900">
              <!-- Icon Meja/Kursi (Ganti icon user group) -->
               <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                 <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd" />
              </svg>
              <span class="text-base font-medium">Meja {{ orderData.table }}</span>
            </div>
          </div>

          <!-- Date & Time -->
          <div class="mb-4">
            <p class="text-sm text-gray-600 mb-2">Tanggal / Jam</p>
            <div class="flex items-center gap-2 text-gray-900">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <span class="text-base font-medium">{{ dateTime }}</span>
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
          Kembali ke Menu
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
const queueNumber = ref('00');
const orderData = ref(null);
const dateTime = ref('');

onMounted(() => {
  // 1. Ambil data pesanan final yang disimpan dari PaymentConfirmation.vue
  const finalSummary = localStorage.getItem('finalOrderSummary');
  
  if (finalSummary) {
    orderData.value = JSON.parse(finalSummary);
    
    // 2. Generate nomor antrian (Simulasi: 1 - 50)
    queueNumber.value = Math.floor(1 + Math.random() * 50).toString();
    
    // 3. Set waktu saat ini
    const now = new Date();
    const date = now.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' }).replace(/\//g, '-');
    const time = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB';
    dateTime.value = `${date} / ${time}`;

  } else {
    // Fallback untuk dev/testing jika diakses langsung
    queueNumber.value = '12';
    orderData.value = {
        customer: { name: 'Tamu', phone: '-' },
        table: '12'
    };
    dateTime.value = '-';
  }
});

const backToMenu = () => {
  // Bersihkan data
  localStorage.removeItem('finalOrderSummary');
  
  // Kembali ke menu
  router.push('/user/menu');
};
</script>

<style scoped>
main {
  overflow-y: auto;
}
</style>