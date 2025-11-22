<template>
  <div class="bg-neutral-100 min-h-screen">
    <div class="max-w-md mx-auto bg-white min-h-screen font-sans flex flex-col">

      <header class="bg-white border-b border-gray-200 px-4 py-4 flex items-center justify-between">
        <router-link to="/order/payment" class="p-1 -ml-2 text-gray-800 hover:text-orange-600 transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
          </svg>
        </router-link>
        <h1 class="text-lg font-bold text-gray-900">Order Summary</h1>
        <div class="w-6"></div>
      </header>

      <main class="flex-1 pb-24 px-4 overflow-y-auto bg-white" v-if="orderData">
        
        <section class="my-4">
          <div class="border-2 border-orange-500 rounded-xl px-4 py-3 flex items-center justify-between">
            <p class="text-sm text-gray-600">Order Type</p>
            <div class="flex items-center gap-2">
              <p class="font-bold text-base text-gray-900">{{ orderData.orderType }}</p>
              <div class="w-7 h-7 rounded-full bg-orange-600 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
              </div>
            </div>
          </div>
        </section>

        <section class="mb-6 text-center">
          <h2 class="text-base font-semibold text-gray-900 mb-3">Order Number</h2>
          <div class="flex items-center justify-center gap-3 mb-4">
            <div class="bg-gray-100 px-6 py-2 rounded-lg">
              <span class="font-bold text-gray-900">{{ orderNumber }}</span>
            </div>
            <div class="bg-gray-100 px-6 py-2 rounded-lg">
              <span class="font-bold text-gray-900">{{ qrCode }}</span>
            </div>
          </div>
          
          <div class="flex justify-center mb-4">
            <div class="w-56 h-56 bg-white border-2 border-gray-300 rounded-lg flex items-center justify-center">
              <div class="text-center text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-32 w-32 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h.01M12 12v.01M12 21v-1m0-6.01V12m0 0h.01" />
                </svg>
                <p class="text-sm">QR Code</p>
              </div>
            </div>
          </div>

          <div class="bg-orange-50 border border-orange-200 rounded-lg p-3 mb-3 flex items-start gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-600 mt-0.5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <div class="flex-1 text-left">
              <p class="text-xs text-gray-700 leading-relaxed">After making the payment, please take a screenshot of your phone screen and send it using the button below.</p>
            </div>
          </div>

          <button 
            class="w-full bg-orange-600 hover:bg-orange-700 text-white font-semibold py-3.5 rounded-lg transition-colors flex items-center justify-center gap-2 shadow-sm"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
            </svg>
            Upload Bukti QRIS
          </button>
        </section>

        <section class="mb-6">
          <h2 class="text-base font-bold text-gray-900 mb-4">Ordered Items</h2>
          
          <div class="space-y-3">
            <div v-for="item in orderData.items" :key="item.id">
              <div class="flex items-start justify-between mb-1">
                <div class="flex-1">
                  <h3 class="text-sm font-bold text-gray-900">{{ item.quantity }}x {{ item.name }}</h3>
                </div>
                <span class="text-sm font-bold text-gray-900 ml-2">Rp{{ formatPrice(item.price * item.quantity) }}</span>
              </div>
              
              <div class="ml-4 space-y-0.5">
                <p class="text-xs text-gray-600">• {{ item.quantity }} x {{ item.variant || 'Espresso' }}</p>
              </div>
            </div>
          </div>
        </section>

        <div class="border-t border-gray-200 my-4"></div>

        <section class="mb-6">
          <div class="space-y-2 mb-3">
            <div class="flex justify-between items-center text-sm text-gray-600">
              <span>Subtotal ({{ orderData.items.length }} menu)</span>
              <span>Rp{{ formatPrice(orderData.totals.subTotal) }}</span>
            </div>
          </div>
          
          <div class="flex justify-between items-center font-bold text-base text-gray-900">
            <span>Total</span>
            <span class="text-orange-600">Rp{{ formatPrice(orderData.totals.total) }}</span>
          </div>
        </section>

      </main>
      
      <main v-else class="flex-1 flex items-center justify-center bg-white">
        <p class="text-gray-500">Tidak ada data pesanan.</p>
      </main>

      <footer class="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-white border-t border-gray-200 pb-4 pt-3 px-4">
        <button 
          @click="processFinalOrder"
          class="w-full py-3.5 bg-orange-600 hover:bg-orange-700 text-white text-base font-bold rounded-xl transition-all shadow-lg active:scale-[0.98]"
        >
          Submit
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
const orderNumber = ref('APP1172');
const qrCode = ref('CNPRCS6O');

onMounted(() => {
  const pendingData = localStorage.getItem('pendingOrder');
  if (pendingData) {
    orderData.value = JSON.parse(pendingData);
    orderNumber.value = 'APP' + Math.floor(1000 + Math.random() * 9000);
    qrCode.value = 'CNP' + Math.random().toString(36).substring(2, 8).toUpperCase();
  } else {
    router.push('/order/menu');
  }
});

// Methods
const formatPrice = (price) => {
  return (price || 0).toLocaleString('id-ID');
};

const processFinalOrder = async () => {

  const reservationDetails = localStorage.getItem('reservationDetails') 
    ? JSON.parse(localStorage.getItem('reservationDetails')) 
    : null;


  const finalData = {
    ...orderData.value,
    reservationMeta: reservationDetails,
    finalOrderId: orderNumber.value,
    transactionTime: new Date().toISOString()
  };

  localStorage.setItem('finishedOrder', JSON.stringify(finalData));

  console.log('FINAL ORDER SENT TO BACKEND:', finalData);

  localStorage.removeItem('cart');
  localStorage.removeItem('cartNotes');
  localStorage.removeItem('pendingOrder');
  localStorage.removeItem('orderType'); 
  localStorage.removeItem('reservationDetails'); 

  router.push('/order/payment/success');
};
</script>

<style scoped>
main {
  overflow-y: auto;
}
</style>