<template>
  <div class="bg-neutral-100 min-h-screen">
    <div class="max-w-md mx-auto bg-white min-h-screen font-sans">

      <header class="fixed top-0 left-0 right-0 max-w-md mx-auto bg-white border-b border-gray-200 z-20">
        <div class="flex items-center justify-between p-4">
          <router-link to="/user/menu" class="p-2 -ml-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
          </router-link>
          
          <h1 class="text-xl font-bold text-gray-900">Your Orders</h1>
          
          <div class="w-8"></div> 
        </div>
      </header>

      <main class="pt-20 pb-36 px-4">
        
        <div v-if="cartItems.length > 0" class="space-y-4">
          <div 
            v-for="item in cartItems" 
            :key="item.id"
            class="flex items-center space-x-4 p-3 bg-white rounded-lg"
          >
            <img :src="item.imageUrl" :alt="item.name" class="w-20 h-20 rounded-lg object-cover flex-shrink-0">
            <div class="flex-1 min-w-0">
              <h2 class="text-base font-bold text-gray-900 truncate">{{ item.name }}</h2>
              <p v-if="item.variant !== 'food'" class="text-sm text-gray-500">
                ({{ item.variant }})
              </p>
              <p class="text-base font-bold text-orange-600 mt-1">Rp{{ formatPrice(item.price) }}</p>
            </div>
            <div class="flex items-center space-x-2 bg-gray-100 rounded-full px-2 py-1">
              <button
                @click="decrementCart(item.id)"
                class="w-7 h-7 rounded-full bg-white flex items-center justify-center hover:bg-gray-200 transition-colors shadow-sm"
                aria-label="Kurangi"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-700" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                </svg>
              </button>
              <span class="text-sm font-bold text-gray-900 w-5 text-center">{{ item.quantity }}</span>
              <button
                @click="incrementCart(item.id)"
                class="w-7 h-7 rounded-full bg-white flex items-center justify-center hover:bg-gray-200 transition-colors shadow-sm"
                aria-label="Tambah"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-700" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
              </button>
            </div>
          </div>
        </div>
        
        <div v-else class="text-center pt-24">
          <p class="text-gray-500">Your cart is empty.</p>
          <router-link to="/user/menu" class="mt-4 inline-block text-orange-600 font-semibold hover:underline">
            Start shopping
          </router-link>
        </div>

        <div v-if="cartItems.length > 0" class="mt-8">
          <label for="notes" class="flex items-center space-x-2 text-lg font-bold text-gray-900 mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-700" viewBox="0 0 20 20" fill="currentColor">
              <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
              <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
            </svg>
            <span>Notes</span>
          </label>
          <textarea
            id="notes"
            v-model="notes"
            rows="3"
            class="w-full p-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-orange-500"
            placeholder="Ex: No onions, extra spicy..."
          ></textarea>
        </div>
      </main>

      <footer 
        v-if="cartItems.length > 0"
        class="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-white border-t border-gray-200 z-20"
      >
        <div class="p-4">
          <div class="flex justify-between items-center mb-4 px-2">
            <span class="text-lg font-medium text-gray-600">Total Price</span>
            <span class="text-2xl font-bold text-gray-900">Rp{{ totalPrice }}</span>
          </div>
    	
          <button 
            @click="goToPayment"
            class="w-full py-4 text-white text-lg font-bold rounded-xl transition-colors shadow-lg"
            style="background-color: #B85814;"
            onmouseover="this.style.backgroundColor='#A04D12'" 
            onmouseout="this.style.backgroundColor='#B85814'"
          >
            Payment
          </button>
        </div>
      </footer>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
const router = useRouter();


const cartItems = ref(JSON.parse(localStorage.getItem('cart') || '[]'));

watch(cartItems, (newCart) => {
  localStorage.setItem('cart', JSON.stringify(newCart));
}, { deep: true });

const notes = ref(localStorage.getItem('cartNotes') || '');

watch(notes, (newNotes) => {
  localStorage.setItem('cartNotes', newNotes);
});


const formatPrice = (price) => {
  if (price === undefined || price === null) return '0';
  return price.toLocaleString('id-ID');
};

const incrementCart = (id) => {
  const item = cartItems.value.find(i => i.id === id);
  if (item) {
    item.quantity++;
  }
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
  router.push('/user/payment');
};


const totalPrice = computed(() => {
  const total = cartItems.value.reduce((sum, item) => {
    return sum + (item.price * item.quantity);
  }, 0);
  return formatPrice(total);
});
</script>

<style scoped>
main {
  overflow-y: auto;
}
</style>