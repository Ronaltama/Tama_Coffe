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
        @click="activeTab = 'paket'"
        :class="[
          'flex-1 py-3 font-medium transition-colors',
          activeTab === 'paket' 
            ? 'text-amber-700 border-b-2 border-amber-700' 
            : 'text-gray-500 hover:text-gray-700'
        ]"
      >
        PAKET
      </button>
      <button 
        @click="activeTab = 'coffee'"
        :class="[
          'flex-1 py-3 font-medium transition-colors',
          activeTab === 'coffee' 
            ? 'text-amber-700 border-b-2 border-amber-700' 
            : 'text-gray-500 hover:text-gray-700'
        ]"
      >
        COFFEE
      </button>
      <button 
        @click="activeTab = 'pastry'"
        :class="[
          'flex-1 py-3 font-medium transition-colors',
          activeTab === 'pastry' 
            ? 'text-amber-700 border-b-2 border-amber-700' 
            : 'text-gray-500 hover:text-gray-700'
        ]"
      >
        PASTRY
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
            <p class="text-sm text-gray-600 mb-3">Rp {{ product.price }}</p>
            
            <router-link 
              :to="`/user/product/${product.id}`"
              class="w-full py-2 bg-amber-600 hover:bg-amber-700 text-white font-medium rounded-md transition-colors text-center mt-auto"
            >
              Add
            </router-link>
            </div>
        </div>
      </div>
    </main>
  </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
// import { useRouter } from 'vue-router'; // Tidak perlu jika pakai <router-link>

// Tab aktif
const activeTab = ref('coffee');

// --- DATA DUMMY ---
const otherProducts = ref([
  {
    id: 1, // Pastikan ID ini unik
    name: 'Iced Coffee Latte',
    price: '25.000',
    imageUrl: 'https://images.unsplash.com/photo-1517487881594-2787fef5ebf7?w=400&q=80'
  },
  {
    id: 2, // Pastikan ID ini unik
    name: 'Caramel Macchiato',
    price: '28.000',
    imageUrl: 'https://images.unsplash.com/photo-1599639957043-f3aa5c986398?w=400&q=80'
  },
  {
    id: 3, // Pastikan ID ini unik
    name: 'Americano',
    price: '22.000',
    imageUrl: 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=400&q=80'
  },
  {
    id: 4, // Pastikan ID ini unik
    name: 'Cappuccino',
    price: '26.000',
    imageUrl: 'https://images.unsplash.com/photo-1572442388796-11668a67e53d?w=400&q=80'
  }
]);

</script>

<style scoped>
/* (Tidak perlu style tambahan) */
</style>