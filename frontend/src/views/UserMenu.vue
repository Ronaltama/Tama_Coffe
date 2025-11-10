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

    <div class="flex border-b border-gray-200 bg-white sticky top-0 z-10">
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

    <main class="px-4 py-6 bg-white pb-40">
      
      <h2 v-if="activeTab === 'all'" class="text-lg font-bold text-gray-900 mb-4">
        ALL MENU<br/>
        <span class="text-2xl">OUR SIGNATURES</span>
      </h2>
      <h2 v-if="activeTab === 'drinks'" class="text-lg font-bold text-gray-900 mb-4">
        DRINKS<br/>
        <span class="text-2xl">COFFEE & MOCKTAILS</span>
      </h2>
      <h2 v-if="activeTab === 'food'" class="text-lg font-bold text-gray-900 mb-4">
        FOOD<br/>
        <span class="text-2xl">PASTRY & SNACKS</span>
      </h2>
      <div class="grid grid-cols-2 gap-4">
        <div 
          v-for="product in filteredProducts" 
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
                        <p class="text-sm text-gray-600 mb-3">Rp{{ formatPrice(product.price) }}</p>
            
            <button
              @click="handleProductClick(product)"
              class="w-full py-2 bg-white hover:bg-amber-600 text-amber-600 hover:text-white border border-amber-600 font-medium rounded-md transition-colors text-center mt-auto"
            >
              Add
            </button>
          </div>
        </div>
      </div>
    </main>

    <footer 
      v-if="cartItems.length > 0"
      class="fixed bottom-0 left-0 right-0 z-50"
    >
      <div class="max-w-md mx-auto bg-white border-t border-gray-100 p-4">
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
                <span class="text-sm font-medium text-gray-900">{{ item.name }} {{ item.variant !== 'food' ? `(${item.variant})` : '' }}</span>
              </div>
              <span class="text-sm font-bold text-gray-900">Rp{{ formatPrice(item.price * item.quantity) }}</span>
            </div>
          </div>
        </button>
      </div>
    </footer>
    </div>
  </div>
</template>

<script setup>
// PERUBAHAN: Impor 'watch'
import { ref, computed, watch } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const activeTab = ref('all');

// ------------------------------------
// PERUBAHAN UTAMA: SINKRONISASI LOCALSTORAGE
// ------------------------------------

// 1. Muat keranjang dari localStorage saat komponen dibuat
const cartItems = ref(JSON.parse(localStorage.getItem('cart') || '[]'));

// 2. Gunakan 'watch' untuk menyimpan perubahan ke localStorage secara otomatis
//    'deep: true' sangat penting untuk mendeteksi perubahan di dalam array/objek
watch(cartItems, (newCart) => {
  localStorage.setItem('cart', JSON.stringify(newCart));
}, { deep: true });

// ------------------------------------
// AKHIR PERUBAHAN UTAMA
// ------------------------------------


// --- DATA DUMMY ---
// PERUBAHAN: Simpan harga sebagai ANGKA (Number) agar kalkulasi lebih mudah
const drinkProducts = ref([
  {
    id: 1,
    type: 'drink',
    name: 'Coffee Latte',
    price: 25000, // Diubah dari '25.000'
    imageUrl: 'https://images.unsplash.com/photo-1517487881594-2787fef5ebf7?w=400&q=80'
  },
  {
    id: 2,
    type: 'drink',
    name: 'Caramel Macchiato',
    price: 28000, // Diubah dari '28.000'
    imageUrl: 'https://images.unsplash.com/photo-1599639957043-f3aa5c986398?w=400&q=80'
  },
  {
    id: 3,
    type: 'drink',
    name: 'Americano',
    price: 22000, // Diubah dari '22.000'
    imageUrl: 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=400&q=80'
  },
  {
    id: 4,
    type: 'drink',
    name: 'Cappuccino',
    price: 26000, // Diubah dari '26.000'
    imageUrl: 'https://images.unsplash.com/photo-1572442388796-11668a67e53d?w=400&q=80'
  }
]);

