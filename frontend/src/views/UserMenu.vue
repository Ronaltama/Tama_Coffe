<template>
  <div class="bg-neutral-100 min-h-screen">
  <div class="max-w-md mx-auto bg-white min-h-screen font-sans">
    
    <div class="relative">
      <img 
        src="https://images.unsplash.com/photo-1511920170033-f8396924c348?w=800&q=80" 
        alt="Coffee brewing setup"
        class="w-full h-48 object-cover"
      />
      <button class="absolute top-4 right-4 w-10 h-10 bg-white rounded-full shadow-lg flex items-center justify-center hover:bg-gray-50 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </button>
    </div>

    <div class="px-4 py-3 bg-white">
      <h1 class="text-2xl font-bold text-gray-900">Tama Caffeine</h1>
      <p class="text-sm text-gray-500">Open today, 07:00-22:00</p>
    </div>

    <div class="bg-amber-50 py-4 text-center">
      <p class="text-amber-800 font-semibold">Table Number: 12</p>
    </div>

    <div class="flex border-b border-gray-200 bg-white">
      <button 
        @click="activeTab = 'all'"
        :class="[
          'flex-1 py-3 font-medium transition-colors',
          activeTab === 'all' 
            ? 'text-amber-700 border-b-2 border-amber-700' 
            : 'text-gray-500 hover:text-gray-700'
        ]"
      >
        ALL
      </button>
      <button 
        @click="activeTab = 'drinks'"
        :class="[
          'flex-1 py-3 font-medium transition-colors',
          activeTab === 'drinks' 
            ? 'text-amber-700 border-b-2 border-amber-700' 
            : 'text-gray-500 hover:text-gray-700'
        ]"
      >
        DRINKS
      </button>
      <button 
        @click="activeTab = 'food'"
        :class="[
          'flex-1 py-3 font-medium transition-colors',
          activeTab === 'food' 
            ? 'text-amber-700 border-b-2 border-amber-700' 
            : 'text-gray-500 hover:text-gray-700'
        ]"
      >
        FOOD
      </button>
    </div>

    <main class="px-4 py-6 bg-white">
      <h2 class="text-lg font-bold text-gray-900 mb-4">
        PAKET<br/>
        <span class="text-2xl">SIGNATURE COFFEE</span>
      </h2>

      <div class="grid grid-cols-2 gap-4">
        <div 
          v-for="product in otherProducts" 
          :key="product.id" 
          class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow flex flex-col"
        >
          <img 
            :src="product.imageUrl" 
            :alt="product.name"
            class="w-full h-40 object-cover"
          />

          <div class="p-3 flex flex-col flex-grow">
            <h3 class="font-bold text-base text-gray-900">{{ product.name }}</h3>
            <p class="text-sm text-gray-600 mb-3">Rp{{ product.price }}</p>
            
            <button
              @click="addToCart(product)"
              class="w-full py-2 bg-white hover:bg-amber-600 text-amber-600 hover:text-white border border-amber-600 font-medium rounded-md transition-colors text-center mt-auto"
            >
              Add
            </button>
          </div>
        </div>
      </div>
    </main>

    <div 
      v-if="cartItems.length > 0"
      class="px-4 pt-4 pb-8 mt-6 border-t border-gray-100"
      >
      <button
        @click="goToCart"
        class="w-full bg-orange-600 hover:bg-orange-700 rounded-2xl shadow-lg transition-all overflow-hidden"
      >
        <div class="flex items-center justify-between px-6 py-4">
          <div class="flex items-center space-x-3">
            <div class="relative">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              <span class="absolute -top-2 -right-2 bg-white text-orange-600 text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">
                {{ totalItems }}
              </span>
            </div>
            <div class="text-left">
              <p class="text-white text-xs font-medium">Total</p>
              <p class="text-white text-lg font-bold">Rp{{ totalPrice }}</p>
            </div>
          </div>

          <div class="bg-white text-orange-600 px-6 py-2 rounded-lg font-bold text-sm">
            CHECK OUT ({{ totalItems }})
          </div>
        </div>

        <div class="bg-white px-4 py-3">
          <div 
            v-for="item in cartItems" 
            :key="item.id"
            class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0"
          >
            <div class="flex items-center space-x-3 flex-1">
              <div class="flex items-center space-x-2 bg-gray-100 rounded-full px-2 py-1">
                <button
                  @click.stop="decrementCart(item.id)"
                  class="w-6 h-6 rounded-full bg-white flex items-center justify-center hover:bg-gray-200 transition-colors"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-gray-700" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                  </svg>
                </button>
                <span class="text-sm font-bold text-gray-900 w-4 text-center">{{ item.quantity }}</span>
                <button
                  @click.stop="incrementCart(item.id)"
                  class="w-6 h-6 rounded-full bg-white flex items-center justify-center hover:bg-gray-200 transition-colors"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-gray-700" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                  </svg>
                </button>
              </div>
              <span class="text-sm font-medium text-gray-900">{{ item.name }}</span>
            </div>
            <span class="text-sm font-bold text-gray-900">Rp{{ item.price }}</span>
          </div>
        </div>
      </button>
    </div>
    </div> </div> </template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';

// --- BAGIAN SCRIPT INI TIDAK PERLU DIUBAH SAMA SEKALI ---
// (Isinya sama persis dengan yang Anda berikan)

const router = useRouter();
const activeTab = ref('drinks');
const cartItems = ref([]);

const otherProducts = ref([
  {
    id: 1,
    name: 'Coffee Latte',
    price: '25.000',
    imageUrl: 'https://images.unsplash.com/photo-1517487881594-2787fef5ebf7?w=400&q=80'
  },
  {
    id: 2,
    name: 'Caramel Macchiato',
    price: '28.000',
    imageUrl: 'https://images.unsplash.com/photo-1599639957043-f3aa5c986398?w=400&q=80'
  },
  {
    id: 3,
    name: 'Americano',
    price: '22.000',
    imageUrl: 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=400&q=80'
  },
  {
    id: 4,
    name: 'Cappuccino',
    price: '26.000',
    imageUrl: 'https://images.unsplash.com/photo-1572442388796-11668a67e53d?w=400&q=80'
  }
]);

const addToCart = (product) => {
  const existingItem = cartItems.value.find(item => item.id === product.id);
  
  if (existingItem) {
    existingItem.quantity++;
  } else {
    cartItems.value.push({
      ...product,
      quantity: 1
    });
  }
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

const goToCart = () => {
  router.push('/user/cart');
};

const totalItems = computed(() => {
  return cartItems.value.reduce((sum, item) => sum + item.quantity, 0);
});

const totalPrice = computed(() => {
  const total = cartItems.value.reduce((sum, item) => {
    const price = parseInt(item.price.replace('.', ''));
    return sum + (price * item.quantity);
  }, 0);
  return total.toLocaleString('id-ID');
});
</script>

<style scoped>
/* Styling sudah menggunakan Tailwind CSS */
</style>