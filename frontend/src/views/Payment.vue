<template>
  <div class="bg-neutral-100 min-h-screen">
    <div class="max-w-md mx-auto bg-white min-h-screen font-sans">

      <header class="fixed top-0 left-0 right-0 max-w-md mx-auto bg-white border-b border-gray-200 z-20">
        <div class="flex items-center justify-between p-4">
          <router-link to="/user/cart" class="p-2 -ml-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
          </router-link>
          
          <h1 class="text-xl font-bold text-gray-900">Payment</h1>
          
          <div class="w-8"></div> 
        </div>
      </header>

      <main class="pt-20 pb-32 px-4 bg-neutral-100">
        
        <section class="mb-4 bg-white rounded-xl border-2 border-orange-500 p-4 flex items-center justify-between">
          <div>
            <p class="text-xs text-gray-500 mb-1">Tipe Pemesanan</p>
            <p class="font-bold text-gray-900">{{ orderType }}</p>
          </div>
          <div class="w-8 h-8 rounded-full bg-orange-500 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
          </div>
        </section>

        <section class="mb-4 bg-white rounded-xl p-4">
          <h2 class="text-lg font-bold text-gray-900 mb-4">Informasi Pemesanan</h2>
          
          <div class="space-y-4">
            <div>
              <label class="text-sm text-gray-900 font-medium mb-2 block">Nama Lengkap*</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                  </svg>
                </div>
                <input 
                  v-model="customerName"
                  type="text" 
                  placeholder="Nama Lengkap"
                  class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500"
                />
              </div>
            </div>

            <div>
              <label class="text-sm text-gray-900 font-medium mb-2 block">Nomor Ponsel</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                  </svg>
                </div>
                <input 
                  v-model="phoneNumber"
                  type="tel" 
                  placeholder="Nomor Ponsel"
                  class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500"
                />
              </div>
            </div>

            <div>
              <label class="text-sm text-gray-900 font-medium mb-2 block">Kirim struk ke email</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                  </svg>
                </div>
                <input 
                  v-model="email"
                  type="email" 
                  placeholder="Email"
                  class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500"
                />
              </div>
            </div>

            <!-- Nomor Meja (hanya untuk Dine In) atau Jumlah Orang (untuk Reservasi) -->
            <div v-if="orderType !== 'Reservasi'">
              <label class="text-sm text-gray-900 font-medium mb-2 block">Nomor Meja*</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd" />
                  </svg>
                </div>
                <input 
                  v-model="tableNumber"
                  type="text" 
                  class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500"
                />
              </div>
            </div>
            <div v-else>
              <label class="text-sm text-gray-900 font-medium mb-2 block">Jumlah Orang*</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                  </svg>
                </div>
                <input 
                  v-model="numberOfPeople"
                  type="text"
                  readonly
                  class="w-full pl-10 pr-4 py-3 bg-gray-100 border border-gray-200 rounded-lg text-gray-700"
                />
              </div>
            </div>
          </div>
        </section>

        <section class="mb-4 bg-white rounded-xl p-4">
          <h2 class="text-lg font-bold text-gray-900 mb-4">Metode Pembayaran</h2>
          
          <div class="space-y-3">
            <div class="flex gap-3">
              <button 
                @click="paymentMethod = 'qris'" 
                :class="[
                  'flex-1 py-3 px-4 rounded-lg border-2 font-semibold transition-colors',
                  paymentMethod === 'qris' 
                    ? 'border-orange-500 bg-orange-50 text-orange-700' 
                    : 'border-gray-200 bg-white text-gray-700'
                ]"
              >
                Pembayaran Online
              </button>
              <button 
                @click="paymentMethod = 'cash'"
                :class="[
                  'flex-1 py-3 px-4 rounded-lg border-2 font-semibold transition-colors',
                  paymentMethod === 'cash' 
                    ? 'border-orange-500 bg-orange-50 text-orange-700' 
                    : 'border-gray-200 bg-white text-gray-700'
                ]"
              >
                Bayar di Kasir
              </button>
            </div>

            <button 
              v-if="paymentMethod === 'qris'" 
              @click="paymentMethod = 'qris'"
              :class="[
                'w-full py-3 px-4 rounded-lg border-2 font-semibold transition-colors flex items-center justify-between',
                paymentMethod === 'qris' 
                  ? 'border-orange-500 bg-orange-50 text-orange-700' 
                  : 'border-gray-200 bg-white text-gray-700'
              ]"
            >
              <span>QRIS</span>
              <div v-if="paymentMethod === 'qris'" class="w-6 h-6 rounded-full bg-orange-500 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
              </div>
            </button>
          </div>
        </section>

        <button class="w-full bg-white rounded-xl p-4 flex items-center justify-between mb-4">
          <div class="flex items-center space-x-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-600" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
            </svg>
            <span class="font-semibold text-orange-600">Tambah Promo atau Voucher</span>
          </div>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
          </svg>
        </button>

      </main>

      <footer class="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-white border-t border-gray-200 z-20">
        <div class="p-4 flex items-center justify-between">
          <div>
            <p class="text-xs text-gray-500">Total Pemesanan</p>
            <p class="text-2xl font-bold text-gray-900">Rp{{ formatPrice(totalPaymentPrice) }}</p>
          </div>
          <button 
            @click="handlePlaceOrder"
            class="px-12 py-4 bg-orange-600 hover:bg-orange-700 text-white text-lg font-bold rounded-xl transition-colors shadow-lg"
          >
            Pay
          </button>
        </div>
      </footer>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

