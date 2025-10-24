<script setup>
import { ref, computed } from 'vue';

// --- DATA CONTOH ---
// Ganti ini dengan data dari API Anda (fetch products)
const allProducts = ref([
  { id: 1, name: 'Artisan Coffee Beans', category: 'Drink', price: 150000, image: 'https://via.placeholder.com/150/8B4513/FFFFFF?text=Coffee' },
  { id: 2, name: 'Gourmet Chocolate Bar', category: 'Food', price: 80000, image: 'https://via.placeholder.com/150/D2691E/FFFFFF?text=Snack' },
  { id: 3, name: 'Organic Tea Selection', category: 'Drink', price: 150000, image: 'https://via.placeholder.com/150/2E8B57/FFFFFF?text=Tea' },
  { id: 4, name: 'Croissant', category: 'Food', price: 25000, image: 'https://via.placeholder.com/150/DEB887/000000?text=Food' },
  { id: 5, name: 'Espresso', category: 'Drink', price: 18000, image: 'https://via.placeholder.com/150/8B4513/FFFFFF?text=Coffee' },
  { id: 6, name: 'Chicken Katsu', category: 'Food', price: 45000, image: 'https://via.placeholder.com/150/D2691E/FFFFFF?text=Food' },
  { id: 7, name: 'Matcha Latte', category: 'Drink', price: 30000, image: 'https://via.placeholder.com/150/2E8B57/FFFFFF?text=Tea' },
]);
// --- AKHIR DATA CONTOH ---

// Menyimpan kategori yang sedang aktif
const activeCategory = ref('All'); // 'All', 'Drink', atau 'Food'

// Mengubah daftar produk yang tampil berdasarkan kategori
const filteredProducts = computed(() => {
  if (activeCategory.value === 'All') {
    return allProducts.value;
  }
  return allProducts.value.filter(product => product.category === activeCategory.value);
});

// Helper untuk format mata uang
const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(value);
};

// Fungsi untuk memilih produk (ganti dengan logika Anda)
const selectProduct = (product) => {
  alert(`Menambahkan ${product.name} ke pesanan.`);
  // Logika untuk menambahkan ke keranjang/order
};
</script>

<template>
  <div class="space-y-6">

    <div>
      <h1 class="text-3xl font-bold text-gray-900">Add Order</h1>
    </div>

    <div class="flex items-center space-x-2 border-b border-gray-300">
      <button
        @click="activeCategory = 'All'"
        :class="activeCategory === 'All' ? 'border-amber-700 text-amber-700' : 'border-transparent text-gray-600'"
        class="px-4 py-3 font-medium border-b-2 hover:text-amber-700 transition"
      >
        All
      </button>
      <button
        @click="activeCategory = 'Drink'"
        :class="activeCategory === 'Drink' ? 'border-amber-700 text-amber-700' : 'border-transparent text-gray-600'"
        class="px-4 py-3 font-medium border-b-2 hover:text-amber-700 transition"
      >
        Drink
      </button>
      <button
        @click="activeCategory = 'Food'"
        :class="activeCategory === 'Food' ? 'border-amber-700 text-amber-700' : 'border-transparent text-gray-600'"
        class="px-4 py-3 font-medium border-b-2 hover:text-amber-700 transition"
      >
        Food
      </button>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-5">
      
      <template v-if="filteredProducts.length > 0">
        <div
          v-for="product in filteredProducts"
          :key="product.id"
          @click="selectProduct(product)"
          class="bg-white border border-gray-200 rounded-xl shadow-sm cursor-pointer hover:shadow-lg transition overflow-hidden"
        >
          <img :src="product.image" alt="product.name" class="w-full h-36 object-cover">
          
          <div class="p-4">
            <h3 class="font-semibold text-gray-900 truncate">{{ product.name }}</h3>
            <p class="text-sm text-gray-700 mt-1">{{ formatCurrency(product.price) }}</p>
          </div>
        </div>
      </template>
      
      <template v-else>
        <p class="text-gray-500 col-span-full text-center py-10">
          No products found in this category.
        </p>
      </template>

    </div>

  </div>
</template>