const foodProducts = ref([
  {
    id: 101,
    type: 'food',
    name: 'Almond Croissant',
    price: 20000, // Diubah dari '20.000'
    imageUrl: 'https://images.unsplash.com/photo-1509355786693-1e179b5b2911?w=400&q=80'
  },
  {
    id: 102,
    type: 'food',
    name: 'Chicken Sandwich',
    price: 35000, // Diubah dari '35.000'
    imageUrl: 'https://images.unsplash.com/photo-1553909489-cd47e0901a20?w=400&q=80'
  },
  {
    id: 103,
    type: 'food',
    name: 'Red Velvet Cake',
    price: 30000, // Diubah dari '30.000'
    imageUrl: 'https://images.unsplash.com/photo-1606890737304-57a16a5d5e0a?w=400&q=80'
  },
]);


// --- METHODS ---

// PERBAIKAN: Fungsi untuk format harga (karena harga sekarang angka)
const formatPrice = (price) => {
  if (price === undefined || price === null) return '0';
  return price.toLocaleString('id-ID');
};

// Logika ini sudah benar
const handleProductClick = (product) => {
  if (product.type === 'food') {
    addToCart(product);
  } else if (product.type === 'drink') {
    router.push(`/user/product/${product.id}`);
  }
};

// PERBAIKAN: Logika addToCart untuk Makanan
const addToCart = (product) => {
  // Makanan menggunakan ID produk aslinya sebagai ID unik di keranjang
  const cartItemId = product.id.toString(); 
  
  const existingItem = cartItems.value.find(item => item.id === cartItemId);
  
  if (existingItem) {
    // Jika sudah ada, tambah jumlahnya
    existingItem.quantity++;
  } else {
    // Jika belum, tambahkan item baru
    cartItems.value.push({
      id: cartItemId,
      name: product.name,
      price: product.price, // Harga sudah angka
      quantity: 1,
      imageUrl: product.imageUrl,
      productId: product.id,
      variant: 'food' // Tandai sebagai 'food'
    });
  }
  // 'watch' akan otomatis menyimpan ke localStorage
};

// Logika ini sudah benar, 'watch' akan menangani penyimpanan
const incrementCart = (id) => {
  const item = cartItems.value.find(i => i.id === id);
  if (item) {
    item.quantity++;
  }
};

// Logika ini sudah benar, 'watch' akan menangani penyimpanan
const decrementCart = (id) => {
  const item = cartItems.value.find(i => i.id === id);
  if (item && item.quantity > 1) {
    item.quantity--;
  } else if (item && item.quantity === 1) {
    // Hapus item dari keranjang jika jumlahnya jadi 0
    cartItems.value = cartItems.value.filter(i => i.id !== id);
  }
};

const goToCart = () => {
  router.push('/user/cart'); // Pastikan rute '/user/cart' ada di router
};

// --- COMPUTED ---

// Logika ini sudah benar
const filteredProducts = computed(() => {
  if (activeTab.value === 'drinks') {
    return drinkProducts.value;
  }
  if (activeTab.value === 'food') {
    return foodProducts.value;
  }
  if (activeTab.value === 'all') {
    return [...drinkProducts.value, ...foodProducts.value];
  }
  return [];
});

// Logika ini sudah benar
const totalItems = computed(() => {
  return cartItems.value.reduce((sum, item) => sum + item.quantity, 0);
});

// PERBAIKAN: Kalkulasi total harga
const totalPrice = computed(() => {
  const total = cartItems.value.reduce((sum, item) => {
    // 'item.price' sekarang sudah pasti angka
    return sum + (item.price * item.quantity);
  }, 0);
  // Format totalnya untuk ditampilkan
  return formatPrice(total);
});
</script>

<style scoped>
/* Styling sudah menggunakan Tailwind CSS */
.sticky {
  position: -webkit-sticky;
  position: sticky;
  top: 0;
}
</style>