// Form Data
const customerName = ref('');
const phoneNumber = ref('');
const email = ref('');
const tableNumber = ref('12');
const numberOfPeople = ref('');
const paymentMethod = ref('qris');
const orderType = ref('Dine In');

// Cart Data
const cartItems = ref(JSON.parse(localStorage.getItem('cart') || '[]'));
const notes = ref(localStorage.getItem('cartNotes') || '');
const TAX_RATE = 0.10;

// Load data on mount
onMounted(() => {
  const storedOrderType = localStorage.getItem('orderType');
  if (storedOrderType) {
    orderType.value = storedOrderType;
  }
  
  // Jika reservasi, load data reservasi
  if (orderType.value === 'Reservasi') {
    const reservationDetails = localStorage.getItem('reservationDetails');
    if (reservationDetails) {
      const details = JSON.parse(reservationDetails);
      customerName.value = details.name;
      phoneNumber.value = details.phone;
      numberOfPeople.value = details.people;
    }
  }
});

// Methods
const formatPrice = (price) => {
  if (price === undefined || price === null) return '0';
  return price.toLocaleString('id-ID');
};

const handlePlaceOrder = () => {
  // Validasi form
  if (!customerName.value.trim()) {
    alert('Silakan masukkan nama Anda');
    return;
  }

  // Siapkan data order
  const orderData = {
    customer: {
      name: customerName.value,
      phone: phoneNumber.value,
      email: email.value
    },
    table: orderType.value === 'Reservasi' ? '-' : tableNumber.value,
    numberOfPeople: orderType.value === 'Reservasi' ? numberOfPeople.value : '-',
    items: cartItems.value,
    notes: notes.value,
    totals: {
      subTotal: subTotalPrice.value,
      tax: taxPrice.value,
      total: totalPaymentPrice.value
    },
    payment: paymentMethod.value,
    orderType: orderType.value
  };

  // Simpan ke localStorage untuk dibawa ke halaman konfirmasi
  localStorage.setItem('pendingOrder', JSON.stringify(orderData));

  // Redirect ke halaman konfirmasi
  router.push('/user/payment/confirm');
};

// Computed
const subTotalPrice = computed(() => {
  return cartItems.value.reduce((sum, item) => {
    return sum + (item.price * item.quantity);
  }, 0);
});

const taxPrice = computed(() => {
  return subTotalPrice.value * TAX_RATE;
});

const totalPaymentPrice = computed(() => {
  return subTotalPrice.value + taxPrice.value;
});
</script>

<style scoped>
main {
  overflow-y: auto;
}
</style>