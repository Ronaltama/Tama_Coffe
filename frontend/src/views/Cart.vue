<template>
  <div class="bg-neutral-100 min-h-screen flex flex-col">
    <div class="max-w-md mx-auto bg-white w-full h-screen flex flex-col relative shadow-xl">

      <header class="bg-white border-b border-gray-200 z-20 flex-none">
        <div class="flex items-center justify-between p-4">
          <router-link to="/order/menu" class="p-2 -ml-2 text-gray-800 hover:text-orange-600 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
          </router-link>
          
          <h1 class="text-xl font-bold text-gray-900">Your Orders</h1>
          <div class="w-8"></div> 
        </div>
      </header>

      <main class="flex-1 overflow-y-auto px-4 py-6 bg-neutral-50">
        
        <div v-if="cartItems.length > 0" class="space-y-4">
          <div 
            v-for="item in cartItems" 
            :key="item.id"
            class="flex items-center space-x-4 p-3 bg-white rounded-xl shadow-sm border border-gray-100"
          > 
            <img :src="item.imageUrl" :alt="item.name" class="w-20 h-20 rounded-lg object-cover flex-shrink-0">
            <div class="flex-1 min-w-0">
              <h2 class="text-base font-bold text-gray-900 truncate">{{ item.name }}</h2>
              <p v-if="item.variant !== 'food'" class="text-sm text-gray-500">
                ({{ item.variant }})
              </p>
              <p class="text-base font-bold text-orange-600 mt-1">Rp{{ formatPrice(item.price) }}</p>
            </div>
            
            <div class="flex items-center space-x-2 bg-gray-50 rounded-lg px-2 py-1 border border-gray-200">
              <button @click="decrementCart(item.id)" class="w-7 h-7 rounded-md bg-white flex items-center justify-center hover:bg-gray-100 transition-colors shadow-sm text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                </svg>
              </button>
              <span class="text-sm font-bold text-gray-900 w-6 text-center">{{ item.quantity }}</span>
              <button @click="incrementCart(item.id)" class="w-7 h-7 rounded-md bg-white flex items-center justify-center hover:bg-gray-100 transition-colors shadow-sm text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
              </button>
            </div>
          </div>

          <div class="mt-6 bg-white p-4 rounded-xl shadow-sm border border-gray-100">
            <label for="notes" class="flex items-center space-x-2 text-sm font-bold text-gray-900 mb-3">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
              </svg>
              <span>Catatan Pesanan</span>
            </label>
            <textarea
              id="notes"
              v-model="notes"
              rows="3"
              class="w-full p-3 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition-all resize-none bg-gray-50"
              placeholder="Contoh: Jangan pakai bawang, sambal dipisah..."
            ></textarea>
          </div>
        </div>
        
        <div v-else class="h-full flex flex-col items-center justify-center text-center p-8 -mt-10">
          <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
          </div>
          <h3 class="text-lg font-bold text-gray-900 mb-2">Keranjang Kosong</h3>
          <p class="text-gray-500 text-sm mb-6">Anda belum menambahkan item apapun.</p>
          <router-link to="/order/menu" class="px-6 py-3 bg-orange-600 text-white rounded-xl font-bold text-sm hover:bg-orange-700 transition-colors shadow-lg">
            Mulai Memesan
          </router-link>
        </div>
      </main>

      <footer 
        v-if="cartItems.length > 0"
        class="bg-white border-t border-gray-200 p-4 z-20 flex-none shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]"
      >
        <div class="flex justify-between items-center mb-4 px-1">
          <div>
            <p class="text-xs text-gray-500 font-medium">Total Pembayaran</p>
            <span class="text-2xl font-bold text-gray-900">Rp{{ totalPrice }}</span>
          </div>
        </div>
    	
        <button 
          @click="goToPayment"
          class="w-full py-4 bg-gradient-to-r from-orange-600 to-orange-700 hover:from-orange-700 hover:to-orange-800 text-white text-lg font-bold rounded-xl transition-all shadow-lg active:scale-[0.98]"
        >
          Lanjut Pembayaran
        </button>
      </footer>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const cartItems = ref(JSON.parse(localStorage.getItem('cart') || '[]'));
const notes = ref(localStorage.getItem('cartNotes') || '');

// Watchers untuk menyimpan state
watch(cartItems, (newCart) => {
  localStorage.setItem('cart', JSON.stringify(newCart));
}, { deep: true });

watch(notes, (newNotes) => {
  localStorage.setItem('cartNotes', newNotes);
});

// Formatting
const formatPrice = (price) => {
  if (price === undefined || price === null) return '0';
  return price.toLocaleString('id-ID');
};

// Cart Logic
const incrementCart = (id) => {
  const item = cartItems.value.find(i => i.id === id);
  if (item) item.quantity++;
};

const decrementCart = (id) => {
  const item = cartItems.value.find(i => i.id === id);
  if (item && item.quantity > 1) {
    item.quantity--;
  } else if (item && item.quantity === 1) {
    cartItems.value = cartItems.value.filter(i => i.id !== id);
  }
};

const goToPayment = () => {
  router.push('/order/payment');
};

const totalPrice = computed(() => {
  const total = cartItems.value.reduce((sum, item) => {
    return sum + (item.price * item.quantity);
  }, 0);
  return formatPrice(total);
});
</script>

<style scoped>
/* Custom scrollbar untuk tampilan yang lebih rapi */
main::-webkit-scrollbar {
  width: 6px;
}
main::-webkit-scrollbar-track {
  background: #f5f5f5;
}
main::-webkit-scrollbar-thumb {
  background-color: #ddd;
  border-radius: 20px;
}
</style>