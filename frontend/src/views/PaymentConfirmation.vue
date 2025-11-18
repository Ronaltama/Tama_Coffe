<template>
  <div class="bg-neutral-100 min-h-screen">
    <div class="max-w-md mx-auto bg-white min-h-screen font-sans flex flex-col">

      <header class="fixed top-0 left-0 right-0 max-w-md mx-auto bg-white border-b border-gray-200 z-30 px-4 py-4 flex items-center justify-between">
        <router-link to="/user/payment" class="p-1 -ml-2 text-gray-800 hover:text-orange-600 transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
          </svg>
        </router-link>
        <h1 class="text-lg font-bold text-gray-900">Confirm Payment</h1>
        <div class="w-6"></div>
      </header>

      <main class="flex-1 pt-20 pb-32 px-4 overflow-y-auto bg-neutral-50" v-if="orderData">
        
        <section class="mb-6 bg-white rounded-xl p-5 shadow-sm border border-gray-100">
          <h2 class="text-base font-bold text-gray-900 mb-4">Order Summary</h2>
          
          <div class="space-y-4">
            <div v-for="item in orderData.items" :key="item.id" class="flex items-start justify-between">
              <div class="flex items-center space-x-3">
                <img :src="item.imageUrl" :alt="item.name" class="w-12 h-12 rounded-lg object-cover flex-shrink-0 bg-gray-100">
                <div>
                  <h3 class="text-sm font-semibold text-gray-800 line-clamp-1">{{ item.name }}</h3>
                  <p class="text-xs text-gray-500">Qty: {{ item.quantity }} {{ item.variant !== 'food' ? `(${item.variant})` : '' }}</p>
                </div>
              </div>
              <span class="text-sm font-semibold text-gray-900">Rp{{ formatPrice(item.price * item.quantity) }}</span>
            </div>
          </div>
        </section>

        <section class="mb-6 bg-white rounded-xl p-5 shadow-sm border border-gray-100">
          <h2 class="text-base font-bold text-gray-900 mb-4">Payment Detail</h2>
          <div class="space-y-2 text-sm">
            <div class="flex justify-between items-center text-gray-600">
              <span>Sub-Total</span>
              <span>Rp{{ formatPrice(orderData.totals.subTotal) }}</span>
            </div>
            <div class="flex justify-between items-center text-gray-600">
              <span>Tax & Fees (10%)</span>
              <span>Rp{{ formatPrice(orderData.totals.tax) }}</span>
            </div>
            <div class="border-t border-dashed border-gray-200 my-2"></div>
            <div class="flex justify-between items-center text-base font-bold text-gray-900">
              <span>Total Payment</span>
              <span class="text-orange-600">Rp{{ formatPrice(orderData.totals.total) }}</span>
            </div>
          </div>
        </section>

        <section class="mb-6 bg-white rounded-xl p-5 shadow-sm border border-gray-100">
          <h2 class="text-base font-bold text-gray-900 mb-4">Payment Method</h2>
          
          <div class="flex items-center justify-between p-3 border border-orange-200 bg-orange-50 rounded-lg">
            <div class="flex items-center space-x-3">
              <div class="w-8 h-8 rounded bg-orange-100 flex items-center justify-center text-orange-600">
                 <svg v-if="orderData.payment === 'cash'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                 </svg>
                 <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 14.5v.01M12 6.6l2.5 2.5m0-2.5l-2.5 2.5m6-6l-2.5 2.5m0-2.5l2.5 2.5" />
                 </svg>
              </div>
              <span class="font-bold text-gray-800 uppercase">{{ orderData.payment }}</span>
            </div>
            <div class="text-orange-600">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
            </div>
          </div>
        </section>

        <section class="mb-6">
          <div class="text-xs text-gray-500 text-center">
            Pesanan untuk <span class="font-bold text-gray-700">{{ orderData.customer.name }}</span> 
            di Meja <span class="font-bold text-gray-700">{{ orderData.table }}</span>
          </div>
        </section>

      </main>
      
      <main v-else class="flex-1 flex items-center justify-center bg-neutral-50">
        <p class="text-gray-500">Tidak ada data pesanan.</p>
      </main>

      <footer class="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-white border-t border-gray-200 z-30 pb-4 pt-3 px-4 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
        <div class="flex items-center justify-between mb-3">
           <div class="text-xs text-gray-500 font-medium">Total Payment</div>
           <div class="text-xl font-bold text-gray-900" v-if="orderData">Rp{{ formatPrice(orderData.totals.total) }}</div>
        </div>
        <button 
          @click="processFinalOrder"
          class="w-full py-3.5 bg-orange-600 hover:bg-orange-700 text-white text-base font-bold rounded-xl transition-all shadow-lg active:scale-[0.98] flex items-center justify-center gap-2"
        >
          <span>Confirm & Pay</span>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
             <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
        </button>
      </footer>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const orderData = ref(null);

// --- Lifecycle ---
onMounted(() => {
  // Ambil data pesanan sementara dari localStorage
  const pendingData = localStorage.getItem('pendingOrder');
  if (pendingData) {
    orderData.value = JSON.parse(pendingData);
  } else {
    // Jika tidak ada data, kembalikan ke menu (misal user refresh paksa atau akses langsung url)
    router.push('/user/menu');
  }
});

// --- Methods ---
const formatPrice = (price) => {
  return (price || 0).toLocaleString('id-ID');
};

const processFinalOrder = async () => {
  // 1. Di sini nanti logika API Call ke Backend Laravel (axios.post)
  console.log('FINAL ORDER SENT TO BACKEND:', orderData.value);

  // 2. Bersihkan semua data keranjang dan pesanan sementara
  localStorage.removeItem('cart');
  localStorage.removeItem('cartNotes');
  localStorage.removeItem('pendingOrder');
  
  // 3. Tampilkan notifikasi sukses
  alert('Pembayaran Berhasil! Pesanan Anda sedang diproses.');

  // 4. Arahkan ke halaman Menu (atau halaman Sukses/Track Order jika ada)
  router.push('/user/menu');
};
</script>